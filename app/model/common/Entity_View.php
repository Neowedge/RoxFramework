<?php
namespace RoxFramework\Model;

use RoxFramework\Filter;
use RoxFramework\RequestResult;
use RoxFramework\Model\Drivers;

class Entity_View extends Entity {

	protected static $Tables;

	public static function getTables() { $entity = get_called_class();  return $entity::$Tables; }
}