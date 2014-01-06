<?php

namespace Helo\Vendor\Controller;

use Helo\Library\Json\JsonReader,
	Helo\Library\Json\JsonWritter,
	Helo\Library\Cache\Cache,
	Helo\Library\File\File,
	Helo\Library\Xml\Xml,
	Helo\Library\Twig\Twig,
	Helo\Library\Smarty\Smarty,
	Helo\Library\Mustache\Mustache;

class Controller
{
	
	private $caching	= false;
	private $templating = false;
	private $cache_dir	= null;

	public function __construct()
	{
		$this->json = new JsonReader();
		$this->cache = new Cache();

		$this->cache_dir = $_SESSION['_CURRENT_APP_DIR'].'Cache/';
		$this->cache->define($this->cache_dir);

		if (($app_config = $this->json->read(_CURRENT_APP_DIR.'config.json'))!=false) {
			if (isset($app_config->cache)) {
				$this->caching = $app_config->cache;
			}

			if (isset($app_config->engine)) {
				if (is_callable(array(
						$this, 
						($fn ='engine'.ucfirst(strtolower($app_config->engine))))
					)) {
					
					$this->{$fn}();
				}
			}
		}
	}

	public function viewExist($tpl)
	{
		if (file_exists(_CURRENT_APP_VIEW.$tpl)) {
			return true;
		}

		return false;
	}

	public function render($tpl, $vars)
	{
		if (file_exists(_CURRENT_APP_VIEW.$tpl)) {
			$hash = $this->cache->hash('HTML', 
				file_get_contents(_CURRENT_APP_VIEW.$tpl), 
				serialize($vars)
			);

		} else {
			$hash = $this->cache->hash('HTML', $tpl, serialize($vars));
		}

		$render = null;
		if ($this->caching==true && ($cache_load = $this->cache->load($hash))!=false) {
			$render = $cache_load;
		} else {
			if (file_exists(_CURRENT_APP_VIEW.$tpl)) {
				$render = $this->templating->render($tpl, $vars);
			} else {
				$render = $this->templating->stringRender($tpl, $vars);
			}

			if ($this->caching==true && $render!=null) {
				$this->cache->save($hash, $render);
			}
		}

		return (object) array(
			'type' => 'html',
			'content' => $render,
		);
	}

	public function jsonRender(array $array)
	{
		$hash = $this->cache->hash('JSON', serialize($array));
		if ($this->caching==true && ($cache_load = $this->cache->load($hash))!=false) {
			$render = $cache_load;
		} else {
			$render = json_encode($array);

			if ($this->caching==true && $render!=null) {
				$this->cache->save($hash, $render);
			}
		}

		return (object) array(
			'type' => 'json',
			'content' => $render
		);
	}

	public function xmlRender(array $array)
	{

		$hash = $this->cache->hash('XML', serialize($array));
		if ($this->caching==true && ($cache_load = $this->cache->load($hash))!=false) {
			$render = $cache_load;
		} else {
			$this->xml = new Xml();
			$x = $this->xml->create('student');
			$x = $this->xml->convert($x, $array);
			$render = $this->xml->get($x);

			if ($this->caching==true && $render!=null) {
				$this->cache->save($hash, $render);
			}
		}

		return (object) array(
			'type' => 'xml',
			'content' => $render
		);
	}

	public function redirect($new_uri)
	{
		header("Location: http://".$_SERVER['HTTP_HOST'].$new_uri);
		exit();
	}

	public function response($result)
	{
		header('Content-type: '.File::get_mime_type($result->type));
		echo $result->content;
	}

	private function engineTwig()
	{
		$this->templating = new Twig($this->caching);
	}

	private function engineSmarty()
	{
		$this->templating = new Smarty($this->caching);
	}

	private function engineMustache()
	{
		$this->templating = new Mustache($this->caching);
	}
}
