<?php

class AdmDevelopmentLocalesController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$localeNode = $this->root->appendChild( $this->xml->createElement( 'locales' ) );
		\Difra\Adm\Localemanage::getInstance()->getLocalesTreeXML( $localeNode );
	}
}