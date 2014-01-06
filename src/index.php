<?php

	require_once 'vendor/resources.php';
	require_once 'vendor/autoloader.php';

	use Helo\Vendor\Router\Router;
		
	$router = new Router();

	if ($router->get()) {
		if (($apps = $router->detect())!=false) {
			$apps->load();
		}
	}

	$router->oups();
?>