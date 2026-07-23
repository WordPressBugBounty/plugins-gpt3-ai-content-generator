<?php
/**
 * Shared Partial: Provider setup notice.
 *
 * Expected variables:
 * - $aipkit_notice_id (string) Unique HTML id.
 * - $aipkit_notice_class (string, optional) Additional CSS classes.
 * - $aipkit_notice_context (string, optional) Phrase following "to", such as
 *   "use this chatbot".
 * - $aipkit_notice_messages (array<string,string>, optional) Provider-specific
 *   message overrides.
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template-local values only.

$aipkit_provider_notice_id = isset($aipkit_notice_id)
    ? sanitize_html_class((string) $aipkit_notice_id)
    : '';
$aipkit_provider_notice_class = isset($aipkit_notice_class)
    ? trim((string) $aipkit_notice_class)
    : '';
$aipkit_provider_notice_context = isset($aipkit_notice_context)
    ? trim((string) $aipkit_notice_context)
    : __('continue', 'gpt3-ai-content-generator');
$aipkit_provider_notice_message_overrides = isset($aipkit_notice_messages) && is_array($aipkit_notice_messages)
    ? $aipkit_notice_messages
    : [];

if ($aipkit_provider_notice_id === '') {
    return;
}

$aipkit_provider_notice_definitions = [
    'openai' => [
        'credential' => __('an OpenAI API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect OpenAI', 'gpt3-ai-content-generator'),
    ],
    'openrouter' => [
        'credential' => __('an OpenRouter API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect OpenRouter', 'gpt3-ai-content-generator'),
    ],
    'google' => [
        'credential' => __('a Google API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect Google', 'gpt3-ai-content-generator'),
    ],
    'azure' => [
        'credential' => __('Azure credentials', 'gpt3-ai-content-generator'),
        'action' => __('Connect Azure', 'gpt3-ai-content-generator'),
    ],
    'claude' => [
        'credential' => __('an Anthropic API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect Anthropic', 'gpt3-ai-content-generator'),
    ],
    'deepseek' => [
        'credential' => __('a DeepSeek API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect DeepSeek', 'gpt3-ai-content-generator'),
    ],
    'xai' => [
        'credential' => __('an xAI API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect xAI', 'gpt3-ai-content-generator'),
    ],
    'ollama' => [
        'credential' => __('an Ollama server', 'gpt3-ai-content-generator'),
        'action' => __('Connect Ollama', 'gpt3-ai-content-generator'),
    ],
    'replicate' => [
        'credential' => __('a Replicate API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect Replicate', 'gpt3-ai-content-generator'),
    ],
    'pexels' => [
        'credential' => __('a Pexels API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect Pexels', 'gpt3-ai-content-generator'),
    ],
    'pixabay' => [
        'credential' => __('a Pixabay API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect Pixabay', 'gpt3-ai-content-generator'),
    ],
    'pinecone' => [
        'credential' => __('Pinecone credentials', 'gpt3-ai-content-generator'),
        'action' => __('Connect Pinecone', 'gpt3-ai-content-generator'),
    ],
    'qdrant' => [
        'credential' => __('Qdrant credentials', 'gpt3-ai-content-generator'),
        'action' => __('Connect Qdrant', 'gpt3-ai-content-generator'),
    ],
    'chroma' => [
        'credential' => __('a Chroma server', 'gpt3-ai-content-generator'),
        'action' => __('Connect Chroma', 'gpt3-ai-content-generator'),
    ],
    'elevenlabs' => [
        'credential' => __('an ElevenLabs API key', 'gpt3-ai-content-generator'),
        'action' => __('Connect ElevenLabs', 'gpt3-ai-content-generator'),
    ],
];

$aipkit_provider_notice_messages = [];
foreach ($aipkit_provider_notice_definitions as $aipkit_provider_notice_key => $aipkit_provider_notice_definition) {
    $aipkit_provider_notice_messages[$aipkit_provider_notice_key] = sprintf(
        /* translators: 1: required provider credential, 2: feature context. */
        __('Connect %1$s to %2$s.', 'gpt3-ai-content-generator'),
        $aipkit_provider_notice_definition['credential'],
        $aipkit_provider_notice_context
    );
}

foreach ($aipkit_provider_notice_message_overrides as $aipkit_provider_notice_key => $aipkit_provider_notice_message) {
    if (is_string($aipkit_provider_notice_key) && is_scalar($aipkit_provider_notice_message)) {
        $aipkit_provider_notice_messages[$aipkit_provider_notice_key] = (string) $aipkit_provider_notice_message;
    }
}

$aipkit_provider_notice_default_message = sprintf(
    /* translators: %s: feature context. */
    __('Connect the required provider credentials to %s.', 'gpt3-ai-content-generator'),
    $aipkit_provider_notice_context
);
$aipkit_provider_notice_settings_url = add_query_arg(
    [
        'page' => 'wpaicg',
        'aipkit_module' => 'settings',
        'aipkit_settings_page' => 'ai',
    ],
    admin_url('admin.php')
);
?>
<div
    id="<?php echo esc_attr($aipkit_provider_notice_id); ?>"
    class="aipkit_notification_bar aipkit_notification_bar--warning aipkit_provider_key_notice aipkit_provider_notice--hidden <?php echo esc_attr($aipkit_provider_notice_class); ?>"
    data-aipkit-provider-notice="1"
    data-aipkit-settings-url="<?php echo esc_url($aipkit_provider_notice_settings_url); ?>"
    data-message-default="<?php echo esc_attr($aipkit_provider_notice_default_message); ?>"
    <?php foreach ($aipkit_provider_notice_messages as $aipkit_provider_notice_key => $aipkit_provider_notice_message) : ?>
        data-message-<?php echo esc_attr($aipkit_provider_notice_key); ?>="<?php echo esc_attr($aipkit_provider_notice_message); ?>"
    <?php endforeach; ?>
    <?php foreach ($aipkit_provider_notice_definitions as $aipkit_provider_notice_key => $aipkit_provider_notice_definition) : ?>
        data-action-<?php echo esc_attr($aipkit_provider_notice_key); ?>="<?php echo esc_attr($aipkit_provider_notice_definition['action']); ?>"
    <?php endforeach; ?>
>
    <span class="dashicons dashicons-warning aipkit_notification_bar__icon" aria-hidden="true"></span>
    <div class="aipkit_notification_bar__content">
        <p>
            <span class="aipkit_provider_notice_message">
                <?php echo esc_html($aipkit_provider_notice_default_message); ?>
            </span>
        </p>
    </div>
    <div class="aipkit_notification_bar__actions">
        <a
            href="<?php echo esc_url($aipkit_provider_notice_settings_url); ?>"
            class="aipkit_btn aipkit_provider_notice_settings_link"
            data-aipkit-provider-action
            data-aipkit-settings-page="ai"
            data-aipkit-settings-card="OpenAI"
            data-aipkit-settings-card-kind="provider"
        >
            <?php esc_html_e('Connect provider', 'gpt3-ai-content-generator'); ?>
        </a>
    </div>
</div>
<?php
unset(
    $aipkit_provider_notice_id,
    $aipkit_provider_notice_class,
    $aipkit_provider_notice_context,
    $aipkit_provider_notice_message_overrides,
    $aipkit_provider_notice_definitions,
    $aipkit_provider_notice_messages,
    $aipkit_provider_notice_default_message,
    $aipkit_provider_notice_settings_url,
    $aipkit_provider_notice_key,
    $aipkit_provider_notice_definition,
    $aipkit_provider_notice_message,
    $aipkit_notice_id,
    $aipkit_notice_class,
    $aipkit_notice_context,
    $aipkit_notice_messages
);
