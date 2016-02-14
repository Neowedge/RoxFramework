<?php
namespace RoxFramework\Model\Drivers;

use RoxFramework\Filter;
use RoxFramework\RequestResult;

class PostgrePdoDriver extends \PDO {

	private $connectionString;
	
	public function __construct() {
		$this->connectionString = 'pgsql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME;
		parent::__construct($this->connectionString, DB_USER, DB_PASSWORD);
	}
	
	public function getConnectionString() {
		return $connectionString;
	}
	
	static function connect() {
		global $db, $log;
		
		try {
			if (!($db instanceof PostgrePdoDriver)) {
				$db = new PostgrePdoDriver();
			}
			
			return $db;
		} catch(\PDOException $e) {
			$log->fatal($e->getMessage());
			return new RequestResult(RequestResult::CODIGO_BBDD_CONEXION, $e->getMessage(), $e);
		}
	}

	public function getLastInsertId($insertResult) { //PDOStatement 
		$row = $insertResult->fetch();
		return $row[0];
	}
	
	public function prepareInsert($table, $data, $validFields=array(), $returning='') {		
		global $log;
		
		$table = DB_SCHEMA.".{$table}";
		
		$insert = array();
		foreach ($data as $fieldName => $fieldValue) {
			if (!empty($validFields) && !in_array($fieldName, $validFields)) {
				$error = "Se ha recibido el campo '{$fieldName}' con el valor '{$fieldValue}', pero esta columna no existe en la tabla '{$table}'";
				$log->warn($error);
				return new RequestResult(RequestResult::CODIGO_PARAMETROS_INVALIDOS, $error, array('campo' => $fieldName, 'camposValidos' => $validFields));
			} else {
				if ($fieldValue === null) {
					$insert[$fieldName] = "NULL";
				} else {
					$fieldValue = pg_escape_string($fieldValue);
					$insert[$fieldName] = "'{$fieldValue}'";
				}
			}
		}
		
		$fieldNames = implode(', ', array_keys($insert));
		$fieldValues = implode(', ', array_values($insert));
		
		$query = "INSERT INTO {$table} ({$fieldNames}) VALUES ({$fieldValues})";
		if (!empty($returning)) {
			$query .= " RETURNING {$returning}";
		}
		
		return $query;
	}
	
	public function prepareUpdate($table, $data, $filters, $validFields=array()) {
		global $log;
		
		$table = DB_SCHEMA.".{$table}";
		
		$update = array();
		foreach ($data as $fieldName => $fieldValue) {
			if (!empty($validFields) && !in_array($fieldName, $validFields)) {
				$error = "Se ha recibido el campo '{$fieldName}' con el valor '{$fieldValue}', pero esta columna no existe en la tabla '{$table}'";
				$log->warn($error);
				return new RequestResult(RequestResult::CODIGO_BBDD_CAMPOS_INVALIDOS, $error, array('campo' => $fieldName, 'camposValidos' => $validFields));
			} else {
				if ($fieldValue === null) {
					$update[] = "{$fieldName}=NULL";
				} else {
					$fieldValue = pg_escape_string($fieldValue);
					$update[] = "{$fieldName}='{$fieldValue}'";
				}
			}
		}

		$set = implode(', ', $update);
		if (($where = $this->prepareWhere($table, $filters, $validFields)) instanceof RequestResult) {
			return $where;
		}
		
		$query = "UPDATE {$table} SET {$set}{$where}";

		return $query;
	} 
	
	public function prepareDelete($table, $filters, $validFields=array()) {
		global $log;
		
		$table = DB_SCHEMA.".{$table}";
		
		if (($where = $this->prepareWhere($table, $filters, $validFields)) instanceof RequestResult) {
			return $where;
		}
		
		$query = "DELETE FROM {$table}{$where}";

		return $query;
	} 

	public function prepareSelect($table, $select='*', $filters=array(), $validFields=array(), $order=array(), $offset=null, $limit=null) {
		global $log;
		
		$table = DB_SCHEMA.".{$table}";
		
		if (($where = $this->prepareWhere($table, $filters, $validFields)) instanceof RequestResult) {
			return $where;
		}
		if (($orderby = $this->prepareOrderBy($table, $order, $validFields)) instanceof RequestResult) {
			return $orderby;
		}
		$offset = $offset === null ? '' : ' OFFSET '.$offset;
		$limit = $limit === null ? '' : ' LIMIT '.$limit;
		
		$query = "SELECT * FROM {$table}{$where}{$orderby}{$limit}{$offset}";
		
		return $query;
	}
	
	public function prepareWhere($table, $filters=array(), $validFields=array()) {
		global $log;
		
		if (empty($filters)) {
			$where = '';
		} else {
			$where = ' WHERE';
			$connector = '';
			$close = false;
			foreach ($filters as $filter) {
				if ($filter instanceof Filter) {
					if ($close) {
						$where .= "{$connector}";
						$close = false;
					}
					
					if (!empty($validFields) && !in_array($filter->field, $validFields)) {
						$error = "Se está intentando filtrar utilizando el campo '{$filter->field}', pero esta columna no existe en la tabla '{$table}'";
						$log->warn($error);
						return new RequestResult(RequestResult::CODIGO_BBDD_FILTER_CAMPOS, $error, array('campo' => $filter->field, 'camposValidos' => $validFields));
					} else {
						if (in_array($filter->operator, array('=', '!=', '<>', '<', '<=', '>', '>=', 'ILIKE', 'LIKE', 'NOT LIKE', 'NOT ILIKE'))) {
							if ($filter->value === null) {
								if (in_array($filter->operator, array('!=', '<>', '>', '>=', 'NOT LIKE', 'NOT ILIKE'))) {
									$where .= " {$connector}{$filter->field} IS NOT NULL";
								} else {
									$where .= " {$connector}{$filter->field} IS NULL";
								}
							} else {
								$value = pg_escape_string($filter->value);
								$value = "'{$value}'";
								$where .= " {$connector}{$filter->field} {$filter->operator} {$value}";
							}
						} elseif (in_array($filter->operator, array('IN', 'NOT IN'))) {
							$value = is_array($filter->value) ? implode(',', array_map(create_function('$value', 'return $value===null ? "NULL" : "\'".pg_escape_string($value)."\'";'), $filter->valor)) : "'".pg_escape_string($value)."'";
							$where .= " {$connector}{$filter->field} {$filter->operator} ({$value})";
						} elseif (in_array($filter->operator, array('BETWEEN', 'NOT BETWEEN')) && is_array($filter->value) && count($filter->value) == 2) {
							$value1 = pg_escape_string($filter->value[0]);
							$value1 = "'{$filter->value[0]}'";
							$value2 = pg_escape_string($filter->value[1]);
							$value2 = "'{$filter->value[1]}'";
							$where .= " {$connector}{$filter->field} {$filter->operator} $value1} AND {$value2}";
						} else {
							$error = "El operador '{$filter->operator}' no es uno de los operadores admitidos ['=' | '!=' | '<>' | '<']";
							$log->warn($error);
							return new RequestResult(RequestResult::CODIGO_BBDD_FILTER_OPERADOR, $error, $filter);
						}
						$connector = 'AND ';
					}
				} else if (in_array($filter, array('AND', 'OR'))) {
					if ($close) {
						$where .= "{$connector}";
						$close = false;
					}
					$connector = "{$filter} ";
				} else if ($filter == "(") {
					$where .= " {$connector}";
					$connector = "{$filter}";
				} else if ($filter == ")") {
					$connector = "{$filter} ";
					$close = true;
				} else {
					$error = "El filtro '{$filter}' no es un objeto de la clase 'Filter' ni uno de los conectores admitidos ['AND' | 'OR' | '(' | ')']";
					$log->warn($error);
					return new RequestResult(RequestResult::CODIGO_BBDD_FILTER, $error, array('filtro' => $filter));
				}
			}
				
			if ($close) {
				$where .= "{$connector}";
			}
		}
		
		return $where;
	}
	
	public function prepareOrderBy($table, $order, $validFields=array()) {
		global $log;
		
		if (empty($order)) {
			$orderby = '';
		} else {
			$orderby =  ' ORDER BY';
			$comma = '';
				
			foreach ($order as $ord) {
				$ord_vars = explode(' ', trim($ord));
				$count = count($ord_vars);
				if ($count < 0 || $count > 2) {
					$error = "El parámetro de ordenación recibido '{$ord_vars}' no tiene un formato adecuado";
					$log->warn($error);
					return new RequestResult(RequestResult::CODIGO_BBDD_ORDER, $error, $ord);
				} else if (!empty($validFields) && !in_array($ord_vars[0], $validFields)) {
					$error = "Se está intentando ordenar utilizando el campo '{$ord_vars[0]}' ('$ord'), pero esta columna no existe en la tabla '{$table}'";
					$log->warn($error);
					return new RequestResult(RequestResult::CODIGO_BBDD_ORDER_CAMPOS, $error, array('campo' => $ord_vars[0], 'camposValidos' => $validFields));
				} else if ($count == 2 && !in_array(strtoupper($ord_vars[1]), array('ASC', 'DESC'))) {
					$error = "La cláusula de ordenación '{$ord_vars[1]}' ('{$ord}') no es una cláusula válida ['ASC' | 'DESC']";
					$log->warn($error);
					return new RequestResult(RequestResult::CODIGO_BBDD_ORDER_CLAUSULA, $error, $ord);
				} else {
					$orderby .= "{$comma} {$ord}";
					$comma = ',';
				}
			}
		}
		
		return $orderby;
	}
}
