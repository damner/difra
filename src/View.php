<?php

namespace Difra;

use Difra\View\HttpError;

/**
 * Class View
 * @package Difra
 */
class View
{
    /** @var bool Page rendered status */
    public static $rendered = false;
    /** @var string XSLT Resourcer instance */
    public static $instance = 'main';
    // options
    /** @var string Template instance */
    private $templateInstance = null;
    /** @var bool Echo output (return otherwise) */
    private $echo = false;
    /** @var bool Fill XML with various data */
    private $fillXML = self::FILL_XML_NONE;
    /** @var bool Normalize output HTML */
    private $normalize = true;
    /** @var \XSLTProcessor */
    private $xslProcessor = null;
    const FILL_XML_NONE = 0;
    const FILL_XML_LOCALE = 1;
    const FILL_XML_MENU = 1 << 1;
    const FILL_XML_OTHER = 1 << 2;
    const FILL_XML_ALL = self::FILL_XML_LOCALE | self::FILL_XML_MENU | self::FILL_XML_OTHER;
    const FILL_XML_PAGE = self::FILL_XML_ALL;

    /**
     * View constructor
     */
    public function __construct()
    {
        $this->templateInstance = self::$instance ?: 'main';
    }

    /**
     * Set template instance
     * @param string $templateInstance
     */
    public function setTemplateInstance(string $templateInstance)
    {
        if ($this->templateInstance === $templateInstance) {
            return;
        }
        $this->templateInstance = $templateInstance;
        $this->xslProcessor = null;
    }

    /**
     * Set echo flag
     * @param bool $echo
     */
    public function setEcho(bool $echo)
    {
        if ($this->echo === $echo) {
            return;
        }
        $this->echo = $echo;
        $this->xslProcessor = null;
    }

    /**
     * Set fill XML flag
     * @param int $fillXML
     */
    public function setFillXML($fillXML)
    {
        // backwards compatibility
        if ($fillXML === true) {
            $fillXML = self::FILL_XML_ALL;
        } elseif ($fillXML === false) {
            $fillXML = self::FILL_XML_NONE;
        }

        if ($this->fillXML === $fillXML) {
            return;
        }
        $this->fillXML = $fillXML;
        $this->xslProcessor = null;
    }

    /**
     * Set normalize flag
     * @param bool $normalize
     */
    public function setNormalize(bool $normalize)
    {
        if ($this->normalize === $normalize) {
            return;
        }
        $this->normalize = $normalize;
        $this->xslProcessor = null;
    }

    /**
     * Render
     * @param \DOMElement|\DOMNode $xml
     * @return bool|string
     * @throws Exception
     */
    public function process(&$xml)
    {
        Debugger::addLine(sprintf('Render start (instance "%s")', $this->templateInstance));

        if (is_null($this->xslProcessor)) {
            if (!$resource = Resourcer::getInstance('xslt')->compile($this->templateInstance)) {
                throw new Exception('XSLT resource not found');
            }
            $time = microtime(true);
            $xslDom = new \DomDocument;
            $xslDom->resolveExternals = true;
            $xslDom->substituteEntities = true;
            if (!$xslDom->loadXML($resource)) {
                throw new Exception(sprintf('XSLT load problem for instance "%s"', $this->templateInstance));
            }
            Debugger::addLine('XSLT XML loaded in ' . round(1000 * (microtime(true) - $time), 2) . 'ms');

            $time = microtime(true);
            $this->xslProcessor = new \XSLTProcessor();

            $internalErrors = libxml_use_internal_errors(true);
            $result = $this->xslProcessor->importStylesheet($xslDom);

            Debugger::addLine('XSLTProcessor initialized in ' . round(1000 * (microtime(true) - $time), 2) . 'ms');

            XML::assertNoLibxmlErrors($internalErrors);

            if ($result === false) {
                throw new Exception('Unknown error in importStylesheet()');
            }

            if (!HttpError::$error and !Debugger::$shutdown) {
                View\XML::fillXML($xml, $this->templateInstance, $this->fillXML);
            }
        }

        // transform template
        if ($html = $this->xslProcessor->transformToDoc($xml)) {
            if ($this->normalize) {
                $html = self::normalize($html);
            } else {
                $html->formatOutput = true;
                $html = $html->saveXML();
            }

            if (!$this->echo) {
                return $html;
            }

            echo $html;
            self::$rendered = true;
            if (Debugger::isEnabled()) {
                echo '<!-- Page rendered in ' . Debugger::getTimer() . ' seconds -->';
            }
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
        } else {
            $errormsg = error_get_last();
            throw new Exception($errormsg ? $errormsg['message'] : 'Can\'t render templates');
        }
        return true;
    }

    /**
     * @param \DOMDocument $xml
     * @param bool|string $specificInstance
     * @param bool $dontEcho
     * @param bool $dontFillXML
     * @param bool $normalize
     * @return bool|string
     * @throws Exception
     * @deprecated
     */
    public static function render(
        &$xml,
        $specificInstance = false,
        $dontEcho = false,
        $dontFillXML = false,
        $normalize = true
    ) {
        $view = new self;
        $view->setTemplateInstance($specificInstance);
        $view->setEcho(!$dontEcho);
        $view->setFillXML(!$dontFillXML);
        $view->setNormalize($normalize);
        return $view->process($xml);
    }

    /**
     * XSLT to HTML5 covertation
     * @param $htmlDoc
     * @return string
     */
    public static function normalize($htmlDoc)
    {
        $normalizerXml = View\Normalizer::getXML();
        $normalizerDoc = new \DOMDocument();
        $normalizerDoc->loadXML($normalizerXml);
        $normalizerProc = new \XSLTProcessor();
        $normalizerProc->importStylesheet($normalizerDoc);
        return $normalizerProc->transformToXml($htmlDoc);
    }

    /**
     * HTTP redirect
     * @param $url
     */
    public static function redirect($url)
    {
        self::$rendered = true;
        header('Location: ' . $url);
        die();
    }

    /**
     * Add Expires and X-Accel-Expires headers
     * @param $ttl
     */
    public static function addExpires($ttl)
    {
        header('Expires: ' . gmdate('D, d M Y H:i:s', $ttl ? (time() + $ttl) : 0));
        if (isset($_SERVER['SERVER_SOFTWARE']) and substr($_SERVER['SERVER_SOFTWARE'], 0, 5) == 'nginx') {
            header('X-Accel-Expires: ' . ($ttl ? $ttl : 'off'));
        }
    }

    public static function simpleTemplate($instance, $rootNodeName)
    {
        $xml = new \DOMDocument();
        $xml->appendChild($xml->createElement($rootNodeName));
        $view = new static;
        $view->setTemplateInstance($instance);
        return $view->process($xml);
    }
}
