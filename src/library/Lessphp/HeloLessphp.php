<?php
	
	namespace Helo\Library\Lessphp;

	class Lessphp
	{

		private $less;
		private $caching;

		public function __construct($caching)
		{
			require_once "lessc.inc.php";
		
			$this->less = new \lessc();
		}

		public function setImportDir($dir)
		{
			if (is_array($dir)) {
				foreach ($dir as $k => $d) {
					if (!is_dir($d)) {
						unset($dir[$k]);
					}
				}

				$this->less->setImportDir($dir);
			} else {
				if (is_dir($dir)) {
					$this->less->setImportDir($dir);
				}
			}
		}

		public function addImportDir($dir)
		{
			if (is_array($dir)) {
				foreach ($dir as $k => $d) {
					if (is_dir($d)) {
						$this->less->addImportDir($d);
					}
				}
			} else { 
				if (is_dir($dir)) {
					$this->less->addImportDir($dir);
				}
			}

		}

		public function compile($input, $output=false)
		{
			if (is_file($input)) {
				$out = $this->less->compile(file_get_contents($input));
			} else {
				$out = $this->less->compile($input);
			}

			if ($output) {
				return $this->saveOutput($out, $output);	
			} else {
				return $out;
			}
		}

		public function saveOutput($content, $file)
		{
			return file_put_contents($file, $content);
		}

		public function getLessphp()
		{
			return $this->less;
		}
	}

?>