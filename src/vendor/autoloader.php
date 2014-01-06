<?php
	
/** 
 * Class used to load all "vendor" class file
 *
 * @package src
 * @subpackage vendor
 * @author Castellant Guillaume
 **/
class Autoloader {

	private $json;
	private $cache;
	private $network;
	private $vendor_exclude = array(
		'console'
	);

	public function __construct()
	{
		$this->constant_definer();

		$this->jsonreader_definer();
		$this->cache_definer();
		
		$this->library_definer();
		$this->application_definer();
		$this->vendor_definer();
	}
	

	/**
	 * Used to define the constant framework
	 *
	 * @return true;
	 **/
	private function constant_definer()
	{
		$cwd = explode('/', getcwd());
		unset($cwd[count($cwd)-1]);

		$host = explode('.', $_SERVER['HTTP_HOST']);
		if (count($host)==3) {
			list($sub, $domain, $ext) = explode('.', $_SERVER['HTTP_HOST']);
		} elseif (count($host)==2) {
			list($domain, $ext) = explode('.', $_SERVER['HTTP_HOST']);
			$sub = 'www';
		}

		$parse_url = parse_url($_SERVER['REQUEST_URI']);

		define('_ENV', (!getenv('HELO') ? 'prod' : strtolower(getenv('HELO'))));
		define('_URI', $parse_url['path']);
	
		$https = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?true:false;
		define('_SSL', $https); 

		if (!empty($parse_url['query'])) {
			$query = array();
			foreach (explode('&', $parse_url['query']) as $q) {
				list($key, $val) = explode('=', $q);
				$query[$key] = $val;
			}

			define('_URI_QUERY', serialize($query));
		}

		if (!empty($_SERVER['REQUEST_METHOD'])) {
			define('_REQUEST', $_SERVER['REQUEST_METHOD']);
		}
		
		if (!empty($_SERVER['REQUEST_TIME_FLOAT'])) {
			define('_REQUEST_TIME', $_SERVER['REQUEST_TIME_FLOAT']);
		}

		define('_URI_SUBDOMAIN', $sub);
		define('_URI_DOMAIN', $domain.'.'.$ext);
		define('_ABSOLUTE', implode('/', $cwd).'/');
		define('_SRC', _ABSOLUTE.'src/');
		define('_VENDOR', _SRC.'vendor/');
		define('_LIBRARY', _SRC.'library/');
		define('_CACHE', _SRC.'cache/');
		define('_APPS', _ABSOLUTE.'apps/');
		define('_CONFIG', _SRC.'config/');
		define('_WEB', _ABSOLUTE.'web/');
		define('_LOG', _SRC.'logs/');
		return true;
	}


	/**
	 * Used to define and load available library class
	 * 
	 * @return true;
	 **/
	private function library_definer()
	{
		if (($library = $this->readDirectory(_LIBRARY))!=false) {
			$require  = array();
			$hash 	  = $this->cache->hash('LIBRARY', serialize($library));

			if (_CONFIG_CACHE_ENABLE && ($cache_load = $this->cache->load($hash))!=false) {
				foreach ($cache_load as $load)
					$require[] = $load;
			} else {
				$declared_class = get_declared_classes();

				foreach ($library as $lib) {
					if (($config = $this->json->read($lib.'/config.json'))!=false) {
						foreach ($config as $key => $value) {
							$package = $value->package."\\".$key;
							
							if (!in_array($package, $declared_class)) {
								$require[] = $lib.'/'.$value->file;
							}
						}
					}
				}
				
				if (count($require)>0) {
					if (_CONFIG_CACHE_ENABLE) {
						$this->cache->save($hash, $require);
					}
				}
			}

			if (count($require)>0) {
				foreach ($require as $req) {
					require_once $req;
				}
			}
		}
	}


	/**
	 * Used to define and load available vendor class
	 * 
	 * @return true;
	 **/
	private function vendor_definer()
	{
		if(($vendor = $this->readDirectory(_VENDOR))!=false)
		{
			$hash    = $this->cache->hash('VENDOR', serialize($vendor));
			$require = array();

			if (_CONFIG_CACHE_ENABLE && ($cache_load = $this->cache->load($hash))!=false) {
				foreach ($cache_load as $load) {
					$require[] = $load;
				}
			} else {
				$curfile = pathinfo(__FILE__, PATHINFO_FILENAME);
				
				foreach ($vendor as $ven) {
					$ven_info = pathinfo($ven, PATHINFO_FILENAME);
					
					if ($ven_info!=$curfile && !in_array($ven_info, $this->vendor_exclude)) {
						$require[] = $ven;
					}
				}

				if (count($require)>0) {
					if (_CONFIG_CACHE_ENABLE) {
						$this->cache->save($hash, $require);
					}
				}
			}

			if (count($require)>0) {
				foreach ($require as $req) {
					require_once $req;
				}
			}
		}
	}


	/**
	 * Used to define available applications
	 * 
	 * @return true;
	 **/
	private function application_definer()
	{
		$apps_config = array();
		$hash = $this->cache->hash('APPS', $this->countApplications());

		if (_CONFIG_CACHE_ENABLE && ($cache_load = $this->cache->load($hash))!=false) {
			$apps_config = serialize($cache_load);
		} else {
			if (is_dir(_APPS)) {
				if ($handle = opendir(_APPS)) {
					while (false !== ($package = readdir($handle))) {
						if ($package!='.' && $package!='..' && is_dir(_APPS.$package)) {
							$apps_config[$package] 		  = array();
							$apps_config[$package]['sub'] = array();
							$apps_config[$package]['dir'] = _APPS.$package.'/';

							if (is_dir($apps_config[$package]['dir'])) {
								if ($handle2 = opendir($apps_config[$package]['dir'])) {
									while (false !== ($apps = readdir($handle2))) {
										if ($apps!='.' && 
										    $apps!='..' && 
										    is_dir($apps_config[$package]['dir'].$apps)) {
											
											$app_enable = false;
											$app_env 	= "dev";
											if (file_exists(($file = $apps_config[$package]['dir'].$apps.'/config.json'))) {
												if (($config = $this->json->read($file))!=false) {
													if (isset($config->enable)) {
														$app_enable = $config->enable;
													}

													if (isset($config->env)) {
														$app_env = $config->env;
													}
												}
											}

											if ($app_enable && $this->use_env($app_env)) {
												$apps_config[$package]['sub'][$apps] 				= array();
												$apps_config[$package]['sub'][$apps]['dir'] 		= $apps_config[$package]['dir'].$apps.'/';
												$apps_config[$package]['sub'][$apps]['controller'] 	= $apps_config[$package]['sub'][$apps]['dir'].'Controller/';
												$apps_config[$package]['sub'][$apps]['model'] 		= $apps_config[$package]['sub'][$apps]['dir'].'Model/';
												$apps_config[$package]['sub'][$apps]['view'] 		= $apps_config[$package]['sub'][$apps]['dir'].'View/';

												if (file_exists($apps_config[$package]['sub'][$apps]['dir'].'config.json')) {
													$apps_config[$package]['sub'][$apps]['config'] = $apps_config[$package]['sub'][$apps]['dir'].'config.json';
												}

												if (file_exists($apps_config[$package]['sub'][$apps]['dir'].'routing.json')) {
													$apps_config[$package]['sub'][$apps]['route'] = $apps_config[$package]['sub'][$apps]['dir'].'routing.json';
												}
											}
										}
									}
								}
							}
						}
					}
				}

				if (_CONFIG_CACHE_ENABLE && count($apps_config)>0) {
					$this->cache->save($hash, $apps_config);
				}

				$apps_config = serialize($apps_config);
			}
		}

		define('_APPS_DEFINED', $apps_config);
	}


	/**
	 * Get and authorize environment
	 * 
	 * @return true;
	 **/
	private function use_env($app_env)
	{
		if (_ENV==$app_env) {
			if (_ENV=='dev') {
				if (($env_ip = $this->json->read(_CONFIG.'env_ip.json'))!=false) {
					$this->network_definer();
					$remote = $_SERVER['REMOTE_ADDR'];
					
					if (isset($env_ip->{_ENV})) {
						foreach ($env_ip->{_ENV} as $ip) {
							if (preg_match('/^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$/i', $ip)) {
								if ($remote==$ip) {
									return true;
								}
							} else {
								if ($this->network->in_range($remote, $ip)) {
									return true;
								}
							}
						}
					}
					return false;
				}
			}
			return true;
		} else if($app_env=='dev') {
			if (($env_ip = $this->json->read(_CONFIG.'env_ip.json'))!=false) {
				$this->network_definer();
				$remote = $_SERVER['REMOTE_ADDR'];
				
				if (isset($env_ip->dev)) {
					foreach ($env_ip->dev as $ip) {
						if (preg_match('/^([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)$/i', $ip)) {
							if ($remote==$ip) {
								return true;
							}
						} else {
							if ($this->network->in_range($remote, $ip)) {
								return true;
							}
						}
					}
				}
				return false;
			}
		}
		return false;
	}


	/**
	 * Used to load the json reader library
	 * 
	 * @return true;
	 **/
	private function jsonreader_definer()
	{
		require_once _LIBRARY.'Json/JsonReader.php';
		$this->json  = new Helo\Library\Json\JsonReader();
	}


	/**
	 * Used to load the json reader library
	 * 
	 * @return true;
	 **/
	private function network_definer()
	{
		require_once _LIBRARY.'Network/Network.php';
		$this->network  = new Helo\Library\Network\Network();
	}


	/**
	 * Used to load the cache class
	 * 
	 * @return true;
	 **/
	private function cache_definer()
	{
		require_once _LIBRARY.'Cache/Cache.php';
		$this->cache = new Helo\Library\Cache\Cache();
		$this->cache->define(_CACHE);
		$cache_config = $this->json->read(_CONFIG.'cache.json');

		define('_CONFIG_CACHE_ENABLE', $cache_config->enable);
	}


	/**
	 * Used to read file or directories from directory
	 * 
	 * @param dir $dir
	 * @return array entry
	 **/
	private function readDirectory($dir)
	{
		$save_entry = array();
		if ($handle = opendir($dir)) {
			while (false !== ($entry = readdir($handle))) {
		       	if ($entry!='.' && $entry!='..') {
		        	$save_entry[] = $dir.$entry;
		        }
		    }
		    closedir($handle);
		}

		return (count($save_entry)>0) ? $save_entry : false;
	}


	/**
	 * Used to determine the application number installed
	 * 
	 * @param dir $dir
	 * @return array entry
	 **/
	private function countApplications()
	{
		// _APPS
		$count_entry = 0;
		if ($handle_pk = opendir(_APPS)) {
			while (false !== ($entry_pk = readdir($handle_pk))) {
		       	if ($entry_pk!='.' && $entry_pk!='..' && is_dir(_APPS.$entry_pk)) {
		        	if ($handle_app = opendir(_APPS.$entry_pk.'/')) {
		        		while (false !== ($entry_app = readdir($handle_app))) {
			        		if ($entry_app!='.' && 
			        			$entry_app!='..' && 
			        			is_dir(_APPS.$entry_pk.'/'.$entry_app)) {

			        			$count_entry++;
			        		}
			        	}
			        	closedir($handle_app);
		        	}
		        }
		    }
		    closedir($handle_pk);
		}
		return $count_entry;
	}
}


/**
 * Init "Autoloader" class
 **/
(new Autoloader());
