import { 
	countLinesFactory,
	randomLineFactory
} from './module-public.js'

const count_line = countLinesFactory(jQuery),
	  random_line = randomLineFactory(jQuery)

;(function( $ ) {
	'use strict';

	function get_list_contact() {
		$.get(
			'/wp-admin/admin-post.php',
			{ 
				action: 'forest_get_list_contact',  
				clients_id: $('#addEditContactModal input[name="clients_id"]').val(),
			},
			function(d){
				console.log(d)

				const table = $('#list-contact')
				table.find('tbody').empty()

				if (d.data.length !== 0) {
					$.each(d.data, function(key, val) {
						let id_button = random_line(8)

						table.find('tbody').append($(
							'<tr class=\"item\" data-id=\"' + val.id + '\" ' +
											 'data-name=\"' + val.name + '\" ' +
											 'data-phone=\"' + val.phone + '\" ' +
											 'data-email=\"' + val.email + '\" >' +
								'<td class=\"text-muted text-center\"></td>' +
								'<td class=\"name-contact\">' + val.name + '</td>' +
								'<td class=\"phone-contact\">' + ((val.phone)?val.phone:'- - -') + '</td>' +
								'<td class=\"email-contact\">' + ((val.email)?val.email:'- - -') + '</td>' +
								'<td class=\"button py-0 text-end\">' +
									'<div class=\"dropdown dropstart\">' +
										'<button type=\"button\" class=\"btn btn-sm btn-icon\" title=\"Опции\" id=\"' + id_button + '\" data-bs-toggle=\"dropdown\" data-bs-auto-close=\"true\" aria-expanded=\"false\" >' +
											'<i class=\"icon-options\"></i>' +
										'</button>' +
										'<ul class=\"dropdown-menu marker-right\" aria-labelledby=\"' + id_button + '\">' +
											'<li class=\"dropdown-item\" data-open-modal=\"addEditContactModal\" data-action=\"edit\">Редактировать</li>' +
											'<li class=\"dropdown-item\" data-open-modal=\"addEditContactModal\" data-action=\"delete\">Удалить</li>' +
										'</ul>' +
									'</div>' +
								'</td>' + 
							'</tr>'
						))
					})

					// Считаем количество строк
					count_line(table.find('tbody tr')) 
				} else {
					table.find('tbody').append($(
						'<tr><td colspan="5" class="fst-italic text-muted text-center">- - Временно пустует - -</td></tr>'
					))
				}
			}
		)
	}
	
	$().ready(function() {
		get_list_contact()

		let addEditContactModal = new bootstrap.Modal( document.getElementById('addEditContactModal'), { 
			backdrop: 'static',
			keyboard: false
		})

		// Открываем модальное окна - Счёт
		$('body').on('click', '[data-open-modal="addEditContactModal"]', function () {
			const modal = $('#addEditContactModal'),
				  item = $(this).parents('.item')

			modal.find('button[data-action="addEditContact"]').text( ($(this).data('action') === 'delete')?'Удалить':'Сохранить' ) // Название кнопки

			// Добавить
			if ($(this).data('action') === 'add') {
				modal.find('#addEditContactModalLabel').text( 'Добавить' ) // Добавляем заголовок
			}

			// Редактировать
			if ($(this).data('action') === 'edit' || $(this).data('action') === 'delete') {
				modal.find('#addEditContactModalLabel').text( ($(this).data('action') === 'edit')?'Редактировать':($(this).data('action') === 'delete')?'Удалить':'' ) // Добавляем заголовок

				modal.find('input[name="id"]').val( item.data('id') ) // Добавляем ID контакта
				modal.find('input[name="name"]').val( item.data('name') ) // Добавляем имя
				modal.find('input[name="phone"]').val( item.data('phone') ) // Добавляем телефон
				modal.find('input[name="email"]').val( item.data('email') ) // Добавляем почту

				// Удалить
				if ($(this).data('action') === 'delete') {
					modal.find('input[name="delete"]').val('1') // Помечаем на удаление
					modal.find('input[type="text"]').prop('disabled', true) // Блокируем поля 
				}
			}
			
			addEditContactModal.show()
		})

		// Закрытие модальное окна
		$('#addEditContactModal').on('click', '.btn-close', function(){ 
			addEditContactModal.hide()
		})

		// Очищаем все после того как модальное окно было скрыто
		$('#addEditContactModal').on('hidden.bs.modal', function (event) { 
			const modal = $('#addEditContactModal')
			// Очищаем заголовок
			modal.find('#addEditContactModalLabel').empty() // Очищаем заголовок
			modal.find('button[data-action="addEditContact"]').empty() // Очищаем кнопку
			modal.find('input[name="delete"]').val('') // Очищаем поля
			modal.find('input[type="text"]').val('') // Очищаем поля
			modal.find('input[type="text"]').prop('disabled', false) // Снимаем блок с полей
		})

		// Отправить форму
		$('#addEditContactModal').on('click', 'button[data-action="addEditContact"]', function (event) { 
			$('#addEditContactModal').validate({ 
				rules: {
	                name: {required: true},
	            },
	            messages: {
	                name: {required: ''},
	            },
	            submitHandler: function(form) {	
					$.post(
						'/wp-admin/admin-post.php', 
						$('#formContact').serialize(),
						function(d){
							console.log(d)
							if (d.success) {
								addEditContactModal.hide()
								get_list_contact() 
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
