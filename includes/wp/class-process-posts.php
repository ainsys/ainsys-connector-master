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

		add_action( 'wp_after_insert_post', [ $this, 'process_create' ], 10, 4 );
		add_action( 'save_post_post', [ $this, 'process_update' ], 10, 4 );
		add_action( 'deleted_post', [ $this, 'process_delete' ], 10, 2 );

	}


	/**
	 * Sends new attachment details to AINSYS
	 *
	 * @param  int $post_id
	 * @param      $post
	 * @param      $update
	 * @param      $post_before
	 *
	 * @return void
	 */
	public function process_create( int $post_id, $post, $update, $post_before ): void {

		self::$action = 'CREATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		if ( ! is_null( $post_before ) || true === $update ) {
			return;
		}

		if ( $post->post_type !== self::$entity ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_create_fields_' . self::$entity,
			$this->prepare_data( $post_id, $post ),
			$post_id
		);

		$this->send_data( $post_id, self::$entity, self::$action, $fields );

	}


	/**
	 * Sends updated post details to AINSYS.
	 *
	 * @param       $post_id
	 * @param       $post
	 * @param       $update
	 */
	public function process_update( $post_id, $post, $update ): void {

		self::$action = 'UPDATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		if ( ! $this->is_updated( $post_id, $update ) ) {
			return;
		}

		if ( $post->post_type !== self::$entity ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $post_id, $post ),
			$post_id
		);

		$this->send_data( $post_id, self::$entity, self::$action, $fields );
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

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_delete_fields_' . self::$entity,
			$this->prepare_data( $post_id, $post ),
			$post_id
		);

		$this->send_data( $post_id, self::$entity, self::$action, $fields );

	}


	/**
	 *
	 * @param       $post_id
	 * @param       $post
	 * @param       $update
	 *
	 * @return array
	 */
	public function process_checking( $post_id, $post, $update ): array {

		self::$action = 'CHECKING';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return [];
		}

		if ( ! $this->is_updated( $post_id, $update ) ) {
			return [];
		}

		if ( $post->post_type !== self::$entity ) {
			return [];
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $post_id, $post ),
			$post_id
		);

		return $this->send_data( $post_id, self::$entity, self::$action, $fields );
	}


	/**
	 *
	 * @param  int $post_ID Post ID.
	 * @param      $post
	 *
	 * @return array
	 */
	protected function prepare_data( int $post_ID, $post ): array {

		if ( ! $post ) {
			$post = get_post( $post_ID );
		}

		if ( $post->post_type !== self::$entity ) {
			return [];
		}

		return [
			'ID'             => $post->ID,
			'post_author'    => $post->post_author,
			'post_content'   => $post->post_content,
			'post_title'     => $post->post_title,
			'post_excerpt'   => $post->post_excerpt,
			'post_status'    => $post->post_status,
			'post_type'      => $post->post_type,
			'post_date'      => $post->post_date,
			'post_modified'  => $post->post_modified,
			'post_password'  => $post->post_password,
			'pinged'         => $post->pinged,
			'post_parent'    => 0,
			'menu_order'     => $post->menu_order,
			'guid'           => $post->guid,
			'comment_status' => $post->comment_status,
			'comment_count'  => $post->comment_count,
			'taxonomies'     => $this->get_taxonomies( $post ),
			'meta_input'     => get_post_meta( $post->ID ),
		];
	}

}
