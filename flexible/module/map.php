<?php
    $spacingContent = get_spacing(($module['section_spacing']));
    $map = $module['map'] ?? '';
    $lat = $map['lat'] ?? '';
    $lng = $map['lng'] ?? '';
?>
<section class="module_map <?= $spacingContent ?>">
    <div class="f--container">
        <div class="f--row">
            <div class="f--col-12">
               
                    <div class="c--map-a js--google-map" data-lat="<?= esc_attr($lat) ?>" data-lng="<?= esc_attr($lng) ?>"></div>
          
            </div>
        </div>
    </div>
</section>
<?php
unset($spacingContent);
unset($map);
unset($lat);
unset($lng);
?>
