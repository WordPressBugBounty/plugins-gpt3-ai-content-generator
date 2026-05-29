<?php
 namespace WPAICG\Chat\Storage\LoggerMethods; if (!defined('ABSPATH')) { exit; } function generate_parent_id_logic(): string { return str_replace('.', '', uniqid('aipkit-parent-', true)); }