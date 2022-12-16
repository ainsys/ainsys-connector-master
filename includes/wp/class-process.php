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
	protected function send_data( int $object_id, string $object_name, string $request_action, $fields ): array {

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


	//TODO вынести проверки настройки в общий класс

	public function get_option_control( $entity ) {

		$controls = Settings::get_option( 'check_controlling_entity' );

		return ! empty( $controls[ $entity ] ) ? $controls[ $entity ]['general'] : [];
	}


	public function get_option_control_on_off( $entity ) {

		return ! empty( $this->get_option_control( $entity )['on_off'] ) ? $this->get_option_control( $entity )['on_off'] : 0;
	}


	public function get_option_control_create( $entity ) {

		return ! empty( $this->get_option_control( $entity )['create'] ) ? $this->get_option_control( $entity )['create'] : 0;
	}


	public function get_option_control_read( $entity ) {

		return ! empty( $this->get_option_control( $entity )['read'] ) ? $this->get_option_control( $entity )['read'] : 0;
	}


	public function get_option_control_update( $entity ) {

		return ! empty( $this->get_option_control( $entity )['update'] ) ? $this->get_option_control( $entity )['update'] : 0;
	}


	public function get_option_control_delete( $entity ) {

		return ! empty( $this->get_option_control( $entity )['delete'] ) ? $this->get_option_control( $entity )['delete'] : 0;
	}


	public function has_entity_disable_create( $entity, $request_action = '' ): bool {

		if ( empty( $this->get_option_control( $entity ) )
		     || ( ! empty( $this->get_option_control_on_off( $entity ) ) || ! empty( $this->get_option_control_create( $entity ) ) )
		) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => '',
					'server_response' => '',
					'error'           => 1,
				]
			);

			return true;
		}

		return false;
	}


	public function has_entity_disable_update( $entity, $request_action = '' ): bool {

		if (
			empty( $this->get_option_control( $entity ) )
			|| ( ! empty( $this->get_option_control_on_off( $entity ) ) || ! empty( $this->get_option_control_update( $entity ) ) )
		) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => '',
					'server_response' => '',
					'error'           => 1,
				]
			);

			return true;
		}

		return false;
	}


	public function has_entity_disable_read( $entity, $request_action = '' ): bool {

		if (
			empty( $this->get_option_control( $entity ) )
			|| ( ! empty( $this->get_option_control_on_off( $entity ) ) || ! empty( $this->get_option_control_read( $entity ) ) )
		) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => '',
					'server_response' => '',
					'error'           => 1,
				]
			);

			return true;
		}

		return false;
	}


	public function has_entity_disable_delete( $entity, $request_action = '' ): bool {

		if (
			empty( $this->get_option_control( $entity ) )
			|| ( ! empty( $this->get_option_control_on_off( $entity ) )
			     || ! empty( $this->get_option_control_delete( $entity ) ) )
		) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => '',
					'server_response' => '',
					'error'           => 1,
				]
			);

			return true;
		}

		return false;
	}

}