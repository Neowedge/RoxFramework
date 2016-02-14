<?php
namespace RoxOffice\Controllers;

use RoxFramework\Controller;
use RoxFramework\Debug;
use RoxFramework\RequestResult;
use RoxFramework\Filter;

include(APP_DIR.'/controller/RoxOffice/classes/Rox_Cms.php');

class Rox_Controller_Index extends Controller {
	public function actionHome() {
		$cms = Rox_Cms::create(CONF_DIR.'/RoxOffice/rox-office-cms.xml', 'admin');
		$args = array(
			'controller' => $this,
			'entities' => $cms->Office->entities,
			'breadcrumb' =>  array(
				'Inicio'				=> '/oficina/'),
			'contentLayout' => $cms->Office->template->getLayout('home'),
			'contentParams' => array(),
			'templateName'	=> $cms->Office->template->name,
		);

		$cms->Office->template->view($this, 'index', $args);
	}
}