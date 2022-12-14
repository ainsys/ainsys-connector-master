<?php

namespace Ainsys\Connector\Master\Settings;

use Ainsys\Connector\Master\Hooked;
use Ainsys\Connector\Master\Logger;

class Admin_UI_Entities_Controlling implements Hooked {

	protected Admin_UI $admin_ui;


	public function __construct( Admin_UI $admin_ui ) {

		if ( ! is_admin() ) {
			return;
		}

		$this->admin_ui = $admin_ui;

	}


	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {

		add_action( 'wp_ajax_save_entity_settings', [ $this, 'save_entities_settings' ] );
		add_action( 'wp_ajax_save_entities_controlling', [ $this, 'save_entities_controlling' ] );
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
		$result_entity[ $entity ]['general'][ 'time' ] = current_time( 'mysql' );

		Settings::set_option( 'check_controlling_entity', $result_entity );

		wp_send_json([
			'result' => $result_entity,
			'value' => $value,
			'message' => __( 'Data updated', AINSYS_CONNECTOR_TEXTDOMAIN )
		]);

	}


	/**
	 * Saves entities settings (for ajax).
	 */
	public function save_entities_settings(): void {

		if ( ! isset( $_POST['action'], $_POST['nonce'] ) && ! wp_verify_nonce( $_POST['nonce'], self::$nonce_title ) ) {
			wp_die( 'Missing nonce' );
		}

		$fields = $_POST;

		$entity      = isset( $_POST['entity'] ) ? sanitize_text_field( $_POST['entity'] ) : '';
		$seting_name = $_POST['seting_name'] ? sanitize_text_field( $_POST['seting_name'] ) : '';

		if ( ! $entity && ! $seting_name ) {
			wp_die( 'Missing entity or setting_name' );
		}

		$fields = $this->sanitise_fields_to_save( $fields );

		global $wpdb;

		$entity_saved_settings = Settings::get_saved_entity_settings_from_db(
			sprintf(
				' WHERE entity="%s" setting_key="saved_field" AND setting_name="%s"',
				esc_sql( $entity ),
				esc_sql( $seting_name )
			)
		);

		if ( empty( $entity_saved_settings ) ) {
			$wpdb->insert(
				$wpdb->prefix . Settings::$ainsys_entities_settings_table,
				[
					'entity'       => $entity,
					'setting_name' => $seting_name,
					'setting_key'  => 'saved_field',
					'value'        => serialize( $fields ),
				]
			);

			$field_data_id = $wpdb->insert_id;
		} else {
			$wpdb->update(
				$wpdb->prefix . Settings::$ainsys_entities_settings_table,
				[ 'value' => serialize( $fields ) ],
				[ 'id' => $entity_saved_settings['id'] ]
			);

			$field_data_id = $entity_saved_settings['id'];
		}

		$request_action = 'field/' . $entity . '/' . $seting_name;

		$fields = apply_filters( 'ainsys_update_entity_fields', $fields );

		$request_data = [
			'entity'  => [
				'id' => $field_data_id,
			],
			'action'  => $request_action,
			'payload' => $fields,
		];

		try {
			$server_response = $this->core->curl_exec_func( $request_data );
		} catch ( \Exception $e ) {
			$server_response = 'Error: ' . $e->getMessage();
		}

		$this->logger::save_log_information(
			(int) $field_data_id,
			$request_action,
			serialize( $request_data ),
			serialize( $server_response ),
			0
		);

		echo $field_data_id ?? 0;

		wp_die();

	}


	/**
	 * Prepares fields to save to DB (for ajax).
	 *
	 * @param  array $fields
	 *
	 * @return array
	 */
	public function sanitise_fields_to_save( $fields ) {

		unset( $fields['action'], $fields['entity'], $fields['nonce'], $fields['seting_name'], $fields['id'] );

		/// exclude 'constant' variables
		foreach ( Settings::get_entities_settings() as $item => $setting ) {
			if ( isset( $fields[ $item ] ) && 'constant' === $setting['type'] ) {
				unset( $fields[ $item ] );
			}
		}

		return $fields;
	}


	public function columns_entities_controlling(): array {

		return apply_filters(
			'columns_entities_controlling',
			[
				'arrow'         => '',
				'entity'         => __( 'Entity', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'on_off'         => __( 'On/Off', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'create'         => __( 'Create', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'read'           => __( 'Read', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'update'         => __( 'Update', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'delete'         => __( 'Delete', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'last_exchange' => __( 'Last exchange', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'log'            => '',
			]
		);
	}


	/**
	 * Generates entities HTML placeholder.
	 *
	 * @return string
	 */
	public function generate_entities_html() {

		$entities_html       = '';
		$collapsed           = '';
		$collapsed_text      = '';
		$first_active        = '';
		$inner_fields_header = '';

		$entities_list = Settings::get_entities();

		$properties = Settings::get_entities_settings();

		foreach ( $properties as $item => $settings ) {
			$checker_property = ( 'bool' === $settings['type'] || 'api' === $item ) ? 'small_property' : '';

			$inner_fields_header .= sprintf(
				'<div class="properties_field_title %s">%s</div>',
				$checker_property,
				$settings['nice_name']
			);
		}

		foreach ( $entities_list as $entity => $title ) {

			$properties = Settings::get_entities_settings( $entity );

			$entities_html .= '<div class="entities_block">';

			$get_fields_functions = Settings::get_entity_fields_handlers();

			$section_fields = [];
			$fields_getter  = $get_fields_functions[ $entity ];
			if ( is_callable( $fields_getter ) ) {
				$section_fields = $fields_getter();
			} else {
				throw new \Exception( 'No fields getter registered for Entity: ' . $entity );
			}

			if ( ! empty( $section_fields ) ) {
				$collapsed      = $collapsed ? ' ' : ' active';
				$collapsed_text = $collapsed_text ? 'expand' : 'collapse';
				$entities_html  .= '<div class="entity_data ' . $entity . '_data' . $collapsed . '"> ';

				$entities_html .= '<div class="entity_block_header"><div class="entity_title">'
				                  . $title
				                  . '</div>'
				                  . $inner_fields_header
				                  . '<a class="button expand_entity_container">'
				                  . $collapsed_text
				                  . '</a></div>';
				foreach ( $section_fields as $field_slug => $field_content ) {
					$first_active          = $first_active ? ' ' : ' active';
					$field_name            = empty( $field_content['nice_name'] ) ? $field_slug : $field_content['nice_name'];
					$entity_saved_settings = array_merge(
						$field_content, Settings::get_saved_entity_settings_from_db( ' WHERE entity="' . $entity . '" AND setting_name="' . $field_slug . '"' )
					);

					if ( ! empty( $field_content['children'] ) ) {

						$data_fields = 'data-seting_name="' . esc_html( $field_slug ) . '" data-entity="' . esc_html( $entity ) . '"';
						foreach ( $properties as $name => $prop_val ) {
							$prop_val_out = 'id' === $name ? $field_slug : $this->get_property( $name, $prop_val, $entity_saved_settings );
							$data_fields  .= 'data-' . $name . '="' . esc_html( $prop_val_out ) . '" ';
						}
						$entities_html .= '<div id="'
						                  . $field_slug
						                  . '" class="entities_field multiple_filds '
						                  . $first_active
						                  . '" '
						                  . $data_fields
						                  . '><div class="entities_field_header"><i class="fa fa-sort-desc" aria-hidden="true"></i>'
						                  . $field_name
						                  . '</div>'
						                  . $this->generate_inner_fields( $properties, $entity_saved_settings, $field_slug )
						                  . '<i class="fa fa-floppy-o"></i><div class="loader_dual_ring"></div></div>';

						foreach ( $field_content['children'] as $inner_field_slug => $inner_field_content ) {
							$field_name            = empty( $inner_field_content['description'] ) ? $inner_field_slug : $inner_field_content['discription'];
							$field_slug_inner      = $field_slug . '_' . $inner_field_slug;
							$entity_saved_settings = array_merge(
								$field_content, Settings::get_saved_entity_settings_from_db( ' WHERE entity="' . $entity . '" AND setting_name="' . $field_slug_inner . '"' )
							);

							$data_fields = 'data-seting_name="' . esc_html( $field_slug ) . '" data-entity="' . esc_html( $entity ) . '"';
							foreach ( $properties as $name => $prop_val ) {
								$prop_val_out = 'id' === $name ? $field_slug_inner : $this->get_property( $name, $prop_val, $entity_saved_settings );
								$data_fields  .= 'data-' . $name . '="' . esc_html( $prop_val_out ) . '" ';
							}
							/*$entities_html .= '<div id="'
							                  . $entity
							                  . '_'
							                  . $inner_field_slug
							                  . '" class="entities_field multiple_filds_children '
							                  . $first_active
							                  . '" '
							                  . $data_fields
							                  . '><div class="entities_field_header"><i class="fa fa-angle-right" aria-hidden="true"></i>'
							                  . $field_name
							                  . '</div>'
							                  . $this->generate_inner_fields( $properties, $entity_saved_settings, $field_slug )
							                  . '<i class="fa fa-floppy-o"></i><div class="loader_dual_ring"></div></div>';*/
						}
					} else {
						$data_fields = 'data-seting_name="' . esc_html( $field_slug ) . '" data-entity="' . esc_html( $entity ) . '"';
						foreach ( $properties as $name => $prop_val ) {
							$prop_val_out = $this->get_property( $name, $prop_val, $entity_saved_settings );
							$data_fields  .= 'data-' . $name . '="' . esc_html( $prop_val_out ) . '" ';
						}
						/*$entities_html .= '<div id="'
						                  . $field_slug
						                  . '" class="entities_field '
						                  . $first_active
						                  . '" '
						                  . $data_fields
						                  . '><div class="entities_field_header">'
						                  . $field_name
						                  . '</div>'
						                  . $this->generate_inner_fields( $properties, $entity_saved_settings, $field_slug )
						                  . '<i class="fa fa-floppy-o"></i><div class="loader_dual_ring"></div></div>';*/
					}
				}
				/// close //// div class="entity_data"
				$entities_html .= '</div>';
			}
			/// close //// div class="entities_block"
			$entities_html .= '</div>';
		}

		return '<div class="entitys_table">' . $entities_html . '</div>';

	}


	/**
	 * Gets a property from an array.
	 *
	 * @param  string $name
	 * @param  mixed  $prop_val
	 * @param  array  $entity_saved_settings
	 *
	 * @return string
	 */
	public function get_property( string $name, $prop_val, array $entity_saved_settings ): string {

		if ( is_array( $prop_val['default'] ) ) {
			return $entity_saved_settings[ strtolower( $name ) ] ?? array_search( '1', $prop_val['default'], true );
		}

		return $entity_saved_settings[ strtolower( $name ) ] ?? $prop_val['default'];
	}


	/**
	 * Generates properties for entity field.
	 *
	 * @param  array  $properties
	 * @param  array  $entity_saved_settings
	 * @param  string $field_slug
	 *
	 * @return string
	 */
	public function generate_inner_fields( $properties, $entity_saved_settings, $field_slug ) {

		$inner_fields = '';
		if ( empty( $properties ) ) {
			return '';
		}

		foreach ( $properties as $item => $settings ) {
			$checker_property = 'bool' === $settings['type'] || 'api' === $item ? 'small_property' : '';
			$inner_fields     .= '<div class="properties_field ' . $checker_property . '">';
			$field_value      = 'id' === $item ? $field_slug : $this->get_property( $item, $settings, $entity_saved_settings );
			switch ( $settings['type'] ) {
				case 'constant':
					$field_value  = $field_value ? $field_value : '<i>' . __( 'empty', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</i>'; // phpcs:ignore
					$inner_fields .= 'api' === $item ? '<div class="entity_settings_value constant ' . $field_value . '"></div>' :
						'<div class="entity_settings_value constant">' . $field_value . '</div>';
					break;
				case 'bool':
					$checked      = (int) $field_value ? 'checked="" value="1"' : ' value="0"';
					$checked_text = (int) $field_value ? __( 'On', AINSYS_CONNECTOR_TEXTDOMAIN ) : __( 'Off', AINSYS_CONNECTOR_TEXTDOMAIN ); // phpcs:ignore
					$inner_fields .= '<input type="checkbox"  class="editor_mode entity_settings_value " id="' . $item . '" ' . $checked . '/> ';
					$inner_fields .= '<div class="entity_settings_value">' . $checked_text . '</div> ';
					break;
				case 'int':
					$inner_fields .= '<input size="10" type="text"  class="editor_mode entity_settings_value" id="' . $item . '" value="' . $field_value . '"/> ';
					$field_value  = $field_value ? $field_value : '<i>' . __( 'empty', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</i>'; // phpcs:ignore
					$inner_fields .= '<div class="entity_settings_value">' . $field_value . '</div>';
					break;
				case 'select':
					$inner_fields .= '<select id="' . $item . '" class="editor_mode entity_settings_value" name="' . $item . '">';
					$state_text   = '';
					foreach ( $settings['default'] as $option => $state ) {
						$selected     = $option === $field_value ? 'selected="selected"' : '';
						$state_text   = $option === $field_value ? $option : $state_text;
						$inner_fields .= '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
					}
					$inner_fields .= '</select>';
					$inner_fields .= '<div class="entity_settings_value">' . $field_value . '</div>';
					break;
				default:
					$field_length = 'description' === $item ? 20 : 8;
					$inner_fields .= '<input size="' . $field_length . '" type="text" class="editor_mode entity_settings_value" id="' . $item . '" value="' . $field_value . '"/>';
					$field_value  = $field_value ? $field_value : '<i>' . __( 'empty', AINSYS_CONNECTOR_TEXTDOMAIN ) . '</i>'; // phpcs:ignore
					$inner_fields .= '<div class="entity_settings_value">' . $field_value . '</div>';
			}
			/// close //// div class="properties_field"
			$inner_fields .= '</div>';
		}

		return $inner_fields;
	}

}