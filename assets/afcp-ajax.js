jQuery( function( $ ) {

	let form = $( '#event-form' );
	let buttonSubmit = $( '.submit-event' );

	let options = {
		url: afcp_ajax.url,
		data: {
			action: 'created_event',
			nonce: afcp_ajax.nonce,
		},
		type: 'POST',
		dataType: 'json',
		beforeSubmit: function( arr, form, options ) {

			buttonSubmit.text( 'Отправка...' );
		},
		success: function( response ) {

			console.log( response );
		},
	};

	form.ajaxForm( options );
} );