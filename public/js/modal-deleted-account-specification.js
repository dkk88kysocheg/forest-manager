import { 
	getListAccountFactory,
	getListSpecificationFactory,  
	getClientFactory,
} from './module-public.js'
const get_list_account = getListAccountFactory(jQuery),
  	  get_list_specification = getListSpecificationFactory(jQuery),
	  get_client = getClientFactory(jQuery)

;(function( $ ) {
	'use strict';

	$().ready(function() {
		let deletedAccountSpecificationModal = new bootstrap.Modal( document.getElementById('deletedAccountSpecificationModal'), {
			backdrop: 'static'
		})

		// Открываем модальное окна - Удалить счёт/спецификацию
		$('body').on('click', '[data-open-modal="deletedAccountSpecificationModal"]', function () {
			const item = $(this).parents('.item'),
				  modal = $('#deletedAccountSpecificationModal')

			if ($(this).data('action-modal') === 'forest_deleted_account') {
				modal.find('.message span').text('Счёт №' + item.data('number'))
			} 
			if ($(this).data('action-modal') === 'forest_deleted_specification') {
				modal.find('.message span').text('Спецификацию №' + item.data('number'))
			} 
			if (item.data('client-id')) {modal.find('button[data-action="deletedAccountSpecification"]').attr('data-client-id', item.data('client-id'))}
			if (item.data('company-id')) {modal.find('button[data-action="deletedAccountSpecification"]').data('company-id', item.data('company-id'))}

			// Добавляем action
			modal.find('input[name="action"]').val( $(this).data('action-modal') )
			modal.find('input[name="id"]').val( item.data('id') )

			deletedAccountSpecificationModal.show() 
		})

		// Закрытие модальное окна
		$('#deletedAccountSpecificationModal').on('click', '.btn-close', function(){
			deletedAccountSpecificationModal.hide()
		})
		// Очищаем все после того как модальное окно было скрыто
		$('#deletedAccountSpecificationModal').on('hidden.bs.modal', function (event) {
			$('#deletedAccountSpecificationModal input').val('')
			$('#deletedAccountSpecificationModalLabel').text('')
			$('#deletedAccountSpecificationModalLabel .message span').text('') 
		})

		// Удалить счёт/спецификацию
		$('#deletedAccountSpecificationModal').on('click', 'button[data-action="deletedAccountSpecification"]', function () { 
			$('#deletedAccountSpecificationModal form').validate({ 
				rules: {
	                id: {required: true}, 
	            },
	            messages: {
	                id: {required: ''},
	            },
	            submitHandler: function(form) {
					$.post(
						'/wp-admin/admin-post.php',  
						{
							action: $('#deletedAccountSpecificationModal form input[name="action"]').val(),
							id: $('#deletedAccountSpecificationModal form input[name="id"]').val(),
						},
						function(d){
							console.log(d)
							if ( d.success ) {
								get_list_account( {clients_id: d.clients_id} )
								get_list_specification( {clients_id: d.clients_id} )
								get_client( d.clients_id )
								
								deletedAccountSpecificationModal.hide()
							}
						} 
					)
	            },
	            errorPlacement: function(error, element) {
	                error.insertAfter(element)
	            }
			})	
		})
	})

})( jQuery );