<?php

	namespace Helo\Library\Json;

	class JsonReader
    {
		/**
         *
         * Used to open and read json file 
         *
         * @param file $file                 
         * 
         **/
        public static function read($file)
        {
            if (file_exists($file)) {
            	return json_decode(file_get_contents($file));
            } else { 
            	return false;
            }
        }
	}
?>