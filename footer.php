</div>
</main>
<?php get_template_part('/components/footer/footer-a') ?>

<?php $bodyBottomScripts = get_field('body_bottom_scripts', 'option'); ?>
<?php echo $bodyBottomScripts; ?>

<script type="text/boostify" src="https://www.googletagmanager.com/gtag/js?id=UA-118692533-1"></script>
<script type="text/boostify">
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-118692533-1');
</script>

<?php wp_footer(); ?>

</body>

</html>