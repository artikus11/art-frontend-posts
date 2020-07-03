jQuery( document ).ready( function( $ ) {
	$( '.js-multiselect' ).select2( {
		width: '100%'
	} );

	$.datepicker.setDefaults( {
		closeText: 'Закрыть',
		prevText: '<Пред',
		nextText: 'След>',
		currentText: 'Сегодня',
		monthNames: [
			'Январь',
			'Февраль',
			'Март',
			'Апрель',
			'Май',
			'Июнь',
			'Июль',
			'Август',
			'Сентябрь',
			'Октябрь',
			'Ноябрь',
			'Декабрь'
		],
		monthNamesShort: [ 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек' ],
		dayNames: [ 'воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота' ],
		dayNamesShort: [ 'вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт' ],
		dayNamesMin: [ 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб' ],
		weekHeader: 'Нед',
		dateFormat: 'dd-mm-yy',
		firstDay: 1,
		showAnim: 'slideDown',
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''
	} );

	// Инициализация
	$( 'input.datepicker' ).datepicker( { dateFormat: 'dd.mm.yy' } );
	// можно подключить datepicker с доп. настройками так:

	$( document ).on( 'tinymce-editor-setup', function( e, ed ) {
		ed.on( 'NodeChange', function( e ) {
			$( '#' + field_editor.key ).html( wp.editor.getContent( field_editor.key ) );
		} );
	} );
} );