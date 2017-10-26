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
     * @param bool $withFilenames
     * @return string
     */
    protected function processData($instance, $withFilenames = false)
    {
        $files = $this->getFiles($instance);

        $newXml = new \SimpleXMLElement(sprintf('<?xml version="1.0" encoding="UTF-8"?><%s></%s>', $this->type, $this->type));
        foreach ($files as $file) {
            $filename = $withFilenames ? $file['raw'] : null;
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
            $this->mergeXML($newXml, $xml, $filename);
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
     * @param string|null $filename
     */
    private function mergeXML(\SimpleXMLElement $xml1, \SimpleXMLElement $xml2, $filename = null)
    {
        /** @var \SimpleXMLElement $node */
        foreach ($xml2 as $name => $node) {
            if (!$filename and property_exists($xml1, $name)) {
                $attr = $xml1->$name->attributes();
                foreach ($node->attributes() as $key => $value) {
                    if (!isset($attr[$key])) {
                        $xml1->$name->addAttribute($key, $value);
                    } elseif ($value != '') {
                        $xml1->$name->attributes()->$key = $value;
                    }
                }
                /** @noinspection PhpParamsInspection */
                $this->mergeXML($xml1->$name, $node, $filename);
            } else {
                $new = $xml1->addChild($name, trim($node) ? $node : '');
                if ($filename) {
                    $new->addAttribute('source', $filename);
                }
                foreach ($node->attributes() as $key => $value) {
                    $new->addAttribute($key, $value);
                }
                $this->mergeXML($new, $node, $filename);
            }
        }
    }
}
