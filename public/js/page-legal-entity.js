import { 
	getListLegalEntityFactory,
	formatDateFactory,
	formatMoneyFactory,
} from './module-public.js'
const format_date = formatDateFactory(jQuery) 
const format_money = formatMoneyFactory(jQuery) 

;(function( $ ) {
	'use strict';
	
	// Получить список всех компаний (и обновить на странице) 
	$().ready(function() {
		getListLegalEntityFactory(jQuery) 
	})

	// Создать клиента
	$('.main-panel').on('click', 'button[data-action="forest_add_client"]', function() {
		window.location.replace ( window.location.protocol + '//' + window.location.hostname + '/client/add/?type=' + $(this).data('type') );
	}) 

	// Редактировать клиента
	$('.main-panel').on('click', 'li[data-action="forest_edit_client"]', function() {
		window.location.replace ( window.location.protocol + '//' + window.location.hostname + '/client/edit/?id=' + $(this).data('id') );
	})

	// Клиент
	$('.main-panel').on('click', '[data-action="open-client"]', function() {
		window.location.replace ( window.location.protocol + '//' + window.location.hostname + '/client?id=' + $(this).parents('.item').data('id') );
	})

	// Поиск по ИНН. Вводим только цифры
	$('#form-search-company').on('input', 'input[name="inn"]', function() {
		this.value = this.value.replace(/[^0-9]/g, '')
	})

	// Поиск по ИНН.
	$('#search-company form').validate({ 
		rules: { inn: {required: true} },
        messages: { inn: {required: ''} },
        submitHandler: function(form) {
			$.get(
				'/wp-admin/admin-post.php',  
				$('#search-company form').serialize(),
				function(d){
					console.log(d) 
					if ( d.success) {
						// Сначала очищаем
						$('#search-company form input[name="inn"]').val('');
						$('#search-company .result').empty();

						if ( $.isEmptyObject(d.data) ) {
							$('#search-company .result').append( $('<span>Нет компаний с таким ИНН</span>'));
						} else {
							const company = d.data[0],
								  block   = $('#search-company .result');

							// Потом заполняем
							block.append( $('<table>', {
								id: 'about-client',
								class: 'table w-100'
							}));
							block.find('table').append( $('<tbody>'));

							block.find('table tbody').append( $( 
								'<tr class=\"name\">' +
									'<td class=\"w-25 py-2 text-muted\">Название</td>' +
									'<td class=\"w-75 py-2\">' + company.name + '</td>' +
								'</tr>' +
								'<tr class=\"inn\">' +
									'<td class=\"w-25 py-2 text-muted\">ИНН</td>' +
									'<td class=\"w-75 py-2\">' + company.inn + '</td>' + 
								'</tr>' +
								'<tr class=\"contract\">' +
									'<td class=\"w-25 py-2 text-muted\">Договор</td>' +
									'<td class=\"list-contract w-75 py-2\"></td>' +
								'</tr>' +
								'<tr class=\"manager\">' +
									'<td class=\"w-25 py-2 text-muted\">Менеджер</td>' +
									'<td class=\"w-75 py-2\">' + company.user_name + '</td>' + 
								'</tr>'
							));

							block.find('table').append( $('<tfoot>'));

							block.find('table tfoot').append( $( 
								'<tr>' +
									'<td colspan="2" class="px-0 item" data-id=\"' + company.id + '\"><button type="button" class="btn btn-primary w-100" data-action="open-client">Подробнее</button></td>' +
								'</tr>'
							));

							$.each(company.list_contract, function(index, value) {
								let cls = (value.old)?'old':'';

								block.find('table tbody .list-contract').append( $('<li>', {
									text: '№' + value.number + ' до ' + format_date('unix', value.date_completion), 
									class: cls,
								}));
							})
						}
					}
				} 
			)
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element)
        }
	})

})( jQuery );