<div class="c--header-a js--header">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-12">
                <div class="c--header-a__wrapper js--header-wrapper">

                    <?php include(locate_template('components/brand/brand-a.php', false, false)); ?>
                    <button class="c--burger-a js--burger">
                        <span class="c--burger-a__item"></span>
                        <span class="c--burger-a__item"></span>
                        <span class="c--burger-a__item"></span>
                    </button>
                    <ul class="c--nav-a js--navbar">
                        <?php
                        $headerNav = get_field('header_nav', 'option') ?: [];
                        foreach ($headerNav as $navItem) :
                            $navLabel = $navItem['label'] ?? '';
                            $navPage  = $navItem['page'] ?? null;
                            $navUrl   = $navPage ? get_permalink($navPage) : '';
                            if (!$navLabel || !$navUrl) {
                                continue;
                            }
                        ?>
                            <li class="c--nav-a__list-item">
                                <a class="c--nav-a__list-item__link" href="<?= esc_url($navUrl) ?>"><?= esc_html($navLabel) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="c--header-a__item-right">
                        <a href="https://wa.me/598XXXXXXXX" class="c--btn-a" target="_blank" rel="noopener">WhatsApp</a>
                        <a href="tel:+598XXXXXXXX" class="c--btn-a c--btn-a--second">Llamar</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>