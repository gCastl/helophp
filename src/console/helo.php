<?php

	namespace Helo\Console;

	class ConsoleHelo extends Console {

		static $JsonReader;
		static $Network;
		static $JsonWritter;
		static $Cache;
		static $File;

		private $access;
		private $attempt = 1;

		public function __construct($argv){
			Cli::read_history();
			$this->load_library();
			$this->instanciate_library();
			$this->get_access_file();

			if(_CLI_HELP){
				Help::all();
				exit;
			}
		}

		public function init(){
			if($this->access->enable){
				if(isset($this->access->users->{_CLI_USER}))
					$this->request_password();
				else {
					Cli::write("Access denied");
					exit;
				}
			} else 
				$this->welcome();
		}

		private function welcome(){
			Cli::clear();
			Cli::write("Welcome to the 'helo' monitor");
			Cli::write("Type 'help' for help and 'command -h' for specific help\n");
			$this->listen();
		}

		private function doit($cmd, $args=false, $opt=false, $imp=false){
			$cmd = str_replace('-', '_', $cmd);
			if($imp){
				$imp = $args[0];
				unset($args[0]);
				$args = array_values($args);
				$cmd_fn = 'cmd_'.$cmd.'_'.$imp;
			} else {
				$cmd_fn = 'cmd_'.$cmd;
			}

			if(is_callable(array($this, $cmd_fn)))
				$this->{$cmd_fn}($args, $opt);
		}

		private function listen(){
			Cli::listen("helo> ", Command::$shortcut, false, function($cmd, $arg, $opt){
				if(array_key_exists($cmd, Command::$available)){
					$cmd_conf = Command::$available[$cmd];

					if($cmd_conf==false && count($arg)==0)
						$this->doit($cmd, false, $opt);
					else {
						if(count($cmd_conf)==2){
							$this->control_cmd($cmd_conf, $cmd, $arg, $opt);
						} else {
							if(count($arg)>0){
								if(array_key_exists($arg[0], $cmd_conf)){ 
									$this->control_cmd($cmd_conf[$arg[0]], $cmd, $arg, $opt, $arg[0]);
								} else
									Help::argument_unknown($cmd);
							} else 
								Help::argument_failure(1, 0, $cmd);
						}
					}
				} else if(array_key_exists($cmd, Command::$personnal)){

				}
				$this->listen();
			});
		}

		/** COMMANDE FUNCTION **/
		private function cmd_clear($opt=false){
			Cli::clear();
		}

		private function cmd_history_clear($opt=false){
			
		}

		private function cmd_cmd_ls($opt=false){
			Command::ls($opt);
		}

		private function cmd_cmd_add($opt=false){
			
		}

		private function cmd_cmd_delete($opt=false){
			
		}

		// CACHE
		private function cmd_cache_ls($args=false, $opt=false){
			$cache_find = array();
			if(is_dir(parent::$cache_dir))
				if ($handle = opendir(parent::$cache_dir))
					while (false !== ($entry = readdir($handle)))
				        if($entry!='.' && $entry!='..')
				        	$cache_find[] = $entry;

			$list = array();
			if(count($cache_find)>0){
				$i = 0; $max = 0;
				foreach($cache_find as $cache){
					if(in_array('l', $opt)){
						$lists[$i]['perms'] = $this->get_perm(parent::$cache_dir.$cache)."  ";
						$lists[$i]['owner'] = fileowner(parent::$cache_dir.$cache).' '.filegroup(parent::$cache_dir.$cache)."  ";
						
						$size = filesize(parent::$cache_dir.$cache);
						$len  = strlen((string) $size);
						if($len>$max)
							$max = $len;
						$lists[$i]['size']  = $size;
						
						$lists[$i]['date']  = date ("M d Y H:i", fileatime(parent::$cache_dir.$cache))."  ";
					}
					$lists[$i]['filename'] = (in_array('l', $opt)) ? ((fileperms(parent::$cache_dir.$cache)==33279) ? Cli::green($cache) : $cache) : $cache;
					$i++;
				}

				foreach($lists as $list){
					foreach($list as $key => $val){
						if($key=='size'){
							$len = strlen((string) $val);
							if($len<$max){
								$diff = $max-$len;
								for($i=0;$i<$diff;$i++)
									$val = " ".$val;
							}

							$val = $val."  ";
						}
						Cli::write($val);
					}
				}
			}
		}

		private function cmd_cache_config_delete($args=false, $opt=false){
			$this_config = self::$JsonReader->read(parent::$config_dir.'cache.json');
			if(isset($this_config->{$args[0]})){
				unset($this_config->{$args[0]});
				$this_config_encode = self::$JsonWritter->full_encode($this_config);
				Cli::write($this_config_encode);
			} else
				Cli::write("The argument '".$args[0]."' is unknown");
		}

		private function cmd_cache_config_set($args=false, $opt=false){
			$this_config = self::$JsonReader->read(parent::$config_dir.'cache.json');
			$this_config->{$args[0]} = $args[1];
			$this_config_encode = self::$JsonWritter->full_encode($this_config);
			self::$JsonWritter->write(parent::$config_dir.'cache.json', $this_config_encode);
		}

		private function cmd_cache_config_get($args=false, $opt=false){
			$this_config = self::$JsonReader->read(parent::$config_dir.'cache.json');
			if(isset($this_config->{$args[0]})){
				$value = (string) $this_config->{$args[0]};
				if($value=='1')
					Cli::write("true");
				else if($value=='0')
					Cli::write("false");
				else
					Cli::write($value);
			} else
				Cli::write("The argument '".$args[0]."' is unknown");
		}

		private function cmd_cache_config_view($args=false, $opt=false){
			$this_config   = self::$JsonReader->read(parent::$config_dir.'cache.json');
			$this_config_encode = str_replace("\t", "  ", self::$JsonWritter->full_encode($this_config));
			Cli::swrite($this_config_encode);
		}

		private function cmd_cache_enable($args=false, $opt=false){
			$this_config   = self::$JsonReader->read(parent::$config_dir.'cache.json');

			if($args[0]=='true')
				$this_config->enable = true;
			else if($args[0]=='false')
				$this_config->enable = false;
			else {
				Cli::write("Enable value is incorrect\n");
				return false;
			}

			$this_config_encode = self::$JsonWritter->full_encode($this_config);
			self::$JsonWritter->write(parent::$config_dir.'cache.json', $this_config_encode);
		}

		private function cmd_cache_clear($args=false, $opt=false){
			$cache_find = array();
			if($args==false || count($args)==0) {
				if(is_dir(parent::$cache_dir))
					if ($handle = opendir(parent::$cache_dir))
						while (false !== ($entry = readdir($handle)))
					        if($entry!='.' && $entry!='..')
					        	$cache_find[] = $entry;
			} else
				foreach($args as $a){
					if(preg_match('/^\-([a-zA-Z]+)\=\"([a-zA-Z_.0-9]+)\"$/i', $a, $matches)){
						if($matches[1]=='type'){
							$hash = hash('crc32', $matches[2]);
							if(is_dir(parent::$cache_dir))
								if ($handle = opendir(parent::$cache_dir))
									while (false !== ($entry = readdir($handle)))
								        if($entry!='.' && $entry!='..' && $entry!='config.json'){
								        	list($cache_hash, $cache_value) = explode('.', $entry);
								        	if($hash==$cache_hash)
								        		$cache_find[] = $entry;
								        }
								        	
						} else if($matches[1]=='file'){
							if(file_exists(parent::$cache_dir.$matches[2]))
								$cache_find[] = $matches[2];
						}
					}
				}

			if(count($cache_find)>0){
				$n = count($cache_find);
				Cli::write("".$n." cache file".(($n>1) ? 's': '')." was found\n");
				
				if(in_array('verbose', $opt)){
					foreach($cache_find as $cf)
						Cli::write("    ".$cf."\n", 'brown');
					Cli::write("\n");
				}

				if(!in_array('force', $opt)){
					$line = Cli::yesORno("Do you want really delete this file".(($n>1) ? 's': '')." ? (yes or no): ", false, "brown");
				} else 
					$line = 'yes';

				if(trim($line)=='yes' || trim($line)=='y'){
					$i = 0;
					foreach($cache_find as $cf){
						if(in_array('verbose', $opt))
							Cli::swrite("Delete: ".$cf." ");
						else {
							$lf = ($i==$n-1) ? "\n" : "\r";
							Cli::swrite("Deleting file".(($n>1) ? 's': '').": ".round((($i+1)*100)/$n)."%   ".$lf);
						}

						unlink(parent::$cache_dir.$cf);

						if(in_array('verbose', $opt)){
							Cli::swrite("> ");
							Cli::success("OK\n");
						}

						$i++;
					}

					Cli::success("Deleting file".(($n>1) ? 's': '')." completed\n");
				} else 
					Cli::write("Cache directory cleaning aborted");
			} else
				Cli::write("Cache directory allready clean");
		}

		private function cmd_cache_mode($args=false, $opt=false){
			$this_config   = self::$JsonReader->read(parent::$config_dir.'cache.json');

			if($args[0]=='auto')
				$this_config->mode = 'auto';
			else if($args[0]=='strict')
				$this_config->mode = 'strict';
			else {
				Cli::write("Enable mode is incorrect\n");
				return false;
			}

			$this_config_encode = self::$JsonWritter->full_encode($this_config);
			self::$JsonWritter->write(parent::$config_dir.'cache.json', $this_config_encode);
		}

		private function cmd_cache_build($args=false, $opt=false){
			Cli::write("DEV");
		}


		// APPS
		private function cmd_apps_enable($args=false, $opt=false){

		}

		private function cmd_apps_create($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_apps_delete($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_apps_rename($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_apps_clone($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_apps_ls($args=false, $opt=false){
			Cli::write("OK");
		}
		

		// USERS
		private function cmd_users_enable($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_users_rename($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_users_create($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_users_delete($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_users_ls($args=false, $opt=false){
			Cli::write("OK");
		}

		private function cmd_users_perms($args=false, $opt=false){
			Cli::write("OK");
		}
		/***********************/

		private function request_password(){
			$this->attempt++;
			Cli::password("Password: ", false, 'md5', function($password){
				if($this->access->users->{_CLI_USER}==$password)
					$this->welcome();
				else {
					if($this->attempt<=3) {
						Cli::write("\nAccess denied");
						$this->request_password();
					} else 
						Cli::write("\nAccess denied, connexion aborted");
				}
			});
		}

		private function control_cmd($cmd_conf, $cmd, $arg, $opt, $imp=false){
			list($narg, $farg) = $cmd_conf;
			if($imp)
				$narg++;

			if(!$farg)
				$this->doit($cmd, $arg, $opt, $imp);
			else if($farg && count($arg)==$narg)
				$this->doit($cmd, $arg, $opt, $imp);
			else
				Help::argument_failure($narg, count($arg), $cmd);
		}


		private function get_access_file(){
			$this->access  = self::$JsonReader->read(parent::$config_dir.'console_access.json');
		}

		private function instanciate_library(){
			self::$JsonReader 	= parent::$json_reader;
			self::$Network 		= new \Helo\Library\Network\Network();
			self::$JsonWritter  = new \Helo\Library\JsonWritter\JsonWritter();
			self::$Cache 		= new \Helo\Library\Cache\Cache();
			self::$File 		= new \Helo\Library\File\File();
		}
	}

?>