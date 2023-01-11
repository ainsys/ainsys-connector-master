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

	}


	public function add_admin_menu_separator( $position ): void {


		global $menu;

		static $index;
		if ( empty( $index ) ) {
			$index = 1;
		}

		foreach ( $menu as $mindex => $section ) {

			if ( $mindex >= $position ) {

				while ( isset( $menu[ $position ] ) ) {
					++ $position;
				}

				$menu[ $position ] = [ '', 'read', "separator-my$index", '', 'wp-menu-separator' ];

				$index ++;
				break;
			}
		}

		ksort( $menu );
	}


	/**
	 * Registers the plugin settings page in WP menu
	 *
	 */
	public function add_admin_menu() {

		$this->add_admin_menu_separator( 56 );

		add_menu_page(
			__( 'AINSYS connector integration', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
			__( 'AINSYS connector', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
			'administrator',
			'ainsys-connector',
			[ $this, 'include_setting_page' ],
			'dashicons-randomize',
			57
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

		wp_enqueue_script( 'clipboard' );

		wp_enqueue_script(
			'ainsys_connector_admin_handle',
			plugins_url( 'assets/js/ainsys_connector_admin.js', AINSYS_CONNECTOR_PLUGIN ),
			[ 'jquery', 'clipboard', 'dataTables_script_handle' ],
			AINSYS_CONNECTOR_VERSION,
			true
		);

		wp_enqueue_style(
			'datatables_style_handle',
			plugins_url( 'assets/css/jquery.dataTables.min.css', AINSYS_CONNECTOR_PLUGIN ),
			[],
			AINSYS_CONNECTOR_VERSION
		);

		wp_enqueue_script(
			'dataTables_script_handle',
			plugins_url( 'assets/js/jquery.dataTables.min.js', AINSYS_CONNECTOR_PLUGIN ),
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

}
