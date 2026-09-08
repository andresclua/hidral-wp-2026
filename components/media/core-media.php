<?php
$mediaType = $media['type'] ?? 'image';
$_clsMediaWrapper = $media['classes']['mediaWrapper'] ?? '';
$_clsMediaAsset   = $media['classes']['mediaAsset']   ?? '';
$_isLazy          = $media['isLazy'] ?? true;
$_showAspectRatio = $media['showAspectRatio'] ?? false;
$_fetchPriority   = $media['fetchPriority'] ?? 'auto';

if ($mediaType === 'video' && !empty($media['url'])) :
    $videoUrl      = $media['url'];
    $videoPoster   = $media['posterImage'] ?? null;
    $videoAutoplay = !empty($media['autoplay']);
    $_clsPosterImg = $_clsMediaAsset;
    ?>
    <div
        class="<?= esc_attr(trim('c--video-a js--boostify-player ' . $_clsMediaWrapper)) ?>"
        data-video-src="<?= esc_url($videoUrl) ?>"
        data-video-poster="<?= esc_url($videoPoster['url'] ?? '') ?>"
        data-video-autoplay="<?= $videoAutoplay ? 'true' : 'false' ?>"
        data-video-class="c--video-a__media"
    >
        <button type="button" class="c--video-a__btn" aria-label="Play video">
            <?php if (!$videoAutoplay) : ?>
                <div class="c--video-a__btn__artwork">
                    <span class="c--video-a__btn__artwork__logo"></span>
                </div>
            <?php endif; ?>
            <?php
            render_wp_image([
                'image'           => $videoPoster,
                'sizes'           => $media['sizes'] ?? 'large',
                'class'           => 'c--video-a__btn__media ' . $_clsPosterImg,
                'isLazy'          => $_isLazy,
                'showAspectRatio' => $_showAspectRatio,
                'fetchPriority'   => $_fetchPriority,
            ]);
            ?>
        </button>
    </div>

<?php elseif ($mediaType === 'lottie' && !empty($media['url'])) :
    $lottiePath     = $media['url'];
    $lottieName     = wp_unique_id('media-core-lottie-');
    $lottieRenderer = $media['renderer'] ?? 'svg';
    $lottieAutoplay = $media['autoplay'] ?? true;
    $lottieLoop     = !empty($media['loop']);
    $lottieDistance = $media['distance'] ?? null;
    $lottieTrigger  = in_array($media['trigger'] ?? '', ['click', 'observer'], true) ? $media['trigger'] : null;
    ?>

    <?php if (!empty($_clsMediaWrapper)) : ?>
    <div class="<?= esc_attr($_clsMediaWrapper) ?>">
    <?php endif; ?>

    <div
        class="<?= esc_attr(trim('js--lottie-element ' . $_clsMediaAsset)) ?>"
        data-name="<?= esc_attr($lottieName) ?>"
        data-path="<?= esc_url($lottiePath) ?>"
        data-animType="<?= esc_attr($lottieRenderer) ?>"
        data-autoplay="<?= $lottieAutoplay ? 'true' : 'false' ?>"
        data-loop="<?= $lottieLoop ? 'true' : 'false' ?>"
        <?php if ($lottieDistance !== null && $lottieDistance !== '') : ?>data-distance="<?= esc_attr($lottieDistance) ?>"<?php endif; ?>
        <?php if ($lottieTrigger) : ?>data-trigger="<?= esc_attr($lottieTrigger) ?>"<?php endif; ?>
        style="aspect-ratio: 1 / 1;"
    ></div>

    <?php if (!empty($_clsMediaWrapper)) : ?>
    </div>
    <?php endif; ?>

<?php elseif (!empty($media['image']) || !empty($media['url'])) :
    $_imageForAlt = !empty($media['image']) ? $media['image'] : $media['url'];

    // alt: lo explícito ($media['alt']) manda; si no, se intenta tomar el
    // "Alternative Text" de la imagen en Media:
    //  - array de ACF → ya trae 'alt' (lo pone ACF desde Media), sin re-consultar la DB.
    //  - URL string   → se resuelve el attachment por URL y se lee su alt meta.
    // Si no se puede resolver queda null, y render_wp_image usa su propio fallback.
    $_mediaAlt = $media['alt'] ?? null;
    if ($_mediaAlt === null || $_mediaAlt === '') {
        if (is_array($_imageForAlt)) {
            $_mediaAlt = $_imageForAlt['alt'] ?? null;
        } elseif (is_string($_imageForAlt) && $_imageForAlt !== '') {
            $_attachId = attachment_url_to_postid($_imageForAlt);
            $_mediaAlt = $_attachId ? (get_post_meta($_attachId, '_wp_attachment_image_alt', true) ?: null) : null;
        }
    }
?>
    <?php if (!empty($_clsMediaWrapper)) : ?>
    <div class="<?= esc_attr($_clsMediaWrapper) ?>">
    <?php endif; ?>
        <?php
        render_wp_image([
            'image'  => $_imageForAlt,
            'alt'    => $_mediaAlt,
            'sizes'  => $media['sizes'] ?? 'large',
            'class'  => $_clsMediaAsset,
            'isLazy' => $_isLazy,
            'showAspectRatio' => $_showAspectRatio,
            'fetchPriority'   => $_fetchPriority,
        ]);
        ?>
    <?php if (!empty($_clsMediaWrapper)) : ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php unset($mediaType, $videoUrl, $videoPoster, $videoAutoplay, $_clsMediaWrapper, $_clsMediaAsset, $_isLazy, $_showAspectRatio, $_fetchPriority, $_clsPosterImg, $lottiePath, $lottieName, $lottieRenderer, $lottieAutoplay, $lottieLoop, $lottieDistance, $lottieTrigger, $_imageForAlt, $_mediaAlt, $_attachId); ?>
