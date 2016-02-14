<?php
namespace RoxFramework\Model;

use RoxFramework\Filter;
use RoxFramework\RequestResult;
use RoxFramework\Model\Drivers;

class Entity {
	protected static $Name;
	protected static $FieldsList;

	
	public static function getName() { $entity = get_called_class(); return DB_PREFIX.$entity::$Name; }
	public static function getFieldsList() { $entity = get_called_class();  return (array)$entity::$FieldsList; }
	
	public function __construct($data) {
		$entity = get_called_class();

		foreach ($data as $field=>$value) {
			if (in_array($field, $entity::getFieldsList())) {
				$this->$field = $value;
			}
		}
	}

	public static function select($db, $where=array(), $order=array(), $offset=null, $length=null, $fetch_style=\PDO::FETCH_ASSOC) { //TODO: $fields
		global $log;
	
		$entity = get_called_class();

		try {
			$query = $db->prepareSelect($entity::getName(), '*', $where, $entity::getFieldsList(), $order, $offset, $length); //TODO: $fields
			if ($query instanceof RequestResult) {
				return $query;
			}
	
			if (!$result = $db->query($query)) {
				$dbError = $db->errorInfo();
				$error = __METHOD__ ." - Ha ocurrido un error tratando de realizar la siguiente consulta SQL: [{$query}]";
				$log->warn($error . " PDO Error: " . $dbError[2]);
				return new RequestResult(RequestResult::CODIGO_BBDD_SQL, $error, $dbError);
			} else if ($fetch_style === null) {
				return $result;
			} else {
				$entity = get_called_class();

				$entities = array();
				while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
					$entities[] = new $entity($row);
				}

				return $entities;
			}
		} catch (\PDOException $e) {
			$error = __METHOD__ ." - Ha ocurrido un error tratando de realizar la siguiente consulta SQL: [{$query}] PDOException: {$e->getMessage()}";
			$log->error($error);
			return new RequestResult(RequestResult::CODIGO_BBDD_QUERY, $error, $e);
		}
	}

	public function getNumericField($field, $precission=2, $mode=PHP_ROUND_HALF_UP) {
		if (empty($this->$field)) {
			return 0;
		} else {
			return round($this->$field, $precission, $mode);
		}
	}

	public function getDateField($field, $format='Y-m-d') {
		$date = new \DateTime($this->$field);
		return $date->format($format);
	}
}