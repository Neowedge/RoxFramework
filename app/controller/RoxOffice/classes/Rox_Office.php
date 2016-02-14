<?php
namespace RoxOffice\Controllers;

class Rox_Office {

	public $template;
	public $entities;

	public function __construct($template, $entities, $params) {
		$this->template = $template;
		$this->entities = $entities;
		foreach ($params as $param=>$value) {
			$this->$param = $value;
		}
	}

	public function getPage($pageName) {
		return $this->pages[$pageName];
	}
}