<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Webhook_Handler;

class Handle_User extends Handle implements Hooked, Webhook_Handler {

	protected static string $entity = 'user';


	public function register_webhook_handler( $handlers = [] ) {

		$handlers[ self::$entity ] = [ $this, 'handler' ];

		return $handlers;
	}


	/**
	 * @param  array  $data
	 * @param  string $action
	 *
	 * @return array
	 */
	protected function create( array $data, string $action ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s creation is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		$data['user_pass'] = $data['user_pass'] ?? wp_generate_password( 15, true, true );

		[ $user, $user_meta ] = $this->get_user_data( $data );

		$user['meta_input'] = $user_meta;

		$result = wp_insert_user( $user );

		return [
			'id'      => is_wp_error( $result ) ? 0 : $result,
			'message' => $this->get_message( $result, $data, self::$entity, $action ),
		];
	}


	/**
	 * @param $data
	 * @param $action
	 * @param $object_id
	 *
	 * @return array
	 */
	protected function update( $data, $action, $object_id ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s update is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		[ $user, $user_meta ] = $this->get_user_data( $data, $object_id );

		$user['meta_input'] = $user_meta;

		$result = wp_update_user( $user );

		return [
			'id'      => is_wp_error( $result ) ? 0 : $result,
			'message' => $this->get_message( $result, $data, self::$entity, $action ),
		];
	}


	/**
	 * @param $object_id
	 * @param $data
	 * @param $action
	 *
	 * @return array
	 */
	protected function delete( $object_id, $data, $action ): array {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return [
				'id'      => 0,
				'message' => $this->handle_error(
					$data,
					'',
					sprintf( __( 'Error: %s delete is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity ),
					self::$entity,
					$action
				),
			];
		}

		if ( is_multisite() ) {
			require_once ABSPATH . 'wp-admin/includes/ms.php';
			$result = wpmu_delete_user( $object_id );
		} else {
			require_once ABSPATH . 'wp-admin/includes/user.php';
			$result = wp_delete_user( $object_id );
		}

		return [
			'id'      => $result ? $object_id : 0,
			'message' => $this->get_message( $object_id, $data, self::$entity, $action ),
		];
	}


	/**
	 * @param  int $object_id
	 * @param      $data
	 *
	 * @return array
	 */
	protected function get_user_data( $data, int $object_id = 0 ): array {

		$user = [
			'ID'              => (int) $object_id,
			'user_login'      => empty( $data['user_login'] ) ? '' : $data['user_login'],
			'user_pass'       => empty( $data['user_pass'] ) ? '' : $data['user_pass'],
			'user_nicename'   => empty( $data['user_nicename'] ) ? '' : $data['user_nicename'],
			'user_url'        => empty( $data['user_link_website'] ) ? '' : $data['user_link_website'],
			'user_email'      => empty( $data['user_email'] ) ? '' : $data['user_email'],
			'role'            => empty( $data['user_role'] ) ? '' : $data['user_role'],
			'display_name'    => empty( $data['user_display_name'] ) ? '' : $data['user_display_name'],
			'nickname'        => empty( $data['user_nickname'] ) ? '' : $data['user_nickname'],
			'first_name'      => empty( $data['user_first_name'] ) ? '' : $data['user_first_name'],
			'last_name'       => empty( $data['user_last_name'] ) ? '' : $data['user_last_name'],
			'description'     => empty( $data['user_description'] ) ? '' : $data['user_description'],
			'user_registered' => empty( $data['user_registered'] ) ? '' : $data['user_registered'],
			'url'             => empty( $data['user_link_website'] ) ? '' : $data['user_link_website'],

		];

		$user_meta = [
			'facebook'            => empty( $data['user_link_facebook'] ) ? '' : $data['user_link_facebook'],
			'instagram'           => empty( $data['user_link_instagram'] ) ? '' : $data['user_link_instagram'],
			'linkedin'            => empty( $data['user_link_linkedin'] ) ? '' : $data['user_link_linkedin'],
			'myspace'             => empty( $data['user_link_myspace'] ) ? '' : $data['user_link_myspace'],
			'pinterest'           => empty( $data['user_link_pinterest'] ) ? '' : $data['user_link_pinterest'],
			'youtube'             => empty( $data['user_link_youtube'] ) ? '' : $data['user_link_youtube'],
			'twitter'             => empty( $data['user_link_twitter'] ) ? '' : $data['user_link_twitter'],
			'tumblr'              => empty( $data['user_link_tumblr'] ) ? '' : $data['user_link_tumblr'],
			'soundcloud'          => empty( $data['user_link_soundcloud'] ) ? '' : $data['user_link_soundcloud'],
			'wikipedia'           => empty( $data['user_link_wikipedia'] ) ? '' : $data['user_link_wikipedia'],
			'billing_first_name'  => empty( $data['user_billing_first_name'] ) ? '' : $data['user_billing_first_name'],
			'billing_last_name'   => empty( $data['user_billing_last_name'] ) ? '' : $data['user_billing_last_name'],
			'billing_company'     => empty( $data['user_billing_company'] ) ? '' : $data['user_billing_company'],
			'billing_address_1'   => empty( $data['user_billing_address_1'] ) ? '' : $data['user_billing_address_1'],
			'billing_address_2'   => empty( $data['user_billing_address_2'] ) ? '' : $data['user_billing_address_2'],
			'billing_city'        => empty( $data['user_billing_city'] ) ? '' : $data['user_billing_city'],
			'billing_postcode'    => empty( $data['user_billing_postcode'] ) ? '' : $data['user_billing_postcode'],
			'billing_country'     => empty( $data['user_billing_country'] ) ? '' : $data['user_billing_country'],
			'billing_state'       => empty( $data['user_billing_state'] ) ? '' : $data['user_billing_state'],
			'billing_phone'       => empty( $data['user_billing_phone'] ) ? '' : $data['user_billing_phone'],
			'billing_email'       => empty( $data['user_billing_email'] ) ? '' : $data['user_billing_email'],
			'shipping_first_name' => empty( $data['user_shipping_first_name'] ) ? '' : $data['user_shipping_first_name'],
			'shipping_last_name'  => empty( $data['user_shipping_last_name'] ) ? '' : $data['user_shipping_last_name'],
			'shipping_company'    => empty( $data['user_shipping_company'] ) ? '' : $data['user_shipping_company'],
			'shipping_address_1'  => empty( $data['user_shipping_address_1'] ) ? '' : $data['user_shipping_address_1'],
			'shipping_address_2'  => empty( $data['user_shipping_address_2'] ) ? '' : $data['user_shipping_address_2'],
			'shipping_city'       => empty( $data['user_shipping_city'] ) ? '' : $data['user_shipping_city'],
			'shipping_postcode'   => empty( $data['user_shipping_postcode'] ) ? '' : $data['user_shipping_postcode'],
			'shipping_country'    => empty( $data['user_shipping_country'] ) ? '' : $data['user_shipping_country'],
			'shipping_state'      => empty( $data['user_shipping_state'] ) ? '' : $data['user_shipping_state'],
			'shipping_phone'      => empty( $data['user_shipping_phone'] ) ? '' : $data['user_shipping_phone'],
		];

		return [ $user, $user_meta ];
	}

}