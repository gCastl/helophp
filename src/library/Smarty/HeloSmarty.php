<?php
	
	namespace Helo\Library\Smarty;

	class Smarty {

		private $smarty;
		private $caching;

		public function __construct($caching)
		{
			require_once __DIR__.'/Smarty.class.php';
			$this->smarty = new \Smarty();
			$this->caching = $caching;

			if ($caching!=false) {
				$this->smarty->caching = true;
				$this->smarty->cache_lifetime = 3600;
				$this->smarty->compile_check = false;

				if ($caching=='strict') {
					$this->smarty->compile_check = true;
				}
			}

			$this->smarty->template_dir = _CURRENT_APP_VIEW;
			$this->smarty->cache_dir = __DIR__.'/cache/';
		}

		public function render($tpl, array $vars) {
			foreach ($vars as $key => $var) {
				$this->smarty->assign($key, $var);
			}

			return $this->smarty->fetch($tpl);
		}

		public function stringRender($tpl, $vars)
		{
			if (!is_dir(_CURRENT_APP_VIEW.'tmp/')) {
				mkdir(_CURRENT_APP_VIEW.'tmp/');
			}

			$file = md5(uniqid()).'.tmp';
			file_put_contents(_CURRENT_APP_VIEW.'tmp/'.$file, $tpl);
		
			foreach ($vars as $key => $var) {
				$this->smarty->assign($key, $var);
			}

			$render = $this->smarty->fetch('tmp/'.$file);

			unlink(_CURRENT_APP_VIEW.'tmp/'.$file);
			return $render;
		}

		public function getSmarty()
		{
			return $this->smarty;
		}
	}

?>