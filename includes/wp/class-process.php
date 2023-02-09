<?php

namespace Ainsys\Connector\Master\WP;

use Ainsys\Connector\Master\Core;
use Ainsys\Connector\Master\Logger;
use WP_Post;

class Process {

	protected static string $entity = '';

	protected static string $action = '';


	/**
	 * @param  int    $object_id
	 * @param  string $object_name
	 * @param  string $request_action
	 * @param         $fields
	 *
	 * @return array
	 */
	public function send_data( int $object_id, string $object_name, string $request_action, $fields ): array {

		if ( 'CHECKING' === $request_action ) {
			$action = 'UPDATE';
		} else {
			$action = $request_action;
		}

		$request_data = [
			'entity'  => [
				'id'   => $object_id,
				'name' => $object_name,
			],
			'action'  => $action,
			'payload' => $fields,
		];

		try {
			$server_response = Core::curl_exec_func( $request_data );
		} catch ( \Exception $e ) {
			$server_response = 'Error: ' . $e->getMessage();

			Logger::save(
				[
					'object_id'       => 0,
					'entity'          => $object_name,
					'request_action'  => $request_action,
					'request_type'    => 'outgoing',
					'request_data'    => serialize( $request_data ),
					'server_response' => serialize( $server_response ),
					'error'           => 1,
				]
			);

			Core::send_error_email( $server_response );
		}

		Logger::save(
			[
				'object_id'       => $object_id,
				'entity'          => $object_name,
				'request_action'  => $request_action,
				'request_type'    => 'outgoing',
				'request_data'    => serialize( $request_data ),
				'server_response' => serialize( $server_response ),
				'error'           => false !== strpos( $server_response, 'Error:' ),
			]
		);

		return [
			'request'  => $request_data,
			'response' => $server_response,
		];
	}


	/**
	 * @param  WP_Post $post Post object.
	 *
	 * @return array
	 */
	public function get_taxonomies( WP_Post $post ): array {

		$taxonomies     = [];
		$taxonomy_names = get_object_taxonomies( $post );

		foreach ( $taxonomy_names as $taxonomy_name ) {

			$terms = get_the_terms( $post, (string) $taxonomy_name );

			$taxonomies[ (string) $taxonomy_name ] = $terms;

		}

		return $taxonomies;
	}


	/**
	 * @param  int    $post_id
	 * @param  object $post Post object
	 * @param  bool   $update
	 *
	 * @return bool
	 */
	public function is_updated( int $post_id, object $post, bool $update ): bool {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Check if it is a REST Request
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		// Check if it is an autosave or a revision.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return false;
		}

		if ( ! $update ) {
			return false;
		}

		return true;
	}


	/**
	 * @param  bool $checking_connected
	 *
	 * @return string
	 */
	protected function get_update_action( bool $checking_connected ): string {

		return $checking_connected ? 'CHECKING' : 'UPDATE';
	}

}