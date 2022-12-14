<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\Plugin_Common;

class Admin_UI implements Hooked {

	use Plugin_Common;

	/**
	 * Storage for admin notices.
	 *
	 * @var array
	 */
	public static array $notices = [];

	/**
	 * @var Settings
	 */
	public Settings $settings;


	public function __construct( Settings $settings ) {

		if ( ! is_admin() ) {
			return;
		}

		$this->settings = $settings;
	}


	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_filter(
			'plugin_action_links_ainsys-connector-master/plugin.php',
			[
				$this,
				'generate_links_to_plugin_bar',
			]
		);

		add_action( 'admin_enqueue_scripts', [ $this, 'ainsys_enqueue_scripts' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_filter( 'option_page_capability_' . 'ainsys-connector', [ $this, 'ainsys_page_capability' ] );

		add_action( 'wp_ajax_reload_log_html', [ $this, 'reload_log_html' ] );
		add_action( 'wp_ajax_toggle_logging', [ $this, 'toggle_logging' ] );
		add_action( 'wp_ajax_clear_log', [ $this, 'clear_log' ] );

	}


	/**
	 * Registers the plugin settings page in WP menu
	 *
	 */
	public function add_admin_menu() {

		add_menu_page(
			__( 'AINSYS connector integration', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
			__( 'AINSYS connector', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
			'administrator',
			'ainsys-connector',
			[ $this, 'include_setting_page' ],
			'dashicons-randomize',
			55
		);
	}


	/**
	 * Gives rights to edit ainsys-connector page
	 *
	 */
	function ainsys_page_capability( $capability ) {

		return 'administrator';
	}


	public function uasort_comparison( $a, $b ): int {

		if ( $a === $b ) {
			return 0;
		}

		return ( $a < $b ) ? - 1 : 1;
	}


	public function fields_uasort_comparison( $a, $b ): int {

		/*
		 * We are not guaranteed to get a priority
		 * setting. So don't compare if they don't
		 * exist.
		 */
		if ( ! isset( $a['priority'], $b['priority'] ) ) {
			return 0;
		}

		return $this->uasort_comparison( $a['priority'], $b['priority'] );
	}


	public function get_nav_fields(): array {


		$settings_nav_tabs = [
			'general'  => [
				'label'    => __( 'General', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'active'   => false,
				'priority' => 10,
			],
			'test'     => [
				'label'    => __( 'Checking entities', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'active'   => false,
				'priority' => 20,
			],
			'log'      => [
				'label'    => __( 'Transfer log', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'active'   => false,
				'priority' => 30,
			],
			'entities' => [
				'label'    => __( 'Entities export settings', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'active'   => false,
				'priority' => 40,
			],
		];

		uasort( $settings_nav_tabs, [ $this, 'fields_uasort_comparison' ] );

		return apply_filters( 'ainsys_settings_tabs', $settings_nav_tabs );
	}


	public function get_nav_content_fields(): array {


		$settings_content_tabs = [
			'general'  => [
				'template' => '/includes/settings/templates/tabs/general.php',
				'active'   => false,
				'priority' => 10,
			],
			'test'     => [
				'template' => '/includes/settings/templates/tabs/tests.php',
				'active'   => false,
				'priority' => 20,
			],
			'log'      => [
				'template' => '/includes/settings/templates/tabs/logs.php',
				'active'   => false,
				'priority' => 30,
			],
			'entities' => [
				'template' => '/includes/settings/templates/tabs/entities.php',
				'active'   => false,
				'priority' => 40,
			],
		];

		uasort( $settings_content_tabs, [ $this, 'fields_uasort_comparison' ] );

		return apply_filters( 'ainsys_settings_tabs_content', $settings_content_tabs );
	}


	/**
	 * Includes settings page
	 *
	 */
	public function include_setting_page() {

		// NB: inside template we inherit $this which gives access to it's deps.
		include_once __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'settings.php';
	}


	/**
	 * Adds a link to ainsys portal to the settings page.
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function generate_links_to_plugin_bar( $links ) {

		$settings_url = esc_url( add_query_arg( [ 'page' => 'ainsys-connector' ], get_admin_url() . 'options-general.php' ) );

		$settings_link = '<a href="' . $settings_url . '">' . __( 'Settings' ) . '</a>';
		$plugin_link   = '<a target="_blank" href="https://app.ainsys.com/en/settings/workspaces">AINSYS dashboard</a>';

		array_push( $links, $settings_link, $plugin_link );

		return $links;
	}


	/**
	 * Enqueues admin styles and scripts.
	 *
	 * @return void
	 */
	public function ainsys_enqueue_scripts() {

		if ( false === strpos( $_GET['page'] ?? '', 'ainsys-connector' ) ) {
			return;
		}

		wp_enqueue_style(
			'ainsys_connector_style_handle',
			plugins_url( 'assets/css/ainsys_connector_style.css', AINSYS_CONNECTOR_PLUGIN ),
			[ 'datatables_style_handle' ],
			AINSYS_CONNECTOR_VERSION
		);

		wp_enqueue_script(
			'ainsys_connector_admin_handle',
			plugins_url( 'assets/js/ainsys_connector_admin.js', AINSYS_CONNECTOR_PLUGIN ),
			[ 'jquery', 'dataTables_script_handle' ],
			AINSYS_CONNECTOR_VERSION,
			true
		);

		wp_enqueue_style(
			'datatables_style_handle',
			'https://cdn.datatables.net/1.13.1/css/jquery.dataTables.css',
			[],
			AINSYS_CONNECTOR_VERSION
		);

		wp_enqueue_script(
			'dataTables_script_handle',
			'https://cdn.datatables.net/1.13.1/js/jquery.dataTables.js',
			[ 'jquery' ],
			AINSYS_CONNECTOR_VERSION,
			true
		);

		wp_localize_script(
			'ainsys_connector_admin_handle',
			'ainsys_connector_params',
			[
				'ajax_url'                           => admin_url( 'admin-ajax.php' ),
				'nonce'                              => wp_create_nonce( 'ainsys_admin_menu_nonce' ),
				'remove_ainsys_integration'          => __( 'Are you sure this action is irreversible, all settings values will be cleared?', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'check_connection_entity_connect'    => __( 'Connection', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'check_connection_entity_no_connect' => __( 'No connection', AINSYS_CONNECTOR_TEXTDOMAIN ),
			]
		);

	}


	/**
	 * Renders admin notices
	 */
	public function admin_notices( $message, $status = 'success' ) {

		if ( self::$notices ) {
			foreach ( self::$notices as $notice ) {
				?>
				<div class="notice notice-<?php echo esc_attr( $notice['status'] ); ?>" is-dismissible>
					<p><?php echo esc_html( $notice['message'] ); ?></p>
				</div>
				<?php
			}
		}
	}


	/**
	 * Adds a notice to the notices array.
	 */
	public function add_admin_notice( $message, $status = 'success' ) {

		self::$notices[] = [
			'message' => $message,
			'status'  => $status,
		];
	}


	/**
	 * Regenerates log HTML (for ajax).
	 *
	 */
	public function reload_log_html() {

		if ( isset( $_POST['action'] ) ) {
			echo Logger::generate_log_html();
		}

		die();
	}


	/**
	 * Toggles logging on/off. Set up time till log is saved (for ajax).
	 *
	 */
	public function toggle_logging() {

		if ( isset( $_POST['command'] )  ) {

			$logging_time = 0;
			if ( isset( $_POST['time'] ) ) {

				$current_time = time();
				$time         = floatval( $_POST['time'] ?? 0 ); //intval( $_POST['time'] ?? 0 );
				$end_time     = $time;
				if ( $time > 0 ) {
					$end_time = $current_time + $time * 60 * 60;
				}
				Settings::set_option( 'log_until_certain_time', $end_time );
				Settings::set_option( 'log_select_value', $time );
				$logging_time = $end_time;
			}

			$logging_since = '';
			if ( 'start_loging' === $_POST['command'] ) {
				Settings::set_option( 'do_log_transactions', 1 );
				Settings::set_option( 'log_transactions_since', htmlspecialchars( strip_tags( $_POST['startat'] ) ) );
				$logging_since = Settings::get_option( 'log_transactions_since' );
			} else {
				Settings::set_option( 'do_log_transactions', 0 );
				Settings::set_option( 'log_transactions_since', '' );
				Settings::set_option( 'log_select_value', - 1 );
				$logging_since = '';
			}
			$result = [
				'logging_time'  => $logging_time,
				'logging_since' => $logging_since,
			];
			echo json_encode( $result );
		}
		die();
	}


	/**
	 * Clears log DB table (for ajax).
	 *
	 */
	public function clear_log(): void {

		if ( isset( $_POST['action']) ) {
			Logger::truncate_log_table();
			echo Logger::generate_log_html();
		}

		die();
	}

}
