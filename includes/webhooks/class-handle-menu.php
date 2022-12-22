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

		if ( empty( $data['menu-name'] ) ) {
			$result = new WP_Error( 'menu-name_missing', __( 'The attribute menu-name is missing.', AINSYS_CONNECTOR_TEXTDOMAIN ), $data );

			return $this->get_message( $result, $data, self::$entity, $action );
		}

		$menu_id = wp_create_nav_menu( $data['menu-name'] );

		$this->set_menu_locations( $data, $menu_id );

		$this->set_menu_items( $data, $menu_id );

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
		error_log( print_r( $data, 1 ) );
		$menu_id = wp_update_nav_menu_object( $data['ID'], $data );

		$this->set_menu_locations( $data, $menu_id );

		$this->set_menu_items( $data, $menu_id );

		$result = $menu_id;

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

		$result = wp_delete_nav_menu( $object_id );

		return $this->get_message( $result, $data, self::$entity, $action );
	}


	/**
	 * @param  array $data
	 * @param        $menu_id
	 *
	 * @return void
	 */
	protected function set_menu_locations( array $data, $menu_id ): void {

		if ( empty( $data['menu_locations'] ) && ! has_nav_menu( $data['menu-name'] ) ) {
			return;
		}

		$locations = [];

		foreach ( $data['menu_locations'] as $location ) {
			$locations[ $location ] = (int) $menu_id;
		}

		set_theme_mod( 'nav_menu_locations', $locations );
	}


	/**
	 * @param  array $data
	 * @param        $menu_id
	 *
	 * @return void
	 * @todo странно работает присвоение родителя при создании меню, подумать как переделать
	 */
	protected function set_menu_items( array $data, $menu_id ): void {

		if ( empty( $data['menu_items'] ) ) {
			return;

		}

		foreach ( $data['menu_items'] as $nav_item ) {

			$menu_item_data = [
				'menu-item-object-id'     => $nav_item['object_id'] ?? 0,
				'menu-item-object'        => $nav_item['object'] ?? '',
				'menu-item-parent-id'     => $nav_item['parent_id'] ?? 0,
				'menu-item-position'      => $nav_item['position'] ?? 0,
				'menu-item-type'          => $nav_item['type'] ?? 'custom',
				'menu-item-title'         => $nav_item['title'] ?? '',
				'menu-item-url'           => $nav_item['url'] ?? '',
				'menu-item-description'   => $nav_item['description'] ?? '',
				'menu-item-attr-title'    => $nav_item['attr_title'] ?? '',
				'menu-item-target'        => $nav_item['target'] ?? '',
				'menu-item-classes'       => $nav_item['classes'] ?? '',
				'menu-item-xfn'           => $nav_item['xfn'] ?? '',
				'menu-item-status'        => $nav_item['status'] ?? 'publish',
				'menu-item-post-date'     => $nav_item['post_date'] ?? '',
				'menu-item-post-date-gmt' => $nav_item['post_date_gmt'] ?? '',
			];

			wp_update_nav_menu_item( (int) $menu_id, $nav_item['db_id'] ?? 0, $menu_item_data );

		}

	}

}