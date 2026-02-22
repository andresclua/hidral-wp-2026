<?php
/**
 * Custom_Blocks
 *
 * Extends the Default_Blocks class in order to reuse all the shared logic
 * for registering ACF Blocks, creating field groups, and rendering blocks.
 *
 * The only responsibility of this class is to override the configuration
 * key used to retrieve the blocks array from the config.
 *
 * Instead of reading from `default_blocks`, this class reads from
 * `custom_blocks`.
 *
 * Example usage:
 *
 * new Custom_Blocks([
 *   'custom_blocks' => $this->projectConfig['custom_blocks'] ?? [],
 *   'template_dir'  => get_stylesheet_directory() . '/functions/framework/blocks',
 * ]);
 */
class Custom_Blocks extends Default_Blocks {

    /**
     * Configuration key that contains the list of custom blocks.
     *
     * This overrides the default value defined in Default_Blocks
     * (`default_blocks`).
     *
     * @var string
     */
    protected $blocks_key = 'custom_blocks';

}
