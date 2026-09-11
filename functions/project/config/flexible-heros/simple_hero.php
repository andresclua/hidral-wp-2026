<?php
return array(
    'label'  => 'Simple Hero',
    'fields' => array_merge(
        ACF_Builder::spacing(),
        ACF_Builder::pretitle(),
        ACF_Builder::title(),
        ACF_Builder::text(array('name' => 'text', 'label' => 'Text')),
        ACF_Builder::custom_button(array('types' => array('link')))
    ),
);
