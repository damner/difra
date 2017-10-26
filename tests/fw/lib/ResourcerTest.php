<?php

class ResourcerTest extends PHPUnit_Framework_TestCase
{
    public function test_menu()
    {
        $oldUri = \Difra\Envi::getUri();
        \Difra\Envi::setUri('/adm/development/plugins');
        $menu = \Difra\Resourcer::getInstance('menu')->compile('adm');
        $this->assertNotEmpty($menu);
        \Difra\Envi::setUri($oldUri);
    }

    public function test_js()
    {
        $js = \Difra\Resourcer::getInstance('js')->compile('main');
        $this->assertNotEmpty($js);
    }

    public function test_css()
    {
        $js = \Difra\Resourcer::getInstance('js')->compile('main');
        $this->assertNotEmpty($js);
    }
}
