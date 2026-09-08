</div>
</main>
<?php get_template_part('/components/footer/footer-a') ?>

<?php include(locate_template('components/modal/modal-a.php', false, false)); ?>

<?php $bodyBottomScripts = get_field('body_bottom_scripts', 'option'); ?>
<?php echo $bodyBottomScripts; ?>

<?php wp_footer(); ?>

</body>

</html>