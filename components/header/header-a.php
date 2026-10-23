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

                    <?php
                    $headerButtons = array(
                        'c--btn-a'                 => get_field('header_button_primary', 'option'),
                        'c--btn-a c--btn-a--second' => get_field('header_button_secondary', 'option'),
                    );
                    $headerButtons = array_filter($headerButtons, fn($button) => !empty($button['link']['url']));
                    ?>
                    <?php if ($headerButtons) : ?>
                        <div class="c--header-a__item-right">
                            <?php foreach ($headerButtons as $buttonClass => $button) :
                                render_wp_button(array('button' => $button, 'class' => $buttonClass));
                            endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php unset($headerButtons, $buttonClass, $button); ?>

                </div>
            </div>
        </div>
    </div>
</div>