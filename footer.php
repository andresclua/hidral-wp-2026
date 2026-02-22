</div>
</main>
<?php get_template_part('/components/footer/footer-a') ?>

<?php $bodyBottomScripts = get_field('body_bottom_scripts', 'option'); ?>
<?php echo $bodyBottomScripts; ?>

<?php wp_footer(); ?>

</body>

</html>