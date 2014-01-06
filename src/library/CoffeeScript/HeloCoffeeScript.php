<?php
	
	namespace Helo\Library\CoffeeScript;

	class CoffeeScript {

		private $coffee;

		public function __construct($dir)
		{
			require_once "vendor/src/Init.php";

			\CoffeeScript\Init::load();
		}

		public function compile($input, $output=false)
		{
			if (is_file($input)) {
				$src = file_get_contents($input);
			}
			
			$js = \CoffeeScript\Compiler::compile($src, array(
				'filename' => $input,
			 	'header' => ''
			));

			if ($output) {
				file_put_contents($output, $js);
			} else {
				return $js;
			}
		}
	}

?>