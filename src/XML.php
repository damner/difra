<?php

namespace Difra;

/**
 * Class XML
 * @package Difra
 */
class XML
{
    /**
     * Throw Exception if libxml errors, set internal errors to old value
     * @param bool $internalErrors
     * @throws Exception
     */
    public static function assertNoLibxmlErrors(bool $internalErrors)
    {
        $errors = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        if (!count($errors)) {
            return;
        }

        $message = '';
        foreach ($errors as $error) {
            $message .= self::createErrorMessage($error) . PHP_EOL;
        }

        throw new Exception($message);
    }

    /**
     * Create error message from LibXMLError object
     * @param \LibXMLError $error
     * @return string
     */
    private static function createErrorMessage(\LibXMLError $error)
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

}
