<?php
return array(
    'label'  => 'Media Options',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::select(array(
            'name'    => 'media_type',
            'label'   => 'Media Type',
            'choices' => array(
                'image'  => 'Image',
                'video'  => 'Video',
                'lottie' => 'Lottie',
            ),
        )),
        ACF_Builder::image(array(
            'name'      => 'image',
            'label'     => 'Image',
            'show_when' => array('media_type', '==', 'image'),
            'width' => '50',
        )),
        ACF_Builder::file(array(
            'name'          => 'lottie_source',
            'label'         => 'Lottie Source',
            'return_format' => 'url',
            'show_when'     => array('media_type', '==', 'lottie'),
            'width'         => '50',
        )),
        ACF_Builder::boolean(array(
            'name'          => 'lottie_loop',
            'label'         => 'Loop?',
            'default_value' => 1,
            'show_when'     => array('media_type', '==', 'lottie'),
            'width'         => '50',
        )),
        ACF_Builder::select(array(
            'name'      => 'video_type',
            'label'     => 'Video Type',
            'choices'   => array(
                'embed'   => 'Embed',
                'gallery' => 'Video from Media Gallery',
            ),
            'show_when' => array('media_type', '==', 'video'),
        )),
        ACF_Builder::text(array(
            'name'         => 'embed_video',
            'label'        => 'Embed Video URL',
            'rows'         => 2,
            'instructions' => 'URL of the video to embed. E.g. https://www.youtube.com/watch?v=jmXm_CvWF14',
            'show_when'    => array('video_type', '==', 'embed'),
        )),
        ACF_Builder::file(array(
            'name'          => 'video_source',
            'label'         => 'Video Source',
            'return_format' => 'url',
            'show_when'     => array('video_type', '==', 'gallery'),
        )),
        ACF_Builder::boolean(array(
            'name'      => 'is_autoplay',
            'label'     => 'Is Autoplay?',
            'show_when' => array('media_type', '==', 'video'),
            'width' => '50',
        )),
        ACF_Builder::image(array(
            'name'      => 'video_poster',
            'label'     => 'Video Poster',
            'show_when' => array(
                array('media_type', '==', 'video'),
                array('is_autoplay', '!=', '1'),
            ),
            'width' => '50',
        )),
    ),
);
