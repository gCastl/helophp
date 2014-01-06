<?php

namespace Helo\Vendor\Application;

use Helo\Library\Json\JsonReader,
	Helo\Library\Cache\Cache,
	Helo\Vendor\Logs\Logs;

class Application
{

	private $cache;
	private $config;

	public function __construct($conf, $args)
	{
		if(!session_id())
			session_start();

		$this->cache = new Cache();
		$this->cache->define(_CACHE);
		
		$hash = $this->cache->hash('APPS', _URI, $conf);

		if (_CONFIG_CACHE_ENABLE && ($cache_load = $this->cache->load($hash))!=false) {
			$this->config = $cache_load;
		} else {
			$apps = unserialize(_APPS_DEFINED);
			list($app, $sub, $controller, $method) = explode(':', $conf);

			if (isset($apps[$app]) && isset($apps[$app]['sub'][$sub])) {
				$this->config['name'] = $app;
				$this->config['sub']  = $sub;
				$this->config['args'] = $args;
				
				$c_name = "\\Helo\\".$app."\\".$sub."\\Controller\\".$controller.'Controller';
				$m_name = "\\Helo\\".$app."\\".$sub."\\Model\\".$controller.'Model';

				$this->config['controller_class'] = $c_name;
				$this->config['model_call'] = $m_name;
				$this->config['method'] = $method;

				$c_file = $apps[$app]['sub'][$sub]['controller'].strtolower($controller).'.php';
				$m_file = $apps[$app]['sub'][$sub]['model'].strtolower($controller).'.php';

				$this->config['controller'] = $conf;
				$this->config['controller_file'] = $c_file;
				$this->config['model_file'] = $m_file;
			}

			if (_CONFIG_CACHE_ENABLE && count($this->config)>0) {
				$this->cache->save($hash, $this->config);
			}
		}
	}

	public function load()
	{
		$json = new JsonReader();

		define('_CURRENT_PACKAGE_NAME', $this->config['name']);
		define('_CURRENT_PACKAGE_DIR', _APPS.$this->config['name'].'/');

		define('_CURRENT_APP_NAME', $this->config['sub']);
		define('_CURRENT_APP_DIR', _CURRENT_PACKAGE_DIR.$this->config['sub'].'/');
		define('_CURRENT_APP_VIEW', _CURRENT_APP_DIR.'View/');
		define('_CURRENT_APP_WEB', _CURRENT_APP_DIR.'Web/');

		$_SESSION['_ENV'] 		 	   = _ENV;
		$_SESSION['_URI'] 			   = _URI;
		$_SESSION['_URI_SUBDOMAIN']    = _URI_SUBDOMAIN;
		$_SESSION['_URI_DOMAIN'] 	   = _URI_DOMAIN;
		$_SESSION['_ABSOLUTE']   	   = _ABSOLUTE;
		$_SESSION['_SRC']  	     	   = _SRC;
		$_SESSION['_VENDOR']     	   = _VENDOR;
		$_SESSION['_LIBRARY']    	   = _LIBRARY;
		$_SESSION['_CACHE']      	   = _CACHE;
		$_SESSION['_APPS'] 	     	   = _APPS;
		$_SESSION['_CONFIG']     	   = _CONFIG;
		$_SESSION['_WEB'] 	     	   = _WEB;
		$_SESSION['_CURRENT_APP_NAME'] = _CURRENT_APP_NAME;
		$_SESSION['_CURRENT_APP_VIEW'] = _CURRENT_APP_VIEW;
		$_SESSION['_CURRENT_APP_WEB']  = _CURRENT_APP_WEB;
		$_SESSION['_CURRENT_APP_DIR']  = _CURRENT_APP_DIR;

		if (($app_config = $json->read(_CURRENT_APP_DIR.'config.json'))!=false) {
			$_SESSION['_CURRENT_APP_CONFIG'] = serialize($app_config); 
		}

		require_once $this->config['controller_file'];
		$controller = new $this->config['controller_class']();

		if (count($this->config['args'])==0) {
			$controller->{$this->config['method']}();
		} else {
			call_user_func_array(array(
				$controller, 
				$this->config['method']
			), $this->config['args']);
		}

		Logs::access('app', 200);
		exit();
	}
}
