<?php
namespace RoxFramework;

use RoxFramework\Model\Drivers;

//DIRS
define('APP_DIR', ROOT_DIR . '/app');
define('CONF_DIR', ROOT_DIR . '/conf');
define('LOGS_DIR', ROOT_DIR . '/logs');
define('ASSETS_DIR', ROOT_DIR . '/assets');
define('PUBLIC_DIR', ROOT_DIR . '/public');

define('VIEW_DIR', APP_DIR . '/view');


//SETTINGS
include(CONF_DIR.'/common/settings.php');

//CORE
include(APP_DIR.'/core/classes/Debug.php');
include(APP_DIR.'/core/classes/RequestResult.php');
include(APP_DIR.'/core/classes/Filter.php');
include(APP_DIR.'/core/classes/Validator.php');
include(APP_DIR.'/core/classes/Converter.php');
include(APP_DIR.'/core/classes/Controller.php');

//MODEl
include(APP_DIR.'/model/common/Entity.php');
include(APP_DIR.'/model/common/Entity_Table.php');
include(APP_DIR.'/model/common/Entity_View.php');


//ROUTING
include(APP_DIR.'/core/libs/PHPRouter/Router.php');
include(APP_DIR.'/core/libs/PHPRouter/Route.php');
include(APP_DIR.'/core/libs/PHPRouter/RouteCollection.php');

//CONFIG
include(CONF_DIR."/common/routing.php");

//LOG
include(APP_DIR.'/core/libs/log4php/Logger.php');
\Logger::configure(CONF_DIR . '/common/log/log-config-'.ENV.'.xml');
$log = \Logger::getLogger(LOG_NAME);

$log->trace("ENTRA");

//DATABASE
include(CONF_DIR.'/common/DataConnection/dc-config-'.ENV.'.php');
include(APP_DIR.'/model/common/drivers/'.DB_DRIVER.'.php');
$db = null;

//MODULES
foreach ($bundles as $bundle) {
	//ROUTING
	include(CONF_DIR."/{$bundle}/config.php");
}

include(APP_DIR.'/core/router.php');