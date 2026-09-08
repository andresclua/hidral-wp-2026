<?php
/**
 * Grammar Class
 *
 * Validates grammar and spelling on published posts using ScraperAPI + OpenAI.
 * Scrapes the rendered page, extracts text, sends it to OpenAI for proofreading,
 * filters results against reserved words, and emails a stylized report.
 *
 * Also registers admin pages (settings, batch check, debug log) under Terra menu.
 *
 * Only triggers for a configured username (e.g. 'terradev').
 *
 * Requires:
 * - WordPress options: grammar_scraperapi_key, grammar_openai_api_key, grammar_reserved_words, grammar_english_variants, grammar_notification_emails
 * - Settings managed via Grammar Checker admin page
 *
 * @package TerraFramework
 * @since 2.0.0
 *
 * @example
 * // Boot from framework index.php (handles instantiation + admin pages):
 * Grammar::boot($GLOBALS['terra_grammar_config'] ?? []);
 *
 * @example
 * // Direct instantiation (core class only, no admin pages):
 * new Grammar([
 *     'post_types' => ['page', 'insight', 'team_member'],
 *     'trigger_username' => 'terradev',
 * ]);
 */
class Grammar {

    private array $post_types;
    private string $trigger_username;

    /** @var Grammar|null Singleton instance for AJAX callbacks */
    private static ?Grammar $instance = null;

    private function log(string $message): void {
        $logs = get_option('grammar_debug_log', []);
        $logs[] = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        // Keep only last 200 entries
        $logs = array_slice($logs, -200);
        update_option('grammar_debug_log', $logs, false);
    }

    public function __construct(array $config = []) {
        $this->post_types = $config['post_types'] ?? ['page'];
        $this->trigger_username = $config['trigger_username'] ?? 'terradev';

        // Skip hooking if grammar checker is disabled
        if (!get_option('grammar_enabled', true)) {
            return;
        }

        $this->init();
    }

    protected function init(): void {
        add_action('transition_post_status', [$this, 'on_post_publish'], 10, 3);
        add_action('grammar_check_async', [$this, 'run_async_check'], 10, 3);
        add_action('grammar_batch_process', [$this, 'process_batch_next']);
    }

    // ========================================================================
    // BOOT: Static entry point — instantiate + register admin pages/AJAX
    // ========================================================================

    /**
     * Boot the Grammar module: create instance, register admin pages and AJAX handlers.
     * Called from the framework autoloader (classes/index.php).
     */
    public static function boot(array $config = []): void {
        self::$instance = new self($config);

        // Admin pages under Terra menu
        add_action('admin_menu', [__CLASS__, 'register_admin_pages'], 15);

        // Settings POST handler
        add_action('admin_init', [__CLASS__, 'handle_settings_post']);

        // AJAX endpoints
        add_action('wp_ajax_grammar_debug_logs', [__CLASS__, 'ajax_debug_logs']);
        add_action('wp_ajax_grammar_start_batch', [__CLASS__, 'ajax_start_batch']);
        add_action('wp_ajax_grammar_batch_status', [__CLASS__, 'ajax_batch_status']);
        add_action('wp_ajax_grammar_cancel_batch', [__CLASS__, 'ajax_cancel_batch']);
    }

    /**
     * Get the singleton instance (for AJAX callbacks).
     */
    public static function get_instance(): ?Grammar {
        return self::$instance;
    }

    // ========================================================================
    // ADMIN PAGES
    // ========================================================================

    /**
     * Register single admin submenu page under Terra dashboard.
     */
    public static function register_admin_pages(): void {
        add_submenu_page(
            'terra_dashboard',
            'Grammar Checker',
            'Grammar Checker',
            'manage_options',
            'terra_grammar',
            [__CLASS__, 'render_page']
        );
    }

    /**
     * Render the Grammar page with tabs (Settings + Debug Log).
     */
    public static function render_page(): void {
        $is_terradev = wp_get_current_user()->user_login === 'terradev';
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'settings';
        if ($current_tab === 'debug' && !$is_terradev) {
            $current_tab = 'settings';
        }

        $tabs = [
            'settings' => ['label' => 'Settings', 'icon' => 'dashicons-admin-generic'],
        ];
        if ($is_terradev) {
            $tabs['batch'] = ['label' => 'Batch Check', 'icon' => 'dashicons-update'];
            $tabs['debug'] = ['label' => 'Debug Log', 'icon' => 'dashicons-code-standards'];
        }
        ?>
        <?php
            require_once dirname(__DIR__) . '/includes/ui/index.php';
            terra_enqueue_ui('tabs');
        ?>
        <div class="wrap" id="terra-grammar">
        <style>
            #terra-grammar { max-width: 860px; }
            #terra-grammar * { box-sizing: border-box; }

            /* Cards */
            .tg-card { background:#f9fafb; border:1px solid #e2e8f0; border-radius:8px; padding:20px; margin-bottom:16px; }
            .tg-card-header { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
            .tg-card-header .dashicons { color:#64748b; font-size:18px; width:18px; height:18px; }
            .tg-card-title { font-size:14px; font-weight:600; color:#1d2327; margin:0; }
            .tg-card-desc { font-size:12.5px; color:#666; margin:0 0 14px; line-height:1.5; }

            /* Toggle switch — same as System page */
            .tg-switch { position:relative; display:inline-block; width:36px; height:20px; flex-shrink:0; }
            .tg-switch input { opacity:0; width:0; height:0; position:absolute; }
            .tg-switch-slider { position:absolute; cursor:pointer; top:0;left:0;right:0;bottom:0; background:#ccc; border-radius:20px; transition:.25s; }
            .tg-switch-slider:before { content:""; position:absolute; height:14px; width:14px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.25s; }
            .tg-switch input:checked + .tg-switch-slider { background:#00a32a; }
            .tg-switch input:checked + .tg-switch-slider:before { transform:translateX(16px); }

            /* Status banner */
            .tg-status { display:flex; align-items:center; gap:12px; padding:14px 18px; border-radius:8px; margin-bottom:20px; transition:all .2s; }
            .tg-status.is-active { background:#f0faf0; border:1px solid #00a32a; }
            .tg-status.is-inactive { background:#fef0f0; border:1px solid #d63638; }
            .tg-status-label { font-weight:600; font-size:13px; }
            .tg-status.is-active .tg-status-label { color:#00a32a; }
            .tg-status.is-inactive .tg-status-label { color:#d63638; }
            .tg-status-desc { font-size:12px; color:#666; margin-top:2px; }

            /* Dynamic rows */
            .tg-row { display:flex; align-items:center; gap:8px; margin-bottom:8px; }
            .tg-row input, .tg-row select { flex:1; max-width:400px; }
            .tg-row .tg-btn-remove { width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center; border:1px solid #dcdcde; background:#fff; border-radius:6px; color:#999; cursor:pointer; transition:.15s; font-size:16px; line-height:1; }
            .tg-row .tg-btn-remove:hover { border-color:#d63638; color:#d63638; background:#fef0f0; }

            /* Buttons */
            .tg-btn-add { display:inline-flex; align-items:center; gap:4px; padding:6px 14px; border:1px dashed #dcdcde; background:none; border-radius:6px; color:#666; font-size:13px; cursor:pointer; transition:.15s; }
            .tg-btn-add:hover { border-color:#2271b1; color:#2271b1; }

            /* Checkbox group */
            .tg-checkbox-group { display:flex; gap:12px; margin:12px 0; }
            .tg-checkbox-label { display:flex; align-items:center; gap:8px; padding:10px 16px; border:1px solid #dcdcde; border-radius:8px; cursor:pointer; font-size:13px; color:#50575e; transition:.15s; }
            .tg-checkbox-label:hover { border-color:#2271b1; }
            .tg-checkbox-label:has(input:checked) { border-color:#2271b1; background:#f0f6fc; color:#1d2327; font-weight:500; }

            /* API key field */
            .tg-api-row { display:flex; align-items:center; gap:8px; margin-bottom:12px; }
            .tg-api-row label { min-width:120px; font-size:13px; font-weight:500; color:#50575e; }
            .tg-api-row input { flex:1; max-width:320px; }
            .tg-api-row .tg-btn-eye { width:32px; height:32px; padding:0; display:flex; align-items:center; justify-content:center; border:1px solid #dcdcde; background:#fff; border-radius:6px; cursor:pointer; color:#999; transition:.15s; }
            .tg-api-row .tg-btn-eye:hover { color:#1d2327; border-color:#999; }
            .tg-api-row .tg-btn-eye .dashicons { font-size:16px; width:16px; height:16px; line-height:32px; }

            /* Batch */
            .tg-batch-progress { background:#dcdcde; border-radius:6px; height:10px; overflow:hidden; margin:12px 0; }
            .tg-batch-progress-bar { background:#2271b1; height:100%; border-radius:6px; transition:width .3s; }

            /* Admin divider */
            .tg-divider { display:flex; align-items:center; gap:12px; margin:8px 0 20px; }
            .tg-divider:before, .tg-divider:after { content:""; flex:1; height:1px; background:#dcdcde; }
            .tg-divider span { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#999; font-weight:600; }

            /* Debug log */
            .tg-log-container { background:#1d2327; border-radius:8px; overflow:hidden; max-height:560px; overflow-y:auto; }
            .tg-log-entry { display:flex; gap:12px; padding:8px 14px; border-bottom:1px solid #2c3338; font-size:13px; font-family:monospace; }
            .tg-log-entry:last-child { border-bottom:none; }
            .tg-log-time { color:#72aee6; white-space:nowrap; }
            .tg-log-msg { word-break:break-word; }
            .tg-log-empty { padding:40px; text-align:center; color:#a7aaad; font-size:13px; }
            .tg-log-toolbar { display:flex; gap:8px; margin-bottom:16px; align-items:center; }
            .tg-log-btn { display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:6px; font-size:13px; font-weight:500; cursor:pointer; border:none; transition:.15s; }
            .tg-log-btn.is-primary { background:#2271b1; color:#fff; }
            .tg-log-btn.is-primary:hover { background:#135e96; }
            .tg-log-btn.is-danger { background:#fef0f0; color:#d63638; border:1px solid #d63638; }
            .tg-log-btn.is-danger:hover { background:#fcdbdc; }
            .tg-log-btn.is-neutral { background:#f0f0f1; color:#50575e; }
            .tg-log-btn.is-neutral:hover { background:#dcdcde; }
        </style>

        <h1>Grammar Checker</h1>

        <ul class="c--tabs__nav">
            <?php foreach ($tabs as $tab_key => $tab_info):
                $active = $current_tab === $tab_key ? ' is-active' : '';
            ?>
                <li class="c--tabs__nav-item<?php echo $active; ?>" data-tab="<?php echo esc_attr($tab_key); ?>"><?php echo esc_html($tab_info['label']); ?></li>
            <?php endforeach; ?>
        </ul>

        <div class="c--tabs__content">
            <div class="c--tabs__panel<?php echo $current_tab === 'settings' ? ' is-active' : ''; ?>" data-panel="settings">
                <?php self::render_settings_tab(); ?>
            </div>
            <?php if ($is_terradev): ?>
                <div class="c--tabs__panel<?php echo $current_tab === 'batch' ? ' is-active' : ''; ?>" data-panel="batch">
                    <?php self::render_batch_tab(); ?>
                </div>
                <div class="c--tabs__panel<?php echo $current_tab === 'debug' ? ' is-active' : ''; ?>" data-panel="debug">
                    <?php self::render_debug_tab(); ?>
                </div>
            <?php endif; ?>
        </div>

        </div>
        <?php
    }

    // ========================================================================
    // SETTINGS: POST handler
    // ========================================================================

    public static function handle_settings_post(): void {
        if (
            !isset($_POST['grammar_settings_nonce']) ||
            !wp_verify_nonce($_POST['grammar_settings_nonce'], 'grammar_save_settings')
        ) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        // Grammar enabled toggle
        update_option('grammar_enabled', !empty($_POST['grammar_enabled']));

        // English variants (checkbox array)
        $variants = [];
        if (!empty($_POST['english_variants']) && is_array($_POST['english_variants'])) {
            $allowed = ['American English', 'British English'];
            foreach ($_POST['english_variants'] as $v) {
                $v = sanitize_text_field($v);
                if (in_array($v, $allowed, true)) {
                    $variants[] = $v;
                }
            }
        }
        update_option('grammar_english_variants', $variants);

        // Reserved words (flat array)
        $words = [];
        if (!empty($_POST['reserved_words']) && is_array($_POST['reserved_words'])) {
            foreach ($_POST['reserved_words'] as $w) {
                $w = sanitize_text_field(trim($w));
                if ($w !== '') {
                    $words[] = $w;
                }
            }
        }
        update_option('grammar_reserved_words', $words);

        // Trigger users (flat array of user IDs)
        $trigger_users = [];
        if (!empty($_POST['trigger_users']) && is_array($_POST['trigger_users'])) {
            foreach ($_POST['trigger_users'] as $u) {
                $id = intval($u);
                if ($id > 0) {
                    $trigger_users[] = $id;
                }
            }
        }
        update_option('grammar_trigger_users', $trigger_users);

        // Notification emails (only terradev can update these)
        if (wp_get_current_user()->user_login === 'terradev') {
            $emails = [];
            if (!empty($_POST['notification_emails']) && is_array($_POST['notification_emails'])) {
                foreach ($_POST['notification_emails'] as $e) {
                    $e = sanitize_email(trim($e));
                    if (is_email($e)) {
                        $emails[] = $e;
                    }
                }
            }
            update_option('grammar_notification_emails', $emails);
        }

        // Redirect with success notice
        wp_redirect(add_query_arg('grammar_updated', '1', wp_get_referer()));
        exit;
    }

    // ========================================================================
    // SETTINGS TAB: Render
    // ========================================================================

    private static function render_settings_tab(): void {
        $is_terradev = wp_get_current_user()->user_login === 'terradev';
        $grammar_enabled = get_option('grammar_enabled', true);
        $english_variants = get_option('grammar_english_variants', []);
        $reserved_words = get_option('grammar_reserved_words', []);
        $trigger_users = get_option('grammar_trigger_users', []);
        $notification_emails = get_option('grammar_notification_emails', []);
        $all_users = get_users(['orderby' => 'display_name', 'order' => 'ASC']);

        if (empty($reserved_words)) $reserved_words = [''];
        if (empty($notification_emails)) $notification_emails = [''];

        $user_options_html = '<option value="">— Select user —</option>';
        foreach ($all_users as $wp_user) {
            if ($wp_user->user_login === 'terradev') continue;
            $user_options_html .= '<option value="' . esc_attr($wp_user->ID) . '">'
                . esc_html($wp_user->display_name) . ' (' . esc_html($wp_user->user_login) . ')'
                . '</option>';
        }
        ?>

        <?php if (!empty($_GET['grammar_updated'])): ?>
            <div class="notice notice-success is-dismissible" style="margin:0 0 20px;"><p>Settings saved.</p></div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('grammar_save_settings', 'grammar_settings_nonce'); ?>

            <!-- Status Toggle -->
            <div class="tg-status <?php echo $grammar_enabled ? 'is-active' : 'is-inactive'; ?>" id="grammar-status-banner">
                <label class="tg-switch">
                    <input type="checkbox" name="grammar_enabled" value="1" <?php checked($grammar_enabled); ?> id="grammar-enabled-toggle">
                    <span class="tg-switch-slider"></span>
                </label>
                <div>
                    <div class="tg-status-label" id="grammar-status-text"><?php echo $grammar_enabled ? 'Grammar Checker Active' : 'Grammar Checker Inactive'; ?></div>
                    <div class="tg-status-desc">Checks run automatically when a trigger user publishes a page</div>
                </div>
            </div>

            <!-- Reserved Words -->
            <div class="tg-card">
                <div class="tg-card-header">
                    <span class="dashicons dashicons-book"></span>
                    <h3 class="tg-card-title">Reserved Words</h3>
                </div>
                <p class="tg-card-desc">Words that will not be flagged as errors (brand names, technical terms, etc.)</p>
                <div id="grammar-reserved-words">
                    <?php foreach ($reserved_words as $word): ?>
                        <div class="tg-row">
                            <input type="text" name="reserved_words[]" value="<?php echo esc_attr($word); ?>" class="regular-text" placeholder="e.g. WordPress">
                            <button type="button" class="tg-btn-remove grammar-remove-row">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="tg-btn-add" id="grammar-add-word">+ Add Word</button>
            </div>

            <!-- English Variants -->
            <div class="tg-card">
                <div class="tg-card-header">
                    <span class="dashicons dashicons-translation"></span>
                    <h3 class="tg-card-title">English Variants</h3>
                </div>
                <p class="tg-card-desc">Select accepted variants. If both are selected, only inconsistencies between them will be flagged.</p>
                <div class="tg-checkbox-group">
                    <label class="tg-checkbox-label">
                        <input type="checkbox" name="english_variants[]" value="American English"
                            <?php checked(in_array('American English', $english_variants)); ?>>
                        <span class="dashicons dashicons-flag" style="font-size:16px;width:16px;height:16px;"></span>
                        American English
                    </label>
                    <label class="tg-checkbox-label">
                        <input type="checkbox" name="english_variants[]" value="British English"
                            <?php checked(in_array('British English', $english_variants)); ?>>
                        <span class="dashicons dashicons-flag" style="font-size:16px;width:16px;height:16px;"></span>
                        British English
                    </label>
                </div>
            </div>

            <!-- Trigger Users -->
            <div class="tg-card">
                <div class="tg-card-header">
                    <span class="dashicons dashicons-groups"></span>
                    <h3 class="tg-card-title">Trigger Users</h3>
                </div>
                <p class="tg-card-desc">Additional users that trigger a grammar check on publish (besides <code>terradev</code>).</p>
                <div id="grammar-trigger-users">
                    <?php foreach ($trigger_users as $uid):
                        $tu = get_user_by('id', $uid);
                        if (!$tu) continue;
                    ?>
                        <div class="tg-row">
                            <select name="trigger_users[]">
                                <option value="">— Select user —</option>
                                <?php foreach ($all_users as $wp_user):
                                    if ($wp_user->user_login === 'terradev') continue;
                                ?>
                                    <option value="<?php echo esc_attr($wp_user->ID); ?>" <?php selected($wp_user->ID, $tu->ID); ?>>
                                        <?php echo esc_html($wp_user->display_name); ?> (<?php echo esc_html($wp_user->user_login); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="tg-btn-remove grammar-remove-row">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="tg-btn-add" id="grammar-add-trigger-user">+ Add User</button>
            </div>

            <!-- Admin section (terradev only) -->
            <?php if ($is_terradev): ?>
            <div class="tg-divider"><span>Admin Only</span></div>

            <div class="tg-card" style="border-color:#fde68a;">
                <div class="tg-card-header">
                    <span class="dashicons dashicons-email-alt"></span>
                    <h3 class="tg-card-title">Notification Emails</h3>
                </div>
                <p class="tg-card-desc">Email addresses that receive grammar check reports.</p>
                <div id="grammar-notification-emails">
                    <?php foreach ($notification_emails as $email): ?>
                        <div class="tg-row">
                            <input type="email" name="notification_emails[]" value="<?php echo esc_attr($email); ?>" class="regular-text" placeholder="email@example.com">
                            <button type="button" class="tg-btn-remove grammar-remove-row">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="tg-btn-add" id="grammar-add-email">+ Add Email</button>
            </div>
            <?php endif; ?>

            <div style="margin-top:12px;">
                <button type="submit" class="button button-primary" style="border-radius:8px;padding:6px 14px;height:auto;">
                    Save Settings
                </button>
            </div>
        </form>

        <script>
        (function() {
            function makeRow(name, type) {
                var div = document.createElement('div');
                div.className = 'tg-row';
                div.innerHTML = '<input type="' + type + '" name="' + name + '[]" value="" class="regular-text">'
                              + '<button type="button" class="tg-btn-remove grammar-remove-row">&times;</button>';
                return div;
            }

            var userOptionsHtml = <?php echo wp_json_encode($user_options_html); ?>;

            document.getElementById('grammar-add-trigger-user').addEventListener('click', function() {
                var div = document.createElement('div');
                div.className = 'tg-row';
                div.innerHTML = '<select name="trigger_users[]">' + userOptionsHtml + '</select>'
                              + '<button type="button" class="tg-btn-remove grammar-remove-row">&times;</button>';
                document.getElementById('grammar-trigger-users').appendChild(div);
            });

            document.getElementById('grammar-add-word').addEventListener('click', function() {
                document.getElementById('grammar-reserved-words').appendChild(makeRow('reserved_words', 'text'));
            });

            var addEmailBtn = document.getElementById('grammar-add-email');
            if (addEmailBtn) {
                addEmailBtn.addEventListener('click', function() {
                    document.getElementById('grammar-notification-emails').appendChild(makeRow('notification_emails', 'email'));
                });
            }

            document.querySelectorAll('.tg-btn-eye').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var input = document.getElementById(btn.dataset.target);
                    var icon = btn.querySelector('.dashicons');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('dashicons-visibility', 'dashicons-hidden');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('dashicons-hidden', 'dashicons-visibility');
                    }
                });
            });

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('grammar-remove-row')) {
                    e.target.closest('.tg-row').remove();
                }
            });

            var toggleCb = document.getElementById('grammar-enabled-toggle');
            if (toggleCb) {
                toggleCb.addEventListener('change', function() {
                    var banner = document.getElementById('grammar-status-banner');
                    var label = document.getElementById('grammar-status-text');
                    if (toggleCb.checked) {
                        banner.className = 'tg-status is-active';
                        label.textContent = 'Grammar Checker Active';
                    } else {
                        banner.className = 'tg-status is-inactive';
                        label.textContent = 'Grammar Checker Inactive';
                    }
                });
            }
        })();
        </script>
        <?php
    }

    // ========================================================================
    // BATCH TAB: Render
    // ========================================================================

    private static function render_batch_tab(): void {
        ?>
        <div class="tg-card">
            <div class="tg-card-header">
                <span class="dashicons dashicons-update"></span>
                <h3 class="tg-card-title">Full Site Grammar Check</h3>
            </div>
            <p class="tg-card-desc">Run a grammar check on every published page. Pages are processed one at a time via background cron to avoid timeouts. Roughly 1-2 minutes per page.</p>

            <?php wp_nonce_field('grammar_batch_nonce', 'grammar_batch_nonce_field'); ?>

            <div id="batch-idle">
                <button type="button" class="button button-primary" id="batch-start-btn" style="border-radius:8px;padding:6px 14px;height:auto;">
                    Run Full Site Check
                </button>
            </div>

            <div id="batch-running" style="display:none;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
                    <span class="spinner is-active" style="float:none;margin:0;"></span>
                    <strong id="batch-progress-text" style="font-size:14px;color:#1e293b;">0 / 0 pages (0%)</strong>
                </div>
                <div class="tg-batch-progress">
                    <div class="tg-batch-progress-bar" id="batch-progress-bar" style="width:0%;"></div>
                </div>
                <div id="batch-current" style="font-size:13px;color:#64748b;margin-bottom:8px;"></div>
                <div id="batch-stats" style="font-size:13px;color:#475569;margin-bottom:16px;"></div>
                <button type="button" class="tg-log-btn is-danger" id="batch-cancel-btn">Cancel</button>
            </div>

            <div id="batch-done" style="display:none;">
                <div id="batch-summary-card" style="padding:16px 20px;border-radius:8px;margin-bottom:16px;"></div>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="tg-log-btn is-neutral" id="batch-dismiss-btn">Dismiss</button>
                    <button type="button" class="tg-log-btn is-primary" id="batch-rerun-btn">Run Again</button>
                </div>
            </div>
        </div>

        <script>
        (function() {
            var startBtn = document.getElementById('batch-start-btn');
            if (!startBtn) return;

            var ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
            var batchNonce = document.getElementById('grammar_batch_nonce_field') ? document.getElementById('grammar_batch_nonce_field').value : '';

            var elIdle = document.getElementById('batch-idle');
            var elRunning = document.getElementById('batch-running');
            var elDone = document.getElementById('batch-done');
            var elProgressText = document.getElementById('batch-progress-text');
            var elProgressBar = document.getElementById('batch-progress-bar');
            var elCurrent = document.getElementById('batch-current');
            var elStats = document.getElementById('batch-stats');
            var elSummaryCard = document.getElementById('batch-summary-card');

            function showState(state) {
                elIdle.style.display = state === 'idle' ? '' : 'none';
                elRunning.style.display = (state === 'running' || state === 'cancelling') ? '' : 'none';
                elDone.style.display = (state === 'completed' || state === 'cancelled') ? '' : 'none';
            }

            function updateProgress(data) {
                var processed = data.processed || 0, total = data.total || 0;
                var pct = total > 0 ? Math.round((processed / total) * 100) : 0;
                elProgressText.textContent = processed + ' / ' + total + ' pages (' + pct + '%)';
                elProgressBar.style.width = pct + '%';
                if (data.current_title) elCurrent.textContent = 'Checking: ' + data.current_title;
                else if (data.status === 'cancelling') elCurrent.textContent = 'Cancelling...';
                else elCurrent.textContent = 'Waiting...';

                var clean = 0, errors = 0, failed = 0;
                (data.results || []).forEach(function(r) {
                    if (r.status === 'clean') clean++;
                    else if (r.status === 'errors_found') errors++;
                    else failed++;
                });
                elStats.innerHTML = '<span style="color:#16a34a;">' + clean + ' clean</span> &middot; '
                    + '<span style="color:#dc2626;">' + errors + ' with errors</span> &middot; '
                    + '<span style="color:#94a3b8;">' + failed + ' failed</span>';
            }

            function showSummary(data) {
                var results = data.results || [], clean = 0, withErrors = 0, failed = 0, totalErrors = 0;
                results.forEach(function(r) {
                    if (r.status === 'clean') clean++;
                    else if (r.status === 'errors_found') { withErrors++; totalErrors += r.error_count; }
                    else failed++;
                });
                var ok = withErrors === 0 && failed === 0;
                var label = data.status === 'cancelled' ? 'Cancelled' : 'Completed';
                elSummaryCard.style.border = '1px solid ' + (ok ? '#bbf7d0' : '#fecaca');
                elSummaryCard.style.background = ok ? '#f0fdf4' : '#fef2f2';
                elSummaryCard.innerHTML = '<strong style="color:' + (ok ? '#16a34a' : '#dc2626') + ';font-size:15px;">' + label + '</strong>'
                    + '<div style="margin-top:8px;font-size:13px;color:#475569;">' + data.processed + ' / ' + data.total + ' pages<br>'
                    + '<span style="color:#16a34a;">' + clean + ' clean</span> &middot; '
                    + '<span style="color:#dc2626;">' + withErrors + ' with errors (' + totalErrors + ')</span> &middot; '
                    + '<span style="color:#94a3b8;">' + failed + ' failed</span></div>'
                    + (data.completed_at ? '<div style="margin-top:6px;font-size:12px;color:#94a3b8;">Finished: ' + data.completed_at + '</div>' : '');
            }

            function pollStatus() {
                fetch(ajaxUrl + '?action=grammar_batch_status&_=' + Date.now(), { credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (!res.success) return;
                        var d = res.data;
                        if (d.status === 'running' || d.status === 'cancelling') { showState('running'); updateProgress(d); setTimeout(pollStatus, 5000); }
                        else if (d.status === 'completed' || d.status === 'cancelled') { showState(d.status); showSummary(d); }
                        else showState('idle');
                    }).catch(function() { setTimeout(pollStatus, 10000); });
            }

            startBtn.addEventListener('click', function() {
                if (!confirm('Run a grammar check on ALL published pages?')) return;
                startBtn.disabled = true;
                var fd = new FormData(); fd.append('action', 'grammar_start_batch'); fd.append('nonce', batchNonce);
                fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        startBtn.disabled = false;
                        if (res.success) { showState('running'); updateProgress({ processed:0, total:res.data.total, results:[] }); setTimeout(pollStatus, 5000); }
                        else alert('Error: ' + (res.data || 'Unknown'));
                    }).catch(function() { startBtn.disabled = false; alert('Request failed.'); });
            });

            document.getElementById('batch-cancel-btn').addEventListener('click', function() {
                if (!confirm('Cancel?')) return;
                this.disabled = true; this.textContent = 'Cancelling...';
                var fd = new FormData(); fd.append('action', 'grammar_cancel_batch'); fd.append('nonce', batchNonce);
                var btn = this;
                fetch(ajaxUrl, { method:'POST', credentials:'same-origin', body:fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) { btn.disabled = false; btn.textContent = 'Cancel'; if (!res.success) alert(res.data); })
                    .catch(function() { btn.disabled = false; btn.textContent = 'Cancel'; });
            });

            document.getElementById('batch-dismiss-btn').addEventListener('click', function() { showState('idle'); });
            document.getElementById('batch-rerun-btn').addEventListener('click', function() { showState('idle'); startBtn.click(); });

            pollStatus();
        })();
        </script>
        <?php
    }

    // ========================================================================
    // DEBUG LOG TAB: Render
    // ========================================================================

    private static function render_debug_tab(): void {
        $ajax_url = admin_url('admin-ajax.php');
        ?>
        <div class="tg-card" style="padding-bottom:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div class="tg-card-header" style="margin-bottom:0;">
                    <span class="dashicons dashicons-code-standards"></span>
                    <h3 class="tg-card-title">Debug Log</h3>
                    <span id="gd-status" style="font-size:12px;color:#94a3b8;font-weight:400;margin-left:8px;"></span>
                </div>
                <div class="tg-log-toolbar" style="margin-bottom:0;">
                    <button id="gd-refresh" class="tg-log-btn is-primary"><span class="dashicons dashicons-update" style="font-size:14px;width:14px;height:14px;line-height:16px;"></span> Refresh</button>
                    <button id="gd-csv" class="tg-log-btn is-neutral"><span class="dashicons dashicons-download" style="font-size:14px;width:14px;height:14px;line-height:16px;"></span> CSV</button>
                    <button id="gd-clear" class="tg-log-btn is-danger"><span class="dashicons dashicons-trash" style="font-size:14px;width:14px;height:14px;line-height:16px;"></span> Clear</button>
                </div>
            </div>
            <div id="gd-log">
                <div class="tg-log-container"><div class="tg-log-empty">Loading...</div></div>
            </div>
        </div>
        <script>
        (function() {
            var logEl = document.getElementById('gd-log');
            var statusEl = document.getElementById('gd-status');
            var ajaxUrl = '<?php echo esc_js($ajax_url); ?>';
            var currentLogs = [];

            function formatLine(line) {
                var match = line.match(/^\[(.+?)\]\s(.+)$/);
                if (!match) return '<div class="tg-log-entry"><span class="tg-log-msg" style="color:#e2e8f0;">' + escapeHtml(line) + '</span></div>';

                var time = match[1];
                var msg = match[2];

                var color = '#cbd5e1';
                var icon = '';
                if (msg.indexOf('FAILED') !== -1 || msg.indexOf('not configured') !== -1 || msg.indexOf('status 5') !== -1 || msg.indexOf('status 4') !== -1) {
                    color = '#f87171'; icon = '&#10007; ';
                } else if (msg.indexOf('Email sent') !== -1 || (msg.indexOf('wp_mail') !== -1 && msg.indexOf('OK') !== -1)) {
                    color = '#4ade80'; icon = '&#10003; ';
                } else if (msg.indexOf('error(s)') !== -1 || msg.indexOf('Skipped') !== -1 || msg.indexOf('Notification sent') !== -1) {
                    color = '#fbbf24'; icon = '&#9888; ';
                } else if (msg.indexOf('Starting async') !== -1 || msg.indexOf('BATCH: Started') !== -1) {
                    color = '#60a5fa'; icon = '&#9654; ';
                } else if (msg.indexOf('BATCH: Completed') !== -1 || msg.indexOf('clean') !== -1) {
                    color = '#4ade80'; icon = '&#10003; ';
                }

                return '<div class="tg-log-entry">'
                    + '<span class="tg-log-time">' + escapeHtml(time) + '</span>'
                    + '<span class="tg-log-msg" style="color:' + color + ';">' + icon + escapeHtml(msg) + '</span>'
                    + '</div>';
            }

            function escapeHtml(str) {
                var div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function renderLogs(logs) {
                currentLogs = logs;
                if (!logs || logs.length === 0) {
                    logEl.innerHTML = '<div class="tg-log-container"><div class="tg-log-empty">No log entries yet. Publish a page as <code style="background:#334155;padding:2px 6px;border-radius:3px;color:#e2e8f0;">terradev</code> to trigger a grammar check.</div></div>';
                    return;
                }
                var reversed = logs.slice().reverse();
                var html = '<div class="tg-log-container">';
                reversed.forEach(function(line) { html += formatLine(line); });
                html += '</div>';
                logEl.innerHTML = html;
            }

            function fetchLogs(clear) {
                var url = ajaxUrl + '?action=grammar_debug_logs' + (clear ? '&clear=1' : '') + '&_=' + Date.now();
                statusEl.textContent = clear ? 'Clearing...' : 'Refreshing...';
                fetch(url, { credentials: 'same-origin' })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        renderLogs(res.data || []);
                        var now = new Date();
                        statusEl.textContent = 'Updated ' + now.toLocaleTimeString();
                    })
                    .catch(function() { statusEl.textContent = 'Fetch failed'; });
            }

            function downloadCsv() {
                if (!currentLogs || currentLogs.length === 0) return;
                var csv = 'Timestamp,Message\n';
                currentLogs.slice().reverse().forEach(function(line) {
                    var match = line.match(/^\[(.+?)\]\s(.+)$/);
                    var time = match ? match[1] : '';
                    var msg = match ? match[2] : line;
                    csv += '"' + time.replace(/"/g, '""') + '","' + msg.replace(/"/g, '""') + '"\n';
                });
                var blob = new Blob([csv], { type: 'text/csv' });
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'grammar-debug-log.csv';
                a.click();
                URL.revokeObjectURL(a.href);
            }

            document.getElementById('gd-refresh').addEventListener('click', function() { fetchLogs(false); });
            document.getElementById('gd-clear').addEventListener('click', function() {
                if (confirm('Clear all log entries?')) fetchLogs(true);
            });
            document.getElementById('gd-csv').addEventListener('click', downloadCsv);

            fetchLogs(false);
        })();
        </script>
        <?php
    }

    // ========================================================================
    // AJAX ENDPOINTS
    // ========================================================================

    public static function ajax_debug_logs(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        if (isset($_GET['clear'])) {
            delete_option('grammar_debug_log');
            wp_send_json_success([]);
            return;
        }

        $logs = get_option('grammar_debug_log', []);
        wp_send_json_success($logs);
    }

    public static function ajax_start_batch(): void {
        if (!current_user_can('manage_options') || wp_get_current_user()->user_login !== 'terradev') {
            wp_send_json_error('Unauthorized', 403);
        }

        check_ajax_referer('grammar_batch_nonce', 'nonce');

        $grammar = self::$instance;
        if (!$grammar) {
            wp_send_json_error('Grammar instance not available');
        }

        $result = $grammar->start_batch(get_current_user_id());

        if (isset($result['error'])) {
            wp_send_json_error($result['error']);
        }

        wp_send_json_success($result);
    }

    public static function ajax_batch_status(): void {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        $grammar = self::$instance;
        if (!$grammar) {
            wp_send_json_error('Grammar instance not available');
        }

        wp_send_json_success($grammar->get_batch_state());
    }

    public static function ajax_cancel_batch(): void {
        if (!current_user_can('manage_options') || wp_get_current_user()->user_login !== 'terradev') {
            wp_send_json_error('Unauthorized', 403);
        }

        check_ajax_referer('grammar_batch_nonce', 'nonce');

        $grammar = self::$instance;
        if (!$grammar) {
            wp_send_json_error('Grammar instance not available');
        }

        $cancelled = $grammar->cancel_batch();
        if (!$cancelled) {
            wp_send_json_error('No running batch to cancel.');
        }

        wp_send_json_success(['message' => 'Cancel requested.']);
    }

    // ========================================================================
    // CORE: Post publish hook
    // ========================================================================

    /**
     * Triggered when post status changes.
     * Runs for the hardcoded trigger_username plus any additional trigger users from settings.
     */
    public function on_post_publish(string $new_status, string $old_status, \WP_Post $post): void {
        if ($new_status !== 'publish') {
            return;
        }

        if (!in_array($post->post_type, $this->post_types, true)) {
            return;
        }

        $current_user = wp_get_current_user();
        if (!$current_user || !$this->is_trigger_user($current_user)) {
            $this->log("Skipped - current user '{$current_user->user_login}' is not a trigger user");
            return;
        }

        $this->log("Post publish detected - '{$post->post_title}' (type: {$post->post_type}) by user '{$current_user->user_login}'");

        // Cooldown: skip if this post was already checked recently
        $cooldown_key = 'grammar_cooldown_' . $post->ID;
        if (get_transient($cooldown_key)) {
            $this->log("Skipped - cooldown active for post {$post->ID}");
            return;
        }
        set_transient($cooldown_key, true, MINUTE_IN_SECONDS);

        if (!wp_next_scheduled('grammar_check_async', [$post->ID, $current_user->ID])) {
            wp_schedule_single_event(time() + 5, 'grammar_check_async', [$post->ID, $current_user->ID]);
        }
    }

    /**
     * Run the grammar check asynchronously via WP Cron.
     *
     * @param int $post_id         Post to check.
     * @param int $trigger_user_id User whose publish triggered the check.
     * @param int $attempt         Which scrape attempt this run is (1-based). Travels in the
     *                             cron args so the retry sequence is self-limiting.
     */
    public function run_async_check(int $post_id, int $trigger_user_id = 0, int $attempt = 1): void {
        $post = get_post($post_id);

        if (!$post || $post->post_status !== 'publish') {
            return;
        }

        $post_url = get_permalink($post->ID);

        if (!$post_url) {
            $this->log("Could not get permalink for post ID {$post->ID}");
            return;
        }

        $this->log("Starting async check for '{$post->post_title}' - {$post_url}");

        // One attempt per cron tick; a retry is rescheduled as a fresh cron event so each
        // gets its own execution budget. The attempt number travels in the cron args
        // rather than in a transient: that makes the sequence self-limiting, so neither a
        // starved WP-Cron nor an unavailable object cache can reset the count and turn
        // this into an endless retry loop against a permanently unreachable URL.
        $max_attempts = 3;

        $html = $this->scrape_page($post_url, 1);
        if (!$html) {
            if ($attempt < $max_attempts) {
                wp_schedule_single_event(
                    time() + 60,
                    'grammar_check_async',
                    [$post->ID, $trigger_user_id, $attempt + 1]
                );
                $this->log("Scrape failed (attempt {$attempt}/{$max_attempts}) — retry scheduled in 60s for {$post_url}");
            } else {
                $this->log("Scrape failed after {$max_attempts} attempts — giving up on {$post_url}");
            }
            return;
        }

        $this->log("ScraperAPI returned " . strlen($html) . " bytes of HTML");

        $text = $this->extract_text($html);
        if (empty(trim($text))) {
            $this->log("No text extracted from HTML for {$post_url}");
            return;
        }

        $this->log("Extracted " . strlen($text) . " chars of text");

        // Split into chunks to stay within reasonable token limits per call
        $max_chars = 15000;
        $chunks = str_split($text, $max_chars);
        $total_chunks = count($chunks);

        if ($total_chunks > 1) {
            $this->log("Text split into {$total_chunks} chunks of up to {$max_chars} chars");
        }

        $errors = [];
        foreach ($chunks as $index => $chunk) {
            $chunk_num = $index + 1;
            $chunk_errors = $this->check_grammar($chunk);

            if ($chunk_errors === null) {
                $this->log("OpenAI request failed for chunk {$chunk_num}/{$total_chunks} of {$post_url}");
                return;
            }

            $this->log("Chunk {$chunk_num}/{$total_chunks}: OpenAI returned " . count($chunk_errors) . " error(s)");
            $errors = array_merge($errors, $chunk_errors);
        }

        $this->log(count($errors) . " total error(s) before filtering");

        $errors = $this->filter_errors($errors);

        $this->log(count($errors) . " error(s) after filtering reserved words");

        if (empty($errors)) {
            $this->log("No errors remaining - skipping email");
            return;
        }

        $this->send_notification($post_url, $errors, $trigger_user_id);
        $this->log("Email sent for '{$post->post_title}'");
    }

    // ========================================================================
    // CORE: Scraping & text extraction
    // ========================================================================

    /**
     * Fetch the page HTML via ScraperAPI.
     *
     * One attempt per call by default. Retries are scheduled as separate cron events by
     * the caller so each gets a fresh PHP execution budget: inline sleep+retry (3 x 35s
     * plus sleeps = 111s) overran the host request limit, the process was killed
     * mid-scrape, and the pending log entries died with it — leaving no trace of why.
     *
     * @param string $url          Target URL.
     * @param int    $max_attempts Inline attempts. Leave at 1 unless the caller is known
     *                             to have room for several 35s requests in one process.
     * @return string|null Raw HTML or null on failure.
     */
    protected function scrape_page(string $url, int $max_attempts = 1): ?string {
        $api_key = function_exists('get_field') ? get_field('scraperapi_key', 'option') : '';
        if (empty($api_key)) $api_key = get_option('grammar_scraperapi_key', '');

        if (empty($api_key)) {
            $this->log('ScraperAPI key not configured — set it under General Options → API Keys');
            return null;
        }

        // Pre-flight from the server itself. If the target refuses anonymous access,
        // ScraperAPI cannot reach it either: it retries internally for ~60s and returns
        // nothing, so the failure reaches us as an opaque cURL timeout that points at
        // ScraperAPI instead of at the real cause. One cheap HEAD makes it actionable.
        // Only a definitive 401/403 aborts — anything else proceeds, since plenty of
        // servers answer HEAD oddly and that must not block a scrape that would work.
        $preflight = wp_remote_head($url, ['timeout' => 10]);
        if (!is_wp_error($preflight)) {
            $pf_code = wp_remote_retrieve_response_code($preflight);

            // Only 401 aborts: it means the target demands credentials, so ScraperAPI is
            // guaranteed to fail too (the WP Engine password-protection case). A 403 is
            // deliberately NOT fatal — it can come from a WAF or CDN challenging this
            // server's own IP while ScraperAPI's proxies still get through, and aborting
            // on it would skip scrapes that would have succeeded.
            if ($pf_code === 401) {
                $this->log("Target URL returned 401 to an anonymous request — ScraperAPI cannot reach it either. Password protection enabled on this environment?");
                return null;
            }

            if ($pf_code && ($pf_code < 200 || $pf_code >= 300)) {
                $this->log("Pre-flight: target URL returned {$pf_code} — continuing anyway");
            }
        }

        // JS rendering is OFF by default. This theme renders its content server-side,
        // so the extracted text is byte-identical either way (verified across three
        // sites), while render=true costs 10-25x the credits, adds 40-50s per request,
        // and is refused outright by some Cloudflare-protected domains. Projects whose
        // content is painted by JS can opt back in:
        //   add_filter('terra_grammar_scraperapi_render', '__return_true');
        $args = [
            'api_key' => $api_key,
            'url'     => $url,
        ];

        if (apply_filters('terra_grammar_scraperapi_render', false, $url)) {
            $args['render'] = 'true';
        }

        $scraper_url = add_query_arg($args, 'https://api.scraperapi.com');

        for ($i = 1; $i <= $max_attempts; $i++) {
            $response = wp_remote_get($scraper_url, [
                'timeout' => 35,
            ]);

            if (is_wp_error($response)) {
                $this->log("ScraperAPI error (attempt {$i}/{$max_attempts}) - " . $response->get_error_message());
                if ($i < $max_attempts) { sleep(3); continue; }
                return null;
            }

            $code = wp_remote_retrieve_response_code($response);
            if ($code >= 200 && $code < 300) {
                if ($i > 1) $this->log("ScraperAPI succeeded on retry (attempt {$i})");
                return wp_remote_retrieve_body($response);
            }

            $this->log("ScraperAPI returned status {$code} (attempt {$i}/{$max_attempts})");
            if ($i < $max_attempts) { sleep(3); continue; }
        }

        return null;
    }

    /**
     * Extract clean text from HTML.
     * Targets <main> or <article> content to avoid nav/footer noise.
     */
    protected function extract_text(string $html): string {
        // Isolate main content area to avoid nav, footer, cookie banners, etc.
        if (preg_match('/<main[^>]*>(.*)<\/main>/is', $html, $match)) {
            $html = $match[1];
        } elseif (preg_match('/<article[^>]*>(.*)<\/article>/is', $html, $match)) {
            $html = $match[1];
        }

        // Remove script, style, and SVG tags and their contents
        $html = preg_replace('/<(script|style|svg)[^>]*>.*?<\/\1>/is', '', $html);

        // Remove all HTML tags
        $text = wp_strip_all_tags($html);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    // ========================================================================
    // CORE: OpenAI grammar check
    // ========================================================================

    /**
     * Send text to OpenAI for grammar/spelling checking.
     *
     * @return array|null Array of error objects or null on failure.
     */
    protected function check_grammar(string $text): ?array {
        $api_key = function_exists('get_field') ? get_field('openai_api_key', 'option') : '';
        if (empty($api_key)) $api_key = get_option('grammar_openai_api_key', '');

        if (empty($api_key)) {
            $this->log('OpenAI API key not configured — set it under General Options → API Keys');
            return null;
        }

        $english_variant = $this->get_english_variant();
        $this->log("English variant setting: '{$english_variant}'");
        $reserved_words = $this->get_reserved_words();

        $reserved_words_context = '';
        if (!empty($reserved_words)) {
            $reserved_words_context = "\n\nThe following are reserved/brand words that should NOT be flagged as errors: " . implode(', ', $reserved_words);
        }

        $variant_count = count(explode(', ', $english_variant));
        if ($variant_count > 1) {
            $variant_instruction = "Accepted English variants: {$english_variant}. Since MULTIPLE variants are accepted:
- A word spelled correctly in ANY accepted variant is NOT an error (e.g. both \"color\" and \"colour\" are valid).
- HOWEVER, if the same word appears in BOTH American and British spelling within the text (e.g. \"colour\" AND \"color\" both appear, or \"favour\" AND \"favor\" both appear), flag ALL such inconsistent pairs. You MUST check every British/American spelling pair thoroughly — do not miss any.
- For inconsistency flags, set the explanation to start with \"Inconsistency:\" so they can be identified.";
        } else {
            $variant_instruction = "Accepted English variant: {$english_variant}. Only flag spellings that are incorrect in this specific variant.";
        }

        $system_prompt = "You are a professional proofreader. Your task is to find grammar, spelling, and punctuation errors in the provided text.

{$variant_instruction}{$reserved_words_context}

Your process:
1. First, scan for genuine errors: misspellings, grammatical mistakes, punctuation errors.
2. Then, scan for spelling inconsistencies: find ALL words that appear in both American and British forms. Flag every instance of every inconsistent pair — do not skip any.

Respond with a JSON object containing an \"errors\" array. Each element must be an object with these keys:
- \"word\": the problematic word or phrase exactly as it appears
- \"context\": a short snippet of surrounding text (5-10 words) showing where the error occurs
- \"suggestion\": the corrected word or phrase
- \"explanation\": a brief explanation of why it's wrong (start with \"Inconsistency:\" for variant inconsistencies)

If there are no errors, return: {\"errors\": []}";

        $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode([
                'model'           => 'gpt-4o-mini',
                'response_format' => ['type' => 'json_object'],
                'messages'        => [
                    ['role' => 'system', 'content' => $system_prompt],
                    ['role' => 'user', 'content' => $text],
                ],
                'temperature' => 0.2,
            ]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            $this->log('OpenAI error - ' . $response->get_error_message());
            return null;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            $body = wp_remote_retrieve_body($response);
            $this->log("OpenAI returned status {$code} - {$body}");
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $content = $body['choices'][0]['message']['content'] ?? '';
        $parsed = json_decode($content, true);

        if (is_array($parsed) && isset($parsed['errors']) && is_array($parsed['errors'])) {
            return $parsed['errors'];
        }

        $this->log('Could not parse OpenAI response - ' . $content);
        return null;
    }

    // ========================================================================
    // CORE: Helpers
    // ========================================================================

    /**
     * Get reserved words from options.
     */
    protected function get_reserved_words(): array {
        $words = get_option('grammar_reserved_words', []);
        return is_array($words) ? array_filter($words) : [];
    }

    /**
     * Get the English variant preference from options.
     */
    protected function get_english_variant(): string {
        $variants = get_option('grammar_english_variants', []);

        if (empty($variants) || !is_array($variants)) {
            return 'Accept both American and British English';
        }

        return implode(', ', $variants);
    }

    /**
     * Get notification emails from options.
     */
    protected function get_notify_emails(): array {
        $emails = get_option('grammar_notification_emails', []);

        if (is_array($emails)) {
            $emails = array_filter($emails, 'is_email');
        }

        if (empty($emails)) {
            $emails = [get_option('admin_email')];
        }

        return $emails;
    }

    /**
     * Check if a user should trigger grammar checks.
     * Always matches the hardcoded trigger_username, plus any user IDs in grammar_trigger_users.
     */
    protected function is_trigger_user(\WP_User $user): bool {
        if (strtolower($user->user_login) === strtolower($this->trigger_username)) {
            return true;
        }

        $extra_users = get_option('grammar_trigger_users', []);
        if (!is_array($extra_users) || empty($extra_users)) {
            return false;
        }

        return in_array($user->ID, array_map('intval', $extra_users), true);
    }

    /**
     * Filter out errors where the flagged word matches a reserved word (case-insensitive).
     */
    protected function filter_errors(array $errors): array {
        $reserved = array_map('strtolower', $this->get_reserved_words());

        if (empty($reserved)) {
            return $errors;
        }

        return array_values(array_filter($errors, function ($error) use ($reserved) {
            $word = strtolower($error['word'] ?? '');
            return !in_array($word, $reserved, true);
        }));
    }

    // ========================================================================
    // CORE: Notifications
    // ========================================================================

    /**
     * Send email notification with the error report.
     *
     * - terradev (or fallback): sends to the configured notification emails list.
     * - Any other trigger user: sends only to that user's WP account email.
     */
    protected function send_notification(string $url, array $errors, int $trigger_user_id = 0): void {
        $slug = '/' . trim(wp_parse_url($url, PHP_URL_PATH), '/');
        $error_count = count($errors);
        $subject = sprintf('[Grammar Check] %s — %d error(s) found', $slug, $error_count);
        $message = $this->build_email_message($url, $slug, $errors);
        $emails = $this->get_emails_for_user($trigger_user_id);

        foreach ($emails as $email) {
            new Mail_To((object) [
                'email'   => $email,
                'subject' => $subject,
                'message' => $message,
            ]);
            $this->log("Mail_To sent to {$email}");
        }

        $this->log("Notification sent to " . implode(', ', $emails) . " for {$slug} ({$error_count} errors)");
    }

    /**
     * Determine who receives the grammar report.
     * Always sends to the configured notification emails list.
     */
    protected function get_emails_for_user(int $trigger_user_id): array {
        return $this->get_notify_emails();
    }

    // ========================================================================
    // BATCH PROCESSING
    // ========================================================================

    /**
     * Get all published posts of configured types.
     */
    public function get_all_checkable_posts(): array {
        $query = new \WP_Query([
            'post_type'      => $this->post_types,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ]);

        return $query->posts;
    }

    /**
     * Start a full site batch grammar check.
     */
    public function start_batch(int $user_id): array {
        $state = $this->get_batch_state();

        // Detect stale batch: if 'running' or 'cancelling' with no activity for 5+ minutes
        // and no cron scheduled, the previous run died — allow restart.
        if (in_array($state['status'], ['running', 'cancelling'], true)) {
            $stale_threshold = 5 * MINUTE_IN_SECONDS;
            $last_active = strtotime($state['last_activity'] ?? $state['started_at'] ?? '');
            $is_stale = $last_active && (time() - $last_active > $stale_threshold) && !wp_next_scheduled('grammar_batch_process');

            if (!$is_stale) {
                return ['error' => 'A batch check is already running.'];
            }
            $this->log("BATCH: Detected stale batch (status: {$state['status']}, no cron scheduled, no activity for " . round((time() - $last_active) / 60) . "m) — resetting");
        }

        $post_ids = $this->get_all_checkable_posts();
        if (empty($post_ids)) {
            return ['error' => 'No published pages found to check.'];
        }

        $state = [
            'status'        => 'running',
            'started_at'    => current_time('mysql'),
            'started_by'    => $user_id,
            'total'         => count($post_ids),
            'queue'         => $post_ids,
            'current_id'    => null,
            'current_title' => null,
            'processed'     => 0,
            'results'       => [],
            'completed_at'  => null,
            'retry_counts'  => [],
            'last_activity' => current_time('mysql'),
        ];

        update_option('grammar_batch_state', $state, false);
        $this->schedule_next_batch(5);

        $this->log("BATCH: Started full site check — {$state['total']} pages queued by user #{$user_id}");

        return ['total' => $state['total']];
    }

    /**
     * Schedule the next batch cron tick.
     * Clears any stale events first to avoid WP 5.7+ duplicate detection blocking.
     */
    private function schedule_next_batch(int $delay = 10): void {
        wp_unschedule_hook('grammar_batch_process');
        $result = wp_schedule_single_event(time() + $delay, 'grammar_batch_process');
        if (is_wp_error($result)) {
            $this->log("BATCH: Failed to schedule next cron — " . $result->get_error_message());
        }
    }

    /**
     * Process the next page in the batch queue (WP Cron callback).
     */
    public function process_batch_next(): void {
        @set_time_limit(120); // Request extended execution time for this cron tick

        $state = $this->get_batch_state();

        // Handle cancellation
        if ($state['status'] === 'cancelling') {
            $state['status'] = 'cancelled';
            $state['completed_at'] = current_time('mysql');
            update_option('grammar_batch_state', $state, false);
            $this->log("BATCH: Cancelled — {$state['processed']}/{$state['total']} pages processed");
            $this->send_batch_summary($state);
            return;
        }

        if ($state['status'] !== 'running') {
            return;
        }

        // Update heartbeat so stale detection knows we're alive
        $state['last_activity'] = current_time('mysql');
        update_option('grammar_batch_state', $state, false);

        // Queue empty — done
        if (empty($state['queue'])) {
            $state['status'] = 'completed';
            $state['current_id'] = null;
            $state['current_title'] = null;
            $state['completed_at'] = current_time('mysql');
            update_option('grammar_batch_state', $state, false);
            $this->log("BATCH: Completed — {$state['processed']}/{$state['total']} pages processed");
            $this->send_batch_summary($state);
            return;
        }

        // Pop next post
        $post_id = array_shift($state['queue']);
        $post = get_post($post_id);

        if (!$post || $post->post_status !== 'publish') {
            $state['processed']++;
            $state['results'][] = [
                'post_id'     => $post_id,
                'title'       => $post ? $post->post_title : "Post #{$post_id}",
                'url'         => '',
                'error_count' => 0,
                'status'      => 'skipped',
                'errors'      => [],
            ];
            update_option('grammar_batch_state', $state, false);
            $this->log("BATCH: Skipped post #{$post_id} — not published or not found");
            $this->schedule_next_batch();
            return;
        }

        $state['current_id'] = $post_id;
        $state['current_title'] = $post->post_title;
        update_option('grammar_batch_state', $state, false);

        $post_url = get_permalink($post_id);
        $retry_counts = $state['retry_counts'] ?? [];
        $attempt = ($retry_counts[$post_id] ?? 0) + 1;

        $this->log("BATCH: Processing '{$post->post_title}' ({$state['processed']}/{$state['total']}) — {$post_url}");

        $result = $this->run_batch_check($post, $post_url);

        $state = $this->get_batch_state(); // re-read in case of long processing

        // If scrape failed and we haven't exhausted retries, re-queue at the end
        if ($result['status'] === 'scrape_failed' && $attempt < 3) {
            $state['retry_counts'][$post_id] = $attempt;
            $state['queue'][] = $post_id; // re-queue at end so other pages proceed
            $state['current_id'] = null;
            $state['current_title'] = null;
            update_option('grammar_batch_state', $state, false);
            $this->log("BATCH: '{$post->post_title}' scrape failed (attempt {$attempt}/3) — re-queued for retry");
            $this->schedule_next_batch();
            return;
        }

        $state['processed']++;
        $state['results'][] = $result;
        $state['current_id'] = null;
        $state['current_title'] = null;
        unset($state['retry_counts'][$post_id]);
        update_option('grammar_batch_state', $state, false);

        $this->log("BATCH: '{$post->post_title}' — {$result['status']} ({$result['error_count']} errors)");

        // Schedule next
        $this->schedule_next_batch();
    }

    /**
     * Run the grammar check pipeline for a single post (batch mode — no email).
     */
    protected function run_batch_check(\WP_Post $post, string $url): array {
        $base = [
            'post_id' => $post->ID,
            'title'   => $post->post_title,
            'url'     => $url,
        ];

        // Single attempt per cron tick — retries are handled at the scheduling level
        // in process_batch_next() to avoid PHP timeout from inline sleep/retry
        $html = $this->scrape_page($url, 1);
        if (!$html) {
            return array_merge($base, ['error_count' => 0, 'status' => 'scrape_failed', 'errors' => []]);
        }

        $text = $this->extract_text($html);
        if (empty(trim($text))) {
            return array_merge($base, ['error_count' => 0, 'status' => 'no_text', 'errors' => []]);
        }

        $chunks = str_split($text, 15000);
        $errors = [];

        foreach ($chunks as $index => $chunk) {
            $chunk_errors = $this->check_grammar($chunk);
            if ($chunk_errors === null) {
                return array_merge($base, ['error_count' => 0, 'status' => 'openai_failed', 'errors' => []]);
            }
            $errors = array_merge($errors, $chunk_errors);
        }

        $errors = $this->filter_errors($errors);

        $status = empty($errors) ? 'clean' : 'errors_found';
        return array_merge($base, ['error_count' => count($errors), 'status' => $status, 'errors' => $errors]);
    }

    /**
     * Cancel a running batch.
     * Accepts both 'running' and 'cancelling' states — the latter handles the case
     * where a previous cancel was set but the cron died before processing it.
     */
    public function cancel_batch(): bool {
        $state = $this->get_batch_state();
        if (!in_array($state['status'], ['running', 'cancelling'], true)) {
            return false;
        }

        $state['status'] = 'cancelling';
        update_option('grammar_batch_state', $state, false);
        $this->log("BATCH: Cancel requested");

        // Ensure a cron event exists to process the cancellation.
        // If the previous cron died (e.g. PHP timeout), no event may be scheduled.
        $this->schedule_next_batch(5);

        return true;
    }

    /**
     * Get the current batch state.
     */
    public function get_batch_state(): array {
        return get_option('grammar_batch_state', [
            'status'        => 'idle',
            'started_at'    => null,
            'started_by'    => null,
            'total'         => 0,
            'queue'         => [],
            'current_id'    => null,
            'current_title' => null,
            'processed'     => 0,
            'results'       => [],
            'completed_at'  => null,
            'retry_counts'  => [],
            'last_activity' => null,
        ]);
    }

    /**
     * Send the batch summary email when batch completes or is cancelled.
     */
    protected function send_batch_summary(array $state): void {
        $emails = $this->get_notify_emails();
        $status_label = $state['status'] === 'cancelled' ? 'Cancelled' : 'Completed';

        $pages_with_errors = array_filter($state['results'], fn($r) => $r['status'] === 'errors_found');
        $total_errors = array_sum(array_column($state['results'], 'error_count'));

        $subject = sprintf(
            '[Grammar Batch] %s — %d pages checked, %d error(s)',
            $status_label,
            $state['processed'],
            $total_errors
        );

        $message = $this->build_batch_summary_email($state, $status_label);

        foreach ($emails as $email) {
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            wp_mail($email, $subject, $message, $headers);
            $this->log("BATCH: Summary email sent to {$email}");
        }
    }

    // ========================================================================
    // EMAIL TEMPLATES
    // ========================================================================

    /**
     * Build the HTML summary email for a batch run.
     */
    protected function build_batch_summary_email(array $state, string $status_label): string {
        $pages_with_errors = array_filter($state['results'], fn($r) => $r['status'] === 'errors_found');
        $clean_pages = array_filter($state['results'], fn($r) => $r['status'] === 'clean');
        $failed_pages = array_filter($state['results'], fn($r) => in_array($r['status'], ['scrape_failed', 'no_text', 'openai_failed', 'skipped']));
        $total_errors = array_sum(array_column($state['results'], 'error_count'));

        $duration = '';
        if ($state['started_at'] && $state['completed_at']) {
            $start = strtotime($state['started_at']);
            $end = strtotime($state['completed_at']);
            $diff = $end - $start;
            $minutes = floor($diff / 60);
            $seconds = $diff % 60;
            $duration = $minutes > 0 ? "{$minutes}m {$seconds}s" : "{$seconds}s";
        }

        $status_color = $total_errors > 0 ? '#dc2626' : '#16a34a';
        $status_bg = $total_errors > 0 ? '#fef2f2' : '#f0fdf4';

        // Build pages with errors section
        $error_pages_html = '';
        foreach ($pages_with_errors as $result) {
            $page_title = esc_html($result['title']);
            $page_url = esc_url($result['url']);
            $error_count = count($result['errors']);

            $error_rows = '';
            foreach ($result['errors'] as $i => $error) {
                $num = $i + 1;
                $word = esc_html($error['word'] ?? '');
                $context = esc_html($error['context'] ?? '');
                $suggestion = esc_html($error['suggestion'] ?? '');
                $explanation = esc_html($error['explanation'] ?? '');

                $error_rows .= "
                    <tr style='border-bottom: 1px solid #e5e7eb;'>
                        <td style='padding: 8px 12px; color: #6b7280; font-size: 12px;'>{$num}</td>
                        <td style='padding: 8px 12px;'>
                            <span style='background-color: #fef2f2; color: #dc2626; padding: 2px 6px; border-radius: 3px; font-weight: 600; font-size: 13px;'>{$word}</span>
                        </td>
                        <td style='padding: 8px 12px; color: #6b7280; font-size: 12px; font-style: italic;'>...{$context}...</td>
                        <td style='padding: 8px 12px;'>
                            <span style='background-color: #f0fdf4; color: #16a34a; padding: 2px 6px; border-radius: 3px; font-weight: 600; font-size: 13px;'>{$suggestion}</span>
                        </td>
                        <td style='padding: 8px 12px; color: #6b7280; font-size: 12px;'>{$explanation}</td>
                    </tr>";
            }

            $error_pages_html .= "
                <div style='margin-bottom: 24px; border: 1px solid #fecaca; border-radius: 6px; overflow: hidden;'>
                    <div style='background-color: #fef2f2; padding: 12px 16px; border-bottom: 1px solid #fecaca;'>
                        <strong style='color: #dc2626;'>{$page_title}</strong>
                        <span style='color: #6b7280; font-size: 13px; margin-left: 8px;'>— {$error_count} error(s)</span>
                        <br><a href='{$page_url}' style='color: #2563eb; font-size: 12px; text-decoration: none;'>{$page_url}</a>
                    </div>
                    <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>
                        <thead>
                            <tr style='background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0;'>
                                <th style='padding: 8px 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase;'>#</th>
                                <th style='padding: 8px 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase;'>Word</th>
                                <th style='padding: 8px 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase;'>Context</th>
                                <th style='padding: 8px 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase;'>Suggestion</th>
                                <th style='padding: 8px 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase;'>Explanation</th>
                            </tr>
                        </thead>
                        <tbody>{$error_rows}</tbody>
                    </table>
                </div>";
        }

        // Build failed pages section
        $failed_html = '';
        if (!empty($failed_pages)) {
            $failed_rows = '';
            foreach ($failed_pages as $result) {
                $title = esc_html($result['title']);
                $reason = esc_html(str_replace('_', ' ', $result['status']));
                $failed_rows .= "<tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 8px 12px; font-size: 13px;'>{$title}</td>
                    <td style='padding: 8px 12px; color: #dc2626; font-size: 13px;'>{$reason}</td>
                </tr>";
            }
            $failed_html = "
                <h2 style='font-size: 16px; color: #1e293b; margin: 24px 0 12px 0; font-weight: 600;'>Failed Pages</h2>
                <table style='width: 100%; border-collapse: collapse;'>
                    <thead>
                        <tr style='background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0;'>
                            <th style='padding: 8px 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase;'>Page</th>
                            <th style='padding: 8px 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase;'>Reason</th>
                        </tr>
                    </thead>
                    <tbody>{$failed_rows}</tbody>
                </table>";
        }

        // Clean pages list
        $clean_html = '';
        if (!empty($clean_pages)) {
            $clean_list = implode(', ', array_map(fn($r) => esc_html($r['title']), $clean_pages));
            $clean_html = "
                <div style='margin-top: 16px; padding: 12px 16px; background-color: #f0fdf4; border-radius: 6px; border-left: 3px solid #16a34a;'>
                    <strong style='color: #16a34a; font-size: 13px;'>Clean pages (" . count($clean_pages) . "):</strong>
                    <span style='color: #6b7280; font-size: 13px;'>{$clean_list}</span>
                </div>";
        }

        return "
        <html>
        <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background-color: #f9fafb;'>
            <div style='max-width: 900px; margin: 0 auto; padding: 32px 16px;'>
                <div style='background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;'>

                    <div style='background-color: #1e293b; padding: 24px 32px;'>
                        <h1 style='margin: 0; color: #ffffff; font-size: 20px; font-weight: 600;'>Full Site Grammar Check — {$status_label}</h1>
                    </div>

                    <div style='padding: 32px;'>

                        <div style='background-color: {$status_bg}; border-radius: 6px; padding: 20px 24px; margin-bottom: 24px;'>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569; width: 160px;'>Status:</td>
                                    <td style='padding: 6px 0;'><span style='color: {$status_color}; font-weight: 700;'>{$status_label}</span></td>
                                </tr>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569;'>Pages Checked:</td>
                                    <td style='padding: 6px 0;'>{$state['processed']} / {$state['total']}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569;'>Total Errors:</td>
                                    <td style='padding: 6px 0;'>
                                        <span style='background-color: " . ($total_errors > 0 ? '#fef2f2' : '#f0fdf4') . "; color: " . ($total_errors > 0 ? '#dc2626' : '#16a34a') . "; padding: 2px 10px; border-radius: 12px; font-weight: 700; font-size: 14px;'>{$total_errors}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569;'>Duration:</td>
                                    <td style='padding: 6px 0;'>{$duration}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569;'>Overview:</td>
                                    <td style='padding: 6px 0; font-size: 13px;'>
                                        <span style='color: #16a34a;'>" . count($clean_pages) . " clean</span> &middot;
                                        <span style='color: #dc2626;'>" . count($pages_with_errors) . " with errors</span> &middot;
                                        <span style='color: #6b7280;'>" . count($failed_pages) . " failed</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        " . (!empty($error_pages_html) ? "<h2 style='font-size: 16px; color: #1e293b; margin: 24px 0 12px 0; font-weight: 600;'>Pages with Errors</h2>{$error_pages_html}" : '') . "

                        {$failed_html}
                        {$clean_html}

                    </div>

                    <div style='background-color: #f8fafc; padding: 16px 32px; border-top: 1px solid #e2e8f0;'>
                        <p style='margin: 0; font-size: 11px; color: #94a3b8; text-align: center;'>
                            Full site grammar check powered by ScraperAPI + OpenAI
                        </p>
                    </div>

                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Build the stylized HTML email message.
     */
    protected function build_email_message(string $url, string $slug, array $errors): string {
        $error_count = count($errors);
        $english_variant = esc_html($this->get_english_variant());
        $reserved_words = $this->get_reserved_words();
        $reserved_note = !empty($reserved_words) ? esc_html(implode(', ', $reserved_words)) : 'None configured';

        $error_rows = '';
        foreach ($errors as $i => $error) {
            $num = $i + 1;
            $word = esc_html($error['word'] ?? '');
            $context = esc_html($error['context'] ?? '');
            $suggestion = esc_html($error['suggestion'] ?? '');
            $explanation = esc_html($error['explanation'] ?? '');

            $error_rows .= "
                <tr style='border-bottom: 1px solid #e5e7eb;'>
                    <td style='padding: 12px 16px; vertical-align: top; color: #6b7280; font-size: 13px;'>{$num}</td>
                    <td style='padding: 12px 16px; vertical-align: top;'>
                        <span style='background-color: #fef2f2; color: #dc2626; padding: 2px 6px; border-radius: 3px; font-weight: 600;'>{$word}</span>
                    </td>
                    <td style='padding: 12px 16px; vertical-align: top; color: #6b7280; font-size: 13px; font-style: italic;'>...{$context}...</td>
                    <td style='padding: 12px 16px; vertical-align: top;'>
                        <span style='background-color: #f0fdf4; color: #16a34a; padding: 2px 6px; border-radius: 3px; font-weight: 600;'>{$suggestion}</span>
                    </td>
                    <td style='padding: 12px 16px; vertical-align: top; color: #6b7280; font-size: 13px;'>{$explanation}</td>
                </tr>";
        }

        return "
        <html>
        <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; line-height: 1.6; color: #1f2937; margin: 0; padding: 0; background-color: #f9fafb;'>
            <div style='max-width: 800px; margin: 0 auto; padding: 32px 16px;'>

                <div style='background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;'>

                    <div style='background-color: #1e293b; padding: 24px 32px;'>
                        <h1 style='margin: 0; color: #ffffff; font-size: 20px; font-weight: 600;'>Grammar Check Report</h1>
                    </div>

                    <div style='padding: 32px;'>

                        <div style='background-color: #f8fafc; border-radius: 6px; padding: 20px 24px; margin-bottom: 24px;'>
                            <table style='width: 100%; border-collapse: collapse;'>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569; width: 140px;'>Page Slug:</td>
                                    <td style='padding: 6px 0; font-family: monospace; font-size: 14px; color: #1e293b;'>{$slug}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569;'>URL:</td>
                                    <td style='padding: 6px 0;'><a href='{$url}' style='color: #2563eb; text-decoration: none;'>{$url}</a></td>
                                </tr>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569;'>English Variant:</td>
                                    <td style='padding: 6px 0; color: #1e293b;'>{$english_variant}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 6px 0; font-weight: 600; color: #475569;'>Errors Found:</td>
                                    <td style='padding: 6px 0;'>
                                        <span style='background-color: #fef2f2; color: #dc2626; padding: 2px 10px; border-radius: 12px; font-weight: 700; font-size: 14px;'>{$error_count}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <h2 style='font-size: 16px; color: #1e293b; margin: 24px 0 12px 0; font-weight: 600;'>Errors</h2>

                        <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                            <thead>
                                <tr style='background-color: #f1f5f9; border-bottom: 2px solid #e2e8f0;'>
                                    <th style='padding: 10px 16px; text-align: left; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase;'>#</th>
                                    <th style='padding: 10px 16px; text-align: left; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase;'>Word</th>
                                    <th style='padding: 10px 16px; text-align: left; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase;'>Context</th>
                                    <th style='padding: 10px 16px; text-align: left; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase;'>Suggestion</th>
                                    <th style='padding: 10px 16px; text-align: left; color: #64748b; font-weight: 600; font-size: 12px; text-transform: uppercase;'>Explanation</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$error_rows}
                            </tbody>
                        </table>

                        <div style='margin-top: 24px; padding: 16px 20px; background-color: #f8fafc; border-radius: 6px; border-left: 3px solid #94a3b8;'>
                            <p style='margin: 0; font-size: 12px; color: #64748b;'>
                                <strong>Reserved words excluded from checks:</strong> {$reserved_note}
                            </p>
                        </div>

                    </div>

                    <div style='background-color: #f8fafc; padding: 16px 32px; border-top: 1px solid #e2e8f0;'>
                        <p style='margin: 0; font-size: 11px; color: #94a3b8; text-align: center;'>
                            Automated grammar check powered by ScraperAPI + OpenAI
                        </p>
                    </div>

                </div>
            </div>
        </body>
        </html>";
    }
}
