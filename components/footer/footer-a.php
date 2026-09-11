<footer class="c--footer-a">

    <div class="c--footer-a__bd">
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-12">
                    <?php include(locate_template('components/brand/brand-a.php', false, false)); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="c--footer-a__ft">
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-6">
                    <?php $copyrightText = get_field('copyright_text', 'option') ?: (date('Y') . ' ' . get_bloginfo('name') . '. Todos los derechos reservados.'); ?>
                    <p class="f--font-e">&copy; <?= esc_html($copyrightText) ?></p>
                    <?php unset($copyrightText); ?>
                </div>
                <div class="f--col-6">
                    <?php include(locate_template('components/social/social-a.php', false, false)); ?>
                </div>
            </div>
        </div>
    </div>

</footer>
