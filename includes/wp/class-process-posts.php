<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;

class Process_Posts extends Process implements Hooked {

	protected static string $entity = 'post';


	/**
	 * Initializes WordPress hooks for plugin/components.
	 *
	 * @return void
	 */
	public function init_hooks() {
		//TODO проверить на ошибки, что-то странное возвращает
		add_action( 'wp_insert_post', [ $this, 'process_create' ], 10, 3 );
		add_action( 'edit_post', [ $this, 'process_update' ], 10,3 );
		add_action( 'delete_post', [ $this, 'process_delete' ], 10, 2 );

	}


	/**
	 * Sends new attachment details to AINSYS
	 *
	 * @param  int $post_id
	 * @param      $post
	 * @param      $update
	 *
	 * @return void
	 */
	public function process_create( int $post_id, $post, $update ): void {

		if ( $update ) {
			return;
		}

		$request_action = 'CREATE';

		if ( Conditions::has_entity_disable_create( self::$entity, $request_action ) ) {
			return;
		}

		if ( $post->post_type !== self::$entity ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_create_fields_' . self::$entity,
			$this->prepare_post_data( $post_id, $post ),
			$post_id
		);

		$this->send_data( $post_id, self::$entity, $request_action, $fields );

	}


	/**
	 * Sends updated attachment details to AINSYS.
	 *
	 * @param       $post_id
	 * @param       $post
	 * @param  bool $checking_connected
	 *
	 * @return array
	 */
	public function process_update( $post_id, $post, bool $checking_connected = false ): array {

		$request_action = $checking_connected ? 'Checking Connected' : 'UPDATE';

		if ( Conditions::has_entity_disable_update( self::$entity, $request_action ) ) {
			return [];
		}

		if ( $post->post_type !== self::$entity ) {
			return [];
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_post_data( $post_id, $post ),
			$post_id
		);
		error_log( print_r( $post, 1 ) );
		return $this->send_data( $post_id, self::$entity, $request_action, $fields );
	}


	/**
	 * Sends delete attachment details to AINSYS
	 *
	 * @param  int $post_id
	 * @param      $post
	 *
	 * @return void
	 */
	public function process_delete( int $post_id, $post ): void {

		$request_action = 'DELETE';

		$fields = apply_filters(
			'ainsys_process_delete_fields_' . self::$entity,
			$this->prepare_post_data( $post_id, $post ),
			$post_id
		);

		$this->send_data( $post_id, self::$entity, $request_action, $fields );

	}


	/**
	 * Function for `add_attachment` action-hook.
	 *
	 * @param  int $post_ID Post ID.
	 * @param      $post
	 *
	 * @return array
	 */
	protected function prepare_post_data( int $post_ID, $post ){

		if ( ! $post ) {
			$post = get_post( $post_ID );
		}

		if ( $post->post_type !== self::$entity ) {
			return [];
		}

		return $post;
	}

}
