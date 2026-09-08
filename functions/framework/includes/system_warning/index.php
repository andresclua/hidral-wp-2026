<?php
    // ACF form_head no longer needed — settings UI is now native.


    function show_system_warning_viewers() { 
        // Hardcoded site URL for Google Search Console
        $gsc_site_url = get_site_url();
        $gsc_console_url = 'https://search.google.com/search-console/index?resource_id=' . urlencode($gsc_site_url);
        ?>
        <style>

        /* Module toggles */
        .terra-modules-grid{
            display: grid;

            gap: 12px;
        }
        .terra-module-item{
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            background: #fafafa;
            transition: border-color .2s, background .2s;
        }
        .terra-module-item.is-active{
            border-color: #00a32a;
            background: #f0faf0;
        }
        .terra-module-item.is-inactive{
            border-color: #dcdcde;
            background: #f9f9f9;
        }
        .terra-module-info{
            flex: 1;
            min-width: 0;
        }
        .terra-module-name{
            font-weight: 600;
            font-size: 13px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .terra-module-desc{
            color: #666;
            font-size: 12px;
            margin: 2px 0 0;
        }
        .terra-module-badge{
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .terra-module-badge.active{
            background: #00a32a;
            color: #fff;
        }
        .terra-module-badge.inactive{
            background: #dcdcde;
            color: #666;
        }
        /* Toggle switch */
        .terra-toggle{
            position: relative;
            width: 36px;
            height: 20px;
            flex-shrink: 0;
        }
        .terra-toggle input{
            opacity: 0;
            width: 0;
            height: 0;
        }
        .terra-toggle-slider{
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc;
            border-radius: 20px;
            transition: .25s;
        }
        .terra-toggle-slider:before{
            content: "";
            position: absolute;
            height: 14px;
            width: 14px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .25s;
        }
        .terra-toggle input:checked + .terra-toggle-slider{
            background-color: #00a32a;
        }
        .terra-toggle input:checked + .terra-toggle-slider:before{
            transform: translateX(16px);
        }
        .terra-group-title{
            font-size: 13px;
            font-weight: 600;
            color: #1d2327;
            margin: 16px 0 8px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .terra-group-title:first-child{
            margin-top: 0;
        }
        .terra-modules-notice{
            display: none;
            padding: 10px 14px;
            margin-top: 12px;
            border-radius: 8px;
            font-size: 13px;
        }
        .terra-modules-notice.success{
            background: #f0faf0;
            border: 1px solid #00a32a;
            color: #00a32a;
        }
        .terra-modules-notice.error{
            background: #fef0f0;
            border: 1px solid #d63638;
            color: #d63638;
        }
        </style>
        <?php
            require_once dirname(__DIR__) . '/ui/index.php';
            terra_enqueue_ui('tabs');
        ?>
        <div class="wrap">
            <h1>System</h1>
            <p class="description">Central dashboard for the Terra framework. Manage which modules are active, configure monitoring schedules, and check site health.</p>

            <ul class="c--tabs__nav">
                <li class="c--tabs__nav-item is-active" data-tab="modules">Framework Modules</li>
                <li class="c--tabs__nav-item" data-tab="settings">Settings</li>
                <li class="c--tabs__nav-item" data-tab="gsc">Google Search Console</li>
                <li class="c--tabs__nav-item" data-tab="url-health">URL Health Check</li>
                <?php if (class_exists('Multilang') && !empty(Multilang::languages())) : ?>
                    <li class="c--tabs__nav-item" data-tab="multilang">Multilang</li>
                <?php endif; ?>
            </ul>

            <div class="c--tabs__content">

            <div class="c--tabs__panel is-active" data-panel="modules">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <h3 style="margin: 0;">Framework Modules</h3>
                    <button id="terra-save-modules" class="button button-primary" style="border-radius: 8px; padding: 6px 14px; height: auto;">
                        Save Modules
                    </button>
                </div>
                <p class="description" style="margin: 0 0 16px;">Toggle individual framework features on or off. Disabled modules are not loaded at all, which can improve performance. Changes take effect on the next page load.</p>
                <?php
                    $modules = Module_Manager::get_modules();
                    $groups = Module_Manager::get_groups();
                    $grouped = [];
                    foreach ($modules as $key => $mod) {
                        $grouped[$mod['group']][$key] = $mod;
                    }
                    foreach ($groups as $group_key => $group_data) :
                        if (empty($grouped[$group_key])) continue;
                ?>
                    <p class="terra-group-title"><?php echo esc_html($group_data['label']); ?></p>
                    <p class="terra-group-desc" style="color:#666;font-size:12.5px;margin:0 0 12px;line-height:1.5;"><?php echo esc_html($group_data['description']); ?></p>
                    <div class="terra-modules-grid">
                        <?php foreach ($grouped[$group_key] as $key => $mod) :
                            $active = $mod['active'];
                            $state_class = $active ? 'is-active' : 'is-inactive';
                            // Declared dependency (a plugin) that is not installed:
                            // the toggle still works, the module just stays inert.
                            $unavailable_dependency = !empty($mod['requires']) && empty($mod['available']);
                        ?>
                            <div class="terra-module-item <?php echo $state_class; ?>" data-module="<?php echo esc_attr($key); ?>">
                                <label class="terra-toggle">
                                    <input type="checkbox" name="terra_modules[<?php echo esc_attr($key); ?>]" value="1" <?php checked($active); ?>>
                                    <span class="terra-toggle-slider"></span>
                                </label>
                                <div class="terra-module-info">
                                    <p class="terra-module-name">
                                        <?php echo esc_html($mod['name']); ?>
                                        <span class="terra-module-badge <?php echo $active ? 'active' : 'inactive'; ?>">
                                            <?php echo $active ? 'Active' : 'Inactive'; ?>
                                        </span>
                                        <?php if (!empty($mod['tag'])) : ?>
                                            <span style="display:inline-block;font-size:10px;font-weight:500;padding:1px 6px;border-radius:4px;background:#f0f0f1;color:#50575e;border:1px solid #dcdcde;letter-spacing:.2px;"><?php echo esc_html($mod['tag']); ?></span>
                                        <?php endif; ?>
                                        <?php if ($unavailable_dependency) : ?>
                                            <span style="display:inline-block;font-size:10px;font-weight:600;padding:1px 6px;border-radius:4px;background:#fcf0f1;color:#8a2424;border:1px solid #f0c8c8;letter-spacing:.2px;">Requires <?php echo esc_html($mod['requires']); ?></span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="terra-module-desc"><?php echo esc_html($mod['description']); ?></p>
                                    <?php if ($unavailable_dependency) : ?>
                                        <p class="terra-module-desc" style="color:#8a2424;">
                                            <?php echo esc_html($mod['requires']); ?> is not active on this site, so this module does nothing until the plugin is installed. Leaving it on is harmless: it starts working on its own once the plugin is there.
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!empty($mod['details'])) : ?>
                                        <details style="margin:6px 0 0;border:none;font-size:12px;">
                                            <summary style="cursor:pointer;color:#2271b1;font-size:11.5px;font-weight:500;list-style:none;user-select:none;">What does this do?</summary>
                                            <div style="margin-top:6px;padding:8px 12px;background:#f6f7f7;border-radius:6px;color:#50575e;font-size:12px;line-height:1.6;">
                                                <?php echo wp_kses_post($mod['details']); ?>
                                            </div>
                                        </details>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <div id="terra-modules-notice" class="terra-modules-notice"></div>

            <script>
            (function() {
                // Toggle visual state on checkbox change
                document.querySelectorAll('.terra-module-item input[type="checkbox"]').forEach(function(cb) {
                    cb.addEventListener('change', function() {
                        var item = this.closest('.terra-module-item');
                        var badge = item.querySelector('.terra-module-badge');
                        if (this.checked) {
                            item.classList.remove('is-inactive');
                            item.classList.add('is-active');
                            badge.className = 'terra-module-badge active';
                            badge.textContent = 'Active';
                        } else {
                            item.classList.remove('is-active');
                            item.classList.add('is-inactive');
                            badge.className = 'terra-module-badge inactive';
                            badge.textContent = 'Inactive';
                        }
                    });
                });

                // Save modules via AJAX
                document.getElementById('terra-save-modules').addEventListener('click', function() {
                    var btn = this;
                    var notice = document.getElementById('terra-modules-notice');
                    btn.disabled = true;
                    btn.textContent = 'Saving...';

                    var formData = new FormData();
                    formData.append('action', 'terra_save_modules');
                    formData.append('nonce', '<?php echo wp_create_nonce("terra_save_modules"); ?>');

                    document.querySelectorAll('.terra-module-item input[type="checkbox"]:checked').forEach(function(cb) {
                        var key = cb.closest('.terra-module-item').getAttribute('data-module');
                        formData.append('modules[' + key + ']', '1');
                    });

                    fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(response) {
                        btn.disabled = false;
                        btn.textContent = 'Save Modules';
                        notice.style.display = 'block';
                        if (response.success) {
                            notice.className = 'terra-modules-notice success';
                            notice.textContent = response.data.message;
                        } else {
                            notice.className = 'terra-modules-notice error';
                            notice.textContent = response.data.message || 'Error saving modules.';
                        }
                        setTimeout(function() { notice.style.display = 'none'; }, 4000);
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        btn.textContent = 'Save Modules';
                        notice.style.display = 'block';
                        notice.className = 'terra-modules-notice error';
                        notice.textContent = 'Request failed: ' + err.message;
                    });
                });
            })();
            </script>
            </div><!-- /c--tabs__panel modules -->

            <div class="c--tabs__panel" data-panel="settings">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                    <h3 style="margin: 0;">Settings</h3>
                    <button id="terra-save-settings" class="button button-primary" style="border-radius: 8px; padding: 6px 14px; height: auto;">
                        Save Settings
                    </button>
                </div>
                <?php
                    $current_interval = get_option('terra_sw_interval', '604800');
                    $interval_labels = array('86400' => 'every day', '604800' => 'every week', '2592000' => 'every month');
                    $interval_text = isset($interval_labels[$current_interval]) ? $interval_labels[$current_interval] : 'periodically';
                    $settings_emails = get_option('terra_sw_emails', []);
                    $settings_emails = array_filter($settings_emails);
                    $emails_text = !empty($settings_emails) ? implode(', ', array_map('esc_html', $settings_emails)) : 'no recipients configured';
                ?>
                <p class="description" style="margin: 0 0 16px;">Automated checks (URL health, Lighthouse, vulnerability scans) run <strong><?php echo esc_html($interval_text); ?></strong> and reports are sent to <strong><?php echo $emails_text; ?></strong>.</p>

                <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                    <div style="flex: 0 0 200px;">
                        <label for="terra-sw-interval" style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">Interval:</label>
                        <select id="terra-sw-interval" style="width: 100%;">
                            <option value="86400" <?php selected($current_interval, '86400'); ?>>Every Day</option>
                            <option value="604800" <?php selected($current_interval, '604800'); ?>>Every Week</option>
                            <option value="2592000" <?php selected($current_interval, '2592000'); ?>>Every Month</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 260px;">
                        <label style="display: block; font-weight: 600; font-size: 13px; margin-bottom: 6px;">Emails:</label>
                        <div id="terra-emails-list">
                            <?php
                                $saved_emails = get_option('terra_sw_emails', []);
                                if (empty($saved_emails) && function_exists('get_field')) {
                                    $acf_emails = get_field('terra_system_warning_emails', 'option');
                                    if ($acf_emails) {
                                        foreach ($acf_emails as $value) {
                                            if (!empty($value['email'])) {
                                                $saved_emails[] = $value['email'];
                                            }
                                        }
                                    }
                                }
                                if (empty($saved_emails)) {
                                    $saved_emails = [''];
                                }
                                foreach ($saved_emails as $email) :
                            ?>
                                <div class="terra-email-row" style="display: flex; gap: 6px; margin-bottom: 6px;">
                                    <input type="email" class="terra-email-input regular-text" value="<?php echo esc_attr($email); ?>" placeholder="email@example.com" style="flex: 1;">
                                    <button type="button" class="button terra-remove-email" title="Remove" style="color: #d63638;">&times;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="terra-add-email" class="button" style="margin-top: 4px;">+ Add Email</button>
                    </div>
                </div>

                <div id="terra-settings-notice" class="terra-modules-notice" style="display: none;"></div>

            <script>
            (function() {
                // Add email row
                document.getElementById('terra-add-email').addEventListener('click', function() {
                    var list = document.getElementById('terra-emails-list');
                    var row = document.createElement('div');
                    row.className = 'terra-email-row';
                    row.style.cssText = 'display: flex; gap: 6px; margin-bottom: 6px;';
                    row.innerHTML = '<input type="email" class="terra-email-input regular-text" value="" placeholder="email@example.com" style="flex: 1;">' +
                                    '<button type="button" class="button terra-remove-email" title="Remove" style="color: #d63638;">&times;</button>';
                    list.appendChild(row);
                });

                // Remove email row (delegated)
                document.getElementById('terra-emails-list').addEventListener('click', function(e) {
                    if (e.target.classList.contains('terra-remove-email')) {
                        var rows = document.querySelectorAll('.terra-email-row');
                        if (rows.length > 1) {
                            e.target.closest('.terra-email-row').remove();
                        } else {
                            e.target.closest('.terra-email-row').querySelector('input').value = '';
                        }
                    }
                });

                // Save settings via AJAX
                document.getElementById('terra-save-settings').addEventListener('click', function() {
                    var btn = this;
                    var notice = document.getElementById('terra-settings-notice');
                    btn.disabled = true;
                    btn.textContent = 'Saving...';

                    var formData = new FormData();
                    formData.append('action', 'terra_save_sw_settings');
                    formData.append('nonce', '<?php echo wp_create_nonce("terra_save_sw_settings"); ?>');
                    formData.append('interval', document.getElementById('terra-sw-interval').value);

                    document.querySelectorAll('.terra-email-input').forEach(function(input) {
                        var val = input.value.trim();
                        if (val) {
                            formData.append('emails[]', val);
                        }
                    });

                    fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(response) {
                        btn.disabled = false;
                        btn.textContent = 'Save Settings';
                        notice.style.display = 'block';
                        if (response.success) {
                            notice.className = 'terra-modules-notice success';
                            notice.textContent = response.data.message;
                        } else {
                            notice.className = 'terra-modules-notice error';
                            notice.textContent = response.data.message || 'Error saving settings.';
                        }
                        setTimeout(function() { notice.style.display = 'none'; }, 4000);
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        btn.textContent = 'Save Settings';
                        notice.style.display = 'block';
                        notice.className = 'terra-modules-notice error';
                        notice.textContent = 'Request failed: ' + err.message;
                    });
                });
            })();
            </script>
            </div><!-- /c--tabs__panel settings -->

            <div class="c--tabs__panel" data-panel="gsc">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h3 style="margin: 0 0 4px 0;">Google Search Console</h3>
                        <p class="description" style="margin: 0;">Google Search Console shows how Google sees this site — indexing status, search queries, crawl errors and sitemaps. This link opens the console for <strong><?php echo esc_html($gsc_site_url); ?></strong>.</p>
                    </div>
                    <a href="<?php echo esc_url($gsc_console_url); ?>" target="_blank" class="button button-primary" style="border-radius: 8px; padding: 6px 14px; height: auto; text-decoration: none;">
                        Open Google Search Console →
                    </a>
                </div>
            </div><!-- /c--tabs__panel gsc -->

            <div class="c--tabs__panel" data-panel="url-health">
                <?php show_urlhealthchequer_dashboard_table(); ?>
            </div><!-- /c--tabs__panel url-health -->

            <?php
            // Multilang tab — rendered by the wp-multilang repo. The partial
            // lives there (functions/framework/wp-multilang/assets/system-warning-tab.php)
            // so all multilang UI stays co-located with the rest of that
            // subsystem; we just hand over the locals it needs and include.
            if (class_exists('Multilang')
                && defined('WP_MULTILANG_PATH')
                && !empty(Multilang::languages())
            ) :
                $ml_enabled   = (bool) get_option('terra_multilang_enabled', 0);
                $ml_languages = Multilang::languages();
                $ml_count     = count($ml_languages);
                $ml_partial   = WP_MULTILANG_PATH . '/admin/system-warning-tab.php';
                if (file_exists($ml_partial)) {
                    include $ml_partial;
                }
            endif;
            ?>

            </div><!-- /c--tabs__content -->

        </div>
    <?php }


    // Register System Warning as submenu under Terra (priority 7, after General Options at 6)
    add_action('admin_menu', function () {
        add_submenu_page(
            'terra_dashboard',
            'System',
            'System',
            'manage_options',
            'terra_dashboard',
            'show_system_warning_viewers'
        );
    }, 7);

    // ACF field group 'group_emails' removed — interval & emails now stored in wp_options
    // via the native settings UI and ajax_save_sw_settings handler.

?>
