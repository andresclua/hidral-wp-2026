<?php
    function get_recipient_emails() {
        // Primary: native wp_options
        $emails = get_option('terra_sw_emails', []);
        if (!empty($emails)) {
            return array_filter($emails);
        }

        // Fallback: ACF options (legacy)
        if (function_exists('get_field')) {
            $acf_emails = get_field('terra_system_warning_emails', 'option');
            if ($acf_emails) {
                $emailList = [];
                foreach ($acf_emails as $value) {
                    if (!empty($value['email'])) {
                        $emailList[] = $value['email'];
                    }
                }
                return $emailList;
            }
        }

        return [];
    }
?>