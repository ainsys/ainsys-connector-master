<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;

class Process_Comments implements Hooked {

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

		$fields = apply_filters( 'ainsys_new_comment_fields', $this->prepare_comment_data( $comment_id, $data ), $data );

		$this->send_data( $comment_id, $request_action, $fields );

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
	 * @param  bool  $test
	 *
	 * @return array|void
	 */
	public function send_update_comment_to_ainsys( $comment_id, $data, $test = false ) {

		$request_action = 'UPDATE';

		$fields = apply_filters( 'ainsys_update_comment_fields', $this->prepare_comment_data( $comment_id, $data ), $data );

		$request_test = $this->send_data( $comment_id, $request_action, $fields );

		if ( $test ) {
			return $request_test;
		}
	}


	/**
	 * @param  int    $comment_id
	 * @param  string $request_action
	 * @param         $fields
	 *
	 * @return array
	 */
	protected function send_data( int $comment_id, string $request_action, $fields ): array {

		$request_data = [
			'entity'  => [
				'id'   => $comment_id,
				'name' => 'comment',
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
					'entity'          => 'comment',
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
				'object_id'       => $comment_id,
				'entity'          => 'comment',
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

}
