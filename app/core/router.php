<?php
namespace RoxFramework;

use PHPRouter\Router;
use PHPRouter\Route;
use PHPRouter\RouteCollection;


$router = new Router($routing, $routing);
$route = $router->matchCurrentRequest();

if (!$route) {
	header("HTTP/1.1 404 Route Not Found");
	exit;
}

$controller = (array)$route->getTarget();
if (!$controller) {
	header("HTTP/1.1 404 Target Not Found");
	exit;
}

$controllerFile = APP_DIR.'/controller/'.$controller[0]."/{$controller[1]}.php";
if (!file_exists($controllerFile)) {
	header("HTTP/1.1 404 Controller Not Found");
	exit;
}

include_once($controllerFile);
$controllerClass = $controller[0].'\\Controllers\\'.$controller[1];
$controllerAction = array(new $controllerClass($controller[0]), $controller[2]);

if (!is_callable($controllerAction, true)) {
	header("HTTP/1.1 404 Action Not Found");
	exit;
}

$parameters = $route->getParameters();
if (empty($parameters)) {
	call_user_func($controllerAction);
} else {
	call_user_func($controllerAction, $parameters);
}