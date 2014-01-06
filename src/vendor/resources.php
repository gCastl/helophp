<?php
	
namespace Helo\Vendor\Resources;

use Helo\Library\Cache\Cache,
	Helo\Library\File\File,
	Helo\Library\Minifier\JsMin,
	Helo\Library\Minifier\CssMin,
	Helo\Library\Lessphp\Lessphp,
	Helo\Library\Scssphp\Scssphp,
	Helo\Library\ImageCache\ImageCache,
	Helo\Library\CoffeeScript\CoffeeScript;

class Resources
{

	private $cache;
	private $caching = false;
	private $cache_dir = null;
	private $compress = false;

	public function __construct($uri)
	{
		require_once "library/Cache/Cache.php";
		require_once "library/File/File.php";

		if (!session_id()) {
			session_start();
		}

		$this->cache_dir = $_SESSION['_CURRENT_APP_DIR'].'Cache/';
		$this->cache = new Cache();

		$config = unserialize($_SESSION['_CURRENT_APP_CONFIG']);
		$this->caching  = (isset($config->cache)) ? $config->cache : false;
		$this->compress = (isset($config->compress)) ? $config->compress : false;

		$fileinfo = pathinfo($uri);

		if ($fileinfo['extension']=='css') {
			$this->requireCss($fileinfo, $uri);
		} elseif ($fileinfo['extension']=='less') {
			$this->requireLess($fileinfo, $uri);
		} elseif ($fileinfo['extension']=='js') {
			$this->requireJs($fileinfo, $uri);
		} elseif ($fileinfo['extension']=='coffee') {
			$this->requireCoffee($fileinfo, $uri);
		} elseif (in_array($fileinfo['extension'], array('png','jpg','jpeg','gif'))) {
			$this->requireImg($fileinfo, $uri);
		} else { 
			$this->requireOther($fileinfo, $uri);
		}

		exit;
	}


	private function show_content($type, $content)
	{
		header('Content-type: '.File::get_mime_type($type));
		ob_start('ob_gzhandler');

		echo $content;
	}

	private function requireCss($fileinfo, $uri)
	{
			$load_css = array();
		$hash 	  = $this->cache->hash('css', $fileinfo['filename']);

		if ($this->caching==true && ($cache_load = $this->cache->load($hash))!=false){
			$css = $cache_load;
		} else {
			$dirfile = $this->get_Dirfile($fileinfo, $uri);

			if (count($dirfile)>0) {
				require_once "library/Minifier/CssMin.php";
				
				$minifier = new CSSMin();
				foreach ($dirfile as $file) {
		   			if (file_exists($file)) {
		   				$load_css[] = $minifier->run(file_get_contents($file));
		   			}
		   		}
		   	}

	   		if ($this->caching==true && count($load_css)>0) {
				$this->cache->save($hash, implode(CHR(10), $load_css));
			}
		
			$css = implode(CHR(10), $load_css);
		}

		$this->show_content('css', $css);
	}

	private function requireLess($fileinfo, $uri)
	{
			$load_css = array();
		$hash 	  = $this->cache->hash('less', $fileinfo['filename']);

		if ($this->caching==true && ($cache_load = $this->cache->load($hash))!=false) {
			$css = $cache_load;
		} else {
			$dirfile = $this->get_Dirfile($fileinfo, $uri);
			
			if (count($dirfile)>0) {
				require_once "library/Minifier/CssMin.php";
				require_once "library/Lessphp/HeloLessphp.php";

				$ls = new Lessphp(false);
				$ls->setImportDir(array($_SESSION['_WEB'], $_SESSION['_CURRENT_APP_DIR'].'Web/'));

				$minifier = new CSSMin();
				foreach ($dirfile as $file) {
		   			if (file_exists($file)) {
		   				$load_css[] = $minifier->run($ls->compile($file));
		   			}
		   		}
		   	}

	   		if ($this->caching==true && count($load_css)>0) {
				$this->cache->save($hash, implode(CHR(10), $load_css));
			}
		
			$css = implode(CHR(10), $load_css);
		}

		$this->show_content('css', $css);
	}

	private function requireCoffee($fileinfo, $uri)
	{
		$load_coffee = array();
		$hash   	 = $this->cache->hash('js', $fileinfo['filename']);

		if ($this->caching==true && ($cache_load = $this->cache->load($hash))!=false) {
			$js = $cache_load;
		} else {
			$dirfile = $this->get_Dirfile($fileinfo, $uri);

			if (count($dirfile)>0) {
				require_once "library/Minifier/JsMin.php";
				require_once "library/CoffeeScript/HeloCoffeeScript.php";

				$CoffeeScript = new CoffeeScript(false);
				foreach ($dirfile as $file) {
		   			if(file_exists($file)) {
		   				$load_coffee[] = JsMin::minify($CoffeeScript->compile($file));
		   			}
		   		}
		   	}

   			if ($this->caching==true && count($load_coffee)>0) {
				$this->cache->save($hash, implode(CHR(10), $load_coffee));
			}
		
			$js = implode(CHR(10), $load_coffee);
		}

		$this->show_content('javascript', $js);
	}

	private function requireJs($fileinfo, $uri)
	{
		$load_js = array();
		$hash    = $this->cache->hash('js', $fileinfo['filename']);

		if ($this->caching==true && ($cache_load = $this->cache->load($hash))!=false) {
			$js = $cache_load;
		} else {
			$dirfile = $this->get_Dirfile($fileinfo, $uri);

			if (count($dirfile)>0) {
				require_once "library/Minifier/JsMin.php";

				foreach ($dirfile as $file) {
		   			if (file_exists($file)) {
		   				$load_js[] = JsMin::minify(file_get_contents($file));
		   			}
		   		}
		   	}

   			if ($this->caching==true && count($load_js)>0) {
				$this->cache->save($hash, implode(CHR(10), $load_js));
			}
		
			$js = implode(CHR(10), $load_js);
		}

		$this->show_content('javascript', $js);
	}

	private function requireImg($fileinfo, $uri)
	{
		$hash = $this->cache->hash($fileinfo['extension'], $fileinfo['filename']);

		if ($this->caching==true && ($cache_load = $this->cache->simple_load($hash))!=false) {
			$img = $cache_load;
		} else {
			$dirfile = $this->get_Dirfile($fileinfo, $uri);

			if (count($dirfile)>0) {
				require_once "library/ImageCache/HeloImageCache.php";

				if ($this->compress) {
					$image 	= new ImageCache($this->cache_dir);
					$img 	= $image->compress($dirfile[0]);
				} else {
					$img = file_get_contents($dirfile[0]);
				}
			}
		}

		$this->show_content($fileinfo['extension'], $img);
	}

	private function requireOther($fileinfo, $uri)
	{
		$hash = $this->cache->hash($fileinfo['extension'], $fileinfo['filename']);

		if ($this->caching==true && ($cache_load = $this->cache->simple_load($hash))!=false) {
			$other = $cache_load;
		} else {
			$dirfile = $this->get_Dirfile($fileinfo, $uri);

			if (count($dirfile)>0) {
				$other = file_get_contents($dirfile[0]);
			}
		}

		$this->show_content($fileinfo['extension'], $other);
	}

	private function get_Dirfile($fileinfo, $uri){
		$dirfile = array();
		if (preg_match('/^\/{0,}web\/(.*)\.(.*)$/i', $uri, $matches)) {
			$this->cache->define($_SESSION['_CACHE']);

			$split = explode('/', $matches[1]);
			$split[count($split)-1] = '';

			foreach(explode('%7C', $fileinfo['basename']) as $file)
				$dirfile[] = $_SESSION['_WEB'].implode('/', $split).$file;
		} elseif (preg_match('/^\/{0,}(.*)\.(.*)$/i', $uri, $matches)) {
			$this->cache->define($this->cache_dir);

			$split = explode('/', $matches[1]);
			$split[count($split)-1] = '';

			if ($fileinfo['filename']=='*') {
				$dirfile = $this->getAllFile(
					$_SESSION['_CURRENT_APP_DIR'].'Web/'.implode('/', $split), 
					$fileinfo['extension']
				);
			} else {
				foreach(explode('%7C', $fileinfo['basename']) as $file) {
					$dirfile[] = $_SESSION['_CURRENT_APP_DIR'].'Web/'.implode('/', $split).$file;
				}
			}
		}

		return $dirfile;
	}

	private function getAllFile($dir, $ext)
	{
		$get = array();
		if (is_dir($dir)) {
			if ($handle = opendir($dir)) {
				while (false !== ($entry = readdir($handle))) {
			        if($entry!='.' && 
			           $entry!='..' &&
			           is_file($dir.$entry) && 
			           pathinfo($entry, PATHINFO_EXTENSION)==$ext) {
			           	
			       		$get[] = $dir.$entry;
			       	}
			    }
			}
		}

		return $get;
	}
}

$ext_array = array(
	'css',
	'js',
	'less',
	'sass',
	'scss',
	'png',
	'jpg',
	'jpeg',
	'gif',
	'eot',
	'svg',
	'ttf',
	'woff',
	'coffee',
	'mp3',
	'ogg'
);

if (in_array(pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION), $ext_array)) {
	new Resources($_SERVER['REQUEST_URI']);
}

