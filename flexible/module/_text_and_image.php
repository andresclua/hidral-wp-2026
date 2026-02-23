<?php 
    $spacingContent = get_spacing(($module['section_spacing'])); 
    $title = $module['title'];
    $content = $module['content'];
    $image = $module['image'];
    $order = $module['orden'];

  


?>
<section class="text_and_image <?= $spacingContent ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-5 f--offset-1">
               
                <?php  
                    $textAndMediaArgs = array(
                                    'image' =>  $image,
                                    'sizes' => '100vw',
                                    'class' => '',
                                    'lazyClass' => 'g--lazy-01',
                                    'isLazy' => true,
                                    'showAspectRatio' => true,
                                    'decodingAsync' => true,
                                    'fetchPriority' => false,
                                    'addFigcaption' => true,
                                    'figureClass' => 'media-wrapper'
                    );
                    render_wp_image($textAndMediaArgs)    
                ?>
            </div>
            <div class="f--col-5 f--offset-1">
                <?php if ($title): ?>
                    <h2 class="f--font-c f--mb-2"><?= $title ?></h2>
                <?php endif; ?>
                <?php if ($content): ?>
                    <div class="c--content-a c--content-a--second f--mb-2"><?= $content ?></div>
                <?php endif; ?>
            </div>
            

        </div>
    </div>
</section>
<?php
unset($spacingContent);
unset($title);
unset($content);
?>