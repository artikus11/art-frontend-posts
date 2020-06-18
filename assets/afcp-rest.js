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

			if ( response.data.response === 'ERROR' ) {
				try {
					$.each( response.data.message, function( key, value ) {
						$( '#' + key + '_field' ).append( '<span class="error">' + value + '</span>' );
					} );
				} catch ( e ) {
					add_message( response.data.message, 'danger' );
				}

			} else {
				buttonSubmit.text( 'Добавить мероприятие' );
				add_message( response.data.message, 'success' );
				form.resetForm();
				$( '.js-multiselect' ).val(null).trigger("change");
			}
		},
	};

	form.ajaxForm( options );
} );


function add_message( $msg, $type ) {
	var body = jQuery( 'body' );
	var html = '<div class="alert alert-' + $type + '">' + $msg + '</div>';
	body.find( jQuery( '.alert' ) ).remove();
	body.fadeIn( 'slow' ).prepend( html );

	setTimeout( function() {
		jQuery( '.alert' ).fadeOut( 'slow' );
	}, 5000 );
}