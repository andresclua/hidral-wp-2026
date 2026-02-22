
<section class="<?= $spacingTextandForm = get_spacing(($module['section_spacing'])); ?>">
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
             
                <?php echo  do_shortcode( '[contact-form-7 id="7c7f9a8" title="Formulario de contacto 1"]' ); ?>
            </div>
        </div>
     
        
    </div>
</section>