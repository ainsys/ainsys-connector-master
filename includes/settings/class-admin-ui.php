<?php

namespace Ainsys\Connector\Master\Settings;


use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\WP\Process_Users;
use Ainsys\Connector\Master\WP\Process_Comments;

class Admin_UI implements Hooked {

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

	public function __construct( Settings $settings, Core $core, Logger $logger, Process_Users $process_users, Process_Comments $process_comments ) {
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
		add_action( 'wp_ajax_remove_ainsys_integration', array( $this, 'remove_ainsys_integration' ) );

		add_action( 'wp_ajax_save_entity_settings', array( $this, 'save_entities_settings' ) );

		add_action( 'wp_ajax_reload_log_html', array( $this, 'reload_log_html' ) );
		add_action( 'wp_ajax_toggle_logging', array( $this, 'toggle_logging' ) );
		add_action( 'wp_ajax_clear_log', array( $this, 'clear_log' ) );
		add_action( 'wp_ajax_test_entity_connection', array( $this, 'test_entity_connection' ) );

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
			//wp_enqueue_script('jquery-ui-sortable');
			return;
		}

		wp_enqueue_style(
			'ainsys_connector_style_handle',
			plugins_url( 'assets/css/ainsys_connector_style.css', AINSYS_CONNECTOR_PLUGIN ),
			[],
			AINSYS_CONNECTOR_VERSION
		);

		wp_enqueue_style( 'font-awesome_style_handle',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
			[],
			AINSYS_CONNECTOR_VERSION);

		wp_enqueue_script( 'ainsys_connector_admin_handle',
			plugins_url( 'assets/js/ainsys_connector_admin.js', AINSYS_CONNECTOR_PLUGIN ),
			array( 'jquery' ),
			AINSYS_CONNECTOR_VERSION,
			true );

		wp_localize_script(
			'ainsys_connector_admin_handle',
			'ainsys_connector_params',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( self::$nonce_title ),
			)
		);

	}

	/**
	 * Handshake with server, implements AINSYS integration
	 */
	public function check_connection_to_server() {

		$ainsys_url = $this->settings::get_option( 'ansys_api_key' ); //https://user-api.ainsys.com/api/v0/workspace-management/workspaces/13/connectors/144/handshake/5ec1a0c99d428601ce42b407ae9c675e0836a8ba591c8ca6e2a2cf5563d97ff0/

		if ( ! empty( $ainsys_url ) && empty( $this->settings::get_option( 'webhook_url' ) ) ) {
			//new connector
			$response = '';
			try {
				$response = $this->core->curl_exec_func( array( 'hook_url' => $this->settings::get_option( 'ansys_api_key' ) ) );
			} catch ( \Exception $exception ) {

			}
			$webhook_data = ! empty( $response ) ? json_decode( $response ) : array();
			if ( ! empty( $response ) && isset( $webhook_data->webhook_url ) ) {
				$this->settings::set_option( 'webhook_url', $webhook_data->webhook_url );
			}

			// old connector
			//          $connectors = ainsys_settings::get_option('connectors');
			//            if (empty($connectors)){
			//                $server_url = empty(ainsys_settings::get_option('server')) ? 'https://user-api.ainsys.com/' : ainsys_settings::get_option('server');
			//                $workspace = empty(ainsys_settings::get_option('workspace')) ? 14 : ainsys_settings::get_option('workspace');
			//                $url = $server_url . 'api/v0/workspace-management/workspaces/' . $workspace . '/connectors/';
			//                $sys_id = empty((int)ainsys_settings::get_option('sys_id')) ? 3 : (int)ainsys_settings::get_option('sys_id');
			//                $post_fields = array(
			//                    "name" => 'string',
			//                    "system" => $sys_id,
			//                    "workspace" => 14,
			//                    "created_by" => 0);
			//                $connectors_responce = self::curl_exec_func( $post_fields, $url );
			//                $connectors_array = !empty($connectors_responce) ? json_decode($connectors_responce) : '';
			//                if ( !empty($connectors_array) && isset($connectors_array->id) ){
			//                    ainsys_settings::set_option('connectors', $connectors_array->id);
			//                    $url = $server_url . 'api/v0/workspace-management/workspaces/'. $workspace . '/connectors/'. $connectors_array->id . '/handshake-url/';
			//                    $url_responce = self::curl_exec_func('', $url );
			//                    $url_array = !empty($url_responce) ? json_decode($url_responce) : '';
			//                    if ( !empty($url_array) && isset($url_array->url) ){
			//                        ainsys_settings::set_option('handshake_url', $url_array->url);
			//                        $webhook_call = self::curl_exec_func( ['webhook_url' => ainsys_settings::get_option('hook_url')], $url_array->url );
			//                        $webhook_array = !empty($webhook_call) ? json_decode($webhook_call) : '';
			//                        if (! empty($webhook_call) && isset($webhook_array->webhook_url)){
			//                            ainsys_settings::set_option('webhook_url', $webhook_array->webhook_url);
			//                        }
			//                    }
			//                }
			//            }
		}
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
	 * Check if AINSYS integration is active.
	 *
	 * @param string $actions
	 *
	 * @return array
	 */
	public function is_ainsys_integration_active( $actions = '' ) {

		$this->check_connection_to_server();

		$webhook_url = $this->settings::get_option( 'ansys_api_key' );

		// TODO check commented out code -  it's legacy copied as is.
		//      if ( ! empty( $webhook_url ) && ! empty( get_option( 'ainsys-webhook_url' ) ) ) {
		//          return array( 'status' => 'success' );
		//      }
		//
		//      $request_to_ainsys = wp_remote_post( $webhook_url, [
		//          'sslverify' => false,
		//          'body'      => [
		//              'webhook_url' => get_option( 'ansys_connector_woocommerce_hook_url' )
		//          ]
		//      ] );

		//      if ( is_wp_error( $request_to_ainsys ) ) {
		//          return array( 'status' => 'none' );
		//      }

		//      $parsed_response = json_decode( $request_to_ainsys['body'] );

		if ( $webhook_url ) {
			$this->add_admin_notice( 'Соединение с сервером Ainsys установлено. Webhook_url получен.' );

			return array( 'status' => 'success' );
		}

		return array( 'status' => 'none' );
	}

	/**
	 * Removes ainsys integration information
	 *
	 * @return
	 */
	public function remove_ainsys_integration() {
		if ( isset( $_POST['action'], $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ) {
			$this->settings::set_option( 'connectors', '' );
			$this->settings::set_option( 'ansys_api_key', '' );
			$this->settings::set_option( 'handshake_url', '' );
			$this->settings::set_option( 'webhook_url', '' );
			$this->settings::set_option( 'debug_log', '' );

			delete_option( 'ainsys-webhook_url' );
		}

		return;
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
		// clear empty fields
		//        foreach ($fields as $field => $val){
		//            if (empty($val))
		//                unset($fields[$field]);
		//        }
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
	 * Tests AINSYS connection for entities (for ajax).
	 *
	 */
	public function test_entity_connection() {

		if ( ! isset( $_POST['entity'], $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ) {
			wp_die( 'Missing nonce' );
		}

		$make_request = false;

		$entity = strip_tags( $_POST['entity'] );

		if ( 'user' === $entity ) {
			$make_request         = true;
			$fields               = (array) wp_get_current_user();
			$user_id              = get_current_user_id();
			$test_result          = $this->process_users->send_user_details_update_to_ainsys( $user_id, $fields, $fields, true );
			$test_result_request  = $test_result['request']; // array
			$test_result_response = $test_result['response']; // string
		}

		if ( 'comments' === $entity ) {
			$args     = [
				'status' => 'approve',
			];
			$comments = get_comments( $args );
			if ( ! empty( $comments ) ) {
				foreach ( $comments as $comment ) {
					$fields = (array) $comment;
					break;
				}

				$comment_id           = $fields['comment_ID'];
				$make_request         = true;
				$test_result          = $this->process_comments->send_update_comment_to_ainsys( $comment_id, $fields, true );
				$test_result_request  = $test_result['request']; // array
				$test_result_response = $test_result['response']; // string
			}
		}

		if ( $make_request ) {

			$result = [
				'short_request'  => mb_substr( serialize( $test_result_request ), 0, 80 ) . ' ... ',
				'short_responce' => mb_substr( $test_result_response, 0, 80 ) . ' ... ',
				'full_request'   => $this->logger::ainsys_render_json( $test_result_request ),
				'full_responce'  => false === strpos( 'Error: ', $test_result_response ) ? [ $test_result_response ] :
					$this->logger::ainsys_render_json( json_decode( $test_result_response ) ),
			];
		} else {
			$result = [
				'short_request'  => __( 'No entities found', AINSYS_CONNECTOR_TEXTDOMAIN ), // phpcs:ignore
				'short_responce' => '',
				'full_request'   => '',
				'full_responce'  => '',
			];
		}

		wp_send_json( $result );

	}


	/**
	 * Generates test data HTML.
	 *
	 * @return string
	 */
	public function generate_test_html() {

		$test_html        = '<div id="connection_test"><table class="ainsys-table">';
		$test_html_header = '<th>' . __( 'Entity', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</th><th>' . __( 'Outgoing JSON', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</th><th>' . __( 'SERVER RESPONCE', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</th><th></th><th>Status</th>'; // phpcs:ignore
		$test_html_body   = '';

		$entities_list = $this->settings::get_entities();
		$wp_entities   = array( 'user', 'comments', 'post', 'page' );

		foreach ( $entities_list as $entity => $title ) {
			if ( in_array( $entity, $wp_entities, true ) ) {
				$test_html_body .= '<tr><td class="ainsys_td_left">' . $title . '</td><td class="ainsys_td_left ainsys-test-json"><div class="ainsys-responce-short"></div><div class="ainsys-responce-full"></div></td><td class="ainsys_td_left ainsys-test-responce"><div class="ainsys-responce-short"></div><div class="ainsys-responce-full"></div></td><td class="ainsys_td_btn"><a href="" class="btn btn-primary ainsys-test" data-entity-name="' . $entity . '">' . __( 'Test', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</a></td><td><span class="ainsys-success"></span><span class="ainsys-failure"></span></td></tr>'; // phpcs:ignore
			}
		}

		$test_html_body = apply_filters( 'ainsys_test_table', $test_html_body );

		$test_html .= '<thead><tr>' . $test_html_header . '</tr></thead><tbody>' . $test_html_body . '</tbody></table> </div>';
		return $test_html;
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
			$inner_fields_header .= '<div class="properties_field_title ' . $checker_property . '">' . $settings['nice_name'] . '</div>';
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
