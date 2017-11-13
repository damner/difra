<?php

namespace Difra\Resourcer\Abstracts;

use Difra\Debugger;
use Difra\Minify;

/**
 * Abstract adapter for text resources
 */
abstract class Plain extends Common
{
    /**
     * Combine resources to single string
     * @param $instance
     * @return string
     */
    protected function processData($instance)
    {
        $result = '';
        if (!empty($this->resources[$instance]['specials'])) {
            foreach ($this->resources[$instance]['specials'] as $resource) {
                if (!empty($resource['files'])) {
                    foreach ($resource['files'] as $file) {
                        $result .= $this->getFile($file);
                    }
                }
            }
        }
        if (!empty($this->resources[$instance]['files'])) {
            foreach ($this->resources[$instance]['files'] as $file) {
                $result .= $this->getFile($file);
            }
        }
        return $result;
    }

    /**
     * Choose most suitable file
     * @param $file
     * @return mixed|string
     */
    private function getFile($file)
    {
        $debuggerEnabled = Debugger::isEnabled();
        if (!$debuggerEnabled and !empty($file['min'])) {
            return file_get_contents($file['min']);
        } elseif (!$debuggerEnabled and !empty($file['raw'])) {
            return Minify::getInstance($this->type)->minify(file_get_contents($file['raw']));
        } elseif ($debuggerEnabled and !empty($file['raw'])) {
            return file_get_contents($file['raw']);
        } elseif ($debuggerEnabled and !empty($file['min'])) {
            return file_get_contents($file['min']);
        }
        return '';
    }
}
