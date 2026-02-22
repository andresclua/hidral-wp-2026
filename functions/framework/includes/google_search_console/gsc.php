<?php

    function terra_gsc_admin_page() {
        echo '<div class="wrap">';
        echo '<h1>Google Search Console</h1>';
        
        $siteUrl = get_site_url();
        $apiUrl = 'https://gsc-api.netlify.app/.netlify/functions/gsc-sitemaps?siteUrl=' . urlencode($siteUrl);
        
        $response = wp_remote_get($apiUrl, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            echo '<div class="notice notice-error"><p>Error fetching data: ' . esc_html($response->get_error_message()) . '</p></div>';
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if ($data) {
                // Display Site URL
                echo '<div class="gsc-sitemaps-wrapper" style="margin-top: 20px;">';
                echo '<h2>Site: ' . esc_html($data['siteUrl']) . '</h2>';
                
                // Display Active Sitemap
                if (isset($data['activeSitemap'])) {
                    echo '<div class="gsc-active-sitemap" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; border-radius: 4px;">';
                    echo '<h3 style="margin-top: 0;">Active Sitemap</h3>';
                    echo '<table class="widefat striped">';
                    echo '<tbody>';
                    echo '<tr><th style="width: 200px;">Path</th><td><a href="' . esc_url($data['activeSitemap']['path']) . '" target="_blank">' . esc_html($data['activeSitemap']['path']) . '</a></td></tr>';
                    echo '<tr><th>Last Submitted</th><td>' . esc_html(date('Y-m-d H:i:s', strtotime($data['activeSitemap']['lastSubmitted']))) . '</td></tr>';
                    echo '<tr><th>Last Downloaded</th><td>' . esc_html(date('Y-m-d H:i:s', strtotime($data['activeSitemap']['lastDownloaded']))) . '</td></tr>';
                    echo '<tr><th>Warnings</th><td>' . esc_html($data['activeSitemap']['warnings']) . '</td></tr>';
                    echo '<tr><th>Errors</th><td>' . esc_html($data['activeSitemap']['errors']) . '</td></tr>';
                    echo '<tr><th>Is Pending</th><td>' . ($data['activeSitemap']['isPending'] ? 'Yes' : 'No') . '</td></tr>';
                    echo '<tr><th>Is Sitemaps Index</th><td>' . ($data['activeSitemap']['isSitemapsIndex'] ? 'Yes' : 'No') . '</td></tr>';
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                }
                
                // Display All Sitemaps
                if (isset($data['sitemaps']) && is_array($data['sitemaps']) && count($data['sitemaps']) > 0) {
                    echo '<div class="gsc-all-sitemaps" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; border-radius: 4px;">';
                    echo '<h3 style="margin-top: 0;">All Sitemaps (' . count($data['sitemaps']) . ')</h3>';
                    echo '<table class="widefat striped">';
                    echo '<thead>';
                    echo '<tr>';
                    echo '<th>Path</th>';
                    echo '<th>Last Submitted</th>';
                    echo '<th>Last Downloaded</th>';
                    echo '<th>Warnings</th>';
                    echo '<th>Errors</th>';
                    echo '<th>Pending</th>';
                    echo '<th>Index</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($data['sitemaps'] as $sitemap) {
                        echo '<tr>';
                        echo '<td><a href="' . esc_url($sitemap['path']) . '" target="_blank">' . esc_html($sitemap['path']) . '</a></td>';
                        echo '<td>' . esc_html(date('Y-m-d H:i:s', strtotime($sitemap['lastSubmitted']))) . '</td>';
                        echo '<td>' . esc_html(date('Y-m-d H:i:s', strtotime($sitemap['lastDownloaded']))) . '</td>';
                        echo '<td>' . esc_html($sitemap['warnings']) . '</td>';
                        echo '<td>' . esc_html($sitemap['errors']) . '</td>';
                        echo '<td>' . ($sitemap['isPending'] ? 'Yes' : 'No') . '</td>';
                        echo '<td>' . ($sitemap['isSitemapsIndex'] ? 'Yes' : 'No') . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                }
                
                echo '</div>';
            } else {
                echo '<div class="notice notice-warning"><p>No data received from API or invalid JSON response.</p></div>';
            }
        }

                // Handle sitemap submission
        if (isset($_POST['submit_sitemap']) && isset($_POST['sitemap_url']) && wp_verify_nonce($_POST['gsc_nonce'], 'gsc_submit_sitemap')) {
            $sitemapUrl = sanitize_url($_POST['sitemap_url']);
            
            if (!empty($sitemapUrl)) {
                $submitApiUrl = 'https://gsc-api.netlify.app/.netlify/functions/gsc-sitemap-submit?siteUrl=' . urlencode($siteUrl) . '&sitemapUrl=' . urlencode($sitemapUrl);
                
                $submitResponse = wp_remote_get($submitApiUrl, array(
                    'timeout' => 30,
                    'headers' => array(
                        'Content-Type' => 'application/json'
                    )
                ));
                
                if (is_wp_error($submitResponse)) {
                    echo '<div class="notice notice-error is-dismissible"><p>Error submitting sitemap: ' . esc_html($submitResponse->get_error_message()) . '</p></div>';
                } else {
                    $submitBody = wp_remote_retrieve_body($submitResponse);
                    $submitData = json_decode($submitBody, true);
                    $statusCode = wp_remote_retrieve_response_code($submitResponse);
                    
                    if ($statusCode >= 200 && $statusCode < 300) {
                        echo '<div class="notice notice-success is-dismissible"><p>Sitemap submitted successfully: ' . esc_html($sitemapUrl) . '</p></div>';
                    } else {
                        $errorMessage = isset($submitData['error']) ? $submitData['error'] : 'Unknown error occurred';
                        echo '<div class="notice notice-error is-dismissible"><p>Error submitting sitemap: ' . esc_html($errorMessage) . '</p></div>';
                    }
                }
            } else {
                echo '<div class="notice notice-warning is-dismissible"><p>Please enter a valid sitemap URL.</p></div>';
            }
        }
        
        // Submit Sitemap Form
        echo '<div class="gsc-submit-sitemap" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ccd0d4; border-radius: 4px;">';
        echo '<h3 style="margin-top: 0;">Submit Sitemap</h3>';
        echo '<form method="post" action="">';
        wp_nonce_field('gsc_submit_sitemap', 'gsc_nonce');
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th scope="row"><label for="site_url">Site URL</label></th>';
        echo '<td><input type="text" id="site_url" value="' . esc_attr($siteUrl) . '" class="regular-text" disabled /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th scope="row"><label for="sitemap_url">Sitemap URL</label></th>';
        echo '<td><input type="url" name="sitemap_url" id="sitemap_url" placeholder="'.get_site_url().'"sitemap.xml" class="regular-text" required /></td>';
        echo '</tr>';
        echo '</table>';
        echo '<p class="submit"><input type="submit" name="submit_sitemap" class="button button-primary" value="Submit Sitemap" /></p>';
        echo '</form>';
        echo '</div>';
        
        echo '</div>';
    }

?>
