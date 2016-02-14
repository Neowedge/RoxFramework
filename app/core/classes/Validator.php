<?php
namespace RoxFramework;

class Validator {
	
	public $comparer;
	
	public function __construct($comparer) {
		$this->comparer = $comparer;
	}
	
	public function validate($key, $value, $logLevel=null) {
		$function = $this->comparer[$key];
		if (call_user_func($function, $value)) {
			return $value;
		} else {
			global $log;
			if ($logLevel == null) {
				$logLevel = \LoggerLevel::getLevelInfo();
			}
			
			$error = "El valor '{$value}' no es válido para la key '{$key}'";
			$log->log($logLevel, $error);
			return new Debug($error, array('clave' => $key, 'valor' => $value, 'comprobacion' => is_array($function) ? array_pop($function) : $function));
		}
	}

	/* TODO: hacer que Validaciones devuelvan Debug en vez de false
	public function validate($key, $value, $logLevel=null) {
		$function = $this->comparer[$key];
		if (($resultado = call_user_func($function, $value)) instanceof Debug) {
			global $log;
			if ($logLevel == null) {
				$logLevel = \LoggerLevel::getLevelInfo();
			}
			//return new Debug($error, array('clave' => $key, 'valor' => $value, 'comprobacion' => is_array($function) ? array_pop($function) : $function));
			$error = "El valor '{$value}' no es válido para la key '{$key}'";
			$log->log($logLevel, $resultado);
		} else {
			return $value;
		}
	}*/
	
	public function validateAll($values, $logLevel=null) {
		//No hay parámetros obligatorios
		$validated = array();

		foreach ($this->comparer as $key=>$function) {
			if (key_exists($key, $values)) {
				$value = $values[$key];
			} else {
				$value = null;
			}
			
			if (call_user_func($function, $value)) {
				$validated[$key] = $value;
			} else {
				global $log;
				if ($logLevel == null) {
					$logLevel = \LoggerLevel::getLevelInfo();
				}
				
				$error = "El valor '{$value}' no es válido para la key '{$key}'";
				$log->log($logLevel, $error);
				$validated[$key] = new Debug($error, array('clave' => $key, 'valor' => $value, 'comprobacion' => is_array($function) ? array_pop($function) : $function));
			}
		}
		
		return $validated;
	}
	
	//VALIDADORES NO NULOS
	public static function isNotNull($value) {
		return $value !== null;
	}

	public static function isNotEmpty($value) {
		return !empty($value);
	}
	
	public static function isBool($value) {
		return in_array($value, array('0', '1'));
	}
	
	public static function isString($value) {
		return is_string($value);
	}
	
	public static function isNumeric($value) {
		return is_numeric($value);
	}
	
	public static function isInteger($value) {
		return $value == (string)intval($value);
	}
	
	public static function isUInteger($value) {
		return self::isInteger($value) && intval($value) >= 0;
	}
	
	public static function isTimestamp($value) {
		return self::isInteger($value);
	}
	
	public static function isCommaSeparatedStrings($value) {
		if (($value) === null) {
			return false;
		}
		
		foreach (explode(',', $value) as $var) {
			if (!self::isString($var)) {
				return false;
			}
		}
			
		return true;
	}
	
	public static function isCommaSeparatedIntegers($value) {
		if (($value) === null) {
			return false;
		}
		
		foreach (explode(',', $value) as $var) {
			if (!self::isInteger($var)) {
				return false;
			}
		}
			
		return true;
	}
	
	public static function isCommaSeparatedUIntegers($value) {
		if (($value) === null) {
			return false;
		}
	
		foreach (explode(',', $value) as $var) {
			if (!self::isInteger($var)) {
				return false;
			}
		}
			
		return true;
	}
	
	//VALIDADORES NULOS
	public static function isStringNull($value) {
		return $value === null || self::isString($value);
	}
	
	public static function isBoolNull($value) {
		return $value === null || self::isBool($value);
	}
	
	public static function isNumericNull($value) {
		return $value === null || self::isNumeric($value);
	}
	
	public static function isIntegerNull($value) {
		return $value === null || self::isInteger($value);
	}
	
	public static function isUIntegerNull($value) {
		return $value === null || self::isUInteger($value);
	}
	
	public static function isTimestampNull($value) {
		return $value === null || self::isTimestamp($value);
	}
	
	public static function isCommaSeparatedStringsNull($value) {
		if ($value === null) {
			return true;
		}
		
		foreach (explode(',', $value) as $var) {
			if (!self::isString($var)) {
				return false;
			}
		}
			
		return true;
	}
	
	public static function isCommaSeparatedIntegersNull($value) {
		if ($value === null) {
			return true;
		}
		
		foreach (explode(',', $value) as $var) {
			if (!self::isInteger($var)) {
				return false;
			}
		}
				
		return true;
	}
	
	public static function isCommaSeparatedUIntegersNull($value) {
		if ($value === null) {
			return true;
		}  
		
		foreach (explode(',', $value) as $var) {
			if (!self::isUInteger($var)) {
				return false;
			}
		}
	
		return true;
	}

	//VALIDADORES ESPECIALES
	public static function isEmail($value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}
	
	public static function isJson($value) {
		return is_string($value) && json_decode($value) !== null;
	}
	
	public static function isJsonNull($value) {
		return $value === null || self::isJson($value);
	}
	
	public static function isIp($value) {
		return filter_var($value, FILTER_VALIDATE_IP);
	}
	
	public static function isIpNull($value) {
		return $value === null || self::isIp($value);
	}
	
	public static function oneValueRestNull($values) {
		$one = false;
		
		foreach ($values as $value) {
			if ($value !== null) {
				if (!$one) {
					$one = true;
				} else {
					return false;
				}
			}
		}
		
		return $one;
	}
}
