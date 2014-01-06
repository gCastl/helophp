<?php

	namespace Helo\Vendor\Console;

	class ConsoleCache {

		private $argv;
		private $cache_dir;
		private $config_dir;
		private $access;
		private $attempt = 1;
		private $command = array(
			'clean',
			'clear',
			'config',
			'enable',
			'exit',
			'help',
			'history',
			'list',
			'ls',
			'mode'
		);

		public function __construct($argv){
			$this->argv = $argv;
			$this->cache_dir  = getcwd().'/src/cache/';
			$this->config_dir  = getcwd().'/src/config/';

			if(_CLI_HELP){
				$this->help();
				exit;
			}
		}

		public function run(){
			require_once getcwd().'/src/library/Json/JsonReader.php';
			$json['read'] = new \Helo\Library\JsonReader\JsonReader();
			$this->access  = $json['read']->read($this->config_dir.'console_access.json');

			if($this->access->enable){
				if(isset($this->access->users->{_CLI_USER}))
					$this->get_password();
				else {
					Cli::write("Access denied\n");
					exit;
				}
			} else 
				$this->welcome();		
		}

		public function get_password(){
			$this->attempt++;
			Cli::password("Password: ", false, 'md5', function($password){
				if($this->access->users->{_CLI_USER}==$password)
					$this->welcome();
				else {
					if($this->attempt<=3) {
						Cli::write("\nAccess denied\n");
						$this->get_password();
					} else 
						Cli::write("\nAccess denied, connexion aborted\n");
				}
			});
		}

		private function welcome(){
			Cli::clear();
			Cli::write("Welcome to the cache monitor\n");
			Cli::write("Type 'help' for help and 'command -h' for specific help\n\n");
			$this->listen();
		}

		private function listen(){
			Cli::listen(Cli::brown("cache>")." ", $this->command, false, function($cmd, $arg, $opt){
				if(in_array($cmd, $this->command))
					if(is_callable(array($this, 'cache_'.$cmd)))
						$this->{'cache_'.$cmd}($arg, $opt);
				
				$this->listen();
			});
		}

		private function cache_history($arg){
			print_r($arg);
		}

		private function cache_config($arg, $opt){
			if(in_array('help', $opt))
				return $this->cache_help('config', $arg);

			if(count($arg)>0){
				require_once getcwd().'/src/library/Json/JsonReader.php';
				require_once getcwd().'/src/library/Json/JsonWritter.php';
				
				$json['read']  = new \Helo\Library\JsonReader\JsonReader();
				$json['write'] = new \Helo\Library\JsonWritter\JsonWritter();

				if($arg[0]=='show') {
					$this_config = $json['read']->read($this->cache_dir.'config.json');
					$this_config_encode = $json['write']->full_encode($this_config);
					Cli::write($this_config_encode);
				} else if($arg[0]=='get'){
					if(isset($arg[1])){
						$this_config = $json['read']->read($this->cache_dir.'config.json');
						if(isset($this_config->{$arg[1]})){
							$value = (string) $this_config->{$arg[1]};
							if($value=='1')
								Cli::write("true\n");
							else if($value=='0')
								Cli::write("false\n");
							else
								Cli::write($value."\n");
						} else
							Cli::write("The argument ".$arg[1]." is unknown\n");
					} else {
						Cli::write("This command need 2 arguements, argument 2 missing. Type -h for help\n");
						return false;
					}
				} else if($arg[0]=='set'){
					if(isset($arg[1])){
						if(isset($arg[2])){
							$this_config = $json['read']->read($this->cache_dir.'config.json');
							$this_config->{$arg[1]} = $arg[2];
							$this_config_encode = $json['write']->full_encode($this_config);
							$json['write']->write($this->cache_dir.'config.json', $this_config_encode);
						} else {
							Cli::write("This command need 3 arguements, argument 3 missing. Type -h for help\n");
							return false;
						}
					} else {
						Cli::write("This command need 3 arguements, argument 2 missing. Type -h for help\n");
						return false;
					}
				} else {
					Cli::write("Unknown argument, type -h for help\n");
					return false;
				}
			} else {
				Cli::write("Missing argument, type -h for help\n");
				return false;
			}
		}

		private function cache_enable($arg){
			if(count($arg)>0){
				require_once getcwd().'/src/library/Json/JsonReader.php';
				require_once getcwd().'/src/library/Json/JsonWritter.php';
				
				$json['read']  = new \Helo\Library\JsonReader\JsonReader();
				$json['write'] = new \Helo\Library\JsonWritter\JsonWritter();
				$this_config   = $json['read']->read($this->cache_dir.'config.json');

				if($arg[0]=='true')
					$this_config->enable = true;
				else if($arg[0]=='false')
					$this_config->enable = false;
				else {
					Cli::write("Enable value is incorrect\n");
					return false;
				}

				$this_config_encode = $json['write']->full_encode($this_config);
				$json['write']->write($this->cache_dir.'config.json', $this_config_encode);
			}
		}

		private function cache_mode($arg){
			if(count($arg)>0){
				require_once getcwd().'/src/library/Json/JsonReader.php';
				require_once getcwd().'/src/library/Json/JsonWritter.php';
				
				$json['read']  = new \Helo\Library\JsonReader\JsonReader();
				$json['write'] = new \Helo\Library\JsonWritter\JsonWritter();
				$this_config   = $json['read']->read($this->cache_dir.'config.json');

				if($arg[0]=='auto')
					$this_config->mode = 'auto';
				else if($arg[0]=='strict')
					$this_config->mode = 'strict';
				else {
					Cli::write("Enable mode is incorrect\n");
					return false;
				}

				$this_config_encode = $json['write']->full_encode($this_config);
				$json['write']->write($this->cache_dir.'config.json', $this_config_encode);
			}
		}

		private function cache_clean($arg, $opt){
			$cache_find = array();

			if(count($arg)==0) {
				if(is_dir($this->cache_dir))
					if ($handle = opendir($this->cache_dir))
						while (false !== ($entry = readdir($handle)))
					        if($entry!='.' && $entry!='..' && $entry!='config.json')
					        	$cache_find[] = $entry;
			} else
				foreach($arg as $a){
					if(preg_match('/^\-([a-zA-Z]+)\=\"([a-zA-Z_.0-9]+)\"$/i', $a, $matches)){
						if($matches[1]=='type'){
							$hash = hash('crc32', $matches[2]);
							if(is_dir($this->cache_dir))
								if ($handle = opendir($this->cache_dir))
									while (false !== ($entry = readdir($handle)))
								        if($entry!='.' && $entry!='..' && $entry!='config.json'){
								        	list($cache_hash, $cache_value) = explode('.', $entry);
								        	if($hash==$cache_hash)
								        		$cache_find[] = $entry;
								        }
								        	
						} else if($matches[1]=='file'){
							if(file_exists($this->cache_dir.$matches[2]))
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
							Cli::write("Delete: ".$cf." ");
						else {
							$lf = ($i==$n-1) ? "\n" : "\r";
							Cli::write("Deleting file".(($n>1) ? 's': '').": ".round((($i+1)*100)/$n)."%   ".$lf);
						}

						unlink($this->cache_dir.$cf);

						if(in_array('verbose', $opt)){
							Cli::write("> ");
							Cli::success("OK\n");
						}

						$i++;
					}

					Cli::success("Deleting file".(($n>1) ? 's': '')." completed\n");
				} else 
					Cli::write("Cache directory cleaning aborted\n");
			} else
				Cli::write("Cache directory allready clean\n");
		}

		private function cache_ls($arg, $opt){
			$this->cache_list($arg, $opt);
		}

		private function cache_list($arg, $opt){
			if(count($arg)==0){
				if(is_dir($this->cache_dir))
					if ($handle = opendir($this->cache_dir))
						while (false !== ($entry = readdir($handle)))
					        if($entry!='.' && $entry!='..' && $entry!='config.json')
					        	$cache_find[] = $entry;
			} else {

			}

			$list = array();
			if(count($cache_find)>0){
				$i = 0; $max = 0;
				foreach($cache_find as $cache){
					if(in_array('l', $opt)){
						$lists[$i]['perms'] = $this->get_perm($this->cache_dir.$cache)."  ";
						$lists[$i]['owner'] = fileowner($this->cache_dir.$cache).' '.filegroup($this->cache_dir.$cache)."  ";
						
						$size = filesize($this->cache_dir.$cache);
						$len  = strlen((string) $size);
						if($len>$max)
							$max = $len;
						$lists[$i]['size']  = $size;
						
						$lists[$i]['date']  = date ("M d Y H:i", fileatime($this->cache_dir.$cache))."  ";
					}
					$lists[$i]['filename'] = (in_array('l', $opt)) ? ((fileperms($this->cache_dir.$cache)==33279) ? Cli::green($cache) : $cache) : $cache;
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
					Cli::write("\n"); 
				}
			}
		}

		private function get_perm($file){
			$perms = fileperms($file);

			if (($perms & 0xC000) == 0xC000) {
			    $info = 's';
			} elseif (($perms & 0xA000) == 0xA000) {
			    $info = 'l';
			} elseif (($perms & 0x8000) == 0x8000) {
			    $info = '-';
			} elseif (($perms & 0x6000) == 0x6000) {
			    $info = 'b';
			} elseif (($perms & 0x4000) == 0x4000) {
			    $info = 'd';
			} elseif (($perms & 0x2000) == 0x2000) {
			    $info = 'c';
			} elseif (($perms & 0x1000) == 0x1000) {
			    $info = 'p';
			} else {
			    $info = 'u';
			}

			// Owner
			$info .= (($perms & 0x0100) ? 'r' : '-');
			$info .= (($perms & 0x0080) ? 'w' : '-');
			$info .= (($perms & 0x0040) ?
			            (($perms & 0x0800) ? 's' : 'x' ) :
			            (($perms & 0x0800) ? 'S' : '-'));

			// Group
			$info .= (($perms & 0x0020) ? 'r' : '-');
			$info .= (($perms & 0x0010) ? 'w' : '-');
			$info .= (($perms & 0x0008) ?
			            (($perms & 0x0400) ? 's' : 'x' ) :
			            (($perms & 0x0400) ? 'S' : '-'));

			// World
			$info .= (($perms & 0x0004) ? 'r' : '-');
			$info .= (($perms & 0x0002) ? 'w' : '-');
			$info .= (($perms & 0x0001) ?
			            (($perms & 0x0200) ? 't' : 'x' ) :
			            (($perms & 0x0200) ? 'T' : '-'));

			return $info;
		}

		private function cache_clear($arg=false){
			Cli::clear();
		}

		private function cache_exit($arg){
			Cli::write("Bye\n");
			exit;
		}

		private function cache_help($cmd=false, $arg=false){
			if(!$cmd){
				// GLOBAL HELP
				Cli::write("Ok\n");
			} else {
				// SPECIFIC HELP
				switch($cmd){
					case 'config':
						if(count($arg)==0){
							Cli::write("The 'config' command is used to show, set or delete cache config\n");
							Cli::write("Usage: config command [arguments] [-f|--force] [-v|--verbose] [-r|--recursive] [-h|--help]\n\n");
							Cli::write("Available command:\n");
							Cli::write("  delete   Delete an config option \n");
							Cli::write("  get\t   Get the value of an option \n");
							Cli::write("  set\t   Set the value of an option \n");
							Cli::write("  show\t   Show the config file \n");
						} else {
							switch($arg[0]){
								case 'delete':
									Cli::write("The 'config delete' command is used to delete an option on cache config\n");
									Cli::write("Usage: config delete option [-f|--force] [-v|--verbose] [-h|--help]\n");
									Cli::write("Option must be an existing and valid option\n");
									Cli::write("Options 'enable' and 'mode' are bloked, you cannot delete them\n");
									break;

								case 'get':
									Cli::write("The 'config get' command is used to get the value of an option\n");
									Cli::write("Usage: config get option [-f|--force] [-v|--verbose] [-h|--help]\n");
									Cli::write("Option must be an existing and valid option\n");
									Cli::write("The option value is printed on the console\n");
									break;

								case 'set':
									Cli::write("The 'config get' command is used to set the value of an option\n");
									Cli::write("Usage: config set option value [-f|--force] [-v|--verbose] [-h|--help]\n");
									Cli::write("If the option does not exist it is added\n");
									break;

								case 'show':
									Cli::write("The 'config show' command is used to show the config file\n");
									Cli::write("Usage: config show [-f|--force] [-v|--verbose] [-h|--help]\n");
									Cli::write("The config file is printed on the console\n");
									break;
							}
						}
						break;
				}
			}
			return false;
		}
	}

?>