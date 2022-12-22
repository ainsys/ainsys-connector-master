<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;

class Process_Attachments extends Process implements Hooked {

	protected static string $entity = 'attachment';


	/**
	 * Initializes WordPress hooks for plugin/components.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'add_attachment', [ $this, 'process_create' ], 10, 1 );
		add_action( 'attachment_updated', [ $this, 'process_update' ], 10, 3 );
		add_action( 'delete_attachment', [ $this, 'process_delete' ], 10, 3 );

		add_filter( 'bulk_actions-upload', [ $this, 'bulk_updates_attachment' ] );
		add_filter( 'handle_bulk_actions-upload', [ $this, 'bulk_updates_attachment_action_handler' ], 10, 3 );
	}


	/**
	 * Sends new attachment details to AINSYS
	 *
	 * @param  int $attachment_id
	 *
	 * @return void
	 */
	public function process_create( int $attachment_id ): void {

		self::$action = 'CREATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_create_fields_' . self::$entity,
			$this->prepare_data( $attachment_id ),
			$attachment_id
		);

		$this->send_data( $attachment_id, self::$entity, self::$action, $fields );

	}


	/**
	 * Sends updated attachment details to AINSYS.
	 *
	 * @param       $attachment_id
	 * @param       $attachment_after
	 * @param       $attachment_before
	 */
	public function process_update( $attachment_id, $attachment_after, $attachment_before ): void {

		self::$action = 'UPDATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $attachment_id ),
			$attachment_after,
			$attachment_before
		);

		$this->send_data( $attachment_id, self::$entity, self::$action, $fields );
	}


	/**
	 * Sends delete attachment details to AINSYS
	 *
	 * @param  int $attachment_id
	 * @param      $attachment
	 *
	 * @return void
	 */
	public function process_delete( int $attachment_id, $attachment ): void {

		self::$action = 'DELETE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_delete_fields_' . self::$entity,
			$this->prepare_data( $attachment_id ),
			$attachment_id,
			$attachment
		);

		$this->send_data( $attachment_id, self::$entity, self::$action, $fields );

	}


	/**
	 *
	 * @param       $attachment_id
	 * @param       $attachment_after
	 * @param       $attachment_before
	 *
	 * @return array
	 */
	public function process_checking( $attachment_id, $attachment_after, $attachment_before ): array {

		self::$action = 'CHECKING';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return [];
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $attachment_id ),
			$attachment_after,
			$attachment_before
		);

		return $this->send_data( $attachment_id, self::$entity, self::$action, $fields );
	}


	/**
	 *
	 *
	 * @param $redirect_to
	 * @param $doaction
	 * @param $post_ids
	 *
	 * @return string
	 */
	public function bulk_updates_attachment_action_handler( $redirect_to, $doaction, $post_ids ): string {

		if ( 'update_attachments' !== $doaction ) {
			return $redirect_to;
		}

		foreach ( $post_ids as $attach_id ) {
			wp_update_post(
				wp_slash( [
					'ID'                => $attach_id,
					'ainsys_attachment' => true,
				] )
			);
		}

		return add_query_arg( 'update_attachments_action_done', count( $post_ids ), $redirect_to );
	}


	/**
	 * @param $bulk_actions
	 *
	 * @return mixed
	 */
	public function bulk_updates_attachment( $bulk_actions ) {

		$bulk_actions['update_attachments'] = 'Update attachments';

		return $bulk_actions;
	}


	/**
	 * Function for `add_attachment` action-hook.
	 *
	 * @param  int $post_ID Attachment ID.
	 *
	 * @return array
	 */
	protected function prepare_data( int $post_ID ): array {

		$attachment = get_post( $post_ID );

		if ( ! $attachment ) {
			return [];
		}

		if ( $attachment->post_type !== self::$entity ) {
			return [];
		}

		$attached_file = get_attached_file( $attachment->ID );

		if ( strpos( $attachment->post_mime_type, '/' ) !== false ) {
			[ $type, $subtype ] = explode( '/', $attachment->post_mime_type );
		} else {
			[ $type, $subtype ] = [ $attachment->post_mime_type, '' ];
		}

		return [
			'ID'                => $attachment->ID,
			'id'                => $attachment->ID,
			'title'             => $attachment->post_title,
			'filename'          => wp_basename( $attached_file ),
			'filesize'          => size_format( filesize( $attached_file ), 2 ),
			'url'               => wp_get_attachment_url( $attachment->ID ),
			'link'              => get_attachment_link( $attachment->ID ),
			'alt'               => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
			'author'            => $attachment->post_author,
			'description'       => $attachment->post_content,
			'caption'           => $attachment->post_excerpt,
			'name'              => $attachment->post_name,
			'uploaded_to'       => $attachment->post_parent,
			'date'              => $attachment->post_date_gmt,
			'modified'          => $attachment->post_modified_gmt,
			'mime_type'         => $attachment->post_mime_type,
			'type'              => $type,
			'subtype'           => $subtype,
			'ainsys_attachment' => true,
			'meta'              => wp_get_attachment_metadata( $post_ID, false ),
		];
	}

}
