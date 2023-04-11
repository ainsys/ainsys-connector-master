<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Webhook_Handler;
use Ainsys\Connector\Master\Webhooks\Setup\Setup_User;

class Handle_User extends Handle implements Hooked, Webhook_Handler {

	protected static string $entity = 'user';


	public function register_webhook_handler( $handlers = [] ) {

		$handlers[ self::$entity ] = [ $this, 'handler' ];

		return $handlers;
	}


	/**
	 * @param  array  $data
	 * @param  string $action
	 *
	 * @return array
	 */
	protected function create( array $data, string $action ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s creation is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		if ( ! empty( $data['user_login'] ) && username_exists( $data['user_login'] ) ) {

			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: This %s already exists.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];

		}

		$data['user_pass'] = $data['user_pass'] ?? wp_generate_password( 15, true, true );

		$result = ( new Setup_User ( $data ) )->setup();

		return [
			'id'      => is_wp_error( $result ) ? 0 : $result,
			'message' => $this->get_message( $result, $data, self::$entity, $action ),
		];
	}


	/**
	 * @param $data
	 * @param $action
	 * @param $object_id
	 *
	 * @return array
	 */
	protected function update( $data, $action, $object_id ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s update is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		$user = get_userdata( $object_id );

		if ( ! is_object( $user ) ) {

			return [
				'id'      => $object_id,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: This %s with ID:%s does not exist.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity, $object_id ),
					self::$entity,
					$action
				),
			];

		}

		$result = ( new Setup_User ( $data ) )->setup();

		return [
			'id'      => is_wp_error( $result ) ? 0 : $result,
			'message' => $this->get_message( $result, $data, self::$entity, $action ),
		];
	}


	/**
	 * @param $object_id
	 * @param $data
	 * @param $action
	 *
	 * @return array
	 */
	protected function delete( $object_id, $data, $action ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s delete is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		if ( is_multisite() ) {
			require_once ABSPATH . 'wp-admin/includes/ms.php';
			$result = wpmu_delete_user( $object_id );
		} else {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			$result = wp_delete_user( $object_id );
		}

		return [
			'id'      => $result ? $object_id : 0,
			'message' => $this->get_message( $object_id, $data, self::$entity, $action ),
		];
	}

}