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

                    <?php $headerButtons = get_field('header_buttons', 'option') ?: []; ?>
                    <?php if ($headerButtons) : ?>
                        <div class="c--header-a__item-right">
                            <?php foreach ($headerButtons as $i => $buttonRow) :
                                render_wp_button(array(
                                    'button' => $buttonRow['button'],
                                    'class'  => trim('c--btn-a ' . ($i === 0 ? '' : 'c--btn-a--second')),
                                ));
                            endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php unset($headerButtons); ?>

                </div>
            </div>
        </div>
    </div>
</div>