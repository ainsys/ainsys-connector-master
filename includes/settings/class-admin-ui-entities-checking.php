<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\WP\Process_Comments;
use Ainsys\Connector\Master\WP\Process_Users;

class Admin_UI_Entities_Checking implements Hooked {

	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'wp_ajax_test_entity_connection', [ $this, 'check_connection_entity' ] );
	}


	/**
	 * Tests AINSYS connection for entities (for ajax).
	 */
	public function check_connection_entity(): void {

		if ( empty( $_POST['entity'] ) ) {
			wp_send_json_error(
				[
					'error' => __( 'Entity ID is missing', AINSYS_CONNECTOR_TEXTDOMAIN ),
				]
			);
		}

		$make_request = apply_filters( 'ainsys_before_check_connection_make_request', false );

		$entity = sanitize_text_field( $_POST['entity'] );

		$result_entity = [];

		if ( empty( Settings::get_option( 'check_connection_entity' ) ) ) {

			Settings::set_option( 'check_connection_entity', $result_entity );
		}

		if ( 'user' === $entity ) {
			$make_request  = true;
			$result_test   = $this->get_user_for_test();
			$result_entity = Settings::get_option( 'check_connection_entity' );
			$result_entity = $this->get_result_entity( $result_test, $result_entity, $entity );

		}

		if ( 'comment' === $entity ) {

			$make_request = true;

			$result_test   = $this->get_comment_for_test();
			$result_entity = Settings::get_option( 'check_connection_entity' );
			$result_entity = $this->get_result_entity( $result_test, $result_entity, $entity );

		}

		$result_entity = apply_filters( 'ainsys_check_connection_request', $result_entity, $entity, $make_request );

		if ( $make_request ) {

			wp_send_json_success(
				[
					'result'  => $result_entity,
					'message' => __( 'The connection has been successfully set up', AINSYS_CONNECTOR_TEXTDOMAIN ),
				]
			);

		}

		wp_send_json_error(
			[
				'result'  => [],
				'message' => __( 'An error occurred while checking the connection', AINSYS_CONNECTOR_TEXTDOMAIN ),
			],
		);

	}


	public function columns_checking_entities(): array {

		return apply_filters(
			'ainsys_columns_checking_entities',
			[
				'entity'          => __( 'Entity', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'outgoing'        => __( 'Outgoing JSON', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'server_response' => __( 'SERVER RESPONSE', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'time'            => __( 'Time and date', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'check'           => __( 'Check entity', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'status'          => __( 'Status', AINSYS_CONNECTOR_TEXTDOMAIN ),
			]
		);
	}


	/**
	 * @return array
	 */
	protected function get_user_for_test(): array {

		$users_args = [
			'fields' => 'all',
		];

		if ( is_multisite() ) {
			$users_args['blog_id'] = get_current_blog_id();
		}

		$users     = get_users( $users_args );
		$user_test = end( $users );

		return ( new Process_Users )->send_user_details_update_to_ainsys(
			(int) $user_test->ID,
			(array) $user_test->data,
			(array) $user_test->data,
			true
		);
	}


	/**
	 * @return array
	 */
	protected function get_comment_for_test(): array {

		$comments = get_comments( [
			'status' => 'approve',
			'type'   => 'comment',
		] );

		if ( empty( $comments ) ) {
			return [];
		}

		$comment    = (array) reset( $comments );
		$comment_id = $comment['comment_ID'];
		unset( $comment['comment_ID'] );

		return ( new Process_Comments )->send_update_comment_to_ainsys( (int) $comment_id, $comment, true );
	}


	/**
	 * @param  array $result_test
	 * @param        $result_entity
	 * @param        $entity
	 *
	 * @return mixed|void
	 */
	protected function get_result_entity( array $result_test, $result_entity, $entity ) {

		$full_response = $this->convert_response( $result_test['response'] );

		$result_entity[ $entity ] = [
			'request'        => $result_test['request'],
			'response'       => $result_test['response'],
			'short_request'  => mb_substr( serialize( $result_test['request'] ), 0, 40 ) . ' ... ',
			'full_request'   => Logger::render_json( $result_test['request'] ),
			'short_response' => mb_substr( serialize( $result_test['response'] ), 0, 40 ) . ' ... ',
			'full_response'  => $full_response,
			'time'           => current_time( 'mysql' ),
			'status'         => false === strpos( $result_test['response'], 'Error:' ),
		];

		Settings::set_option( 'check_connection_entity', $result_entity );

		return $result_entity;
	}


	/**
	 * @param $response
	 *
	 * @return string
	 */
	private function convert_response( $response ): string {

		try {
			$value_out = json_decode( $response, true, 512, JSON_THROW_ON_ERROR );
		} catch ( \JsonException $exception ) {
			$value_out = $response;
		}

		if ( is_string( $value_out ) ) {
			$full_response = $value_out;
		} else {
			$full_response = Logger::render_json( $value_out );
		}

		return $full_response;
	}

}