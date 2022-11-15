<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Webhook_Listener;

/**
 * AINSYS connector core.
 *
 * @class          AINSYS connector settings
 * @version        1.0.0
 * @author         AINSYS
 */
class Settings implements Hooked {

	/**
	 * AINSYS log table name.
	 */
	public static string $ainsys_entities_settings_table = 'ainsys_entities_settings';

	/**
	 * AINSYS options and their default values.
	 */
	public static array $ainsys_options = array(
		'ansys_api_key'          => '',
		'handshake_url'          => '',
		'webhook_url'            => '',
		'server'                 => 'https://user-api.ainsys.com/',
		'sys_id'                 => '',
		'connectors'             => '',
		'workspace'              => 14,
		'backup_email'           => '',
		'backup_email_1'         => '',
		'backup_email_2'         => '',
		'backup_email_3'         => '',
		'backup_email_4'         => '',
		'backup_email_5'         => '',
		'backup_email_6'         => '',
		'backup_email_7'         => '',
		'backup_email_8'         => '',
		'backup_email_9'         => '',
		'do_log_transactions'    => 1,
		'log_transactions_since' => '',
		'log_until_certain_time' => '',
		'log_select_value'       => -1,
		'full_uninstall'         => 0,
		'connector_id'           => '',
		'client_full_name'       => '',
		'client_company_name'    => '',
		'client_tin'             => '',
		'debug_log'              => '',
	);

	/**
	 * Init hooks.
	 */
	public function init_hooks() {

		add_action( 'admin_init', array( $this, 'register_options' ) );
		add_action( 'init', array( $this, 'check_to_auto_disable_logging' ) );

	}


	/**
	 * Autodisables logging.
	 *
	 * @return void
	 */
	public function check_to_auto_disable_logging(): void {
		$logging_enabled = (int) self::get_option( 'do_log_transactions' );
		// Generate log until time settings
		$current_time = time();
		$limit_time   = (int) self::get_option( 'log_until_certain_time' );

		// make it really infinite as in select infinite option is -1;
		if ( $limit_time < 0 ) {
			return;
		}

		if ( $logging_enabled && $limit_time && ( $current_time < $limit_time ) ) {
			self::set_option( 'do_log_transactions', 1 );
		} else {
			self::set_option( 'do_log_transactions', 0 );
			self::set_option( 'log_until_certain_time', -1 );
		}
	}

	/**
	 * Gets options value by name.
	 *
	 * @param $name
	 *
	 * @return mixed|void
	 */
	public static function get_option( $name ) {
		return get_option( self::get_option_name( $name ) );
	}


	/**
	 * Gets full options name.
	 *
	 * @param  string $name
	 *
	 * @return string
	 */
	public static function get_option_name( string $name ): string {

		return self::get_plugin_name() . '_' . $name;
	}


	/**
	 * Gets plugin uniq name to show on the settings page.
	 *
	 * @return string
	 */
	public static function get_plugin_name(): string {

		return strtolower( str_replace( '\\', '_', __NAMESPACE__ ) );
	}


	/**
	 * Activates plugin
	 *
	 * @return void
	 */
	public static function activate(): void {
		global $wpdb;

		update_option( self::get_plugin_name(), AINSYS_CONNECTOR_VERSION , false);

		flush_rewrite_rules();
		ob_start();

		$wpdb->hide_errors();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( self::get_schema() );

		update_option( self::get_plugin_name() . '_db_version', AINSYS_CONNECTOR_VERSION, false);

		ob_get_clean();

	}


	/**
	 * Gets Table schema.
	 *
	 * @return string
	 */
	private static function get_schema(): string {

		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 *
		 * This may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
		 * indexes first causes too much load on some servers/larger DB.
		 */

		$table_entities_settings = $wpdb->prefix . self::$ainsys_entities_settings_table;

		return "CREATE TABLE $table_entities_settings (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `entity` text DEFAULT NULL,
                `setting_name` text DEFAULT NULL,
                `setting_key` text DEFAULT NULL,
                `value` text DEFAULT NULL,
                `creation_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY  (`id`)
            ) $collate;";
	}

	/**
	 * Deactivates plugin. Removes logs, settings, etc. if the option 'full_uninstall' is on.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		if ( (int) self::get_option( 'full_uninstall' ) ) {
			self::uninstall();
		}
	}

	/**
	 * Gets full option name.
	 *
	 * @param  string $name
	 *
	 * @return string
	 */
	public static function get_setting_name( string $name ): string {
		return self::get_plugin_name() . '_' . $name;
	}


	/**
	 * Updates an option.
	 *
	 * @param  string $name
	 * @param         $value
	 *
	 * @return bool
	 */
	public static function set_option( string $name, $value ): bool {
		return update_option( self::get_option_name( $name ), $value, 'no' );
	}


	/**
	 * Gets saved email or admin email.
	 *
	 * @return bool|mixed|void
	 */
	public static function get_backup_email( $mail = '' ) {
		$field  = 'backup_email';
		$field .= $mail ? '_' . $mail : '';
		if ( ! empty( self::get_option( $field ) ) ) {
			return self::get_option( $field );
		}

		if ( empty( $mail ) && ! empty( get_option( 'admin_email' ) ) ) {
			return get_option( 'admin_email' );
		}

		return false;
	}


	/**
	 * Generates a list of Entities.
	 *
	 * @return array
	 */
	public static function get_entities(): array {
		/// Get WordPress pre installed entities.
		$entities = array(
			'user'     => __( 'User / fields', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
			'comments' => __( 'Comments / fields', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
		);

		return apply_filters( 'ainsys_get_entities_list', $entities );
	}

	/**
	 * Gives an ability to provide specific to entity type fields getters supplied by child plugins.
	 *
	 * @return array
	 */
	public static function get_entity_fields_handlers(): array {
		$field_getters = array(
			'user'     => array( static::class, 'get_user_fields' ),
			'comments' => array( static::class, 'get_comments_fields' ),
		);

		return apply_filters( 'ainsys_get_entity_fields_handlers', $field_getters );
	}


	/**
	 * Generates a list of settings for entity field with default values
	 * $entity param used for altering settins depending on entity
	 *
	 * @param  string $entity
	 *
	 * @return array
	 */
	public static function get_entities_settings( string $entity = '' ): array {

		$default_apis = apply_filters(
			'ainsys_default_apis_for_entities',
			array(
				'wordpress' => '',
			)
		);

		return array(
			'id'          => array(
				'nice_name' => __( 'Id', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => '',
				'type'      => 'constant',
			),
			'api'         => array(
				'nice_name' => __( 'API', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => $default_apis,
				'type'      => 'constant',
			),
			'read'        => array(
				'nice_name' => __( 'Read', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => '1',
				'type'      => 'bool',
			),
			'write'       => array(
				'nice_name' => __( 'Write', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => '0',
				'type'      => 'bool',
			),
			'required'    => array(
				'nice_name' => __( 'Required', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => '0',
				'type'      => 'bool',
			),
			'unique'      => array(
				'nice_name' => __( 'Unique', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => '0',
				'type'      => 'bool',
			),
			'data_type'   => array(
				'nice_name' => __( 'Data type', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => array(
					'string' => '1',
					'int'    => '',
					'bool'   => '',
					'mixed'  => '',
				),
				'type'      => 'acf' === $entity ? 'constant' : 'select',
			),
			'description' => array(
				'nice_name' => __( 'Description', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => '',
				'type'      => 'string',
			),
			'sample'      => array(
				'nice_name' => __( 'Sample', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'default'   => '',
				'type'      => 'string',
			),
		);
	}

	/**
	 * Gets entity field settings from DB.
	 *
	 * @param  string $where
	 * @param  bool   $single
	 *
	 * @return array
	 */
	public static function get_saved_entity_settings_from_db( string $where = '', bool $single = true ): array {
		global $wpdb;

		$query    = sprintf( "SELECT * FROM $wpdb->prefix%s %s" , self::$ainsys_entities_settings_table, $where );
		$result   = $wpdb->get_results( $query, ARRAY_A );

		if ( isset( $result[0]['value'] ) && $single ) {
			$keys = array_column( $result, 'setting_key' );
			if ( count( $result ) > 1 && isset( array_flip( $keys )['saved_field'] ) ) {
				$saved_settins_id = array_flip( $keys )['saved_field'];
				$data             = maybe_unserialize( $result[ $saved_settins_id ]['value'] );
				$data['id']       = $result[ $saved_settins_id ]['id'] ?? 0;
			} else {
				$data       = maybe_unserialize( $result[0]['value'] );
				$data['id'] = $result[0]['id'] ?? 0;
			}
		} else {
			$data = $result;
		}

		return $data ?? array();
	}


	/**
	 * Generates fields for COMMENTS entity.
	 *
	 * @return array
	 */
	public static function get_comments_fields(): array {
		$prepered_fields = array(
			'comment_ID'           => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_post_ID'      => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_author'       => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_author_email' => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_author_url'   => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_author_IP'    => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_date'         => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_date_gmt'     => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_content'      => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_karma'        => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_approved'     => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_agent'        => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_type'         => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_parent'       => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'user_id'              => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'children'             => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'populated_children'   => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'post_fields'          => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
		);

		$extra_fields = apply_filters( 'ainsys_prepare_extra_comment_fields', array() );

		return array_merge( $prepered_fields, $extra_fields );
	}

	/**
	 * Generates fields for USER entity
	 *
	 * @return array
	 */
	public static function get_user_fields(): array {
		$prepered_fields = array(
			'ID'                   => array(
				'nice_name' => __( '{ID}', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'api'       => 'wordpress',
			),
			'user_login'           => array(
				'nice_name' => __( 'User login', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'api'       => 'wordpress',
			),
			'user_nicename'        => array(
				'nice_name' => __( 'Readable name', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'api'       => 'wordpress',
			),
			'user_email'           => array(
				'nice_name' => __( 'User mail', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'api'       => 'wordpress',
				'children'  => array(
					'primary'   => array(
						'nice_name' => __( 'Main email', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
						'api'       => 'wordpress',
					),
					'secondary' => array(
						'nice_name' => '',
						'api'       => 'wordpress',
					),
				),
			),
			'user_url'             => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'user_registered'      => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'user_activation_key'  => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'user_status'          => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'display_name'         => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'first_name'           => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'last_name'            => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'nickname'             => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'nice_name'            => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'rich_editing'         => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'syntax_highlighting'  => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'comment_shortcuts'    => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'admin_color'          => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'use_ssl'              => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'show_admin_bar_front' => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
			'locale'               => array(
				'nice_name' => '',
				'api'       => 'wordpress',
			),
		);

		$extra_fields = apply_filters( 'ainsys_prepare_extra_user_fields', array() );

		return array_merge( $prepered_fields, $extra_fields );
	}

	/**
	 * Registers options.
	 *
	 */
	public static function register_options(): void {

		foreach ( self::$ainsys_options as $option_name => $option_value ) {
			if ( ! empty( $option_value ) ) {
				register_setting( self::get_setting_name( 'group' ), self::get_setting_name( $option_name ), array( 'default' => $option_value ) );
			} else {
				register_setting( self::get_setting_name( 'group' ), self::get_setting_name( $option_name ) );
			}
		}

		register_setting(
			self::get_setting_name( 'group' ),
			self::get_setting_name( 'hook_url' ),
			array(
				Webhook_Listener::class,
				'get_webhook_url',
			)
		);
	}

	/**
	 * Uninstalls plugin.
	 */
	public static function uninstall(): void {

		foreach ( self::$ainsys_options as $option_name => $option_value ) {
			delete_option( self::get_setting_name( $option_name ) );
		}

		delete_option( self::get_setting_name( 'hook_url' ) );

		global $wpdb;

		$wpdb->query( sprintf( 'DROP TABLE IF EXISTS %s', $wpdb->prefix . self::$ainsys_entities_settings_table ) );

	}

}
