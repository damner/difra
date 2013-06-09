<?php

class ActionTest extends PHPUnit_Framework_TestCase {

	public function test_getInstance() {

		$action1 = \Difra\Action::getInstance();
		$action2 = \Difra\Action::getInstance();
		$this->assertSame( $action1, $action2 );
	}

	public function test_getUri_Fail() {

		$this->setExpectedException( 'Difra\Exception' );
		$action = new \Difra\Action;
		$action->getUri();
	}

	public function test_find_IndexIndex() {

		$action = new \Difra\Action;
		$action->uri = '';
		$action->find();
		$this->assertEquals( $action->className, 'IndexController' );
		$this->assertEquals( $action->method, 'indexAction' );
	}

	public function test_find_NameIndex() {

		$action = new \Difra\Action;
		$action->uri = 'adm';
		$action->find();
		$this->assertEquals( $action->className, 'AdmIndexController' );
		$this->assertEquals( $action->method, 'indexAction' );
	}

	public function test_find_NameIndex2() {

		$action = new \Difra\Action;
		$action->uri = 'adm/development/config';
		$action->find();
		$this->assertEquals( $action->className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( $action->method, 'indexAction' );
	}

	public function test_find_NameNameAjax() {

		$action = new \Difra\Action;
		$action->uri = 'adm/development/config/reset';
		$action->find();
		$this->assertEquals( $action->className, 'AdmDevelopmentConfigController' );
		$this->assertEquals( $action->methodAjax, 'resetAjaxAction' );
	}
}