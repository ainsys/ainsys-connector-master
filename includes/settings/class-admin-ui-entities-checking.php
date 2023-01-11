<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\WP\Process_Attachments;
use Ainsys\Connector\Master\WP\Process_Comments;
use Ainsys\Connector\Master\WP\Process_Menus;
use Ainsys\Connector\Master\WP\Process_Pages;
use Ainsys\Connector\Master\WP\Process_Posts;
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

		switch ( $entity ) {
			case 'user':
				$make_request  = true;
				$result_test   = $this->get_user();
				$result_entity = Settings::get_option( 'check_connection_entity' );
				$result_entity = $this->get_result_entity( $result_test, $result_entity, $entity );
				break;
			case 'comment':
				$make_request  = true;
				$result_test   = $this->get_comment();
				$result_entity = Settings::get_option( 'check_connection_entity' );
				$result_entity = $this->get_result_entity( $result_test, $result_entity, $entity );
				break;
			case 'attachment':
				$make_request  = true;
				$result_test   = $this->get_attachment();
				$result_entity = Settings::get_option( 'check_connection_entity' );
				$result_entity = $this->get_result_entity( $result_test, $result_entity, $entity );
				break;
			case 'post':
				$make_request  = true;
				$result_test   = $this->get_post();
				$result_entity = Settings::get_option( 'check_connection_entity' );
				$result_entity = $this->get_result_entity( $result_test, $result_entity, $entity );
				break;
			case 'page':
				$make_request  = true;
				$result_test   = $this->get_page();
				$result_entity = Settings::get_option( 'check_connection_entity' );
				$result_entity = $this->get_result_entity( $result_test, $result_entity, $entity );
				break;
			case 'menu':
				$make_request  = true;
				$result_test   = $this->get_menu();
				$result_entity = Settings::get_option( 'check_connection_entity' );
				$result_entity = $this->get_result_entity( $result_test, $result_entity, $entity );
				break;
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


	public static function columns_checking_entities(): array {

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
	protected function get_user(): array {

		$users_args = [
			'fields' => 'all',
		];

		if ( is_multisite() ) {
			$users_args['blog_id'] = get_current_blog_id();
		}

		$users     = get_users( $users_args );
		$user_test = end( $users );

		return ( new Process_Users )->process_checking( (int) $user_test->ID, (array) $user_test->data, (array) $user_test->data );
	}


	/**
	 * @return array
	 */
	protected function get_comment(): array {

		$comments = get_comments( [
			'status' => 'approve',
			'type'   => 'comment',
		] );

		if ( empty( $comments ) ) {
			return [
				'request'  => '',
				'response' => __( 'Error: There is no data to check.', AINSYS_CONNECTOR_TEXTDOMAIN ),
			];
		}

		$comment    = (array) reset( $comments );
		$comment_id = $comment['comment_ID'];
		unset( $comment['comment_ID'] );

		return ( new Process_Comments )->process_checking( (int) $comment_id, $comment );
	}


	/**
	 * @return array
	 */
	protected function get_attachment(): array {

		$attachments = get_posts( [
			'post_type'      => 'attachment',
			'posts_per_page' => 50,
			'post_status'    => 'any',
			'post_parent'    => null,
		] );

		if ( empty( $attachments ) ) {
			return [
				'request'  => '',
				'response' => __( 'Error: There is no data to check.', AINSYS_CONNECTOR_TEXTDOMAIN ),
			];
		}

		$attachment    = (array) end( $attachments );
		$attachment_id = (int) $attachment['ID'];

		return ( new Process_Attachments )->process_checking( $attachment_id, $attachment, true );
	}


	/**
	 * @return array
	 */
	protected function get_post(): array {

		$posts = get_posts( [
			'post_type'      => 'post',
			'posts_per_page' => 50,
			'post_status'    => 'public',
			'post_parent'    => null,
		] );

		if ( empty( $posts ) ) {
			return [
				'request'  => '',
				'response' => __( 'Error: There is no data to check.', AINSYS_CONNECTOR_TEXTDOMAIN ),
			];
		}

		$post    = end( $posts );
		$post_id = (int) $post->ID;

		return ( new Process_Posts )->process_checking( $post_id, $post, true );
	}


	/**
	 * @return array
	 */
	protected function get_page(): array {

		$posts = get_posts( [
			'post_type'      => 'page',
			'posts_per_page' => 50,
			'post_status'    => 'public',
			'post_parent'    => null,
		] );

		if ( empty( $posts ) ) {
			return [
				'request'  => '',
				'response' => __( 'Error: There is no data to check.', AINSYS_CONNECTOR_TEXTDOMAIN ),
			];
		}

		$post    = end( $posts );
		$post_id = (int) $post->ID;

		return ( new Process_Pages )->process_checking( $post_id, $post, true );
	}


	/**
	 * @return array
	 */
	protected function get_menu(): array {

		$menus = wp_get_nav_menus();

		if ( empty( $menus ) ) {
			return [
				'request'  => '',
				'response' => __( 'Error: There is no data to check.', AINSYS_CONNECTOR_TEXTDOMAIN ),
			];
		}

		$menu    = end( $menus );
		$menu_id = (int) $menu->term_id;

		return ( new Process_Menus() )->process_checking( $menu_id );
	}


	/**
	 * @param  array $result_test
	 * @param        $result_entity
	 * @param        $entity
	 *
	 * @return mixed|void
	 */
	protected function get_result_entity( array $result_test, $result_entity, $entity ) {

		if ( ! empty( $result_test['request'] ) ) {
			$result_request = $result_test['request'];
		} else {
			$result_request = __( 'Error: Data transfer is disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN );
		}

		if ( ! empty( $result_test['response'] ) ) {
			$result_response = $result_test['response'];
		} else {
			$result_response = __( 'Error: Data transfer is disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN );
		}

		$result_entity[ $entity ] = [
			'request'        => $result_request,
			'response'       => $result_response,
			'short_request'  => mb_substr( Logger::convert_response( $result_request ), 0, 40 ) . ' ... ',
			'full_request'   => Logger::convert_response( $result_request ),
			'short_response' => mb_substr( Logger::convert_response( $result_response ), 0, 40 ) . ' ... ',
			'full_response'  => Logger::convert_response( $result_response ),
			'time'           => current_time( 'mysql' ),
			'status'         => false === strpos( $result_response, 'Error:' ),
		];

		Settings::set_option( 'check_connection_entity', $result_entity );

		return $result_entity;
	}

}