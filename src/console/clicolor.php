<?php

	namespace Helo\Console;

	class Color {

		/**
		 * Foreground colors declaration
		 **/
		static $f_colors = array(
            'black'        => '0;30', 'dark_gray'    => '1;30',
            'blue'         => '0;34', 'light_blue'   => '1;34',
            'green'        => '0;32', 'light_green'  => '1;32',
            'cyan'         => '0;36', 'light_cyan'   => '1;36',
            'red'          => '0;31', 'light_red'    => '1;31',
            'purple'       => '0;35', 'light_purple' => '1;35',
            'brown'        => '0;33', 'yellow'	     => '1;33',
            'light_gray'   => '0;37', 'white'        => '1;37',
        );

		/**
		 * Background colors declaration
		 **/
        static $b_colors = array(
            'black'        => '40', 'red'          => '41',
            'green'        => '42', 'yellow'       => '43',
            'blue'         => '44', 'magenta'      => '45',
            'cyan'         => '46', 'light_gray'   => '47',
        );

        /**
		 * Color the passed string
		 **/
        public static function color($string, $fc = null, $bc = null) {
            $colored_string = "";

            if(isset(self::$f_colors[$fc]))
            	$colored_string .= "\033[" . self::$f_colors[$fc] . "m";
            
            if( isset(self::$b_colors[$bc]))
                $colored_string .= "\033[" . self::$b_colors[$bc] . "m";

            $colored_string .=  $string . "\033[0m";
            return $colored_string;
        }

        /**
		 * Get the foreground color value
		 **/
        public static function getColor($fc){
            return self::$f_colors[$fc];
        }


		/**
		 * Color string in 'purple'
		 **/
        public static function purple($text){
        	return self::color($text, 'purple');
        }


        /**
		 * Color string in 'brown'
		 **/
        public static function brown($text){
        	return self::color($text, 'brown');
        }


        /**
		 * Color string in 'red'
		 **/
       	public static function red($text){
        	return self::color($text, 'red');	
        }


        /**
		 * Color string in 'cyan'
		 **/
        public static function cyan($text){
        	return self::color($text, 'cyan');	
        }


        /**
		 * Color string in 'blue'
		 **/
        public static function blue($text){
        	return self::color($text, 'blue');	
        }


        /**
		 * Color string in 'green'
		 **/
        public static function green($text){
        	return self::color($text, 'green');	
        }


        /**
		 * Color string in 'yellow'
		 **/
        public static function yellow($text){
        	return self::color($text, 'yellow');	
        }


        /**
		 * Color string in 'white'
		 **/
        public static function white($text){
        	return self::color($text, 'white');	
        }


        /**
		 * Color string in 'black'
		 **/
        public static function black($text){
        	return self::color($text, 'black');	
        }


         /**
		 * Color string in 'light gray'
		 **/
        public static function lgray($text){
        	return self::color($text, 'light_gray');	
        }


        /**
		 * Color string in 'light blue'
		 **/
        public static function lblue($text){
        	return self::color($text, 'light_blue');
        }


        /**
		 * Color string in 'light geen'
		 **/
        public static function lgreen($text){
        	return self::color($text, 'light_green');
        }


        /**
		 * Color string in 'light cyan'
		 **/
        public static function lcyan($text){
        	return self::color($text, 'light_cyan');
        }


        /**
		 * Color string in 'light red'
		 **/
        public static function lred($text){
        	return self::color($text, 'light_red');
        }


        /**
		 * Color string in 'light purple'
		 **/
        public static function lpurple($text){
        	return self::color($text, 'light_purple');
        }


        /**
		 * Color string in 'dark gray'
		 **/
        public static function dgray($text){
        	return self::color($text, 'dark_gray');
        }
	}

?>