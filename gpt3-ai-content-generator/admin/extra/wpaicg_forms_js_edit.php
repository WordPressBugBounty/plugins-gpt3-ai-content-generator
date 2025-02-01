<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * JS for the AI Forms Beta "Edit" interface.
 * Splits out the Edit Form logic from wpaicg_forms_js.php.
 *
 * Revised to use switches for "Use Embeddings?" and "Use Default Embedding Model?"
 * arranged in 3 lines like the Create mode, while preserving existing logic.
 *
 * Now also includes "fileupload" support in Edit Mode (drag & drop).
 */

// Prepare Qdrant and Pinecone arrays the same way as in create mode:
$qdrant_collections_opt = get_option('wpaicg_qdrant_collections', []);
if ( ! is_array($qdrant_collections_opt) ) {
    $decoded_qdrant = json_decode((string) $qdrant_collections_opt, true);
    if ( is_array($decoded_qdrant) ) {
        $qdrant_collections_opt = $decoded_qdrant;
    } else {
        $qdrant_collections_opt = [];
    }
}
$qdrant_default_collection = get_option('wpaicg_qdrant_default_collection', '');

$pinecone_indexes_opt = get_option('wpaicg_pinecone_indexes','');
if ( ! is_array($pinecone_indexes_opt) ) {
    $decoded_pinecone = json_decode((string) $pinecone_indexes_opt, true);
    if ( is_array($decoded_pinecone) ) {
        $pinecone_indexes_opt = $decoded_pinecone;
    } else {
        $pinecone_indexes_opt = [];
    }
}

// Embedding models from WPAICG_Util
use WPAICG\WPAICG_Util;
$embedding_models = WPAICG_Util::get_instance()->get_embedding_models();

// Also load icons array from icons.json so we can populate the Icon dropdown:
$wpaicg_plugin_dir = WPAICG_PLUGIN_DIR;
$wpaicg_icons_file = $wpaicg_plugin_dir . 'admin/data/icons.json';
$wpaicg_icons      = [];
if ( file_exists( $wpaicg_icons_file ) ) {
    $content = file_get_contents( $wpaicg_icons_file );
    $decoded = json_decode( $content, true );
    if ( is_array( $decoded ) ) {
        $wpaicg_icons = $decoded;
    }
}
?>
<script>
(function($){
    "use strict";

////////////////////////////////////////////////////////////////////////////////
// Global data for Qdrant / Pinecone / Embedding Models / Icons
////////////////////////////////////////////////////////////////////////////////
var qdrantCollectionsEdit = <?php echo json_encode($qdrant_collections_opt); ?>;
var qdrantDefaultEdit     = <?php echo json_encode($qdrant_default_collection); ?>;
var pineconeIndexesEdit   = <?php echo json_encode($pinecone_indexes_opt); ?>;
var embeddingModelsEdit   = <?php echo json_encode($embedding_models); ?>;

// Icons from icons.json, e.g. { "tag": "dashicons dashicons-tag", "linkedin": "dashicons dashicons-linkedin", ... }
var wpaicgIconsEdit       = <?php echo json_encode($wpaicg_icons); ?>;

////////////////////////////////////////////////////////////////////////////////
// Populate Qdrant, Pinecone, Embedding Models, Icon Keys
////////////////////////////////////////////////////////////////////////////////
function populatePineconeIndexesEdit(){
    var $select = $('#wpaicg_editform_pineconeindexes');
    $select.empty();
    if(Array.isArray(pineconeIndexesEdit) && pineconeIndexesEdit.length){
        pineconeIndexesEdit.forEach(function(idx){
            var name = idx.name || 'unknown';
            var url = idx.url || ''; // Ensure there's a value
            $select.append('<option value="'+ url +'">'+ name +'</option>');
        });
    }
}

function populateQdrantCollectionsEdit(){
    var $select = $('#wpaicg_editform_collections');
    $select.empty();
    if(Array.isArray(qdrantCollectionsEdit) && qdrantCollectionsEdit.length){
        qdrantCollectionsEdit.forEach(function(c){
            var cname = c.name || 'unnamed';
            $select.append('<option value="'+ cname +'">'+ cname +'</option>');
        });
    }
    if(qdrantDefaultEdit){
        $select.val(qdrantDefaultEdit);
    }
}

function populateEmbeddingProvidersAndModelsEdit(){
    var $provider = $('#wpaicg_editform_selected_embedding_provider');
    var $model    = $('#wpaicg_editform_selected_embedding_model');
    $provider.empty();
    $model.empty();

    $.each(embeddingModelsEdit, function(providerName, modelObj){
        $provider.append('<option value="'+providerName+'">'+providerName+'</option>');
    });

    $provider.on('change', function(){
        var selectedProv = $(this).val();
        $model.empty();
        if(embeddingModelsEdit[selectedProv]){
            $.each(embeddingModelsEdit[selectedProv], function(mName, dimension){
                $model.append('<option value="'+mName+'">'+ mName +' (dim:'+dimension+')</option>');
            });
        }
    });
    $provider.trigger('change');
}

// Populate the Icon dropdown with keys from wpaicgIconsEdit
function populateIconKeysEdit(){
    var $select = $('#wpaicg_editform_icon');
    $select.empty();
    // Add a default "(none)" option
    $select.append('<option value=""><?php echo esc_js(__('(none)','gpt3-ai-content-generator')); ?></option>');
    // Insert each key from icons.json
    $.each(wpaicgIconsEdit, function(iconKey, iconClass){
        $select.append('<option value="'+iconKey+'">'+iconKey+'</option>');
    });
}

$(document).ready(function(){
    // Qdrant / Pinecone
    populatePineconeIndexesEdit();
    populateQdrantCollectionsEdit();
    populateEmbeddingProvidersAndModelsEdit();

    // Icons
    populateIconKeysEdit();
});

////////////////////////////////////////////////////////////////////////////////
// Embeddings Show/Hide logic (revised to use a switch for yes/no)
////////////////////////////////////////////////////////////////////////////////

// Switch for "Use Embeddings?"
$('#wpaicg_editform_embeddings_switch').on('change', function(){
    if($(this).is(':checked')){
        $('#wpaicg_editform_embeddings').val('yes');
    } else {
        $('#wpaicg_editform_embeddings').val('no');
    }
    // Trigger the existing change logic
    $('#wpaicg_editform_embeddings').trigger('change');
});

$('#wpaicg_editform_embeddings').on('change', function(){
    var val = $(this).val();
    if(val === 'yes'){
        $('#wpaicg_editform_embeddings_settings_wrapper').show();
    } else {
        $('#wpaicg_editform_embeddings_settings_wrapper').hide();
        // hide sub-items
        $('#wpaicg_editform_pineconeindexes_wrap').hide();
        $('#wpaicg_editform_collections_wrap').hide();
    }
});

// Vector DB toggles
$('#wpaicg_editform_vectordb').on('change', function(){
    var dbVal = $(this).val();
    if(dbVal === 'pinecone'){
        $('#wpaicg_editform_pineconeindexes_wrap').show();
        $('#wpaicg_editform_collections_wrap').hide();
    } else {
        $('#wpaicg_editform_pineconeindexes_wrap').hide();
        $('#wpaicg_editform_collections_wrap').show();
    }
});

// Switch for "Use Default Embedding Model?"
$('#wpaicg_editform_default_embed_switch').on('change', function(){
    if($(this).is(':checked')){
        $('#wpaicg_editform_use_default_embedding_model').val('yes');
        // disable provider/model
        $('#wpaicg_editform_selected_embedding_provider').prop('disabled', true);
        $('#wpaicg_editform_selected_embedding_model').prop('disabled', true);
    } else {
        $('#wpaicg_editform_use_default_embedding_model').val('no');
        // enable provider/model
        $('#wpaicg_editform_selected_embedding_provider').prop('disabled', false);
        $('#wpaicg_editform_selected_embedding_model').prop('disabled', false);
    }
});

////////////////////////////////////////////////////////////////////////////////
// Toggles for header, copy, feedback, draft, etc.
////////////////////////////////////////////////////////////////////////////////
$('#wpaicg_editform_header_switch').on('change', function(){
    if ($(this).is(':checked')) {
        $('#wpaicg_editform_header').val('yes');
    } else {
        $('#wpaicg_editform_header').val('no');
    }
});

$('#wpaicg_editform_copy_button_switch').on('change', function(){
    if ($(this).is(':checked')) {
        $('#wpaicg_editform_copy_button').val('yes');
    } else {
        $('#wpaicg_editform_copy_button').val('no');
    }
});

$('#wpaicg_editform_ddraft_switch').on('change', function(){
    if ($(this).is(':checked')) {
        $('#wpaicg_editform_ddraft').val('yes');
    } else {
        $('#wpaicg_editform_ddraft').val('no');
    }
});

$('#wpaicg_editform_dclear_switch').on('change', function(){
    if ($(this).is(':checked')) {
        $('#wpaicg_editform_dclear').val('yes');
    } else {
        $('#wpaicg_editform_dclear').val('no');
    }
});

$('#wpaicg_editform_dnotice_switch').on('change', function(){
    if ($(this).is(':checked')) {
        $('#wpaicg_editform_dnotice').val('yes');
    } else {
        $('#wpaicg_editform_dnotice').val('no');
    }
});

$('#wpaicg_editform_ddownload_switch').on('change', function(){
    if ($(this).is(':checked')) {
        $('#wpaicg_editform_ddownload').val('yes');
    } else {
        $('#wpaicg_editform_ddownload').val('no');
    }
});

$('#wpaicg_editform_feedback_buttons_switch').on('change', function(){
    if ($(this).is(':checked')) {
        $('#wpaicg_editform_feedback_buttons').val('yes');
    } else {
        $('#wpaicg_editform_feedback_buttons').val('no');
    }
});

////////////////////////////////////////////////////////////////////////////////
// Inline title editing
////////////////////////////////////////////////////////////////////////////////
var isEditingEditTitle = false;
var $editTitleHidden   = $('#wpaicg_editform_title');
var $editTitleDisplay  = $('#wpaicg_edit_tab1_title');
var $editTitleEditIcon = $('#wpaicg_edit_tab1_edit_icon');

function truncateEditTitle(fullTitle){
    if(!fullTitle){
        return '<?php echo esc_js(__('Design','gpt3-ai-content-generator')); ?>';
    }
    if(fullTitle.length > 10){
        return fullTitle.substring(0, 10) + '...';
    }
    return fullTitle;
}

function updateEditTabTitleDisplay(newTitle){
    $editTitleDisplay.text(truncateEditTitle(newTitle));
}

function startEditTitleEditing(){
    if(isEditingEditTitle) return;
    isEditingEditTitle = true;
    var currentVal = $editTitleHidden.val().trim();
    $editTitleDisplay.text(currentVal);
    $editTitleDisplay.attr('contenteditable','true').focus();
    document.execCommand('selectAll', false, null);
}

function finishEditTitleEditing(){
    if(!isEditingEditTitle) return;
    var newVal = $editTitleDisplay.text().trim();
    $editTitleHidden.val(newVal);
    $editTitleDisplay.removeAttr('contenteditable');
    isEditingEditTitle = false;
    updateEditTabTitleDisplay(newVal);
}

function cancelEditTitleEditing(){
    if(!isEditingEditTitle) return;
    $editTitleDisplay.removeAttr('contenteditable');
    var oldVal = $editTitleHidden.val().trim();
    updateEditTabTitleDisplay(oldVal);
    isEditingEditTitle = false;
}

// Double-click or pen icon => begin editing
$editTitleDisplay.on('dblclick', function(){
    startEditTitleEditing();
});
$editTitleEditIcon.on('click', function(){
    startEditTitleEditing();
});

// On keydown => Enter or Escape
$editTitleDisplay.on('keydown', function(e){
    if(e.key === 'Enter'){
        e.preventDefault();
        finishEditTitleEditing();
    } else if(e.key === 'Escape'){
        e.preventDefault();
        cancelEditTitleEditing();
    }
});

// On blur => finish
$editTitleDisplay.on('blur', function(){
    if(isEditingEditTitle){
        finishEditTitleEditing();
    }
});

////////////////////////////////////////////////////////////////////////////////
// "Edit" button from the preview: show the edit container
////////////////////////////////////////////////////////////////////////////////
$('.wpaicg_preview_edit').on('click', function(){
    var formID = $(this).attr('data-edit-id');
    if(!formID){
        return;
    }
    // Hide main containers
    $('#wpaicg_aiforms_container, #wpaicg_logs_container, #wpaicg_settings_container, #wpaicg_preview_panel').hide();
    // Show edit container
    $('#wpaicg_edit_container').show();

    // Hide top icons except 'Back' + hide "create" save
    $('#wpaicg_toggle_sidebar_icon, #wpaicg_plus_icon, #wpaicg_search_icon, #wpaicg_menu_icon, .wpaicg_preview_back, .wpaicg_preview_duplicate, .wpaicg_preview_edit, .wpaicg_preview_delete, #wpaicg_create_save_form').hide();
    $('#wpaicg_return_main, #wpaicg_save_edited_form').show();

    // Rename the return button to "Exit Edit Mode"
    $('#wpaicg_return_main').text('<?php echo esc_js(__('Exit Edit Mode','gpt3-ai-content-generator')); ?>');

    // Clear old data
    $('#wpaicg_edit_dropzone').find('.builder_field_item').remove();
    $('#wpaicg_edit_dropzone .builder_placeholder').show();
    $('#wpaicg_editform_prompt').val('');
    $('#wpaicg_edit_validation_results').hide();
    $('#wpaicg_edit_status').hide().text('').css('color','green');
    $('#wpaicg_edit_form_id').val(formID);

    // Clear snippet
    $('#wpaicg_edit_snippets').empty();
    $('#wpaicg_edit_copied_msg').hide();

    // Clear interface fields
    $('#wpaicg_editform_category').val('');
    $('#wpaicg_editform_description').val('');
    $('#wpaicg_editform_color').val('#00BFFF');
    $('#wpaicg_editform_bgcolor').val('#f9f9f9');
    $('#wpaicg_editform_icon').val('');
    $('#wpaicg_editform_header').val('no');
    $('#wpaicg_editform_header_switch').prop('checked', false);
    $('#wpaicg_editform_copy_button').val('no');
    $('#wpaicg_editform_copy_button_switch').prop('checked', false);
    $('#wpaicg_editform_ddraft').val('no');
    $('#wpaicg_editform_ddraft_switch').prop('checked', false);
    $('#wpaicg_editform_dclear').val('no');
    $('#wpaicg_editform_dclear_switch').prop('checked', false);
    $('#wpaicg_editform_dnotice').val('no');
    $('#wpaicg_editform_dnotice_switch').prop('checked', false);
    $('#wpaicg_editform_ddownload').val('no');
    $('#wpaicg_editform_ddownload_switch').prop('checked', false);
    $('#wpaicg_editform_copy_text').val('');
    $('#wpaicg_editform_feedback_buttons').val('no');
    $('#wpaicg_editform_feedback_buttons_switch').prop('checked', false);
    $('#wpaicg_editform_generate_text').val('');
    $('#wpaicg_editform_noanswer_text').val('');
    $('#wpaicg_editform_draft_text').val('');
    $('#wpaicg_editform_clear_text').val('');
    $('#wpaicg_editform_stop_text').val('');
    $('#wpaicg_editform_cnotice_text').val('');
    $('#wpaicg_editform_download_text').val('');

    // Disable save initially
    $('#wpaicg_save_edited_form').attr('disabled','disabled');

    // Reset advanced model settings
    $('#wpaicg_editform_max_tokens').val('1500');
    $('#wpaicg_editform_top_p').val('1');
    $('#wpaicg_editform_top_p_value').text('1');
    $('#wpaicg_editform_frequency_penalty').val('0');
    $('#wpaicg_editform_frequency_penalty_value').text('0');
    $('#wpaicg_editform_presence_penalty').val('0');
    $('#wpaicg_editform_presence_penalty_value').text('0');
    $('#wpaicg_editform_stop').val('');
    $('#wpaicg_editform_best_of').val('1');

    // Reset the inline-editable form title to default
    $('#wpaicg_editform_title').val('');
    updateEditTabTitleDisplay('');

    // Show the snippet placeholder for now
    showShortcodeSnippet('[wpaicg_form id=' + formID + ' settings="no" custom="yes"]');

    // AJAX load existing data
    $.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'wpaicg_get_form_data_for_editing',
            nonce: '<?php echo esc_js(wp_create_nonce("wpaicg_edit_form_nonce")); ?>',
            form_id: formID
        },
        success: function(response){
            if(response.success){
                $('#wpaicg_editform_prompt').val(response.data.prompt);

                // If we have fields
                if(response.data.fields && response.data.fields.length > 0){
                    $('#wpaicg_edit_dropzone .builder_placeholder').hide();
                    response.data.fields.forEach(function(field){
                        createEditFieldItem(
                            field.type,
                            field.label,
                            field.id,
                            field.min,
                            field.max,
                            field.rows,
                            field.cols,
                            (field.options ? field.options.split('|') : [])
                        );
                    });
                }
                if(response.data.engine){
                    $('#wpaicg_editform_engine').val(response.data.engine);
                }

                // Title => store in hidden + update tab label
                $('#wpaicg_editform_title').val(response.data.title || '');
                updateEditTabTitleDisplay(response.data.title || '');

                // description
                $('#wpaicg_editform_description').val(response.data.description);

                // Interface
                if(response.data.interface) {
                    var ifData = response.data.interface;
                    if(ifData.wpaicg_form_category){
                        $('#wpaicg_editform_category').val(ifData.wpaicg_form_category);
                    }
                    if(ifData.wpaicg_form_color){
                        $('#wpaicg_editform_color').val(ifData.wpaicg_form_color);
                    }
                    if(ifData.wpaicg_form_bgcolor){
                        $('#wpaicg_editform_bgcolor').val(ifData.wpaicg_form_bgcolor);
                    }
                    if(ifData.wpaicg_form_icon){
                        $('#wpaicg_editform_icon').val(ifData.wpaicg_form_icon);
                    }
                    if(ifData.wpaicg_form_editor){
                        $('#wpaicg_editform_editor').val(ifData.wpaicg_form_editor);
                    }

                    // Header switch
                    if(ifData.wpaicg_form_header === 'yes'){
                        $('#wpaicg_editform_header_switch').prop('checked', true);
                        $('#wpaicg_editform_header').val('yes');
                    } else {
                        $('#wpaicg_editform_header_switch').prop('checked', false);
                        $('#wpaicg_editform_header').val('no');
                    }

                    // Copy switch
                    if(ifData.wpaicg_form_copy_button === 'yes'){
                        $('#wpaicg_editform_copy_button_switch').prop('checked', true);
                        $('#wpaicg_editform_copy_button').val('yes');
                    } else {
                        $('#wpaicg_editform_copy_button_switch').prop('checked', false);
                        $('#wpaicg_editform_copy_button').val('no');
                    }

                    // Draft switch
                    if(ifData.wpaicg_form_ddraft === 'yes'){
                        $('#wpaicg_editform_ddraft_switch').prop('checked', true);
                        $('#wpaicg_editform_ddraft').val('yes');
                    } else {
                        $('#wpaicg_editform_ddraft_switch').prop('checked', false);
                        $('#wpaicg_editform_ddraft').val('no');
                    }

                    // Clear switch
                    if(ifData.wpaicg_form_dclear === 'yes'){
                        $('#wpaicg_editform_dclear_switch').prop('checked', true);
                        $('#wpaicg_editform_dclear').val('yes');
                    } else {
                        $('#wpaicg_editform_dclear_switch').prop('checked', false);
                        $('#wpaicg_editform_dclear').val('no');
                    }

                    // Notification switch
                    if(ifData.wpaicg_form_dnotice === 'yes'){
                        $('#wpaicg_editform_dnotice_switch').prop('checked', true);
                        $('#wpaicg_editform_dnotice').val('yes');
                    } else {
                        $('#wpaicg_editform_dnotice_switch').prop('checked', false);
                        $('#wpaicg_editform_dnotice').val('no');
                    }

                    // Download switch
                    if(ifData.wpaicg_form_ddownload === 'yes'){
                        $('#wpaicg_editform_ddownload_switch').prop('checked', true);
                        $('#wpaicg_editform_ddownload').val('yes');
                    } else {
                        $('#wpaicg_editform_ddownload_switch').prop('checked', false);
                        $('#wpaicg_editform_ddownload').val('no');
                    }

                    // Feedback switch
                    if(ifData.wpaicg_form_feedback_buttons === 'yes'){
                        $('#wpaicg_editform_feedback_buttons_switch').prop('checked', true);
                        $('#wpaicg_editform_feedback_buttons').val('yes');
                    } else {
                        $('#wpaicg_editform_feedback_buttons_switch').prop('checked', false);
                        $('#wpaicg_editform_feedback_buttons').val('no');
                    }

                    if(ifData.wpaicg_form_copy_text){
                        $('#wpaicg_editform_copy_text').val(ifData.wpaicg_form_copy_text);
                    }
                    if(ifData.wpaicg_form_generate_text){
                        $('#wpaicg_editform_generate_text').val(ifData.wpaicg_form_generate_text);
                    }
                    if(ifData.wpaicg_form_noanswer_text){
                        $('#wpaicg_editform_noanswer_text').val(ifData.wpaicg_form_noanswer_text);
                    }
                    if(ifData.wpaicg_form_draft_text){
                        $('#wpaicg_editform_draft_text').val(ifData.wpaicg_form_draft_text);
                    }
                    if(ifData.wpaicg_form_clear_text){
                        $('#wpaicg_editform_clear_text').val(ifData.wpaicg_form_clear_text);
                    }
                    if(ifData.wpaicg_form_stop_text){
                        $('#wpaicg_editform_stop_text').val(ifData.wpaicg_form_stop_text);
                    }
                    if(ifData.wpaicg_form_cnotice_text){
                        $('#wpaicg_editform_cnotice_text').val(ifData.wpaicg_form_cnotice_text);
                    }
                    if(ifData.wpaicg_form_download_text){
                        $('#wpaicg_editform_download_text').val(ifData.wpaicg_form_download_text);
                    }
                }

                // Advanced settings
                if(response.data.advanced_settings){
                    var adv = response.data.advanced_settings;
                    $('#wpaicg_editform_max_tokens').val(adv.max_tokens);
                    $('#wpaicg_editform_top_p').val(adv.top_p);
                    $('#wpaicg_editform_top_p_value').text(adv.top_p);
                    $('#wpaicg_editform_frequency_penalty').val(adv.frequency_penalty);
                    $('#wpaicg_editform_frequency_penalty_value').text(adv.frequency_penalty);
                    $('#wpaicg_editform_presence_penalty').val(adv.presence_penalty);
                    $('#wpaicg_editform_presence_penalty_value').text(adv.presence_penalty);
                    $('#wpaicg_editform_stop').val(adv.stop);
                    $('#wpaicg_editform_best_of').val(adv.best_of);
                }

                // Embeddings
                if(response.data.embedding_settings){
                    const emb = response.data.embedding_settings;
                    $('#wpaicg_editform_embeddings').val(emb.use_embeddings || 'no');
                    $('#wpaicg_editform_vectordb').val(emb.vectordb || 'pinecone');
                    $('#wpaicg_editform_collections').val(emb.collections || '');
                    $('#wpaicg_editform_pineconeindexes').val(emb.pineconeindexes || '');
                    $('#wpaicg_editform_suffix_text').val(emb.suffix_text || 'Context:');
                    $('#wpaicg_editform_suffix_position').val(emb.suffix_position || 'after');
                    $('#wpaicg_editform_use_default_embedding_model').val(emb.use_default_embedding_model || 'yes');
                    $('#wpaicg_editform_selected_embedding_provider').val(emb.selected_embedding_provider || '').trigger('change');
                    $('#wpaicg_editform_selected_embedding_model').val(emb.selected_embedding_model || '');
                    $('#wpaicg_editform_embeddings_limit').val(emb.embeddings_limit || 1);

                    // Manually update the toggle states after we've set the hidden fields:
                    if (emb.use_embeddings === 'yes') {
                        $('#wpaicg_editform_embeddings_switch').prop('checked', true);
                    } else {
                        $('#wpaicg_editform_embeddings_switch').prop('checked', false);
                    }
                    // Show/hide embeddings wrapper
                    $('#wpaicg_editform_embeddings').trigger('change');

                    // Then ensure correct vector DB subfield is shown
                    $('#wpaicg_editform_vectordb').trigger('change');

                    if (emb.use_default_embedding_model === 'yes') {
                        $('#wpaicg_editform_default_embed_switch').prop('checked', true);
                        $('#wpaicg_editform_selected_embedding_provider').prop('disabled', true);
                        $('#wpaicg_editform_selected_embedding_model').prop('disabled', true);
                    } else {
                        $('#wpaicg_editform_default_embed_switch').prop('checked', false);
                        $('#wpaicg_editform_selected_embedding_provider').prop('disabled', false);
                        $('#wpaicg_editform_selected_embedding_model').prop('disabled', false);
                    }
                }

            } else {
                $('#wpaicg_edit_status').show().css('color','red')
                    .text(response.data.message || '<?php echo esc_js(__('Error loading form','gpt3-ai-content-generator')); ?>');
            }

            // Check prompt validity automatically
            updateEditValidateButtonState();
        },
        error: function(){
            $('#wpaicg_edit_status').show().css('color','red')
                .text('<?php echo esc_js(__('Failed to load form data.','gpt3-ai-content-generator')); ?>');
        }
    });

    // Show Tab 1 by default
    $('.wpaicg_edit_tabs li').removeClass('active').first().addClass('active');
    $('.wpaicg_edit_tab_content').removeClass('active').hide();
    $('#wpaicg_edit_tab1').addClass('active').show();
});

////////////////////////////////////////////////////////////////////////////////
// BACK/RETURN button -> exit edit mode
////////////////////////////////////////////////////////////////////////////////
$('#wpaicg_return_main').on('click', function(e){
    var editVisible   = $('#wpaicg_edit_container').is(':visible');
    if(editVisible) {
        if(!confirm('<?php echo esc_js(__('Are you sure? All changes will be lost if you exit Edit Mode.','gpt3-ai-content-generator')); ?>')) {
            e.stopImmediatePropagation();
            e.preventDefault();
            return false;
        }
    }

    // Return to main
    $('#wpaicg_logs_container, #wpaicg_settings_container, #wpaicg_create_container, #wpaicg_edit_container').hide();
    $('#wpaicg_aiforms_container').show();
    $('#wpaicg_preview_panel').hide();
    $('#wpaicg_forms_grid').show();

    // Restore main icons
    $('#wpaicg_toggle_sidebar_icon, #wpaicg_search_icon, #wpaicg_plus_icon, #wpaicg_menu_icon').show();

    // Hide secondary
    $('#wpaicg_return_main, .wpaicg_preview_back, .wpaicg_preview_duplicate, .wpaicg_preview_edit, .wpaicg_preview_delete, #wpaicg_create_save_form, #wpaicg_save_edited_form').hide();

    // Hide snippet
    hideShortcodeSnippet();

    // Reset the return button
    $('#wpaicg_return_main').text('<?php echo esc_js(__('Back','gpt3-ai-content-generator')); ?>');
});

////////////////////////////////////////////////////////////////////////////////
// EDIT MODE DRAG & DROP
////////////////////////////////////////////////////////////////////////////////
var $editDropZone = $('#wpaicg_edit_dropzone');
var $editPlaceholder = $editDropZone.find('.builder_placeholder');
var $editFieldsDraggedItem = null;
var editFieldCounter = 1; // auto ID for newly added fields in edit

$('.builder_left li').on('dragstart', handleEditDragStart);
$editDropZone.on('dragover', function(e){ e.preventDefault(); });
$editDropZone.on('drop', handleEditDrop);

function handleEditDragStart(e) {
    e.originalEvent.dataTransfer.setData('text/plain', e.target.getAttribute('data-type'));
}
function handleEditDrop(e) {
    e.preventDefault();
    if ($editFieldsDraggedItem) {
        return;
    }
    var dataType = e.originalEvent.dataTransfer.getData('text/plain');
    // Now includes 'fileupload'
    var allowed = ['text','textarea','email','number','checkbox','radio','select','url','fileupload'];
    if (allowed.indexOf(dataType) >= 0) {
        createEditFieldItem(dataType);
    }
}

function createEditFieldItem(dataType, labelVal, idVal, minVal, maxVal, rowsVal, colsVal, optionsArr) {
    labelVal = labelVal || '';
    idVal    = idVal    || ('idE' + editFieldCounter++);
    minVal   = (typeof minVal !== 'undefined') ? minVal : '';
    maxVal   = (typeof maxVal !== 'undefined') ? maxVal : '';
    rowsVal  = (typeof rowsVal !== 'undefined') ? rowsVal : '';
    colsVal  = (typeof colsVal !== 'undefined') ? colsVal : '';
    optionsArr = Array.isArray(optionsArr) ? optionsArr : [];

    var domID = 'edit-field-' + Date.now();
    var settingsHtml = getFieldSettingsHtml(dataType, minVal, maxVal, rowsVal, colsVal, optionsArr);

    var $fieldEl = $(
        '<div class="builder_field_item" draggable="true" data-type="'+dataType+'" id="'+domID+'">'+
            '<span class="remove_field">&times;</span>'+
            '<span class="builder_settings_icon">&#9881;</span>'+
            '<label><?php echo esc_js(__('Label','gpt3-ai-content-generator')); ?>:'+
                '<input type="text" class="builder_label_input" value="'+(labelVal||'')+'" placeholder="<?php echo esc_attr__('Field Label','gpt3-ai-content-generator'); ?>" />'+
            '</label>'+
            '<label><?php echo esc_js(__('ID','gpt3-ai-content-generator')); ?>:'+
                '<input type="text" class="builder_id_input" value="'+(idVal||'')+'" placeholder="<?php echo esc_attr__('Short ID','gpt3-ai-content-generator'); ?>" />'+
            '</label>'+
            settingsHtml+
            '<small style="display:block; color:#777;"><?php echo esc_js(__('Type','gpt3-ai-content-generator')); ?>: '+dataType+'</small>'+
        '</div>'
    );

    $editDropZone.append($fieldEl);
    $editPlaceholder.hide();
    initEditFieldItemDrag($fieldEl);

    // Add snippet
    addEditSnippet(idVal, domID);

    // Listen for ID changes
    var $idInput = $fieldEl.find('.builder_id_input');
    $idInput.data('oldid', idVal);
    $idInput.on('input', function(){
        var oldID = $(this).data('oldid') || idVal;
        var newIDVal = $(this).val().trim();
        if(!newIDVal){ return; }
        replacePlaceholderInText($('#wpaicg_editform_prompt'), oldID, newIDVal);
        updateEditSnippet(oldID, newIDVal, domID);
        $(this).data('oldid', newIDVal);
        updateEditValidateButtonState();
    });

    updateEditValidateButtonState();
}

function initEditFieldItemDrag($el){
    $el.on('dragstart', function(ev){
        ev.originalEvent.dataTransfer.setData('text/plain', $(this).attr('id'));
        $editFieldsDraggedItem = this;
    });
    $el.on('dragend', function(){
        $editFieldsDraggedItem = null;
    });
}

$editDropZone.on('dragover', '.builder_field_item', function(e){
    e.preventDefault();
    var bounding = this.getBoundingClientRect();
    var offset   = bounding.y + (bounding.height / 2);
    if(e.originalEvent.clientY - offset > 0) {
        $(this).addClass('drag-bottom').removeClass('drag-top');
    } else {
        $(this).addClass('drag-top').removeClass('drag-bottom');
    }
});
$editDropZone.on('dragleave', '.builder_field_item', function(){
    $(this).removeClass('drag-top drag-bottom');
});
$editDropZone.on('drop', '.builder_field_item', function(e){
    e.preventDefault();
    if(!$editFieldsDraggedItem) return;
    if($(this).hasClass('drag-top')){
        $(this).before($editFieldsDraggedItem);
    } else {
        $(this).after($editFieldsDraggedItem);
    }
    $(this).removeClass('drag-top drag-bottom');
    updateEditValidateButtonState();
});
$editDropZone.on('click', '.remove_field', function(){
    var $parent = $(this).parent();
    var oldID = $parent.find('.builder_id_input').val().trim();
    removePlaceholderFromText($('#wpaicg_editform_prompt'), oldID);
    removeEditSnippet(oldID, $parent.attr('id'));
    $parent.remove();
    if($editDropZone.find('.builder_field_item').length===0){
        $editPlaceholder.show();
    }
    updateEditValidateButtonState();
});
$editDropZone.on('click', '.builder_settings_icon', function(){
    $(this).siblings('.field_settings').slideToggle(150);
});
$editDropZone.on('click', '.add_option_btn', function(){
    var $ul = $(this).siblings('.options_list');
    if($ul.length) {
        $ul.append('<li><input type="text" class="option_value" value="" /></li>');
    }
});

////////////////////////////////////////////////////////////////////////////////
// SNIPPETS (EDIT)
////////////////////////////////////////////////////////////////////////////////
function addEditSnippet(idVal, domID){
    var snippet = '<span class="wpaicg_snippet" data-dom="'+domID+'" data-id="'+idVal+'">{'+idVal+'}</span>';
    $('#wpaicg_edit_snippets').append(snippet);
}
function updateEditSnippet(oldID, newID, domID){
    var $snip = $('#wpaicg_edit_snippets').find('[data-dom="'+domID+'"]');
    if($snip.length){
        $snip.attr('data-id', newID).data('id', newID).text('{'+newID+'}');
    }
}
function removeEditSnippet(idVal, domID){
    $('#wpaicg_edit_snippets').find('[data-dom="'+domID+'"]').remove();
}

$('#wpaicg_edit_snippets').on('click', '.wpaicg_snippet', function(){
    var snippetID = $(this).data('id');
    var toCopy = '{' + snippetID + '}';
    copyToClipboard(toCopy, $('#wpaicg_edit_copied_msg'));
});

////////////////////////////////////////////////////////////////////////////////
// VALIDATION FOR EDIT
////////////////////////////////////////////////////////////////////////////////
var editPromptIsValid = false;

function updateEditValidateButtonState() {
    var fieldsCount = $editDropZone.find('.builder_field_item').length;
    var promptValue = $('#wpaicg_editform_prompt').val().trim();
    if(fieldsCount > 0 && promptValue.length > 0) {
        $('#wpaicg_edit_validate_prompt').removeAttr('disabled');
    } else {
        $('#wpaicg_edit_validate_prompt').attr('disabled','disabled');
    }

    editPromptIsValid = checkEditPromptFields();
    if(editPromptIsValid) {
        $('#wpaicg_save_edited_form').removeAttr('disabled');
    } else {
        $('#wpaicg_save_edited_form').attr('disabled','disabled');
    }
}

$('#wpaicg_editform_prompt').on('input', updateEditValidateButtonState);

function checkEditPromptFields() {
    var $fields = $editDropZone.find('.builder_field_item');
    if($fields.length === 0) {
        return false;
    }
    var missingID = false;
    var fieldIDs = [];
    $fields.each(function(){
        var idVal = $(this).find('.builder_id_input').val().trim();
        if(!idVal){ missingID = true; }
        fieldIDs.push(idVal);
    });
    if(missingID){ return false; }

    var promptValue = $('#wpaicg_editform_prompt').val().trim();
    if(!promptValue) { return false; }

    var matches = promptValue.match(/\{([^}]+)\}/g) || [];
    var placeholderIDs = matches.map(function(m){ return m.replace(/[{}]/g,''); });

    if(placeholderIDs.length !== fieldIDs.length) { return false; }

    // Check order
    for(var i=0; i<fieldIDs.length; i++){
        if(fieldIDs[i] !== placeholderIDs[i]){ return false; }
    }
    // Check existence
    for(var j=0; j<fieldIDs.length; j++){
        if(!promptValue.includes('{'+fieldIDs[j]+'}')){
            return false;
        }
    }
    return true;
}

function validateEditPrompt() {
    var $fields = $editDropZone.find('.builder_field_item');
    var fieldIDs = [];
    var missingID = false;

    $fields.each(function(){
        var idVal = $(this).find('.builder_id_input').val().trim();
        if(!idVal){ missingID = true; }
        fieldIDs.push(idVal);
    });

    var promptValue = $('#wpaicg_editform_prompt').val().trim();
    var matches = promptValue.match(/\{([^}]+)\}/g) || [];
    var placeholderIDs = matches.map(function(m){ return m.replace(/[{}]/g,''); });

    $('#wpaicg_edit_validation_results').show();

    if(missingID){
        $('#wpaicg_edit_validate_count_result').css('color','red').text('✘ Some fields have no ID');
        $('#wpaicg_edit_validate_order_result').css('color','red').text('✘ Not checked');
        $('#wpaicg_edit_validate_existence_result').css('color','red').text('✘ Not checked');
        editPromptIsValid = false;
        $('#wpaicg_save_edited_form').attr('disabled','disabled');
        return;
    }

    // 1) Count
    var countPass = (placeholderIDs.length === fieldIDs.length);
    if(countPass){
        $('#wpaicg_edit_validate_count_result').css('color','green')
            .text('✔ ' + placeholderIDs.length + ' placeholder(s)');
    } else {
        $('#wpaicg_edit_validate_count_result').css('color','red')
            .text('✘ Expected ' + fieldIDs.length + ', found ' + placeholderIDs.length);
    }

    // 2) Order
    var orderPass = true;
    if(countPass){
        for(var i=0; i<fieldIDs.length; i++){
            if(fieldIDs[i] !== placeholderIDs[i]){
                orderPass = false;
                break;
            }
        }
    } else {
        orderPass = false;
    }
    if(orderPass){
        $('#wpaicg_edit_validate_order_result').css('color','green').text('✔ Order matches');
    } else {
        $('#wpaicg_edit_validate_order_result').css('color','red').text('✘ Order mismatch');
    }

    // 3) Existence
    var missingIDs = [];
    for(var j=0; j<fieldIDs.length; j++){
        if(!promptValue.includes('{'+fieldIDs[j]+'}')){
            missingIDs.push(fieldIDs[j]);
        }
    }
    if(missingIDs.length === 0){
        $('#wpaicg_edit_validate_existence_result').css('color','green').text('✔ All field IDs exist in prompt');
    } else {
        $('#wpaicg_edit_validate_existence_result').css('color','red').text('✘ Missing placeholders: ' + missingIDs.join(', '));
    }

    editPromptIsValid = (missingIDs.length === 0 && countPass && orderPass);
    if(editPromptIsValid){
        $('#wpaicg_save_edited_form').removeAttr('disabled');
    } else {
        $('#wpaicg_save_edited_form').attr('disabled','disabled');
    }
}
$('#wpaicg_edit_validate_prompt').on('click', validateEditPrompt);

////////////////////////////////////////////////////////////////////////////////
// SAVE CHANGES (EDIT)
////////////////////////////////////////////////////////////////////////////////
$('#wpaicg_save_edited_form').on('click', function(){
    if(!editPromptIsValid){
        showGlobalMessage('error','<?php echo esc_js(__('Prompt not valid.','gpt3-ai-content-generator')); ?>');
        return;
    }

    var formID      = $('#wpaicg_edit_form_id').val();
    var title       = $('#wpaicg_editform_title').val().trim();
    var description = $('#wpaicg_editform_description').val().trim();
    var prompt      = $('#wpaicg_editform_prompt').val().trim();
    var engine      = $('#wpaicg_editform_engine').val();
    var $fields     = $('#wpaicg_edit_dropzone').find('.builder_field_item');

    if(!formID){
        showGlobalMessage('error','<?php echo esc_js(__('Missing form ID.','gpt3-ai-content-generator')); ?>');
        return;
    }
    if(!title || !description || !prompt){
        showGlobalMessage('error','<?php echo esc_js(__('Please fill all required fields.','gpt3-ai-content-generator')); ?>');
        return;
    }

    var fieldsData = [];
    var missingID = false;
    $fields.each(function(idx, el){
        var $el   = $(el);
        var type  = $el.data('type');
        var label = $el.find('.builder_label_input').val().trim();
        var fid   = $el.find('.builder_id_input').val().trim();
        if(!label || !fid){
            missingID = true;
        }
        var minVal = $el.find('.min_value').val() || "";
        var maxVal = $el.find('.max_value').val() || "";
        var rowsVal= $el.find('.rows_value').val() || "";
        var colsVal= $el.find('.cols_value').val() || "";

        var optionValues = [];
        $el.find('.options_list .option_value').each(function(){
            var val = $(this).val().trim();
            if(val){
                optionValues.push(val);
            }
        });
        var optionsStr = optionValues.join('|');

        // For fileupload, also read the "file_types" if present
        var fileTypes = "";
        if(type === 'fileupload'){
            var $fTypesInput = $el.find('.file_types');
            if($fTypesInput.length){
                fileTypes = $fTypesInput.val().trim();
            }
        }

        fieldsData.push({
            type: type,
            label: label ? label : 'Field'+(idx+1),
            id: fid,
            min: minVal,
            max: maxVal,
            rows: rowsVal,
            cols: colsVal,
            options: optionsStr,
            file_types: fileTypes
        });
    });
    if(missingID){
        showGlobalMessage('error','<?php echo esc_js(__('Each field must have a Label and ID','gpt3-ai-content-generator')); ?>');
        return;
    }

    // Read interface data from hidden inputs / text fields
    var interfaceData = {
        'wpaicg_form_category':          $('#wpaicg_editform_category').val().trim(),
        'wpaicg_form_color':             $('#wpaicg_editform_color').val().trim(),
        'wpaicg_form_icon':              $('#wpaicg_editform_icon').val().trim(),
        'wpaicg_form_header':            $('#wpaicg_editform_header').val(),
        'wpaicg_form_editor':            $('#wpaicg_editform_editor').val().trim(),
        'wpaicg_form_copy_button':       $('#wpaicg_editform_copy_button').val(),
        'wpaicg_form_ddraft':           $('#wpaicg_editform_ddraft').val(),
        'wpaicg_form_dclear':           $('#wpaicg_editform_dclear').val(),
        'wpaicg_form_dnotice':          $('#wpaicg_editform_dnotice').val(),
        'wpaicg_form_ddownload':        $('#wpaicg_editform_ddownload').val(),
        'wpaicg_form_copy_text':        $('#wpaicg_editform_copy_text').val().trim(),
        'wpaicg_form_feedback_buttons': $('#wpaicg_editform_feedback_buttons').val(),
        'wpaicg_form_generate_text':    $('#wpaicg_editform_generate_text').val().trim(),
        'wpaicg_form_noanswer_text':    $('#wpaicg_editform_noanswer_text').val().trim(),
        'wpaicg_form_draft_text':       $('#wpaicg_editform_draft_text').val().trim(),
        'wpaicg_form_clear_text':       $('#wpaicg_editform_clear_text').val().trim(),
        'wpaicg_form_stop_text':        $('#wpaicg_editform_stop_text').val().trim(),
        'wpaicg_form_cnotice_text':     $('#wpaicg_editform_cnotice_text').val().trim(),
        'wpaicg_form_download_text':    $('#wpaicg_editform_download_text').val().trim(),
        'wpaicg_form_bgcolor':          $('#wpaicg_editform_bgcolor').val().trim()
    };

    // Advanced model settings
    var formSettingsData = {
        max_tokens: parseInt($('#wpaicg_editform_max_tokens').val()) || 1500,
        top_p: parseFloat($('#wpaicg_editform_top_p').val()) || 1,
        best_of: parseInt($('#wpaicg_editform_best_of').val()) || 1,
        frequency_penalty: parseFloat($('#wpaicg_editform_frequency_penalty').val()) || 0,
        presence_penalty: parseFloat($('#wpaicg_editform_presence_penalty').val()) || 0,
        stop: $('#wpaicg_editform_stop').val().trim()
    };

    // Embedding settings
    var embeddingSettings = {
        use_embeddings: $('#wpaicg_editform_embeddings').val(),
        vectordb: $('#wpaicg_editform_vectordb').val(),
        collections: $('#wpaicg_editform_collections').val(),
        pineconeindexes: $('#wpaicg_editform_pineconeindexes').val(),
        suffix_text: $('#wpaicg_editform_suffix_text').val(),
        suffix_position: $('#wpaicg_editform_suffix_position').val(),
        use_default_embedding_model: $('#wpaicg_editform_use_default_embedding_model').val(),
        selected_embedding_provider: $('#wpaicg_editform_selected_embedding_provider').val(),
        selected_embedding_model: $('#wpaicg_editform_selected_embedding_model').val(),
        embeddings_limit: $('#wpaicg_editform_embeddings_limit').val()
    };

    var $status = $('#wpaicg_edit_status');
    $status.hide().css('color','green');

    $.ajax({
        url: ajaxurl,
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'wpaicg_save_edited_form',
            nonce: '<?php echo esc_js(wp_create_nonce("wpaicg_save_edited_form_nonce")); ?>',
            form_id: formID,
            title: title,
            description: description,
            prompt: prompt,
            fields: JSON.stringify(fieldsData),
            interface: interfaceData,
            engine: engine,
            model_settings: formSettingsData,
            embedding_settings: embeddingSettings
        },
        success: function(response){
            if(response.success){
                showGlobalMessage('success', response.data.message);
                $status.text(response.data.message).show();
            } else {
                showGlobalMessage('error', response.data.message || 'Error saving form');
                $status.css('color','red').text(response.data.message || 'Error').show();
            }
        },
        error: function(){
            showGlobalMessage('error','<?php echo esc_js(__('Ajax error saving form','gpt3-ai-content-generator')); ?>');
            $status.css('color','red')
                .text('<?php echo esc_js(__('Ajax error saving form','gpt3-ai-content-generator')); ?>').show();
        }
    });
});

////////////////////////////////////////////////////////////////////////////////
// EDIT TABS NAVIGATION
////////////////////////////////////////////////////////////////////////////////
$(document).on('click', '.wpaicg_edit_tabs li', function(){
    var tab = $(this).data('tab');
    $('.wpaicg_edit_tabs li').removeClass('active');
    $(this).addClass('active');
    $('.wpaicg_edit_tab_content').removeClass('active').hide();
    $('#'+tab).addClass('active').show();
});

////////////////////////////////////////////////////////////////////////////////
// MODEL SETTINGS ICON + MODAL
////////////////////////////////////////////////////////////////////////////////
$('#wpaicg_editform_model_settings_icon').on('click', function(e){
    e.preventDefault();
    $('#wpaicg_editform_model_settings_modal').show();
});

// Close modal
$('#wpaicg_editform_model_settings_close').on('click', function(){
    $('#wpaicg_editform_model_settings_modal').hide();
});

// Track range values
$('#wpaicg_editform_top_p').on('input', function(){
    $('#wpaicg_editform_top_p_value').text($(this).val());
});
$('#wpaicg_editform_frequency_penalty').on('input', function(){
    $('#wpaicg_editform_frequency_penalty_value').text($(this).val());
});
$('#wpaicg_editform_presence_penalty').on('input', function(){
    $('#wpaicg_editform_presence_penalty_value').text($(this).val());
});

// Save button inside modal
$('#wpaicg_editform_model_settings_save').on('click', function(){
    $('#wpaicg_editform_model_settings_modal').hide();
});

/*************************************************************
 * Shared functions (from the main forms_js)
 *************************************************************/
window.showGlobalMessage = window.showGlobalMessage || function(){};
window.hideShortcodeSnippet = window.hideShortcodeSnippet || function(){};
window.showShortcodeSnippet = window.showShortcodeSnippet || function(){};
window.copyToClipboard = window.copyToClipboard || function(){};
window.replacePlaceholderInText = window.replacePlaceholderInText || function(){};
window.removePlaceholderFromText = window.removePlaceholderFromText || function(){};

window.getFieldSettingsHtml = window.getFieldSettingsHtml || function(type, minVal, maxVal, rowsVal, colsVal, optionsArr) {
    if(typeof minVal === 'undefined')  { minVal = ''; }
    if(typeof maxVal === 'undefined')  { maxVal = ''; }
    if(typeof rowsVal === 'undefined') { rowsVal = ''; }
    if(typeof colsVal === 'undefined') { colsVal = ''; }
    if(!Array.isArray(optionsArr))     { optionsArr = []; }

    var html = '<div class="field_settings">';

    // text/number
    if(type === 'text' || type === 'number') {
        html += '<label>Min: <input type="number" class="min_value" value="'+ minVal +'"/></label>';
        html += '<label>Max: <input type="number" class="max_value" value="'+ maxVal +'"/></label>';
    }
    // textarea
    if(type === 'textarea') {
        html += '<label>Min: <input type="number" class="min_value" value="'+ minVal +'"/></label>';
        html += '<label>Max: <input type="number" class="max_value" value="'+ maxVal +'"/></label>';
        html += '<label>Rows: <input type="number" class="rows_value" value="'+ rowsVal +'"/></label>';
        html += '<label>Cols: <input type="number" class="cols_value" value="'+ colsVal +'"/></label>';
    }
    // checkbox / radio / select
    if(type === 'checkbox' || type === 'radio' || type === 'select') {
        html += '<ul class="options_list">';
        optionsArr.forEach(function(opt){
            opt = opt.replace(/"/g, "&quot;");
            html += '<li><input type="text" class="option_value" value="'+ opt +'" /></li>';
        });
        html += '</ul>';
        html += '<button type="button" class="add_option_btn">+ Add Option</button>';
    }

    // NEW: fileupload
    if(type === 'fileupload') {
        html += '<label><?php echo esc_js(__("Allowed File Types (comma-separated):","gpt3-ai-content-generator")); ?><br/>';
        html += '<input type="text" class="file_types" value="txt,csv" /></label>';
    }

    html += '</div>';
    return html;
};

})(jQuery);
</script>