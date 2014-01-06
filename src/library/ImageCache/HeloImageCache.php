<?php
	
	namespace Helo\Library\ImageCache;

	class ImageCache {

		private $image;

		public function __construct($dir)
		{
			require_once "ImageCache.php";
			$this->image = new \ImageCache($dir);
		}

		public function compress($input, $output=false)
		{
			if (is_file($input)) {
				$out = $this->image->compress($input);
			}

			return file_get_contents($out['src']);
		}


		public function getImageCache()
		{
			return $this->image;
		}
	}

?>