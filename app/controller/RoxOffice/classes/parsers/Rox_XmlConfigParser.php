<?php
namespace RoxOffice\Controllers;

class Rox_XmlConfigParser implements Rox_IConfigParser {

	static $objects = array(
		'layouts' => array(),
		'entitiesModel' => array()
		);

	static function parse($fileName, $officeName) {
		if (!file_exists($fileName)) {
			return false;
		}

		$xml = simplexml_load_file($fileName);
		$xmlOffice = $xml->offices->xpath('office[@name="'.$officeName.'"]');
		if (empty($xmlOffice)) {
			return false;
		}

		$office = self::parseOffice($xmlOffice[0], $xml);
		if (!$office) {
			return false;
		}

		return new Rox_Cms($office);
	}

	static function toFile(Rox_Cms $cms) {
		return false;
	}

	static function iterateEntities($xmlEntities, $xml) {
		$entities=array();

		foreach ($xmlEntities as $xmlEntity) {
			if ($xmlEntity->getName() == 'entityGroup') {
				$entities[(string)$xmlEntity['name']] = array(
					'title' => (string)$xmlEntity['title'],
					'entities' => self::iterateEntities($xmlEntity->xpath('entityRef|entityGroup'), $xml));
			} elseif ($xmlEntity->getName() == 'entityRef') {
				$entities[(string)$xmlEntity['name']] = self::parseEntity($xmlEntity, $xml);
			}
		}

		return $entities;
	}

	static function parseOffice($xmlOffice, $xml) {
		foreach ($xmlOffice->xpath('include') as $include) {
			$dir = (string)$include['dir'];
			if (!empty($dir)) {
				$dir = constant($dir);
			}
			include($dir . (string)$include);
		}
		if (!empty($xmlOffice['class'])) {
			$officeClass = (string)$xmlOffice['class'];
		} else {
			$officeClass = 'RoxOffice\Controllers\Rox_Office';
		}

		$xmlTemplateRef = $xmlOffice->xpath('templateRef');

		if (empty($xmlTemplateRef)) {
			return false;
		}
		$xmlTemplate = $xml->templates->xpath('template[@name="'.(string)$xmlTemplateRef[0]['ref'].'"]');
		if (empty($xmlTemplate)) {
			return false;
		}

		$template = self::parseTemplate($xmlTemplate[0], $xml);
		$entities = self::iterateEntities($xmlOffice->xpath('entityRef|entityGroup'), $xml);

		$params = array();
		$xmlParams = (array)$xmlOffice->xpath('param');
		foreach ($xmlParams as $xmlParam) {
			$params[(string)$xmlParam['name']] = (string)$xmlParam['value'];
		}

		$office = new $officeClass($template, $entities, $params);
		return $office;
	}

	static function parseEntity($xmlEntity, $xml) {

		$params = array();
		$params['title'] = (string)$xmlEntity['title'];
		$params['model'] = array();
		$params['layouts'] = array();

		if (!empty($xmlEntity['class'])) {
			$entityClass = (string)$xmlEntity['class'];
		} else {
			$entityClass = 'RoxOffice\Controllers\Rox_EntityManager';
		}

		$xmlLayoutsRef = (array)$xmlEntity->xpath('layoutRef');
		$xmlParams = (array)$xmlEntity->xpath('param');

		$xmlEntityModel = $xml->entities->xpath('entity[@name="'.(string)$xmlEntity['ref'].'"]');
		foreach(self::parseEntityModel($xmlEntityModel[0], $xml) as $param=>$value) {
			$params['model'][$param] = $value;
		}

		foreach ($xmlLayoutsRef as $xmlLayoutRef) {
			$xmlLayout = $xml->layouts->xpath('layout[@name="'.(string)$xmlLayoutRef['ref'].'"]');
			if (!empty($xmlLayout)) {
				$params['layouts'][(string)$xmlLayoutRef['name']] = self::parseLayout($xmlLayout[0], $xml);
			}
		}

		foreach ($xmlParams as $xmlParam) {
			$params[(string)$xmlParam['name']] = (string)$xmlParam['value'];
		}

		$entity = new $entityClass($params);

		return $entity;
	}

	static function parseEntityModel($xmlEntityModel, $xml) {

		if (isset(self::$objects['entitiesModel'][(string)$xmlEntityModel['name']])) {
			return self::$objects['entitiesModel'][(string)$xmlEntityModel['name']];
		}

		$xmlMethods = (array)$xmlEntityModel->xpath('method');
		$xmlFields = (array)$xmlEntityModel->xpath('field');
		$xmlParams = (array)$xmlEntityModel->xpath('param');

		$entityModel = array();
		$entityModel['class'] = (string)$xmlEntityModel['class'];
		$entityModel['methods'] = array();
		$entityModel['fields'] = array();

		foreach ($xmlMethods as $xmlMethod) {
			$xmlArgs = $xmlMethod->xpath('arg');

			$entityModel['methods'][(string)$xmlMethod['name']] = array();
			$entityModel['methods'][(string)$xmlMethod['name']]['function'] = (string)$xmlMethod['function'];
			if (!empty($xmlArgs)) {
				$entityModel['methods'][(string)$xmlMethod['name']]['args'] = self::iterateArgs($xmlArgs, $xml);
			}
			
		}

		foreach ($xmlFields as $xmlField) {
			if (!empty($xmlField['class'])) {
				$fieldClass = (string)$xmlField['class'];
			} else {
				$fieldClass = 'RoxOffice\Controllers\Rox_EntityField';
			}
			
			$name = (string)$xmlField['name'];
			$title = (string)$xmlField['title'];
			$type = (string)$xmlField['type'];
			$format = empty($xmlField['format']) ? null : $xmlField['format'];
			$entity = null;
			$list = null;
			$fieldParams = array();
			
			$xmlLayoutsRef = (array)$xmlField->xpath('layoutRef');
			$xmlValidatorsRef = (array)$xmlField->xpath('validatorRef');
			$xmlConverterRef = (array)$xmlField->xpath('converterRef');

			foreach ($xmlLayoutsRef as $xmlLayoutRef) {
				$xmlLayout = $xml->layouts->xpath('layout[@name="'.(string)$xmlLayoutRef['ref'].'"]');
				if (!empty($xmlLayout)) {
					$fieldParams['layouts'][(string)$xmlLayoutRef['name']] = self::parseLayout($xmlLayout[0], $xml);
				}
			}

			foreach ($xmlValidatorsRef as $xmlValidatorRef) {
				$xmlValidator = $xml->validators->xpath('validator[@name="'.(string)$xmlValidatorRef['ref'].'"]');
				if (!empty($xmlValidator)) {
					$validator = array(
						'class'		=> (string)$xmlValidator[0]['class'],
						'function'	=> (string)$xmlValidator[0]['function']
					);

					if (isset($xmlValidator[0]['error'])) {
						$validator['error'] = (string)$xmlValidator[0]['error'];
					}

					$fieldParams['validators'][] = $validator;
				}
			}

			if (!empty($xmlConverterRef)) {
				$xmlConverter = $xml->converters->xpath('converter[@name="'.(string)$xmlConverterRef[0]['ref'].'"]');
				if (!empty($xmlConverter)) {
					$fieldParams['converter'] = array(
						'class'					=> (string)$xmlConverter[0]['class'],
						'function-convert'		=> (string)$xmlConverter[0]['function-convert'],
						'function-unconvert'	=> (string)$xmlConverter[0]['function-unconvert']
					);
				}
			}

			if ($type == 'entity') {
				$entity = array();

				$xmlEntityRef = $xmlField->xpath('entityRef');
				$entity['manager'] = self::parseEntity($xmlEntityRef[0], $xml);
				$entity['display'] = (string)$xmlEntityRef[0]['display'];
				$entity['value'] = (string)$xmlEntityRef[0]['value'];
				if (isset($xmlEntityRef[0]['format'])) {
					$entity['format'] = (string)$xmlEntityRef[0]['format'];
				}
				

			} elseif ($type=='list') {
				$list = array();

				$xmlList = $xmlField->xpath('list');
				foreach ($xmlList as $xmlElement) {
					$value = (string)$xmlElement['value'];
					$list[$value] = (string)$xmlElement['display'];
				}
			}

			$xmlFieldParams = (array)$xmlField->xpath('param');
			foreach ($xmlFieldParams as $xmlFieldParam) {
				$fieldParams[(string)$xmlFieldParam['name']] = (string)$xmlFieldParam['value'];
			}

			$entityModel['fields'][(string)$xmlField['name']] = new $fieldClass($name, $title, $type, $format, $entity, $list, $fieldParams);
		}

		foreach ($xmlParams as $xmlParam) {
			$entityModel[(string)$xmlParam['name']] = (string)$xmlParam['value'];
		}

		self::$objects['entitiesModel'][(string)$xmlEntityModel['name']] = $entityModel;

		return $entityModel;
	}

	static function iterateArgs($xmlArgs, $xml) {
		$args = array();

		foreach($xmlArgs as $xmlArg) {
			if (!empty($xmlArg['class']) && $xmlArg['class'] == 'array') {
				$xmlArray = $xmlArg->xpath('arg');
				if (!empty($xmlArray)) {
					$args[(string)$xmlArg['name']] = self::iterateArgs($xmlArray, $xml);
				} else {
					$args[(string)$xmlArg['name']] = array();
				}
			} elseif (!empty($xmlArg['class'])) {
				$argClass = (string)$xmlArg['class'];
			
				$xmlVars = (array)$xmlArg->xpath('var');
				if (!empty($xmlVars)) {
					$vars = array();
					foreach ($xmlVars as $xmlVar) {
						//$varName = (string)$xmlVar['name'];
						$vars[] = (string)$xmlVar['value'];
					}
					$reflection = new \ReflectionClass($argClass);
					$args[(string)$xmlArg['name']] = $reflection->newInstanceArgs($vars);
				} else {
					$args[(string)$xmlArg['name']] = new $argClass();
				}

			} elseif (!empty($xmlArg['call'])) {
				$paramCall = (string)$xmlArg['call'];
				
				$xmlVars = (array)$xmlEntityModel->xpath('var');
				if (!empty($xmlVars)) {
					$vars = array();
					foreach ($xmlVars as $xmlVar) {
						$vars[(string)$xmlVar['name']] = (string)$xmlVar['value'];
					}

					$args[(string)$xmlArg['name']] = call_user_func_array($paramCall, $vars);
				} else {
					$args[(string)$xmlArg['name']] = call_user_func($paramCall);
				}

			} elseif (!empty($xmlArg['value'])) {
				$args[(string)$xmlArg['name']]  = (string)$xmlArg['value'];
			}
		}

		return $args;
	}

	static function parseLayout($xmlLayout, $xml) {

		if (isset(self::$objects['layouts'][(string)$xmlLayout['name']])) {
			return self::$objects['layouts'][(string)$xmlLayout['name']];
		}

		if (!empty($xmlLayout['class'])) {
			$layoutClass = (string)$xmlLayout['class'];
		} else {
			$layoutClass = 'RoxOffice\Controllers\Rox_layout';
		}

		$xmlParams = (array)$xmlLayout->xpath('param');
		foreach ($xmlParams as $xmlParam) {
			$params[(string)$xmlParam['name']] = (string)$xmlParam['value'];
		}

		$layout = new $layoutClass($params);

		return $layout;
	}

	static function parseTemplate($xmlTemplate, $xml) {
		$templateName = (string)$xmlTemplate['name'];
		$templateClass = (string)$xmlTemplate['class'];
		$layouts = array();
		$params = array();

			
		$xmlLayoutsRef = (array)$xmlTemplate->xpath('layoutRef');
		foreach ($xmlLayoutsRef as $xmlLayoutRef) {
			$xmlLayout = $xml->layouts->xpath('layout[@name="'.(string)$xmlLayoutRef['ref'].'"]');
			if (!empty($xmlLayout)) {
				$layouts[(string)$xmlLayoutRef['name']] = self::parseLayout($xmlLayout[0], $xml);
			}
		}
		$xmlParams = (array)$xmlTemplate->xpath('param');
		foreach ($xmlParams as $xmlParam) {
			$params[(string)$xmlParam['name']] = (string)$xmlParam['value'];
		}

		$template = new $templateClass($templateName, $layouts, $params);

		return $template;
	}
}
