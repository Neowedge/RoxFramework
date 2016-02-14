<?php
namespace RoxOffice\Controllers;

class Rox_Template {
	public $name;
	protected $mainLayouts;

	public function __construct($name, $mainLayouts, $params=array()) {
		$this->name = $name;
		$this->mainLayouts = $mainLayouts;
		foreach ($params as $param=>$value) {
			$this->$param = $value;
		}
	}
	
	public function view($controller, $mainLayoutName, $params=array()) {
		$controller->view($this->name . $this->mainLayouts[$mainLayoutName]->filename, $params);
	}
	
	public function getLayout($layoutName) {
		if (isset($this->mainLayouts[$layoutName])) {
			return $this->mainLayouts[$layoutName];
		}

		return false;
	} 
}