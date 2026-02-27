<?php
/*
Template Name: Modules
*/
?>
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

<section>
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-6">
                <?php include(locate_template('components/card/card-x.php', false, false)); ?>
            </div>
            <div class="f--col-6">
                <?php include(locate_template('components/card/card-y.php', false, false)); ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer() ?>