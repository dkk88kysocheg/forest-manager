import { 
	getListAccountFactory,
	getListSpecificationFactory,
	getClientFactory,
	getMessagesFactory,
	addEditMessagesFactory,
	deleteMessagesFactory,
	paginationFactory,
} from './module-public.js'
const get_list_account = getListAccountFactory(jQuery),
	  get_list_specification = getListSpecificationFactory(jQuery),
	  get_client = getClientFactory(jQuery),
	  get_messages = getMessagesFactory(jQuery),
	  add_edit_messages = addEditMessagesFactory(jQuery),
	  delete_messages = deleteMessagesFactory(jQuery),
	  pagination = paginationFactory(jQuery) 
 

;(function( $ ) {
	'use strict';

	// Редактировать клиента
	$('#about-client').on('click', 'button[data-action="forest_edit_client"]', function() {
		window.location.replace ( window.location.protocol + '//' + window.location.hostname + '/client/edit/?id=' + $(this).data('id') )
	})

	// Редактирование сообщения
	$('#messages').on('click', 'button[data-action="editMessage"]', function() {  
		const item = $(this).parents('.item.owner'),
			  form = $('form#comments-inter')

		form.find('input[name="id"]').val( item.data('id') )
		// Очищаем и потом заполняем
		form.find('.form-info').empty() 
		form.find('.form-info').append($(
			'<div class="edit d-flex text-muted">' +
				'<i class="text-danger icon-close btn-icon-prepend m-1" data-action="canselEditMessage"></i>' + 
				'<b class="text-dark mx-2">Ред.</b>' +
				'<div class="w-100">' + item.find('.item-text').text() + '</div>' +
			'</div>'
		))
		form.find('textarea[name="comment"]').val( item.find('.item-text').text() )
	})

	// Отмена редактирования сообщения
	$('#messages').on('click', 'i[data-action="canselEditMessage"]', function() {  
		const form = $('form#comments-inter')
		// Очищаем информационное поле
		form.find('.form-info').empty()
		// Очищаем ID
		form.find('input[name="id"]').val('')
	})

	// Удалить сообщение сообщения
	$('#messages').on('click', 'button[data-action="deleteMessageModal"]', function() { 
		const item = $(this).parents('.item.owner')
		if ($(this).attr('data-proof')) {
			delete_messages({
				clients_id: $('#about-client').data('id'),
				id: item.data('id'),
			})
		} else {
			$(this).attr('data-proof', '1')
			item.find('.item-text').addClass( 'text-decoration-line-through' )
			item.find('.item-text').addClass( 'text-danger' ) 
		}
	})

	// Отправка сообщения
	$('#messages').on('click', 'button[data-action="commentInter"]', function() {  
		const form = $('form#comments-inter')
		if( form.find('textarea[name="comment"]').val().length !== 0 ) {
			add_edit_messages({
				clients_id: $('#about-client').data('id'),
				user_id: $('#user-name').data('id-user'),
				type_message: 'comment', 
				text: form.find('textarea[name="comment"]').val(),
				id: form.find('input[name="id"]').val(),
			})
		} 
	})

	// Погинация
	$('.card').on('click', 'button[data-action="pagination"]', function() {
		pagination( $(this) )
	})

	$().ready(function() {
		// Получить список всех счетов (или обновить на странице)
		get_list_account( {clients_id: $('#about-client').data('id')} )
		get_list_specification( {clients_id: $('#about-client').data('id')} )
		get_client( $('#about-client').data('id') ) 
		get_messages( $('#about-client').data('id') )  
	})  

})( jQuery );