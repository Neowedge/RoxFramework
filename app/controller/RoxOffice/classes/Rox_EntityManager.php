<?php
namespace RoxOffice\Controllers;

class Rox_EntityManager {

	public $title;
	public $entityClass;
	public $entityFields;
	public $entityMethods;

	public $layoutEntityDetail;
	public $layoutEntityMaster;

	public function __construct($params) {
		$this->title = $params['title'];
		$this->layoutEntityDetail 	= isset($params['layouts']['layoutDetail']) ? $params['layouts']['layoutDetail'] : null;
		$this->layoutEntityMaster 	= isset($params['layouts']['layoutMaster']) ? $params['layouts']['layoutMaster'] : null;

		$this->entityClass 			= isset($params['model']['class']) ? $params['model']['class'] : 'RoxFramwork\Model\Entity';
		$this->entityFields 		= isset($params['model']['fields']) ? $params['model']['fields'] : array();
		$this->entityMethods 		= isset($params['model']['methods']) ? $params['model']['methods'] : array();

		unset($params['layouts']);
		unset($params['model']);

		foreach ($params as $param=>$value) {
			$this->$param = $value;
		}
	}

	public function call($entity, $functionName, $args=array()) {
		global $log;

		if (empty($this->entityMethods[$functionName])) {
			$error = "El método '{$functionName}' no existe en la entidad '{$entity->name}'";
			$log->info($error);
			return new RequestResult(RequestResult::CODIGO_ROXOOFICCE_BADFUNCTION, $error, $entity);
		}
		$function = $this->entityMethods[$functionName]['function'];
		$functionArgs = isset($this->entityMethods[$functionName]['args']) ? $this->entityMethods[$functionName]['args'] : array();

		foreach ($functionArgs as $functionArgName=>$functionArgValue) {
			if (isset($args[$functionArgName]) && empty($args[$functionArgName])) {
				if (is_array($args[$functionArgName]) && is_array($functionArgValue)) {
					$args[$functionArgName] = array_merge($args[$functionArgName], $functionArgValue);
				}
			} else {
				$args[$functionArgName] = $functionArgValue;
			}
		}

		return call_user_func_array(array($entity, $function), $args);
	}
}