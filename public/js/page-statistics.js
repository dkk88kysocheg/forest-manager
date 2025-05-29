;(function( $ ) {
	'use strict';


	// Переадресация на личную страницу
	$('.card').on('click', '[data-action="open-client"]', function() {
		window.location.href = '/client?id=' + $(this).parents('.item').data('client-id')
	})

})( jQuery );