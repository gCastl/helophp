<?php

	namespace Helo\Library\Cache;

	class Cache {

		static public $_cache;

		static public function hash()
		{
			$extension = 'null';
			$hash 	   = array();
			
			foreach (func_get_args() as $k => $args) {
				if ($k==0) {
					$extension = strtolower($args);
				} else {
					$hash[] = $args;
				}
			}

			return md5(implode('', $hash)).'.'.$extension;
		}

		static public function save($name, $value, $json=false)
		{
			if ($fp = fopen(self::$_cache.$name, 'w+')) {
				$content = ($json) ? json_encode($value) : serialize($value);
				fputs($fp, $content);
				fclose($fp);
			}
		}

		static public function load($name, $json=false)
		{
			if (file_exists(($file = self::$_cache.$name))) {
				$content = file_get_contents($file);
				return ($json) ? json_decode($content) : unserialize($content);
			}
		}

		static public function simple_load($name)
		{
			if (file_exists(($file = self::$_cache.$name))) {
				return file_get_contents($file);
			}
		}

		static public function delete($name)
		{
			if (file_exists(($file = self::$_cache.$name))) {
				unlink($file);
			}
		}

		static public function deleteType($type)
		{
			$hash = self::hash($type);

			if ($handle = opendir(_CACHE)) {
				while (false !== ($entry = readdir($handle))) {
			       	if ($entry!='.' && $entry!='..' && is_file(_CACHE.$entry)) {
			       		list($type, $name) = explode('.', $entry);
			       		if ($type==$hash) {
			       			unlink(_CACHE.$entry);
			       		}
			       	}
			    }
			    closedir($handle);
			}
		}

		static public function define($dir)
		{
			self::$_cache = $dir;
		}
	}

?>