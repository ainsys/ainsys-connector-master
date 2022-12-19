<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Logger;

class Handle {

	protected static string $entity;


	public function handle_error( $data, $result, $message_error, $entity, $action ): string {

		$message = $message_error . $result->get_error_message();

		Logger::save(
			[
				'object_id'       => 0,
				'entity'          => $entity,
				'request_action'  => $action,
				'request_type'    => 'incoming',
				'request_data'    => serialize( $data ),
				'server_response' => serialize( $message ),
				'error'           => 1,
			]
		);

		Core::send_error_email( $message );

		return $message;
	}


	/**
	 * @param $action
	 * @param $user_id
	 *
	 * @return string
	 */
	public function message_success( $action, $user_id ): string {

		return sprintf(
			__( '%s has been successfully %s - %s ID:  %s', AINSYS_CONNECTOR_TEXTDOMAIN ),
			ucwords( strtolower( self::$entity ) ),
			strtolower( $action ),
			$user_id,
			self::$entity
		);
	}

}