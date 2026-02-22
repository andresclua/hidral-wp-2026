<?php
/**
 * Extracts the video ID from a YouTube or Vimeo URL and returns the corresponding iframe embed HTML.
 *
 * @param string $url The URL of the YouTube or Vimeo video.
 * @param array $attributes Optional associative array of iframe attributes (e.g. width, height, class).
 * @return string|false The iframe HTML string if matched, otherwise false.
 *
 * @example
 * echo url_to_embed_iframe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
 */
function url_to_embed($url, $attributes = []) {
    $youtubePattern = '/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([^\s?&]+)/';
    $vimeoPattern = '/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(\d+)/';

    $defaultAttrs = [
        'width' => '100%',
        'height' => '600',
        'frameborder' => '0',
        'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
        'allowfullscreen' => 'allowfullscreen'
    ];

    if ($url) {
        if (preg_match($youtubePattern, $url, $matches)) {
            $embedUrl = 'https://www.youtube.com/embed/' . $matches[1];
        } elseif (preg_match($vimeoPattern, $url, $matches)) {
            $embedUrl = 'https://player.vimeo.com/video/' . $matches[1];
        } else {
            return false;
        }

        
        return esc_url($embedUrl);
    }

    return false;
}