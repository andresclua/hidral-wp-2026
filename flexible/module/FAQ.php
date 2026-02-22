<?php 
    $spacingContent = get_spacing(($module['section_spacing'])); 
    $title = $module['title'];
    $description = $module['description'];
    $faqItems = $module['faq_item'];

?>
<section class="module_FAQ f--background-a <?= $spacingContent = get_spacing(($module['section_spacing'])); ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-5 f--col-tabletl-12">
                <?php if ($title): ?>
                    <h2 class="f--font-c u--mb-2 f--color-b"><?= $title ?></h2>
                <?php endif; ?>
                <?php if ($description): ?>
                    <div class="c--content-a u--mb-2 f--color-b"><?= $description ?></div>
                <?php endif; ?>
            </div>
            <div class="f--col-7  f--col-tabletl-12 ">
                <div class="c--faq-a f--color-b">
               
                    <?php if ($faqItems): ?>
                        <div class="c--accordion-a js--collapsify f--color-b">
                        <?php foreach ($faqItems as $index => $item): ?>
                            <div class="c--accordion-a__item">
                               <button class="c--accordion-a__item__hd"  data-collapsify-control="collapsify-<?= $index ?>">
                                    <?= $item['title'] ?>
                                    <svg class="c--accordion-a__item__hd__artwork"  width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>

                                </button>
                               <div class="c--accordion-a__item__bd" data-collapsify-content="collapsify-<?= $index ?>">
                                    <?= $item['content'] ?>
                               </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
?>