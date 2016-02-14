<?php
namespace RoxOffice\Controllers;

class Rox_Layout {
	public $filename;


	public function __construct($params) {
		if (!isset($params['filename'])) {
			throw new Exception('No se ha definido el nombre del archivo');
		}

		foreach ($params as $param=>$value) {
			$this->$param = $value;
		}
	}

	public function view($controller, $templateName='', $data) {
		$controller->view($templateName . $this->filename, $data);
	}
}
