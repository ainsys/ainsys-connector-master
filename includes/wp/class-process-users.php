<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Helper;
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
			$this->prepare_data( $user_id ),
			$user_id
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
	public function process_checking( $user_id, $userdata, $old_user_data ): array {

		self::$action = 'CHECKING';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return [];
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $user_id ),
			$user_id
		);

		return $this->send_data( $user_id, self::$entity, self::$action, $fields );

	}


	/**
	 * Prepares WP user data. Adds ACF fields if there are any.
	 *
	 * @param  int $user_id
	 *
	 * @return array
	 */
	protected function prepare_data( int $user_id ): array {

		$user = get_userdata( $user_id );

		return [
			'user_id'                  => $user->ID,
			'user_login'               => $user->get( 'user_login' ),
			'user_pass'                => $user->get( 'user_pass' ),
			'user_email'               => $user->get( 'user_email' ),
			'user_nicename'            => $user->get( 'user_nicename' ),
			'user_display_name'        => $user->get( 'display_name' ),
			'user_registered'          => $user->get( 'user_registered' ),
			'user_first_name'          => $user->get( 'first_name' ),
			'user_last_name'           => $user->get( 'last_name' ),
			'user_description'         => $user->get( 'description' ),
			'user_link_website'        => $user->get( 'user_url' ),
			'user_link_facebook'       => $user->get( 'facebook' ),
			'user_link_instagram'      => $user->get( 'instagram' ),
			'user_link_linkedin'       => $user->get( 'linkedin' ),
			'user_link_myspace'        => $user->get( 'myspace' ),
			'user_link_pinterest'      => $user->get( 'pinterest' ),
			'user_link_youtube'        => $user->get( 'youtube' ),
			'user_link_twitter'        => $user->get( 'twitter' ),
			'user_link_tumblr'         => $user->get( 'tumblr' ),
			'user_link_soundcloud'     => $user->get( 'soundcloud' ),
			'user_billing_first_name'  => $user->get( 'billing_first_name' ),
			'user_billing_last_name'   => $user->get( 'billing_last_name' ),
			'user_billing_company'     => $user->get( 'billing_company' ),
			'user_billing_address_1'   => $user->get( 'billing_address_1' ),
			'user_billing_address_2'   => $user->get( 'billing_address_2' ),
			'user_billing_city'        => $user->get( 'billing_city' ),
			'user_billing_postcode'    => $user->get( 'billing_postcode' ),
			'user_billing_country'     => $user->get( 'billing_country' ),
			'user_billing_state'       => $user->get( 'billing_state' ),
			'user_billing_phone'       => $user->get( 'billing_phone' ),
			'user_billing_email'       => $user->get( 'billing_email' ),
			'user_shipping_first_name' => $user->get( 'shipping_first_name' ),
			'user_shipping_last_name'  => $user->get( 'shipping_last_name' ),
			'user_shipping_company'    => $user->get( 'shipping_company' ),
			'user_shipping_address_1'  => $user->get( 'shipping_address_1' ),
			'user_shipping_address_2'  => $user->get( 'shipping_address_2' ),
			'user_shipping_city'       => $user->get( 'shipping_city' ),
			'user_shipping_postcode'   => $user->get( 'shipping_postcode' ),
			'user_shipping_country'    => $user->get( 'shipping_country' ),
			'user_shipping_state'      => $user->get( 'shipping_state' ),
			'user_shipping_phone'      => $user->get( 'shipping_phone' ),
			'user_role_caps'           => Helper::array_to_string( $user->get_role_caps() ),
		];
	}

}
