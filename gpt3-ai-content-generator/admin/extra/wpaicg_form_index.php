<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');
$azure_deployment_name = get_option('wpaicg_azure_deployment', '');

$wpaicg_google_api_key = get_option('wpaicg_google_model_api_key', ''); // Get Google API Key
$wpaicg_google_model_list = get_option('wpaicg_google_model_list', ['gemini-pro']);
$wpaicg_google_default_model = get_option('wpaicg_google_default_model', 'gemini-pro');

$wpaicg_categories = array();
$wpaicg_items = array();
$wpaicg_icons = array();

// Retrieve collections and default collection from the options table
$qdrant_collections_serialized = get_option('wpaicg_qdrant_collections');
$qdrant_default_collection = get_option('wpaicg_qdrant_default_collection');

// Unserialize the collections string to an array
$qdrant_collections = maybe_unserialize($qdrant_collections_serialized);

// Ensure $qdrant_collections is an array before using it
if (!is_array($qdrant_collections)) {
    $qdrant_collections = [];
}

// Retrieve Pinecone indexes from the options table
$pineconeindexes = get_option('wpaicg_pinecone_indexes','');
$pineconeindexes = empty($pineconeindexes) ? array() : json_decode($pineconeindexes,true);

$gpt4_models = \WPAICG\WPAICG_Util::get_instance()->openai_gpt4_models;
$gpt35_models = \WPAICG\WPAICG_Util::get_instance()->openai_gpt35_models;
$engineMaxTokens = \WPAICG\WPAICG_Util::get_instance()->max_token_values;

$custom_models = get_option('wpaicg_custom_models', []);

$current_model = 'gpt-4o-mini';

$wpaicg_authors = array('default' => array('name' => 'AI Power','count' => 0));
if(file_exists(WPAICG_PLUGIN_DIR.'admin/data/gptcategories.json')){
    $wpaicg_file_content = file_get_contents(WPAICG_PLUGIN_DIR.'admin/data/gptcategories.json');
    $wpaicg_file_content = json_decode($wpaicg_file_content, true);
    if($wpaicg_file_content && is_array($wpaicg_file_content) && count($wpaicg_file_content)){
        foreach($wpaicg_file_content as $key=>$item){
            $wpaicg_categories[$key] = trim($item);
        }
    }
}
if(file_exists(WPAICG_PLUGIN_DIR.'admin/data/icons.json')){
    $wpaicg_file_content = file_get_contents(WPAICG_PLUGIN_DIR.'admin/data/icons.json');
    $wpaicg_file_content = json_decode($wpaicg_file_content, true);
    if($wpaicg_file_content && is_array($wpaicg_file_content) && count($wpaicg_file_content)){
        foreach($wpaicg_file_content as $key=>$item){
            $wpaicg_icons[$key] = trim($item);
        }
    }
}
if(file_exists(WPAICG_PLUGIN_DIR.'admin/data/gptforms.json')){
    $wpaicg_file_content = file_get_contents(WPAICG_PLUGIN_DIR.'admin/data/gptforms.json');
    $wpaicg_file_content = json_decode($wpaicg_file_content, true);
    if($wpaicg_file_content && is_array($wpaicg_file_content) && count($wpaicg_file_content)){
        foreach($wpaicg_file_content as $item){
            $item['type'] = 'json';
            $item['author'] = 'default';
            $wpaicg_authors['default']['count'] += 1;
            $wpaicg_items[] = $item;
        }
    }
}

$sql = "SELECT p.ID as id,p.post_title as title,p.post_author as author, p.post_content as description";
$wpaicg_meta_keys = array('fields','editor','prompt','response','category','engine','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','color','icon','bgcolor','header','embeddings','use_default_embedding_model','selected_embedding_model','selected_embedding_provider','vectordb','collections','pineconeindexes','suffix_text','suffix_position','embeddings_limit','dans','ddraft','dclear','dnotice','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text','download_text','ddownload','copy_button','copy_text','feedback_buttons');

foreach ($wpaicg_meta_keys as $wpaicg_meta_key) {
    $sql .= $wpdb->prepare(
        ", (SELECT {$wpaicg_meta_key}.meta_value FROM {$wpdb->postmeta} {$wpaicg_meta_key} WHERE {$wpaicg_meta_key}.meta_key = %s AND p.ID = {$wpaicg_meta_key}.post_id LIMIT 1) as {$wpaicg_meta_key}",
        "wpaicg_form_{$wpaicg_meta_key}"
    );
}

$sql .= $wpdb->prepare(" FROM {$wpdb->posts} p WHERE p.post_type = %s AND p.post_status = 'publish' ORDER BY p.post_date DESC", 'wpaicg_form');
$wpaicg_custom_templates = $wpdb->get_results($sql,ARRAY_A);
if($wpaicg_custom_templates && is_array($wpaicg_custom_templates) && count($wpaicg_custom_templates)){
    foreach ($wpaicg_custom_templates as $wpaicg_custom_template){
        $wpaicg_custom_template['type'] = 'custom';
        $wpaicg_items[] = $wpaicg_custom_template;
        if(!isset($wpaicg_authors[$wpaicg_custom_template['author']])){
            $prompt_author = get_user_by('ID', $wpaicg_custom_template['author']);
            $wpaicg_authors[$wpaicg_custom_template['author']] = array('name' => $prompt_author->display_name, 'count' => 1);
        }
        else{
            $wpaicg_authors[$wpaicg_custom_template['author']]['count'] += 1;
        }
    }
}
$wpaicg_per_page = 36;
wp_enqueue_editor();
wp_enqueue_script('wp-color-picker');
wp_enqueue_style('wp-color-picker');
$kses_defaults = wp_kses_allowed_html( 'post' );
$svg_args = array(
    'svg'   => array(
        'class'           => true,
        'aria-hidden'     => true,
        'aria-labelledby' => true,
        'role'            => true,
        'xmlns'           => true,
        'width'           => true,
        'height'          => true,
        'viewbox'         => true // <= Must be lower case!
    ),
    'g'     => array( 'fill' => true ),
    'title' => array( 'title' => true ),
    'path'  => array(
        'd'               => true,
        'fill'            => true
    )
);
$allowed_tags = array_merge( $kses_defaults, $svg_args );
?>
<style>
    .wpaicg-template-icon{
        width: 70px;
        height: 70px;
        border-radius: 3px;
        display: flex;
        justify-content: center;
        align-items: center;
        color: #fff;
    }
    .wpaicg-template-icon svg{
        fill: currentColor;
        width: 50px;
        height: 50px;
    }
    .wpaicg-template-item{
        cursor: pointer;
        height: 100px;
        position: relative;
    }
    .wpaicg-template-content{
        margin-left: 10px;
        flex: 1;
    }
    .wpaicg-template-content p{
        margin: 5px 0;
        font-size: 12px;
        height: 36px;
        overflow: hidden;
    }
    .wpaicg_modal{
        position: relative;
        top: 5%;
        height: 90%;
    }
    .disappear-item{
        position: absolute;
        top: -10000px;
    }
    .wpaicg-template-items{
        position: relative;
        overflow-y: hidden;
    }
    .wpaicg-paginate .page-numbers{
        background: #e5e5e5;
        margin-right: 5px;
        cursor: pointer;
    }
    .wpaicg-paginate .page-numbers.current{
        background: #fff;
    }
    .wpaicg-template-settings > div{
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .wpaicg-template-settings > div > strong{
        display: inline-block;
        width: 50%;
    }
    .wpaicg-template-settings > div > strong > small{
        font-weight: normal;
        display: block;
    }
    .wpaicg-template-settings > div > input,.wpaicg-template-settings > div > select{
        width: 48%;
        margin: 0;
    }
    .wpaicg-template-settings .wpaicg-template-sample{
        display: block;
        position: relative;
    }
    .wpaicg-template-settings .wpaicg-template-sample:hover .wpaicg-template-response{
        display: block;
    }
    .wpaicg-template-settings .wpaicg-template-response{
        background: #333;
        border: 1px solid #444;
        position: absolute;
        border-radius: 3px;
        color: #fff;
        padding: 5px;
        width: 100%;
        bottom: calc(100% + 5px);
        right: calc(50% - 55px);
        z-index: 99;
        display: none;
    }
    .wpaicg-template-settings .wpaicg-template-response:after,.wpaicg-template-settings .wpaicg-template-response:before{
        top: 100%;
        left: 50%;
        border: solid transparent;
        content: "";
        height: 0;
        width: 0;
        position: absolute;
        pointer-events: none;
    }
    .wpaicg-template-settings .wpaicg-template-response:before{
        border-color: rgba(68, 68, 68, 0);
        border-top-color: #444;
        border-width: 7px;
        margin-left: -7px;
    }
    .wpaicg-template-settings .wpaicg-template-response:after{
        border-color: rgba(51, 51, 51, 0);
        border-top-color: #333;
        border-width: 6px;
        margin-left: -6px;
    }
    .wpaicg_modal_content{
        max-height: calc(100% - 103px);
        overflow-y: auto;
    }
    .wpaicg_notice_text {
        padding: 10px;
        text-align: left;
        margin-bottom: 12px;
    }
    .wpaicg-create-template{
        width: 100%;
        display: block!important;
        margin-bottom: 10px!important;
    }

    .wpaicg-export-template{
        width: 32%;
        display: inline-block;
        margin-bottom: 10px!important;
    }
    .wpaicg-delete-template{
        width: 32%;
        display: inline-block;
        margin-bottom: 10px!important;
        background: #9d0000!important;
        border-color: #9b0000!important;
        color: #fff!important;
    }
    .wpaicg-template-icons{}
    .wpaicg-template-icons span{
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        margin-right: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        color: #333;
    }
    .wpaicg-template-icons span svg{
        fill: currentColor;
        width: 30px;
        height: 30px;
    }
    .wpaicg-template-icons span.icon_selected{
        background: #343434;
        color: #fff;
    }
    .wp-picker-holder{
        position: absolute;
        z-index: 99;
    }
    .wp-picker-container{
        position: relative;
    }
    .wpaicg-template-action{
        position: absolute;
        right: 0;
        top: 37px;
        display: none;
    }
    .wpaicg-template-item:hover .wpaicg-template-action{
        display: block;
    }
    .wpaicg-template-action-edit{}
    .wpaicg-template-action-delete{
        background: #9d0000!important;
        border-color: #9b0000!important;
        color: #fff!important;
    }
    .wpaicg-template-form-field{
        display: block;
        padding: 10px;
        background: #d7d7d7;
        position: relative;
        border-radius: 4px;
        margin-bottom: 5px;
    }
    .wpaicg-template-form-field .wpaicg-grid{
        grid-row-gap: 0;
    }
    .wpaicg-template-form-field input{
        display: block;
        width: 100%;
    }
    .wpaicg-field-delete{
        position: absolute;
        top: 4px;
        right: 5px;
        width: 20px;
        height: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #b00;
        border-radius: 2px;
        color: #fff;
        font-size: 20px;
        font-family: Arial, serif;
        cursor: pointer;
    }
    .wpaicg-form-fields input[type=text],.wpaicg-form-fields select,.wpaicg-form-fields input[type=url],.wpaicg-form-fields input[type=email],.wpaicg-form-fields input[type=number]{
        width: 50%;
        margin-bottom: 10px;
    }
    .wpaicg-modal-tabs{
        margin: 0;
        display: flex;
    }
    .wpaicg-modal-tabs li{
        padding: 12px 15px;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
        background: #2271b1;
        margin-bottom: 0;
        margin-right: 5px;
        border-top: 1px solid #2271b1;
        border-left: 1px solid #2271b1;
        border-right: 1px solid #2271b1;
        cursor: pointer;
        position: relative;
        top: 1px;
        color: #fff;
    }
    .wpaicg-modal-tabs li.wpaicg-active{
        background: #fff;
        color: #333;
    }
    .wpaicg-modal-tab-content{
        border: 1px solid #ccc;
    }
    .wpaicg-modal-tab{
        padding: 10px;
    }
    .wpaicg-template-item:before{
        display:none
    }
</style>
<div id="exportMessage" style="display: none;" class="notice notice-success"></div>
<div class="wpaicg-template-form-field-default" style="display: none">
    <div class="wpaicg-template-form-field">
        <div class="wpaicg-grid">
            <div class="">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Label','gpt3-ai-content-generator')?></strong>
                <input type="text" name="fields[0][label]" required class="wpaicg-create-template-field-label">
            </div>
            <div class="wpaicg-grid-2">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('ID','gpt3-ai-content-generator')?></strong>
                <input placeholder="example_field" type="text" name="fields[0][id]" required class="wpaicg-create-template-field-id">
                <small><?php echo esc_html__('Add this field in your prompt with following format','gpt3-ai-content-generator')?>: {example_field}</small>
            </div>
            <div class="">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Type','gpt3-ai-content-generator')?></strong>
                <select name="fields[0][type]" class="wpaicg-create-template-field-type">
                    <option value="text"><?php echo esc_html__('Single Line Text','gpt3-ai-content-generator')?></option>
                    <option value="select"><?php echo esc_html__('Dropdown Menu','gpt3-ai-content-generator')?></option>
                    <option value="number"><?php echo esc_html__('Number Input','gpt3-ai-content-generator')?></option>
                    <option value="email"><?php echo esc_html__('Email Address','gpt3-ai-content-generator')?></option>
                    <option value="url"><?php echo esc_html__('Website URL','gpt3-ai-content-generator')?></option>
                    <option value="textarea"><?php echo esc_html__('Multi-line Text','gpt3-ai-content-generator')?></option>
                    <option value="checkbox"><?php echo esc_html__('Checkbox Option','gpt3-ai-content-generator')?></option>
                    <option value="radio"><?php echo esc_html__('Radio Button','gpt3-ai-content-generator')?></option>
                </select>
            </div>
            <div class="wpaicg-create-template-field-min-main">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Min','gpt3-ai-content-generator')?></strong>
                <input placeholder="<?php echo esc_html__('Optional','gpt3-ai-content-generator')?>" type="number" name="fields[0][min]" class="wpaicg-create-template-field-min">
            </div>
            <div class="wpaicg-create-template-field-max-main">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Max','gpt3-ai-content-generator')?></strong>
                <input placeholder="<?php echo esc_html__('Optional','gpt3-ai-content-generator')?>" type="number" name="fields[0][min]" class="wpaicg-create-template-field-max">
            </div>
            <div class="wpaicg-create-template-field-rows-main" style="display: none">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Rows','gpt3-ai-content-generator')?></strong>
                <input placeholder="<?php echo esc_html__('Optional','gpt3-ai-content-generator')?>" type="number" name="fields[0][rows]" class="wpaicg-create-template-field-rows">
            </div>
            <div class="wpaicg-create-template-field-cols-main" style="display: none">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Cols','gpt3-ai-content-generator')?></strong>
                <input placeholder="<?php echo esc_html__('Optional','gpt3-ai-content-generator')?>" type="number" name="fields[0][cols]" class="wpaicg-create-template-field-cols">
            </div>
        </div>
        <div class="wpaicg-create-template-field-options-main" style="display: none">
            <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Options','gpt3-ai-content-generator')?></strong>
            <textarea name="fields[0][options]" class="wpaicg-create-template-field-options wpaicg-w-100"></textarea>
            <small><?php echo esc_html__('Separate by','gpt3-ai-content-generator')?> "|"</small>
        </div>
        <span class="wpaicg-field-delete">&times;</span>
    </div>
</div>
<div class="wpaicg-create-template-content" style="display: none">
    <?php
    wp_nonce_field('wpaicg_formai_save');
    ?>
    <input type="hidden" name="action" value="wpaicg_update_template">
    <input type="hidden" name="id" value="" class="wpaicg-create-template-id">
    <ul class="wpaicg-modal-tabs">
        <li class="wpaicg-active" data-target="properties"><?php echo esc_html__('Properties','gpt3-ai-content-generator')?></li>
        <li data-target="ai-engine"><?php echo esc_html__('AI Engine','gpt3-ai-content-generator')?></li>
        <li data-target="fields"><?php echo esc_html__('Fields','gpt3-ai-content-generator')?></li>
        <li data-target="embeddings"><?php echo esc_html__('Embeddings','gpt3-ai-content-generator')?></li>
        <li data-target="style"><?php echo esc_html__('Style','gpt3-ai-content-generator')?></li>
        <li data-target="frontend"><?php echo esc_html__('Frontend','gpt3-ai-content-generator')?></li>
    </ul>
    <div class="wpaicg-modal-tab-content wpaicg-mb-10">
        <div class="wpaicg-modal-tab wpaicg-modal-tab-properties">
            <div class="wpaicg-grid wpaicg-mb-10">
                <div class="wpaicg-grid-3">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Title','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="title" required class="regular-text wpaicg-w-100 wpaicg-create-template-title">
                </div>
                <div class="wpaicg-grid-3">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Category','gpt3-ai-content-generator')?></strong>
                    <select name="category" class="wpaicg-w-100 wpaicg-create-template-category">
                        <?php
                        foreach($wpaicg_categories as $key=>$wpaicg_category){
                            echo '<option value="'.esc_html($key).'">'.esc_html($wpaicg_category).'</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="wpaicg-mb-10">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Description','gpt3-ai-content-generator')?></strong>
                <input type="text" name="description" required class="regular-text wpaicg-w-100 wpaicg-create-template-description">
            </div>
            <div class="wpaicg-mb-10">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Prompt','gpt3-ai-content-generator')?></strong>
                <textarea name="prompt" required class="regular-text wpaicg-w-100 wpaicg-create-template-prompt"></textarea>
            </div>
            <div class="wpaicg-mb-10">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Sample Response','gpt3-ai-content-generator')?></strong>
                <textarea name="response" class="regular-text wpaicg-w-100 wpaicg-create-template-response"></textarea>
            </div>
        </div>
        <div class="wpaicg-modal-tab wpaicg-modal-tab-ai-engine" style="display: none">
            <div class="wpaicg-grid wpaicg-mb-10">
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Engine','gpt3-ai-content-generator')?></strong>
                    <?php if ($wpaicg_provider == 'OpenAI'): ?>
                        <!-- Display dropdown for OpenAI -->
                        <select name="engine" class="wpaicg-w-100 wpaicg-create-template-engine" required>
                            <optgroup label="GPT-4">
                                <?php foreach ($gpt4_models as $value => $display_name): ?>
                                    <option value="<?php echo esc_attr($value); ?>"<?php selected($value, $current_model); ?>><?php echo esc_html($display_name); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="GPT-3.5">
                                <?php foreach ($gpt35_models as $value => $display_name): ?>
                                    <option value="<?php echo esc_attr($value); ?>"<?php selected($value, $current_model); ?>><?php echo esc_html($display_name); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Custom Models">
                                <?php foreach ($custom_models as $model): ?>
                                    <option value="<?php echo esc_attr($model); ?>"<?php selected($model, $current_model); ?>><?php echo esc_html($model); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    <?php elseif ($wpaicg_provider == 'Google'): ?>
                        <!-- Display dropdown for Google AI -->
                        <select name="engine" class="wpaicg-w-100 wpaicg-create-template-engine" required>
                            <optgroup label="Google Models">
                                <?php foreach ($wpaicg_google_model_list as $model): ?>
                                    <?php if (stripos($model, 'vision') !== false): ?>
                                        <!-- Option disabled if it contains the word 'vision' -->
                                        <option value="<?php echo esc_attr($model); ?>" disabled><?php echo esc_html($model); ?></option>
                                    <?php else: ?>
                                        <option value="<?php echo esc_attr($model); ?>"<?php selected($model, $wpaicg_google_default_model); ?>><?php echo esc_html($model); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    <?php elseif ($wpaicg_provider == 'OpenRouter'): ?>
                        <!-- Display dropdown for OpenRouter -->
                        <?php
                        $openrouter_models = get_option('wpaicg_openrouter_model_list', []);
                        $openrouter_grouped_models = [];
                        foreach ($openrouter_models as $openrouter_model) {
                            $openrouter_provider = explode('/', $openrouter_model['id'])[0];
                            if (!isset($openrouter_grouped_models[$openrouter_provider])) {
                                $openrouter_grouped_models[$openrouter_provider] = [];
                            }
                            $openrouter_grouped_models[$openrouter_provider][] = $openrouter_model;
                        }
                        ksort($openrouter_grouped_models);

                        $openrouter_selected_model = get_option('wpaicg_widget_openrouter_model', 'openrouter/auto');
                        ?>
                        <select name="engine" class="wpaicg-w-100 wpaicg-create-template-engine" required>
                            <?php
                            foreach ($openrouter_grouped_models as $openrouter_provider => $openrouter_models): ?>
                                <optgroup label="<?php echo esc_attr($openrouter_provider); ?>">
                                    <?php
                                    usort($openrouter_models, function($a, $b) {
                                        return strcmp($a["name"], $b["name"]);
                                    });
                                    foreach ($openrouter_models as $openrouter_model): ?>
                                        <option value="<?php echo esc_attr($openrouter_model['id']); ?>"<?php selected($openrouter_model['id'], $openrouter_selected_model); ?>><?php echo esc_html($openrouter_model['name']); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <!-- Display readonly text field for AzureAI -->
                        <input type="text" 
                            name="engine" 
                            class="wpaicg-w-100 wpaicg-create-template-engine"
                            readonly
                            value="<?php echo esc_html($azure_deployment_name); ?>"
                        />
                    <?php endif; ?>

                </div>
                <div>
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Max Tokens','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="max_tokens" class="regular-text wpaicg-w-100 wpaicg-create-template-max_tokens">
                </div>
                <div>
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Temperature','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="temperature" class="regular-text wpaicg-w-100 wpaicg-create-template-temperature">
                </div>
                <div>
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Top P','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="top_p" class="regular-text wpaicg-w-100 wpaicg-create-template-top_p">
                </div>
                <div>
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Best Of','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="best_of" class="regular-text wpaicg-w-100 wpaicg-create-template-best_of">
                </div>
                <div>
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('F. Penalty','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="frequency_penalty" class="regular-text wpaicg-w-100 wpaicg-create-template-frequency_penalty">
                </div>
                <div>
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('P. Penalty','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="presence_penalty" class="regular-text wpaicg-w-100 wpaicg-create-template-presence_penalty">
                </div>
                <div>
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Stop','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="stop" class="regular-text wpaicg-w-100 wpaicg-create-template-stop">
                </div>
            </div>
        </div>
        <div class="wpaicg-modal-tab wpaicg-modal-tab-fields" style="display: none">
            <div class="wpaicg-mb-10">
                <div class="mb-5 wpaicg-d-flex wpaicg-align-items-center"><strong class=""><?php echo esc_html__('Fields','gpt3-ai-content-generator')?></strong><button type="button" class="wpaicg-create-form-field button button-primary button-small wpaicg-align-items-center" style="display: inline-flex;margin-left: 5px;"><span class="dashicons dashicons-plus"></span></button></div>
                <div class="wpaicg-template-fields"></div>
            </div>
        </div>
        <div class="wpaicg-modal-tab wpaicg-modal-tab-embeddings" style="display: none">
            <!--Activate Embeddings -->
            <div class="wpaicg-grid wpaicg-mb-10">
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Use Embeddings','gpt3-ai-content-generator')?></strong>
                    <select name="embeddings" class="wpaicg-w-100 wpaicg-create-template-embeddings">
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                        <option value="yes"><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <!-- Vector DB Section -->
                <div class="wpaicg-grid-1 wpaicg-vectordb-container">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Vector DB','gpt3-ai-content-generator')?></strong>
                    <select name="vectordb" class="wpaicg-w-100 wpaicg-create-template-vectordb">
                        <option value=""><?php echo esc_html__('None','gpt3-ai-content-generator')?></option>
                        <option value="qdrant"><?php echo esc_html__('Qdrant','gpt3-ai-content-generator')?></option>
                        <option value="pinecone"><?php echo esc_html__('Pinecone','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <!-- Placeholder for Collections Dropdown -->
                <div class="wpaicg-grid-1 wpaicg-collections-dropdown" style="display: none">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Collection', 'gpt3-ai-content-generator'); ?></strong>
                    <select name="collections" class="wpaicg-w-100 wpaicg-create-template-collections">
                        <!-- Options will be dynamically added here -->
                    </select>
                </div>
                <!-- Placeholder for Pinecone Indexes Dropdown -->
                <div class="wpaicg-grid-1 wpaicg-pineconeindexes-dropdown" style="display: none">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Index', 'gpt3-ai-content-generator'); ?></strong>
                    <select name="pineconeindexes" class="wpaicg-w-100 wpaicg-create-template-pineconeindexes">
                        <!-- Options will be dynamically added here -->
                    </select>
                </div>
                <!-- Placeholder for Number of Results dropdown from 1 to 3-->
                <div class="wpaicg-grid-1 wpaicg-embeddings-limit" style="display: none">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Limit', 'gpt3-ai-content-generator'); ?></strong>
                    <select name="embeddings_limit" class="wpaicg-w-100 wpaicg-create-template-embeddings_limit">
                        <option value="1"><?php echo esc_html__('1', 'gpt3-ai-content-generator'); ?></option>
                        <option value="2"><?php echo esc_html__('2', 'gpt3-ai-content-generator'); ?></option>
                        <option value="3"><?php echo esc_html__('3', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
                <!-- Placeholder for Suffix Text -->
                <div class="wpaicg-grid-1 wpaicg-context-suffix" style="display: none">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Context Label','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Context:','gpt3-ai-content-generator')?>" type="text" name="suffix_text" class="regular-text wpaicg-w-100 wpaicg-create-template-suffix_text">
                </div>
                <!-- Placeholder for Context label position: before prompt or after prompt -->
                <div class="wpaicg-grid-1 wpaicg-context-suffix-position" style="display: none">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Context Position','gpt3-ai-content-generator')?></strong>
                    <select name="suffix_position" class="wpaicg-w-100 wpaicg-create-template-suffix_position">
                        <option value="after"><?php echo esc_html__('After Prompt','gpt3-ai-content-generator')?></option>
                        <option value="before"><?php echo esc_html__('Before Prompt','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1 wpaicg-context-use_default_embedding_model" style="display: none">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Use Default Model','gpt3-ai-content-generator')?></strong>
                    <select name="use_default_embedding_model" class="wpaicg-w-100 wpaicg-create-template-use_default_embedding_model">
                        <option value="yes"><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1 wpaicg-context-selected_embedding_model">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Embedding Model','gpt3-ai-content-generator')?></strong>
                    <select name="selected_embedding_model" class="wpaicg-w-100 wpaicg-create-template-selected_embedding_model">
                        <?php
                        $embedding_models = \WPAICG\WPAICG_Util::get_instance()->get_embedding_models();
                        $embedding_model = '';
                        foreach ($embedding_models as $provider => $models) {
                            echo '<optgroup label="' . esc_attr($provider) . '">';
                            foreach ($models as $model => $dimension) {
                                $selected = ($model === $embedding_model) ? 'selected' : '';
                                echo '<option value="' . esc_attr($model) . '" data-provider="' . esc_attr($provider) . '" ' . $selected . '>' . esc_html($model) . ' (' . esc_html($dimension) . ')</option>';
                            }
                            echo '</optgroup>';
                        }
                        ?>
                    </select>
                    <input type="hidden" id="selected_embedding_provider" name="selected_embedding_provider" class="wpaicg-w-100 wpaicg-create-template-selected_embedding_provider">
                </div>
            </div>
        </div>
        <div class="wpaicg-modal-tab wpaicg-modal-tab-style" style="display: none">
            <div class="wpaicg-grid wpaicg-mb-10">
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Icon Color','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="color" class="regular-text wpaicg-w-100 wpaicg-create-template-color">
                </div>
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Background Color','gpt3-ai-content-generator')?></strong>
                    <input type="text" name="bgcolor" class="regular-text wpaicg-w-100 wpaicg-create-template-bgcolor">
                </div>
            </div>
            <div class="wpaicg-mb-10">
                <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Icon','gpt3-ai-content-generator')?></strong>
                <input type="hidden" class="wpaicg-create-template-icon" name="icon" value="robot">
                <div class="wpaicg-template-icons">
                    <?php
                    foreach($wpaicg_icons as $key=>$wpaicg_icon){
                        echo '<span data-key="'.esc_html($key).'">'.wp_kses($wpaicg_icon,$allowed_tags).'</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="wpaicg-modal-tab wpaicg-modal-tab-frontend" style="display: none">
            <h3><strong><?php echo esc_html__('Response','gpt3-ai-content-generator')?></strong></h3>
            <div class="wpaicg-grid wpaicg-mb-10">
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Result','gpt3-ai-content-generator')?></strong>
                    <select name="editor" class="wpaicg-w-100 wpaicg-create-template-editor">
                        <option value="div"><?php echo esc_html__('Inline','gpt3-ai-content-generator')?></option>
                        <option value="textarea"><?php echo esc_html__('Text Editor','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
            </div>
            <h3><strong><?php echo esc_html__('Display','gpt3-ai-content-generator')?></strong></h3>
            <div class="wpaicg-grid wpaicg-mb-10">
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Header','gpt3-ai-content-generator')?></strong>
                    <select name="header" class="wpaicg-w-100 wpaicg-create-template-header">
                        <option value=""><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Num. Answers','gpt3-ai-content-generator')?></strong>
                    <select name="dans" class="wpaicg-w-100 wpaicg-create-template-dans">
                        <option value=""><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Draft Button','gpt3-ai-content-generator')?></strong>
                    <select name="ddraft" class="wpaicg-w-100 wpaicg-create-template-ddraft">
                        <option value=""><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Clear Button','gpt3-ai-content-generator')?></strong>
                    <select name="dclear" class="wpaicg-w-100 wpaicg-create-template-dclear">
                        <option value=""><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Notification','gpt3-ai-content-generator')?></strong>
                    <select name="dnotice" class="wpaicg-w-100 wpaicg-create-template-dnotice">
                        <option value=""><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Download','gpt3-ai-content-generator')?></strong>
                    <select name="ddownload" class="wpaicg-w-100 wpaicg-create-template-ddownload">
                        <option value=""><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Copy','gpt3-ai-content-generator')?></strong>
                    <select name="copy_button" class="wpaicg-w-100 wpaicg-create-template-copy_button">
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                        <option value="yes"><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="wpaicg-grid-1">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Feedback','gpt3-ai-content-generator')?></strong>
                    <select name="feedback_buttons" class="wpaicg-w-100 wpaicg-create-template-feedback_buttons">
                        <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                        <option value="yes"><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                    </select>   
                </div>
            </div>
            <h3><strong><?php echo esc_html__('Custom Text','gpt3-ai-content-generator')?></strong></h3>
            <div class="wpaicg-grid wpaicg-mb-10">
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Generate Button','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Generate','gpt3-ai-content-generator')?>" type="text" name="generate_text" class="regular-text wpaicg-w-100 wpaicg-create-template-generate_text">
                </div>
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Num. Answers Text','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Number of Answers','gpt3-ai-content-generator')?>" type="text" name="noanswer_text" class="regular-text wpaicg-w-100 wpaicg-create-template-noanswer_text">
                </div>
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Draft Text','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Save Draft','gpt3-ai-content-generator')?>" type="text" name="draft_text" class="regular-text wpaicg-w-100 wpaicg-create-template-draft_text">
                </div>
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Clear Text','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Clear','gpt3-ai-content-generator')?>" type="text" name="clear_text" class="regular-text wpaicg-w-100 wpaicg-create-template-clear_text">
                </div>
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Stop Text','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Stop','gpt3-ai-content-generator')?>" type="text" name="stop_text" class="regular-text wpaicg-w-100 wpaicg-create-template-stop_text">
                </div>
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Notification Text','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Please register to save your result','gpt3-ai-content-generator')?>" type="text" name="cnotice_text" class="regular-text wpaicg-w-100 wpaicg-create-template-cnotice_text">
                </div>
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Download Text','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Download','gpt3-ai-content-generator')?>" type="text" name="download_text" class="regular-text wpaicg-w-100 wpaicg-create-template-download_text">
                </div>
                <div class="wpaicg-grid-2">
                    <strong class="wpaicg-d-block mb-5"><?php echo esc_html__('Copy Text','gpt3-ai-content-generator')?></strong>
                    <input value="<?php echo esc_html__('Copy','gpt3-ai-content-generator')?>" type="text" name="copy_text" class="regular-text wpaicg-w-100 wpaicg-create-template-copy_text">
                </div>
            </div>
        </div>
    </div>
    <button class="button button-primary wpaicg-create-template-save"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
</div>
<?php
if(isset($_GET['update_template']) && !empty($_GET['update_template'])):
    ?>
    <p style="padding: 6px 12px;border: 1px solid green;border-radius: 3px;background: lightgreen;">
        <strong><?php echo esc_html__('Success','gpt3-ai-content-generator')?>:</strong> <?php echo esc_html__('Congrats! Your template created! You can add this shortcode to your page','gpt3-ai-content-generator')?>: [wpaicg_form id=<?php echo esc_html($_GET['update_template'])?> custom=yes]
    </p>
<?php
endif;
?>
<div class="wpaicg_template">
    <div class="wpaicg-grid">
        <div class="wpaicg-grid-1">
            <button class="button button-primary wpaicg-create-template" type="button"><?php echo esc_html__('Design Your Form','gpt3-ai-content-generator')?></button>
            <!-- add Export and Import buttons next to each ohter -->
            <button class="button button-primary wpaicg-export-template" type="button" id="exportButton"><?php echo esc_html__('Export','gpt3-ai-content-generator')?></button>
            <button class="button button-primary wpaicg-export-template" type="button" id="importButton"><?php echo esc_html__('Import','gpt3-ai-content-generator')?></button>
            <button class="button button-primary wpaicg-delete-template" type="button" id="deleteButton"><?php echo esc_html__('Delete','gpt3-ai-content-generator')?></button>
            <!-- Hidden File Input for Import -->
            <input type="file" id="importFileInput" style="display: none;" accept=".json">
            <p></p>
            <strong><?php echo esc_html__('Author','gpt3-ai-content-generator')?></strong>
            <ul class="wpaicg-list wpaicg-mb-10 wpaicg-authors">
                <?php
                if(count($wpaicg_authors)){
                    foreach($wpaicg_authors as $key=>$wpaicg_author){
                        ?>
                        <li><label><input type="checkbox" value="<?php echo esc_attr($key)?>">&nbsp;<?php echo esc_html($wpaicg_author['name'])?> (<?php echo esc_html($wpaicg_author['count'])?>)</label></li>
                        <?php
                    }
                }
                ?>
            </ul>
            <strong><?php echo esc_html__('Category','gpt3-ai-content-generator')?></strong>
            <ul class="wpaicg-list wpaicg-categories">
                <?php
                if(count($wpaicg_categories)){
                    foreach($wpaicg_categories as $wpaicg_category){
                        ?>
                        <li><label><input type="checkbox" value="<?php echo sanitize_title($wpaicg_category)?>">&nbsp;<?php echo esc_html($wpaicg_category)?></label></li>
                        <?php
                    }
                }
                ?>
            </ul>
        </div>
        <div class="wpaicg-grid-5">
            <div class="wpaicg-mb-10">
                <input class="wpaicg-w-100 wpaicg-d-block wpaicg-template-search" type="text" placeholder="<?php echo esc_html__('Search Template','gpt3-ai-content-generator')?>">
            </div>
            <div class="wpaicg-grid-three wpaicg-template-items">
                <?php
                if(count($wpaicg_items)):
                    foreach($wpaicg_items as $wpaicg_item):
                        $wpaicg_item_categories = array();
                        $wpaicg_item_categories_name = array();
                        if(isset($wpaicg_item['category']) && !empty($wpaicg_item['category'])){
                            $wpaicg_item_categories = array_map('trim', explode(',', $wpaicg_item['category']));
                        }
                        $wpaicg_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path d="M320 0c17.7 0 32 14.3 32 32V96H480c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H160c-35.3 0-64-28.7-64-64V160c0-35.3 28.7-64 64-64H288V32c0-17.7 14.3-32 32-32zM208 384c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H208zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H304zm96 0c-8.8 0-16 7.2-16 16s7.2 16 16 16h32c8.8 0 16-7.2 16-16s-7.2-16-16-16H400zM264 256c0-22.1-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40s40-17.9 40-40zm152 40c22.1 0 40-17.9 40-40s-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40zM48 224H64V416H48c-26.5 0-48-21.5-48-48V272c0-26.5 21.5-48 48-48zm544 0c26.5 0 48 21.5 48 48v96c0 26.5-21.5 48-48 48H576V224h16z"/></svg>';
                        if(isset($wpaicg_item['icon']) && !empty($wpaicg_item['icon']) && isset($wpaicg_icons[$wpaicg_item['icon']]) && !empty($wpaicg_icons[$wpaicg_item['icon']])){
                            $wpaicg_icon = $wpaicg_icons[$wpaicg_item['icon']];
                        }
                        $wpaicg_icon_color = isset($wpaicg_item['color']) && !empty($wpaicg_item['color']) ? $wpaicg_item['color'] : '#19c37d';
                        $wpaicg_engine = isset($wpaicg_item['engine']) && !empty($wpaicg_item['engine']) ? $wpaicg_item['engine'] : $this->wpaicg_engine;
                        if ($wpaicg_provider == 'Azure') {
                            $wpaicg_engine = get_option('wpaicg_azure_deployment', '');
                        } elseif ($wpaicg_provider == 'Google') {
                            $wpaicg_google_default_model = get_option('wpaicg_google_default_model', 'gemini-pro');
                            $wpaicg_engine = isset($wpaicg_item['engine']) && !empty($wpaicg_item['engine']) ? $wpaicg_item['engine'] : $wpaicg_google_default_model;
                        } elseif ($wpaicg_provider == 'OpenRouter') {
                            $wpaicg_openrouter_default_model = get_option('wpaicg_openrouter_default_model', 'openrouter/auto');
                            $wpaicg_engine = isset($wpaicg_item['engine']) && !empty($wpaicg_item['engine']) ? $wpaicg_item['engine'] : $wpaicg_openrouter_default_model;
                        }
                        $wpaicg_max_tokens = isset($wpaicg_item['max_tokens']) && !empty($wpaicg_item['max_tokens']) ? $wpaicg_item['max_tokens'] : $this->wpaicg_max_tokens;
                        $wpaicg_temperature = isset($wpaicg_item['temperature']) && !empty($wpaicg_item['temperature']) ? $wpaicg_item['temperature'] : $this->wpaicg_temperature;
                        $wpaicg_top_p = isset($wpaicg_item['top_p']) && !empty($wpaicg_item['top_p']) ? $wpaicg_item['top_p'] : $this->wpaicg_top_p;
                        $wpaicg_best_of = isset($wpaicg_item['best_of']) && !empty($wpaicg_item['best_of']) ? $wpaicg_item['best_of'] : $this->wpaicg_best_of;
                        $wpaicg_frequency_penalty = isset($wpaicg_item['frequency_penalty']) && !empty($wpaicg_item['frequency_penalty']) ? $wpaicg_item['frequency_penalty'] : $this->wpaicg_frequency_penalty;
                        $wpaicg_presence_penalty = isset($wpaicg_item['presence_penalty']) && !empty($wpaicg_item['presence_penalty']) ? $wpaicg_item['presence_penalty'] : $this->wpaicg_presence_penalty;
                        $wpaicg_stop = isset($wpaicg_item['stop']) && !empty($wpaicg_item['stop']) ? $wpaicg_item['stop'] : $this->wpaicg_stop;
                        $wpaicg_stop_lists = '';
                        if(is_array($wpaicg_stop) && count($wpaicg_stop)){
                            foreach($wpaicg_stop as $item_stop){
                                if($item_stop === "\n"){
                                    $item_stop = '\n';
                                }
                                $wpaicg_stop_lists = empty($wpaicg_stop_lists) ? $item_stop : ','.$item_stop;
                            }
                        }
                        elseif(is_array($wpaicg_stop) && !count($wpaicg_stop)){
                            $wpaicg_stop_lists = '';
                        }
                        else{
                            $wpaicg_stop_lists = $wpaicg_stop;
                        }
                        if(count($wpaicg_item_categories)){
                            foreach($wpaicg_item_categories as $wpaicg_item_category){
                                if(isset($wpaicg_categories[$wpaicg_item_category]) && !empty($wpaicg_categories[$wpaicg_item_category])){
                                    $wpaicg_item_categories_name[] = $wpaicg_categories[$wpaicg_item_category];
                                }
                            }
                        }
                        $wpaicg_fields = isset($wpaicg_item['fields']) && !empty($wpaicg_item['fields']) ? $wpaicg_item['fields'] : '';
                        if(!empty($wpaicg_fields) && !is_array($wpaicg_fields) && strpos($wpaicg_fields,'\"') !== false) {
                            $wpaicg_fields = str_replace('\"', '[QUOTE]', $wpaicg_fields);
                        }
                        if(!empty($wpaicg_fields) && !is_array($wpaicg_fields) && strpos($wpaicg_fields,"\'") !== false) {
                            $wpaicg_fields = str_replace('\\', '', $wpaicg_fields);
                        }
                        ?>
                        <div
                            id="wpaicg-template-item-<?php echo esc_html($wpaicg_item['id'])?>"
                            data-fields="<?php echo esc_html($wpaicg_item['type'] == 'custom' ? $wpaicg_fields : json_encode($wpaicg_fields,JSON_UNESCAPED_UNICODE ))?>"
                            data-title="<?php echo esc_html($wpaicg_item['title'])?>"
                            data-type="<?php echo esc_html($wpaicg_item['type'])?>"
                            data-id="<?php echo esc_html($wpaicg_item['id'])?>"
                            data-post_title="<?php echo esc_html($wpaicg_item['title'])?>"
                            data-desc="<?php echo esc_html(@$wpaicg_item['description'])?>"
                            data-description="<?php echo esc_html(@$wpaicg_item['description'])?>"
                            data-icon="<?php echo esc_html(@$wpaicg_item['icon'])?>"
                            data-color="<?php echo esc_html($wpaicg_icon_color)?>"
                            data-engine="<?php echo esc_html($wpaicg_engine)?>"
                            data-max_tokens="<?php echo esc_html($wpaicg_max_tokens)?>"
                            data-temperature="<?php echo esc_html($wpaicg_temperature)?>"
                            data-top_p="<?php echo esc_html($wpaicg_top_p)?>"
                            data-best_of="<?php echo esc_html($wpaicg_best_of)?>"
                            data-frequency_penalty="<?php echo esc_html($wpaicg_frequency_penalty)?>"
                            data-presence_penalty="<?php echo esc_html($wpaicg_presence_penalty)?>"
                            data-stop="<?php echo esc_html($wpaicg_stop_lists)?>"
                            data-categories="<?php echo esc_html(implode(', ',$wpaicg_item_categories_name))?>"
                            data-category="<?php echo esc_html($wpaicg_item['category'])?>"
                            data-prompt="<?php echo esc_html(@$wpaicg_item['prompt']);?>"
                            data-estimated="<?php echo isset($wpaicg_item['estimated']) ? esc_html($wpaicg_item['estimated']): '';?>"
                            data-response="<?php echo esc_html(@$wpaicg_item['response']);?>"
                            data-editor="<?php echo isset($wpaicg_item['editor']) && $wpaicg_item['editor'] == 'div' ? 'div' : 'textarea'?>"
                            data-header="<?php echo isset($wpaicg_item['header']) ? esc_html($wpaicg_item['header']) : '';?>"
                            data-embeddings="<?php echo isset($wpaicg_item['embeddings']) ? esc_html($wpaicg_item['embeddings']) : 'no';?>"
                            data-use_default_embedding_model="<?php echo isset($wpaicg_item['use_default_embedding_model']) ? esc_html($wpaicg_item['use_default_embedding_model']) : 'yes';?>"
                            data-selected_embedding_model = "<?php echo isset($wpaicg_item['selected_embedding_model']) ? esc_html($wpaicg_item['selected_embedding_model']) : '';?>"
                            data-selected_embedding_provider = "<?php echo isset($wpaicg_item['selected_embedding_provider']) ? esc_html($wpaicg_item['selected_embedding_provider']) : '';?>"
                            data-vectordb="<?php echo isset($wpaicg_item['vectordb']) ? esc_html($wpaicg_item['vectordb']) : '';?>"
                            data-suffix_position="<?php echo isset($wpaicg_item['suffix_position']) ? esc_html($wpaicg_item['suffix_position']) : 'after';?>"
                            data-collections="<?php echo isset($wpaicg_item['collections']) ? esc_html($wpaicg_item['collections']) : '';?>"
                            data-pineconeindexes="<?php echo isset($wpaicg_item['pineconeindexes']) ? esc_html($wpaicg_item['pineconeindexes']) : '';?>"
                            data-bgcolor="<?php echo isset($wpaicg_item['bgcolor']) ? esc_html($wpaicg_item['bgcolor']) : '';?>"
                            data-dans="<?php echo isset($wpaicg_item['dans']) ? esc_html($wpaicg_item['dans']) : '';?>"
                            data-ddraft="<?php echo isset($wpaicg_item['ddraft']) ? esc_html($wpaicg_item['ddraft']) : '';?>"
                            data-dclear="<?php echo isset($wpaicg_item['dclear']) ? esc_html($wpaicg_item['dclear']) : '';?>"
                            data-dnotice="<?php echo isset($wpaicg_item['dnotice']) ? esc_html($wpaicg_item['dnotice']) : '';?>"
                            data-generate_text="<?php echo isset($wpaicg_item['generate_text']) && !empty($wpaicg_item['generate_text']) ? esc_html($wpaicg_item['generate_text']) : esc_html__('Generate','gpt3-ai-content-generator');?>"
                            data-suffix_text = "<?php echo isset($wpaicg_item['suffix_text']) && !empty($wpaicg_item['suffix_text']) ? esc_html($wpaicg_item['suffix_text']) : esc_html__('Context:','gpt3-ai-content-generator');?>"
                            data-embeddings_limit="<?php echo isset($wpaicg_item['embeddings_limit']) ? esc_html($wpaicg_item['embeddings_limit']) : '1';?>"
                            data-noanswer_text="<?php echo isset($wpaicg_item['noanswer_text']) && !empty($wpaicg_item['noanswer_text']) ? esc_html($wpaicg_item['noanswer_text']) : esc_html__('Number of Answers','gpt3-ai-content-generator');?>"
                            data-draft_text="<?php echo isset($wpaicg_item['draft_text']) && !empty($wpaicg_item['draft_text']) ? esc_html($wpaicg_item['draft_text']) : esc_html__('Save Draft','gpt3-ai-content-generator');?>"
                            data-clear_text="<?php echo isset($wpaicg_item['clear_text']) && !empty($wpaicg_item['clear_text']) ? esc_html($wpaicg_item['clear_text']) : esc_html__('Clear','gpt3-ai-content-generator');?>"
                            data-stop_text="<?php echo isset($wpaicg_item['stop_text']) && !empty($wpaicg_item['stop_text']) ? esc_html($wpaicg_item['stop_text']) : esc_html__('Stop','gpt3-ai-content-generator');?>"
                            data-cnotice_text="<?php echo isset($wpaicg_item['cnotice_text']) && !empty($wpaicg_item['cnotice_text']) ? esc_html($wpaicg_item['cnotice_text']) : esc_html__('Please register to save your result','gpt3-ai-content-generator');?>"
                            data-download_text="<?php echo isset($wpaicg_item['download_text']) && !empty($wpaicg_item['download_text']) ? esc_html($wpaicg_item['download_text']) : esc_html__('Download','gpt3-ai-content-generator');?>"
                            data-ddownload="<?php echo isset($wpaicg_item['ddownload']) ? esc_html($wpaicg_item['ddownload']) : '';?>"
                            data-copy_text = "<?php echo isset($wpaicg_item['copy_text']) && !empty($wpaicg_item['copy_text']) ? esc_html($wpaicg_item['copy_text']) : esc_html__('Copy','gpt3-ai-content-generator');?>"
                            data-copy_button = "<?php echo isset($wpaicg_item['copy_button']) ? esc_html($wpaicg_item['copy_button']) : 'no';?>"
                            data-feedback_buttons = "<?php echo isset($wpaicg_item['feedback_buttons']) ? esc_html($wpaicg_item['feedback_buttons']) : 'no';?>"
                            class="wpaicg-template-item wpaicg-d-flex wpaicg-align-items-center <?php echo implode(' ',$wpaicg_item_categories)?><?php echo ' user-'.esc_html($wpaicg_item['author'])?><?php echo ' wpaicg-template-item-'.$wpaicg_item['type'].'-'.esc_html($wpaicg_item['id']);?>">
                            <div class="wpaicg-template-icon" style="background: <?php echo esc_html($wpaicg_icon_color)?>"><?php echo wp_kses($wpaicg_icon,$allowed_tags)?></div>
                            <div class="wpaicg-template-content">
                                <strong><?php echo isset($wpaicg_item['title']) && !empty($wpaicg_item['title']) ? esc_html($wpaicg_item['title']) : ''?></strong>
                                <?php
                                if(isset($wpaicg_item['description']) && !empty($wpaicg_item['description'])){
                                    echo '<p>'.esc_html($wpaicg_item['description']).'</p>';
                                }
                                ?>
                            </div>
                            <?php
                            if($wpaicg_item['type'] == 'custom'):
                                ?>
                                <div class="wpaicg-template-action">
                                    <button class="button button-small wpaicg-template-action-duplicate" data-id="<?php echo esc_html($wpaicg_item['id'])?>"><?php echo esc_html__('Duplicate','gpt3-ai-content-generator')?></button>
                                    <button class="button button-small wpaicg-template-action-edit" data-id="<?php echo esc_html($wpaicg_item['id'])?>"><?php echo esc_html__('Edit','gpt3-ai-content-generator')?></button>
                                    <button class="button button-small wpaicg-template-action-delete" data-id="<?php echo esc_html($wpaicg_item['id'])?>"><?php echo esc_html__('Delete','gpt3-ai-content-generator')?></button>
                                </div>
                            <?php
                            endif;
                            ?>
                        </div>
                    <?php
                    endforeach;
                endif;
                ?>
            </div>
            <div class="wpaicg-paginate"></div>
        </div>
    </div>
</div>
<div class="wpaicg-template-modal-content" style="display: none">
    <form method="post" action="">
        <div class="wpaicg-grid-three">
            <div class="wpaicg-grid-2">
                <input type="hidden" class="wpaicg-template-response-type" value="textarea">
                <textarea style="display: none" class="wpaicg-template-title" rows="8"></textarea>
                <div class="wpaicg-form-fields"></div>
                <div class="wpaicg-mb-10">
                    <strong class="wpaicg-template-text-noanswer_text"><?php echo esc_html__('Number of Answers','gpt3-ai-content-generator')?></strong>
                    <select class="wpaicg-template-max-lines">
                        <?php
                        for($i=1;$i<=10;$i++){
                            echo '<option value="'.esc_html($i).'">'.esc_html($i).'</option>';
                        }
                        ?>
                    </select>
                    <button class="button button-primary wpaicg-generate-button wpaicg-template-text-generate_text"><?php echo esc_html__('Generate','gpt3-ai-content-generator')?></button>
                    &nbsp;<button type="button" class="button button-primary wpaicg-template-stop-generate wpaicg-template-text-stop_text" style="display: none"><?php echo esc_html__('Stop','gpt3-ai-content-generator')?></button>
                </div>
                <div class="mb-5">
                    <div class="wpaicg-template-response-editor">
                        <textarea class="wpaicg-template-result" rows="12"></textarea>
                    </div>
                    <div class="wpaicg-template-response-element"></div>
                </div>
                <div class="wpaicg-template-save-result" style="display: none">
                    <button type="button" class="button button-primary wpaicg-template-save-draft wpaicg-template-text-draft_text"><?php echo esc_html__('Save Draft','gpt3-ai-content-generator')?></button>
                    <button type="button" class="button wpaicg-template-clear wpaicg-template-text-clear_text"><?php echo esc_html__('Clear','gpt3-ai-content-generator')?></button>
                    <button type="button" class="button wpaicg-template-download wpaicg-template-text-download_text"><?php echo esc_html__('Download','gpt3-ai-content-generator')?></button>
                    <button type="button" class="button wpaicg-template-copy wpaicg-template-text-copy_text"><?php echo esc_html__('Copy','gpt3-ai-content-generator')?></button>
                </div>
            </div>
            <div class="wpaicg-grid-1">
                <div class="wpaicg-mb-10 wpaicg-template-settings">
                    <button type="button" style="width: 100%" class="button button-primary wpaicg-template-action-customize" data-id="">
                    <?php echo esc_html__('Duplicate This Form','gpt3-ai-content-generator')?></button>
                    <h3><?php echo esc_html__('Settings','gpt3-ai-content-generator')?></h3>
                    <div class="mb-5 wpaicg-template-engine">
                        <strong><?php echo esc_html__('Engine','gpt3-ai-content-generator')?>: </strong>
                        <?php if ($wpaicg_provider == 'OpenAI'): ?>
                            <!-- Display dropdown for OpenAI -->
                            <select name="engine" class="wpaicg-w-100 wpaicg-create-template-engine" required>
                                <optgroup label="GPT-4">
                                    <?php foreach ($gpt4_models as $value => $display_name): ?>
                                        <option value="<?php echo esc_attr($value); ?>"<?php selected($value, $current_model); ?>><?php echo esc_html($display_name); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="GPT-3.5">
                                    <?php foreach ($gpt35_models as $value => $display_name): ?>
                                        <option value="<?php echo esc_attr($value); ?>"<?php selected($value, $current_model); ?>><?php echo esc_html($display_name); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Custom Models">
                                    <?php foreach ($custom_models as $model): ?>
                                        <option value="<?php echo esc_attr($model); ?>"<?php selected($model, $current_model); ?>><?php echo esc_html($model); ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        <?php elseif ($wpaicg_provider == 'Google'): ?>
                            <!-- Display dropdown for Google AI -->
                            <select name="engine" class="wpaicg-w-100 wpaicg-create-template-engine" required>
                                <optgroup label="Google Models">
                                    <?php foreach ($wpaicg_google_model_list as $model): ?>
                                        <?php if (stripos($model, 'vision') !== false): ?>
                                            <!-- Option disabled if it contains the word 'vision' -->
                                            <option value="<?php echo esc_attr($model); ?>" disabled><?php echo esc_html($model); ?></option>
                                        <?php else: ?>
                                            <option value="<?php echo esc_attr($model); ?>"<?php selected($model, $wpaicg_google_default_model); ?>><?php echo esc_html($model); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                        <?php elseif ($wpaicg_provider == 'OpenRouter'): ?>
                            <!-- Display dropdown for OpenRouter -->
                            <?php
                            $openrouter_models = get_option('wpaicg_openrouter_model_list', []);
                            $openrouter_grouped_models = [];
                            foreach ($openrouter_models as $openrouter_model) {
                                $openrouter_provider = explode('/', $openrouter_model['id'])[0];
                                if (!isset($openrouter_grouped_models[$openrouter_provider])) {
                                    $openrouter_grouped_models[$openrouter_provider] = [];
                                }
                                $openrouter_grouped_models[$openrouter_provider][] = $openrouter_model;
                            }
                            ksort($openrouter_grouped_models);

                            ?>
                            <select name="engine" class="wpaicg-w-100 wpaicg-create-template-engine" required>
                                <?php
                                foreach ($openrouter_grouped_models as $openrouter_provider => $openrouter_models): ?>
                                    <optgroup label="<?php echo esc_attr($openrouter_provider); ?>">
                                        <?php
                                        usort($openrouter_models, function($a, $b) {
                                            return strcmp($a["name"], $b["name"]);
                                        });
                                        foreach ($openrouter_models as $openrouter_model): ?>
                                            <option value="<?php echo esc_attr($openrouter_model['id']); ?>"<?php selected($openrouter_model['id'], $wpaicg_openrouter_default_model); ?>><?php echo esc_html($openrouter_model['name']); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <!-- Display readonly text field for AzureAI -->
                            <input type="text" 
                                name="engine" 
                                class="regular-text wpaicg_custom_template_model"
                                readonly
                                value="<?php echo esc_html($azure_deployment_name); ?>"
                            />  
                        <?php endif; ?>

                    </div>
                    <div class="mb-5 wpaicg-template-max_tokens"><strong><?php echo esc_html__('Max Tokens','gpt3-ai-content-generator')?>: </strong><input name="max_tokens" type="text" min="1" max="2048"></div>
                    <div class="mb-5 wpaicg-template-temperature"><strong><?php echo esc_html__('Temperature','gpt3-ai-content-generator')?>: </strong><input name="temperature" type="text" min="0" max="1" step="any"></div>
                    <div class="mb-5 wpaicg-template-top_p"><strong><?php echo esc_html__('Top P','gpt3-ai-content-generator')?>: </strong><input name="top_p" type="text" min="0" max="1"></div>
                    <div class="mb-5 wpaicg-template-best_of"><strong><?php echo esc_html__('Best Of','gpt3-ai-content-generator')?>: </strong><input name="best_of" type="text" min="1" max="20"></div>
                    <div class="mb-5 wpaicg-template-frequency_penalty"><strong><?php echo esc_html__('Frequency Penalty','gpt3-ai-content-generator')?>: </strong><input name="frequency_penalty" type="text" min="0" max="2" step="any"></div>
                    <div class="mb-5 wpaicg-template-presence_penalty"><strong><?php echo esc_html__('Presence Penalty','gpt3-ai-content-generator')?>: </strong><input name="presence_penalty" type="text" min="0" max="2" step="any"></div>
                    <div class="mb-5 wpaicg-template-stop"><strong><?php echo esc_html__('Stop','gpt3-ai-content-generator')?>:<small><?php echo esc_html__('separate by commas','gpt3-ai-content-generator')?></small></strong><input name="stop" type="text"></div>
                    <div class="mb-5 wpaicg-template-estimated"><strong><?php echo esc_html__('Estimated','gpt3-ai-content-generator')?>: </strong><span></span></div>
                    <div class="mb-5 wpaicg-template-post_title"><input type="hidden" name="post_title"></div>
                    <div class="mb-5 wpaicg-template-id"><input type="hidden" name="id"></div>
                    <div class="mb-5 wpaicg-template-sample"><?php echo esc_html__('Sample Response','gpt3-ai-content-generator')?><div class="wpaicg-template-response"></div></div>
                    <div style="padding: 5px;background: #ffc74a;border-radius: 4px;color: #000;" class="wpaicg-template-shortcode"></div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    var qdrantCollections = <?php echo json_encode($qdrant_collections); ?>;
    var qdrantDefaultCollection = "<?php echo esc_js($qdrant_default_collection); ?>";
    var pineconeIndexes = <?php echo json_encode($pineconeindexes); ?>;
    var engineMaxTokens = <?php echo json_encode($engineMaxTokens); ?>;

    jQuery(document).ready(function ($){
        let prompt_id;
        let prompt_name;
        let prompt_response = '';
        let wpaicg_limited_token = false;
        let wp_nonce = '<?php echo esc_html(wp_create_nonce( 'wpaicg-formlog' ))?>'
        /*Modal tab*/
        $(document).on('click','.wpaicg-modal-tabs li', function (e){
            var tab = $(e.currentTarget);
            var target =  tab.attr('data-target');
            var modal = tab.closest('.wpaicg_modal_content');
            modal.find('.wpaicg-modal-tabs li').removeClass('wpaicg-active');
            tab.addClass('wpaicg-active');
            modal.find('.wpaicg-modal-tab').hide();
            modal.find('.wpaicg-modal-tab-'+target).show();
        })
        /*Create Template*/
        var wpaicgTemplateContent = $('.wpaicg-create-template-content');
        var wpaicgTemplageFieldDefault = $('.wpaicg-template-form-field-default');
        var wpaicgCreateField = $('.wpaicg-template-form-field-default');
        var wpaicgFieldInputs = ['label','id','type','min','max','options','rows','cols'];

        // Function to handle export ai forms
        function exportSettings() {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpaicg_export_ai_forms',
                    nonce: '<?php echo wp_create_nonce('wpaicg_export_ai_forms'); ?>'
                },
                success: function(response) {
                    var messageDiv = $('#exportMessage');
                    if (response.success) {
                        // Assuming the response contains a URL to the exported file
                        var downloadLink = '<a href="' + response.data.url + '" download><?php echo esc_html__('Download Exported Forms', 'gpt3-ai-content-generator'); ?></a>';
                        messageDiv.html('<?php echo esc_html__('Export successful.', 'gpt3-ai-content-generator'); ?> ' + downloadLink);
                    } else {
                        messageDiv.html('<?php echo esc_html__('Export failed:', 'gpt3-ai-content-generator'); ?>' + response.data);
                    }
                    messageDiv.show();
                },
                error: function(xhr, status, error) {
                    $('#exportMessage').html('<?php echo esc_html__('An error occurred:', 'gpt3-ai-content-generator'); ?>' + error).show();
                }
            });
        }

        // Attach the exportSettings function to the exportButton's click event
        $('#exportButton').on('click', function() {
            exportSettings();
        });

        // Trigger file input when the Import button is clicked
        $('#importButton').on('click', function(e) {
            e.preventDefault();
            $('#importFileInput').click();
        });

        // Handle file selection
        $('#importFileInput').on('change', function() {
            var file = this.files[0]; // Get the file

            var formData = new FormData();
            formData.append('action', 'wpaicg_import_ai_forms');
            formData.append('nonce', '<?php echo wp_create_nonce('wpaicg_import_ai_forms_nonce'); ?>');
            formData.append('file', file);

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                processData: false, // Important for FormData
                contentType: false, // Important for FormData
                dataType: 'json',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert('Import successful.');
                        location.reload(); // Reload to reflect changes
                    } else {
                        alert('Import failed: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });

        $('#deleteButton').on('click', function() {
            if (confirm('<?php echo esc_js(__('This action will delete all your custom forms. Are you sure?', 'gpt3-ai-content-generator')); ?>')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wpaicg_delete_all_forms',
                        nonce: '<?php echo wp_create_nonce('wpaicg_delete_all_forms_nonce'); ?>'
                    },
                    success: function(response) {
                        // Directly display the message from the backend
                        alert(response.data);
                        if (response.success) {
                            location.reload(); // Optionally reload the page to reflect the changes
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('<?php echo esc_js(__('An error occurred:', 'gpt3-ai-content-generator')); ?>' + error);
                    }
                });
            }
        });

        $(document).on('click','.wpaicg-template-icons span', function (e){
            var icon = $(e.currentTarget);
            icon.parent().find('span').removeClass('icon_selected');
            icon.addClass('icon_selected');
            icon.parent().parent().find('.wpaicg-create-template-icon').val(icon.attr('data-key'));
        });
        $(document).on('click','.wpaicg-field-delete', function(e){
            $(e.currentTarget).parent().remove();
            wpaicgSortField();
        });
        function wpaicgSortField(){
            $('.wpaicg-create-template-form .wpaicg-template-form-field').each(function(idx, item){
                $.each(wpaicgFieldInputs, function(idxy, field){
                    $(item).find('.wpaicg-create-template-field-'+field).attr('name','fields['+idx+']['+field+']');
                });
            })
        }

        $(document).on('change', '.wpaicg-create-template-selected_embedding_model', function() {
            var selectedOption = $(this).find('option:selected');
            var provider = selectedOption.data('provider');
            $('#selected_embedding_provider').val(provider);
        });

        // Handle change event for the 'use_default_embedding_model' dropdown
        $(document).on('change', 'select[name="use_default_embedding_model"]', function() {
            var selectedOption = $(this).val();
            var selected_embedding_model_container = $('.wpaicg-context-selected_embedding_model');

            if (selectedOption === 'no') {
                selected_embedding_model_container.show();
            } else {
                selected_embedding_model_container.hide();
            }
        });

        // if embeddings is yes then display the vector db section
        $(document).on('change', '.wpaicg-create-template-embeddings', function(e) {
            var embeddings = $(e.currentTarget).val();
            // wpaicg-vectordb-container
            var vectorDBSection = $('.wpaicg-vectordb-container');
            var suffixTextContainer = $('.wpaicg-context-suffix');
            var suffixPositionContainer = $('.wpaicg-context-suffix-position');
            var embeddingsLimitContainer = $('.wpaicg-embeddings-limit');
            // use_default_embedding_model
            var use_default_embedding_model = $('.wpaicg-context-use_default_embedding_model');
            var selected_embedding_model_container = $('.wpaicg-context-selected_embedding_model');

            if (embeddings === 'yes') {
                vectorDBSection.show(); // Show the Vector DB section
                $('.wpaicg-create-template-vectordb').trigger('change');
            } else {
                vectorDBSection.hide(); // Hide the Vector DB section
                $('.wpaicg-collections-dropdown').hide();
                $('.wpaicg-collections-dropdown').next('p').remove();
                $('.wpaicg-pineconeindexes-dropdown').hide();
                $('.wpaicg-pineconeindexes-dropdown').next('p').remove();
                suffixTextContainer.hide(); // Hide the context suffix
                suffixPositionContainer.hide(); // Hide the context suffix position
                embeddingsLimitContainer.hide(); // Hide the embeddings limit
                use_default_embedding_model.hide(); // Hide the default embedding model
                selected_embedding_model_container.hide();
            }
        });

        // Change event for vectordb selection to show/hide collections dropdown
        $(document).on('change', '.wpaicg-create-template-vectordb', function() {
            var vectordb = $(this).val();
            var collectionsDropdownContainer = $('.wpaicg-collections-dropdown');
            var collectionsDropdown = $('.wpaicg-create-template-collections');
            var pineconeIndexesContainer = $('.wpaicg-pineconeindexes-dropdown');
            var indexDropdown = $('.wpaicg-create-template-pineconeindexes');
            var suffixTextContainer = $('.wpaicg-context-suffix');
            var suffixPositionContainer = $('.wpaicg-context-suffix-position');
            var embeddingsLimitContainer = $('.wpaicg-embeddings-limit');
            var use_default_embedding_model = $('.wpaicg-context-use_default_embedding_model');
            var selected_embedding_model_container = $('.wpaicg-context-selected_embedding_model');
            // get use_default_embedding_model value using find and parent
            var useDefaultModel = $(this).parent().parent().find('select[name="use_default_embedding_model"]').val();
            
            // Define a message for no collections or indexes
            var noCollectionsMessage = '<p class="wpaicg-no-items-message"><?php echo esc_html__('No collections available', 'gpt3-ai-content-generator'); ?></p>';
            var noIndexesMessage = '<p class="wpaicg-no-items-message"><?php echo esc_html__('No indexes available', 'gpt3-ai-content-generator'); ?></p>';

            // Remove any existing no collections/indexes message
            $('.wpaicg-no-items-message').remove();
            if (vectordb === 'qdrant' && qdrantCollections.length > 0) {
                collectionsDropdown.empty(); // Clear existing options

                // Populate the collections dropdown
                $.each(qdrantCollections, function(index, collection) {
                    var name, dimension, displayName, selectedAttribute;
                    // Check if the collection is an object (new structure) or just a string (old structure)
                    if (typeof collection === 'object' && collection.name) {
                        name = collection.name;
                        dimension = collection.dimension ? ' (' + collection.dimension + ')' : '';
                        displayName = name + dimension;
                    } else {
                        // Handle as a string (old structure)
                        name = collection;
                        displayName = collection;
                    }
                    selectedAttribute = (name === qdrantDefaultCollection) ? ' selected' : '';
                    collectionsDropdown.append('<option value="' + name + '"' + selectedAttribute + '>' + displayName + '</option>');
                });

                collectionsDropdownContainer.show(); // Show the Collections dropdown
                pineconeIndexesContainer.hide(); // Hide the Pinecone Indexes dropdown
                // show the context suffix
                suffixTextContainer.show();
                suffixPositionContainer.show();
                embeddingsLimitContainer.show(); // Show the embeddings limit
                use_default_embedding_model.show(); // Show the default embedding model
                if (useDefaultModel === 'no') {
                    selected_embedding_model_container.show();
                } else {
                    selected_embedding_model_container.hide();
                }
            } else if (vectordb === 'pinecone' && pineconeIndexes.length > 0) {
                indexDropdown.empty(); // Clear existing options
                // Populate the Pinecone Indexes dropdown
                $.each(pineconeIndexes, function(index, item) {
                    indexDropdown.append('<option value="' + item.url + '">' + item.name + '</option>');
                });

                pineconeIndexesContainer.show(); // Show the Pinecone Indexes dropdown
                collectionsDropdownContainer.hide(); // Hide the Collections dropdown
                // show the context suffix
                suffixTextContainer.show();
                suffixPositionContainer.show();
                embeddingsLimitContainer.show(); // Show the embeddings limit
                use_default_embedding_model.show(); // Show the default embedding model
                if (useDefaultModel === 'no') {
                    selected_embedding_model_container.show();
                } else {
                    selected_embedding_model_container.hide();
                }
            } else {
                collectionsDropdownContainer.hide(); // Hide the Collections dropdown
                pineconeIndexesContainer.hide(); // Hide the Pinecone Indexes dropdown
                suffixTextContainer.hide(); // Hide the context suffix
                suffixPositionContainer.hide(); // Hide the context suffix position
                embeddingsLimitContainer.hide(); // Hide the embeddings limit
                use_default_embedding_model.hide(); // Hide the default embedding model
                selected_embedding_model_container.hide();
                // Display message if no collections or indexes are available
                if (vectordb === 'qdrant') {
                    collectionsDropdownContainer.after(noCollectionsMessage);
                } else if (vectordb === 'pinecone') {
                    pineconeIndexesContainer.after(noIndexesMessage);
                }
            }
        });
        
        // Trigger the change event on page load to set the initial visibility of the Vector DB section
        $('.wpaicg-create-template-embeddings').trigger('change');
        // Initially trigger vectordb change to apply logic based on default selected value
        $('.wpaicg-create-template-vectordb').trigger('change');
        // Initial call to update the provider when the page loads or when modal content is dynamically loaded
        $('.wpaicg-create-template-selected_embedding_model').trigger('change');

        $(document).on('click','.wpaicg-create-form-field', function(e){
            $('.wpaicg-create-template-form .wpaicg-template-fields').append(wpaicgCreateField.html());
            wpaicgSortField();
        });
        $(document).on('change','.wpaicg-create-template-field-type', function(e){
            var type = $(e.currentTarget).val();
            var parentEl = $(e.currentTarget).closest('.wpaicg-template-form-field');
            if(type === 'select' || type === 'checkbox' || type === 'radio'){
                parentEl.find('.wpaicg-create-template-field-options-main').show();
                parentEl.find('.wpaicg-create-template-field-min-main').hide();
                parentEl.find('.wpaicg-create-template-field-max-main').hide();
                parentEl.find('.wpaicg-create-template-field-rows-main').hide();
                parentEl.find('.wpaicg-create-template-field-cols-main').hide();
            }
            else if(type === 'textarea'){
                parentEl.find('.wpaicg-create-template-field-rows-main').show();
                parentEl.find('.wpaicg-create-template-field-cols-main').show();
                parentEl.find('.wpaicg-create-template-field-options-main').hide();
                parentEl.find('.wpaicg-create-template-field-min-main').show();
                parentEl.find('.wpaicg-create-template-field-max-main').show();
            }
            else{
                parentEl.find('.wpaicg-create-template-field-rows-main').hide();
                parentEl.find('.wpaicg-create-template-field-cols-main').hide();
                parentEl.find('.wpaicg-create-template-field-options-main').hide();
                parentEl.find('.wpaicg-create-template-field-min-main').show();
                parentEl.find('.wpaicg-create-template-field-max-main').show();
            }
        })
        $('.wpaicg-create-template').click(function (){
            $('.wpaicg_modal_content').empty();
            $('.wpaicg_modal_title').html('<?php echo esc_html__('Design Your Form','gpt3-ai-content-generator')?>');
            $('.wpaicg_modal_content').html('<form action="" method="post" class="wpaicg-create-template-form">'+wpaicgTemplateContent.html()+'</form>');
            $('.wpaicg-create-template-form .wpaicg-create-template-color').wpColorPicker();
            $('.wpaicg-create-template-form .wpaicg-create-template-bgcolor').wpColorPicker();
            $('.wpaicg-create-template-form .wpaicg-create-template-category').val('generation');
            $('.wpaicg-create-template-form .wpaicg-template-icons span[data-key=robot]').addClass('icon_selected');
            $('.wpaicg-overlay').show();
            $('.wpaicg_modal').show();
        })
        $(document).on('click','.wpaicg-template-item .wpaicg-template-action-delete',function (e){
            var id = $(e.currentTarget).attr('data-id');
            var conf = confirm('<?php echo esc_html__('Are you sure?','gpt3-ai-content-generator')?>');
            if(conf){
                $('.wpaicg-template-item-custom-'+id).remove();
                $.post('<?php echo admin_url('admin-ajax.php')?>', {action: 'wpaicg_template_delete', id: id,'nonce': '<?php echo wp_create_nonce('wpaicg-ajax-nonce')?>'});
            }
        });
        $(document).on('click','.wpaicg-template-item .wpaicg-template-action-edit',function (e){
            var id = $(e.currentTarget).attr('data-id');
            var item = $('.wpaicg-template-item-custom-'+id);
            $('.wpaicg_modal_content').empty();
            $('.wpaicg_modal_title').html('<?php echo esc_html__('Edit your Template','gpt3-ai-content-generator')?>');
            $('.wpaicg_modal_content').html('<form action="" method="post" class="wpaicg-create-template-form">'+wpaicgTemplateContent.html()+'</form>');
            var form = $('.wpaicg-create-template-form');
            var wpaicg_template_keys = ['engine','editor','title','description','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','prompt','response','category','icon','color','bgcolor','header','embeddings','vectordb','collections','pineconeindexes','suffix_text','embeddings_limit','use_default_embedding_model','selected_embedding_model','selected_embedding_provider','suffix_position','dans','ddraft','dclear','dnotice','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text','download_text','ddownload','copy_button','copy_text','feedback_buttons'];
            for(var i = 0; i < wpaicg_template_keys.length;i++){
                var wpaicg_template_key = wpaicg_template_keys[i];
                var wpaicg_template_key_value = item.attr('data-'+wpaicg_template_key);
                form.find('.wpaicg-create-template-'+wpaicg_template_key).val(wpaicg_template_key_value);
                if(wpaicg_template_key === 'icon'){
                    if(wpaicg_template_key_value === ''){
                        wpaicg_template_key_value = 'robot';
                    }
                    $('.wpaicg-create-template-form .wpaicg-template-icons span[data-key='+wpaicg_template_key_value+']').addClass('icon_selected');
                }
            }
            var wpaicg_form_fields = item.attr('data-fields');
            if(wpaicg_form_fields !== '') {
                wpaicg_form_fields = wpaicg_form_fields.replace(/\\/g,'');
                wpaicg_form_fields = JSON.parse(wpaicg_form_fields);
                $.each(wpaicg_form_fields, function(idx, field){
                    $('.wpaicg-create-template-form .wpaicg-template-fields').append('<div id="wpaicg-template-form-field-'+idx+'">'+wpaicgCreateField.html()+'</div>');
                    var field_item = $('#wpaicg-template-form-field-'+idx);
                    $.each(wpaicgFieldInputs, function(idxy, field_key){
                        if(field[field_key] !== undefined) {
                            var field_value = field[field_key];
                            field_value = field_value.replaceAll('[QUOTE]','"');
                            field_item.find('.wpaicg-create-template-field-' + field_key).val(field_value);
                            if(field_key === 'type' && (field_value === 'select' || field_value === 'checkbox' || field_value === 'radio')){
                                field_item.find('.wpaicg-create-template-field-options-main').show();
                                field_item.find('.wpaicg-create-template-field-min-main').hide();
                                field_item.find('.wpaicg-create-template-field-max-main').hide();
                            }
                            else if(field_key === 'type' && field_value === 'textarea'){
                                field_item.find('.wpaicg-create-template-field-rows-main').show();
                                field_item.find('.wpaicg-create-template-field-cols-main').show();
                            }
                        }
                    });
                    wpaicgSortField();
                })
            }
            form.find('.wpaicg-create-template-id').val(id);
            // Retrieve the saved collection for the item being edited
            var savedCollection = item.attr('data-collections');
            // Retrieve the saved pinecone index for the item being edited
            var savedPineconeIndex = item.attr('data-pineconeindexes');

            // if embeddings is yes then display the vector db section
            if (form.find('.wpaicg-create-template-embeddings').val() === 'yes') {
                form.find('.wpaicg-vectordb-container').show();
                if (form.find('.wpaicg-create-template-vectordb').val() === 'qdrant') {
                    // Populate the collections dropdown
                    form.find('.wpaicg-create-template-collections').empty(); // Clear existing options

                    $.each(qdrantCollections, function(index, collection) {
                        var name, dimension, displayName, selectedAttribute;
                        if (typeof collection === 'object' && collection.name) {
                            name = collection.name;
                            dimension = collection.dimension ? ' (' + collection.dimension + ')' : '';
                            displayName = name + dimension;
                        } else {
                            name = collection; // For older structure where collection is just a string
                            displayName = collection;
                        }
                        selectedAttribute = (name === savedCollection) ? ' selected' : '';
                        form.find('.wpaicg-create-template-collections').append('<option value="' + name + '"' + selectedAttribute + '>' + displayName + '</option>');
                    });
                    form.find('.wpaicg-collections-dropdown').show(); // Show the Collections dropdown
                    // hide the pinecone indexes dropdown
                    form.find('.wpaicg-pineconeindexes-dropdown').hide();
                    // display the context suffix
                    form.find('.wpaicg-context-suffix').show();
                    // display the context suffix position
                    form.find('.wpaicg-context-suffix-position').show();
                    // display wpaicg-embeddings-limit
                    form.find('.wpaicg-embeddings-limit').show();
                    // display the default embedding model
                    form.find('.wpaicg-context-use_default_embedding_model').show();
                    // display the selected embedding model
                    if (form.find('.wpaicg-create-template-use_default_embedding_model').val() === 'no') {
                        form.find('.wpaicg-context-selected_embedding_model').show();
                    } else {
                        form.find('.wpaicg-context-selected_embedding_model').hide();
                    }

                } else if (form.find('.wpaicg-create-template-vectordb').val() === 'pinecone') {
                    // Populate the Pinecone Indexes dropdown
                    form.find('.wpaicg-create-template-pineconeindexes').empty(); // Clear existing options

                    $.each(pineconeIndexes, function(index, item) {
                        // set the selected attribute based on whether this index matches the saved state
                        var isSelected = (item.url === savedPineconeIndex) ? ' selected' : '';
                        form.find('.wpaicg-create-template-pineconeindexes').append('<option value="' + item.url + '"' + isSelected + '>' + item.name + '</option>');
                    });
                    form.find('.wpaicg-pineconeindexes-dropdown').show(); // Show the Pinecone Indexes dropdown
                    // hide the collections dropdown
                    form.find('.wpaicg-collections-dropdown').hide();
                    // display the context suffix
                    form.find('.wpaicg-context-suffix').show();
                    // display the context suffix position
                    form.find('.wpaicg-context-suffix-position').show();
                    // display wpaicg-embeddings-limit
                    form.find('.wpaicg-embeddings-limit').show();
                    // display the default embedding model
                    form.find('.wpaicg-context-use_default_embedding_model').show();
                    // display the selected embedding model
                    if (form.find('.wpaicg-create-template-use_default_embedding_model').val() === 'no') {
                        form.find('.wpaicg-context-selected_embedding_model').show();
                    } else {
                        form.find('.wpaicg-context-selected_embedding_model').hide();
                    }
                }
                else {
                    form.find('.wpaicg-collections-dropdown').hide(); // Hide the Collections dropdown
                    form.find('.wpaicg-pineconeindexes-dropdown').hide(); // Hide the Pinecone Indexes dropdown
                    form.find('.wpaicg-context-suffix').hide(); // Hide the context suffix
                    form.find('.wpaicg-context-suffix-position').hide(); // Hide the context suffix position
                    form.find('.wpaicg-embeddings-limit').hide(); // Hide the embeddings limit
                    // hide the default embedding model
                    form.find('.wpaicg-context-use_default_embedding_model').hide();
                    form.find('.wpaicg-context-selected_embedding_model').hide();
                }

            } else {
                form.find('.wpaicg-vectordb-container').hide();
                form.find('.wpaicg-collections-dropdown').hide(); // Hide the Collections dropdown
                form.find('.wpaicg-pineconeindexes-dropdown').hide(); // Hide the Pinecone Indexes dropdown
                form.find('.wpaicg-context-suffix').hide(); // Hide the context suffix
                form.find('.wpaicg-context-suffix-position').hide(); // Hide the context suffix position
                form.find('.wpaicg-embeddings-limit').hide(); // Hide the embeddings limit
                // hide the default embedding model
                form.find('.wpaicg-context-use_default_embedding_model').hide();
                form.find('.wpaicg-context-selected_embedding_model').hide();
            }

            $('.wpaicg-create-template-form .wpaicg-create-template-color').wpColorPicker();
            $('.wpaicg-create-template-form .wpaicg-create-template-bgcolor').wpColorPicker();
            //$('.wpaicg_modal').css('height','60%');
            $('.wpaicg-overlay').show();
            $('.wpaicg_modal').show();
        });

        $(document).on('click','.wpaicg-template-action-customize',function (e){
            var id = $(e.currentTarget).attr('data-id');
            var item = $('.wpaicg-template-item-json-'+id);
            $('.wpaicg_modal_content').empty();
            $('.wpaicg_modal_title').html('<?php echo esc_html__('Customize your Template','gpt3-ai-content-generator')?>');
            $('.wpaicg_modal_content').html('<form action="" method="post" class="wpaicg-create-template-form">'+wpaicgTemplateContent.html()+'</form>');
            var form = $('.wpaicg-create-template-form');
            var wpaicg_template_keys = ['engine','editor','title','description','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','prompt','response','category','icon','color','bgcolor','header','embeddings','use_default_embedding_model','selected_embedding_model','selected_embedding_provider','vectordb','collections','pineconeindexes','suffix_text','suffix_position','embeddings_limit','dans','ddraft','dclear','dnotice','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text','download_text','ddownload','copy_button','copy_text','feedback_buttons'];
            for(var i = 0; i < wpaicg_template_keys.length;i++){
                var wpaicg_template_key = wpaicg_template_keys[i];
                var wpaicg_template_key_value = item.attr('data-'+wpaicg_template_key);
                if(wpaicg_template_key === 'category' && wpaicg_template_key_value !== ''){
                    if(wpaicg_template_key_value.indexOf(',') > -1){
                        wpaicg_template_key_value = wpaicg_template_key_value.split(',')[0];
                    }
                }
                form.find('.wpaicg-create-template-'+wpaicg_template_key).val(wpaicg_template_key_value);
                if(wpaicg_template_key === 'icon'){
                    if(wpaicg_template_key_value === ''){
                        wpaicg_template_key_value = 'robot';
                    }
                    $('.wpaicg-create-template-form .wpaicg-template-icons span[data-key='+wpaicg_template_key_value+']').addClass('icon_selected');
                }
            }
            var wpaicg_form_fields = item.attr('data-fields');
            if(wpaicg_form_fields !== '') {
                wpaicg_form_fields = wpaicg_form_fields.replace(/\\/g,'');
                wpaicg_form_fields = JSON.parse(wpaicg_form_fields);
                $.each(wpaicg_form_fields, function(idx, field){
                    $('.wpaicg-create-template-form .wpaicg-template-fields').append('<div id="wpaicg-template-form-field-'+idx+'">'+wpaicgCreateField.html()+'</div>');
                    var field_item = $('#wpaicg-template-form-field-'+idx);
                    $.each(wpaicgFieldInputs, function(idxy, field_key){
                        if(field[field_key] !== undefined) {
                            var field_value = field[field_key];
                            if(field_key === 'options'){
                                if(typeof field_value === 'object'){
                                    field_value = field_value.join('|');
                                }
                                field_item.find('.wpaicg-create-template-field-' + field_key).val(field_value);
                            }
                            else {
                                field_item.find('.wpaicg-create-template-field-' + field_key).val(field_value);
                            }
                            if(field_key === 'type' && (field_value === 'select' || field_value === 'checkbox' || field_value === 'radio')){
                                field_item.find('.wpaicg-create-template-field-options-main').show();
                                field_item.find('.wpaicg-create-template-field-min-main').hide();
                                field_item.find('.wpaicg-create-template-field-max-main').hide();
                            }
                            else if(field_key === 'type' && field_value === 'textarea'){
                                field_item.find('.wpaicg-create-template-field-rows-main').show();
                                field_item.find('.wpaicg-create-template-field-cols-main').show();
                            }
                        }
                    });
                    wpaicgSortField();
                })
            }
            $('.wpaicg-create-template-form .wpaicg-create-template-color').wpColorPicker();
            $('.wpaicg-create-template-form .wpaicg-create-template-bgcolor').wpColorPicker();
            //$('.wpaicg_modal').css('height','60%');
            $('.wpaicg-overlay').show();
            $('.wpaicg_modal').show();
        });
        $(document).on('submit','.wpaicg-create-template-form', function(e){
            e.preventDefault(); // Prevent the form from submitting for debugging
            var form = $(e.currentTarget);
            var selected_embedding_provider = $('#selected_embedding_provider').val(); // Fetch the value directly from the input
            var btn = form.find('.wpaicg-create-template-save');
            var engine = form.find('.wpaicg-create-template-engine').val();
            var useDefault = form.find('.wpaicg-create-template-use_default_embedding_model').val();
    
            if (useDefault === 'yes') {
                // Clear values if the default use is selected
                form.find('.wpaicg-create-template-selected_embedding_provider').val('');
                form.find('.wpaicg-create-template-selected_embedding_model').val('');
            } else {
                // Determine the provider based on the model if not using default
                var selected_embedding_model = form.find('.wpaicg-create-template-selected_embedding_model').val();
                var provider;
                if (selected_embedding_model === 'embedding-001' || selected_embedding_model === 'text-embedding-004') {
                    provider = 'Google';
                } else if (selected_embedding_model === 'text-embedding-3-small' || 
                        selected_embedding_model === 'text-embedding-3-large' || 
                        selected_embedding_model === 'text-embedding-ada-002') {
                    provider = 'OpenAI';
                } else {
                    provider = 'Azure';
                }
                
                // Update the hidden input with the provider
                form.find('.wpaicg-create-template-selected_embedding_provider').val(provider);
            }

            // Fetch context size based on the selected engine, if it is a custom model, use the default value: 4096.
            var maxValidToken = engineMaxTokens[engine] || 4096;

            var max_tokens = form.find('.wpaicg-create-template-max_tokens').val();
            var temperature = form.find('.wpaicg-create-template-temperature').val();
            var top_p = form.find('.wpaicg-create-template-top_p').val();
            var best_of = form.find('.wpaicg-create-template-best_of').val();
            var frequency_penalty = form.find('.wpaicg-create-template-frequency_penalty').val();
            var presence_penalty = form.find('.wpaicg-create-template-presence_penalty').val();
            var error_message = false;
            var data = form.serialize();
            
            if (max_tokens !== '' && (parseFloat(max_tokens) < 1 || parseFloat(max_tokens) > maxValidToken)) {
                var baseErrorMessage = '<?php echo esc_html__("Please enter a valid max token value between 1 and", "gpt3-ai-content-generator")?>';
                error_message = baseErrorMessage + ' ' + maxValidToken + '.';
            }
            else if(temperature !== '' && (parseFloat(temperature) < 0 || parseFloat(temperature) > 1)){
                error_message = '<?php echo sprintf(esc_html__('Please enter a valid temperature value between %d and %d.','gpt3-ai-content-generator'),0,1)?>';
            }
            else if(top_p !== '' && (parseFloat(top_p) < 0 || parseFloat(top_p) > 1)){
                error_message = '<?php echo sprintf(esc_html__('Please enter a valid top p value between %d and %d.','gpt3-ai-content-generator'),0,1)?>';
            }
            else if(best_of !== '' && (parseFloat(best_of) < 1 || parseFloat(best_of) > 20)){
                error_message = '<?php echo sprintf(esc_html__('Please enter a valid best of value between %d and %d.','gpt3-ai-content-generator'),1,20)?>';
            }
            else if(frequency_penalty !== '' && (parseFloat(frequency_penalty) < 0 || parseFloat(frequency_penalty) > 2)){
                error_message = '<?php echo sprintf(esc_html__('Please enter a valid frequency penalty value between %d and %d.','gpt3-ai-content-generator'),0,2)?>';
            }
            else if(presence_penalty !== '' && (parseFloat(presence_penalty) < 0 || parseFloat(presence_penalty) > 2)){
                error_message = '<?php echo sprintf(esc_html__('Please enter a valid presence penalty value between %d and %d.','gpt3-ai-content-generator'),0,2)?>';
            }
            if(error_message){
                alert(error_message);
            }
            else{
                if(form.find('.wpaicg-template-form-field').length){
                    var wpaicgFieldID = [];
                    form.find('.wpaicg-template-form-field').each(function (idx, item){
                        var field_id = $(item).find('.wpaicg-create-template-field-id').val();
                        if(field_id !== ''){
                            if($.inArray(field_id,wpaicgFieldID) > -1){
                                error_message = '<?php echo esc_html__('Please insert unique ID','gpt3-ai-content-generator')?>';
                            }
                            else{
                                wpaicgFieldID.push(field_id)
                            }
                        }
                    })
                }
                if(error_message){
                    alert(error_message)
                }
                else {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php')?>',
                        data: data,
                        dataType: 'JSON',
                        type: 'POST',
                        beforeSend: function () {
                            wpaicgLoading(btn)
                        },
                        success: function (res) {
                            wpaicgRmLoading(btn);
                            if (res.status === 'success') {
                                window.location.href = '<?php echo admin_url('admin.php?page=wpaicg_forms&update_template=')?>' + res.id;
                            } else {
                                alert(res.msg)
                            }
                        },
                        error: function () {
                            wpaicgRmLoading(btn);
                            alert('<?php echo esc_html__('Something went wrong','gpt3-ai-content-generator')?>');
                        }
                    })
                }
            }
            return false;
        })
        /*End create*/
        var wpaicgNumberParse = 3;
        if($(window).width() < 900){
            wpaicgNumberParse = 2;
        }
        if($(window).width() < 480){
            wpaicgNumberParse = 1;
        }
        var wpaicg_per_page = <?php echo esc_html($wpaicg_per_page);?>;
        var wpaicg_count_templates = <?php echo esc_html(count($wpaicg_items))?>;
        $('.wpaicg-list input').on('change',function (){
            wpaicgTemplatesFilter();
        });
        var wpaicgTemplateItem = $('.wpaicg-template-item');
        var wpaicgTemplateSearch = $('.wpaicg-template-search');
        var wpaicgTemplateItems = $('.wpaicg-template-items');
        var wpaicgTemplateSettings = ['engine','max_tokens','temperature','top_p','embeddings','vectordb','collections','pineconeindexes','suffix_text','suffix_position','embeddings_limit','best_of','frequency_penalty','presence_penalty','stop','post_title','use_default_embedding_model','selected_embedding_model','selected_embedding_provider','id','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text','download_text','copy_text'];
        var wpaicgTemplateDefaultContent = $('.wpaicg-template-modal-content');
        var wpaicgTemplateEditor = false;
        var eventGenerator = false;
        wpaicgTemplateSearch.on('input', function (){
            wpaicgTemplatesFilter();
        });
        function wpaicgTemplatesFilter(){
            var categories = [];
            var users = [];
            var filterClasses = [];
            $('.wpaicg-categories input').each(function (idx, item){
                if($(item).prop('checked')){
                    categories.push($(item).val());
                    filterClasses.push($(item).val());
                }
            });
            $('.wpaicg-authors input').each(function (idx, item){
                if($(item).prop('checked')){
                    users.push('user-'+$(item).val());
                    filterClasses.push('user-'+$(item).val());
                }
            });
            var search = wpaicgTemplateSearch.val();
            wpaicgTemplateItem.each(function (idx, item){
                var show = false;
                var item_title = $(item).attr('data-title');
                var item_desc = $(item).attr('data-desc');
                if(categories.length){
                    for(var i=0;i<categories.length;i++){
                        if($(item).hasClass(categories[i])){
                            show = true;
                            break;
                        }
                        else{
                            show = false;
                        }
                    }
                    if(show && users.length){
                        for(var i=0;i<users.length;i++){
                            if($(item).hasClass(users[i])){
                                show = true;
                                break;
                            }
                            else{
                                show = false;
                            }
                        }
                    }
                }
                if(users.length){
                    for(var i=0;i<users.length;i++){
                        if($(item).hasClass(users[i])){
                            show = true;
                            break;
                        }
                        else{
                            show = false;
                        }
                    }
                    if(show && categories.length){
                        for(var i=0;i<categories.length;i++){
                            if($(item).hasClass(categories[i])){
                                show = true;
                                break;
                            }
                            else{
                                show = false;
                            }
                        }
                    }
                }
                if(!users.length && !categories.length){
                    show = true;
                }
                if(search !== '' && show){
                    search = search.toLowerCase();
                    item_title = item_title.toLowerCase();
                    item_desc = item_desc.toLowerCase();
                    if(item_title.indexOf(search) === -1 && item_desc.indexOf(search) === -1){
                        show = false;
                    }
                }
                if(show){
                    $(item).show();
                }
                else{
                    $(item).hide();
                }
            });
            wpaicgTemplatePagination();
        }
        wpaicgTemplatePagination();
        function wpaicgTemplatePagination(){
            wpaicgTemplateItem.removeClass('disappear-item');
            var number_rows = 0 ;
            wpaicgTemplateItem.each(function (idx, item){
                if($(item).is(':visible')){
                    number_rows++;
                }
            });
            $('.wpaicg-paginate').empty();
            if(number_rows > wpaicg_per_page){
                var  totalPage = Math.ceil(number_rows/wpaicg_per_page);
                for(var i=1;i <=totalPage;i++){
                    var classSpan = 'page-numbers';
                    if(i === 1){
                        classSpan = 'page-numbers current';
                    }
                    $('.wpaicg-paginate').append('<span class="'+classSpan+'" data-page="'+i+'">'+i+'</span>');
                }
            }
            var rowDisplay = 0;
            wpaicgTemplateItem.each(function (idx, item){
                if($(item).is(':visible')){
                    rowDisplay += 1;
                }
            });
            if(rowDisplay > wpaicg_per_page) {
                wpaicgTemplateItems.css('height', ((Math.ceil(wpaicg_per_page/wpaicgNumberParse) * 120) - 20) + 'px');
            }
            else{
                wpaicgTemplateItems.css('height', ((Math.ceil(rowDisplay/wpaicgNumberParse) * 120) - 20) + 'px');
            }
        }

        $(document).on('click','.wpaicg-paginate span:not(.current)', function (e){
            var btn = $(e.currentTarget);
            var page = parseInt(btn.attr('data-page'));
            $('.wpaicg-paginate span').removeClass('current');
            btn.addClass('current');
            var prevpage = page-1;
            var startRow = prevpage*wpaicg_per_page;
            var endRow = startRow+wpaicg_per_page;
            var keyRow = 0;
            var rowDisplay = 0;
            wpaicgTemplateItem.each(function (idx, item){
                if($(item).is(':visible')){
                    keyRow += 1;
                    if(keyRow > startRow && keyRow <= endRow){
                        rowDisplay += 1;
                        $(item).removeClass('disappear-item');
                    }
                    else{
                        $(item).addClass('disappear-item');
                    }
                }
            });
            wpaicgTemplateItems.css('height',((Math.ceil(rowDisplay/wpaicgNumberParse)*120)- 20)+'px');
        });
        $('.wpaicg_modal_close').click(function (){
            $('.wpaicg_modal_close').closest('.wpaicg_modal').hide();
            $('.wpaicg_modal_close').closest('.wpaicg_modal').removeClass('wpaicg-small-modal');
            $('.wpaicg-overlay').hide();
            if(eventGenerator){
                eventGenerator.close();
            }
        });
        var wpaicgEditorNumber;
        $(document).on('click','.wpaicg-template-form .wpaicg-template-save-draft', function(e){
            e.preventDefault();
            var btn = $(this);
            var response_type = $('.wpaicg-template-form .wpaicg-template-response-type').val();
            var post_content = '';
            if(response_type === 'textarea') {
                var basicEditor = true;
                var btn = $(e.currentTarget);
                var editor = tinyMCE.get('editor-' + wpaicgEditorNumber);
                if ($('#wp-editor-' + wpaicgEditorNumber + '-wrap').hasClass('tmce-active') && editor) {
                    basicEditor = false;
                }
                if (basicEditor) {
                    post_content = $('#editor-' + wpaicgEditorNumber).val();
                } else {
                    post_content = editor.getContent();
                }
            }
            else{
                post_content = $('.wpaicg-template-response-element').html();
            }
            var post_title = $('.wpaicg-template-form .wpaicg-template-post_title input').val();
            var id = $('.wpaicg-template-form .wpaicg-create-template-id').val();
            if(post_content !== ''){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: {title: post_title, id, content: post_content, action: 'wpaicg_save_draft_post_extra',save_source: 'promptbase','nonce': '<?php echo wp_create_nonce('wpaicg-ajax-nonce')?>'},
                    dataType: 'json',
                    type: 'POST',
                    beforeSend: function (){
                        wpaicgLoading(btn);
                    },
                    success: function (res){
                        wpaicgRmLoading(btn);
                        if(res.status === 'success'){
                            window.location.href = '<?php echo admin_url('post.php')?>?post='+res.id+'&action=edit';
                        }
                        else{
                            alert(res.msg);
                        }
                    },
                    error: function (){
                        wpaicgRmLoading(btn);
                        alert('<?php echo esc_html__('Something went wrong','gpt3-ai-content-generator')?>');
                    }
                });
            }
            else{
                alert('<?php echo esc_html__('Please wait content generated','gpt3-ai-content-generator')?>');
            }

        });
        $(document).on('click','.wpaicg-template-item .wpaicg-template-action-duplicate',function (e){
            var id = $(e.currentTarget).attr('data-id');
            var btn = $(e.currentTarget);
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php')?>',
                data: {action: 'wpaicg_form_duplicate',id: id,nonce:'<?php echo wp_create_nonce('wpaicg-ajax-nonce')?>'},
                dataType: 'JSON',
                type:'POST',
                beforeSend: function(){
                    wpaicgLoading(btn);
                },
                success: function(res){
                    window.location.reload();
                }
            });
        });
        $(document).on('click','.wpaicg-template-form .wpaicg-template-clear', function(e){
            var response_type = $('.wpaicg-template-form .wpaicg-template-response-type').val();
            if(response_type === 'textarea') {
                var basicEditor = true;
                var editor = tinyMCE.get('editor-' + wpaicgEditorNumber);
                if ($('#wp-editor-' + wpaicgEditorNumber + '-wrap').hasClass('tmce-active') && editor) {
                    basicEditor = false;
                }
                if (basicEditor) {
                    $('#editor-' + wpaicgEditorNumber).val('');
                } else {
                    editor.setContent('');
                }
            }
            else{
                $('.wpaicg-template-response-element').empty();
            }
            $('.wpaicg-template-form .wpaicg-template-save-result').hide();
        });
        $(document).on('click','.wpaicg-template-download', function(e){
            var currentContent = '';
            var response_type = $('.wpaicg-template-form .wpaicg-template-response-type').val();
            if(response_type === 'textarea') {
                var basicEditor = true;
                var editor = tinyMCE.get('editor-' + wpaicgEditorNumber);
                if ($('#wp-editor-' + wpaicgEditorNumber + '-wrap').hasClass('tmce-active') && editor) {
                    basicEditor = false;
                }
                if (basicEditor) {
                    currentContent = $('#editor-' + wpaicgEditorNumber).val();
                } else {
                    currentContent = editor.getContent();
                    currentContent = currentContent.replace(/<\/?p(>|$)/g, "");
                }
            }
            else{
                currentContent = $('.wpaicg-template-response-element').html();
            }
            var element = document.createElement('a');
            currentContent = currentContent.replace(/<br>/g,"\n");
            currentContent = currentContent.replace(/<br \/>/g,"\n");
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(currentContent));
            element.setAttribute('download', 'ai-response.txt');

            element.style.display = 'none';
            document.body.appendChild(element);

            element.click();

            document.body.removeChild(element);
        });

        $(document).on('click', '.wpaicg-template-copy', function(e) {
            e.preventDefault();

            var currentContent = '';
            var response_type = $('.wpaicg-template-form .wpaicg-template-response-type').val();

            if (response_type === 'textarea') {
                var basicEditor = true;
                var editor = tinyMCE.get('editor-' + wpaicgEditorNumber);
                
                if ($('#wp-editor-' + wpaicgEditorNumber + '-wrap').hasClass('tmce-active') && editor) {
                    basicEditor = false;
                }
                
                if (basicEditor) {
                    currentContent = $('#editor-' + wpaicgEditorNumber).val();
                } else {
                    currentContent = editor.getContent();
                    currentContent = currentContent.replace(/<\/?p(>|$)/g, "");
                }
            } else {
                currentContent = $('.wpaicg-template-response-element').html();
            }

            // Replace &nbsp; with space
            currentContent = currentContent.replace(/&nbsp;/g, ' ');

            // Replace single occurrences of <br> or <br /> with a newline
            currentContent = currentContent.replace(/<br\s*\/?>/g, '\r\n');

            // Replace double occurrences of <br><br> or <br /><br /> with double newline
            currentContent = currentContent.replace(/\r\n\r\n/g, '\r\n\r\n');

            // Copy currentContent to clipboard
            navigator.clipboard.writeText(currentContent).then(function() {
                console.log('Text successfully copied to clipboard');
            }).catch(function(err) {
                console.error('Unable to copy text to clipboard', err);
            });
        });

        $(document).on('input','.wpaicg-template-form .wpaicg-template-max_tokens input', function(e){
            var maxtokens = $(e.currentTarget).val();
            var wpaicg_estimated_cost = maxtokens !== '' ? parseFloat(maxtokens)*0.002/1000 : 0;
            wpaicg_estimated_cost = '$'+parseFloat(wpaicg_estimated_cost.toFixed(5));
            $('.wpaicg-template-form .wpaicg-template-estimated span').html(wpaicg_estimated_cost);
        });
        $(document).on('click','.wpaicg-template-item .wpaicg-template-content,.wpaicg-template-item .wpaicg-template-icon',function (e){
            var item = $(e.currentTarget).parent();
            var title = item.attr('data-title');
            var id = item.attr('data-id');
            var type = item.attr('data-type');
            var categories = item.attr('data-categories');
            prompt_name = title;
            prompt_id = id;
            $('.wpaicg_modal_content').empty();
            if(type === 'json') {
                $('.wpaicg-template-action-customize').attr('data-id', id);
            }
            else{
                $('.wpaicg-template-action-customize').hide();
            }
            var modal_head = '<div class="wpaicg-d-flex wpaicg-align-items-center wpaicg-modal-template-head"><div style="margin-left: 10px;">';
            modal_head += '<strong>'+title+'</strong>';
            if(categories !== ''){
                modal_head += '<div><small>'+categories+'</small></div>';
            }
            modal_head += '</div></div>';
            $('.wpaicg_modal_title').html(modal_head);
            $('.wpaicg-modal-template-head').prepend(item.find('.wpaicg-template-icon').clone());
            var prompt = item.attr('data-prompt');
            var response = item.attr('data-response');
            var response_type = item.attr('data-editor');
            $('.wpaicg-template-response-type').val(response_type);
            wpaicgEditorNumber = Math.ceil(Math.random()*1000000);
            $('.wpaicg_modal_content').html('<div class="wpaicg-template-form">'+wpaicgTemplateDefaultContent.html()+'</div>');
            $('.wpaicg-template-form').find('.wpaicg-template-title').val(prompt);
            var wpaicgFieldsForm = item.attr('data-fields');
            if(wpaicgFieldsForm !== ''){
                var wpaicgFormFieldsElement = $('.wpaicg-template-form .wpaicg-form-fields');
                wpaicgFieldsForm = wpaicgFieldsForm.replace(/\\/g,'');
                wpaicgFieldsForm = JSON.parse(wpaicgFieldsForm);
                $.each(wpaicgFieldsForm, function(idx, form_field){
                    var form_field_html = '<div class="mb-5"><label>'+form_field['label']+'</label><br>';
                    var form_field_type = 'text';
                    if(form_field['type'] !== undefined){
                        form_field_type = form_field['type'];
                    }
                    if(form_field_type === 'select'){
                        form_field_html += '<select name="'+form_field['id']+'" data-min="'+form_field['min']+'" data-max="'+form_field['max']+'" data-type="'+form_field_type+'" data-label="'+form_field['label']+'" class="wpaicg-form-field-template">';
                        if(form_field['options'] !== undefined && form_field['options'].length){
                            var form_field_options = form_field['options'];
                            if(typeof form_field_options === 'string'){
                                form_field_options = form_field_options.split("|");
                            }
                            $.each(form_field_options, function (idy, form_field_option){
                                form_field_html += '<option value="'+form_field_option+'">'+form_field_option+'</option>';
                            })
                        }
                        form_field_html += '</select>';
                    }
                    else if(form_field_type === 'checkbox' || form_field_type === 'radio'){
                        form_field_html += '<div>';
                        if(form_field['options'] !== undefined && form_field['options'].length){
                            var form_field_options = form_field['options'];
                            if(typeof form_field_options === 'string'){
                                form_field_options = form_field_options.split("|");
                            }
                            $.each(form_field_options, function (idy, form_field_option){
                                form_field_html += '<input name="'+form_field['id']+(form_field_type === 'checkbox' ? '[]':'')+'" type="'+form_field_type+'" value="'+form_field_option+'">&nbsp;'+form_field_option+'&nbsp;&nbsp;&nbsp;';
                            })
                        }
                        form_field_html += '</div>';
                    }
                    else if(form_field_type === 'textarea'){
                        var textarea_rows = form_field['rows'] !== undefined ? ' rows="'+form_field['rows']+'"' : '';
                        var textarea_cols = form_field['cols'] !== undefined ? ' cols="'+form_field['cols']+'"' : '';
                        form_field_html += '<textarea'+textarea_rows+textarea_cols+' name="'+form_field['id']+'" data-type="'+form_field_type+'" data-label="'+form_field['label']+'" type="'+form_field_type+'" required class="wpaicg-form-field-template" data-min="'+form_field['min']+'" data-max="'+form_field['max']+'"></textarea>'
                    }
                    else{
                        form_field_html += '<input name="'+form_field['id']+'" data-type="'+form_field_type+'" data-label="'+form_field['label']+'" type="'+form_field_type+'" required class="wpaicg-form-field-template" data-min="'+form_field['min']+'" data-max="'+form_field['max']+'">'
                    }
                    form_field_html += '</div>';
                    wpaicgFormFieldsElement.append(form_field_html);
                })
            }
            wpaicgTemplateEditor = $('.wpaicg-template-form').find('.wpaicg-template-result');
            if(id !== undefined){
                var embed_message = '<?php echo esc_html__('Embed this form to your website','gpt3-ai-content-generator')?>: [wpaicg_form id='+id+' settings=no';
                if(type === 'custom'){
                    embed_message += ' custom=yes';
                }
                embed_message += ']';
                $('.wpaicg-template-form .wpaicg-template-shortcode').html(embed_message);
            }
            for(var i = 0; i < wpaicgTemplateSettings.length; i++){
                var item_name = wpaicgTemplateSettings[i];
                var item_value = item.attr('data-'+item_name);
                if(item_name === 'max_tokens'){
                    var wpaicg_estimated_cost = item_value !== undefined ? parseFloat(item_value)*0.002/1000 : 0;
                    wpaicg_estimated_cost = '$'+parseFloat(wpaicg_estimated_cost.toFixed(5));
                    $('.wpaicg-template-form .wpaicg-template-estimated span').html(wpaicg_estimated_cost);
                }
                if(item_value !== undefined){
                    if(
                        item_name === 'generate_text'
                        || item_name === 'draft_text'
                        || item_name === 'noanswer_text'
                        || item_name === 'clear_text'
                        || item_name === 'stop_text'
                        || item_name === 'copy_text'
                        || item_name === 'suffix_text'
                    ){
                        $('.wpaicg-template-text-'+item_name).html(item_value);
                    }
                    else {
                        if (item_name !== 'engine' && item_name !== 'stop' && item_name !== 'post_title') {
                            item_value = parseFloat(item_value);
                            item_value = item_value.toString().replace(/,/g, '.');
                        }
                        $('.wpaicg-template-form .wpaicg-template-' + item_name).find('[name=' + item_name + ']').val(item_value);
                        $('.wpaicg-template-form .wpaicg-template-' + item_name).show();
                    }
                }
                else{
                    $('.wpaicg-template-form .wpaicg-template-'+item_name).hide();
                }
            }
            $('.wpaicg-template-form .wpaicg-template-response').html(response);
            wpaicgTemplateEditor.attr('id','editor-'+wpaicgEditorNumber);
            if(response_type === 'textarea') {
                wp.editor.initialize('editor-' + wpaicgEditorNumber, {
                    tinymce: {
                        wpautop: true,
                        plugins: 'charmap colorpicker hr lists paste tabfocus textcolor fullscreen wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
                        toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,wp_more,spellchecker,fullscreen,wp_adv,listbuttons',
                        toolbar2: 'styleselect,strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help',
                        height: 300
                    },
                    quicktags: {buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close'},
                    mediaButtons: true
                });
            }
            else{
                $('.wpaicg-template-form .wpaicg-template-response-editor').hide();
            }
            $('.wpaicg_modal').css('top','');
            $('.wpaicg_modal').css('height','');
            $('.wpaicg-overlay').show();
            $('.wpaicg_modal').show();
        });
        function wpaicgLoading(btn){
            btn.attr('disabled','disabled');
            if(!btn.find('spinner').length){
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility','unset');
        }
        function wpaicgRmLoading(btn){
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }
        function stopOpenAIGenerator(){
            $('.wpaicg-template-form .wpaicg-template-stop-generate').hide();
            if(!wpaicg_limited_token) {
                $('.wpaicg-template-form .wpaicg-template-save-result').show();
            }
            wpaicgRmLoading($('.wpaicg-template-form .wpaicg-generate-button'));
            eventGenerator.close();
        }
        $(document).on('click','.wpaicg-template-form .wpaicg-template-stop-generate', function (e){
            stopOpenAIGenerator();
        });
        function wpaicgValidEmail(email){
            return String(email)
                .toLowerCase()
                .match(
                    /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                );
        }
        function wpaicgValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (err) {
                return false;
            }
        }

// Listen for form submission on .wpaicg-template-form
$(document).on('submit', '.wpaicg-template-form form', function (e) {
    e.preventDefault(); // Stop default form action
    var form = $(e.currentTarget);
    var btn = form.find('.wpaicg-generate-button');
    var template_title = form.find('.wpaicg-template-title').val();
    var response_type = form.find('.wpaicg-template-response-type').val();

    // Trim and validate prompt title
    if (template_title !== '') {
        template_title = template_title.trim();

        // Get form fields
        var max_tokens         = form.find('.wpaicg-template-max_tokens input').val();
        var temperature        = form.find('.wpaicg-template-temperature input').val();
        var top_p              = form.find('.wpaicg-template-top_p input').val();
        var best_of            = form.find('.wpaicg-template-best_of input').val();
        var frequency_penalty  = form.find('.wpaicg-template-frequency_penalty input').val();
        var presence_penalty   = form.find('.wpaicg-template-presence_penalty input').val();
        var engine             = form.find('.wpaicg-template-engine select').val();

        // Fetch max valid tokens for the selected engine
        var maxValidToken = engineMaxTokens[engine] || 4096;

        // Replaceable base error message (configured in PHP)
        var baseErrorMessageTemplate = '<?php echo esc_js(sprintf(esc_html__("Please enter a valid max token value between %s and %s", "gpt3-ai-content-generator"), "{minValue}", "{maxValue}"))?>';

        // Basic validation
        var error_message = false;

        if (max_tokens === '') {
            error_message = '<?php echo esc_html__("Please enter max tokens", "gpt3-ai-content-generator")?>';
        } else if (parseFloat(max_tokens) < 1 || parseFloat(max_tokens) > maxValidToken) {
            error_message = baseErrorMessageTemplate
                .replace('{minValue}', '1')
                .replace('{maxValue}', maxValidToken);
        } else if (temperature === '') {
            error_message = '<?php echo esc_html__("Please enter temperature","gpt3-ai-content-generator")?>';
        } else if (parseFloat(temperature) < 0 || parseFloat(temperature) > 1) {
            error_message = '<?php echo sprintf(esc_html__("Please enter a valid temperature value between %d and %d.","gpt3-ai-content-generator"),0,1)?>';
        } else if (top_p === '') {
            error_message = '<?php echo esc_html__("Please enter Top P","gpt3-ai-content-generator")?>';
        } else if (parseFloat(top_p) < 0 || parseFloat(top_p) > 1) {
            error_message = '<?php echo sprintf(esc_html__("Please enter a valid top p value between %d and %d.","gpt3-ai-content-generator"),0,1)?>';
        } else if (best_of === '') {
            error_message = '<?php echo esc_html__("Please enter best of","gpt3-ai-content-generator")?>';
        } else if (parseFloat(best_of) < 1 || parseFloat(best_of) > 20) {
            error_message = '<?php echo sprintf(esc_html__("Please enter a valid best of value between %d and %d.","gpt3-ai-content-generator"),1,20)?>';
        } else if (frequency_penalty === '') {
            error_message = '<?php echo esc_html__("Please enter frequency penalty","gpt3-ai-content-generator")?>';
        } else if (parseFloat(frequency_penalty) < 0 || parseFloat(frequency_penalty) > 2) {
            error_message = '<?php echo sprintf(esc_html__("Please enter a valid frequency penalty value between %d and %d.","gpt3-ai-content-generator"),0,2)?>';
        } else if (presence_penalty === '') {
            error_message = '<?php echo esc_html__("Please enter presence penalty","gpt3-ai-content-generator")?>';
        } else if (parseFloat(presence_penalty) < 0 || parseFloat(presence_penalty) > 2) {
            error_message = '<?php echo sprintf(esc_html__("Please enter a valid presence penalty value between %d and %d.","gpt3-ai-content-generator"),0,2)?>';
        }

        // If any errors so far, alert and stop
        if (error_message) {
            alert(error_message);
            return false;
        }

        // Validate any additional custom fields
        if ($('.wpaicg-template-form .wpaicg-form-field-template').length) {
            $('.wpaicg-template-form .wpaicg-form-field-template').each(function(idf, item) {
                var field_type  = $(item).attr('data-type');
                var field_name  = $(item).attr('name');
                var field_label = $(item).attr('data-label');
                var field_value = $(item).val();
                var field_min   = $(item).attr('data-min');
                var field_max   = $(item).attr('data-max');

                if (field_type === 'text' || field_type === 'textarea' || field_type === 'email' || field_type === 'url') {
                    if (field_min !== '' && field_value.length < parseInt(field_min)) {
                        error_message = '<?php echo esc_html__("{label} minimum {min} characters","gpt3-ai-content-generator")?>'
                            .replace(/{label}/g, field_label)
                            .replace(/{min}/g, field_min);
                    } else if (field_max !== '' && field_value.length > parseInt(field_max)) {
                        error_message = '<?php echo esc_html__("{label} maximum {max} characters","gpt3-ai-content-generator")?>'
                            .replace(/{label}/g, field_label)
                            .replace(/{max}/g, field_max);
                    } else if (field_type === 'email' && !wpaicgValidEmail(field_value)) {
                        error_message = '<?php echo esc_html__("{label} must be email address","gpt3-ai-content-generator")?>'
                            .replace(/{label}/g, field_label);
                    } else if (field_type === 'url' && !wpaicgValidUrl(field_value)) {
                        error_message = '<?php echo esc_html__("{label} must be url","gpt3-ai-content-generator")?>'
                            .replace(/{label}/g, field_label);
                    }
                } else if (field_type === 'number') {
                    if (field_min !== '' && parseFloat(field_value) < parseInt(field_min)) {
                        error_message = '<?php echo esc_html__("{label} minimum {min}","gpt3-ai-content-generator")?>'
                            .replace(/{label}/g, field_label)
                            .replace(/{min}/g, field_min);
                    } else if (field_max !== '' && parseFloat(field_value) > parseInt(field_max)) {
                        error_message = '<?php echo esc_html__("{label} maximum {max}","gpt3-ai-content-generator")?>'
                            .replace(/{label}/g, field_label)
                            .replace(/{max}/g, field_max);
                    }
                }

                if (error_message) {
                    return false; // Break .each() loop
                }
            });
        }

        // If an error was found during field checks
        if (error_message) {
            alert(error_message);
            return false;
        }

        // No errors, proceed with AI generation
        var prompt_response = ''; // We'll store the entire AI response here
        let startTime = new Date();

        // Replace placeholders in template title with field values
        if ($('.wpaicg-template-form .wpaicg-form-field-template').length) {
            $('.wpaicg-template-form .wpaicg-form-field-template').each(function (idf, item) {
                var field_name  = $(item).attr('name');
                var field_value = $(item).val();
                var sRegExInput = new RegExp('{' + field_name + '}', 'g');
                template_title  = template_title.replace(sRegExInput, field_value);
            });
        }

        // Prepare data for request
        var data = form.serialize();
        data += '&source_stream=form&nonce=<?php echo wp_create_nonce("wpaicg-ajax-nonce")?>';

        // If we're using a WP editor, detect if TinyMCE is active
        var basicEditor = true;
        if (response_type === 'textarea') {
            var editor = tinyMCE.get('editor-' + wpaicgEditorNumber);
            if ($('#wp-editor-' + wpaicgEditorNumber + '-wrap').hasClass('tmce-active') && editor) {
                basicEditor = false;
            }
            // Clear editor before new content
            if (basicEditor) {
                $('#editor-' + wpaicgEditorNumber).val('');
            } else {
                editor.setContent('');
            }
        } else {
            // Clear inline display element
            $('.wpaicg-template-response-element').html('');
        }

        // Show loader, reveal "Stop" button, etc.
        wpaicgLoading(btn);
        form.find('.wpaicg-template-stop-generate').show();
        form.find('.wpaicg-template-save-result').hide();

        // Potential line-limit setting
        var wpaicg_limitLines = parseInt(form.find('.wpaicg-template-max-lines').val());
        var count_line = 0;

        // Open SSE connection
        eventGenerator = new EventSource(
            '<?php echo esc_html(add_query_arg("wpaicg_stream","yes",site_url()."/index.php"));?>&' + data
        );

        // Track how many chunks we've seen, etc.
        var wpaicg_response_events = 0;
        var wpaicg_limited_token = false;

        // Handle streaming responses
        eventGenerator.onmessage = function (e) {
            // Check special signals first
            if (e.data === "[DONE]") {
                stopOpenAIGenerator(); // Graceful close
                return;
            }
            if (e.data === "[LIMITED]") {
                // Indicate a limitation (e.g., usage limit)
                wpaicg_limited_token = true;
                count_line++;
                stopOpenAIGenerator();
                return;
            }

            // Otherwise, parse normal chunk
            var resultData = JSON.parse(e.data);

            // Check if the chunk signals a finishing reason
            var hasFinishReason = (
                resultData.choices &&
                resultData.choices[0] &&
                (
                    resultData.choices[0].finish_reason === "stop" ||
                    resultData.choices[0].finish_reason === "length" ||
                    (
                        resultData.choices[0].finish_details &&
                        resultData.choices[0].finish_details.type === "stop"
                    )
                )
            );

            if (hasFinishReason) {
                count_line++;
            }

            if (resultData.error !== undefined) {
                // If there's an error in the response
                prompt_response += resultData.error.message;
            } else {
                // Accumulate chunk
                var content_generated = (resultData.choices[0].delta !== undefined)
                    ? (resultData.choices[0].delta.content !== undefined
                        ? resultData.choices[0].delta.content
                        : ''
                      )
                    : resultData.choices[0].text;

                prompt_response += content_generated;
            }

            // Use marked.parse to convert the entire AI response so far to HTML
            var parsedHTML = marked.parse(prompt_response);

            // Update either WP editor (raw HTML) or inline element with parsed HTML
            if (response_type === 'textarea') {
                if (basicEditor) {
                    // Basic text area
                    $('#editor-' + wpaicgEditorNumber).val(parsedHTML);
                } else {
                    // TinyMCE editor
                    editor.setContent(parsedHTML);
                }
            } else {
                // Inline display element
                $('.wpaicg-template-response-element').html(parsedHTML);
            }

            // If the chunk indicated a finishing reason or line-limit, stop
            if (hasFinishReason || count_line === wpaicg_limitLines) {
                let endTime = new Date();
                let timeDiff = (endTime - startTime) / 1000;

                form.find('.wpaicg-template-stop-generate').hide();
                stopOpenAIGenerator();
                wpaicgRmLoading(btn);
            }
        };
    } else {
        alert('<?php echo esc_html__("Please enter prompt","gpt3-ai-content-generator")?>');
    }
    return false;
});

    })
</script>