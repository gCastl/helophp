<?php

	namespace Helo\Vendor\Console;

	class ConsoleApplication extends Console {

		private $argv;
		private $verbose = false;
		private $help = false;
		private $force = false;
		private $apps_dir;

		public function __construct($argv){
			$this->apps_dir  = getcwd().'/apps/';
			$this->argv = array_values($argv);
		}

		public function run(){
			if(count($this->argv)>0){
				$command = $this->argv[0];
				unset($this->argv[0]);
				$this->argv = array_values($this->argv);

				if(_CLI_HELP)
					$this->helper($command);
				else 
					$this->command($command, ((count($this->argv)>0) ? $this->argv : false));
			} else
				$this->helper();
		}

		private function command($command, $argv=false){
			if(preg_match('/^([a-zA-Z0-9]+)\:([a-zA-Z0-9]+)$/i', $command, $match)){
				$package 	   = $match[1];
				$application   = $match[2];
				$package_ls	   = $this->getAppPackage();
				
				if(!in_array($package, $package_ls)){
					Cli::error("The package must exist\n");
					exit();
				}

				$subpackage_ls = $this->getAppSubPackage($package);

				if(!in_array($application, $subpackage_ls)){
					Cli::error("The application must exist\n");
					exit();
				}

				if(in_array('delete', $argv)){
					$this->delete_application(array($command));
				} else {
					foreach($argv as $arg){
						switch($arg){
							case '-enable=true': $this->config_application(array($command), 'enable', true); break;
							case '-enable=false': $this->config_application(array($command), 'enable', false); break;
							case '-cache=strict': $this->config_application(array($command), 'cache', 'strict'); break;
							case '-cache=true': $this->config_application(array($command), 'cache', true); break;
							case '-cache=false': $this->config_application(array($command), 'cache', false); break;
							case '-log=true': $this->config_application(array($command), 'log', true); break;
							case '-log=false': $this->config_application(array($command), 'log', false); break;
							case '-env=prod': $this->config_application(array($command), 'env', 'prod'); break;
							case '-env=dev': $this->config_application(array($command), 'env', 'dev'); break;
							case '-engine=twig': $this->config_application(array($command), 'engine', 'twig'); break;
							case '-engine=native': $this->config_application(array($command), 'engine', 'native'); break;
							case '-engine=false': $this->config_application(array($command), 'engine', false); break;
						}
					}
				}
			} else {
				switch($command){
					case 'create': $this->create_application($argv); break;
					case 'delete': $this->delete_application($argv); break;
					case 'enable': $this->enable_application($argv); break;
					case 'disable': $this->disable_application($argv); break;
					case 'list': $this->list_application($argv); break;
					default: $this->helper(); break;
				}
			}
		}

		private function list_application($argv){
			require_once getcwd().'/src/library/Json/JsonReader.php';
			$json['read']  = new \Helo\Library\JsonReader\JsonReader();

			$args = array();
			if($argv && count($argv)>0){
				foreach($argv as &$arg){
					if(preg_match('/^\-(.*)$/', $arg)){
						$args[] = $arg;
						$arg = null;
					}
				}
			
				$argv = array_diff($argv, array(null));
			}

			if(!$argv || count($argv)==0){
				$application = array();
				$package_ls	 = $this->getAppPackage();
				foreach($package_ls as $package){
					$application[$package] = $this->getAppSubPackage($package);
				}
			} else {
				if($argv[0]=='?'){
					$package_ls	 = $this->getAppPackage();
					Cli::write("Packages list:\n");
					foreach ($package_ls as $key => $package) {
						Cli::write("  ".$package."\n");
					}
					
					$line = Cli::prompt("Which package you want list ? ", false, $package_ls, 'red');
					if(in_array($line, $package_ls)){
						$application[$line] = $this->getAppSubPackage($line);
					} else {
						Cli::error("The package must exist\n");
						exit();
					}
				} else {
					$package_ls	 = $this->getAppPackage();
					if(!in_array($argv[0], $package_ls)){
						Cli::error("The package must exist\n");
						exit();
					}
					$application[$argv[0]] = $this->getAppSubPackage($argv[0]);
				}
			}

			$list = array();
			foreach($application as $package => $apps){
				$package_dir = $this->apps_dir.$package.'/';
				foreach ($apps as $name => $app) {
					$application_dir = $package_dir.$app.'/';
					$application_config_file = $application_dir.'config.json';

					$this_config = $json['read']->read($application_config_file);
					
					$to_show = $to_title = array();
					$color_show = false;
					if(in_array('-enable', $args) || in_array('-full', $args)){
						$to_title[] = 'enable';
						if(isset($this_config->enable) && $this_config->enable){
							$color_show = "success";
							$to_show[]  = 'true';
						} else {
							$color_show = "error";
							$to_show[]  = 'false';
						}
					}

					if(in_array('-env', $args) || in_array('-full', $args)){
						$to_title[] = 'env';
						if(isset($this_config->env))
							$to_show[] = $this_config->env;
						else 
							$to_show[] = 'dev';
					}

					if(in_array('-cache', $args) || in_array('-full', $args)){
						$to_title[] = 'cache';
						if(isset($this_config->cache) && $this_config->cache)
							$to_show[] = ((string) $this_config->cache=='1') ? 'true' : $this_config->cache;
						else 
							$to_show[] = 'false';
					}

					if(in_array('-log', $args) || in_array('-full', $args)){
						$to_title[] = 'log';
						if(isset($this_config->log) && $this_config->log)
							$to_show[] = 'true';
						else 
							$to_show[] = 'false';
					}

					if(in_array('-engine', $args) || in_array('-full', $args)){
						$to_title[] = 'engine';
						if(isset($this_config->engine) && $this_config->engine)
							$to_show[] = $this_config->engine;
						else 
							$to_show[] = 'false';
					}

					if(in_array('-orm', $args) || in_array('-full', $args)){
						$to_title[] = 'orm';
						if(isset($this_config->orm) && $this_config->orm)
							$to_show[] = ((string) $this_config->orm=='1') ? 'true' : $this_config->orm;
						else 
							$to_show[] = 'false';
					}

					if(count($to_title)>0)
						$to_title[] = '';

					if(count($to_show)>0)
						$to_show[] = '';

					$val = $app." (".$package.")\t".implode("\t", $to_show);
					if($color_show=='success')
						$list[] = Cli::green($val);
					else if($color_show=='error')
						$list[] = Cli::red($val);
					else
						$list[] = $val;
				}
			}


			if(count($list)>0){
				Cli::write("Name (package)\t".implode("\t", $to_title)."\n");
				foreach($list as $ls){
					echo $ls."\n";
				}
			} else
				Cli::error("0 application founded\n");
			
		}

		private function config_application($argv, $mode, $value){
			require_once getcwd().'/src/library/Json/JsonReader.php';
			require_once getcwd().'/src/library/Json/JsonWritter.php';

			$json['read']  = new \Helo\Library\JsonReader\JsonReader();
			$json['write'] = new \Helo\Library\JsonWritter\JsonWritter();

			list($package, $application) = explode(':', $argv[0]);

			$this_config = $json['read']->read($this->apps_dir.$package.'/'.$application.'/config.json');
			$this_config->{$mode} = $value;
			Cli::write("Set \"".strtoupper($mode)."\" to \"".strtoupper(((string) $value=='0' || (string) $value=='1') ? (($value==0) ? 'false' : 'true') : $value)."\" ".Cli::green('SUCCESS')."\n");
			
			$this_config_encode = $json['write']->full_encode($this_config);
			$json['write']->write($this->apps_dir.$package.'/'.$application.'/config.json', $this_config_encode);
		}

		private function disable_application($argv){
			require_once getcwd().'/src/library/Json/JsonReader.php';
			require_once getcwd().'/src/library/Json/JsonWritter.php';

			$json['read']  = new \Helo\Library\JsonReader\JsonReader();
			$json['write'] = new \Helo\Library\JsonWritter\JsonWritter();

			$package_ls	   = $this->getAppPackage();

			$package = false;
			$application = false;
			if(!$argv){
				$package = ucfirst(strtolower($this->getExistPackageName($package_ls)));
				
				$subpackage_ls = $this->getAppSubPackage($package);
				$application   = ucfirst(strtolower($this->getExistApplicationName($subpackage_ls)));
			} else {
				$argv_explode  = explode(':', $argv[0]);
				if(count($argv_explode)==1){
					$package 	   = $argv_explode[0];
					$subpackage_ls = $this->getAppSubPackage($package);
					$application   = ucfirst(strtolower($this->getExistApplicationName($subpackage_ls)));
				} else {
					$package 	   = $argv_explode[0];
					$subpackage_ls = $this->getAppSubPackage($package);
					$application   = $argv_explode[1];
				}
			}

			if(!in_array($package, $package_ls)){
				Cli::error("The package must exist\n");
				Cli::error("Enable application aborted\n");
				exit();
			}

			if(!in_array($application, $subpackage_ls)){
				Cli::error("The application must exist\n");
				Cli::error("Enable application aborted\n");
				exit();
			}

			if($package && $application){
				if(_CLI_FORCE)
					$line = 'y';
				else 
					$line = Cli::yesORno("Disable this application ? (yes or no): ", false, "red");
				if($line=='yes' || $line=='y'){
					$this_config = $json['read']->read($this->apps_dir.$package.'/'.$application.'/config.json');
					$this_config->enable = false;
					$this_config_encode = $json['write']->full_encode($this_config);
					$json['write']->write($this->apps_dir.$package.'/'.$application.'/config.json', $this_config_encode);

					Cli::success("Disable application success\n");
				} else {
					Cli::error("\nDisable application aborted\n");
				}
			}
		}

		private function enable_application($argv, $type='enable'){
			require_once getcwd().'/src/library/Json/JsonReader.php';
			require_once getcwd().'/src/library/Json/JsonWritter.php';

			$json['read']  = new \Helo\Library\JsonReader\JsonReader();
			$json['write'] = new \Helo\Library\JsonWritter\JsonWritter();

			$package_ls	   = $this->getAppPackage();

			$package = false;
			$application = false;
			if(!$argv){
				$package = ucfirst(strtolower($this->getExistPackageName($package_ls)));
				
				$subpackage_ls = $this->getAppSubPackage($package);
				$application   = ucfirst(strtolower($this->getExistApplicationName($subpackage_ls)));
			} else {
				$argv_explode  = explode(':', $argv[0]);
				if(count($argv_explode)==1){
					$package 	   = $argv_explode[0];
					$subpackage_ls = $this->getAppSubPackage($package);
					$application   = ucfirst(strtolower($this->getExistApplicationName($subpackage_ls)));
				} else {
					$package 	   = $argv_explode[0];
					$subpackage_ls = $this->getAppSubPackage($package);
					$application   = $argv_explode[1];
				}
			}

			if(!in_array($package, $package_ls)){
				Cli::error("The package must exist\n");
				Cli::error("Enable application aborted\n");
				exit();
			}

			if(!in_array($application, $subpackage_ls)){
				Cli::error("The application must exist\n");
				Cli::error("Enable application aborted\n");
				exit();
			}

			if($package && $application){
				if(_CLI_FORCE)
					$line = 'y';
				else 
					$line = Cli::yesORno("Enable this application ? (yes or no): ", false, "red");
				if($line=='yes' || $line=='y'){
					$this_config = $json['read']->read($this->apps_dir.$package.'/'.$application.'/config.json');
					$this_config->enable = true;
					$this_config_encode = $json['write']->full_encode($this_config);
					$json['write']->write($this->apps_dir.$package.'/'.$application.'/config.json', $this_config_encode);

					Cli::success("Enable application success\n");
				} else {
					Cli::error("\nEnable application aborted\n");
				}
			}
		}

		private function delete_application($argv){
			require_once getcwd().'/src/library/Json/JsonReader.php';
			require_once getcwd().'/src/library/Json/JsonWritter.php';

			$json['read']  = new \Helo\Library\JsonReader\JsonReader();
			$json['write'] = new \Helo\Library\JsonWritter\JsonWritter();

			$package_ls	   = $this->getAppPackage();

			$package = false;
			$application = false;
			if(!$argv){
				$package = ucfirst(strtolower($this->getExistPackageName($package_ls)));
				
				$subpackage_ls = $this->getAppSubPackage($package);
				$application   = ucfirst(strtolower($this->getExistApplicationName($subpackage_ls)));
			} else {
				$argv_explode  = explode(':', $argv[0]);
				if(count($argv_explode)==1){
					$package 	   = $argv_explode[0];
					$subpackage_ls = $this->getAppSubPackage($package);
					$application   = ucfirst(strtolower($this->getExistApplicationName($subpackage_ls)));
				} else {
					$package 	   = $argv_explode[0];
					$subpackage_ls = $this->getAppSubPackage($package);
					$application   = $argv_explode[1];
				}
			}

			if(!in_array($package, $package_ls)){
				Cli::error("The package must exist\n");
				Cli::error("Enable application aborted\n");
				exit();
			}

			if(!in_array($application, $subpackage_ls)){
				Cli::error("The application must exist\n");
				Cli::error("Enable application aborted\n");
				exit();
			}

			if($package && $application){
				if(_CLI_FORCE)
					$line = 'y';
				else 
					$line = Cli::yesORno("Delete this application ? (yes or no): ", false, "red");
				if($line=='yes' || $line=='y'){					
					if(is_dir(($dir = $this->apps_dir.$package.'/'.$application."/"))){
						$this->rmdir_recursive($dir);
						if(_CLI_VERBOSE)
							Cli::write("  Delete application folder ".Cli::green('SUCCESS')."\n");
					}

					Cli::success("\nDelete application success\n");
				} else {
					Cli::error("\nDeleting application aborted\n");
				}
			}
		}

		private function create_application($argv){
			require_once getcwd().'/src/library/Json/JsonReader.php';
			require_once getcwd().'/src/library/Json/JsonWritter.php';

			$create 				= array();
			$use 					= array();
		
			$json['read']  			= new \Helo\Library\JsonReader\JsonReader();
			$json['write'] 			= new \Helo\Library\JsonWritter\JsonWritter();

			if(preg_match('/^([a-zA-Z0-9]+)\:([a-zA-Z0-9]+)$/i', $argv[0], $match)){
				$create['package'] 		= $match[1];
				$create['application']  = $match[2];
				$subpackage_ls			= $this->getAppSubPackage($create['package']);
			} else {
				$package_ls 			= $this->getAppPackage();
				$create['package']		= ucfirst(strtolower($this->getPackageName($package_ls)));
				$create['application'] 	= ucfirst(strtolower($this->getApplicationName()));
				$subpackage_ls			= $this->getAppSubPackage($create['package']);
			}

			$package_dir 	 		= $this->apps_dir.$create['package'].'/';
			$application_dir 		= $package_dir.$create['application'].'/';
			$controller_dir	 		= $application_dir.'Controller/';
			$model_dir 		 		= $application_dir.'Model/';
			$view_dir 		 		= $application_dir.'View/';

			$controller_file 		= $application_dir.'Controller/default.php';
			$model_file 	 		= $application_dir.'Model/default.php';
			$route_file 	 		= $application_dir.'routing.json';
			$config_file 	 		= $application_dir.'config.json';

			if(in_array($create['application'], $subpackage_ls)){
				Cli::error("Creating application aborted, cause this application allready exist\n");
				exit();
			}

			if(_CLI_VERBOSE)
				Cli::write("Creating directories\n");

			if(!is_dir($package_dir)){
				mkdir($package_dir);
				if(_CLI_VERBOSE)
					Cli::write("  Create package directory ".Cli::green('SUCCESS')."\n");
			}

			if(!is_dir($application_dir)){
				mkdir($application_dir);
				if(_CLI_VERBOSE)
					Cli::write("  Create application directory ".Cli::green('SUCCESS')."\n");
			}

			if(!is_dir($controller_dir)){
				mkdir($controller_dir);
				if(_CLI_VERBOSE)
					Cli::write("  Create controller directory ".Cli::green('SUCCESS')."\n");
			}

			if(!is_dir($model_dir)){
				mkdir($model_dir);
				if(_CLI_VERBOSE)
					Cli::write("  Create model directory ".Cli::green('SUCCESS')."\n");
			}

			if(!is_dir($view_dir)){
				mkdir($view_dir);
				if(_CLI_VERBOSE)
					Cli::write("  Create view directory ".Cli::green('SUCCESS')."\n");
			}

			if(_CLI_VERBOSE)
				Cli::success("  Creating directories completed\n");

			Cli::write("\nConfigurating application\n");

			if(!file_exists($config_file)){
				if(($fp = fopen($config_file, 'w+'))){
					fputs($fp, "{}");
					fclose($fp);

					if(_CLI_VERBOSE)
						Cli::write("  Create config file ".Cli::green('SUCCESS')."\n");
				} else 
					if(_CLI_VERBOSE)
						Cli::write("  Create config file ".Cli::red('ERROR')."\n");	
			}

			$config = array();
			$line = Cli::prompt("  Use template engine ? (twig, native): ", false, array('twig', 'native'), "red");
			if($line=='twig'){
				$use[] = "use Helo\Library\Twig\Twig;\n";
				$config['engine'] = 'twig';
			} else if($line=='native'){
				$use[] = "use Helo\Library\HeloTPL\HeloTPL;\n";
				$config['engine'] = 'native';
			} else
				$config['engine'] = false;
			
			$line = Cli::yesORno("  Use ORM system ? (yes or no): ", false, "red");
			if(($line=='yes' || $line=='y')){
				$use[] = "use Helo\Library\Orm\Orm;\n";
				$config['orm'] = true;
			} else 
				$config['orm'] = false;

			$line = Cli::prompt("  Use cache system ? (auto, strict or no): ", false, array('auto', 'strict', 'no'), "red");
			if(($line=='auto' || $line=='strict')){
				$use[] = "use Helo\Library\Cache\Cache;\n";
				$config['cache'] = $line;
			} else 
				$config['cache'] = false;

			$line = Cli::yesORno("  Use log system ? (yes or no): ", false, "red");
			if(($line=='yes' || $line=='y')){
				$use[] = "use Helo\Library\Log\Log;\n";
				$config['log'] = true;
			} else 
				$config['log'] = false;

			$line = Cli::prompt("  Application environment ? (prod or dev): ", 'dev', array('prod', 'dev'), "red");
			if($line=='prod'){
				$config['env'] = 'prod';
			} else 
				$config['env'] = 'dev';

			$line = Cli::yesORno("  Enable application ? (yes or no): ", 'yes', "red");
			if(($line=='yes' || $line=='y')){
				$config['enable'] = true;
			} else 
				$config['enable'] = false;

			$this_config = $json['read']->read($config_file);
			foreach($config as $mode => $value){
				$this_config->{$mode} = $value;
				Cli::write("  Set \"".strtoupper($mode)."\" to \"".strtoupper(((string) $value=='0' || (string) $value=='1') ? (($value==0) ? 'false' : 'true') : $value)."\" ".Cli::green('SUCCESS')."\n");
			}

			$this_config_encode = $json['write']->full_encode($this_config);
			$json['write']->write($config_file, $this_config_encode);	

			if(_CLI_VERBOSE)
				Cli::write("  Set configuration ".Cli::green('SUCCESS')."\n");

			if(!file_exists($route_file)){
				if(($fp = fopen($route_file, 'w+'))){
					fputs($fp, "{}");
					fclose($fp);

					if(_CLI_VERBOSE)
						Cli::write("  Create routing file ".Cli::green('SUCCESS')."\n");
				} else 
					if(_CLI_VERBOSE)
						Cli::write("  Create routing file ".Cli::red('ERROR')."\n");	
			}

			if(!file_exists($controller_file)){
				$tpl = $this->getTemplate("controller");
				$tpl = str_replace('{{package}}', $create['package'],$tpl);
				$tpl = str_replace('{{application}}', $create['application'],$tpl);

				if(count($use)>0)
					$tpl = str_replace('{{use}}', implode("\t", $use), $tpl);
				else 
					$tpl = str_replace('{{use}}', "", $tpl);				

				if(($fp = fopen($controller_file, 'w+'))){
					fputs($fp, $tpl);
					fclose($fp);

					if(_CLI_VERBOSE)
						Cli::write("  Create default controller class ".Cli::green('SUCCESS')."\n");
				} else 
					if(_CLI_VERBOSE)
						Cli::write("  Create default controller class ".Cli::red('ERROR')."\n");	
			}

			if(!file_exists($model_file)){
				$tpl = $this->getTemplate("model");
				$tpl = str_replace('{{package}}', $create['package'],$tpl);
				$tpl = str_replace('{{application}}', $create['application'],$tpl);

				if(count($use)>0)
					$tpl = str_replace('{{use}}', implode("\t", $use), $tpl);
				else 
					$tpl = str_replace('{{use}}', '', $tpl);

				if(($fp = fopen($model_file, 'w+'))){
					fputs($fp, $tpl);
					fclose($fp);

					if(_CLI_VERBOSE)
						Cli::write("  Create default model class ".Cli::green('SUCCESS')."\n");
				} else 
					if(_CLI_VERBOSE)
						Cli::write("  Create default model class ".Cli::red('ERROR')."\n");	
			}

			Cli::success("\nApplication created\n");
		}

		private function getPackageName($pls=false){
			$line = Cli::prompt('Set the package name: ', false, $pls, 'brown');
			if($line==null){
				Cli::error("The package name could not be null\n");
				return $this->getPackageName($pls);
			}
			return $line;
		}

		private function getExistPackageName($pls=false){
			$line = Cli::prompt('Set the package name: ', false, $pls, 'brown');
			if($line==null){
				Cli::error("The package name could not be null\n");
				return $this->getExistPackageName($pls);
			} else {
				if($pls && !in_array($line, $pls)){
					Cli::error("The package must exist\n");
					return $this->getExistPackageName($pls);
				}
			}
			return $line;
		}

		private function getApplicationName($pls=false){
			$line = Cli::prompt('Set the application name: ', false, $pls, 'brown');
			if($line==null){
				Cli::error("The application name could not be null\n");
				return $this->getApplicationName($pls);
			}
			return $line;
		}

		private function getExistApplicationName($pls=false){
			$line = Cli::prompt('Set the application name: ', false, $pls, 'brown');
			if($line==null){
				Cli::error("The application name could not be null\n");
				return $this->getExistApplicationName($pls);
			} else {
				if($pls && !in_array($line, $pls)){
					Cli::error("The application must exist\n");
					return $this->getExistApplicationName($pls);
				}
			}
			return $line;
		}

		private function getTemplate($name){
			$tpl_dir = getcwd().'/src/vendor/console/templates/';
			if(file_exists(($file = $tpl_dir.$name.'.tpl'))){
				return file_get_contents($file);
			}
		}

		private function getAppSubPackage($pls){
			$p = array();
			if(is_dir($this->apps_dir.$pls.'/')){
				if ($handle = opendir($this->apps_dir.$pls.'/'))
					while (false !== ($entry = readdir($handle)))
						if($entry!='.' && $entry!='..' && is_dir($this->apps_dir.$pls.'/'.$entry))
							$p[] = $entry;
			}
			return (count($p)>0) ? $p : false;
		}

		private function getAppPackage(){
			$p = array();
			if(is_dir($this->apps_dir)){
				if ($handle = opendir($this->apps_dir))
					while (false !== ($entry = readdir($handle)))
						if($entry!='.' && $entry!='..' && is_dir($this->apps_dir.$entry))
							$p[] = $entry;
			}
			return (count($p)>0) ? $p : false;
		}

		private function rmdir_recursive($dir) {
		    if (!file_exists($dir)) return true;
		    if (!is_dir($dir)) return unlink($dir);
		    foreach (scandir($dir) as $item) {
		        if ($item == '.' || $item == '..') continue;
		        if (!$this->rmdir_recursive($dir.'/'.$item)) return false;
		    }
		    return rmdir($dir);
		}

		private function helper($command=false){
			if(!$command){
				echo "usage: console/application [--verbose] [--help] [--force] COMMAND [ARGS]\n\n";
				echo "The most commonly console/cache git commands are:\n";
				echo "  create\tCreate new application\n";
				echo "  delete\tDelete application\n";
				echo "  disable\tDisable application\n";

				echo "\nSee 'console/cache --help COMMAND' for more information on a specific command.\n";
			} else {
				print_r($command);
			}
		}
	}

?>