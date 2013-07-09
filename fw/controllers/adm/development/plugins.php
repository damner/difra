<?php

/**
 * Class AdmDevelopmentPluginsController
 */
class AdmDevelopmentPluginsController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$pluginsNode = $this->root->appendChild( $this->xml->createElement( 'plugins' ) );
		\Difra\Plugger::getPluginsXML( $pluginsNode );
	}

	/**
	 * Enable plugin
	 *
	 * @param \Difra\Param\AnyString $name
	 */
	public function enableAjaxAction( \Difra\Param\AnyString $name ) {

		if( !\Difra\Plugger::turnOn( $name->val() ) ) {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'adm/plugins/failed' ) );
		}
		$this->ajax->refresh();
	}

	/**
	 * Disable plugin
	 *
	 * @param \Difra\Param\AnyString $name
	 */
	public function disableAjaxAction( \Difra\Param\AnyString $name ) {

		if( !\Difra\Plugger::turnOff( $name->val() ) ) {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'adm/plugins/failed' ) );
		}
		$this->ajax->refresh();
	}
}