<?php
namespace RoxFramework;

class Debug {
	public $message;

	public function __construct($message, $datos) {
		$this->message = $message;
		foreach ($datos as $clave=>$valor) {
			$this->$clave=$valor;
		}
	}
}
