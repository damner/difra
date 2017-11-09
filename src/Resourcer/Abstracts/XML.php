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
     * @throws Exception
     */
    protected function processData($instance)
    {
        $files = $this->getFiles($instance);

        $t = microtime(1);

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
        $removed = [];

        $old = [];
        foreach ($xml1->children() as $name => $node) {
            $old[$name][] = clone $node;
        }

        /** @var \SimpleXMLElement $node */
        foreach ($xml2->children() as $name => $node) {
            if (isset($node['atomic'])) {
                $dom1 = dom_import_simplexml($xml1);
                $domNode = $dom1->ownerDocument->createDocumentFragment();
                $domNode->appendXML($node->asXML());

                if (isset($xml1->$name)) {
                    $attributes = $xml1->$name->attributes();
                    $dom1->replaceChild($domNode, dom_import_simplexml($xml1->$name));
                    foreach ($attributes as $key => $value) {
                        dom_import_simplexml($xml1->$name)->setAttribute($key, $value);
                    }
                } else {
                    $dom1->appendChild($domNode);
                }

                continue;
            }

            if (!isset($old[$name])) {
                $subNode = $xml1->addChild($name);

                // Set node content (text)
                dom_import_simplexml($subNode)->textContent = trim((string)$node);

                // Add new attributes
                foreach ($node->attributes() as $key => $value) {
                    $subNode[$key] = $value;
                }

                // Add child nodes
                $this->mergeXML($subNode, $node);

                continue;
            }

            foreach ($old[$name] as $oldNode) {
                if (!in_array($name, $removed, true)) {
                    $removed[] = $name;
                    unset($xml1->$name);
                }

                // Create node
                $subNode = $xml1->addChild($name);

                // Set node content (text)
                $text = trim((string)$node);
                if ($text !== '') {
                    dom_import_simplexml($subNode)->textContent = $text;
                }

                // Add old attributes
                foreach ($oldNode->attributes() as $key => $value) {
                    $subNode[$key] = $value;
                }

                // Add new attributes
                foreach ($node->attributes() as $key => $value) {
                    $subNode[$key] = $value;
                }

                // Merge old child nodes
                $this->mergeXML($subNode, $oldNode);

                // Merge new child nodes
                $this->mergeXML($subNode, $node);
            }
        }
    }
}
