<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/processor/sse/start/class-sse-request-executor.php
// Status: NEW FILE

namespace WPAICG\Core\Stream\Processor\SSE\Start;

use WPAICG\Core\Stream\Processor\SSEStreamProcessor;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Executes the cURL request for SSE streaming.
 */
class SSERequestExecutor {

    private $processorInstance;

    public function __construct(SSEStreamProcessor $processorInstance) {
        $this->processorInstance = $processorInstance;
    }

    /**
     * Executes the cURL request and handles the streaming callback.
     *
     * @param string $endpoint_url The API endpoint URL.
     * @param array $curl_headers HTTP headers for cURL.
     * @param string $curl_post_json JSON encoded POST data.
     * @return array ['final_http_code' => int, 'curl_error_num' => int, 'curl_error_msg' => string]
     */
    public function execute(string $endpoint_url, array $curl_headers, string $curl_post_json): array {
        $strategy = $this->processorInstance->get_strategy();
        if (!$strategy) {
            return ['final_http_code' => 0, 'curl_error_num' => -1, 'curl_error_msg' => 'Strategy not set for executor.'];
        }

        $curl_options_base = $strategy->get_request_options('stream');
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_post_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, [$this->processorInstance, 'curl_stream_callback_public_wrapper']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, $curl_options_base['timeout'] ?? 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $curl_options_base['sslverify'] ?? true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ($curl_options_base['sslverify'] ?? true) ? 2 : 0);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        if (!empty($curl_options_base['user-agent'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $curl_options_base['user-agent']);
        }

        curl_exec($ch);

        $final_http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error_num  = curl_errno($ch);
        $curl_error_msg  = curl_error($ch);
        curl_close($ch);

        error_log("SSERequestExecutor: cURL execution finished. HTTP Code: {$final_http_code}, Error: {$curl_error_num} ({$curl_error_msg}), Chunks: {$this->processorInstance->get_curl_chunk_counter()}, DataSent: {$this->processorInstance->get_data_sent_to_frontend_status()}, ErrorSent: {$this->processorInstance->get_error_occurred_status()}, Conv: {$this->processorInstance->get_current_conversation_uuid()}, MsgId: {$this->processorInstance->get_current_bot_message_id()}.");

        return [
            'final_http_code' => $final_http_code,
            'curl_error_num'  => $curl_error_num,
            'curl_error_msg'  => $curl_error_msg,
        ];
    }
}