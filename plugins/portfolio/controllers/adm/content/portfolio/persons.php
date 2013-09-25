<?php

class AdmContentPortfolioPersonsController extends \Difra\Plugins\Widgets\DirectoryController {

	const directory = 'PortfolioPersons';

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function action( $value ) {

		$escapedValue = htmlspecialchars( $value );
		\Difra\Ajax::getInstance()->exec(
			<<<SCRIPT
			var person = $( '.widgets-directory.last' );
			if( person.length ) {
				var id = person.closest( 'tr' ).find( '.role' ).attr( 'ts' );
				person.before(
					'<div class="person">' +
					  '$escapedValue' +
					  '<input type="hidden" name="roles[' + id + '][]" value="$escapedValue">' +
					  ' &nbsp; <a href="#" class="action delete" onclick="$(this).parent().remove();"></a> &nbsp; ' +
	        			'</div>'
	        		);
	        	}
SCRIPT
		);
	}
}