<?php
 namespace WPAICG\Chat\Storage\LoggerMethods; if (!defined('ABSPATH')) { exit; } function generate_message_id_logic(): string { return str_replace('.', '', uniqid('aipkit-msg-', true)); }