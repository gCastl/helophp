<?php
	
	namespace Helo\Library\Mustache;

	class Mustache {

		private $mustache;
		private $caching;

		public function __construct($caching)
		{
			require_once __DIR__.'/Autoloader.php';
			$autoload = new \Mustache_Autoloader();
			$autoload->register();
			$this->caching = $caching;

			$this->mustache = new \Mustache_Engine(array(
				'cache' => __DIR__.'/cache/',
				'loader' => new \Mustache_Loader_FilesystemLoader(_CURRENT_APP_VIEW)
			));
		}

		public function render($tpl, array $vars)
		{
			return $this->mustache->render($tpl, $vars);
		}

		public function stringRender($tpl, $vars)
		{
			if (!is_dir(_CURRENT_APP_VIEW.'tmp/')) {
				mkdir(_CURRENT_APP_VIEW.'tmp/');
			}

			$file = md5(uniqid()).'.tmp';
			file_put_contents(_CURRENT_APP_VIEW.'tmp/'.$file, $tpl);
			$render = $this->mustache->render('tmp/'.$file, $vars);
			unlink(_CURRENT_APP_VIEW.'tmp/'.$file);
			return $render;
		}

		public function getMustache()
		{
			return $this->mustache;
		}
	}

?>