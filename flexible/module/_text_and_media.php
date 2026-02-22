
<section class="<?= $spacingCTA = get_spacing(($module['section_spacing'])); ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-6 f--col-tabletl-12">
                <h2 class="f--font-c f--mb-2"><?php echo $module['title']; ?></h2>
            </div>
        </div>
        <div class="f--row">
            <div class="f--col-4 f--col-tabletl-12 f--order-tabletl-2">
                <div class="c--content-a f--mb-2">
                    <?php echo $module['content']; ?>
                </div>
            </div>
            <div class="f--col-8 f--col-tabletl-12 f--order-tabletl-1">
            <?php  
         
                $textAndMediaArgs = array(
                            'image' => $module['image'],
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
                generate_image_tag($textAndMediaArgs)    
                ?>
            </div>
        </div>
     
        
    </div>
</section>