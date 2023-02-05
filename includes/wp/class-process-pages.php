<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;
use WP_Post;

class Process_Pages extends Process implements Hooked {

	protected static string $entity = 'page';

	/**
	 * @var false
	 */
	protected bool $has_update = true;


	/**
	 * Initializes WordPress hooks for plugin/components.
	 *save_post_page
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'wp_after_insert_post', [ $this, 'process_create' ], 1000, 4 );
		add_action( 'wp_after_insert_post', [ $this, 'process_update' ], 1010, 4 );
		add_action( 'deleted_post', [ $this, 'process_delete' ], 10, 2 );

	}


	/**
	 * Sends new page details to AINSYS
	 *
	 * @param  int          $post_id     Post ID.
	 * @param  WP_Post      $post        Post object.
	 * @param  bool         $update      Whether this is an existing post being updated.
	 * @param  WP_Post|null $post_before Null for new posts, the WP_Post object prior
	 *                                   to the update for updated posts.
	 *
	 * @return void
	 */
	public function process_create( int $post_id, WP_Post $post, bool $update, ?WP_Post $post_before ): void {


		self::$action = 'CREATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( $post_before && 'publish' === $post_before->post_status ) {
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
	 * Sends updated page details to AINSYS.
	 *
	 * @param  int          $post_id     Post ID.
	 * @param  WP_Post      $post        Post object.
	 * @param  bool         $update      Whether this is an existing post being updated.
	 * @param  WP_Post|null $post_before Null for new posts, the WP_Post object prior
	 *                                   to the update for updated posts.
	 */
	public function process_update( int $post_id, WP_Post $post, bool $update, ?WP_Post $post_before ): void {


		self::$action = 'UPDATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		if ( 'auto-draft' === $post->post_status ) {
			return;
		}

		if ( ( $post && $post_before ) && ( $post->post_modified > $post_before->post_modified ) ) {
			return;
		}

		if ( ! $this->is_updated( $post_id, $post, $update ) ) {
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
	 * Sends delete page details to AINSYS
	 *
	 * @param  int     $post_id
	 * @param  WP_Post $post Post object.
	 *
	 * @return void
	 */
	public function process_delete( int $post_id, WP_Post $post ): void {

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
	 * Sends checking page details to AINSYS.
	 *
	 * @param  int     $post_id Post ID.
	 * @param  WP_Post $post    Post object.
	 * @param  bool    $update  Whether this is an existing post being updated.
	 *
	 * @return array
	 */
	public function process_checking( int $post_id, WP_Post $post, bool $update ): array {

		self::$action = 'CHECKING';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return [];
		}

		if ( ! $this->is_updated( $post_id, $post, $update ) ) {
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
	 * Function for `add_attachment` action-hook.
	 *
	 * @param  int     $post_ID Post ID.
	 * @param  WP_Post $post    Post object.
	 *
	 * @return array
	 */
	protected function prepare_data( int $post_ID, WP_Post $post ): array {

		if ( $post->post_type !== self::$entity ) {
			return [];
		}

		return [
			'post_id'        => $post->ID,
			'post_title'     => $post->post_title,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_author'    => (int) $post->post_author,
			'post_status'    => $post->post_status,
			'post_type'      => $post->post_type,
			'post_date'      => $post->post_date,
			'post_modified'  => $post->post_modified,
			'post_password'  => $post->post_password,
			'post_parent'    => $post->post_parent,
			'menu_order'     => $post->menu_order,
			'post_slug'      => $post->post_name,
			'post_link'      => $post->guid,
			'comment_status' => $post->comment_status,
			'comment_count'  => (int) $post->comment_count,
			'custom_fields'  => get_post_meta( $post->ID ),
		];
	}

}
