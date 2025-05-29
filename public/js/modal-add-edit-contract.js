import { 
	countLinesFactory,
	formatDateFactory,
	randomLineFactory,
	getListContractFactory
} from './module-public.js'

const count_line = countLinesFactory(jQuery),
	  format_date = formatDateFactory(jQuery),
	  random_line = randomLineFactory(jQuery),
	  get_list_contract = getListContractFactory(jQuery)

;(function( $ ) {
	'use strict';


	let addEditContractModal = new bootstrap.Modal( document.getElementById('addEditContractModal'), { 
		backdrop: 'static',
		keyboard: false
	}), 
		addDeleteFileContractModal = new bootstrap.Modal( document.getElementById('addDeleteFileContractModal'))

	// Открываем модальное окна - Договор
	$('body').on('click', '[data-open-modal="addEditContractModal"]', function () {
		const modal = $('#addEditContractModal'),
			  item = $(this).parents('.item')

		// Добавить
		if ($(this).data('action') === 'add') {
			modal.find('#addEditContractModalLabel').text( 'Добавить' ) // Добавляем заголовок
		}

		// Редактировать
		if ($(this).data('action') === 'edit') {
			modal.find('#addEditContractModalLabel').text( 'Редактировать' ) // Добавляем заголовок

			modal.find('input[name="id"]').val( item.data('id') ) // Добавляем ID договора
			modal.find('input[name="number"]').val( item.data('number') ) // Добавляем номер договора
			modal.find('input[name="days"]').val( item.data('days') ) // Добавляем дней на оплату
			modal.find('input[name="date_creation"]').val( item.data('date-creation') ) // Добавляем дата создания
			modal.find('input[name="date_completion"]').val( item.data('date-completion') ) // Добавляем дата окончания
		}

		addEditContractModal.show()
	})
	// Открываем модальное окна - Файл
	$('body').on('click', '[data-open-modal="addDeleteFileContractModal"]', function () {
		const modal = $('#addDeleteFileContractModal'),
			  item = $(this).parents('.item')

		modal.find('input[name="id"]').val( item.data('id') ) // Добавляем ID договора

		// Добавить
		if ($(this).data('action') === 'add') {
			modal.find('#addDeleteFileContractModalLabel').text( 'Добавить' ) // Добавляем заголовок
			modal.find('#upload').show()
		}

		// Удалить
		if ($(this).data('action') === 'delete') {
			modal.find('#addDeleteFileContractModalLabel').text( 'Удалить' ) // Добавляем заголовок
			modal.find('.name-file').text( $(this).data('name') ) // Добавляем название файла
			modal.find('#delete').show()
		}

		addDeleteFileContractModal.show()
	})


	// Закрытие модальное окна
	$('#addEditContractModal').on('click', '.btn-close', function(){ addEditContractModal.hide() })
	$('#addDeleteFileContractModal').on('click', '.btn-close', function(){ addDeleteFileContractModal.hide() })

	// Очищаем все после того как модальное окно было скрыто
	$('#addEditContractModal').on('hidden.bs.modal', function (event) { 
		const modal = $('#addEditContractModal')
		// Очищаем заголовок
		modal.find('#addEditContractModalLabel').empty() // Очищаем заголовок
		modal.find('input[type="text"]').val('') // Очищаем поля
		modal.find('input[type="date"]').val('') // Очищаем поля
	})
	$('#addDeleteFileContractModal').on('hidden.bs.modal', function (event) { 
		const modal = $('#addDeleteFileContractModal')
		// Очищаем заголовок
		modal.find('#addEditContractModalLabel').empty() // Очищаем заголовок
		modal.find('.name-file').empty() // Очищаем название файла
		modal.find('#result').empty() // Очищаем результаты
		modal.find('.block').hide() // Скрывааем
		modal.find('input[name="id"]').val('') // Очищаем поля
		modal.find('input[name="file"]').val('') // Очищаем поля
	})

	// Отправить форму
	$('#addEditContractModal').on('click', 'button[data-action="addEditContract"]', function (event) { 
		$('#addEditContractModal').validate({ 
			rules: {
                name: {required: true},
            },
            messages: {
                name: {required: ''},
            },
            submitHandler: function(form) {
				$.post(
					'/wp-admin/admin-post.php', 
					$('#formContract').serialize(),
					function(d){
						console.log(d)
						if (d.success) {
							addEditContractModal.hide()
							get_list_contract( $('#addEditContractModal input[name="clients_id"]').val() ) 
						} 
					}
				)
			},
			errorPlacement: function(error, element) {
                error.insertAfter(element)
            }
		})
	})

	// Загрузка файла на сервер
	$('#addDeleteFileContractModal').on('change', 'input[name="file"]', function(){
		const modal = $('#addDeleteFileContractModal')

		if (window.FormData === undefined) {
			modal.find('.block').hide() // Скрывааем
			modal.find('#result').show() // Показываем
			modal.find('#result').append($('<div class="px-3 text-center">В вашем браузере FormData не поддерживается.<br><br>Попробуйте обновить браузер и повторите попытку</div>'))
			alert('')
		} else {
			let formData = new FormData();
			formData.append('file', modal.find('input[name="file"]')[0].files[0]) 

			$.ajax({ 
				type: 'post',
				url: '/wp-content/plugins/forest-manager/includes/forest-manager-upload.php', 
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				dataType : 'json',
				success: function(d){ 
					console.log(d) 
					if (d.success) {
						// Успех
						modal.find('#result').append($('<label class="w-100 input-file">'))
						modal.find('#result .input-file').append($('<span class="input-file-btn" data-action="addDeleteFileContract" data-name="' + d.name + '">Загрузить файл</span>'))
						modal.find('#result .input-file').append($('<i class="px-3 mt-3 text-center fw-bold">' + d.name + '</i>'))
						modal.find('#upload').hide() 
						modal.find('#result').show()
					} else {
						// Ошибка
						modal.find('#result').append($('<label class="w-100 input-file">'))
						modal.find('#result .input-file').append($('<div class="px-3 text-center text-danger">' + d.message + '</div>'))
						modal.find('#result .input-file').append($('<i class="px-3 mt-3 text-center fw-bold">' + d.name + '</i>'))
						modal.find('#upload').hide() 
						modal.find('#result').show()
					}
				}
			})
		}
	})

	// Загрузка/Удаление файла на сервер
	$('#addDeleteFileContractModal').on('click', 'span[data-action="addDeleteFileContract"]', function(){
		const modal = $('#addDeleteFileContractModal'),
			  data  = {
						action: modal.find('input[name="action"]').val(),
						id: modal.find('input[name="id"]').val(),
					}

		if ($(this).data('name')) { data['name'] = $(this).data('name') }	// Указываем имя файла

		$.post(
			'/wp-admin/admin-post.php', 
			data,
			function(d){
				console.log(d) 
				if (d.success) {
					addDeleteFileContractModal.hide()
					get_list_contract( $('#addEditClient input[name="id"]').val() )  
				} else {
					modal.find('#result').empty() // Очищаем результаты
					modal.find('#result').append($('<label class="w-100 input-file">'))
					modal.find('#result .input-file').append($('<div class="px-3 text-center text-danger">' + d.message + '</div>'))
				}
			}
		)
	}) 


	$().ready(function() {
		get_list_contract( $('#addEditClient input[name="id"]').val() )
	})


})( jQuery );
