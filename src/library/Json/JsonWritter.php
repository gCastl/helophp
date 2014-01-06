<?php

	namespace Helo\Library\Json;

	class JsonWritter
	{

		public static function write($file, $value)
		{
			if ($fp = fopen($file, 'w+')) {
				fputs($fp, $value);
				fclose($fp);
			}
		}

		public static function simple_encode($value)
		{
			return json_encode($value);
		}
		
		public static function full_encode($value)
		{
			if (is_object($value) || is_array($value)) {
				$value = json_encode($value);
			}
			
			$split_value = str_split($value);
			
			$tab = 0; $in = false;
			foreach ($split_value as &$val) {
				if ($val=='{') {
					$tab++;
					$val .= self::pr("\n").self::pr("\t", $tab);
				} elseif ($val==',') {
					if ($in) {
						$val .= ' ';
					} else {
						$val .= self::pr("\n").self::pr("\t", $tab);
					} 
				} elseif ($val==':') {
					$val .= ' ';
				} elseif ($val=='[') {
					$in = true;
				} elseif ($val=='}') {
					$tab--;
					$val = self::pr("\n").self::pr("\t", $tab).$val;
				}
			} 

			$split_value[] = self::pr("\n");
			return implode('', $split_value);
		}

		private static function pr($v, $n=1)
		{
			$pr = '';
			for ($i=0;$i<$n;$i++) {
				$pr .= $v;
			}

			return $pr;
		}
	}
?>