<?php

	namespace Helo\Console;

	class Console {
		
		static $name;
		static $argv;

		static $cache_dir;
		static $config_dir;
		static $console_dir;
		static $library_dir;

		static $json_reader;
	
		public function __construct($argv){
			self::$name = explode('/', $argv[0]);
			foreach(self::$name as &$name)
				$name = ucfirst($name);
			
			self::$name = implode('', self::$name);
			unset($argv[0]);

			if(count(($key = array_keys($argv, '--help')))>0){
				define('_CLI_HELP', true);
				foreach($key as $k)
					unset($argv[$k]);
			} else
				define('_CLI_HELP', false);

			// USERS GET
			foreach ($argv as $key => $value) {
				if(preg_match('/^--user\=([0-9a-zA-Z_.]+)$/i', $value, $match))
					define('_CLI_USER', $match[1]);
			}

			if(!defined('_CLI_USER'))
				define('_CLI_USER', 'root');

			self::$argv 	   = array_values($argv);
			self::$cache_dir   = getcwd().'/src/cache/';
			self::$config_dir  = getcwd().'/src/config/';
			self::$console_dir = getcwd().'/src/console/';
			self::$library_dir = getcwd().'/src/library/';
		}

		public function valid_console(){
			$file = str_replace('console', '', strtolower(self::$name));
			require_once self::$console_dir.$file.'.php';
			
			if(class_exists(($class = 'Helo\\Console\\'.self::$name))){
				return new $class(self::$argv);
			}
			return false;
		}

		/**
		 * Load library
		 **/
		public function load_library(){
			require_once getcwd().'/src/library/Json/JsonReader.php';
			self::$json_reader = new \Helo\Library\JsonReader\JsonReader();

			if(($library = $this->readDirectory(self::$library_dir))!=false){
				$declared_class = get_declared_classes();
				foreach($library as $lib){
					if(($config = self::$json_reader->read($lib.'/config.json'))!=false){
						foreach($config as $key => $value) {
							$package = $value->package."\\".$key;
							if(!in_array($package, $declared_class)){
								require_once $lib.'/'.$value->file;
							}
						}
					}
				}
			}
		}

		/**
		 * Used to read file or directories from directory
		 * 
		 * @param dir $dir
		 * @return array entry
		 **/
		private function readDirectory($dir){
			$save_entry = array();
			if ($handle = opendir($dir)) {
				while (false !== ($entry = readdir($handle))) {
			       	if($entry!='.' && $entry!='..')
			        	$save_entry[] = $dir.$entry;
			    }
			    closedir($handle);
			}

			return (count($save_entry)>0) ? $save_entry : false;
		}
	}

?>