<?php

namespace Difra\Envi;

use Difra\Cache;
use Difra\Controller;
use Difra\Debugger;
use Difra\Envi;
use Difra\Exception;
use Difra\Resourcer;
use Difra\View;
use Difra\View\HttpError;

/**
 * Class Action
 * Find controller and action for current URI
 * @package Difra
 */
class Action
{
    /** @var string[] */
    private static $parameters = [];
    /** @var string */
    private static $controllerClass = null;
    /** @var string */
    private static $controllerFile = '';
    /** @var Controller */
    private static $controllerObj = null;
    /** @var string */
    private static $controllerUri = null;
    /** @var string */
    public static $method = null;
    /** @var string */
    public static $methodAuth = null;
    /** @var string */
    public static $methodAjax = null;
    /** @var string */
    public static $methodAjaxAuth = null;
    /** @var array */
    public static $methodTypes = [
        ['', ''],
        ['', 'Auth'],
        ['Ajax', ''],
        ['Ajax', 'Auth']
    ];

    /**
     * Find controller and action for current URI
     * @throws Exception
     */
    public static function find()
    {
        if (self::loadCache()) {
            Debugger::addLine('Cached controller ' . self::$controllerClass . ' from ' . self::$controllerFile);
            return;
        }

        $uri = trim(Envi::getUri(), '/');
        $parts = $uri ? explode('/', $uri) : [];

        self::getResource($parts);

        $controllerUriParts = $parts;
        if (!self::$controllerFile = self::findController($parts)) {
            self::saveCache('404');
            throw new HttpError(404);
        }
        self::$controllerUri = '/' . implode(
                '/',
                sizeof($parts) ? array_slice($controllerUriParts, 0, -sizeof($parts)) : $controllerUriParts
            );

        /** @noinspection PhpIncludeInspection */
        include_once(self::$controllerFile);
        if (!class_exists(self::$controllerClass)) {
            throw new Exception('Error! Controller class ' . self::$controllerClass . ' not found');
        }

        self::findAction($parts);
        self::$parameters = $parts;

        self::saveCache('action');
        Debugger::addLine('Selected controller ' . self::$controllerClass . ' from ' . self::$controllerFile);
    }

    /**
     * Load cached data
     * @throws \Difra\View\HttpError
     * @return bool
     */
    private static function loadCache()
    {
        if (!$data = Cache::getInstance()->get(self::getCacheKey())) {
            return false;
        }
        switch ($data['result']) {
            case 'action':
                /** @noinspection PhpIncludeInspection */
                foreach ($data['vars'] as $k => $v) {
                    self::${$k} = $v;
                }
                include_once(self::$controllerFile);
                break;
            case '404':
                throw new HttpError(404);
        }
        return true;
    }

    /**
     * Cache data
     * @param string $result Result type: 'action' or '404'
     */
    private static function saveCache($result = 'action')
    {
        if ($result != '404') {
            // save main variables
            $match = [
                'vars' => [
                    'controllerClass' => self::$controllerClass,
                    'controllerFile' => self::$controllerFile,
                    'controllerUri' => self::$controllerUri,
                    'parameters' => self::$parameters
                ],
                'result' => $result
            ];
            // save action types variables
            foreach (self::$methodTypes as $methodType) {
                $methodVar = "method{$methodType[0]}{$methodType[1]}";
                $match['vars'][$methodVar] = self::${$methodVar};
            }
        } else {
            $match = [
                'result' => '404'
            ];
        }

        Cache::getInstance()->put(self::getCacheKey(), $match, 300);
    }

    /**
     * Resource (JS, CSS, etc.) request processor
     * @param string[] $parts
     * @throws \Difra\View\HttpError
     * @return bool
     */
    private static function getResource($parts)
    {
        if (sizeof($parts) != 2) {
            return false;
        }
        $resourcer = Resourcer::getInstance($parts[0], true);
        if ($resourcer and $resourcer->isPrintable()) {
            try {
                if (!$resourcer->view($parts[1])) {
                    throw new HttpError(404);
                }
                View::$rendered = true;
                die();
            } catch (Exception $ex) {
                throw new HttpError(404);
            }
        }
        return false;
    }

    /**
     * Get controller object
     */
    public static function getController()
    {
        if (!self::$controllerClass) {
            self::find();
        }
        if (!self::$controllerObj) {
            self::$controllerObj = new self::$controllerClass(self::$parameters);
            if (!self::$controllerObj instanceof Controller) {
                throw new Exception('Controller should extend \Difra\Controller: ' . self::$controllerClass);
            }
        }
        return self::$controllerObj;
    }

    /**
     * Get list of controllers directories
     * @return string[]
     */
    public static function getControllerPaths()
    {
        static $controllerDirs = null;
        if (!is_null($controllerDirs)) {
            return $controllerDirs;
        }
        $controllerDirs = Roots::get();
        foreach ($controllerDirs as $k => $v) {
            $controllerDirs[$k] = $v . '/controllers';
        }
        return $controllerDirs;
    }

    /**
     * Get cache record key
     * @return string
     */
    private static function getCacheKey()
    {
        return 'action:uri:' . Envi::getUri();
    }

    /**
     * Find deppest controller directory path for current REQUEST
     * @param string[] $parts
     * @return string[]
     */
    private static function findControllerDirs(array &$parts)
    {
        $path = '';
        $depth = 0;
        $controllerDirs = $dirs = self::getControllerPaths();
        foreach ($parts as $part) {
            $path .= '/' . $part;
            $newDirs = [];
            foreach ($controllerDirs as $nextDir) {
                if (is_dir($nextDir . $path)) {
                    $newDirs[] = $nextDir . $path;
                }
            }
            if (empty($newDirs)) {
                break;
            }
            $depth++;
            $dirs = $newDirs;
        }
        self::$controllerClass = array_slice($parts, 0, $depth);
        $parts = array_slice($parts, $depth);
        return $dirs;
    }

    /**
     * Find matching controller for current URI
     * @param $parts
     * @return null|string
     */
    private static function findController(&$parts)
    {
        $dirs = self::findControllerDirs($parts);
        $cName = $controllerFile = null;
        if (!empty($parts)) {
            foreach ($dirs as $tmpDir) {
                if (is_file($tmpDir . '/'. $parts[0] . '.php')) {
                    $cName = $parts[0];
                    $controllerFile = $tmpDir . '/' . $cName . '.php';
                    break;
                }
            }
        }
        if (!$cName) {
            foreach ($dirs as $tmpDir) {
                if (is_file($tmpDir . '/index.php')) {
                    $cName = 'index';
                    $controllerFile = $tmpDir . '/index.php';
                    break;
                }
            }
        }
        if (!$cName) {
            return null;
        }
        if ($cName != 'index') {
            array_shift($parts);
        }
        self::$controllerClass[] = $cName;
        foreach (self::$controllerClass as $k => $v) {
            self::$controllerClass[$k] = ucFirst($v);
        };
        self::$controllerClass = implode(self::$controllerClass) . 'Controller';
        return $controllerFile;
    }

    /**
     * Find matching action for current URI
     * @param string[] $parts
     * @return bool|string
     */
    private static function findAction(array &$parts)
    {
        $foundMethod = false;
        $methodNames = !empty($parts) ? [$parts[0], 'index'] : ['index'];
        foreach ($methodNames as $methodTmp) {
            foreach (self::$methodTypes as $methodType) {
                if (method_exists(
                    self::$controllerClass,
                    $m = $methodTmp . $methodType[0] . 'Action' . $methodType[1]
                )) {
                    $foundMethod = $methodTmp;
                    $methodVar = 'method' . $methodType[0] . $methodType[1];
                    self::${$methodVar} = $m;
                }
            }
            if ($foundMethod and $foundMethod !== 'index') {
                array_shift($parts);
                break;
            }
        }
        return $foundMethod;
    }

    /**
     * Get matched controller name
     * @return null|string
     */
    public static function getControllerClass()
    {
        return self::$controllerClass;
    }

    /**
     * Manually set controller and action
     * @param string $controllerClass
     * @param string $actionMethod
     * @param array $parameters
     */
    public static function setCustomAction($controllerClass, $actionMethod, array $parameters = [])
    {
        self::$controllerClass = $controllerClass;
        self::$method = $actionMethod;
        self::$parameters = $parameters;
    }

    /**
     * Get URI matching current controller
     * Useful for relative paths.
     * @return string
     */
    public static function getControllerUri()
    {
        return self::$controllerUri;
    }
}
