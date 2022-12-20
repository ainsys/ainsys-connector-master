<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Webhook_Handler;

class Handle_Menu extends Handle implements Hooked, Webhook_Handler {

	protected static string $entity = 'menu';


	public function register_webhook_handler( $handlers = [] ) {

		$handlers[ self::$entity ] = [ $this, 'handler' ];

		return $handlers;
	}


	/**
	 * @param  array  $data
	 * @param  string $action
	 *
	 * @return string
	 */
	protected function create( array $data, string $action ): string {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s creation is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}

		if ( empty( $data['post_type'] ) && $data['post_type'] !== self::$entity ) {
			$data['post_type'] = self::$entity;
		}

		if ( empty( $data['post_status'] ) && ! in_array( $data['post_status'], $this->statuses(), true ) ) {
			$data['post_status'] = 'publish';
		}

		$result = wp_insert_post( $data );

		return $this->get_message( $result, $data, self::$entity, $action );
	}


	/**
	 * @param $data
	 * @param $action
	 * @param $object_id
	 *
	 * @return string
	 */
	protected function update( $data, $action, $object_id ): string {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s update is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}

		if ( empty( $data['post_type'] ) && $data['post_type'] !== self::$entity ) {
			$data['post_type'] = self::$entity;
		}

		if ( empty( $data['post_status'] ) && ! in_array( $data['post_status'], $this->statuses(), true ) ) {
			$data['post_status'] = 'publish';
		}

		$result = wp_update_post( $data );

		return $this->get_message( $result, $data, self::$entity, $action );
	}


	/**
	 * @param $object_id
	 * @param $data
	 * @param $action
	 *
	 * @return string
	 */
	protected function delete( $object_id, $data, $action ): string {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s delete is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}

		$result = wp_delete_post( $object_id );

		return $this->get_message( $result, $data, self::$entity, $action );
	}

}