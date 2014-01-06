<?php
	
	namespace Helo\Library\Scssphp;

	class Scssphp {

		private $scss;
		private $caching;

		public function __construct($caching)
		{
			require_once "scss.inc.php";
		
			$this->scss = new \scssc();
		}

		public function compile($input, $output=false)
		{
			if (is_file($input)) {
				$out = $this->scss->compile(file_get_contents($input));
			} else {
				$out = $this->scss->compile($input);
			}

			if ($output) {
				return $this->saveOutput($out, $output);	
			} else { 
				return $out;
			}
		}

		public function importPaths($path)
		{
			if (is_dir($path)) {
				$this->scss->setImportPaths($path);
			}
		}

		public function saveOutput($content, $file)
		{
			return file_put_contents($file, $content);
		}

		public function getLessphp()
		{
			return $this->scss;
		}
	}

?>