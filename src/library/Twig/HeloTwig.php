<?php
	
	namespace Helo\Library\Twig;

	use Helo\Vendor\Library\Library;

	class Twig {

		private $twig;
		private $caching;

		public function __construct($caching)
		{
			require_once __DIR__.'/Autoloader.php';
			$autoloader = new \Twig_Autoloader();
			$autoloader->register();

			$cache = ($caching!=false) ? __DIR__.'/Cache/' : $caching;

			$loader 	= new \Twig_Loader_Filesystem(_CURRENT_APP_VIEW);
			$this->twig = new \Twig_Environment($loader, array(
				'cache' => $cache,
				'auto_reload' => true
			));
		}

		public function render($tpl, array $vars)
		{
			return $this->twig->render($tpl, $vars);
		}

		public function stringRender($tpl, $vars)
		{
			if (!is_dir(_CURRENT_APP_VIEW.'tmp/')) {
				mkdir(_CURRENT_APP_VIEW.'tmp/');
			}

			$file = md5(uniqid()).'.tmp';
			file_put_contents(_CURRENT_APP_VIEW.'tmp/'.$file, $tpl);
			$render = $this->twig->render('tmp/'.$file, $vars);
			unlink(_CURRENT_APP_VIEW.'tmp/'.$file);
			return $render;
		}

		public function getTwig()
		{
			return $this->twig;
		}
	}

?>