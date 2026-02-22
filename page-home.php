<?php
/*
Template Name: Home
*/
?>
<?php get_header() ?>

<div class="c--hero-a">
    <div class="c--hero-a__title">WP Starter Kit</div>
</div>

<div class="f--container">
    <div class="f--row">
        <div class="f--col-4">
            <h2 class="f--font-c js--reveal-stack">Hoy es juevers y quiero dormir
            </h2>
        </div>
    </div>
</div>

<div class="f--container">
    <div class="f--row">
        <div class="f--col-4">
            <h2 class="f--font-c js--reveal-stack">Todary in monday and it is a sunny dary
            </h2>
        </div>
    </div>
</div>


<div class="f--container">
    <div class="f--row">
        <div class="f--col-4">
            <h2 class="f--font-c js--reveal-stack">Move element
                <div>
                    TITLE 1
                </div>
            </h2>
        </div>
    </div>
</div>
             

<div class="f--container">
    <div class="f--row" id="load-more">
        <div class="f--col-6">

        </div>
    </div>
    <div class="f--row">
        <div class="f--col-12 u--text-center">

            <button class="js--loadmore" 
                data-load-more-action="loadmore_media-and-press"
                data-load-more-per-page="2"
                data-load-more-container="load-more">
                Load More
            </button> 

        </div>
    </div>
</div>
 
<?php
    new Mail_To((object) array(
        'email'   => 'nerea@terrahq.com',
        'subject' => 'Email de prueba',
        'message' => '<h1>Hola!</h1><p>Este es un email de prueba.</p>',
    ));
?>




<div class="js--lottie-element"
     data-path="https://placeholder.terrahq.com/lotties/terraform-1.json"
     data-animType="svg"
     data-loop="true"
     data-autoplay="true"
     style="width:400px;height:600px;" 
     data-name="graphic"></div>


<?php
render_wp_image([
  'image'           => 'https://placeholder.terrahq.com/img-16by9.webp',
    'sizes'           => 'large',
    'class'           => 'placeholder-image',
    'isLazy'          => false,
    'showAspectRatio' => true,
    'addFigcaption'   => false,
]);
?>

<?php get_footer() ?>
