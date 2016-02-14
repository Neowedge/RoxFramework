<?php
namespace RoxOffice\Controllers;

use RoxFramework\Controller;
use RoxFramework\Debug;
use RoxFramework\RequestResult;
use RoxFramework\Filter;

include(APP_DIR.'/controller/RoxOffice/classes/Rox_Cms.php');

class Rox_Controller_EntityDetail extends Controller {

	public function actionNew($params) {
		global $log;
		
		/*$params = self::validateEnviar($_POST);
		$errores = array();

		foreach ($params as $param) {
			if ($param instanceof Debug) {
				$errores[] = $param;
			}
		}*/

		if (empty($errores)) {
			$dbClass = "RoxFramework\\Model\\Drivers\\".DB_DRIVER;
				
			if (!($db = $dbClass::connect()) instanceof RequestResult) {
				try {
					$db->beginTransaction();
					
					$cms = Rox_Cms::create(CONF_DIR.'/RoxOffice/rox-office-cms.xml', 'admin');

					if (isset($params['entityGroupSlug'])) {
						$entityManager = $cms->Office->entities[$params['entityGroupSlug']]['entities'][$params['entitySlug']];
						$routingMasterId 	= 'backoffice_master_group';
						$routingId 			= 'backoffice_new_group';
						$routingFormId 		= 'backoffice_new_group_save';
					} else {
						$entityManager = $cms->Office->entities[$params['entitySlug']];
						$routingMasterId 	= 'backoffice_master';
						$routingId 			= 'backoffice_new';
						$routingFormId 		= 'backoffice_new_save';
					}

					global $router;

					$PKField = call_user_func(array($entityManager->entityClass, 'getPKField'));

					$args = array();
					//GENERAL
					$args['controller'] 	= $this;
					$args['templateName'] 	= $cms->Office->template->name;
					$args['entities'] 		= $cms->Office->entities;

					$args['breadcrumb']		= array(
						'Inicio'				=> '/oficina/',
						$entityManager->title 	=> $router->generate($routingMasterId, $params),
						'Nuevo'					=> $router->generate($routingId, $params));

					//PAGE
					$args['entityTitle'] 	= $entityManager->title;
					$args['contentLayout'] 	= $entityManager->layoutEntityDetail;
					$args['contentParams'] 	= array(
						'formAction'			=> $router->generate($routingFormId, $params),
						'controller'			=> $this,
						'templateName'			=> $cms->Office->template->name,
						'PKField'				=> $PKField,
						'title'					=> $entityManager->title,
						'fields'				=> $entityManager->entityFields); //call_user_func(array($entityManager->entityClass, 'getFieldsList')), //TODO: DataSource

					$resultado = new RequestResult(RequestResult::CODIGO_OK, "ok");
				} catch (\PDOException $e) {
					$log->warn($e->getMessage());
					$resultado = new RequestResult(RequestResult::CODIGO_BBDD_TRANSACCION, $e->getMessage(), $e);
					$db->rollBack();
				}
			} else {
				$log->warn($db->message);
				$resultado = $db;
			}
		} else {
			$log->warn($errores[0]->message);
			$resultado = new RequestResult(RequestResult::CODIGO_PARAMETROS_INVALIDOS, $errores[0]->message, $errores);
		}


		if ($resultado->cod == RequestResult::CODIGO_OK) {
			$cms->Office->template->view($this, 'index', $args);
		} else {
			echo var_dump($resultado);
			//$this->viewHtml('arma_tu_seleccion_enviar_error');
		}
	}

	public function actionEdit($params) {
		global $log;
		
		/*$params = self::validateEnviar($_POST);
		$errores = array();

		foreach ($params as $param) {
			if ($param instanceof Debug) {
				$errores[] = $param;
			}
		}*/

		if (empty($errores)) {
			$dbClass = "RoxFramework\\Model\\Drivers\\".DB_DRIVER;
				
			if (!($db = $dbClass::connect()) instanceof RequestResult) {
				try {
					$db->beginTransaction();
					
					$cms = Rox_Cms::create(CONF_DIR.'/RoxOffice/rox-office-cms.xml', 'admin');

					if (isset($params['entityGroupSlug'])) {
						$entityManager = $cms->Office->entities[$params['entityGroupSlug']]['entities'][$params['entitySlug']];
						$routingMasterId 	= 'backoffice_master_group';
						$routingId 			= 'backoffice_edit_group';
						$routingFormId 		= 'backoffice_edit_group_save';
					} else {
						$entityManager = $cms->Office->entities[$params['entitySlug']];
						$routingMasterId 	= 'backoffice_master';
						$routingId 			= 'backoffice_edit';
						$routingFormId 		= 'backoffice_edit_save';
					}

					global $router;

					$masterParams = array_slice($params, 0, -1);

					$PKField = call_user_func(array($entityManager->entityClass, 'getPKField'));
					$data = $entityManager->call($entityManager->entityClass, 'select', array('db' =>  $db, 'where' => array(new Filter($PKField, $params['entity']))));
					if (!empty($data)) {
						$data = $data[0];
					}

					$args = array();
					//GENERAL
					$args['controller'] 	= $this;
					$args['templateName'] 	= $cms->Office->template->name;
					$args['entities'] 		= $cms->Office->entities;

					$args['breadcrumb']		= array(
						'Inicio'				=> '/', //TODO: crear portada
						$entityManager->title 	=> $router->generate($routingMasterId, $masterParams),
						'Id. '.$data->$PKField	=> $router->generate($routingId, $params));

					//PAGE
					$args['entityTitle'] 	= $entityManager->title;
					$args['contentLayout'] 	= $entityManager->layoutEntityDetail;
					$args['contentParams'] 	= array(
						'formAction'			=> $router->generate($routingFormId, $params),
						'controller'			=> $this,
						'templateName'			=> $cms->Office->template->name,
						'PKField'				=> $PKField,
						'title'					=> $entityManager->title,
						'fields'				=> $entityManager->entityFields, //call_user_func(array($entityManager->entityClass, 'getFieldsList')), //TODO: DataSource
						'data'					=> $data);

					$resultado = new RequestResult(RequestResult::CODIGO_OK, "ok");
				} catch (\PDOException $e) {
					$log->warn($e->getMessage());
					$resultado = new RequestResult(RequestResult::CODIGO_BBDD_TRANSACCION, $e->getMessage(), $e);
					$db->rollBack();
				}
			} else {
				$log->warn($db->message);
				$resultado = $db;
			}
		} else {
			$log->warn($errores[0]->message);
			$resultado = new RequestResult(RequestResult::CODIGO_PARAMETROS_INVALIDOS, $errores[0]->message, $errores);
		}


		if ($resultado->cod == RequestResult::CODIGO_OK) {
			$cms->Office->template->view($this, 'index', $args);
		} else {
			echo var_dump($resultado);
			//$this->viewHtml('arma_tu_seleccion_enviar_error');
		}
	}

	public function actionNewSave($params) {
		global $log;
		
		/*$params = self::validateEnviar($params);
		$errores = array();

		foreach ($params as $param) {
			if ($param instanceof Debug) {
				$errores[] = $param;
			}
		}*/

		$data = $_POST;
		/*$data = self::validateEnviar($_POST);
		$errores = array();

		foreach ($params as $param) {
			if ($param instanceof Debug) {
				$errores[] = $param;
			}
		}*/
		

		if (empty($errores)) {
			$dbClass = "RoxFramework\\Model\\Drivers\\".DB_DRIVER;
				
			if (!($db = $dbClass::connect()) instanceof RequestResult) {
				try {
					//$db->beginTransaction();

					$cms = Rox_Cms::create(CONF_DIR.'/RoxOffice/rox-office-cms.xml', 'admin');

					if (isset($params['entityGroupSlug'])) {
						$entityManager = $cms->Office->entities[$params['entityGroupSlug']]['entities'][$params['entitySlug']];
						$routingMasterId 	= 'backoffice_master_group';
						$routingId 			= 'backoffice_new_group';
						$routingFormId 		= 'backoffice_new_group_save';
						$routingEditId 		= 'backoffice_edit_group';
						$routingEditFormId	= 'backoffice_edit_group_save';
					} else {
						$entityManager = $cms->Office->entities[$params['entitySlug']];
						$routingMasterId 	= 'backoffice_master';
						$routingId 			= 'backoffice_new';
						$routingFormId 		= 'backoffice_new_save';
						$routingEditId 		= 'backoffice_edit';
						$routingEditFormId	= 'backoffice_edit_save';
					}

					global $router;

					$PKField = call_user_func(array($entityManager->entityClass, 'getPKField'));

					$validates = array();
					$valid = true;
					foreach ($entityManager->entityFields as $field) {
						$fieldName = $field->name;
						$fieldValid = true;
						foreach ($field->validators as $validator) {
							$validatorFunction = $validator['function'];
							if (isset($validator['class'])) {
								$validatorClass = $validator['class'];
								$fieldValid = call_user_func(array($validatorClass, $validatorFunction), $data[$fieldName]);
							} else {
								$fieldValid = $validatorFunction($data[$fieldName]);
							}
							if (!$fieldValid) {
								$validates[$fieldName] = isset($validator['error']) ? $validator['error'] : 'Este valor no es válido';
								$valid = false;
								break;
							} else {
								$validates[$fieldName] = true;
							}
						}
						if ($fieldValid) {
							if (isset($data[$fieldName])) {
								$data[$fieldName] = $field->getDbValue($data[$fieldName]);
							} elseif ($field->type == 'bool') {
								$data[$fieldName] = 0;
							}
						}
						if (!isset($validates[$fieldName])) {
							$validates[$fieldName] = $fieldValid ? true : 'Este valor no es válido';
						}
					}

					$entityData = new $entityManager->entityClass($data);

					if ($valid) {
						$entityResult = $entityManager->call($entityManager->entityClass, 'insert', array('db' =>  $db, 'data' => $data));
						$valid = !($entityResult instanceof RequestResult);
					} else {
						$entityResult = new RequestResult(RequestResult::CODIGO_PARAMETROS_INVALIDOS, 'Algunos de los valores introducidos no son válidos.');
					}

					$args = array();
					//GENERAL
					$args['controller'] 	= $this;
					$args['templateName'] 	= $cms->Office->template->name;
					$args['entities'] 		= $cms->Office->entities;

					//PAGE
					if ($valid) {
						$entityId = $db->getLastInsertId($entityResult);

						$args['data'][$PKField] = $entityId;
						
						$messsage = array(
							'type' 	=> 'success',
							'text'	=> '¡Los datos se han guardado correctamente!');

						$paramsEdit = $params;
						$paramsEdit['entity'] = $entityId;

						$args['breadcrumb']		= array(
							'Inicio'				=> '/', //TODO: crear portada
							$entityManager->title 	=> $router->generate($routingMasterId, $params),
							'Id. '					=> $router->generate($routingEditId, $paramsEdit));
						
					} else {
						$messsage = array(
							'type' 	=> 'error',
							'text'	=> $entityResult->message);


						$args['breadcrumb']		= array(
							'Inicio'				=> '/', //TODO: crear portada
							$entityManager->title 	=> $router->generate($routingMasterId, $params),
							'Nuevo'					=> $router->generate($routingId, $params));

					}

					$args['entityTitle'] 	= $entityManager->title;
					$args['contentLayout'] 	= $entityManager->layoutEntityDetail;
					$args['contentParams'] 	= array(
						'formAction'			=> $valid ? $router->generate($routingEditFormId, $paramsEdit) : $router->generate($routingFormId, $params),
						'controller'			=> $this,
						'templateName'			=> $cms->Office->template->name,
						'PKField'				=> $PKField,
						'title'					=> $entityManager->title,
						'fields'				=> $entityManager->entityFields,
						'data'					=> $entityData,
						'validates'				=> $validates,
						'message'				=> $messsage);

					$resultado = new RequestResult(RequestResult::CODIGO_OK, "ok");

				} catch (\PDOException $e) {
					$log->warn($e->getMessage());
					$resultado = new RequestResult(RequestResult::CODIGO_BBDD_TRANSACCION, $e->getMessage(), $e);
					//$db->rollBack();
				}
			} else {
				$log->warn($db->message);
				$resultado = $db;
			}
		} else {
			$log->warn($errores[0]->message);
			$resultado = new RequestResult(RequestResult::CODIGO_PARAMETROS_INVALIDOS, $errores[0]->message, $errores);
		}


		if ($resultado->cod == RequestResult::CODIGO_OK) {
			$cms->Office->template->view($this, 'index', $args);
		} else {
			echo var_dump($resultado);
			//$this->viewHtml('arma_tu_seleccion_enviar_error');
		}
	}

	public function actionEditSave($params) {
		global $log;
		
		/*$params = self::validateEnviar($params);
		$errores = array();

		foreach ($params as $param) {
			if ($param instanceof Debug) {
				$errores[] = $param;
			}
		}*/

		$data = $_POST;
		/*$data = self::validateEnviar($_POST);
		$errores = array();

		foreach ($params as $param) {
			if ($param instanceof Debug) {
				$errores[] = $param;
			}
		}*/
		

		if (empty($errores)) {
			$dbClass = "RoxFramework\\Model\\Drivers\\".DB_DRIVER;
				
			if (!($db = $dbClass::connect()) instanceof RequestResult) {
				try {
					//$db->beginTransaction();

					$cms = Rox_Cms::create(CONF_DIR.'/RoxOffice/rox-office-cms.xml', 'admin');

					$entityId = $params['entity'];

					if (isset($params['entityGroupSlug'])) {
						$entityManager = $cms->Office->entities[$params['entityGroupSlug']]['entities'][$params['entitySlug']];
						$routingMasterId 	= 'backoffice_master_group';
						$routingId 			= 'backoffice_edit_group';
						$routingFormId 		= 'backoffice_edit_group_save';
					} else {
						$entityManager = $cms->Office->entities[$params['entitySlug']];
						$routingMasterId 	= 'backoffice_master';
						$routingId 			= 'backoffice_edit';
						$routingFormId 		= 'backoffice_edit_save';
					}

					$paramsMaster = array_slice($params, 0, -1);

					global $router;

					$PKField = call_user_func(array($entityManager->entityClass, 'getPKField'));

					$validates = array();
					$valid = true;
					foreach ($entityManager->entityFields as $field) {
						$fieldName = $field->name;
						$fieldValid = true;
						foreach ($field->validators as $validator) {
							$validatorFunction = $validator['function'];
							if (isset($validator['class'])) {
								$validatorClass = $validator['class'];
								$fieldValid = call_user_func(array($validatorClass, $validatorFunction), $data[$fieldName]);
							} else {
								$fieldValid = $validatorFunction($data[$fieldName]);
							}
							if (!$fieldValid) {
								$validates[$fieldName] = isset($validator['error']) ? $validator['error'] : 'Este valor no es válido';
								$valid = false;
								break;
							} else {
								$validates[$fieldName] = true;
							}
						}
						if ($fieldValid) {
							if (isset($data[$fieldName])) {
								$data[$fieldName] = $field->getDbValue($data[$fieldName]);
							} elseif ($field->type == 'bool') {
								$data[$fieldName] = 0;
							}
						}
						if (!isset($validates[$fieldName])) {
							$validates[$fieldName] = $fieldValid ? true : 'Este valor no es válido';
						}
					}



					$entityData = new $entityManager->entityClass($data);
					$entityData->$PKField = $entityId;

					if ($valid) {
						$entityResult = $entityManager->call($entityData, 'update', array('db' =>  $db, 'data' => $data));
						$valid = !($entityResult instanceof RequestResult);
					} else {
						$entityResult = new RequestResult(RequestResult::CODIGO_PARAMETROS_INVALIDOS, 'Algunos de los valores introducidos no son válidos.');
					}

					$args = array();
					//GENERAL
					$args['controller'] 	= $this;
					$args['templateName'] 	= $cms->Office->template->name;
					$args['entities'] 		= $cms->Office->entities;

					$args['breadcrumb']		= array(
							'Inicio'				=> '/', //TODO: crear portada
							$entityManager->title 	=> $router->generate($routingMasterId, $paramsMaster),
							'Id. ' . $entityId		=> $router->generate($routingId, $params));

					//PAGE
					if ($valid) {
						$args['data'][$PKField] = $entityId;
						
						$messsage = array(
							'type' 	=> 'success',
							'text'	=> '¡Los datos se han guardado correctamente!');

						
					} else {
						$messsage = array(
							'type' 	=> 'error',
							'text'	=> $entityResult->message);

					}
					
					$args['entityTitle'] 	= $entityManager->title;
					$args['contentLayout'] 	= $entityManager->layoutEntityDetail;
					$args['contentParams'] 	= array(
						'formAction'			=> $router->generate($routingFormId, $params),
						'controller'			=> $this,
						'templateName'			=> $cms->Office->template->name,
						'PKField'				=> $PKField,
						'title'					=> $entityManager->title,
						'fields'				=> $entityManager->entityFields,
						'data'					=> $entityData,
						'validates'				=> $validates,
						'message'				=> $messsage);

					$resultado = new RequestResult(RequestResult::CODIGO_OK, "ok");

				} catch (\PDOException $e) {
					$log->warn($e->getMessage());
					$resultado = new RequestResult(RequestResult::CODIGO_BBDD_TRANSACCION, $e->getMessage(), $e);
					//$db->rollBack();
				}
			} else {
				$log->warn($db->message);
				$resultado = $db;
			}
		} else {
			$log->warn($errores[0]->message);
			$resultado = new RequestResult(RequestResult::CODIGO_PARAMETROS_INVALIDOS, $errores[0]->message, $errores);
		}


		if ($resultado->cod == RequestResult::CODIGO_OK) {
			$cms->Office->template->view($this, 'index', $args);
		} else {
			echo var_dump($resultado);
			//$this->viewHtml('arma_tu_seleccion_enviar_error');
		}
	}

}