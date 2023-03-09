<?php

namespace Ainsys\Connector\Master;

class Helper {

	public static function array_to_string( $array ): string {

		$str = '';
		foreach ( $array as $key => $value ) {

			$str .= "$key=$value|";

		}

		return $str;
	}

	public static function is_localhost( $whitelist = [ '127.0.0.1', '::1' ] ): bool {

		return in_array( $_SERVER['REMOTE_ADDR'], $whitelist, true );
	}

	/**
	 * @param      $post_type
	 * @param  int $count_output
	 *
	 * @return array
	 */
	public static function get_rand_posts( $post_type, int $count_output = 1 ): array {

		$args = [
			'post_type'              => $post_type,
			'posts_per_page'         => 50,
			'post_status'            => 'public',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return [];
		}

		$count = $count_output;

		if ( count( $posts ) < $count_output ) {
			$count = count( $posts );
		}

		if ( $count === count( $posts ) ) {
			-- $count;
		}

		return (array) array_rand( array_flip( $posts ), $count );
	}


	/**
	 * @param      $array
	 * @param  int $count_output
	 *
	 * @return array
	 */
	public static function get_rand_array( $array, int $count_output = 1 ): array {


		$count = $count_output;

		if ( count( $array ) < $count_output ) {
			$count = count( $array );
		}

		if ( $count === count( $array ) ) {
			-- $count;
		}

		return (array) array_rand( array_flip( $array ), $count );
	}


	public static function random_int($start_int = 0, $finish_int = 999999999999999){

		try {
			return random_int( $start_int, $finish_int );
		} catch ( \Exception $e ) {
			return  $e->getMessage();
		}
	}
}