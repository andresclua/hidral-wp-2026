<?php
return [
  [
    'block_name' => 'customctablock', // no puede llevar _ o espacios o cosas raras
    'template_path' => 'custom-cta-block', // no puede llevar _ o espacios o cosas raras
    'singular_name' => 'Custom CTA Block',
    'keywords' => 'Custom, CTA Block',
    'fields' => 
      [
        [
          'name' => 'title',
          'type' => 'textarea',
          'label' => 'CTA Title',
          'conditional_logic' => 0
        ],
        [
          'name' => 'button',
          'type' => 'link',
          'label' => 'CTA Button',
          'conditional_logic' => 0
        ],
      ],
  ],
  [
    'block_name' => 'customicontitle',
    'template_path' => 'custom-icon-title',
    'singular_name' => 'Block Icon + title',
    'keywords' => 'Custom, icontitle',
    'fields' => 
      [
        [
          'name' => 'image_option',
          'type' => 'select',
          'label' => 'Image Option',
          'conditional_logic' => 0,
          'choices' => array(
              'problem' => 'Problem',
              'solution' => 'Solution',
              'results' => 'Results',
              'custom' => 'Custom',
          ),
          'default_value' => 'custom',
        ],
        [
          'name' => 'icon_title',
          'type' => 'text',
          'label' => 'Title',
          'conditional_logic' => 0,
        ],
        
      ],
  ],
];