<?php
return array(
    'title'  => 'Social Media',
    'fields' => array_merge(
        ACF_Builder::url(array('name' => 'facebook_url', 'label' => 'Facebook URL', 'width' => '50')),
        ACF_Builder::url(array('name' => 'instagram_url', 'label' => 'Instagram URL', 'width' => '50')),
        ACF_Builder::url(array('name' => 'twitter_url', 'label' => 'Twitter / X URL', 'width' => '50')),
        ACF_Builder::url(array('name' => 'linkedin_url', 'label' => 'LinkedIn URL', 'width' => '50')),
        ACF_Builder::url(array('name' => 'youtube_url', 'label' => 'YouTube URL', 'width' => '50')),
    ),
);
