<?php
namespace RoxFramework\Model;

use RoxFramework\Filter;
use RoxFramework\RequestResult;
use RoxFramework\Model\Drivers;

use RoxFramework\Model\Entity;

class Entity_Table extends Entity {	

	protected static $PKField;
	
	public static function getPKField() { $entity = get_called_class();  return $entity::$PKField; }

	public static function insert($db, $data) {
		global $log;
	
		$entity = get_called_class();

		try {
			$insertData = array();
			foreach ($data as $field=>$value) if (in_array($field, $entity::getFieldsList())) {
				$insertData[$field] = $value;
			}
			$query = $db->prepareInsert($entity::getName(), $insertData, (array)$entity::getFieldsList(), $entity::getPKField());
			if ($query instanceof RequestResult) {
				return $query;
			}

			if (!$result = $db->query($query)) {
				$dbError = $db->errorInfo();
				$error = __METHOD__ ." - Ha ocurrido un error tratando de realizar la siguiente consulta SQL: [{$query}]";
				$log->warn($error . " PDO Error: " . $dbError[2]);
	
				return new RequestResult(RequestResult::CODIGO_BBDD_SQL, $error, $dbError);
			} else {
				return $result;
			}
		} catch (\PDOException $e) {
			$error = "Medida::inserta() - Ha ocurrido un error tratando de realizar la siguiente consulta SQL: [{$query}] PDOException: {$e->getMessage()}";
			$log->error($error);
			return new RequestResult(RequestResult::CODIGO_BBDD_QUERY, $error, $e);
		}
	}


	public function update($db) {
		global $log;

		$entity = get_called_class();
	
		try {
			$entityPKField = $entity::$PKField;
			$data = array();
			foreach ($entity::getFieldsList() as $field) if ($field != $entityPKField) {
				$data[$field] = $this->$field;
			}

			$where = array(new Filter($entityPKField, $this->$entityPKField));
	
			$query = $db->prepareUpdate($entity::getName(), $data, $where, (array)$entity::getFieldsList());
			if ($query instanceof RequestResult) {
				return $query;
			}
	
			if (!$result = $db->query($query)) {
				$dbError = $db->errorInfo();
				$error = __METHOD__." - Ha ocurrido un error tratando de realizar la siguiente consulta SQL: [{$query}]";
				$log->warn($error . " PDO Error: " . $dbError[2]);
	
				return new RequestResult(RequestResult::CODIGO_BBDD_SQL, $error, $dbError);
			} else {
				return $result;
			}
		} catch (\PDOException $e) {
			$error = __METHOD__." - Ha ocurrido un error tratando de realizar la siguiente consulta SQL: [{$query}] PDOException: {$e->getMessage()}";
			$log->error($error);
			return new RequestResult(RequestResult::CODIGO_BBDD_QUERY, $error, $e);
		}
	}

	public function delete($db) {
		global $log;

		$entity = get_called_class();
	
		try {
			$entityPKField = $entity::$PKField;

			$where = array(new Filter($entityPKField, $this->$entityPKField));
	
			$query = $db->prepareDelete($entity::getName(), $where, (array)$entity::getFieldsList());
			if ($query instanceof RequestResult) {
				return $query;
			}
	
			if (!$result = $db->query($query)) {
				$dbError = $db->errorInfo();
				$error = __METHOD__." - Ha ocurrido un error tratando de realizar la siguiente consulta SQL: [{$query}]";
				$log->warn($error . " PDO Error: " . $dbError[2]);
	
				return new RequestResult(RequestResult::CODIGO_BBDD_SQL, $error, $dbError);
			} else {
				return $result;
			}
		} catch (\PDOException $e) {
			$error = __METHOD__." - Ha ocurrido un error tratando de realizar la siguiente consulta SQL: [{$query}] PDOException: {$e->getMessage()}";
			$log->error($error);
			return new RequestResult(RequestResult::CODIGO_BBDD_QUERY, $error, $e);
		}
	}
}