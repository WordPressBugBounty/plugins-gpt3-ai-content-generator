<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Fired during plugin activation
 *
 * @link       https://aipower.org
 * @since      1.0.0
 *
 * @package    Wp_Ai_Content_Generator
 * @subpackage Wp_Ai_Content_Generator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Ai_Content_Generator
 * @subpackage Wp_Ai_Content_Generator/includes
 * @author     Senol Sahin <senols@gmail.com>
 */
class Wp_Ai_Content_Generator_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::createTable();
        self::create_image_tables();
        self::create_form_tables();
        self::create_chat_tables();
        self::create_ai_account_tables();
	}

	public static function createTable()
	{
		global $wpdb;

		$wpaicgTable = $wpdb->prefix . 'wpaicg';
		if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$wpaicgTable)) != $wpaicgTable) {
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $wpaicgTable (
						ID mediumint(11) NOT NULL AUTO_INCREMENT,
						name text NOT NULL,
						temperature float NOT NULL,
						max_tokens float NOT NULL,
						top_p float NOT NULL,
						best_of float NOT NULL,
						frequency_penalty float NOT NULL,
						presence_penalty float NOT NULL,
						img_size text NOT NULL,
						api_key text NOT NULL,
						wpai_language VARCHAR(255) NOT NULL,
						wpai_add_img BOOLEAN NOT NULL,
						wpai_add_intro BOOLEAN NOT NULL,
						wpai_add_conclusion BOOLEAN NOT NULL,
						wpai_add_tagline BOOLEAN NOT NULL,
						wpai_add_faq BOOLEAN NOT NULL,
						wpai_add_keywords_bold BOOLEAN NOT NULL,
						wpai_number_of_heading INT NOT NULL,
						wpai_modify_headings BOOLEAN NOT NULL,
						wpai_heading_tag VARCHAR(10) NOT NULL,
						wpai_writing_style VARCHAR(255) NOT NULL,
						wpai_writing_tone VARCHAR(255) NOT NULL,
						wpai_target_url VARCHAR(255) NOT NULL,
						wpai_target_url_cta VARCHAR(255) NOT NULL,
						wpai_cta_pos VARCHAR(255) NOT NULL,
						added_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
						modified_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
						PRIMARY KEY  (ID)
					) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
            $sampleData = [
                'name'						=> 'wpaicg_settings',
                'temperature' 				=> '1',
                'max_tokens' 				=> '1500',
                'top_p' 					=> '0.01',
                'best_of' 					=> '1',
                'frequency_penalty' 		=> '0.01',
                'presence_penalty' 			=> '0.01',
                'img_size' 					=> '1024x1024',
                'api_key' 					=> 'sk..',
                'wpai_language' 			=> 'en',
                'wpai_add_img' 				=> 1,
                'wpai_add_intro' 			=> 'false',
                'wpai_add_conclusion' 		=> 'false',
                'wpai_add_tagline' 			=> 'false',
                'wpai_add_faq' 				=> 'false',
                'wpai_add_keywords_bold' 	=> 'false',
                'wpai_number_of_heading' 	=>  3,
                'wpai_modify_headings' 		=> 'false',
                'wpai_heading_tag' 			=> 'h1',
                'wpai_writing_style' 		=> 'infor',
                'wpai_writing_tone' 		=> 'formal',
                'wpai_cta_pos' 				=> 'beg',
                'added_date' 				=> gmdate('Y-m-d H:i:s'),
                'modified_date'				=> gmdate('Y-m-d H:i:s')

            ];

            $result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpaicgTable WHERE name = %s", 'wpaicg_settings' ) );

            if(!empty($result->name)){
                $wpdb->update(
                    $wpaicgTable,
                    $sampleData,
                    [
                        'name'			=> 'wpaicg_settings'
                    ],
                    [
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ],
                    [
                        '%s'
                    ]
                );
            }else{
                $wpdb->insert(
                    $wpaicgTable,
                    $sampleData,
                    [
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    ]
                );
            }
		}
	}

    public static function create_image_tables()
    {
        global $wpdb;
    
        if ( is_admin() ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
            // Table 1: wpaicg_image_logs
            $wpaicgLogTable = $wpdb->prefix . 'wpaicg_image_logs';
            $table_exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $wpaicgLogTable
                )
            );
    
            if ( $table_exists !== $wpaicgLogTable ) {
                $charset_collate = $wpdb->get_charset_collate();
    
                $sql = "CREATE TABLE {$wpaicgLogTable} (
                    id mediumint(11) NOT NULL AUTO_INCREMENT,
                    prompt TEXT NOT NULL,
                    source INT NOT NULL DEFAULT '0',
                    shortcode VARCHAR(255) DEFAULT NULL,
                    size VARCHAR(255) DEFAULT NULL,
                    total INT NOT NULL DEFAULT '0',
                    duration VARCHAR(255) DEFAULT NULL,
                    price VARCHAR(255) DEFAULT NULL,
                    created_at VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                ) $charset_collate;";
    
                dbDelta( $sql );
            }
    
            // Table 2: wpaicg_imagetokens
            $wpaicgTokensTable = $wpdb->prefix . 'wpaicg_imagetokens';
            $token_table_exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $wpaicgTokensTable
                )
            );
    
            if ( $token_table_exists !== $wpaicgTokensTable ) {
                $charset_collate = $wpdb->get_charset_collate();
    
                $sql = "CREATE TABLE {$wpaicgTokensTable} (
                    id mediumint(11) NOT NULL AUTO_INCREMENT,
                    tokens VARCHAR(255) DEFAULT NULL,
                    user_id VARCHAR(255) DEFAULT NULL,
                    session_id VARCHAR(255) DEFAULT NULL,
                    source VARCHAR(255) DEFAULT NULL,
                    created_at VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                ) $charset_collate;";
    
                dbDelta( $sql );
            }
        }
    }
    

    public static function create_form_tables()
    {
        global $wpdb;
    
        if ( is_admin() ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
            // Table 1: wpaicg_form_logs
            $wpaicgLogTable = $wpdb->prefix . 'wpaicg_form_logs';
            $log_table_exists = $wpdb->get_var(
                $wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgLogTable)
            );
    
            if ( $log_table_exists !== $wpaicgLogTable ) {
                $charset_collate = $wpdb->get_charset_collate();
    
                $sql = "CREATE TABLE {$wpaicgLogTable} (
                    id mediumint(11) NOT NULL AUTO_INCREMENT,
                    prompt TEXT NOT NULL,
                    source INT NOT NULL DEFAULT '0',
                    data LONGTEXT NOT NULL,
                    prompt_id VARCHAR(255) DEFAULT NULL,
                    name VARCHAR(255) DEFAULT NULL,
                    model VARCHAR(255) DEFAULT NULL,
                    duration VARCHAR(255) DEFAULT NULL,
                    tokens VARCHAR(255) DEFAULT NULL,
                    created_at VARCHAR(255) NOT NULL,
                    eventID mediumint(11) DEFAULT NULL,
                    userID varchar(255) DEFAULT NULL,
                    PRIMARY KEY (id)
                ) $charset_collate;";
    
                dbDelta( $sql );
            } else {
                // Check and add missing columns
                $columns = $wpdb->get_col("DESCRIBE {$wpaicgLogTable}");
    
                if ( !in_array('eventID', $columns, true) ) {
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static ALTER TABLE, no user input
                    $wpdb->query("ALTER TABLE {$wpaicgLogTable} ADD eventID mediumint(11) DEFAULT NULL");
                }
                if ( !in_array('userID', $columns, true) ) {
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Static ALTER TABLE, no user input
                    $wpdb->query("ALTER TABLE {$wpaicgLogTable} ADD userID varchar(255) DEFAULT NULL");
                }
            }
    
            // Table 2: wpaicg_formtokens
            $wpaicgTokensTable = $wpdb->prefix . 'wpaicg_formtokens';
            $tokens_table_exists = $wpdb->get_var(
                $wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgTokensTable)
            );
    
            if ( $tokens_table_exists !== $wpaicgTokensTable ) {
                $charset_collate = $wpdb->get_charset_collate();
    
                $sql = "CREATE TABLE {$wpaicgTokensTable} (
                    id mediumint(11) NOT NULL AUTO_INCREMENT,
                    tokens VARCHAR(255) DEFAULT NULL,
                    user_id VARCHAR(255) DEFAULT NULL,
                    session_id VARCHAR(255) DEFAULT NULL,
                    source VARCHAR(255) DEFAULT NULL,
                    created_at VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id)
                ) $charset_collate;";
    
                dbDelta( $sql );
            }
    
            // Table 3: wpaicg_form_feedback
            $wpaicgFormFeedbackTable = $wpdb->prefix . 'wpaicg_form_feedback';
            $feedback_table_exists = $wpdb->get_var(
                $wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgFormFeedbackTable)
            );
    
            if ( $feedback_table_exists !== $wpaicgFormFeedbackTable ) {
                $charset_collate = $wpdb->get_charset_collate();
    
                $sql = "CREATE TABLE {$wpaicgFormFeedbackTable} (
                    id mediumint(11) NOT NULL AUTO_INCREMENT,
                    formID mediumint(11) NOT NULL,
                    eventID mediumint(11) DEFAULT NULL,
                    source varchar(255) DEFAULT NULL,
                    formname varchar(255) DEFAULT NULL,
                    response text DEFAULT NULL,
                    session_id varchar(255) DEFAULT NULL,
                    feedback enum('thumbs_up', 'thumbs_down') NOT NULL,
                    comment text DEFAULT NULL,
                    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                ) $charset_collate;";
    
                dbDelta( $sql );
            }
        }
    }
    

    public static function create_chat_tables()
    {
        global $wpdb;
    
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
        // Table 1: wpaicg_chatlogs
        $wpaicgChatLogTable = $wpdb->prefix . 'wpaicg_chatlogs';
        $log_exists = $wpdb->get_var(
            $wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgChatLogTable)
        );
    
        if ( $log_exists !== $wpaicgChatLogTable ) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$wpaicgChatLogTable} (
                id mediumint(11) NOT NULL AUTO_INCREMENT,
                log_session VARCHAR(255) NOT NULL,
                data LONGTEXT NOT NULL,
                page_title TEXT DEFAULT NULL,
                source VARCHAR(255) DEFAULT NULL,
                created_at VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";
            dbDelta( $sql );
        }
    
        // Table 2: wpaicg_chattokens
        $wpaicgChatTokensTable = $wpdb->prefix . 'wpaicg_chattokens';
        $tokens_exists = $wpdb->get_var(
            $wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgChatTokensTable)
        );
    
        if ( $tokens_exists !== $wpaicgChatTokensTable ) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE {$wpaicgChatTokensTable} (
                id mediumint(11) NOT NULL AUTO_INCREMENT,
                tokens VARCHAR(255) DEFAULT NULL,
                user_id VARCHAR(255) DEFAULT NULL,
                session_id VARCHAR(255) DEFAULT NULL,
                source VARCHAR(255) DEFAULT NULL,
                created_at VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";
            dbDelta( $sql );
        }
    }
    

    public static function create_ai_account_tables()
    {
        global $wpdb;
    
        if ( is_admin() ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
            $wpaicgLogTable = $wpdb->prefix . 'wpaicg_token_logs';
            $table_exists = $wpdb->get_var(
                $wpdb->prepare("SHOW TABLES LIKE %s", $wpaicgLogTable)
            );
    
            if ( $table_exists !== $wpaicgLogTable ) {
                $charset_collate = $wpdb->get_charset_collate();
    
                $sql = "CREATE TABLE {$wpaicgLogTable} (
                    id mediumint(11) NOT NULL AUTO_INCREMENT,
                    user_id VARCHAR(255) DEFAULT NULL,
                    module VARCHAR(255) DEFAULT NULL,
                    tokens VARCHAR(255) DEFAULT NULL,
                    created_at VARCHAR(255) NOT NULL,
                    PRIMARY KEY (id),
                    KEY {$wpaicgLogTable}_user_id_index (user_id)
                ) $charset_collate;";
    
                dbDelta( $sql );
            }
        }
    }
    
}
