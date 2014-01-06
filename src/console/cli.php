<?php

	namespace Helo\Console;

	class Cli {

		static $autocomplete;

		/**
		 * Clear the console screen
		 **/
        public static function clear(){
        	passthru('clear');
        }


        /**
		 * Print text on screen
		 **/
		public static function write($text, $color=false, $back=false){
			if($color)
				echo Color::color($text, $color, $back);
			else 
				echo $text;
			echo "\n";
		}

		/**
		 * Print text on screen
		 **/
		public static function swrite($text, $color=false, $back=false){
			if($color)
				echo Color::color($text, $color, $back);
			else 
				echo $text;
		}


		/**
		 * Print text on screen with red color
		 **/
		public static function error($text){
			echo Color::color($text, 'red');
		}


		/**
		 * Print text on screen green color
		 **/
		public static function success($text){
			echo Color::color($text, 'green');
		}


		/**
		 * Print text on screen
		 **/
		public static function print_c($text){
			$args = func_get_args();
			unset($args[0]);
			$args = array_values($args);

			$i = 0;
			while(($pos = strpos($text, '%s'))!==false)
				$text = substr_replace($text, $args[$i++], $pos, 2);

			echo $text;
		}


		/**
		 * Get hidden and crypt text
		 **/
		public static function password($text, $color, $crypt='md5', $callback){
			self::write($text);
			shell_exec('stty -echo');
			$password = fgets(STDIN);
			shell_exec('stty echo');

			$password = str_replace(CHR(10), '', $password);
			if(is_callable($crypt)) {
				if(is_array($crypt))
					$password = $crypt[0]->{$crypt[1]}($password);
				else
					$password = $crypt($password);
			} else
				$password = hash($crypt, $password);
				
			if(is_callable($callback))
				if(is_array($callback))
					$callback[0]->{$callback[1]}($password);
				else 
					$callback($password);
			else
				return $password;
		}


		/**
		 * Ask if yes or no
		 **/
		public static function yesORno($text, $instance=false, $color='white'){
			readline_completion_function(function(){
				return array('yes', 'no');
			});

			if($instance)
				echo $text.Color::color($instance, 'brown')."\r";

			$color = Color::getColor($color);
			$line  = readline($text."\033[".$color."m");
			if($line==null && $instance)
				$line = $instance;

			echo "\033[0m";
			return $line;
		}


		/**
		 * Ask if true of false
		 **/
		public static function trueORfalse($text, $instance=false, $color='white'){
			readline_completion_function(function(){
				return array('true', 'false');
			});

			if($instance)
				echo $text.Color::color($instance, 'brown')."\r";

			$color = Color::getColor($color);
			$line  = readline($text."\033[".$color."m");
			if($line==null && $instance)
				$line = $instance;

			echo "\033[0m";
			return $line;
		}

		/**
		 * Listen write command
		 **/
		public static function listen($text, $auto, $color, $callback){
			if($auto) {
				self::$autocomplete = $auto;
				readline_completion_function(function(){
					return self::$autocomplete;
				});
			}

			if($color!='white' && $color!=false){
				$color = Color::getColor($color);
				$line = readline($text);
			} else
				$line = readline($text);

			self::save_history($line);

			$cmd = null;
			$opt = $arg = array();
			foreach(explode(' ', $line) as $a){
				if($cmd==null)
					$cmd = $a;
				else {
					if(preg_match('/^\-([a-zA-Z]+)$/', $a)){
						$o = str_replace('-', '', $a);
						$o = str_split($o);
						foreach($o as $oo)
							$opt[] = self::options(strtolower($oo));
					} else 
						$arg[] = $a;
				}
			}

			if(is_callable($callback))
				if(is_array($callback))
					$callback[0]->{$callback[1]}($cmd, $arg, $opt);
				else
					$callback($cmd, $arg, $opt);
			else 
				return array(
					'cmd' => $cmd,
					'arg' => $arg,
					'opt' => $opt
				);
		}


		/**
		 * Get the option equivalent
		 **/
		public static function options($opt){
			if($opt=='v')
				return 'verbose';
			else if($opt=='f')
				return 'force';
			else if($opt=='r')
				return 'recursive';
			else if($opt=='h')
				return 'help';
			else if($opt=='p')
				return 'password';
			else if($opt=='i')
				return 'ignore';
			else 
				return $opt;
		}


		/**
		 * Read and set the command history
		 **/
		public static function read_history(){
			$history_file = getcwd().'/src/console/history/'.hash('crc32', '_HISTORY');
			readline_read_history($history_file);
		} 
		

		/**
		 * Get and save the command history
		 **/
		public static function save_history($line){
			$history_file = getcwd().'/src/console/history/'.hash('crc32', '_HISTORY');
			readline_add_history($line);
			readline_write_history($history_file);
		} 


		/**
		 * Ask any question
		 **/
		public static function prompt($text, $instance=false, $auto=false, $color='white'){
			if($auto) {
				self::$autocomplete = $auto;
				readline_completion_function(function(){
					return self::$autocomplete;
				});
			}

			if($instance)
				echo $text.Color::color($instance, 'brown')."\r";

			$color = Color::getColor($color);
			
			$line = readline($text."\033[".$color."m");
			if($line==null && $instance)
				$line = $instance;

			echo "\033[0m";
			return $line;
		}
	}

?>