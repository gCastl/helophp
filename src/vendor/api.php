<?php

namespace Helo\Vendor\Api;

class Api {

	static public $request_mode = 'GET';

	public function __construct()
	{
		if(defined('_REQUEST'))
			self::$request_mode = _REQUEST;
	}

	public static function get_request_mode()
	{
		return self::$request_mode;
	}

	public static function unformat_data($data)
	{
		$parse = array();

		if (!empty($data)) {
			$split = explode('&', $data);

			foreach ($split as $sp) {
				list($key, $val) = explode('=', $sp);
				$parse[$key] = $val;
			}
		}
	
		return $parse;
	}

	public static function format_data($data)
	{
		if (is_array($data) || is_object($data)) {
			$tmp  = $data;
			$data = array();

			foreach ($tmp as $key => $val) {
				$data[] = $key.'='.$val;
			}

			$data = implode('&', $data);
		}

		return $data;
	}

	public static function get_data()
	{
		if (self::$request_mode=='GET') {
			return $_GET;
		} elseif (self::$request_mode=='POST') {
			return $_POST;
		} elseif (self::$request_mode=='PUT' || self::$request_mode=='DELETE') {
			return self::unformat_data(file_get_contents("php://input"));
		}
	}

	public static function send_get()
	{

	}

	public static function send_post()
	{

	}

	public static function send_put()
	{

	}

	public static function send_delete()
	{

	}

	public static function send()
	{

	}
}
