<?php

	namespace Helo\Hp\Api\Controller;

	use Helo\Vendor\Controller\Controller,
		Helo\Vendor\Api\Api;
	
	class DefaultController extends Controller
	{

		public function testApi($type, $name, $format)
		{	
			$api = new Api();
			$data = array('foo'=>'bar');
			print_r(func_get_args());
		}
	}