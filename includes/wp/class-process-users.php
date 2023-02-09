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

		add_action( 'user_register', [ $this, 'process_create' ], 10, 1 );
		add_action( 'profile_update', [ $this, 'process_update' ], 10, 1 );
		add_action( 'delete_user', [ $this, 'process_delete' ], 10, 1 );
		add_action( 'wpmu_delete_user', [ $this, 'process_delete' ], 10, 1 );

	}


	/**
	 * Sends new user details to AINSYS
	 *
	 * @param  int $user_id
	 *
	 * @return void
	 */
	public function process_create( int $user_id ): void {

		self::$action = 'CREATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_create_fields_' . self::$entity,
			$this->prepare_data( $user_id ),
			$user_id
		);

		$this->send_data( $user_id, self::$entity, self::$action, $fields );

	}


	/**
	 * Sends updated user details to AINSYS.
	 *
	 * @param  int $user_id
	 *
	 * @return void
	 * @reference in multisite mode, users are created without a password,
	 * a password is created automatically or when clicking on a link, because this hook triggers the user creation field
	 */
	public function process_update( int $user_id ): void {

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
	 * Sends delete user details to AINSYS
	 *
	 * @param  int $user_id
	 *
	 * @return void
	 */
	public function process_delete( int $user_id ): void {

		self::$action = 'DELETE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_delete_fields_' . self::$entity,
			$this->prepare_data( $user_id ),
			$user_id
		);

		$this->send_data( $user_id, self::$entity, self::$action, $fields );

	}


	/**
	 *
	 * @param  int $user_id
	 *
	 * @return array
	 */

	public function process_checking( int $user_id ): array {

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
			'user_nickname'            => $user->get( 'nickname' ),
			'user_display_name'        => $user->get( 'display_name' ),
			'user_registered'          => $user->get( 'user_registered' ),
			'user_first_name'          => $user->get( 'first_name' ),
			'user_last_name'           => $user->get( 'last_name' ),
			'user_description'         => $user->get( 'description' ),
			'user_link_website'        => $user->get( 'user_url' ),
			'user_link_facebook'       => get_user_meta( $user->ID, 'facebook', true ),
			'user_link_instagram'      => get_user_meta( $user->ID, 'instagram', true ),
			'user_link_linkedin'       => get_user_meta( $user->ID, 'linkedin', true ),
			'user_link_myspace'        => get_user_meta( $user->ID, 'myspace', true ),
			'user_link_pinterest'      => get_user_meta( $user->ID, 'pinterest', true ),
			'user_link_youtube'        => get_user_meta( $user->ID, 'youtube', true ),
			'user_link_twitter'        => get_user_meta( $user->ID, 'twitter', true ),
			'user_link_tumblr'         => get_user_meta( $user->ID, 'tumblr', true ),
			'user_link_soundcloud'     => get_user_meta( $user->ID, 'soundcloud', true ),
			'user_link_wikipedia'      => get_user_meta( $user->ID, 'wikipedia', true ),
			'user_billing_first_name'  => get_user_meta( $user->ID, 'billing_first_name', true ),
			'user_billing_last_name'   => get_user_meta( $user->ID, 'billing_last_name', true ),
			'user_billing_company'     => get_user_meta( $user->ID, 'billing_company', true ),
			'user_billing_address_1'   => get_user_meta( $user->ID, 'billing_address_1', true ),
			'user_billing_address_2'   => get_user_meta( $user->ID, 'billing_address_2', true ),
			'user_billing_city'        => get_user_meta( $user->ID, 'billing_city', true ),
			'user_billing_postcode'    => get_user_meta( $user->ID, 'billing_postcode', true ),
			'user_billing_country'     => get_user_meta( $user->ID, 'billing_country', true ),
			'user_billing_state'       => get_user_meta( $user->ID, 'billing_state', true ),
			'user_billing_phone'       => get_user_meta( $user->ID, 'billing_phone', true ),
			'user_billing_email'       => get_user_meta( $user->ID, 'billing_email', true ),
			'user_shipping_first_name' => get_user_meta( $user->ID, 'shipping_first_name', true ),
			'user_shipping_last_name'  => get_user_meta( $user->ID, 'shipping_last_name', true ),
			'user_shipping_company'    => get_user_meta( $user->ID, 'shipping_company', true ),
			'user_shipping_address_1'  => get_user_meta( $user->ID, 'shipping_address_1', true ),
			'user_shipping_address_2'  => get_user_meta( $user->ID, 'shipping_address_2', true ),
			'user_shipping_city'       => get_user_meta( $user->ID, 'shipping_city', true ),
			'user_shipping_postcode'   => get_user_meta( $user->ID, 'shipping_postcode', true ),
			'user_shipping_country'    => get_user_meta( $user->ID, 'shipping_country', true ),
			'user_shipping_state'      => get_user_meta( $user->ID, 'shipping_state', true ),
			'user_shipping_phone'      => get_user_meta( $user->ID, 'shipping_phone', true ),
			'user_role_caps'           => Helper::array_to_string( $user->get_role_caps() ),
			'user_role'                => empty( $user->roles ) ? '' : implode( '|', $user->roles ),
		];
	}

}
