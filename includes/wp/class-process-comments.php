<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;

class Process_Comments extends Process implements Hooked {

	protected static string $entity = 'comment';


	/**
	 * Initializes WordPress hooks for plugin/components.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'comment_post', [ $this, 'process_create' ], 10, 3 );
		add_action( 'edit_comment', [ $this, 'process_update' ], 10, 3 );
	}


	/**
	 * Sends updated WP comment details to AINSYS.
	 *
	 * @param  int    $comment_id
	 * @param         $comment_approved
	 * @param  object $data
	 *
	 */
	public function process_create( $comment_id, $comment_approved, $data ): void {

		self::$action = 'CREATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_create_fields_' . self::$entity,
			$this->prepare_data( $comment_id, $data ),
			$data
		);

		$this->send_data( $comment_id, 'comment', self::$action, $fields );

	}


	/**
	 * Sends updated WP comment details to AINSYS.
	 *
	 * @param  int   $comment_id
	 * @param  array $data
	 *
	 */
	public function process_update( int $comment_id, array $data ): void {

		self::$action = 'UPDATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $comment_id, $data ),
			$data
		);

		$this->send_data( $comment_id, 'comment', self::$action, $fields );

	}


	/**
	 * Sends updated WP comment details to AINSYS.
	 *
	 * @param  int   $comment_id
	 * @param  array $data
	 *
	 * @return array
	 */
	public function process_checking( int $comment_id, array $data ): array {

		self::$action = 'CHECKING';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return [];
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $comment_id, $data ),
			$data
		);

		return $this->send_data( $comment_id, 'comment', self::$action, $fields );

	}


	/**
	 * Prepares WP comment data. Adds ACF fields if there are any.
	 *
	 * @param  int   $comment_id
	 * @param  array $data
	 *
	 * @return array
	 */
	private function prepare_data( $comment_id, $data ) {

		$data['id'] = $comment_id;
		/// Get ACF fields
		$acf_fields = apply_filters( 'ainsys_prepare_extra_comment_data', [], $comment_id );

		return array_merge( $data, $acf_fields );
	}

}
