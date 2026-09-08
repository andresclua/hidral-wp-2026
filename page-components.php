<?php
/*
Template Name: Components
*/

// Only accessible to logged-in admins
if (!current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}

$img = 'https://picsum.photos/seed/comp/800/450';
?>
<?php get_header(); ?>

<style>
    .components-page {
        padding: 60px 0 100px;
    }
    .components-page__header {
        margin-bottom: 60px;
    }
    .components-page__header h1 {
        font-size: 42px;
        font-weight: 700;
        margin: 0 0 8px;
    }
    .components-page__header p {
        font-size: 16px;
        color: #666;
        margin: 0;
    }
    .components-page__category {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #999;
        margin: 60px 0 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e0e0e0;
    }
    .components-page__section {
        margin-bottom: 48px;
    }
    .components-page__title {
        font-size: 22px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #1a1a1a;
    }
    .components-page__preview {
        background-color: #f5f5f5;
        padding: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 24px;
        flex-wrap: wrap;
    }
    .components-page__preview--column {
        flex-direction: column;
        align-items: flex-start;
    }
    .components-page__preview--full {
        display: block;
        padding: 0;
        overflow: hidden;
    }
    .components-page__preview--grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }
    .components-page__variant-label {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #666;
        margin-bottom: 8px;
    }
    /* Sidenav skin (c--sidenav-a) moved to the global build: framework/components/sidenav/_c--sidenav-a.scss */
    /* Override component positioning for preview */
    .components-page .c--hero-a {
        position: relative;
    }
    .components-page .c--preloader-a {
        position: relative;
        height: 200px;
        max-width: 100%;
    }
    .components-page .c--footer-a {
        position: relative;
    }
</style>

<div class="components-page">
    <div class="f--container">

        <div class="components-page__header">
            <h1>Component Library</h1>
            <p>All available components rendered with the theme styles.</p>
        </div>

        <!-- ======================== -->
        <!-- CONTENT                  -->
        <!-- ======================== -->
        <h2 class="components-page__category">Content</h2>

        <!-- C--CONTENT-A -->
        <div class="components-page__section">
            <h3 class="components-page__title">c--content-a</h3>
            <div class="components-page__preview components-page__preview--column">
                <div class="c--content-a">
                    <?= the_content() ?>
                </div>
                <div class="c--content-a">
                    <!-- wp:heading -->
                    <h2 class="wp-block-heading">This is H2 Lorem ipsum dolor sit amet consectetur adipiscing elit.</h2>
                    <!-- /wp:heading -->

                    <!-- wp:acf/highlighted {"name":"acf/highlighted","data":{"highlighted":"This is Highlighted  text. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere.","_highlighted":"field_643771213adb8607dc8826089dcece0d"},"mode":"edit"} /-->

                    <!-- wp:paragraph -->
                    <p>Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":3} -->
                    <h3 class="wp-block-heading">H3 Ut hendrerit semper vel class aptent taciti sociosqu.</h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:list -->
                    <ul class="wp-block-list"><!-- wp:list-item -->
                    <li>Tempus leo eu aenean sed diam urna tempor.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Pulvinar vivamus fringilla lacus nec metus bibendum egestas.<!-- wp:list -->
                    <ul class="wp-block-list"><!-- wp:list-item -->
                    <li>Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Ad litora torquent per conubia nostra inceptos himenaeos.<!-- wp:list -->
                    <ul class="wp-block-list"><!-- wp:list-item -->
                    <li>Quisque faucibus ex sapien vitae pellentesque sem placerat. Ut hendrerit semper vel class aptent taciti sociosqu.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>In id cursus mi pretium tellus duis convallis.</li>
                    <!-- /wp:list-item --></ul>
                    <!-- /wp:list --></li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Iaculis massa nisl malesuada lacinia integer nunc posuere.</li>
                    <!-- /wp:list-item --></ul>
                    <!-- /wp:list --></li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis.</li>
                    <!-- /wp:list-item --></ul>
                    <!-- /wp:list -->

                    <!-- wp:heading {"level":4} -->
                    <h4 class="wp-block-heading">H4 Lorem ipsum dolor sit amet consectetur adipiscing elit.</h4>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph -->
                    <p>Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:list {"ordered":true} -->
                    <ol class="wp-block-list"><!-- wp:list-item -->
                    <li>Tempus leo eu aenean sed diam urna tempor.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Pulvinar vivamus fringilla lacus nec metus bibendum egestas.<!-- wp:list {"ordered":true} -->
                    <ol class="wp-block-list"><!-- wp:list-item -->
                    <li>Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Ad litora torquent per conubia nostra inceptos himenaeos.<!-- wp:list {"ordered":true} -->
                    <ol class="wp-block-list"><!-- wp:list-item -->
                    <li>Quisque faucibus ex sapien vitae pellentesque sem placerat. Ut hendrerit semper vel class aptent taciti sociosqu.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>In id cursus mi pretium tellus duis convallis.</li>
                    <!-- /wp:list-item --></ol>
                    <!-- /wp:list --></li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Iaculis massa nisl malesuada lacinia integer nunc posuere.</li>
                    <!-- /wp:list-item --></ol>
                    <!-- /wp:list --></li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis.</li>
                    <!-- /wp:list-item --></ol>
                    <!-- /wp:list -->

                    <!-- wp:paragraph -->
                    <p>Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:list {"ordered":true} -->
                    <ol class="wp-block-list"><!-- wp:list-item -->
                    <li>Tempus leo eu aenean sed diam urna tempor.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Pulvinar vivamus fringilla lacus nec metus bibendum egestas.<!-- wp:list -->
                    <ul class="wp-block-list"><!-- wp:list-item -->
                    <li>Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Ad litora torquent per conubia nostra inceptos himenaeos.<!-- wp:list {"ordered":true} -->
                    <ol class="wp-block-list"><!-- wp:list-item -->
                    <li>Quisque faucibus ex sapien vitae pellentesque sem placerat. Ut hendrerit semper vel class aptent taciti sociosqu.</li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>In id cursus mi pretium tellus duis convallis.</li>
                    <!-- /wp:list-item --></ol>
                    <!-- /wp:list --></li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Iaculis massa nisl malesuada lacinia integer nunc posuere.</li>
                    <!-- /wp:list-item --></ul>
                    <!-- /wp:list --></li>
                    <!-- /wp:list-item -->

                    <!-- wp:list-item -->
                    <li>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis.</li>
                    <!-- /wp:list-item --></ol>
                    <!-- /wp:list -->

                    <!-- wp:paragraph -->
                    <p>Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":5} -->
                    <h5 class="wp-block-heading">This is H5 Ad litora torquent per conubia nostra inceptos himenaeos.</h5>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":6} -->
                    <h6 class="wp-block-heading">H6 Ut hendrerit semper vel class aptent taciti sociosqu.</h6>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:image {"sizeSlug":"large"} -->
                    <figure class="wp-block-image size-large"><img src="http://placeholder.terrahq.com/img-16by9.webp" alt=""/></figure>
                    <!-- /wp:image -->

                    <!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:image {"lightbox":{"enabled":true},"sizeSlug":"large","linkDestination":"none"} -->
                    <figure class="wp-block-image size-large"><img src="http://placeholder.terrahq.com/img-16by9.webp" alt=""/></figure>
                    <!-- /wp:image -->

                    <!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:image {"lightbox":{"enabled":true},"sizeSlug":"large","linkDestination":"none"} -->
                    <figure class="wp-block-image size-large"><img src="http://placeholder.terrahq.com/img-16by9.webp" alt=""/><figcaption class="wp-element-caption">this is a caption</figcaption></figure>
                    <!-- /wp:image -->

                    <!-- wp:paragraph -->
                    <p>Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
                    <figure class="wp-block-image size-large"><img src="http://placeholder.terrahq.com/img-16by9.webp" alt=""/><figcaption class="wp-element-caption">this is a caption</figcaption></figure>
                    <!-- /wp:image -->

                    <!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:quote -->
                    <blockquote class="wp-block-quote"><!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.</p>
                    <!-- /wp:paragraph --><cite>Citation lorem ipsum</cite></blockquote>
                    <!-- /wp:quote -->

                    <!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:quote -->
                    <blockquote class="wp-block-quote"><!-- wp:paragraph -->
                    <p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Quisque faucibus ex sapien vitae pellentesque sem placerat. In id cursus mi pretium tellus duis convallis. Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas.</p>
                    <!-- /wp:paragraph --></blockquote>
                    <!-- /wp:quote -->

                    <!-- wp:paragraph -->
                    <p>Tempus leo eu aenean sed diam urna tempor. Pulvinar vivamus fringilla lacus nec metus bibendum egestas. Iaculis massa nisl malesuada lacinia integer nunc posuere. Ut hendrerit semper vel class aptent taciti sociosqu. Ad litora torquent per conubia nostra inceptos himenaeos.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:acf/footnote {"name":"acf/footnote","data":{"footnote":"This is a footnote lorem ipsum dolor sit amet","_footnote":"field_8b2e0eb5cb4e337d4783a4c77963fa79"},"mode":"edit"} /-->
                </div>
            </div>
        </div>

        <!-- ======================== -->
        <!-- FOUNDATION               -->
        <!-- ======================== -->
        <h2 class="components-page__category">Foundation</h2>

        <!-- GRID -->
        <div class="components-page__section">
            <h3 class="components-page__title">f--container / f--row / f--col</h3>
            <div class="components-page__preview components-page__preview--column" style="padding:0;">
                <div class="f--container" style="width:100%;">
                    <div class="f--row">
                        <div class="f--col-3"><div style="background:#1E4687;color:#fff;padding:16px;text-align:center;border-radius:4px;">col-3</div></div>
                        <div class="f--col-3"><div style="background:#1E4687;color:#fff;padding:16px;text-align:center;border-radius:4px;">col-3</div></div>
                        <div class="f--col-3"><div style="background:#1E4687;color:#fff;padding:16px;text-align:center;border-radius:4px;">col-3</div></div>
                        <div class="f--col-3"><div style="background:#1E4687;color:#fff;padding:16px;text-align:center;border-radius:4px;">col-3</div></div>
                    </div>
                    <div class="f--row" style="margin-top:12px;">
                        <div class="f--col-6"><div style="background:#ff7d2c;color:#fff;padding:16px;text-align:center;border-radius:4px;">col-6</div></div>
                        <div class="f--col-6"><div style="background:#ff7d2c;color:#fff;padding:16px;text-align:center;border-radius:4px;">col-6</div></div>
                    </div>
                    <div class="f--row" style="margin-top:12px;">
                        <div class="f--col-4"><div style="background:#f8d773;color:#1a1a1a;padding:16px;text-align:center;border-radius:4px;">col-4</div></div>
                        <div class="f--col-8"><div style="background:#f8d773;color:#1a1a1a;padding:16px;text-align:center;border-radius:4px;">col-8</div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLORS -->
        <div class="components-page__section">
            <h3 class="components-page__title">Colors</h3>
            <div class="components-page__preview">
                <div style="display:flex;gap:16px;flex-wrap:wrap;">
                    <div style="text-align:center;">
                        <div class="f--background-a" style="width:80px;height:80px;border-radius:8px;border:1px solid #ddd;"></div>
                        <p style="font-size:12px;margin:8px 0 0;font-family:monospace;">$color-a<br>#1E4687</p>
                    </div>
                    <div style="text-align:center;">
                        <div class="f--background-b" style="width:80px;height:80px;border-radius:8px;border:1px solid #ddd;"></div>
                        <p style="font-size:12px;margin:8px 0 0;font-family:monospace;">$color-b<br>#F7F2EB</p>
                    </div>
                    <div style="text-align:center;">
                        <div style="width:80px;height:80px;border-radius:8px;background:#ff7d2c;border:1px solid #ddd;"></div>
                        <p style="font-size:12px;margin:8px 0 0;font-family:monospace;">$color-c<br>#ff7d2c</p>
                    </div>
                    <div style="text-align:center;">
                        <div style="width:80px;height:80px;border-radius:8px;background:#f8d773;border:1px solid #ddd;"></div>
                        <p style="font-size:12px;margin:8px 0 0;font-family:monospace;">$color-d<br>#f8d773</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TYPOGRAPHY -->
        <div class="components-page__section">
            <h3 class="components-page__title">Typography</h3>
            <div class="components-page__preview components-page__preview--column">
                <div>
                    <p style="font-size:11px;color:#999;font-family:monospace;margin:0 0 4px;">f--font-a ($type-a: Nacelle)</p>
                    <p class="f--font-a" style="font-size:32px;margin:0;">The quick brown fox jumps over the lazy dog</p>
                </div>
                <div style="margin-top:24px;">
                    <p style="font-size:11px;color:#999;font-family:monospace;margin:0 0 4px;">f--font-b ($type-b: Libre Caslon Condensed)</p>
                    <p class="f--font-b" style="font-size:32px;margin:0;">The quick brown fox jumps over the lazy dog</p>
                </div>
            </div>
        </div>

        <?php
            // Shared placeholder sources reused across the core-media / core-btn demos.
            $flexImg    = 'https://placeholder.terrahq.com/img-16by9.webp';
            $flexLottie = 'https://placeholder.terrahq.com/lotties/terraform-1.json';

            // label => url. Without the ACF module the provider type is irrelevant —
            // core-media / core-btn just take the `url` and the Video handler resolves it.
            $flexVideos = [
                'youtube'      => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'vimeo'        => 'https://vimeo.com/76979871',
                'wistia'       => 'https://home.wistia.com/medias/e4a27b971d',
                'mp4 (inline)' => 'https://placeholder.terrahq.com/30-sec-video.mp4',
            ];
        ?>

        <!-- ======================== -->
        <!-- CORE MEDIA               -->
        <!-- ======================== -->
        <h2 class="components-page__category">Core Media (components/media/core-media.php)</h2>

        <p style="margin:0 0 32px;font-size:14px;color:#666;">
            Each demo builds a <code>$media</code> array and includes <code>components/media/core-media.php</code>
            directly (no flexible module). <code>type</code> selects image / video / lottie; the rest of the keys
            mirror what the component reads.
        </p>

        <!-- CORE MEDIA: image -->
        <div class="components-page__section">
            <h3 class="components-page__title">core-media.php — image (<code>type = image</code>)</h3>
            <p style="margin:0 0 16px;font-size:14px;opacity:.7;">Pass <code>image</code> (URL string or ACF array) and an optional <code>alt</code>. By default images lazy-load via Blazy (<code>g--lazy-01</code> starts at <code>opacity:0</code> until revealed); this preview passes <code>isLazy = false</code> so it renders eagerly and is always visible.</p>
            <div class="components-page__preview">
                <?php
                    $media = [
                        'type'   => 'image',
                        'image'  => ['url' => $flexImg, 'alt' => 'Placeholder image'],
                        'isLazy' => false, // eager so the preview is visible without scrolling (lazy reveals on scroll via Blazy)
                        'sizes'  => 'medium',
                    ];
                    include(locate_template('components/media/core-media.php', false, false));
                ?>
            </div>
        </div>

        <!-- CORE MEDIA: video, click to play (poster) -->
        <div class="components-page__section">
            <h3 class="components-page__title">core-media.php — video, click to play (<code>type = video</code> + <code>posterImage</code>)</h3>
            <p style="margin:0 0 16px;font-size:14px;opacity:.7;">With a <code>posterImage</code> and <code>autoplay = false</code> the poster shows and the play button mounts the player on click. youtube / vimeo / wistia render an iframe; the mp4 renders an inline HTML5 <code>&lt;video&gt;</code>. The Video handler resolves the provider from the <code>url</code>.</p>
            <div class="components-page__preview components-page__preview--grid">
                <?php foreach ($flexVideos as $label => $url) : ?>
                    <div>
                        <p class="components-page__variant-label"><?= esc_html($label) ?></p>
                        <?php
                            $media = [
                                'type'        => 'video',
                                'url'         => $url,
                                'posterImage' => ['url' => $flexImg],
                                'autoplay'    => false,
                                'isLazy'      => false, // eager poster so the preview is always visible
                                'sizes'       => 'medium',
                            ];
                            include(locate_template('components/media/core-media.php', false, false));
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CORE MEDIA: video, muted background autoplay -->
        <div class="components-page__section">
            <h3 class="components-page__title">core-media.php — video, muted background autoplay (<code>autoplay = true</code>)</h3>
            <p style="margin:0 0 16px;font-size:14px;opacity:.7;">With <code>autoplay = true</code> the player mounts immediately and plays <strong>muted</strong> in a loop (browsers only allow autoplay when muted) — no poster, no click.</p>
            <div class="components-page__preview">
                <?php
                    $media = [
                        'type'     => 'video',
                        'url'      => $flexVideos['mp4 (inline)'],
                        'autoplay' => true, // → data-video-autoplay="true" (muted, looping background)
                    ];
                    include(locate_template('components/media/core-media.php', false, false));
                ?>
            </div>
        </div>

        <!-- CORE MEDIA: lottie (loop / no-loop / click) -->
        <div class="components-page__section">
            <h3 class="components-page__title">core-media.php — lottie (<code>type = lottie</code>)</h3>
            <p style="margin:0 0 16px;font-size:14px;opacity:.7;">All three are <code>autoplay = true</code>, so each plays as soon as it <strong>loads</strong>. The <code>trigger</code> only controls <strong>when it loads</strong>, never when it plays: default <code>scroll</code>, <code>observer</code> (loads when it scrolls into view), or <code>click</code> (loads on click). Once loaded, <code>loop true</code> → continuous, <code>loop false</code> → plays once.</p>
            <div class="components-page__preview components-page__preview--grid">
                <?php
                $lottie_media_demos = [
                    'loop TRUE (autoplay, continuous loop)'                 => ['loop' => true],
                    'loop TRUE (observer)'                                  => ['loop' => true, 'trigger' => 'observer'],
                    'loop TRUE (click to load)'                             => ['loop' => true, 'trigger' => 'click'],
                ];
                foreach ($lottie_media_demos as $label => $opts) :
                ?>
                    <div>
                        <p class="components-page__variant-label"><?= esc_html($label) ?></p>
                        <?php
                            $media = [
                                'type'     => 'lottie',
                                'url'      => $flexLottie,
                                'autoplay' => true,
                                'loop'     => $opts['loop'],
                                'trigger'  => $opts['trigger'] ?? null,
                            ];
                            include(locate_template('components/media/core-media.php', false, false));
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ======================== -->
        <!-- CORE BUTTON              -->
        <!-- ======================== -->
        <h2 class="components-page__category">Core Button (render_wp_button())</h2>

        <p style="margin:0 0 32px;font-size:14px;color:#666;">
            Each demo calls <code>render_wp_button()</code> with a config array (no flexible module, no ACF).
            The tag is resolved from the config — <code>url</code> → <code>&lt;a&gt;</code>,
            <code>isButton</code> → <code>&lt;button&gt;</code>, <code>isModal</code> → always <code>&lt;button&gt;</code>, otherwise <code>&lt;div&gt;</code>.
            Buttons backed by ACF pass the group instead: <code>render_wp_button(['button' =&gt; $acf, 'class' =&gt; '…'])</code>.
        </p>

        <!-- CORE BTN: link (same window / new window) -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — link (<code>url</code> → <code>&lt;a&gt;</code>)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">A <code>url</code> makes it an anchor. Add <code>target = true</code> for a new window (it adds <code>target</code>/<code>rel</code>/<code>aria-label</code>).</p>
            <div class="components-page__preview">
                aa
                <?php
                    render_wp_button(['title' => 'Visit Terra (same window)', 'url' => 'https://terrahq.com']);

                    render_wp_button(['title' => 'Visit Terra (new window)', 'url' => 'https://terrahq.com', 'target' => '_blank']);
                ?>
            </div>
        </div>

        <!-- CORE BTN: plain container (renders a <div>) -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — plain container (renders a <code>&lt;div&gt;</code>)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">No <code>url</code>, no <code>isButton</code>, not a modal → the tag resolves to a <code>&lt;div&gt;</code>. Useful when you want the same content/class/attributes pipeline without a clickable element. <code>content</code> is pre-escaped inner HTML; falls back to <code>esc_html(title)</code>.</p>
            <div class="components-page__preview">
                <?php
                    render_wp_button([
                        'title'   => 'Plain container',
                        'class'   => 'c--content-a',
                        'content' => '<p>Rendered through <code>render_wp_button()</code> as a <strong>&lt;div&gt;</strong> — no anchor, no button, no modal trigger.</p>',
                    ]);
                ?>
            </div>
        </div>

        <!-- CORE BTN: anchor-to (scroll) button -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — anchor-to button (<code>isButton</code> + <code>js--anchor-to</code>)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">A <code>&lt;button&gt;</code> with class <code>js--anchor-to</code> and <code>data-scroll-to</code>; the AnchorTo handler smooth-scrolls to the element whose <code>anchor-id</code> matches. This one points at the sidenav at the bottom (<code>module-41</code>).</p>
            <div class="components-page__preview">
                <?php
                    render_wp_button([
                        'title'      => 'Scroll to the sidenav module ↓',
                        'isButton'   => true,
                        'class'      => 'js--anchor-to',
                        'attributes' => ['data-scroll-to' => 'module-41', 'tf-data-distance' => 40],
                    ]);
                ?>
            </div>
        </div>

        <!-- CORE BTN: standard modal (clone a target element) -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — standard modal (<code>modalVariation = ''</code>, clones a target)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">The base modal with no variation → emits a <code>js--modal-button</code>. Pass the source via <code>attributes</code> as <code>data-modal-target="#id"</code>; on open, <code>standardConfig</code> clones that element/<code>&lt;template&gt;</code> into <code>.js--modal-content</code> and clears it on close.</p>
            <div class="components-page__preview">
                <?php
                    render_wp_button([
                        'title'      => 'Open standard modal',
                        'isModal'    => true,
                        'attributes' => ['data-modal-target' => '#demo-standard-modal-tpl'],
                    ]);
                ?>
                <!-- Cloned into the modal on open (a <template> is inert until cloned). -->
                <template id="demo-standard-modal-tpl">
                    <h2>Standard modal content</h2>
                    <p>This block lived in a <code>&lt;template&gt;</code> and was cloned into the shared modal shell when the button was clicked.</p>
                </template>
            </div>
        </div>

        <!-- CORE BTN: media modal — image -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — media modal: image (<code>modalVariation = media</code>, <code>type = image</code>)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">Emits a <code>js--image-modal-button</code> carrying <code>data-media-*</code>; the modal injects an <code>&lt;img&gt;</code> on open.</p>
            <div class="components-page__preview">
                <?php
                    render_wp_button([
                        'title'          => 'View image',
                        'isModal'        => true,
                        'modalVariation' => 'media',
                        'media'          => ['type' => 'image', 'image' => ['url' => $flexImg, 'alt' => 'Placeholder image']],
                    ]);
                ?>
            </div>
        </div>

        <!-- CORE BTN: media modal — video, click to play (poster) -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — media modal: video, click to play (poster set)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">Emits a <code>js--video-modal-button</code>. With a <code>posterImage</code> and <code>autoplay = false</code> it's click-to-play inside the modal. One button per provider.</p>
            <div class="components-page__preview">
                <?php foreach ($flexVideos as $label => $url) {
                    render_wp_button([
                        'title'          => 'Watch video — ' . $label,
                        'isModal'        => true,
                        'modalVariation' => 'media',
                        'media'          => ['type' => 'video', 'url' => $url, 'posterImage' => ['url' => $flexImg], 'autoplay' => false],
                    ]);
                } ?>
            </div>
        </div>

        <!-- CORE BTN: media modal — video, autoplay with sound (no poster) -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — media modal: video, autoplay with sound (no poster)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">No <code>posterImage</code> → it derives <code>data-video-forceplay="true"</code>, so the video auto-plays <strong>with sound</strong> when the modal opens (the opening click satisfies the browser's gesture requirement).</p>
            <div class="components-page__preview">
                <?php foreach ($flexVideos as $label => $url) {
                    render_wp_button([
                        'title'          => 'Autoplay (sound) — ' . $label,
                        'isModal'        => true,
                        'modalVariation' => 'media',
                        'media'          => ['type' => 'video', 'url' => $url, 'autoplay' => false], // no poster → forceplay
                    ]);
                } ?>
            </div>
        </div>

        <!-- CORE BTN: media modal — lottie (loop / no-loop) -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — media modal: lottie (<code>type = lottie</code>)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">Emits a <code>js--lottie-modal-button</code>. <code>autoplay</code> and <code>loop</code> both default to <code>true</code>, so they only need passing to switch one off. Pass a <code>name</code> as well: without one it is generated with <code>uniqid()</code> and changes on every render, so nothing can target the instance — and two lotties on a page need distinct ones.</p>
            <div class="components-page__preview">
                <?php foreach (['autoplay + loop' => true, 'autoplay once (no loop)' => false] as $label => $loop) {
                    render_wp_button([
                        'title'          => 'View animation — ' . $label,
                        'isModal'        => true,
                        'modalVariation' => 'media',
                        'media'          => [
                            'type' => 'lottie',
                            'url'  => $flexLottie,
                            'name' => 'demo-lottie-' . ($loop ? 'loop' : 'once'),
                            'loop' => $loop,
                        ],
                    ]);
                } ?>
            </div>
        </div>

        <!-- CORE BTN: htmlContent modal -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — htmlContent modal (<code>modalVariation = htmlContent</code>)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">Emits a <code>js--htmlContent-modal-button</code> carrying the markup in <code>data-modal-content</code> (sanitized with <code>wp_kses_post</code>); the modal injects it inline on open. Pass raw <code>html</code> (below) or <code>template</code> + <code>id</code> to render a partial server-side.</p>
            <div class="components-page__preview">
                <?php
                    // A PHP value injected into the markup — escape it with esc_html at the
                    // interpolation point (the whole string is also wp_kses_post'd downstream).
                    $heading = 'Inline HTML content';

                    render_wp_button([
                        'title'          => 'Read the details',
                        'isModal'        => true,
                        'modalVariation' => 'htmlContent',
                        'htmlContent'    => [
                            'html' => '<h2>' . esc_html($heading) . '</h2>'
                                . '<p>This markup travelled in <code>data-modal-content</code> and was injected into the shared modal shell on open — no AJAX, no template lookup.</p>'
                                . '<ul><li>Sanitized with <code>wp_kses_post</code></li><li>Rendered client-side by <code>htmlContentConfig</code></li></ul>',
                        ],
                    ]);
                ?>
            </div>
        </div>

        <!-- CORE BTN: hubspot form modal -->
        <div class="components-page__section">
            <h3 class="components-page__title">render_wp_button() — HubSpot form modal (<code>modalVariation = hubspot</code>)</h3>
            <p style="margin:0 0 12px;font-size:14px;opacity:.7;">Emits a <code>js--hubspot-modal-button</code> carrying <code>data-hs-portal-id</code> / <code>data-hs-form-id</code>; on open it lazy-loads the HubSpot embed script and creates the form. Swap in your own IDs to render a real form.</p>
            <div class="components-page__preview">
                <?php
                    render_wp_button([
                        'title'          => 'Open contact form',
                        'isModal'        => true,
                        'modalVariation' => 'hubspot',
                        'hubspot'        => ['portalId' => '3120747', 'formId' => 'ba5e0f28-49dd-44a2-87a9-000f8cc6487d'],
                    ]);
                ?>
            </div>
        </div>

        <!-- ======================== -->
        <!-- FLEXIBLE MODULE: sidenav -->
        <!-- ======================== -->
        <h2 class="components-page__category">Flexible Module — sidenav</h2>

        <div class="components-page__section">
            <h3 class="components-page__title">flexible/module/sidenav.php — scrollspy sidenav</h3>
            <p style="margin:0 0 16px;font-size:14px;opacity:.7;">Builds <code>core-sidenav.php</code> + content sections from the <code>sidenav_items</code> repeater, namespacing each target by <code>$keyIndexModule</code>. Included directly (not via the module switch). It renders <code>anchor-id="module-41"</code>, the target of the anchor-to button above.</p>
            <?php
                $keyIndexModule = 41; // → anchor-id="module-41"
                $module = [
                    'acf_fc_layout' => 'sidenav',
                    'sidenav_items' => [
                        ['title' => 'Your inbox stays yours',         'content' => '<h2>Your inbox stays yours</h2><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>'],
                        ['title' => 'An AI scheduling assistant',     'content' => '<h2>An AI scheduling assistant</h2><p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>'],
                        ['title' => 'Clear communication every time', 'content' => '<h2>Clear communication every time</h2><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>'],
                        ['title' => 'Delegate and move on',           'content' => '<h2>Delegate and move on</h2><p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>'],
                        ['title' => 'Grounded in reality',            'content' => '<h2>Grounded in reality</h2><p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>'],
                    ],
                ];
                include(locate_template('flexible/module/sidenav.php', false, false));
            ?>
        </div>

    </div>
</div>

<?php get_footer(); ?>
