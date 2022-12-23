<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;

class Process_Users extends Process implements Hooked {

	protected static string $entity = 'user';


	/**
	 * Initializes WordPress hooks for plugin/components.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'user_register', [ $this, 'process_create' ], 10, 2 );
		add_action( 'profile_update', [ $this, 'process_update' ], 10, 4 );
	}


	/**
	 * Sends new user details to AINSYS
	 *
	 * @param  int   $user_id
	 * @param  array $userdata
	 *
	 * @return void
	 */
	public function process_create( int $user_id, array $userdata ): void {

		self::$action = 'CREATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_create_fields_' . self::$entity,
			$this->prepare_data( $user_id, $userdata ),
			$userdata
		);

		$this->send_data( $user_id, self::$entity, self::$action, $fields );

	}


	/**
	 * Sends updated user details to AINSYS.
	 *
	 * @param  int   $user_id
	 * @param  array $userdata
	 *
	 * @param  array $old_user_data
	 *
	 * @return void
	 * @reference in multisite mode, users are created without a password,
	 * a password is created automatically or when clicking on a link, because this hook triggers the user creation field
	 */
	public function process_update( $user_id, $userdata, $old_user_data ): void {

		self::$action = 'UPDATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $user_id, $userdata ),
			$userdata
		);

		$this->send_data( $user_id, self::$entity, self::$action, $fields );

	}

	/**
	 *
	 * @param  int   $user_id
	 * @param  array $userdata
	 *
	 * @param  array $old_user_data
	 *
	 * @return array
	 */
	public function process_checking( $user_id, $userdata, $old_user_data): array {

		self::$action = 'CHECKING';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return [];
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $user_id, $userdata ),
			$userdata
		);

		return $this->send_data( $user_id, self::$entity, self::$action, $fields );

	}


	/**
	 * Prepares WP user data. Adds ACF fields if there are any.
	 *
	 * @param  int   $user_id
	 * @param  array $data
	 *
	 * @return array
	 */
	private function prepare_data( $user_id, $data ) {

		//$data['id'] = $user_id;
		/// Get ACF fields
		$acf_fields = apply_filters( 'ainsys_prepare_extra_user_data', [], $user_id );

		return array_merge( $data, $acf_fields );
	}

}
