
<?php get_header() ?>



<?php $heros = get_field('heros');
if ($heros) { 
    foreach ($heros as $keyIndexHero => $hero):
       include(locate_template('flexible/hero/index.php', false, false)); 
    endforeach;
} ?>

<?php $modules = get_field('modules');
if ($modules) { 
    foreach ($modules as $keyIndexModule => $module):
       include(locate_template('flexible/module/index.php', false, false)); 
    endforeach;
} 

?>

<?php get_footer() ?>