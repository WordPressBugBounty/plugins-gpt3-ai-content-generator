<?php

if (!defined('ABSPATH')) {
    exit;
}

$has_woocommerce = class_exists('WooCommerce') && post_type_exists('product');
$create_modes = [
    [
        'mode' => 'task',
        'title' => __('Manual Entry', 'gpt3-ai-content-generator'),
        'desc' => __('Type topics directly', 'gpt3-ai-content-generator'),
        'active' => true,
    ],
    [
        'mode' => 'csv',
        'title' => __('Import CSV', 'gpt3-ai-content-generator'),
        'desc' => __('Upload topics as CSV', 'gpt3-ai-content-generator'),
        'active' => false,
    ],
    [
        'mode' => 'rss',
        'title' => __('RSS Feed', 'gpt3-ai-content-generator'),
        'desc' => __('Pull from a feed', 'gpt3-ai-content-generator'),
        'active' => false,
    ],
    [
        'mode' => 'url',
        'title' => __('Web Page', 'gpt3-ai-content-generator'),
        'desc' => __('Extract from a page', 'gpt3-ai-content-generator'),
        'active' => false,
    ],
    [
        'mode' => 'gsheets',
        'title' => __('Google Sheets', 'gpt3-ai-content-generator'),
        'desc' => __('Sync from Sheets', 'gpt3-ai-content-generator'),
        'active' => false,
    ],
];

$optimize_modes = [
    [
        'mode' => 'existing-content',
        'title' => __('Rewrite Content', 'gpt3-ai-content-generator'),
        'desc' => __('Rewrite existing copy', 'gpt3-ai-content-generator'),
        'active' => false,
    ],
    [
        'mode' => 'existing-images',
        'title' => __('Image Metadata', 'gpt3-ai-content-generator'),
        'desc' => __('Alt text and captions', 'gpt3-ai-content-generator'),
        'active' => false,
    ],
];

if ($has_woocommerce) {
    $optimize_modes[] = [
        'mode' => 'existing-products',
        'title' => __('Optimize Products', 'gpt3-ai-content-generator'),
        'desc' => __('Improve product copy', 'gpt3-ai-content-generator'),
        'active' => false,
    ];
}
?>
<div class="aipkit_cw_source_selector_wrapper">
    <div class="aipkit_cw_mode_section" aria-labelledby="aipkit_cw_mode_section_create">
        <div class="aipkit_cw_mode_section_heading" id="aipkit_cw_mode_section_create">
            <?php esc_html_e('Create', 'gpt3-ai-content-generator'); ?>
        </div>
        <div class="aipkit_cw_mode_group_list" role="list" aria-labelledby="aipkit_cw_mode_section_create">
            <?php foreach ($create_modes as $item) : ?>
                <button
                    type="button"
                    class="aipkit_cw_mode_card<?php echo $item['active'] ? ' is-active' : ''; ?>"
                    data-mode="<?php echo esc_attr($item['mode']); ?>"
                    aria-pressed="<?php echo $item['active'] ? 'true' : 'false'; ?>"
                >
                    <span class="aipkit_cw_mode_text">
                        <span class="aipkit_cw_mode_title"><?php echo esc_html($item['title']); ?></span>
                        <span class="aipkit_cw_mode_desc"><?php echo esc_html($item['desc']); ?></span>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="aipkit_cw_mode_section" aria-labelledby="aipkit_cw_mode_section_optimize">
        <div class="aipkit_cw_mode_section_heading" id="aipkit_cw_mode_section_optimize">
            <?php esc_html_e('Optimize', 'gpt3-ai-content-generator'); ?>
        </div>
        <div class="aipkit_cw_mode_group_list" role="list" aria-labelledby="aipkit_cw_mode_section_optimize">
            <?php foreach ($optimize_modes as $item) : ?>
                <button
                    type="button"
                    class="aipkit_cw_mode_card<?php echo $item['active'] ? ' is-active' : ''; ?>"
                    data-mode="<?php echo esc_attr($item['mode']); ?>"
                    aria-pressed="<?php echo $item['active'] ? 'true' : 'false'; ?>"
                >
                    <span class="aipkit_cw_mode_text">
                        <span class="aipkit_cw_mode_title"><?php echo esc_html($item['title']); ?></span>
                        <span class="aipkit_cw_mode_desc"><?php echo esc_html($item['desc']); ?></span>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <label class="screen-reader-text" for="aipkit_cw_mode_select"><?php esc_html_e('Source', 'gpt3-ai-content-generator'); ?></label>
    <select id="aipkit_cw_mode_select" name="cw_generation_mode" class="aipkit_form-input aipkit_autosave_trigger screen-reader-text">
        <option value="task"><?php esc_html_e('Manual Entry', 'gpt3-ai-content-generator'); ?></option>
        <option value="csv"><?php esc_html_e('Import CSV', 'gpt3-ai-content-generator'); ?></option>
        <option value="rss"><?php esc_html_e('RSS Feed', 'gpt3-ai-content-generator'); ?></option>
        <option value="url"><?php esc_html_e('Web Page', 'gpt3-ai-content-generator'); ?></option>
        <option value="gsheets"><?php esc_html_e('Google Sheets', 'gpt3-ai-content-generator'); ?></option>
        <option value="existing-content"><?php esc_html_e('Rewrite Content', 'gpt3-ai-content-generator'); ?></option>
        <option value="existing-images"><?php esc_html_e('Image Metadata', 'gpt3-ai-content-generator'); ?></option>
        <?php if ($has_woocommerce): ?>
            <option value="existing-products"><?php esc_html_e('Optimize Products', 'gpt3-ai-content-generator'); ?></option>
        <?php endif; ?>
        <option value="existing"><?php esc_html_e('Update Existing (Legacy)', 'gpt3-ai-content-generator'); ?></option>
    </select>
</div>
