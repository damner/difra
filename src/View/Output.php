<?php

namespace Difra\View;

use Difra\Ajaxer;
use Difra\Controller;
use Difra\Debugger;
use Difra\Envi\Request;
use Difra\Resourcer;
use Difra\View;

/**
 * Class Output
 * @package Difra\View
 */
class Output
{
    const CONTENT_TEXT_XML = 'text/xml';
    const CONTENT_APPLICATION_XML = 'application/xml';
    const CONTENT_APPLICATION_JSON = 'application/json';
    /** @var string|array */
    public static $output = null;
    /** @var string */
    public static $outputType = 'text/plain';

    /**
     * Choose view depending on request type
     */
    final public static function start()
    {
        $controller = Controller::getInstance();
        if (Controller::hasUnusedParameters()) {
            $controller->putExpires(true);
            throw new HttpError(404);
        } elseif (!is_null(self::$output)) {
            $controller->putExpires();
            header('Content-Type: ' . self::$outputType . '; charset="utf-8"');
            if (!is_array(self::$output)) {
                echo self::$output;
            } else {
                self::autoRender();
            }
            View::$rendered = true;
        } elseif (Debugger::isEnabled() and isset($_GET['xslt']) and $_GET['xslt']) {
            header('Content-Type: text/plain; charset="utf-8"');
            echo Resourcer::getInstance('xslt')->compile(View::$instance);
            View::$rendered = true;
        } elseif (Debugger::isEnabled() and isset($_GET['xml']) and $_GET['xml']) {
            if ($_GET['xml'] == '2') {
                View\XML::fillXML();
            }
            header('Content-Type: text/xml; charset="utf-8"');
            $controller->xml->formatOutput = true;
            $controller->xml->encoding = 'utf-8';
            echo rawurldecode($controller->xml->saveXML());
            View::$rendered = true;
        } elseif (!View::$rendered and Request::isAjax()) {
            $controller->putExpires();
            // should be application/json, but opera doesn't understand it and offers to save file to disk
            header('Content-type: text/plain');
            echo Ajaxer::getResponse();
            View::$rendered = true;
        } elseif (!View::$rendered) {
            $controller->putExpires();
            try {
                $view = new View();
                $view->setEcho(true);
                $view->setFillXML(View::FILL_XML_PAGE);
                $view->setNormalize(true);
                $view->process($controller->xml);
            } catch (HttpError $ex) {
                if (!Debugger::isConsoleEnabled()) {
                    throw new HttpError(HttpError::E_INTERNAL_SERVER_ERROR);
                } else {
                    echo Debugger::debugHTML(true);
                    die();
                }
            }
        }
    }

    /**
     * Auto render certain content types
     */
    protected static function autoRender()
    {
        switch (self::$outputType) {
//            case self::CONTENT_APPLICATION_XML:
//            case self::CONTENT_TEXT_XML:
//                header('Content-Type: text/plain');
//                $doc = new \DOMDocument();
//                $xml = $doc->appendChild($doc->createElement('xml'));
//                self::domTree($xml, self::$output);
//                $doc->formatOutput = Debugger::isEnabled();
//                echo $doc->saveXML();
//                break;
            case self::CONTENT_APPLICATION_JSON:
                echo json_encode(self::$output, Ajaxer::getJsonFlags());
                break;
            default:
        }
    }
}
