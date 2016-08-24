<?php

define('LIB_VERSION', '7.0.0');
define('LIB_DATE', '2015.12.06');

mb_http_input('UTF-8'); 
mb_http_output('UTF-8'); 
mb_internal_encoding("UTF-8");

$loader = require __DIR__.'/../vendor/autoload.php';

use Fuga\Component\Container;
use Fuga\Component\Registry;

function exception_handler($exception) 
{	
	$statusCode = $exception instanceof \Fuga\Component\Exception\NotFoundHttpException 
			? $exception->getStatusCode() 
			: 500;
	$message = $statusCode != 500 || PRJ_ENV == 'dev'? $exception->getMessage() : 'Произошла внутренняя ошибка сервера. Обратитесь к администратору';

	if (isset($_SERVER['REQUEST_URI'])) {
		$controller = new Fuga\CommonBundle\Controller\ExceptionController();
		$res = $controller->indexAction($statusCode, $message);
		$res->send();
	} else {
		echo $message;
	}
}

set_exception_handler('exception_handler');

if (file_exists(__DIR__.'/config/config.php')) {
	require_once __DIR__.'/config/config.php';
}

$container = new Container($loader);

// инициализация переменных
if (isset($_SERVER['REQUEST_URI'])) {
	$params = array();
	$sql = 'SELECT name, value FROM config_variable';
	$stmt = $container->get('connection')->prepare($sql);
	$stmt->execute();
	$vars = $stmt->fetchAll();
	foreach ($vars as $var) {
		$params[strtolower($var['name'])] = $var['value'];
		define($var['name'], $var['value']);
	}

	// TODO убрать инициализацию всех таблиц
	$container->initialize();
}