<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\WP\Prepare\Prepare_Posts;

class Process_Posts extends Prepare_Posts implements Hooked {

	protected static string $entity = 'post';


	/**
	 * Sends new post details to AINSYS
	 *
	 * @param       $post
	 *
	 * @return void
	 */
	public function process_create( $post ): void {

		self::$action = 'CREATE';

		$this->create( $post, self::$entity, self::$action );

	}


	/**
	 * Sends updated post details to AINSYS.
	 *
	 * @param  int  $post_id Post ID.
	 * @param       $post
	 * @param       $post_before
	 */
	public function process_update( int $post_id, $post, $post_before ): void {

		self::$action = 'UPDATE';

		$this->update( $post, $post_before, self::$entity, self::$action );

	}


	/**
	 * Sends delete post details to AINSYS
	 *
	 * @param  int $post_id
	 * @param      $post
	 *
	 * @return void
	 */
	public function process_delete( int $post_id, $post ): void {

		self::$action = 'DELETE';

		$this->delete( $post, self::$entity, self::$action );

	}


	/**
	 * Sends checking post details to AINSYS.
	 *
	 * @param  int $post_id Post ID.
	 *
	 * @return array
	 */
	public function process_checking( int $post_id ): array {


		return $this->checking( $post_id, self::$entity );
	}


	/**
	 * Function for `add_attachment` action-hook.
	 *
	 * @param  int $post_ID Post ID.
	 *
	 * @return array
	 */
	protected function prepare_data( int $post_ID ): array {

		$post = get_post( $post_ID );

		if ( $post->post_type !== self::$entity ) {
			return [];
		}

		return array_merge(
			$this->get_prepare_data( $post ),
			$this->get_taxonomies( $post )
		);
	}

}
