<?php

namespace Ainsys\Connector\Master;

use Ainsys\Connector\Master\Settings\Settings;

class Conditions implements Hooked {

	/**
	 * Init hooks.
	 */
	public function init_hooks() { }


	public static function get_option_control( $entity ) {

		$controls = Settings::get_option( 'check_controlling_entity' );

		return ! empty( $controls[ $entity ] ) ? $controls[ $entity ]['general'] : [];
	}


	public static function get_option_control_on_off( $entity ) {

		return ! empty( self::get_option_control( $entity )['on_off'] ) ? self::get_option_control( $entity )['on_off'] : 0;
	}


	public static function get_option_control_create( $entity ) {

		return ! empty( self::get_option_control( $entity )['create'] ) ? self::get_option_control( $entity )['create'] : 0;
	}


	public static function get_option_control_read( $entity ) {

		return ! empty( self::get_option_control( $entity )['read'] ) ? self::get_option_control( $entity )['read'] : 0;
	}


	public static function get_option_control_update( $entity ) {

		return ! empty( self::get_option_control( $entity )['update'] ) ? self::get_option_control( $entity )['update'] : 0;
	}


	public static function get_option_control_delete( $entity ) {

		return ! empty( self::get_option_control( $entity )['delete'] ) ? self::get_option_control( $entity )['delete'] : 0;
	}


	public static function has_entity_disable( $entity, $action = '', $type = 'outgoing' ): bool {

		if ( empty( self::get_option_control( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $action,
					'request_type'    => $type,
					'request_data'    => '',
					'server_response' => serialize( 'Error: No url provided' ),
					'error'           => 1,
				]
			);

			return true;
		}

		if ( ! self::get_option_control_on_off( $entity ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $action,
					'request_type'    => $type,
					'request_data'    => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;
		}

		switch ( $action ) {
			case 'CREATE':
				if ( ( ! self::get_option_control_create( $entity ) ) ) {
					Logger::save(
						[
							'object_id'       => 0,
							'entity'          => $entity,
							'request_action'  => $action,
							'request_type'    => $type,
							'request_data'    => serialize( __( 'Error: Data transfer for creation is disabled in the settings', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
							'server_response' => serialize( __( 'Error: Data transfer for creation is disabled in the settings', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
							'error'           => 1,
						]
					);

					return true;

				}
				break;
			case 'CHECKING':
			case 'UPDATE':
				if ( ( ! self::get_option_control_update( $entity ) ) ) {
					Logger::save(
						[
							'object_id'       => 0,
							'entity'          => $entity,
							'request_action'  => $action,
							'request_type'    => $type,
							'request_data'    => serialize( __( 'Error: Data transfer for update is disabled in the settings', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
							'server_response' => serialize( __( 'Error: Data transfer for update is disabled in the settings', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
							'error'           => 1,
						]
					);

					return true;

				}
				break;
			case 'READ':
				if ( ( ! self::get_option_control_read( $entity ) ) ) {
					Logger::save(
						[
							'object_id'       => 0,
							'entity'          => $entity,
							'request_action'  => $action,
							'request_type'    => $type,
							'request_data'    => serialize( __( 'Error: Data transfer for read is disabled in the settings', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
							'server_response' => serialize( __( 'Error: Data transfer for read is disabled in the settings', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
							'error'           => 1,
						]
					);

					return true;

				}
				break;
			case 'DELETE':
				if ( ( ! self::get_option_control_delete( $entity ) ) ) {
					Logger::save(
						[
							'object_id'       => 0,
							'entity'          => $entity,
							'request_action'  => $action,
							'request_type'    => $type,
							'request_data'    => serialize( __( 'Error: Data transfer for delete is disabled in the setting', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
							'server_response' => serialize( __( 'Error: Data transfer for delete is disabled in the setting', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
							'error'           => 1,
						]
					);

					return true;

				}
				break;
		}

		return false;
	}

}