<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;

class Process_Attachments implements Hooked {

	/**
	 * @var Core
	 */
	private Core $core;

	/**
	 * @var Logger
	 */
	private Logger $logger;


	public function __construct( Core $core, Logger $logger ) {

		$this->core   = $core;
		$this->logger = $logger;
	}


	/**
	 * Initializes WordPress hooks for plugin/components.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'add_attachment', [ $this, 'process_new_attachment' ], 10, 1 );
		add_action( 'attachment_updated', [ $this, 'process_edit_attachment' ], 10, 3 );
		add_action( 'delete_attachment', [ $this, 'process_delete_attachment' ], 10, 3 );

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
	public function process_new_attachment( int $attachment_id ): void {

		$request_action = 'CREATE';

		$fields = apply_filters(
			'ainsys_new_attachment_fields',
			$this->prepare_attachment_data( $attachment_id ),
			$attachment_id
		);

		$this->send_data( $attachment_id, $request_action, $fields );

	}


	/**
	 * Sends delete attachment details to AINSYS
	 *
	 * @param  int $attachment_id
	 * @param      $attachment
	 *
	 * @return void
	 */
	public function process_delete_attachment( int $attachment_id, $attachment ): void {

		$request_action = 'DELETE';

		$fields = apply_filters(
			'ainsys_delete_attachment_fields',
			$this->prepare_attachment_data( $attachment_id ),
			$attachment_id,
			$attachment
		);

		$this->send_data( $attachment_id, $request_action, $fields );

	}


	/**
	 * Sends updated attachment details to AINSYS.
	 *
	 * @param       $attachment_id
	 * @param       $attachment_after
	 * @param       $attachment_before
	 * @param  bool $test
	 *
	 * @return array|void
	 */
	public function process_edit_attachment( $attachment_id, $attachment_after, $attachment_before, bool $test = false ) {

		$request_action = 'UPDATE';

		$fields = apply_filters(
			'ainsys_update_attachment_fields',
			$this->prepare_attachment_data( $attachment_id ),
			$attachment_after,
			$attachment_before
		);

		$request_test = $this->send_data( $attachment_id, $request_action, $fields );

		if ( $test ) {
			return $request_test;
		}
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
	 * @param  int    $attachment_id
	 * @param  string $request_action
	 * @param         $fields
	 *
	 * @return array
	 */
	protected function send_data( int $attachment_id, string $request_action, $fields ): array {

		$request_data = [
			'entity'  => [
				'id'   => $attachment_id,
				'name' => 'attachment',
			],
			'action'  => $request_action,
			'payload' => $fields,
		];

		try {
			$server_response = $this->core->curl_exec_func( $request_data );
		} catch ( \Exception $e ) {
			$server_response = 'Error: ' . $e->getMessage();

			$this->logger::save_log_information(
				[
					'object_id'       => 0,
					'entity'          => 'attachment',
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( $request_data ),
					'server_response' => serialize( $server_response ),
					'error'           => 1,
				]
			);

			$this->core->send_error_email( $server_response );
		}

		$this->logger::save_log_information(
			[
				'object_id'       => $attachment_id,
				'entity'          => 'attachment',
				'request_action'  => $request_action,
				'request_type'    => 'outgoing',
				'request_data'    => serialize( $request_data ),
				'server_response' => serialize( $server_response ),
			]
		);

		return [
			'request'  => $request_data,
			'response' => $server_response,
		];
	}


	/**
	 * Function for `add_attachment` action-hook.
	 *
	 * @param  int $post_ID Attachment ID.
	 *
	 * @return array
	 */
	protected function prepare_attachment_data( int $post_ID ): array {

		$attachment = get_post( $post_ID );

		if ( ! $attachment ) {
			return [];
		}

		if ( $attachment->post_type !== 'attachment' ) {
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
