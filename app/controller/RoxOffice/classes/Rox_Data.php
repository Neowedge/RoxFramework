<?php
namespace RoxOffice\Controllers;

class Rox_Data {
	public $value;
	public $display;

	public function __construct($value, $display) {
		$this->value = $value;
		$this->display = $display;
	}
}