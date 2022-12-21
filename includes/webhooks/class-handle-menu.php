<?php

namespace Ainsys\Connector\Master\Webhooks;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Webhook_Handler;
use WP_Error;

class Handle_Menu extends Handle implements Hooked, Webhook_Handler {

	protected static string $entity = 'menu';


	public function register_webhook_handler( $handlers = [] ) {

		$handlers[ self::$entity ] = [ $this, 'handler' ];

		return $handlers;
	}


	/**
	 * @param  array  $data
	 * @param  string $action
	 *
	 * @return string
	 */
	protected function create( array $data, string $action ): string {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s creation is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}


		if(empty($data['menu-name'])){
			$result = new WP_Error( 'menu-name_missing', __( 'The attribute menu-name  is missing.', AINSYS_CONNECTOR_TEXTDOMAIN ), $data );

			return $this->get_message( $result, $data, self::$entity, $action );
		}

		$menu_id = wp_create_nav_menu( $data['menu-name'] );

		if( !empty($data['menu_locations']) && !has_nav_menu( $data['menu-name'] ) ){
			$locations = [];

			foreach ($data['menu_locations'] as $location){
				$locations[$location] = $menu_id;
			}

			set_theme_mod( 'nav_menu_locations', $locations );
		}
		$menu_items = [];
		if ( ! empty( $data['menu_items'] ) ) {


			foreach ( $data['menu_items'] as $slug => $nav_item ) {
				$menu_items[] = [];
				if ( array_key_exists( 'parent', $nav_item ) ) {
					$menu_items[]['parent'] = $nav_item['parent'];
				}

				$menu_item_data = [
					'menu-item-db-id'         => $nav_item['object_id'],
					'menu-item-object-id'     => $nav_item['object_id'],
					'menu-item-object'        => $nav_item['object'] ? : '',
					'menu-item-parent-id'     => $nav_item['parent_id'] ? : 0,
					'menu-item-position'      => $nav_item['position'] ? : 0,
					'menu-item-type'          => $nav_item['type'] ? : 'custom',
					'menu-item-title'         => $nav_item['title'] ? : '',
					'menu-item-url'           => $nav_item['url'] ? : '',
					'menu-item-description'   => $nav_item['title'] ? : '',
					'menu-item-attr-title'    => $nav_item['description'] ? : '',
					'menu-item-target'        => $nav_item['attr_title'] ? : '',
					'menu-item-classes'       => $nav_item['classes'] ? : [],
					'menu-item-xfn'           => $nav_item['xfn'] ? : '',
					'menu-item-status'        => $nav_item['status'] ? : '',
					'menu-item-post-date'     => $nav_item['post_date'] ? : '',
					'menu-item-post-date-gmt' => $nav_item['post_date_gmt'] ? : '',
				];

				$menu_items[]['id'] = wp_update_nav_menu_item( 0, 0, $menu_item_data );
			}
		}
		error_log( print_r( $menu_items, 1 ) );
		$result = $menu_id;

		return $this->get_message( $result, $data, self::$entity, $action );
	}


	/**
	 * @param $data
	 * @param $action
	 * @param $object_id
	 *
	 * @return string
	 */
	protected function update( $data, $action, $object_id ): string {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s update is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}



		$result = wp_update_post( $data );

		return $this->get_message( $result, $data, self::$entity, $action );
	}


	/**
	 * @param $object_id
	 * @param $data
	 * @param $action
	 *
	 * @return string
	 */
	protected function delete( $object_id, $data, $action ): string {

		if ( Conditions::has_entity_disable( self::$entity, $action, 'incoming' ) ) {
			return sprintf( __( 'Error: %s delete is disabled in settings.', AINSYS_CONNECTOR_TEXTDOMAIN ), self::$entity );
		}

		$result = wp_delete_post( $object_id );

		return $this->get_message( $result, $data, self::$entity, $action );
	}

}