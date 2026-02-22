<?php
    add_action('admin_init', function () {
        if (!is_admin()) return;
        if (!isset($_GET['page']) || $_GET['page'] !== 'system_warning') return;

        if (function_exists('acf_form_head')) {
            acf_form_head();
        }
    });


    function show_system_warning_viewers() { 
        // Hardcoded site URL for Google Search Console
        $gsc_site_url = get_site_url();
        $gsc_console_url = 'https://search.google.com/search-console/index?resource_id=' . urlencode($gsc_site_url);
        ?>
        <style>
            .terra-card{
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 12px;
            padding: 20px;
            margin-top: 16px;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
            }

            /* Ajustes visuales para ACF dentro del card */
            .terra-card .acf-fields{
            margin-top: 6px;
            }
            .terra-card .acf-form-submit{
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #eee;
            }

            /* Botón un poco más "WP moderno" (sin romper estilos) */
            .terra-card .acf-form-submit .button-primary{
            border-radius: 8px;
            padding: 6px 14px;
            height: auto;
        }
        </style>
        <div class="wrap">
            <h1>System Warning</h1>
            <div class="terra-card">  
                <?php
                    if (function_exists('acf_form')) {
                        acf_form([
                            'post_id'       => 'options',
                            'field_groups'  => ['group_emails'],
                            'submit_value'  => 'Save Emails',
                            'updated_message' => 'Emails updated',
                        ]);
                    } else {
                        echo '<p>ACF no está activo.</p>';
                    }
                ?>
            </div>

            <div class="terra-card" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h3 style="margin: 0 0 4px 0;">Google Search Console</h3>
                    <p style="margin: 0; color: #666; font-size: 13px;">Access the Search Console for <?php echo esc_html($gsc_site_url); ?></p>
                </div>
                <a href="<?php echo esc_url($gsc_console_url); ?>" target="_blank" class="button button-primary" style="border-radius: 8px; padding: 6px 14px; height: auto; text-decoration: none;">
                    Open Google Search Console →
                </a>
            </div>

            <div class="terra-card">  
                <?php
                    show_lighthouse_dashboard_table()
                ?>
            </div>

             <div class="terra-card">
                <?php
                    show_urlhealthchequer_dashboard_table()
                ?>
            </div>

            <div class="terra-card">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <h3 style="margin: 0 0 4px 0;">Grammar Check</h3>
                        <p style="margin: 0; color: #666; font-size: 13px;">Check all published pages for spelling and grammar issues via Spling</p>
                    </div>
                    <button id="terra-grammar-check-all" class="button button-primary" style="border-radius: 8px; padding: 6px 14px; height: auto;">
                        Check All Pages
                    </button>
                </div>

                <div id="terra-grammar-loading" style="display: none; text-align: center; padding: 20px;">
                    <span class="spinner is-active" style="float: none; margin: 0 8px 0 0;"></span>
                    <span id="terra-grammar-status">Checking pages... This may take a while.</span>
                </div>

                <div id="terra-grammar-results" style="display: none;">
                    <table class="widefat striped" style="margin-top: 12px;">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>URL</th>
                                <th style="text-align: center;">Issues</th>
                                <th>Report</th>
                            </tr>
                        </thead>
                        <tbody id="terra-grammar-tbody"></tbody>
                    </table>
                </div>

                <div id="terra-grammar-error" style="display: none; color: #d63638; padding: 12px 0;"></div>
            </div>

            <script>
            (function() {
                var btn = document.getElementById('terra-grammar-check-all');
                var loading = document.getElementById('terra-grammar-loading');
                var status = document.getElementById('terra-grammar-status');
                var results = document.getElementById('terra-grammar-results');
                var tbody = document.getElementById('terra-grammar-tbody');
                var errorDiv = document.getElementById('terra-grammar-error');
                var nonce = '<?php echo wp_create_nonce('terra_grammar_check_all'); ?>';

                btn.addEventListener('click', function() {
                    btn.disabled = true;
                    btn.textContent = 'Checking...';
                    loading.style.display = 'block';
                    results.style.display = 'none';
                    errorDiv.style.display = 'none';
                    tbody.innerHTML = '';

                    var formData = new FormData();
                    formData.append('action', 'terra_grammar_check_all');
                    formData.append('nonce', nonce);

                    fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: formData
                    })
                    .then(function(response) { return response.json(); })
                    .then(function(response) {
                        loading.style.display = 'none';
                        btn.disabled = false;
                        btn.textContent = 'Check All Pages';

                        if (!response.success) {
                            errorDiv.textContent = response.data && response.data.message ? response.data.message : 'An error occurred.';
                            errorDiv.style.display = 'block';
                            return;
                        }

                        var data = response.data;
                        if (!data.length) {
                            errorDiv.textContent = 'No pages found to check.';
                            errorDiv.style.display = 'block';
                            return;
                        }

                        data.forEach(function(item) {
                            var tr = document.createElement('tr');
                            var issueColor = item.issues > 0 ? '#d63638' : '#00a32a';
                            var reportLink = item.report_url ? '<a href="' + item.report_url + '" target="_blank">View Report</a>' : '—';

                            tr.innerHTML =
                                '<td>' + escHtml(item.title) + '</td>' +
                                '<td>' + escHtml(item.type) + '</td>' +
                                '<td><a href="' + escHtml(item.url) + '" target="_blank" style="word-break: break-all;">' + escHtml(item.url) + '</a></td>' +
                                '<td style="text-align: center; font-weight: bold; color: ' + issueColor + ';">' + item.issues + '</td>' +
                                '<td>' + reportLink + '</td>';

                            tbody.appendChild(tr);
                        });

                        results.style.display = 'block';
                    })
                    .catch(function(err) {
                        loading.style.display = 'none';
                        btn.disabled = false;
                        btn.textContent = 'Check All Pages';
                        errorDiv.textContent = 'Request failed: ' + err.message;
                        errorDiv.style.display = 'block';
                    });
                });

                function escHtml(str) {
                    var div = document.createElement('div');
                    div.appendChild(document.createTextNode(str || ''));
                    return div.innerHTML;
                }
            })();
            </script>
        </div>
    <?php }


    add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) return;

    acf_add_local_field_group([
        'key' => 'group_emails',
        'title' => 'Terra Lighthouse Settings',
        'fields' => [
            [
            'key' => 'field_6863d3a6dd58445',
            'label' => 'Interval:',
            'name' => 'terra_system_warning_interval',
            'type' => 'select',
            'choices' => [
                '86400'   => 'Every Day',
                '604800'  => 'Every Week',
                '2592000' => 'Every Month',
            ],
            'default_value' => '604800',
            'wrapper' => [
                'width' => '50',
            ],
            ],
            [
            'key' => 'field_emails_repeater',
            'label' => 'Emails',
            'name' => 'terra_system_warning_emails',
            'type' => 'repeater',
            'sub_fields' => [
                [
                'key' => 'field_single_email',
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                ],
            ],
            'min' => 1,
            'layout' => 'table',
            'button_label' => 'Add Email',
            'wrapper' => [
                'width' => '50',
            ],
            ],
        ],
        'location' => [
            [
            [
                'param' => 'options_page',
                'operator' => '==',
                'value' => 'system_warning',
            ],
            ],
        ],
        ]);


        
    });

?>
