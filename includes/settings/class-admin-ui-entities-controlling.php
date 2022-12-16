<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;

class Admin_UI_Entities_Controlling implements Hooked {

	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {

		if ( ! is_admin() ) {
			return;
		}

		add_action( 'wp_ajax_save_entities_controlling', [ $this, 'save_entities_controlling' ] );
	}


	public static function columns_entities_controlling(): array {

		return apply_filters(
			'ainsys_columns_entities_controlling',
			[
				//'arrow'         => '',
				'entity'        => __( 'Entity', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'on_off'        => __( 'On/Off', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'create'        => __( 'Create', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'read'          => __( 'Read', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'update'        => __( 'Update', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'delete'        => __( 'Delete', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'last_exchange' => __( 'Last exchange', AINSYS_CONNECTOR_TEXTDOMAIN ),
				//'log'           => '',
			]
		);
	}


	/**
	 * Saves entities settings (for ajax).
	 */
	public function save_entities_controlling(): void {

		if ( empty( $_POST['entity'] ) ) {
			wp_send_json_error(
				[
					'error' => __( 'Entity ID is missing', AINSYS_CONNECTOR_TEXTDOMAIN ),
				]
			);
		}

		$entity = sanitize_text_field( $_POST['entity'] );
		$column = sanitize_text_field( $_POST['column'] );
		$value  = sanitize_text_field( $_POST['value'] );

		if ( empty( Settings::get_option( 'check_controlling_entity' ) ) ) {
			$result_entity = [];
			Settings::set_option( 'check_controlling_entity', $result_entity );
		}

		$result_entity = Settings::get_option( 'check_controlling_entity' );

		$result_entity[ $entity ]['general'][ $column ] = $value;
		$result_entity[ $entity ]['general']['time']    = current_time( 'mysql' );

		Settings::set_option( 'check_controlling_entity', $result_entity );

		wp_send_json( [
			'result'  => $result_entity,
			'value'   => $value,
			'message' => __( 'Data updated', AINSYS_CONNECTOR_TEXTDOMAIN ),
		] );

	}

}