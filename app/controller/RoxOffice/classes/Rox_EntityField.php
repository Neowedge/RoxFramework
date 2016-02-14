<?php
namespace RoxOffice\Controllers;

class Rox_EntityField {

	public $name;
	public $title;
	public $type;
	public $format = null;
	public $entity = null;
	public $list = null;
	public $validators = null;
	public $converter = null;

	public $layoutFieldDetail = null;
	public $layoutFieldMaster = null;

	public function __construct($name, $title, $type, $format=null, $entity=null, $list=null, $params=array()) {
		$this->name = $name;
		$this->title = $title;
		$this->type = $type;
		$this->format = $format;
		$this->entity = $entity;
		$this->list = $list;

		$this->validators = isset($params['validators']) ? $params['validators'] : array();
		$this->converter = isset($params['converter']) ? $params['converter'] : null;
		$this->layoutFieldDetail = isset($params['layouts']['layoutDetail']) ? $params['layouts']['layoutDetail'] : null;
		$this->layoutFieldMaster = isset($params['layouts']['layoutMaster']) ? $params['layouts']['layoutMaster'] : null;

		foreach ($params as $param=>$value) {
			$this->$param = $value;
		}
	}

	public function getValuesList() {
		switch ($this->type) {
			case 'list':
				return $this->list;

			case 'entity':
				if ($this->list !== null) {
					return $this->list;
				}
				$dbClass = "RoxFramework\\Model\\Drivers\\".DB_DRIVER;
				
				if (!($db = $dbClass::connect()) instanceof RequestResult) {
					try {
						$entityManager = $this->entity['manager'];
						$entityValue = $this->entity['value'];
						$entityDisplay = explode(',', $this->entity['display']);
						$entityDisplayFormat = isset($this->entity['format']) ? $this->entity['format'] : null;
						$entities = $entityManager->call($entityManager->entityClass, 'select', array('db' => $db));

						$this->list = array();
						foreach ($entities as $entity) {
							$displays = array();
							foreach($entityDisplay as $display) {
								$displays[] = $entity->$display;
							}

							if ($entityDisplayFormat == null) {
								$this->list[$entity->$entityValue] = implode(',', $displays);
							} else {
								$this->list[$entity->$entityValue] = vsprintf($entityDisplayFormat, $displays);
							}
						}

						return $this->list;

					}  catch (\PDOException $e) {
						$log->warn($e->getMessage());
						//$resultado = new RequestResult(RequestResult::CODIGO_BBDD_TRANSACCION, $e->getMessage(), $e);
						return false;
					}
				} else {
					$log->warn($db->message);
					$resultado = $db;
					return false;
				
				}

			default:
				return false;
		}
	}

	public function getDisplay($value) {
		$values = explode(',', $value);
		$displays = array();
		foreach ($values as $value) {
			$value = trim($value);
			switch ($this->type) {
				case 'list':
					$displays[] = $this->list[$value];
					break;

				case 'entity':
					if ($this->list === null) {
						$this->getValuesList();
					}
					$displays[] = $this->list[$value];
					break;

				default:
					$displays[] = $this->getValue($value);
			}
		}

		if ($this->format === null) {
			return implode(',', $displays);
		}

		return vsprintf($this->format, $displays);
	}

	public function getValue($value) {
		if ($this->converter === null) {
			return $value;
		} else {
			$converterFunction = $this->converter['function-convert'];
			if (isset($this->converter['class'])) {
				$converterClass = $this->converter['class'];
				return call_user_func(array($converterClass, $converterFunction), $value);
			} else {
				return $converterFunction($value);
			}
		}
	}

	public function getDbValue($value) {
		if ($this->converter === null) {
			return $value;
		} else {
			$converterFunction = $this->converter['function-unconvert'];
			if (isset($this->converter['class'])) {
				$converterClass = $this->converter['class'];
				return call_user_func(array($converterClass, $converterFunction), $value);
			} else {
				return $converterFunction($value);
			}
		}
	}
}