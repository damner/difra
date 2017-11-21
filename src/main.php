<?php

use Difra\Events\System;

/**
 * Class Difra
 * @package Difra
 */
class Difra
{
    /**
     * Entry point
     */
    public static function main()
    {
        \Difra\Envi::setMode(!empty($_SERVER['REQUEST_METHOD']) ? \Difra\Envi::MODE_WEB : \Difra\Envi::MODE_CLI);
        System::run();
    }
}
