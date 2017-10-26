<?php

namespace Difra\Resourcer\Abstracts;

use Difra\Exception;

/**
 * Abstract adapter for XML resources
 */
abstract class XML extends Common
{
    /**
     * Assemble resources to single XML
     * @param string $instance
     * @return string
     */
    protected function processData($instance)
    {
        $files = $this->getFiles($instance);

        $newXml = new \SimpleXMLElement(sprintf('<?xml version="1.0" encoding="UTF-8"?><%s></%s>', $this->type, $this->type));
        foreach ($files as $file) {
            $old = libxml_use_internal_errors(true);
            $xml = simplexml_load_file($file['raw']);
            if ($xml === false) {
                $message = '';
                foreach (libxml_get_errors() as $error) {
                    $message .= $this->createErrorMessage($error) . PHP_EOL;
                }
                libxml_use_internal_errors($old);
                throw new Exception($message);
            }
            libxml_use_internal_errors($old);
            $this->mergeXML($newXml, $xml);
            foreach ($xml->attributes() as $key => $value) {
                $newXml->addAttribute($key, $value);
            }
        }
        if (method_exists($this, 'postprocess')) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->postprocess($newXml, $instance);
        }
        return $newXml->asXML();
    }

    /**
     * Create error message from LibXMLError object
     * @param \LibXMLError $error
     * @return string
     */
    private function createErrorMessage(\LibXMLError $error)
    {
        $type = 'error (unknown type)';
        if ($error->level === \LIBXML_ERR_WARNING) {
            $type = 'warning';
        }
        if ($error->level === \LIBXML_ERR_ERROR) {
            $type = 'error';
        }
        if ($error->level === \LIBXML_ERR_FATAL) {
            $type = 'fatal error';
        }
        return sprintf('libxml %s %s: %s in file %s (%s)', $type, $error->code, trim($error->message), $error->file, $error->line);
    }

    /**
     * Recursively merge two XML trees
     * @param \SimpleXMLElement $xml1
     * @param \SimpleXMLElement $xml2
     */
    private function mergeXML(\SimpleXMLElement $xml1, \SimpleXMLElement $xml2)
    {
        /** @var \SimpleXMLElement $node */
        foreach ($xml2->children() as $name => $node) {
            if (isset($node['atomic'])) {
                $dom = dom_import_simplexml($xml1);
                $domNode = $dom->ownerDocument->createDocumentFragment();
                $domNode->appendXML($node->asXML());
                $dom->appendChild($domNode);

                continue;
            }

            $new = $xml1->addChild($name, trim($node));
            foreach ($node->attributes() as $key => $value) {
                $new[$key] = $value;
            }

            $this->mergeXML($new, $node);
        }
    }
}
