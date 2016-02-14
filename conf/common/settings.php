<?php
namespace RoxFramework;
//VERSION
define('APP_VERSION', '00001'); //0.0.1
define('DEBUG', true);

//ENVIRONMENTS
if ($_SERVER['SERVER_NAME'] == 'localhost') {
	define('ENV', 'local');
} elseif ($_SERVER['SERVER_NAME'] == 'dev.mydomain.com') {
	define('ENV', 'dev');
} elseif ($_SERVER['SERVER_NAME'] == 'test.mydomain.com') {
	define('ENV', 'test');
} else {
	define('ENV', 'prod');
}

//TIMEZONE
define('TIMEZONE', 'Europe/Madrid');
date_default_timezone_set(TIMEZONE);

//USER IP
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	define('USER_IP', $_SERVER['HTTP_CLIENT_IP']);
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	define('USER_IP', $_SERVER['HTTP_X_FORWARDED_FOR']);
} else {
	define('USER_IP', $_SERVER['REMOTE_ADDR']);
}

//LOGS
define('LOG_NAME', 'VodafoneBasket');

//BUNDLES
$bundles = array(
	'RoxOffice'
);