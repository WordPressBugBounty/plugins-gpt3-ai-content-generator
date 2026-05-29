<?php
 namespace WPAICG\Chat\Storage\ReaderMethods; if (!defined('ABSPATH')) { exit; } function generate_message_id_logic(): string { return str_replace('.', '', uniqid('aipkit-msg-', true)); }