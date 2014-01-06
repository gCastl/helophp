<?php
	
	namespace Helo\Console;

	class Command {

		static $personnal = array();

		static $available = array(
			/* CACHE COMMAND */
			'cache-config' 	=> array(
				'set' 		=> array(2,true),	// name - value
				'delete'	=> array(1,true), 	// name
				'get'		=> array(1,true), 	// name
				'view'		=> false
			),

			'cache-enable' 	=> array(1,true),
			'cache-clear' 	=> false,
			'cache-mode' 	=> array(1,true), 	// value
			'cache-ls' 		=> false,
			'cache-build' 	=> array(1,true), 	// value
			/*****************/

			/* APPS COMMAND */
			'apps-enable'	=> array(2, true), 	// appname - [true or false]
			'apps-create'	=> array(1, false), // appname
			'apps-delete'	=> array(1, true), 	// appname
			'apps-rename'	=> array(2, true), 	// appname - new appname
			'apps-clone'	=> array(2, true), 	// appname - clone appname
			'apps-ls' 		=> false,
			/****************/

			/* USERS COMMAND */
			'users-enable'	=> array(2, true), 	// username - [true or false]
			'users-rename'	=> array(2, true), 	// username - new username
			'users-create'	=> array(1, false), // username
			'users-delete'	=> array(1, true), 	// username
			'users-ls' 		=> false,
			'users-perms'	=> array(1, true), 	// username
			/*****************/

			/* OTHER COMMAND */
			'clear'			=> false,
			'history-clear'	=> false,
			'cmd-ls'		=> false,
			'cmd-add'		=> array(2, true),	// commandname - commanddir
			'cmd-delete'	=> array(1, true),  // commandname
			/*****************/
		);

		static $description = array(
			'cache-config' 	=> 'View, get, set or delete config options',
			'cache-enable' 	=> 'Enable or disable the cache system',
			'cache-clear' 	=> 'Erase cache files',
			'cache-mode'	=> 'Set the cache mode [auto, strict]',
			'cache-ls'		=> 'List the cache files',
			'cache-build'	=> 'Create cache files
			',

			'apps-enable'	=> 'ok',
			'apps-create'	=> 'ok',
			'apps-delete'	=> 'ok',
			'apps-rename'	=> 'ok',
			'apps-clone'	=> 'ok',
			'apps-ls'		=> 'ok', 	

			'users-enable'	=> 'ok',
			'users-rename'	=> 'ok',
			'users-create'	=> 'ok',
			'users-delete'	=> 'ok',
			'users-ls'		=> 'ok',
			'users-perms'	=> 'ok',

			'clear'			=> 'ok',			
			'history-clear'	=> 'ok',	
			'cmd-ls'		=> 'ok',		
			'cmd-add'		=> 'ok',		
			'cmd-delete'	=> 'ok'	
		);


		static $shortcut = array(
			'cache-config',
			'cache-enable',
			'cache-clear',
			'cache-mode',
			'cache-ls',
			'cache-build',

			'apps-enable',
			'apps-create',
			'apps-delete',
			'apps-rename',
			'apps-clone',
			'apps-ls', 	

			'users-enable',
			'users-rename',
			'users-create',
			'users-delete',
			'users-ls',
			'users-perms',

			'clear',	
			'history-clear',	
			'cmd-ls',		
			'cmd-add',		
			'cmd-delete',	
		);

		public static function ls($opt){
			Cli::write("Native command:");
			foreach(self::$available as $available => $args){
				$cmd = "  ".$available;

				if(in_array('verbose', $opt) && array_key_exists($available, self::$description))
					$cmd .= "\t".self::$description[$available];

				Cli::write($cmd);
			}

			if(count(self::$personnal)>0){
				Cli::write("Native command:");
				foreach(self::$personnal as $personnal => $args){
					$cmd = "  ".$personnal;

					if(in_array('verbose', $opt) && array_key_exists($personnal, self::$description))
						$cmd .= "\t".self::$description[$personnal];

					Cli::write($cmd);
				}
			}
		}

	}

?>