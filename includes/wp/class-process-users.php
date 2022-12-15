<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;

class Process_Users implements Hooked {

	/**
	 * Initializes WordPress hooks for plugin/components.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'user_register', [ $this, 'process_new_user' ], 10, 2 );
		add_action( 'profile_update', [ $this, 'send_user_details_update_to_ainsys' ], 10, 4 );
	}


	/**
	 * Sends new user details to AINSYS
	 *
	 * @param  int   $user_id
	 * @param  array $userdata
	 *
	 * @return void
	 */
	public function process_new_user( int $user_id, array $userdata ): void {

		$request_action = 'CREATE';

		$fields = apply_filters( 'ainsys_new_user_fields', $this->prepare_user_data( $user_id, $userdata ), $userdata );

		$this->send_data( $user_id, $request_action, $fields );

	}


	/**
	 * Prepares WP user data. Adds ACF fields if there are any.
	 *
	 * @param  int   $user_id
	 * @param  array $data
	 *
	 * @return array
	 */
	private function prepare_user_data( $user_id, $data ) {

		//$data['id'] = $user_id;
		/// Get ACF fields
		$acf_fields = apply_filters( 'ainsys_prepare_extra_user_data', [], $user_id );

		return array_merge( $data, $acf_fields );
	}


	/**
	 * Sends updated user details to AINSYS.
	 *
	 * @param  int   $user_id
	 * @param  array $userdata
	 *
	 * @param  array $old_user_data
	 * @param  bool  $checking_connected
	 *
	 * @return array
	 * @reference in multisite mode, users are created without a password,
	 * a password is created automatically or when clicking on a link, because this hook triggers the user creation field
	 */
	public function send_user_details_update_to_ainsys( $user_id, $userdata, $old_user_data, $checking_connected = false ): array {

		$request_action = $checking_connected ? 'Checking Connected' : 'UPDATE';

		$fields = apply_filters( 'ainsys_user_details_update_fields', $this->prepare_user_data( $user_id, $userdata ), $userdata );

		return $this->send_data( $user_id, $request_action, $fields );

	}


	/**
	 * @param  int    $user_id
	 * @param  string $request_action
	 * @param         $fields
	 *
	 * @return array
	 */
	protected function send_data( int $user_id, string $request_action, $fields ): array {

		$request_data = [
			'entity'  => [
				'id'   => $user_id,
				'name' => 'user',
			],
			'action'  => $request_action,
			'payload' => $fields,
		];

		try {
			$server_response = Core::curl_exec_func( $request_data );
		} catch ( \Exception $e ) {
			$server_response = 'Error: ' . $e->getMessage();

			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => 'user',
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( $request_data ),
					'server_response' => serialize( $server_response ),
					'error'           => 1,
				]
			);

			Core::send_error_email( $server_response );
		}

		Logger::save(
			[
				'object_id'       => $user_id,
				'entity'          => 'user',
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
