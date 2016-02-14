<?php
namespace RoxOffice\Controllers;

use RoxFramework\Controller;
use RoxFramework\Debug;
use RoxFramework\RequestResult;
use RoxFramework\Filter;

include(APP_DIR.'/controller/RoxOffice/classes/Rox_Cms.php');

class Rox_Controller_EntityMaster extends Controller {

	public function actionDefault($params) {
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
					//$db->beginTransaction();
					
					$cms = Rox_Cms::create(CONF_DIR.'/RoxOffice/rox-office-cms.xml', 'admin');

					if (isset($params['entityGroupSlug'])) {
						$entityManager = $cms->Office->entities[$params['entityGroupSlug']]['entities'][$params['entitySlug']];
						$routingId = 'backoffice_master_group';
					} else {
						$entityManager = $cms->Office->entities[$params['entitySlug']];
						$routingId = 'backoffice_master';
					}

					global $router;

					$args = array();
					//GENERAL
					$args['controller'] 	= $this;
					$args['templateName'] 	= $cms->Office->template->name;
					$args['entities'] 		= $cms->Office->entities;
					
					$args['breadcrumb']		= array(
						'Inicio'				=> '/oficina/',
						$entityManager->title 	=> $router->generate($routingId, $params));
					
					//PAGE
					$args['entityTitle'] 	= $entityManager->title;
					$args['contentLayout'] 	= $entityManager->layoutEntityMaster;
					$args['contentParams'] 	= array(
						'controller'			=> $this,
						'templateName'			=> $cms->Office->template->name,
						'PKField'				=> call_user_func(array($entityManager->entityClass, 'getPKField')),
						'title'					=> $entityManager->title,
						'fields'				=> $entityManager->entityFields, //call_user_func(array($entityManager->entityClass, 'getFieldsList')), //TODO: DataSource
						'data'					=> $entityManager->call($entityManager->entityClass, 'select', array('db' =>  $db)),
						'routing'				=> $params);

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

	public function actionDelete($params) {
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
					//$db->beginTransaction();
					
					$cms = Rox_Cms::create(CONF_DIR.'/RoxOffice/rox-office-cms.xml', 'admin');

					$entityId = $params['entity'];

					if (isset($params['entityGroupSlug'])) {
						$entityManager = $cms->Office->entities[$params['entityGroupSlug']]['entities'][$params['entitySlug']];
						$routingId = 'backoffice_master_group';
					} else {
						$entityManager = $cms->Office->entities[$params['entitySlug']];
						$routingId = 'backoffice_master';
					}

					$PKField = call_user_func(array($entityManager->entityClass, 'getPKField'));
					$data = $entityManager->call($entityManager->entityClass, 'select', array('db' =>  $db, 'where' => array(new Filter($PKField, $entityId))));
					if (!empty($data)) {
						$data = $data[0];
						$entityManager->call($data, 'delete', array('db' =>  $db));
					}

					global $router;

					$args = array();
					//GENERAL
					$args['controller'] 	= $this;
					$args['templateName'] 	= $cms->Office->template->name;
					$args['entities'] 		= $cms->Office->entities;
					
					$args['breadcrumb']		= array(
						'Inicio'				=> '/', //TODO: crear portada
						$entityManager->title 	=> $router->generate($routingId, $params));
					
					//PAGE
					$args['entityTitle'] 	= $entityManager->title;
					$args['contentLayout'] 	= $entityManager->layoutEntityMaster;
					$args['contentParams'] 	= array(
						'controller'			=> $this,
						'templateName'			=> $cms->Office->template->name,
						'PKField'				=> $PKField,
						'title'					=> $entityManager->title,
						'fields'				=> $entityManager->entityFields, //call_user_func(array($entityManager->entityClass, 'getFieldsList')), //TODO: DataSource
						'data'					=> $entityManager->call($entityManager->entityClass, 'select', array('db' =>  $db)),
						'routing'				=> $params);

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
