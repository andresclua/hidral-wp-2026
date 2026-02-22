<?php get_header() ?>


<?php 
if ( have_posts() ) { while ( have_posts() ) { the_post(); ?>
		
        <a href="<?php echo site_url('/'); ?>"> home </a>

        <h3><?php the_title(); ?></h3>
        
        <?php the_content(); ?>
<?php } // end while
} // end if
?>
<?php get_footer() ?>