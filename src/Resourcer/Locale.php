<?php

namespace Difra\Resourcer;

use Difra\Exception;
use Difra\XML as XMLUtils;

/**
 * Class Locale
 * @package Difra\Resourcer
 */
class Locale extends Abstracts\XML
{
    protected $type = 'locale';
    protected $printable = false;

    /**
     * @inheritdoc
     */
    protected function processData($instance)
    {
        $files = $this->getFiles($instance);

        $document = new \DOMDocument('1.0', 'UTF-8');
        $locale = $document->appendChild($document->createElement('locale'));

        foreach ($files as $file) {
            $internalErrors = libxml_use_internal_errors(true);
            $doc = new \DOMDocument('1.0', 'UTF-8');
            $result = $doc->load($file['raw']);
            XMLUtils::assertNoLibxmlErrors($internalErrors);
            if ($result === false) {
                throw new Exception('Unknown error in DOMDocument::load()');
            }

            foreach ($doc->documentElement->childNodes as $node) {
                if ($node->nodeType === XML_TEXT_NODE) {
                    continue;
                }

                /** @var \DOMElement $node */
                if ($node instanceof \DOMElement) {
                    $node->setAttribute('from-file', $file['raw']);
                }

                $locale->appendChild($document->importNode($node, true));
            }
        }

        return $document->saveXML();
    }

}
