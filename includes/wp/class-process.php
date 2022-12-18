<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\Settings\Settings;

class Process {

	/**
	 * @param  int    $object_id
	 * @param  string $object_name
	 * @param  string $request_action
	 * @param         $fields
	 *
	 * @return array
	 */
	public function send_data( int $object_id, string $object_name, string $request_action, $fields ): array {

		$request_data = [
			'entity'  => [
				'id'   => $object_id,
				'name' => $object_name,
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
					'entity'          => $object_name,
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
				'object_id'       => $object_id,
				'entity'          => $object_name,
				'request_action'  => $request_action,
				'request_type'    => 'outgoing',
				'request_data'    => serialize( $request_data ),
				'server_response' => serialize( $server_response ),
				'error'           => false !== strpos( $server_response, 'Error:' ),
			]
		);

		return [
			'request'  => $request_data,
			'response' => $server_response,
		];
	}

}