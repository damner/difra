<?php

use Difra\Locales;

/**
 * Returns localized string
 * For usage in XSLT
 * Example: php:function('l10n', $locale/default/title)
 *
 * @param \DOMElement[] $nodes
 * @param string|null $field1
 * @param string|null $value1
 * @param string|null $field2
 * @param string|null $value2
 * @param string|null $field3
 * @param string|null $value3
 * @param string|null $field4
 * @param string|null $value4
 * @return string
 */
function l10n(array $nodes, $field1 = null, $value1 = null, $field2 = null, $value2 = null, $field3 = null, $value3 = null, $field4 = null, $value4 = null)
{
    return Locales::l10n($nodes, $field1, $value1, $field2, $value2, $field3, $value3, $field4, $value4);
}
