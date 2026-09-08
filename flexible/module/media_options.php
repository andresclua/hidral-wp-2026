<?php
$mediaUrl = '';

if ($module['media_type'] === 'lottie' && $module['lottie_source']) {
    $mediaUrl = $module['lottie_source'] ?? '';
} elseif ($module['media_type'] === 'video') {
    if ($module['video_type'] === 'embed') {
        $mediaUrl = $module['embed_video'] ?? '';
    } elseif ($module['video_type'] === 'gallery' && $module['video_source']) {
        $mediaUrl = $module['video_source'] ?? '';
    }
}

$media = [
    'type'    => $module['media_type'] ?? 'image',
    'image'   => $module['media_type'] === 'image' ? ($module['image'] ?? []) : null,
    'posterImage' => $module['media_type'] === 'video' ? ($module['video_poster'] ?? []) : null,
    'url'     => $module['media_type'] !== 'image' ? $mediaUrl : null,
    'autoplay' => $module['media_type'] === 'video' ? !empty($module['is_autoplay']) : ($module['media_type'] === 'lottie' ? true : false),
    'loop'     => $module['media_type'] === 'lottie' ? !empty($module['lottie_loop']) : false,
    'trigger'  => $module['media_type'] === 'lottie' ? ($module['lottie_trigger'] ?? null) : null,
    'distance' => $module['media_type'] === 'lottie' ? ($module['lottie_distance'] ?? null) : null,
    'sizes'   => 'medium',
    'isLazy'  => $module['isLazy'] ?? true, // default lazy (Blazy); callers can opt out for above-the-fold
    'classes' => ['mediaWrapper' => '', 'mediaAsset' => ''],
];
?>

<section class="" anchor-id="module-<?= $keyIndexModule ?>">
    <?php include(locate_template('components/media/core-media.php', false, false)); ?>
</section>
