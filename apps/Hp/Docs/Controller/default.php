<?php

	namespace Helo\Hp\Docs\Controller;

	use Helo\Vendor\Controller\Controller;
	
	class DefaultController extends Controller {

		public function index($lang='fr', $category='about', $mode=false){
			if(!$this->viewExist(($tpl = $lang.'/index.html.twig')))
				$this->redirect(str_replace('/'.$lang.'/', '/fr/', _URI));

			$this->response($this->render(
				$tpl, array(
					'active' => $category,
					'mode'	 => $mode,
					'lang'	 => $lang
				)
			));
		}
	}

?>