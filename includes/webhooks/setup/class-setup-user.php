<?php

namespace Ainsys\Connector\Master\Webhooks\Setup;

use WP_User;

class Setup_User {

	protected array $data;

	protected int $user_id;

	protected $user;


	public function __construct( $data ) {

		$this->data    = $data;
		$this->user_id = isset( $data['user_id'] ) ? (int) $data['user_id'] : 0;
		$this->user    = get_userdata( $this->user_id );
	}


	public function setup() {

		$user_data = $this->get_user_data();

		if ( 0 !== $this->user_id ) {
			$result = $this->update_user( $user_data );
		} else {
			$result = wp_insert_user( $user_data );
		}

		return $result;
	}


	/**
	 * @param  array $user_data
	 *
	 * @return int|\WP_Error
	 */
	protected function update_user( array $user_data ) {

		$result = $this->user_id;

		if ( $this->has_update() ) {

			if ( $this->user->get( 'user_login' ) !== $user_data['user_login'] ) {
				$this->change_username( $this->user_id, $user_data['user_login'] );
			}

			$result = wp_update_user( $user_data );

		}

		return $result;
	}


	/**
	 * @return array
	 */
	public function get_user_data(): array {

		return [
			'ID'              => $this->user_id,
			'user_login'      => empty( $this->data['user_login'] ) ? '' : $this->data['user_login'],
			'user_pass'       => empty( $this->data['user_pass'] ) ? '' : $this->data['user_pass'],
			'user_nicename'   => empty( $this->data['user_nicename'] ) ? '' : $this->data['user_nicename'],
			'user_url'        => empty( $this->data['user_url'] ) ? '' : $this->data['user_url'],
			'user_email'      => empty( $this->data['user_email'] ) ? '' : $this->data['user_email'],
			'role'            => empty( $this->data['role'] ) ? '' : $this->data['role'],
			'display_name'    => empty( $this->data['display_name'] ) ? '' : $this->data['display_name'],
			'nickname'        => empty( $this->data['nickname'] ) ? '' : $this->data['nickname'],
			'first_name'      => empty( $this->data['first_name'] ) ? '' : $this->data['first_name'],
			'last_name'       => empty( $this->data['last_name'] ) ? '' : $this->data['last_name'],
			'description'     => empty( $this->data['description'] ) ? '' : $this->data['description'],
			'user_registered' => empty( $this->data['user_registered'] ) ? '' : $this->data['user_registered'],
			'meta_input'      => [
				'facebook'            => empty( $this->data['facebook'] ) ? '' : $this->data['facebook'],
				'instagram'           => empty( $this->data['instagram'] ) ? '' : $this->data['instagram'],
				'linkedin'            => empty( $this->data['linkedin'] ) ? '' : $this->data['linkedin'],
				'myspace'             => empty( $this->data['myspace'] ) ? '' : $this->data['myspace'],
				'pinterest'           => empty( $this->data['pinterest'] ) ? '' : $this->data['pinterest'],
				'youtube'             => empty( $this->data['youtube'] ) ? '' : $this->data['youtube'],
				'twitter'             => empty( $this->data['twitter'] ) ? '' : $this->data['twitter'],
				'tumblr'              => empty( $this->data['tumblr'] ) ? '' : $this->data['tumblr'],
				'soundcloud'          => empty( $this->data['soundcloud'] ) ? '' : $this->data['soundcloud'],
				'wikipedia'           => empty( $this->data['wikipedia'] ) ? '' : $this->data['wikipedia'],
				'billing_first_name'  => empty( $this->data['billing_first_name'] ) ? '' : $this->data['billing_first_name'],
				'billing_last_name'   => empty( $this->data['billing_last_name'] ) ? '' : $this->data['billing_last_name'],
				'billing_company'     => empty( $this->data['billing_company'] ) ? '' : $this->data['billing_company'],
				'billing_address_1'   => empty( $this->data['billing_address_1'] ) ? '' : $this->data['billing_address_1'],
				'billing_address_2'   => empty( $this->data['billing_address_2'] ) ? '' : $this->data['billing_address_2'],
				'billing_city'        => empty( $this->data['billing_city'] ) ? '' : $this->data['billing_city'],
				'billing_postcode'    => empty( $this->data['billing_postcode'] ) ? '' : $this->data['billing_postcode'],
				'billing_country'     => empty( $this->data['billing_country'] ) ? '' : $this->data['billing_country'],
				'billing_state'       => empty( $this->data['billing_state'] ) ? '' : $this->data['billing_state'],
				'billing_phone'       => empty( $this->data['billing_phone'] ) ? '' : $this->data['billing_phone'],
				'billing_email'       => empty( $this->data['billing_email'] ) ? '' : $this->data['billing_email'],
				'shipping_first_name' => empty( $this->data['shipping_first_name'] ) ? '' : $this->data['shipping_first_name'],
				'shipping_last_name'  => empty( $this->data['shipping_last_name'] ) ? '' : $this->data['shipping_last_name'],
				'shipping_company'    => empty( $this->data['shipping_company'] ) ? '' : $this->data['shipping_company'],
				'shipping_address_1'  => empty( $this->data['shipping_address_1'] ) ? '' : $this->data['shipping_address_1'],
				'shipping_address_2'  => empty( $this->data['shipping_address_2'] ) ? '' : $this->data['shipping_address_2'],
				'shipping_city'       => empty( $this->data['shipping_city'] ) ? '' : $this->data['shipping_city'],
				'shipping_postcode'   => empty( $this->data['shipping_postcode'] ) ? '' : $this->data['shipping_postcode'],
				'shipping_country'    => empty( $this->data['shipping_country'] ) ? '' : $this->data['shipping_country'],
				'shipping_state'      => empty( $this->data['shipping_state'] ) ? '' : $this->data['shipping_state'],
				'shipping_phone'      => empty( $this->data['shipping_phone'] ) ? '' : $this->data['shipping_phone'],
			],

		];
	}


	/**
	 * @return bool
	 */
	public function has_update(): bool {


		$data = [];

		foreach ( $this->data as $key => $val ) {
			if ( 'user_login' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'user_email' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'user_pass' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'user_nicename' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'nickname' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'display_name' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'user_registered' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'first_name' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'last_name' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'description' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'user_url' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'role' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data );
			}

			if ( 'facebook' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'instagram' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'linkedin' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'myspace' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'pinterest' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'youtube' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'twitter' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'tumblr' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'soundcloud' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'wikipedia' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_first_name' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_last_name' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_company' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_address_1' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_address_2' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_city' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_postcode' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_country' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_state' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_phone' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'billing_email' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_first_name' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_last_name' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_company' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_address_1' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_address_2' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_city' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_postcode' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_country' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_state' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

			if ( 'shipping_phone' === $key ) {
				$data = $this->set_update_user_data( $key, $val, $data, true );
			}

		}

		return in_array( 'yes', $data, true );

	}


	/**
	 * @param  string $key
	 * @param         $val
	 * @param  array  $data
	 * @param  bool   $meta
	 *
	 * @return array
	 */
	protected function set_update_user_data( string $key, $val, array $data, bool $meta = false ): array {

		$current_value = $meta ? get_user_meta( $this->user->ID, $key, true ) : $this->user->get( $key );

		if ( $current_value === $val ) {
			$data[ $key ] = 'no';
		} else {
			$data[ $key ] = 'yes';
		}

		return $data;
	}


	protected function change_username( $user_id, $new_username ): void {

		global $wpdb;

		$wpdb->update(
			$wpdb->users,
			[ 'user_login' => $new_username ],
			[ 'ID' => $user_id ]
		);

	}

}