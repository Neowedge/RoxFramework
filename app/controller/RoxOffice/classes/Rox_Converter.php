<?php
namespace RoxOffice\Controllers;

use RoxFramework\Converter;

class Rox_Converter extends Converter {
	
	//DATE
	static function convert_DbDate_To_Date($value) {
		$date = new \DateTime($value);
		return $date->format('d/m/Y');
	}

	static function convert_Date_To_DbDate($value) {
		$date = \DateTime::createFromFormat('d/m/Y', $value);
		return $date->format('Y-m-d');
	}
}