<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Conditions\Conditions;
use Ainsys\Connector\Master\Hooked;

class Process_Comments extends Process implements Hooked {

	/**
	 * Initializes WordPress hooks for plugin/components.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'comment_post', [ $this, 'send_new_comment_to_ainsys' ], 10, 3 );
		add_action( 'edit_comment', [ $this, 'send_update_comment_to_ainsys' ], 10, 3 );
	}


	/**
	 * Sends updated WP comment details to AINSYS.
	 *
	 * @param  int    $comment_id
	 * @param         $comment_approved
	 * @param  object $data
	 *
	 */
	public function send_new_comment_to_ainsys( $comment_id, $comment_approved, $data ): void {

		$request_action = 'CREATE';

		if ( Conditions::has_entity_disable_create( 'comment', $request_action ) ) {
			return;
		}

		$fields = apply_filters( 'ainsys_new_comment_fields', $this->prepare_comment_data( $comment_id, $data ), $data );

		$this->send_data( $comment_id, 'comment', $request_action, $fields );

	}


	/**
	 * Prepares WP comment data. Adds ACF fields if there are any.
	 *
	 * @param  int   $comment_id
	 * @param  array $data
	 *
	 * @return array
	 */
	private function prepare_comment_data( $comment_id, $data ) {

		$data['id'] = $comment_id;
		/// Get ACF fields
		$acf_fields = apply_filters( 'ainsys_prepare_extra_comment_data', [], $comment_id );

		return array_merge( $data, $acf_fields );
	}


	/**
	 * Sends updated WP comment details to AINSYS.
	 *
	 * @param  int   $comment_id
	 * @param  array $data
	 * @param  bool  $checking_connected
	 *
	 * @return array
	 */
	public function send_update_comment_to_ainsys( $comment_id, $data, $checking_connected = false ): array {

		$request_action = $checking_connected ? 'Checking Connected' : 'UPDATE';

		if ( Conditions::has_entity_disable_update( 'comment', $request_action ) ) {
			return [];
		}

		$fields = apply_filters( 'ainsys_update_comment_fields', $this->prepare_comment_data( $comment_id, $data ), $data );

		return $this->send_data( $comment_id, 'comment', $request_action, $fields );

	}

}
