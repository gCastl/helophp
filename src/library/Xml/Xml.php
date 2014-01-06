<?php

	namespace Helo\Library\Xml;

	class Xml {

		public function array_to_xml($array, &$xml)
		{
		    foreach ($array as $key => $value) {
		        if (is_array($value))  {
		            if (!is_numeric($key)) {
		                $subnode = $xml->addChild("$key");
		                $this->array_to_xml($value, $subnode);
		            } else {
		                $subnode = $xml->addChild("item$key");
		                $this->array_to_xml($value, $subnode);
		            }
		        } else {
		            $xml->addChild("$key","$value");
		        }
		    }
		}

		public function create($root)
		{
			return new \SimpleXMLElement("<?xml version=\"1.0\"?><".$root."></".$root.">");
		}

		public function convert($in, array $array)
		{
			$this->array_to_xml($array, $in);
			return $in;
		}

		public function get($xml)
		{
			if (!is_dir(_CURRENT_APP_VIEW.'tmp/')) {
				mkdir(_CURRENT_APP_VIEW.'tmp/');
			}

			$file = md5(uniqid()).'.tmp';
			$xml->asXML(_CURRENT_APP_VIEW.'tmp/'.$file);
			$render = file_get_contents(_CURRENT_APP_VIEW.'tmp/'.$file);
			unlink(_CURRENT_APP_VIEW.'tmp/'.$file);

			return $render;
		}
	}
?>