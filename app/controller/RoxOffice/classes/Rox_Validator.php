<?php
namespace RoxOffice\Controllers;

use RoxFramework\Validator;

class Rox_Validator extends Validator {

	public static function isDate($value) {
		$date = explode('/', $value);
		if (count($date) != 3) {
			return false;
		}

		return checkdate($date[1], $date[0], $date[2]);
	}

	public static function isDateNull($value) {
		return $value === null || self::isDate($value);
	}
}