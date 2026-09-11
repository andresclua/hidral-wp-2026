<?php
return array(
    'title'  => 'Contact',
    'fields' => array_merge(
        ACF_Builder::title(array('name' => 'contact_title', 'label' => 'Title')),
        ACF_Builder::text(array('name' => 'contact_description', 'label' => 'Description', 'rows' => 3)),
        ACF_Builder::title(array('name' => 'contact_email', 'label' => 'Email')),
        ACF_Builder::title(array('name' => 'contact_phone_guardia', 'label' => 'Phone (Guardia)')),
        ACF_Builder::title(array('name' => 'contact_phone_comercial', 'label' => 'Phone (Comercial)')),
        ACF_Builder::title(array('name' => 'contact_address', 'label' => 'Address')),
        ACF_Builder::text(array(
            'name'         => 'contact_form_shortcode',
            'label'        => 'Form Shortcode',
            'instructions' => 'Paste the Contact Form 7 shortcode, e.g. [contact-form-7 id="123" title="Contact"].',
            'rows'         => 1,
        )),
    ),
);
