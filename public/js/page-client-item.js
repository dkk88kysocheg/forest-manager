import { 
	getListAccountFactory,
	getListSpecificationFactory,
	getClientFactory,
	getMessagesFactory,
	addMessagesFactory,
} from './module-public.js'
const get_list_account = getListAccountFactory(jQuery),
	  get_list_specification = getListSpecificationFactory(jQuery),
	  get_client = getClientFactory(jQuery),
	  get_messages = getMessagesFactory(jQuery),
	  add_messages = addMessagesFactory(jQuery) 

;(function( $ ) {
	'use strict';
	
	$().ready(function() {
		// Получить список всех счетов (или обновить на странице)
		get_list_account( {client_id: $('#about-client').data('id')} )
		get_list_specification( {client_id: $('#about-client').data('id')} )
		get_client( $('#about-client').data('id') ) 
		get_messages( 'client', $('#about-client').data('id') ) 

		// Отправка сообщения
		$('#messages').on('click', 'button[data-action="commentInter"]', function() {  
			const form = $('form#comments-inter')
			if( form.find('input[name="id"]').val().length !== 0 &&
				form.find('input[name="user_id"]').val().length !== 0 &&
			 	form.find('textarea[name="comment"]').val().length !== 0 ) {
				add_messages('client', form.find('input[name="id"]').val(), form.find('textarea[name="comment"]').val(), 'comment', form.find('input[name="user_id"]').val() )
			} 
		})
	})

})( jQuery );