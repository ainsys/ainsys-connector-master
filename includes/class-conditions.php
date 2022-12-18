<?php

namespace Ainsys\Connector\Master\Conditions;

use Ainsys\Connector\Master\Logger;
use Ainsys\Connector\Master\Settings\Settings;

class Conditions {

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


	public static function has_entity_disable_create( $entity, $request_action = '' ): bool {

		if ( empty( self::get_option_control( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
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
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;
		}

		if ( ( ! self::get_option_control_create( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( __( 'Error: Data transfer is disabled', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is disabled', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;

		}

		return false;

	}


	public static function has_entity_disable_update( $entity, $request_action = '' ): bool {

		if ( empty( self::get_option_control( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
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
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;
		}

		if ( ( ! self::get_option_control_update( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( __( 'Error: Data transfer is disabled', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is disabled', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;

		}

		return false;
	}


	public static function has_entity_disable_read( $entity, $request_action = '' ): bool {

		if ( empty( self::get_option_control( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
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
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;
		}

		if ( ( ! self::get_option_control_read( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( __( 'Error: Data transfer is disabled', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is disabled', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;

		}

		return false;
	}


	public static function has_entity_disable_delete( $entity, $request_action = '' ): bool {

		if ( empty( self::get_option_control( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
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
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is completely disabled. Check the Entities export settings tab', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;
		}

		if ( ( ! self::get_option_control_delete( $entity ) ) ) {
			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $entity,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( __( 'Error: Data transfer is disabled', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'server_response' => serialize( __( 'Error: Data transfer is disabled', AINSYS_CONNECTOR_TEXTDOMAIN ) ),
					'error'           => 1,
				]
			);

			return true;

		}

		return false;
	}

}