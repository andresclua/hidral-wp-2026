<?php
$title           = get_field('contact_title', 'option') ?: '';
$description     = get_field('contact_description', 'option') ?: '';
$email           = get_field('contact_email', 'option') ?: '';
$phoneGuardia    = get_field('contact_phone_guardia', 'option') ?: '';
$phoneComercial  = get_field('contact_phone_comercial', 'option') ?: '';
$address         = get_field('contact_address', 'option') ?: '';
$formShortcode   = get_field('contact_form_shortcode', 'option') ?: '';
$spacingContentModule = get_spacing($module['section_spacing'] ?? '');

if (!$title && !$formShortcode) {
    return;
}
?>
<section class="<?= $spacingContentModule ?>">
    <div class="c--contact-a" anchor-id="module-<?= $keyIndexModule ?>">
        <div class="f--container">
            <div class="f--row">
                <div class="f--col-6">
                    <?php if ($title) : ?>
                        <h2 class="c--contact-a__title"><?= esc_html($title) ?></h2>
                    <?php endif; ?>
                    <?php if ($description) : ?>
                        <p class="c--contact-a__content"><?= esc_html($description) ?></p>
                    <?php endif; ?>

                    <?php if ($email || $phoneGuardia || $phoneComercial || $address) : ?>
                        <ul class="c--contact-a__list-group">
                            <?php if ($email) : ?>
                                <li class="c--contact-a__list-group__item">
                                    <svg class="c--contact-a__list-group__item__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="13" y="16" width="22" height="16" rx="2"></rect><path d="M35 18l-11 8-11-8"></path>
                                    </svg>
                                    <a class="c--link-a" href="mailto:<?= esc_attr($email) ?>"><?= esc_html($email) ?></a>
                                </li>
                            <?php endif; ?>
                            <?php if ($phoneGuardia) : ?>
                                <li class="c--contact-a__list-group__item">
                                    <svg class="c--contact-a__list-group__item__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M18 14c-1.8 0-3.2 1.5-2.8 3.3 1.4 6.6 6.9 12.1 13.5 13.5 1.8.4 3.3-1 3.3-2.8v-2.3l-4.6-1.5-2 2c-2.9-1.5-5.1-3.7-6.6-6.6l2-2-1.5-4.6z"></path>
                                    </svg>
                                    <span>Guardia: <a class="c--link-a" href="tel:<?= esc_attr($phoneGuardia) ?>"><?= esc_html($phoneGuardia) ?></a></span>
                                </li>
                            <?php endif; ?>
                            <?php if ($phoneComercial) : ?>
                                <li class="c--contact-a__list-group__item">
                                    <svg class="c--contact-a__list-group__item__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M18 14c-1.8 0-3.2 1.5-2.8 3.3 1.4 6.6 6.9 12.1 13.5 13.5 1.8.4 3.3-1 3.3-2.8v-2.3l-4.6-1.5-2 2c-2.9-1.5-5.1-3.7-6.6-6.6l2-2-1.5-4.6z"></path>
                                    </svg>
                                    <span>Comercial: <a class="c--link-a" href="tel:<?= esc_attr($phoneComercial) ?>"><?= esc_html($phoneComercial) ?></a></span>
                                </li>
                            <?php endif; ?>
                            <?php if ($address) : ?>
                                <li class="c--contact-a__list-group__item">
                                    <svg class="c--contact-a__list-group__item__icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M32 21c0 6-8 13-8 13s-8-7-8-13a8 8 0 0 1 16 0z"></path><circle cx="24" cy="21" r="3"></circle>
                                    </svg>
                                    <span><?= esc_html($address) ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="f--col-6">
                    <?php if ($formShortcode) : ?>
                        <div class="c--contact-a__wrapper">
                            <?= do_shortcode($formShortcode) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($title);
unset($description);
unset($email);
unset($phoneGuardia);
unset($phoneComercial);
unset($address);
unset($formShortcode);
unset($spacingContentModule);
?>
