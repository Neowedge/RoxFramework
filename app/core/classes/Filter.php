<?php
namespace RoxFramework;

class Filter {
	public $field;
	public $value;
	public $operator;

	public function __construct($field, $value, $operator='=') {
		$this->field = $field;
		$this->value = $value;
		$this->operator = $operator;
	}
}
