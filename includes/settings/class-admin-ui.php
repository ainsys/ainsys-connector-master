<?php

namespace Ainsys\Connector\Master\Settings;


use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\Plugin_Common;
use Ainsys\Connector\Master\Webhook_Listener;
use Ainsys\Connector\Master\WP\Process_Users;
use Ainsys\Connector\Master\WP\Process_Comments;

class Admin_UI implements Hooked {
	use Plugin_Common;
	/**
	 * Storage for admin notices.
	 *
	 * @var array
	 */
	public static array $notices = array();

	public static string $nonce_title = 'ainsys_admin_menu_nonce';

	/**
	 * @var Settings
	 */
	public Settings $settings;

	/**
	 * @var Core
	 */
	public Core $core;

	/**
	 * @var Logger
	 */
	public Logger $logger;

	/**
	 * @var Process_Users
	 */
	public Process_Users $process_users;

	/**
	 * @var Process_Comments
	 */
	public Process_Comments $process_comments;

	public function __construct( Settings $settings, Core $core, Logger $logger, Process_Users $process_users, Process_Comments $process_comments) {
		$this->settings         = $settings;
		$this->core             = $core;
		$this->logger           = $logger;
		$this->process_users    = $process_users;
		$this->process_comments = $process_comments;
	}

	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {
		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_filter(
				'plugin_action_links_ainsys-connector-master/plugin.php',
				array(
					$this,
					'generate_links_to_plugin_bar',
				)
			);
			add_action( 'admin_enqueue_scripts', array( $this, 'ainsys_enqueue_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_filter( 'option_page_capability_' . 'ainsys-connector', array( $this, 'ainsys_page_capability' ) );
		}
		// let's register ajax handlers as it's a part of admin UI. NB: they were a part of Core originally.
		//add_action( 'wp_ajax_remove_ainsys_integration', array( $this, 'remove_ainsys_integration' ) );

		add_action( 'wp_ajax_save_entity_settings', array( $this, 'save_entities_settings' ) );

		add_action( 'wp_ajax_reload_log_html', array( $this, 'reload_log_html' ) );
		add_action( 'wp_ajax_toggle_logging', array( $this, 'toggle_logging' ) );
		add_action( 'wp_ajax_clear_log', array( $this, 'clear_log' ) );


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
			array( $this, 'include_setting_page' ),
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
				'template'    => '/includes/settings/templates/tabs/general.php',
				'active'   => false,
				'priority' => 10,
			],
			'test'     => [
				'template'    => '/includes/settings/templates/tabs/tests.php',
				'active'   => false,
				'priority' => 20,
			],
			'log'      => [
				'template'    => '/includes/settings/templates/tabs/logs.php',
				'active'   => false,
				'priority' => 30,
			],
			'entities' => [
				'template'    => '/includes/settings/templates/tabs/entities.php',
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
		$settings_url = esc_url( add_query_arg( array( 'page' => 'ainsys-connector' ), get_admin_url() . 'options-general.php' ) );

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
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( self::$nonce_title ),
				'remove_ainsys_integration'    => __('Are you sure this action is irreversible, all settings values will be cleared?', AINSYS_CONNECTOR_TEXTDOMAIN),
				'check_connection_entity_connect'    => __( 'Connection', AINSYS_CONNECTOR_TEXTDOMAIN ) ,
				'check_connection_entity_no_connect'    => __( 'No connection', AINSYS_CONNECTOR_TEXTDOMAIN ) ,
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
		self::$notices[] = array(
			'message' => $message,
			'status'  => $status,
		);
	}








	/**
	 * Saves entities settings (for ajax).
	 */
	public function save_entities_settings(): void {

		if ( ! isset( $_POST['action'], $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ) {
			wp_die( 'Missing nonce' );
		}

		$fields = $_POST;

		$entity      = isset( $_POST['entity'] ) ? sanitize_text_field( $_POST['entity'] ) : '';
		$seting_name = $_POST['seting_name'] ? sanitize_text_field( $_POST['seting_name'] ) : '';

		if ( ! $entity && ! $seting_name ) {
			wp_die( 'Missing entity or setting_name' );
		}

		$fields = $this->sanitise_fields_to_save( $fields );

		global $wpdb;

		$entity_saved_settings = $this->settings::get_saved_entity_settings_from_db(
			sprintf(
				' WHERE entity="%s" setting_key="saved_field" AND setting_name="%s"',
				esc_sql( $entity ),
				esc_sql( $seting_name )
			)
		);

		if ( empty( $entity_saved_settings ) ) {
			$wpdb->insert(
				$wpdb->prefix . Settings::$ainsys_entities_settings_table,
				[
					'entity'       => $entity,
					'setting_name' => $seting_name,
					'setting_key'  => 'saved_field',
					'value'        => serialize( $fields ),
				]
			);

			$field_data_id = $wpdb->insert_id;
		} else {
			$wpdb->update(
				$wpdb->prefix . $this->settings::$ainsys_entities_settings_table,
				[ 'value' => serialize( $fields ) ],
				[ 'id' => $entity_saved_settings['id'] ]
			);

			$field_data_id = $entity_saved_settings['id'];
		}

		$request_action = 'field/' . $entity . '/' . $seting_name;

		$fields = apply_filters( 'ainsys_update_entity_fields', $fields );

		$request_data = [
			'entity'  => [
				'id' => $field_data_id,
			],
			'action'  => $request_action,
			'payload' => $fields,
		];

		try {
			$server_response = $this->core->curl_exec_func( $request_data );
		} catch ( \Exception $e ) {
			$server_response = 'Error: ' . $e->getMessage();
		}

		$this->logger::save_log_information(
			(int) $field_data_id,
			$request_action,
			serialize( $request_data ),
			serialize( $server_response ),
			0
		);

		echo $field_data_id ?? 0;

		wp_die();

	}

	/**
	 * Prepares fields to save to DB (for ajax).
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function sanitise_fields_to_save( $fields ) {
		unset( $fields['action'], $fields['entity'], $fields['nonce'], $fields['seting_name'], $fields['id'] );

		/// exclude 'constant' variables
		foreach ( $this->settings::get_entities_settings() as $item => $setting ) {
			if ( isset( $fields[ $item ] ) && 'constant' === $setting['type'] ) {
				unset( $fields[ $item ] );
			}
		}

		return $fields;
	}

	/**
	 * Regenerates log HTML (for ajax).
	 *
	 */
	public function reload_log_html() {

		if ( isset( $_POST['action'], $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ) {
			echo $this->logger::generate_log_html();
		}

		die();
	}

	/**
	 * Toggles logging on/off. Set up time till log is saved (for ajax).
	 *
	 */
	public function toggle_logging() {
		if ( isset( $_POST['command'] ) && isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ) {

			$logging_time = 0;
			if ( isset( $_POST['time'] ) ) {

				$current_time = time();
				$time         = floatval( $_POST['time'] ?? 0 ); //intval( $_POST['time'] ?? 0 );
				$end_time     = $time;
				if ( $time > 0 ) {
					$end_time = $current_time + $time * 60 * 60;
				}
				$this->settings::set_option( 'log_until_certain_time', $end_time );
				$this->settings::set_option( 'log_select_value', $time );
				$logging_time = $end_time;
			}

			$logging_since = '';
			if ( 'start_loging' === $_POST['command'] ) {
				$this->settings::set_option( 'do_log_transactions', 1 );
				$this->settings::set_option( 'log_transactions_since', htmlspecialchars( strip_tags( $_POST['startat'] ) ) );
				$logging_since = $this->settings::get_option( 'log_transactions_since' );
			} else {
				$this->settings::set_option( 'do_log_transactions', 0 );
				$this->settings::set_option( 'log_transactions_since', '' );
				$this->settings::set_option( 'log_select_value', -1 );
				$logging_since = '';
			}
			$result = array(
				'logging_time'  => $logging_time,
				'logging_since' => $logging_since,
			);
			echo json_encode( $result );
		}
		die();
	}

	/**
	 * Clears log DB table (for ajax).
	 *
	 */
	public function clear_log(): void {

		if ( isset( $_POST['action'], $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ) {
			$this->logger->truncate_log_table();
			echo $this->logger::generate_log_html();
		}

		die();
	}






	/**
	 * Generates entities HTML placeholder.
	 *
	 * @return string
	 */
	public function generate_entities_html() {

		$entities_html       = '';
		$collapsed           = '';
		$collapsed_text      = '';
		$first_active        = '';
		$inner_fields_header = '';

		$entities_list = $this->settings::get_entities();

		$properties = $this->settings::get_entities_settings();

		foreach ( $properties as $item => $settings ) {
			$checker_property     = ( 'bool' === $settings['type'] || 'api' === $item ) ? 'small_property' : '';

			$inner_fields_header .= sprintf( '<div class="properties_field_title %s">%s</div>',
				$checker_property,
				$settings['nice_name']
			);
		}

		foreach ( $entities_list as $entity => $title ) {

			$properties = $this->settings::get_entities_settings( $entity );

			$entities_html .= '<div class="entities_block">';

			$get_fields_functions = $this->settings::get_entity_fields_handlers();

			$section_fields = array();
			$fields_getter  = $get_fields_functions[ $entity ];
			if ( is_callable( $fields_getter ) ) {
				$section_fields = $fields_getter();
			} else {
				throw new \Exception( 'No fields getter registered for Entity: ' . $entity );
			}

			if ( ! empty( $section_fields ) ) {
				$collapsed      = $collapsed ? ' ' : ' active';
				$collapsed_text = $collapsed_text ? 'expand' : 'collapse';
				$entities_html .= '<div class="entity_data ' . $entity . '_data' . $collapsed . '"> ';

				$entities_html .= '<div class="entity_block_header"><div class="entity_title">' . $title . '</div>' . $inner_fields_header . '<a class="button expand_entity_container">' . $collapsed_text . '</a></div>';
				foreach ( $section_fields as $field_slug => $field_content ) {
					$first_active          = $first_active ? ' ' : ' active';
					$field_name            = empty( $field_content['nice_name'] ) ? $field_slug : $field_content['nice_name'];
					$entity_saved_settings = array_merge( $field_content, $this->settings::get_saved_entity_settings_from_db( ' WHERE entity="' . $entity . '" AND setting_name="' . $field_slug . '"' ) );

					if ( ! empty( $field_content['children'] ) ) {

						$data_fields = 'data-seting_name="' . esc_html( $field_slug ) . '" data-entity="' . esc_html( $entity ) . '"';
						foreach ( $properties as $name => $prop_val ) {
							$prop_val_out = 'id' === $name ? $field_slug : $this->get_property( $name, $prop_val, $entity_saved_settings );
							$data_fields .= 'data-' . $name . '="' . esc_html( $prop_val_out ) . '" ';
						}
						$entities_html .= '<div id="' . $field_slug . '" class="entities_field multiple_filds ' . $first_active . '" ' . $data_fields . '><div class="entities_field_header"><i class="fa fa-sort-desc" aria-hidden="true"></i>' . $field_name . '</div>' . $this->generate_inner_fields( $properties, $entity_saved_settings, $field_slug ) . '<i class="fa fa-floppy-o"></i><div class="loader_dual_ring"></div></div>';

						foreach ( $field_content['children'] as $inner_field_slug => $inner_field_content ) {
							$field_name            = empty( $inner_field_content['description'] ) ? $inner_field_slug : $inner_field_content['discription'];
							$field_slug_inner      = $field_slug . '_' . $inner_field_slug;
							$entity_saved_settings = array_merge( $field_content, $this->settings::get_saved_entity_settings_from_db( ' WHERE entity="' . $entity . '" AND setting_name="' . $field_slug_inner . '"' ) );

							$data_fields = 'data-seting_name="' . esc_html( $field_slug ) . '" data-entity="' . esc_html( $entity ) . '"';
							foreach ( $properties as $name => $prop_val ) {
								$prop_val_out = 'id' === $name ? $field_slug_inner : $this->get_property( $name, $prop_val, $entity_saved_settings );
								$data_fields .= 'data-' . $name . '="' . esc_html( $prop_val_out ) . '" ';
							}
							$entities_html .= '<div id="' . $entity . '_' . $inner_field_slug . '" class="entities_field multiple_filds_children ' . $first_active . '" ' . $data_fields . '><div class="entities_field_header"><i class="fa fa-angle-right" aria-hidden="true"></i>' . $field_name . '</div>' . $this->generate_inner_fields( $properties, $entity_saved_settings, $field_slug ) . '<i class="fa fa-floppy-o"></i><div class="loader_dual_ring"></div></div>';
						}
					} else {
						$data_fields = 'data-seting_name="' . esc_html( $field_slug ) . '" data-entity="' . esc_html( $entity ) . '"';
						foreach ( $properties as $name => $prop_val ) {
							$prop_val_out = $this->get_property( $name, $prop_val, $entity_saved_settings );
							$data_fields .= 'data-' . $name . '="' . esc_html( $prop_val_out ) . '" ';
						}
						$entities_html .= '<div id="' . $field_slug . '" class="entities_field ' . $first_active . '" ' . $data_fields . '><div class="entities_field_header">' . $field_name . '</div>' . $this->generate_inner_fields( $properties, $entity_saved_settings, $field_slug ) . '<i class="fa fa-floppy-o"></i><div class="loader_dual_ring"></div></div>';
					}
				}
				/// close //// div class="entity_data"
				$entities_html .= '</div>';
			}
			/// close //// div class="entities_block"
			$entities_html .= '</div>';
		}

		return '<div class="entitys_table">' . $entities_html . '</div>';

	}

	/**
	 * Gets a property from an array.
	 *
	 * @param  string $name
	 * @param mixed   $prop_val
	 * @param  array  $entity_saved_settings
	 *
	 * @return string
	 */
	public function get_property( string $name, $prop_val, array $entity_saved_settings ): string {
		if ( is_array( $prop_val['default'] ) ) {
			return $entity_saved_settings[ strtolower( $name ) ] ?? array_search( '1', $prop_val['default'], true );
		}

		return $entity_saved_settings[ strtolower( $name ) ] ?? $prop_val['default'];
	}

	/**
	 * Generates properties for entity field.
	 *
	 * @param array $properties
	 * @param array $entity_saved_settings
	 * @param string $field_slug
	 *
	 * @return string
	 */
	public function generate_inner_fields( $properties, $entity_saved_settings, $field_slug ) {

		$inner_fields = '';
		if ( empty( $properties ) ) {
			return '';
		}

		foreach ( $properties as $item => $settings ) {
			$checker_property = 'bool' === $settings['type'] || 'api' === $item ? 'small_property' : '';
			$inner_fields    .= '<div class="properties_field ' . $checker_property . '">';
			$field_value      = 'id' === $item ? $field_slug : $this->get_property( $item, $settings, $entity_saved_settings );
			switch ( $settings['type'] ) {
				case 'constant':
					$field_value   = $field_value ? $field_value : '<i>' . __( 'empty', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</i>'; // phpcs:ignore
					$inner_fields .= 'api' === $item ? '<div class="entity_settings_value constant ' . $field_value . '"></div>' : '<div class="entity_settings_value constant">' . $field_value . '</div>';
					break;
				case 'bool':
					$checked       = (int) $field_value ? 'checked="" value="1"' : ' value="0"';
					$checked_text  = (int) $field_value ? __( 'On', AINSYS_CONNECTOR_TEXTDOMAIN ) : __( 'Off', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore
					$inner_fields .= '<input type="checkbox"  class="editor_mode entity_settings_value " id="' . $item . '" ' . $checked . '/> ';
					$inner_fields .= '<div class="entity_settings_value">' . $checked_text . '</div> ';
					break;
				case 'int':
					$inner_fields .= '<input size="10" type="text"  class="editor_mode entity_settings_value" id="' . $item . '" value="' . $field_value . '"/> ';
					$field_value   = $field_value ? $field_value : '<i>' . __( 'empty', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</i>'; // phpcs:ignore
					$inner_fields .= '<div class="entity_settings_value">' . $field_value . '</div>';
					break;
				case 'select':
					$inner_fields .= '<select id="' . $item . '" class="editor_mode entity_settings_value" name="' . $item . '">';
					$state_text    = '';
					foreach ( $settings['default'] as $option => $state ) {
						$selected      = $option === $field_value ? 'selected="selected"' : '';
						$state_text    = $option === $field_value ? $option : $state_text;
						$inner_fields .= '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
					}
					$inner_fields .= '</select>';
					$inner_fields .= '<div class="entity_settings_value">' . $field_value . '</div>';
					break;
				default:
					$field_length  = 'description' === $item ? 20 : 8;
					$inner_fields .= '<input size="' . $field_length . '" type="text" class="editor_mode entity_settings_value" id="' . $item . '" value="' . $field_value . '"/>';
					$field_value   = $field_value ? $field_value : '<i>' . __( 'empty', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</i>'; // phpcs:ignore
					$inner_fields .= '<div class="entity_settings_value">' . $field_value . '</div>';
			}
			/// close //// div class="properties_field"
			$inner_fields .= '</div>';
		}

		return $inner_fields;
	}

}
