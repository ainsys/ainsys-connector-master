<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;

class Admin_UI_General implements Hooked {

	protected Admin_UI $admin_ui;


	public function __construct( Admin_UI $admin_ui ) {

		$this->admin_ui = $admin_ui;
	}


	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {

		// let's register ajax handlers as it's a part of admin UI. NB: they were a part of Core originally.
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
						$this->admin_ui->settings::get_backup_email()
					)
				),
				'active'        => ! empty( $this->admin_ui->settings::get_backup_email() ) && filter_var( $this->admin_ui->settings::get_backup_email(), FILTER_VALIDATE_EMAIL ),
				'label_success' => __( 'Valid', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'label_error'   => __( 'Invalid', AINSYS_CONNECTOR_TEXTDOMAIN ),
			],
		];

		for ( $i = 1; $i < 10; $i ++ ) {

			if ( empty( $this->admin_ui->settings::get_backup_email( $i ) ) ) {
				continue;
			}

			$status_system[ 'emails_' . $i ] = [
				'title'         => sprintf(
					__( 'Backup email: %s', AINSYS_CONNECTOR_TEXTDOMAIN ),
					esc_html( $this->admin_ui->settings::get_backup_email( $i ) )
				),
				'active'        => ! empty( $this->admin_ui->settings::get_backup_email( $i ) )
				                   && filter_var(
					                   $this->admin_ui->settings::get_backup_email( $i ), FILTER_VALIDATE_EMAIL
				                   ),
				'label_success' => __( 'Valid', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'label_error'   => __( 'Invalid', AINSYS_CONNECTOR_TEXTDOMAIN ),
			];

		}

		return apply_filters( 'ainsys_status_system_list', $status_system );
	}


	public function get_statuses_addons() {

		$status = [
			'woocommerce' => [
				'title'   => 'AINSYS connector Woocommerce Integration',
				'slug'    => 'ainsys-connector-woocommerce',
				'active'  => $this->admin_ui->is_plugin_active( 'ainsys-connector-woocommerce/plugin.php' ),
				'install' => $this->admin_ui->is_plugin_install( 'ainsys-connector-woocommerce/plugin.php' ),
			],
			'content'     => [
				'title'   => 'AINSYS Connector Headless CMS',
				'slug'    => 'ainsys-connector-content',
				'active'  => $this->admin_ui->is_plugin_active( 'ainsys-connector-content/plugin.php' ),
				'install' => $this->admin_ui->is_plugin_install( 'ainsys-connector-content/plugin.php' ),
			],
			'acf'         => [
				'title'   => 'AINSYS connector ACF Integration',
				'slug'    => 'ainsys-connector-acf',
				'active'  => $this->admin_ui->is_plugin_active( '1ainsys-connector-acf/plugin.php' ),
				'install' => $this->admin_ui->is_plugin_install( '1ainsys-connector-acf/plugin.php' ),
			],
			'wpcf7'       => [
				'title'   => 'AINSYS connector WPCF7 Integration',
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
		$this->admin_ui->settings::truncate();
		wp_die();
	}

	/**
	 * Removes ainsys integration information
	 */
	public function check_ainsys_integration(): void {

		if ( $_POST['check_integration'] ) {

			$check = $this->check_connection_to_server();

			$this->admin_ui->settings::set_option( 'check_connection', $check );

			Logger::save_log_information(
				[
					'object_id'       => 0,
					'entity'          => 'settings',
					'request_action'  => 'check_integration',
					'request_type'    => 'outgoing',
					'request_data'    => '',
					'server_response' => serialize( $check ),
				]
			);
			//error_log( print_r( $check, 1 ) );

		}


		wp_die();
	}


	/**
	 * Handshake with server, implements AINSYS integration
	 *
	 */
	public function check_connection_to_server() {

		$ainsys_url = $this->admin_ui->settings::get_option( 'ansys_api_key' );

		if ( ! empty( $ainsys_url ) ) {
			$response = $this->admin_ui->core->curl_exec_func();

			try {
				$webhook_data = ! empty( $response ) ? json_decode( $response, false, 512, JSON_THROW_ON_ERROR ) : [];
			} catch ( \Exception $e ) {
				return esc_html( $e->getMessage() );
			}

			if ( ! empty( $response ) ) {
				return $response;
			}

		}

		return false;
	}


	/**
	 * Check if AINSYS integration is active.
	 *
	 * @param string $actions
	 *
	 * @return array
	 */
	public function is_ainsys_integration_active( $actions = '' ) {

		//$this->check_connection_to_server();

		$webhook_url = $this->admin_ui->settings::get_option( 'ansys_api_key' );

		if ( $webhook_url ) {
			$this->admin_ui->add_admin_notice( 'Соединение с сервером Ainsys установлено. Webhook_url получен.' );

			return array( 'status' => 'success' );
		}

		return array( 'status' => 'none' );
	}
}