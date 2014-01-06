<?php

	namespace Helo\Console;

	class Help {

		public static function all(){
			Cli::write('OK');
		}

		public static function argument_failure($n1, $n2, $cmd){
			Cli::write("This command need ".$n1." argument".(($n1>1) ? 's' : '').", ".$n2." argument".(($n2>1) ? 's' : '')." passed");
		}
	
		public static function argument_unknown($cmd){
			Cli::write("This is not valid argument for the '".$cmd."' command");
		}
	}

?>