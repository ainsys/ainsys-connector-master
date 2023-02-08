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

}