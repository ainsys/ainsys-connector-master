<?php

namespace Ainsys\Connector\Master\WP\Prepare;

use Ainsys\Connector\Master\Helper;
use Ainsys\Connector\Master\WP\Process;

class Prepare_Taxonomies extends Process {

	public function get_prepare_tax( $taxonomy ): array {

		return [
			'id'          => $taxonomy->term_id . '_' . Helper::random_int( 78 ),
			'term_id'     => $taxonomy->term_id,
			'slug'        => $taxonomy->slug,
			'name'        => $taxonomy->name,
			'description' => $taxonomy->description,
			'parent'      => $taxonomy->parent === 0 ? '' : get_term( $taxonomy->parent )->name,
		];

	}


	protected function get_prepare_data_tax( $term_id, $taxonomy ): array {

		$tax = get_term_by( 'id', $term_id, $taxonomy );

		if ( ! $tax ) {
			return [];
		}

		return $this->get_prepare_tax( $tax );

	}


	/**
	 * Get product data for AINSYS
	 *
	 * @param  string $entity
	 * @param  object $process
	 *
	 * @return array
	 */
	public function get_tax_to_check( string $entity, object $process ): array {

		$taxes = get_terms( [
			'taxonomy'   => $entity,
			'hide_empty' => false,
		] );

		if ( empty( $taxes ) ) {
			return [
				'request'  => __( 'Error: There is no data to check.', AINSYS_CONNECTOR_TEXTDOMAIN ),
				'response' => __( 'Error: There is no data to check.', AINSYS_CONNECTOR_TEXTDOMAIN ),
			];
		}

		$taxes_ids = Helper::get_rand_array( wp_list_pluck( $taxes, 'term_id' ) );

		$tax_id = end( $taxes_ids );
		$tax    = wp_list_filter( $taxes, [ 'term_id' => $tax_id ] );

		return ( $process )->process_checking( $tax_id, array_shift( $tax ) );

	}

}