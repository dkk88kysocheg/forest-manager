import { 
	getListPhysicalEntityFactory,
} from './module-public.js'

;(function( $ ) {
	'use strict';
	
	$().ready(function() {
		// Получить список всех компаний (и обновить на странице)
		getListPhysicalEntityFactory(jQuery)

		// Создать клиента
		$('#physical-entity').on('click', 'button[data-action="forest_add_client"]', function() {
			window.location.replace ( window.location.protocol + '//' + window.location.hostname + '/client/add/?type=' + $(this).data('type') )
		})

		// Редактировать клиента
		$('#physical-entity').on('click', 'li[data-action="forest_edit_client"]', function() {
			window.location.replace ( window.location.protocol + '//' + window.location.hostname + '/client/edit/?id=' + $(this).data('id') )
		})

		// Клиент
		$('#list-physical-entity').on('click', '[data-action="open-client"]', function() {
			window.location.href = '/client?id=' + $(this).parents('.item').data('id') 
		})
	})

})( jQuery );