<?php

class AutoloaderTest extends PHPUnit_Framework_TestCase {

	public function test_bl() {

		\Difra\Autoloader::register();
		\Difra\Autoloader::addBL( 'Difra\\Mailer' );
		$this->assertFalse( class_exists( 'Difra\\Mailer' ) );
	}

	public function test_paths() {

		$this->assertEquals( \Difra\Autoloader::class2file( 'Test' ), DIR_ROOT . 'lib/test.php' );
		$this->assertEquals( \Difra\Autoloader::class2file( 'Difra\\Test' ), DIR_FW . 'lib/test.php' );
		$this->assertEquals( \Difra\Autoloader::class2file( 'Difra\\Plugins\\Test' ), DIR_PLUGINS . 'test/lib/test.php' );
		$this->assertEquals( \Difra\Autoloader::class2file( 'Difra\\Plugins\\Test\\A' ), DIR_PLUGINS . 'test/lib/a.php' );
	}
}