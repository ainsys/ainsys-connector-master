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
			'log_select_value'         => - 1,
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
	 * Gets full option name.
	 *
	 * @param  string $name
	 *
	 * @return string
	 */
	/*	public static function get_setting_name( string $name ): string {
			return self::get_plugin_name() . '_' . $name;
		}*/

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
			'user'    => __( 'User / fields', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
			'comment' => __( 'Comments / fields', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
		];

		return apply_filters( 'ainsys_get_entities_list', $entities );
	}


	/**
	 * Autodisables logging.
	 *
	 * @return void
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
					//'sanitize_callback' => [ self::class, 'sanitize_update_settings' ],
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


	public static function sanitize_update_settings( $options ) {

		// Detect multiple sanitizing passes.
		// Accomodates bug: https://core.trac.wordpress.org/ticket/21989
		static $pass_count = 0;

		$pass_count ++;

		if ( $pass_count <= 1 ) {
			foreach ( self::get_settings_options() as $option_name => $option_value ) {
				update_option( self::get_option_name( $option_name ), $options, 'no' );
			}
		}

		return $options;

	}


	/**
	 * Activates plugin
	 *
	 * @return void
	 */
	public static function activate(): void {

		update_option( self::get_plugin_name(), AINSYS_CONNECTOR_VERSION, false );
		update_option( self::get_plugin_name() . '_db_version', AINSYS_CONNECTOR_VERSION, false );
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
	 * @return void
	 */
	protected static function truncate_tables(): void {

		global $wpdb;

		foreach ( self::get_settings_tables() as $key_table => $value_table ) {
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
	}

}
