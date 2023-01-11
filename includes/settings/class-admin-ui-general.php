<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;

class Admin_UI_General implements Hooked {

	protected Admin_UI $admin_ui;


	public function __construct( Admin_UI $admin_ui ) {

		if ( ! is_admin() ) {
			return;
		}

		$this->admin_ui = $admin_ui;
	}


	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'wp_ajax_remove_ainsys_integration', [ $this, 'remove_ainsys_integration' ] );
		add_action( 'wp_ajax_check_ainsys_integration', [ $this, 'check_ainsys_integration' ] );
	}


	public function get_statuses_system() {

		$status_system = [
			'curl'   => [
				'title'         => 'CURL',
				'active'        => extension_loaded( 'curl' ),
				'label_success' => __( 'Enabled', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'label_error'   => __( 'Disabled', AINSYS_CONNECTOR_TEXTDOMAIN ),
			],
			'ssl'    => [
				'title'         => 'SSL',
				'active'        => \is_ssl(),
				'label_success' => __( 'Enabled', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'label_error'   => __( 'Disabled', AINSYS_CONNECTOR_TEXTDOMAIN ),
			],
			'php'    => [
				'title'         => __( 'PHP version 7.2+', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'active'        => version_compare( PHP_VERSION, '7.2.0' ) > 0,
				'label_success' => 'PHP ' . esc_html( PHP_VERSION ),
				'label_error'   => sprintf( __( 'Bad PHP version %s Update on your hosting', AINSYS_CONNECTOR_TEXTDOMAIN ), esc_html( PHP_VERSION ) ),
			],
			'emails' => [
				'title'         => sprintf(
					__( 'Backup email: %s', AINSYS_CONNECTOR_TEXTDOMAIN ), esc_html(
						Settings::get_backup_email()
					)
				),
				'active'        => ! empty( Settings::get_backup_email() ) && filter_var( Settings::get_backup_email(), FILTER_VALIDATE_EMAIL ),
				'label_success' => __( 'Valid', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'label_error'   => __( 'Invalid', AINSYS_CONNECTOR_TEXTDOMAIN ),
			],
		];

		for ( $i = 1; $i < 10; $i ++ ) {


			if ( empty( Settings::get_backup_email( $i ) ) ) {
				continue;
			}

			$status_system[ 'emails_' . $i ] = [
				'title'         => sprintf(
					__( 'Backup email: %s', AINSYS_CONNECTOR_TEXTDOMAIN ),
					esc_html( Settings::get_backup_email( $i ) )
				),
				'active'        => ! empty( Settings::get_backup_email( $i ) )
				                   && filter_var( Settings::get_backup_email( $i ), FILTER_VALIDATE_EMAIL ),
				'label_success' => __( 'Valid', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'label_error'   => __( 'Invalid', AINSYS_CONNECTOR_TEXTDOMAIN ),
			];

		}

		return apply_filters( 'ainsys_status_system_list', $status_system );
	}


	public function get_statuses_addons() {

		$status = [
			'ainsys_woocommerce' => [
				'title'   => 'AINSYS Woocommerce Integration',
				'slug'    => 'ainsys-connector-woocommerce',
				'active'  => $this->admin_ui->is_plugin_active( 'ainsys-connector-woocommerce/plugin.php' ),
				'install' => $this->admin_ui->is_plugin_install( 'ainsys-connector-woocommerce/plugin.php' ),
			],
			'ainsys_content'     => [
				'title'   => 'AINSYS Headless CMS',
				'slug'    => 'ainsys-connector-content',
				'active'  => $this->admin_ui->is_plugin_active( 'ainsys-connector-content/plugin.php' ),
				'install' => $this->admin_ui->is_plugin_install( 'ainsys-connector-content/plugin.php' ),
			],
			'ainsys_acf'         => [
				'title'   => 'AINSYS ACF Integration',
				'slug'    => 'ainsys-connector-acf',
				'active'  => $this->admin_ui->is_plugin_active( '1ainsys-connector-acf/plugin.php' ),
				'install' => $this->admin_ui->is_plugin_install( '1ainsys-connector-acf/plugin.php' ),
			],
			'ainsys_wpcf7'       => [
				'title'   => 'AINSYS WPCF7 Integration',
				'slug'    => 'ainsys-connector-wpcf7',
				'active'  => $this->admin_ui->is_plugin_active( 'ainsys-connector-wpcf7/plugin.php' ),
				'install' => $this->admin_ui->is_plugin_install( 'ainsys-connector-wpcf7/plugin.php' ),
			],
		];

		return apply_filters( 'ainsys_status_list', $status );
	}


	/**
	 * Removes ainsys integration information
	 */
	public function remove_ainsys_integration(): void {

		Settings::truncate();
		wp_die();
	}


	/**
	 * Removes ainsys integration information
	 */
	public function check_ainsys_integration(): void {

		if ( empty( $_POST['check_integration'] ) ) {
			wp_send_json_error(
				[
					'error' => __( 'Entity ID is missing', AINSYS_CONNECTOR_TEXTDOMAIN ),
				]
			);
		}

		$check_response = $this->check_connection_to_server();

		if ( false !== strpos( $check_response, 'Error:' ) ) {
			$this->get_connect_error( $check_response );
		} else {
			$this->get_connect_success( $check_response );
		}

	}


	/**
	 * Handshake with server, implements AINSYS integration
	 *
	 */
	public function check_connection_to_server(): string {

		try {
			$response = Core::curl_exec_func();
		} catch ( \Exception $e ) {
			$response = esc_html( $e->getMessage() );
		}

		return $response;
	}


	/**
	 * @param  string $check_response
	 *
	 * @return void
	 */
	private function get_connect_error( string $check_response ): void {

		$result = [
			'response' => $check_response,
			'time'     => current_time( 'mysql' ),
		];

		Settings::set_option( 'check_connection', $result );

		Logger::save(
			[
				'object_id'       => 0,
				'entity'          => 'settings',
				'request_action'  => 'CHECKING CONNECT',
				'request_type'    => 'outgoing',
				'request_data'    => '',
				'server_response' => serialize( $check_response ),
				'error'           => 1,
			]
		);

		wp_send_json_error(
			[
				'result'  => $result,
				'message' => __( 'An error occurred while checking the connection', AINSYS_CONNECTOR_TEXTDOMAIN ),
			]
		);

	}


	/**
	 * @param  string $check_response
	 *
	 * @return void
	 */
	private function get_connect_success( string $check_response ): void {

		$result = [
			'response' => $check_response,
			'time'     => current_time( 'mysql' ),
		];

		Settings::set_option( 'check_connection', $result );

		Logger::save(
			[
				'object_id'       => 0,
				'entity'          => 'settings',
				'request_action'  => 'CHECKING CONNECT',
				'request_type'    => 'outgoing',
				'request_data'    => '',
				'server_response' => serialize( $check_response ),
			]
		);

		wp_send_json_success( [
			'result'  => $result,
			'message' => __( 'The connection has been successfully set up', AINSYS_CONNECTOR_TEXTDOMAIN ),
		] );
	}

}