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

	protected static array $settings_tables;

	/**
	 * AINSYS options and their default values.
	 */
	public static array $settings_options;


	/**
	 * @return array|string[]
	 */
	public static function get_settings_tables(): array {

		self::$settings_tables = [
			'logs' => 'ainsys_log',
		];

		return apply_filters( 'ainsys_settings_tables', self::$settings_tables );
	}


	/**
	 * @return array
	 */
	public static function get_settings_options(): array {

		self::$settings_options = [
			'ansys_api_key'            => '',
			'handshake_url'            => '',
			'webhook_url'              => '',
			'server'                   => 'https://user-api.ainsys.com/',
			'sys_id'                   => '',
			'connectors'               => '',
			'workspace'                => 14,
			'backup_email'             => '',
			'backup_email_1'           => '',
			'backup_email_2'           => '',
			'backup_email_3'           => '',
			'backup_email_4'           => '',
			'backup_email_5'           => '',
			'backup_email_6'           => '',
			'backup_email_7'           => '',
			'backup_email_8'           => '',
			'backup_email_9'           => '',
			'do_log_transactions'      => 0,
			'log_transactions_since'   => '',
			'log_until_certain_time'   => 0,
			'log_select_value'         => 1,
			'full_uninstall'           => 0,
			'connector_id'             => '',
			'client_full_name'         => '',
			'client_company_name'      => '',
			'client_tin'               => '',
			'debug_log'                => '',
			'check_connection'         => '',
			'check_connection_entity'  => [],
			'check_controlling_entity' => [],
		];

		return apply_filters( 'ainsys_settings_options', self::$settings_options );
	}


	/**
	 * Init hooks.
	 */
	public function init_hooks() {

		add_action( 'admin_init', [ $this, 'register_options' ] );
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

		$field = 'backup_email';
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
		$entities = [
			'user'    => __( 'User / fields', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'comment' => __( 'Comments / fields', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'attachment' => __( 'Attachments / fields', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'post' => __( 'Posts / fields', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'page' => __( 'Pages / fields', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'menu' => __( 'Menus / fields', AINSYS_CONNECTOR_TEXTDOMAIN ),
		];

		return apply_filters( 'ainsys_get_entities_list', $entities );
	}


	/**
	 * Autodisables logging.
	 *
	 * @return void
	 * @todo плохо релизовано отключение логировния по времени, отчсет идет в js что не есть правильно
	 */
	public static function check_to_auto_disable_logging(): void {

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
			self::set_option( 'log_until_certain_time', - 1 );
		}
	}


	/**
	 * Registers options.
	 *
	 */
	public static function register_options(): void {

		foreach ( self::get_settings_options() as $option_name => $option_value ) {
			register_setting(
				self::get_option_name( 'group' ),
				self::get_option_name( $option_name ),
				[
					'default' => $option_value,
				]
			);
		}

		register_setting(
			self::get_option_name( 'group' ),
			self::get_option_name( 'webhook_url' ),
			[
				'default'           => self::get_option( 'webhook_url' ),
				'sanitize_callback' => [ Webhook_Listener::class, 'get_webhook_url' ],
			]
		);

		self::check_to_auto_disable_logging();
	}


	/**
	 * Activates plugin
	 *
	 * @return void
	 */
	public static function activate(): void {

		self::set_schema_table_logs();
		update_option( self::get_plugin_name() . '_version', AINSYS_CONNECTOR_VERSION, false );
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
	 * Uninstalls plugin.
	 */
	public static function uninstall(): void {

		if ( (int) self::get_option( 'full_uninstall' ) ) {
			self::delete_options();
			self::drop_tables();
		}
	}


	/**
	 * Uninstalls plugin.
	 */
	public static function truncate(): void {

		self::delete_options();
		self::truncate_tables();
	}


	/**
	 *
	 * @return void
	 */
	protected static function drop_tables(): void {

		global $wpdb;

		foreach ( self::get_settings_tables() as $key_table => $value_table ) {
			$wpdb->query( sprintf( "DROP TABLE IF EXISTS %s", $wpdb->prefix . $value_table ) );
		}
	}

	/**
	 *
	 * @param  string $table_name
	 *
	 * @return void
	 */
	public static function truncate_tables( string $table_name = '' ): void {

		global $wpdb;

		foreach ( self::get_settings_tables() as $key_table => $value_table ) {

			if ( ! empty( $table_name ) && $value_table === $table_name ) {
				$wpdb->query( sprintf( "TRUNCATE TABLE %s", $wpdb->prefix . $table_name ) );

				break;
			}

			$wpdb->query( sprintf( "TRUNCATE TABLE %s", $wpdb->prefix . $value_table ) );
		}
	}


	/**
	 * @return void
	 */
	protected static function delete_options(): void {

		foreach ( self::get_settings_options() as $option_name => $option_value ) {
			delete_option( self::get_option_name( $option_name ) );
		}

		delete_option( self::get_plugin_name() . '_version');
	}


	protected static function set_schema_table_logs(): void {

		ob_start();
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_schema_table_logs() );

		ob_get_clean();
	}


	/**
	 * Get Table schema.
	 *
	 * @return string
	 */
	protected static function get_schema_table_logs(): string {

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
		$table_log = $wpdb->prefix . self::get_settings_tables()['logs'];

		return "CREATE TABLE $table_log (
                `log_id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `creation_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `object_id` bigint NOT NULL,
                `entity` varchar(100) NOT NULL,
                `request_action` varchar(100) NOT NULL,
                `request_type` varchar(100) NOT NULL,
                `request_data` text DEFAULT NULL,
                `server_response` text DEFAULT NULL,
                `error` smallint NOT NULL,
                PRIMARY KEY  (log_id),
                KEY object_id (object_id)
            ) $collate;";

	}

}
