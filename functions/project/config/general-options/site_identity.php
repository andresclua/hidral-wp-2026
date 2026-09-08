<?php
return array(
    'title'  => 'Site Identity',
    'fields' => array_merge(
        ACF_Builder::title(array('name' => 'site_title', 'label' => 'Site Title')),
        ACF_Builder::text(array('name' => 'meta_description', 'label' => 'Meta Description', 'rows' => 3)),
        ACF_Builder::image(array(
            'name'         => 'favicon',
            'label'        => 'Favicon',
            'width'        => '50',
            'instructions' => 'Square icon displayed in browser tabs and bookmarks. Recommended: 512×512px, WebP or PNG.',
            'mime_types'   => 'webp,png,ico,svg',
        )),
        ACF_Builder::image(array(
            'name'         => 'og_image',
            'label'        => 'OG Image',
            'width'        => '50',
            'instructions' => 'Default image shown when the site is shared on social media (Facebook, LinkedIn, X). Recommended: 1200×630px, WebP, JPG or PNG.',
            'mime_types'   => 'webp,jpg,jpeg,png',
        )),
        ACF_Builder::image(array(
            'name'         => 'login_logo',
            'label'        => 'Login Logo',
            'instructions' => 'Logo displayed on the wp-admin login screen. Recommended: horizontal/rectangular, WebP or PNG with transparent background.',
            'mime_types'   => 'webp,png,svg',
        )),
    ),
);
