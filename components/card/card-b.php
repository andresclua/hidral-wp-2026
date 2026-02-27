<?php
/**
 * Card B Component
 *
 * Stat card: number + title + description.
 * Expects $stat_number, $stat_title, $stat_description variables in scope.
 *
 * @package TerraFramework
 */
?>
<div class="c--card-b">
    <span class="c--card-b__title"><?= esc_html($stat_number) ?></span>
    <h3 class="c--card-b__subtitle"><?= esc_html($stat_title) ?></h3>
    <p class="c--card-b__content"><?= esc_html($stat_description) ?></p>
</div>
