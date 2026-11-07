<?php
return array(
    'label'  => 'Mission & Vision',
    'fields' => array_merge(
        ACF_Builder::title(array('name' => 'vision_label', 'label' => 'Vision Label', 'default_value' => 'Visión')),
        ACF_Builder::title(array('name' => 'vision_title', 'label' => 'Vision Title', 'default_value' => 'Un mundo donde el agua nunca deja de fluir.')),
        ACF_Builder::text(array('name' => 'vision_text', 'label' => 'Vision Text', 'default_value' => 'Esa es la meta. Cada bomba que sale de nuestro taller lleva esa promesa soldada en el acero.')),
        ACF_Builder::title(array('name' => 'mission_label', 'label' => 'Mission Label', 'default_value' => 'Misión')),
        ACF_Builder::title(array('name' => 'mission_title', 'label' => 'Mission Title', 'default_value' => 'Llevar agua a quien la necesita, sin interrupciones.')),
        ACF_Builder::text(array('name' => 'mission_text', 'label' => 'Mission Text', 'default_value' => 'Diseñamos y fabricamos bombas que trabajan sin descanso, con la precisión de quien sabe que detrás de cada una hay una familia, un campo y una ciudad.'))
    ),
);
