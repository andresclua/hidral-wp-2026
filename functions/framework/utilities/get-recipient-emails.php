<?php 
    function get_recipient_emails() {
        $emailList = [];
        if ( function_exists('get_field') ) {
            $emails = get_field('terra_system_warning_emails', 'option');
            if ($emails) {
                foreach ($emails as $key => $value) {
                    if(!empty($value['email'])) {
                        $emailList[] = $value['email'];
                    }
                }
            }
        }
        return !empty($emailList) ? $emailList : ['andres@terrahq.com'];
    }
?>