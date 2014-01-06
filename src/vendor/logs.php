<?php

namespace Helo\Vendor\Logs;

class Logs
{

	public static $request_time = _REQUEST_TIME;

	public static function set_request_time($t)
	{
		self::$request_time = $t;
	}

	public static function get_current_time()
	{
		list($utime, $time) = explode(" ", microtime());
		return ((float)$utime + (float)$time);
	}

	public static function get_execution_time()
	{
		return round(self::get_current_time()-self::$request_time, 4);
	}

	public static function open($type)
	{
		return fopen(_LOG.$type.'.log', 'a+');
	}

	public static function close($file)
	{
		return (is_resource($file)) ? fclose($file) : true;
	}

	public static function write($file)
	{
		$args = func_get_args();
		$args[] = CHR(10);
		unset($args[0]);

		fputs($file, implode('', $args));
	}

	public static function access($type, $code)
	{
		$exec_time = self::get_execution_time();

		if (($f = self::open('access'))!=false) {
			$protocol = $_SERVER['SERVER_PROTOCOL'];
			$s = '';

			if (_SSL==true) {
				$s = 's';
			}

			$host = 'http'.$s.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$agent = $_SERVER['HTTP_USER_AGENT'];

			self::write(
				$f, 
				"[".$_SERVER['REMOTE_ADDR']." - ",
				"".date('d/M/Y:H:i:s O', self::$request_time)." - ".$exec_time."] ",
				'"'._REQUEST.' '.$host.' '.$protocol.'" ',
				$code." ",
				'"'.$agent.'"'
			);
		}
	}
}
	