<?php
$title    = $module['title'] ?? '';
$subtitle = $module['subtitle'] ?? '';
$services = $module['services'] ?? [];
$spacingContentModule = get_spacing($module['section_spacing'] ?? '');

if (!$title && !$subtitle && !$services) {
    return;
}
?>
<section class="<?= $spacingContentModule ?>">
    <div class="c--services-list-a" anchor-id="module-<?= $keyIndexModule ?>">
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-12">
                    <?php if ($title) : ?>
                        <h2 class="c--services-list-a__title"><?= esc_html($title) ?></h2>
                    <?php endif; ?>
                    <?php if ($subtitle) : ?>
                        <p class="c--services-list-a__subtitle"><?= esc_html($subtitle) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($services) : ?>
                <div class="f--row">
                    <?php foreach ($services as $service) :
                        $serviceId = is_object($service) ? $service->ID : $service;
                        $card = array(
                            'category'    => get_field('service_category', $serviceId),
                            'title'       => get_the_title($serviceId),
                            'description' => get_field('card_description', $serviceId) ?: get_the_excerpt($serviceId),
                            'link'        => get_permalink($serviceId),
                            'image'       => get_post_thumbnail_id($serviceId),
                        );
                    ?>
                        <div class="f--col-4">
                            <?php include(locate_template('components/card/card-a.php', false, false)); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php
unset($title);
unset($subtitle);
unset($services);
unset($spacingContentModule);
?>
