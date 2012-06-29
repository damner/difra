/**
 * Отправляет ajax-запросы и обрабатывает результаты.
 *
 * Добавляет события:
 * form-submit		— срабатывает перед отправкой данных формы
 */
$.ajaxSetup( {
	async : false,
	cache :	false,
	headers : {
		'X-Requested-With' : 'XMLHttpRequest'
	}
} );

var ajaxer = {};
ajaxer.id = 1;

ajaxer.httpRequest = function( url, params, headers ) {

	var data = {};
	if( typeof params == 'undefined' || !params ) {
		data.type = 'GET';
	} else {
		data.type = 'POST';
		data.data = 'json=' + encodeURIComponent( JSON.stringify( params ) );
	}
	if( typeof headers != 'undefined' ) {
		data.headers = headers;
	}
	return $.ajax( url, data ).responseText;
};

ajaxer.sendForm = function( form, event ) {

	var data = {
		form: $( form ).serializeArray()
	};
	//var data = $( event.target ).serialize();
	$( form ).find( '.required' ).fadeOut( 'fast' );
	$( form ).find( '.invalid' ).fadeOut( 'fast' );
	$( form ).find( '.status' ).fadeOut( 'fast' );
	$( form ).find( '.problem' ).removeClass( 'problem' );
	ajaxer.process( this.httpRequest( $( form ).attr( 'action' ), data ), form );
};

ajaxer.query = function( url, data ) {
	
	ajaxer.process( this.httpRequest( url, data ) );
};

ajaxer.process = function( data, form ) {

	try {
		console.info( 'Server said:', data );
		var data1 = $.parseJSON( data );
		if( !data1.actions ) {
			throw "data error";
		}
		this.clean( form );
		for( var key in data1.actions ) {
			var action = data1.actions[key];
			switch( action.action ) {
			case 'notify':	// сообщение
				this.notify( action.lang, action.message );
				break;
			case 'require':// не заполнено обязательное поле формы
				this.require( form, action.name );
				break;
			case 'invalid':	// не правильное значение поля формы
				this.invalid( form, action.name, action.message );
				break;
			case 'status': // текстовый статус для поля
				this.status( form, action.name, action.message, action.classname );
				break;
			case 'redirect':// перенаправление
				this.redirect( action.url );
				break;
			case 'display':	// показать окно с пришедшим html
				this.display( action.html );
				break;
			case 'reload': // перезагрузить страницу
				this.reload();
				break;
			case 'close':
				this.close( form );
				break;
			case 'error':	// сообщение об ошибке
				this.error( action.lang, action.message );
				break;
			case 'reset':	// сделать форме reset
				this.reset( form );
				break;
			default:
				console.warn( 'Ajaxer action "' + action.action + '" not implemented' );
			}
		}
	} catch( ex ) {
		this.notify( {close:'OK'}, 'Unknown error.' );
		console.warn( 'Server returned:', data );
	}
};

ajaxer.clean = function( form ) {

	$( form ).find( '.problem' ).removeClass( 'problem' );
};

ajaxer.notify = function( lang, message ) {

	$( 'body' ).append(
		'<div class="overlay" id="ajaxer-' + ajaxer.id + '">' +
			'<div class="overlay-container auto-center">' +
			'<div class="overlay-inner" style="display:none">' +
			'<div class="close-button" onclick="ajaxer.close(this)"></div>' +
			'<p>' + message + '</p>' +
			'<a href="#" onclick="ajaxer.close(this)" class="button">' + ( lang.close ? lang.close : 'OK' ) + '</a>' +
			'</div>' +
			'</div>' +
			'</div>'
	);
	$( '#ajaxer-' + ajaxer.id ).find( '.overlay-inner' ).fadeIn( 'fast' );
	$( window ).resize();
	ajaxer.id++;
};

ajaxer.error = function( lang, message ) {

	ajaxer.notify( lang, message );
};

ajaxer.require = function( form, name ) {

	var el = $( form ).find( '[name=' + name + ']' );
	if( !el.length || el.attr( 'type' ) == 'hidden' ) {
		ajaxer.error( {}, 'Field "' + name + '" is required.' );
		return;
	}
	var cke = $( form ).find( '#cke_' + name );
	if( cke.length ) {
		cke.addClass( 'problem' );
	} else {
		el.addClass( 'problem' );
	}
	var container = el.closest( '.container' );
	if( !container.length ) {
		return;
	}
	cke = $( form ).find( '#cke_' + name );
	if( cke.length ) {
		cke.addClass( 'problem' );
	} else {
		container.addClass( 'problem' );
	}
	var req = container.find( '.required' );
	if( !req.length ) {
		return;
	}
	req.fadeIn();
};

ajaxer.invalid = function( form, name, message ) {

	var el = $( form ).find( '[name=' + name + ']' );
	if( !el.length || el.attr( 'type' ) == 'hidden' ) {
		ajaxer.error( {}, 'Invalid value for field "' + name + '".' );
		return;
	}
	var cke = $( form ).find( '#cke_' + name );
	if( cke.length ) {
		cke.addClass( 'problem' );
	} else {
		el.addClass( 'problem' );
	}
	var container = el.closest( '.container' );
	if( !container.length ) {
		return;
	}
	cke = $( form ).find( '#cke_' + name );
	if( cke.length ) {
		cke.addClass( 'problem' );
	} else {
		container.addClass( 'problem' );
	}
	var req = container.find( '.invalid' );
	if( !req.length ) {
		return;
	}
	if( message ) {
		req.find( '.invalid-text' ).html( message );
	}
	req.fadeIn( 'fast' );
};

ajaxer.status = function( form, name, message, classname ) {

	var status = $( form ).find( '[name=' + name + ']' ).parents( '.container' ).find( '.status' );
	if( status ) {
		status.fadeIn( 'fast' );
		status.attr( 'class', 'status ' + classname );
		status.html( message );
	}
};

ajaxer.redirect = function( url ) {

	if( typeof(switcher) != 'undefined' ) {
		switcher.page( url );
	} else {
		document.location( url );
	}
};

ajaxer.reload = function() {

	window.location.reload();
};

ajaxer.display = function( html ) {

	$( 'body' ).append(
		'<div class="overlay" id="ajaxer-' + ajaxer.id + '">' +
			'<div class="overlay-container auto-center">' +
			'<div class="overlay-inner" style="display:none">' +
			'<div class="close-button" onclick="ajaxer.close(this)"></div>' +
			html +
			'</div>' +
			'</div>' +
			'</div>'
	);
	$( '#ajaxer-' + ajaxer.id ).find( '.overlay-inner' ).fadeIn( 'fast' );
	$( window ).resize();
	ajaxer.id++;
};

ajaxer.close = function( obj ) {

	var el = $( obj ).parents( '.overlay' );
	el.fadeOut( 'fast', function() {
		$( this ).remove();
	} );
};

ajaxer.reset = function( form ) {

	$( form ).get( 0 ).reset();
};

var main = {};
main.httpRequest = ajaxer.httpRequest;

$( document ).delegate( 'form.ajaxer', 'submit', function( event ) {

	var form = $( this );
	var files = form.find( 'input[type=file]' );
	$( document ).triggerHandler( 'form-submit' );
	if( !files.length ) {
		// serialize method
		event.preventDefault();
		ajaxer.sendForm( this, event );
	} else {
		// iframe method
		if( !$( '#ajaxerFrame' ).length ) {
			var frame = $( '<iframe id="ajaxerFrame" name="ajaxerFrame" style="display:none"></iframe>' );
			var uuid = '';
			for( var i = 0; i < 32; i++ ) {
				uuid += Math.floor( Math.random() * 16 ).toString( 16 );
			}
			var originalAction = form.attr( 'action' );
			form.attr( 'method', 'post' );
			form.attr( 'enctype', 'multipart/form-data' );
			form.attr( 'action', form.attr( 'action' ) + ( originalAction.indexOf( '?' ) == -1 ? '?' : '&' ) + 'X-Progress-ID=' + uuid );
			$( 'body' ).append( frame );
			form.attr( 'target', 'ajaxerFrame' );
			form.append( '<input type="hidden" name="_method" value="iframe"/>' );
			var loading = $( '#loading' );
			if( !loading.length ) {
				$( 'body' ).append( loading = $( '<div id="loading"></div>' ) );
			}
			loading.fadeIn();
			var interval = window.setInterval(
				function() {
					ajaxer.fetchProgress( uuid );
				},
				1000
			);
			frame.load( function() {
				var rawframe = frame.get( 0 );
				if( rawframe.contentDocument ) {
					var val = rawframe.contentDocument.body.innerHTML;
				} else if( rawframe.contentWindow ) {
					val = rawframe.contentWindow.document.body.innerHTML;
				} else if( rawframe.document ) {
					val = rawframe.document.body.innerHTML;
				}
				try {
					var fc = $( val ).text();
					if( fc ) {
						val = fc;
					}
				} catch( e ) {}
				form.attr( 'action', originalAction );
				form.find( 'input[name=_method]' ).remove();
				$( 'iframe#ajaxerFrame' ).remove();
				loading.find( 'td1' ).css( 'width', Math.ceil( $( '#upprog' ).width() - 20 ) + 'px' );
				loading.fadeOut( 'slow', function() {
					window.clearTimeout( interval );
					loading.find( '#upprog' ).remove();
				} );
				ajaxer.process( val, form );
			} );
		}
	}
} );

ajaxer.fetchProgress = function( uuid ) {

	var res = ajaxer.httpRequest( '/progress', null, { 'X-Progress-ID': uuid } );
	res = $.parseJSON( res );
	if( res.state == 'uploading' ) {
		var progressbar = $( '#upprog' );
		if( !progressbar.length ) {
			$( '#loading' )
				.css( 'background-image', 'none' )
				.append( '<div id="upprog" class="auto-center"><div class="td1"/></div>' );
			autocenter();
			progressbar = $( '#upprog' );
		}
		progressbar.find( '.td1' )
			.css( 'width', Math.ceil( ( progressbar.width() - 20 ) * res.received / res.size ) + 'px' );
	}
};

$( 'a.ajaxer' ).live( 'click dblclick', function( e ) {
	var href = $( this ).attr( 'href' );
	if( href && href != '#' ) {
		ajaxer.query( href );
	}
	e.preventDefault();
} );

$( '.ajaxer input' ).live( 'keypress', function( e ) {
	if( e.which == 13 ) {
		$( this ).parents( 'form' ).submit();
		e.preventDefault();
	}
} );

$( '.submit' ).live( 'click dblclick', function( e ) {
	$( this ).parents( 'form' ).submit();
	e.preventDefault();
});

$( '.reset' ).live( 'click dblclick', function( e ) {
	ajaxer.reset( $( this ).parents( 'form' ) );
	e.preventDefault();
} );

ajaxer.watcher = function() {

	var mc = $.cookie( 'query' );
	if( mc ) {
		mc = $.parseJSON( mc );
		ajaxer.query( mc.url );
		$.cookie( 'query', null, { path: "/", domain: config.mainhost ? '.' + config.mainhost : false } );
	}
	mc = $.cookie( 'notify' );
	if( mc ) {
		mc = $.parseJSON( mc );
		if( mc.type == 'error' ) {
			ajaxer.error( mc.lang, mc.message );
		} else {
			ajaxer.notify( mc.lang, mc.message );
		}
		if( config.mainhost ) {
			$.cookie( 'notify', null, { path:"/", domain:'.' + config.mainhost } );
		} else {
			$.cookie( 'notify', null, { path:"/", domain:'.' + window.location.host } );
		}
	}
};

$( document ).ready( ajaxer.watcher );
$( document ).bind( 'construct', ajaxer.watcher );