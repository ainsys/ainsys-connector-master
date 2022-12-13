<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;

class Admin_UI_Checking_Entities implements Hooked {

	protected Admin_UI $admin_ui;


	public function __construct( Admin_UI $admin_ui ) {

		$this->admin_ui = $admin_ui;
	}


	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {

		add_action( 'wp_ajax_test_entity_connection', [ $this, 'test_entity_connection' ] );
	}


	/**
	 * Tests AINSYS connection for entities (for ajax).
	 *
	 */
	public function test_entity_connection() {

		if ( ! isset( $_POST['entity'] ) ) {
			wp_send_json_error( [
				'error' => __( 'Entity ID is missing', AINSYS_CONNECTOR_TEXTDOMAIN ),
			] );
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


	public function columns_checking_entities(): array {

		return apply_filters( 'ainsys_columns_checking_entities', [
			'entity'          => __( 'Entity', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'outgoing'        => __( 'Outgoing JSON', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'server_response' => __( 'SERVER RESPONSE', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'time'            => __( 'Time and date', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'check'           => __( 'Check entity', AINSYS_CONNECTOR_TEXTDOMAIN ),
			'status'          => __( 'Status', AINSYS_CONNECTOR_TEXTDOMAIN ),
		] );
	}


	public function entities_list(): array {

		return $this->admin_ui->settings::get_entities();
	}

}