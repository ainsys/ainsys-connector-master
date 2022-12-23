<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Conditions;
use Ainsys\Connector\Master\Hooked;

class Process_Menus extends Process implements Hooked {

	protected static string $entity = 'menu';


	/**
	 * Initializes WordPress hooks for plugin/components.
	 *
	 * @return void
	 */
	public function init_hooks() {

		add_action( 'create_nav_menu', [ $this, 'process_create' ], 10, 1 );
		add_action( 'edit_nav_menu', [ $this, 'process_update' ], 10, 1 );
		add_action( 'delete_nav_menu', [ $this, 'process_delete' ], 10, 1 );

	}


	/**
	 * Sends new attachment details to AINSYS
	 *
	 * @param  int $menu_id
	 *
	 * @return void
	 */
	public function process_create( int $menu_id ): void {

		self::$action = 'CREATE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_create_fields_' . self::$entity,
			$this->prepare_data( $menu_id ),
			$menu_id
		);

		$this->send_data( $menu_id, self::$entity, self::$action, $fields );

	}


	/**
	 * Sends updated post details to AINSYS.
	 *
	 * @param  int  $menu_id
	 * @param  bool $checking_connected
	 *
	 * @return void
	 */
	public function process_update( int $menu_id, bool $checking_connected = false ): void {

		self::$action = $this->get_update_action( $checking_connected );

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		// Check if it is a REST Request
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $menu_id ),
			$menu_id
		);

		$this->send_data( $menu_id, self::$entity, self::$action, $fields );
	}


	/**
	 * Sends delete post details to AINSYS
	 *
	 * @param  int $menu_id
	 *
	 * @return void
	 */
	public function process_delete( int $menu_id ): void {

		self::$action = 'DELETE';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return;
		}

		$fields = apply_filters(
			'ainsys_process_delete_fields_' . self::$entity,
			$this->prepare_data( $menu_id ),
			$menu_id
		);

		$this->send_data( $menu_id, self::$entity, self::$action, $fields );

	}


	/**
	 * Sends updated post details to AINSYS.
	 *
	 * @param  int $menu_id
	 *
	 * @return array
	 */
	public function process_checking( int $menu_id ): array {

		self::$action = 'CHECKING';

		if ( Conditions::has_entity_disable( self::$entity, self::$action ) ) {
			return [];
		}

		// Check if it is a REST Request
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return [];
		}

		$fields = apply_filters(
			'ainsys_process_update_fields_' . self::$entity,
			$this->prepare_data( $menu_id ),
			$menu_id
		);

		return $this->send_data( $menu_id, self::$entity, self::$action, $fields );
	}


	/**
	 *
	 * @param  int $menu_id
	 *
	 * @return array
	 */
	protected function prepare_data( int $menu_id ): array {

		$menu = wp_get_nav_menu_object( $menu_id );

		$menus = [];

		if ( $menu->count > 0 ) {
			$menus = [
				'ID'                  => $menu_id,
				'menu-name'           => $menu->name,
				'site_menu_locations' => get_registered_nav_menus(),
				'menu_locations'      => $this->get_menu_locations( $menu_id ),
				'menu_items'          => $this->get_menu_items( $menu_id ),
			];
		}

		return $menus;
	}


	/**
	 * @param  int $menu_id
	 *
	 * @return array
	 */
	protected function get_menu_locations( int $menu_id ): array {

		$locations = get_nav_menu_locations();

		$location = [];

		foreach ( $locations as $location_key => $location_name ) {
			if ( $menu_id === $location_name ) {
				$location[] = $location_key;
			}
		}

		return $location;
	}


	/**
	 * @param  int $menu_id
	 *
	 * @return array
	 */
	protected function get_menu_items( int $menu_id ): array {

		$items = [];

		$menu_items = wp_get_nav_menu_items( $menu_id );

		foreach ( (array) $menu_items as $key => $menu_item ) {
			$parent_item = get_post( (int) $menu_item->menu_item_parent );

			$items[] = [
				'db_id'         => $menu_item->ID,
				'title'         => $menu_item->title,
				'url'           => $menu_item->url,
				'parent_id'     => $menu_item->menu_item_parent,
				'parent_name'   => empty( $parent_item->post_title ) ? '' : $parent_item->post_title,
				'object_id'     => $menu_item->object_id,
				'object'        => $menu_item->object,
				'type'          => $menu_item->type,
				'type_label'    => $menu_item->type_label,
				'target'        => $menu_item->target,
				'attr_title'    => $menu_item->attr_title,
				'description'   => $menu_item->description,
				'classes'       => $menu_item->classes,
				'xfn'           => $menu_item->xfn,
				'status'        => $menu_item->post_status,
				'post_date'     => $menu_item->post_date,
				'post_date_gmt' => $menu_item->post_date_gmt,
				'position'      => $menu_item->menu_order,
			];
		}

		return $items;
	}

}
