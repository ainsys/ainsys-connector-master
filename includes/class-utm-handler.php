<?php

namespace Ainsys\Connector\Master;

class UTM_Handler {

	use Is_Singleton;

	public function __construct() {

		include_once __DIR__ . '/libs/UtmCookie.php';

		$utm_source = \UtmCookie\UtmCookie::get( 'utm_source' );

		if ( ! empty( $utm_source ) ) {
			$referer = self::get_referer_url();
			$host    = self::get_my_host_name();

			if ( isset( $referer, $host ) && $referer !== $host ) {
				\UtmCookie\UtmCookie::save( [ 'utm_source' => $referer ] );
			}
		}
	}


	/**
	 * Get user IP
	 *
	 * @return string
	 */
	public static function get_my_ip(): string {

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		//CloudFlare
		if ( $_SERVER['HTTP_CF_CONNECTING_IP'] ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		}

		return '';
	}


	/**
	 * Get referer url.
	 *
	 * @return string
	 */
	public static function get_referer_url(): string {

		$referer = false;

		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$referer = wp_parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST );
		}

		return $referer ? str_replace( 'www.', '', $referer ) : '';
	}


	/**
	 * Get my server addres
	 *
	 * @return string
	 */
	public static function get_my_host_name(): string {

		$host = false;

		if ( ! empty( $_SERVER['SERVER_NAME'] ) ) {
			$host = sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) );
		}

		return $host ? str_replace( 'www.', '', $host ) : '';
	}


	/**
	 * Get roistat attibute
	 *
	 * @return string
	 */
	public static function get_roistat(): string {

		return $_COOKIE['roistat_visit'] ?? '';
	}


	public static function get_user_agent(): string {

		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
	}

}