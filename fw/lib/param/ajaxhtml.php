<?php

namespace Difra\Param;

class AjaxHTML extends Common {

	const source = 'ajax';
	const type = 'html';
	const named = true;
	const filtered = false;

	use Traits\HTML;

	/**
	 * Конструктор
	 * @param string $value
	 */
	public function __construct( $value = '' ) {

		$this->raw = $value;
		$this->value = Filters\HTML::getInstance()->process( $value, self::filtered );
	}
}
