<?php

namespace Ainsys\Connector\Master\WP\Prepare;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\WP\Process;

class Prepare_Posts extends Process {

	public function init_hooks() {

		add_action( 'auto-draft_to_publish', [ $this, 'process_create' ], 100, 1 );
		add_action( 'draft_to_publish', [ $this, 'process_create' ], 100, 1 );
		add_action( 'future_to_publish', [ $this, 'process_create' ], 100, 1 );

		add_action( 'post_updated', [ $this, 'process_update' ], 200, 3 );

		add_action( 'deleted_post', [ $this, 'process_delete' ], 10, 2 );
	}


	/**
	 * @param      $post
	 * @param      $entity
	 * @param      $action
	 *
	 * @return void
	 */
	protected function create( $post, $entity, $action ): void {


		if ( $post->post_type !== $entity ) {
			return;
		}

		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		if ( Conditions::has_entity_disable( $entity, $action ) ) {
			return;
		}

		$post_id = (int) $post->ID;

		$fields = apply_filters(
			"ainsys_process_create_fields_$entity",
			$this->prepare_data( $post_id ),
			$post_id
		);

		$this->send_data( $post_id, $entity, $action, $fields );
	}


	/**
	 * @param      $post
	 * @param      $post_before
	 * @param      $entity
	 * @param      $action
	 *
	 * @return void
	 */
	protected function update( $post, $post_before, $entity, $action ): void {


		if ( $post->post_type !== $entity ) {
			return;
		}

		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		if ( in_array( $post->post_status, [ 'auto-draft', 'draft', 'future', 'trash' ], true ) ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		if ( strtotime( $post->post_date_gmt ) === strtotime( $post_before->post_modified_gmt ) ) {
			return;
		}

		if ( Conditions::has_entity_disable( $entity, $action ) ) {
			return;
		}

		$post_id = (int) $post->ID;

		$fields = apply_filters(
			"ainsys_process_update_fields_$entity",
			$this->prepare_data( $post_id ),
			$post_id
		);

		$this->send_data( $post_id, $entity, $action, $fields );
	}


	/**
	 * @param      $post
	 * @param      $entity
	 * @param      $action
	 *
	 * @return void
	 */
	protected function delete( $post, $entity, $action ): void {

		if ( $post->post_type !== $entity ) {
			return;
		}

		if ( Conditions::has_entity_disable( $entity, $action ) ) {
			return;
		}

		$post_id = (int) $post->ID;

		$fields = apply_filters(
			"ainsys_process_delete_fields_$entity",
			$this->prepare_data( $post_id ),
			$post_id
		);

		$this->send_data( $post_id, $entity, $action, $fields );
	}


	public function checking( int $post_id, $entity ): array {

		$action = 'CHECKING';

		if ( Conditions::has_entity_disable( $entity, $action ) ) {
			return [];
		}

		$post = get_post( $post_id );

		if ( $post->post_type !== $entity ) {
			return [];
		}

		$fields = apply_filters(
			"ainsys_process_checking_fields_$entity",
			$this->prepare_data( $post_id ),
			$post_id
		);

		return $this->send_data( $post_id, $entity, $action, $fields );
	}


	protected function prepare_data( int $post_ID ): array {

		$post = get_post( $post_ID );

		return $this->get_prepare_data( $post );
	}


	public function get_prepare_data( $post ): array {

		return [
			'post_id'           => $post->ID,
			'post_title'        => $post->post_title,
			'post_content'      => $post->post_content,
			'post_excerpt'      => $post->post_excerpt,
			'post_author'       => (int) $post->post_author,
			'post_status'       => $post->post_status,
			'post_type'         => $post->post_type,
			'post_date'         => $post->post_date,
			'post_modified'     => $post->post_modified,
			'post_date_gmt'     => $post->post_date_gmt,
			'post_modified_gmt' => $post->post_modified_gmt,
			'post_password'     => $post->post_password,
			'post_parent'       => $post->post_parent,
			'menu_order'        => $post->menu_order,
			'post_slug'         => $post->post_name,
			'post_link'         => $post->guid,
			'comment_status'    => $post->comment_status,
			'comment_count'     => (int) $post->comment_count,
			'custom_fields'     => get_post_meta( $post->ID ),
		];
	}

}