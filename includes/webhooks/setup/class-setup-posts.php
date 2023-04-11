<?php

namespace Ainsys\Connector\Master\Webhooks\Setup;

class Setup_Posts {

	protected array $data;

	protected int $post_id;

	protected $post;


	public function __construct( $data ) {


		$this->data    = $data;
		$this->post_id = isset( $data['ID'] ) ? (int) $data['ID'] : 0;
		$this->post    = get_post( $this->post_id );
	}


	public function setup() {

		$post_data = $this->get_post_data();

		if ( 0 !== $this->post_id ) {
			$result = $this->update_post( $post_data );
		} else {
			$result = wp_insert_post( $post_data, true );
		}

		return $result;
	}


	protected function update_post( array $post_data ) {

		$result = $this->post_id;

		if ( $this->has_update() ) {
			$result = wp_update_post( $post_data, true );
		}

		return $result;
	}


	protected function has_update(): bool {

		$data = [];

		foreach ( $this->data as $key => $val ) {

			if ( in_array( $key, [ 'ID', 'post_type', 'post_modified', 'post_modified_gmt', 'post_link', 'comment_count' ], true ) ) {
				continue;
			}

			$data = $this->set_update_post_data( $key, $val, $data );

		}

		return in_array( 'yes', $data, true );

	}


	protected function set_update_post_data( string $key, $val, array $data ): array {

		$current_value = $this->post->$key;

		if ( $current_value === $val ) {
			$data[ $key ] = 'no';
		} else {
			$data[ $key ] = 'yes';
		}

		return $data;
	}


	public function get_post_data(): array {

		return [
			'ID'                => $this->post_id,
			'post_title'        => empty( $this->data['post_title'] ) ? '' : $this->data['post_title'],
			'post_content'      => empty( $this->data['post_content'] ) ? '' : $this->data['post_content'],
			'post_excerpt'      => empty( $this->data['post_excerpt'] ) ? '' : $this->data['post_excerpt'],
			'post_author'       => empty( $this->data['post_author'] ) ? '' : $this->data['post_author'],
			'post_status'       => empty( $this->data['post_status'] ) ? '' : $this->data['post_status'],
			'post_type'         => empty( $this->data['post_type'] ) ? '' : $this->data['post_type'],
			'post_date'         => empty( $this->data['post_date'] ) ? '' : $this->data['post_date'],
			'post_modified'     => empty( $this->data['post_modified'] ) ? '' : $this->data['post_modified'],
			'post_date_gmt'     => empty( $this->data['post_date_gmt'] ) ? '' : $this->data['post_date_gmt'],
			'post_modified_gmt' => empty( $this->data['post_modified_gmt'] ) ? '' : $this->data['post_modified_gmt'],
			'post_password'     => empty( $this->data['post_password'] ) ? '' : $this->data['post_password'],
			'post_parent'       => empty( $this->data['post_parent'] ) ? '' : $this->data['post_parent'],
			'menu_order'        => empty( $this->data['menu_order'] ) ? '' : $this->data['menu_order'],
			'post_name'         => empty( $this->data['post_name'] ) ? '' : $this->data['post_name'],
			'post_link'         => empty( $this->data['post_link'] ) ? '' : $this->data['post_link'],
			'comment_status'    => empty( $this->data['comment_status'] ) ? '' : $this->data['comment_status'],
			'comment_count'     => empty( $this->data['comment_count'] ) ? '' : $this->data['comment_count'],
		];
	}

}