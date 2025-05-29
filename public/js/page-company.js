import { 
	getListCompanyFactory,
	formatDateFactory,
	formatMoneyFactory,
} from './module-public.js'
const format_date = formatDateFactory(jQuery) 
const format_money = formatMoneyFactory(jQuery) 

;(function( $ ) {
	'use strict';
	
	$().ready(function() {
		// Получить список всех компаний (и обновить на странице)
		getListCompanyFactory(jQuery)

		$('#list-all-company').on('click', '[data-action="open-company"]', function() {
			window.location.href = '/company/item?id=' + $(this).parents('.item').data('id')
		})

		// Поиск по ИНН. Вводим только цифры
		$('#form-search-company').on('input', 'input[name="inn"]', function() {
			this.value = this.value.replace(/[^0-9]/g, '')
		})
		// Поиск по ИНН.
		$('#form-search-company').on('click', 'button[data-action="formSearchCompany"]', function() {
			$('#form-search-company').validate({ 
				rules: {
	                inn: {required: true}, 
	            },
	            messages: {
	                inn: {required: ''},
	            },
	            submitHandler: function(form) {
					$.get(
						'/wp-admin/admin-post.php',  
						{
							action: 'forest_get_company',
							inn: $('#form-search-company input[name="inn"]').val(),
						},
						function(d){
							console.log(d) 
							if ( d.success) {
								// Сначала очищаем
								$('#form-search-company input[name="inn"]').val('')
								$('#answer-search-company').empty()

								if ( !d.data ) {
									$('#answer-search-company').append( $('<span>Нет компаний с таким ИНН</span>'))
								} else {
									// Потом заполняем
									$('#answer-search-company').append( $('<table>', {class: 'w-100'}) ) 

									$('#answer-search-company table').append( $( 
										'<tr class=\"name\">' +
											'<td class=\"w-25 py-2\">Название</td>' +
											'<td class=\"w-75 py-2\">' + d.data.name + '</td>' +
										'</tr>'
									))
									$('#answer-search-company table').append( $(
										'<tr class=\"inn\">' +
											'<td class=\"w-25 py-2\">ИНН</td>' +
											'<td class=\"w-75 py-2\">' + d.data.inn + '</td>' + 
										'</tr>'
									))
									$('#answer-search-company table').append( $(
										'<tr class=\"address\">' +
											'<td class=\"w-25 py-2\">Юр. адрес</td>' +
											'<td class=\"w-75 py-2\">' + d.data.address + '</td>' +
										'</tr>'
									))
									$('#answer-search-company table').append( $(
										'<tr class=\"contract\">' +
											'<td class=\"w-25 py-2\">Договор</td>' +
											'<td class=\"list-contract w-75 py-2\"></td>' +
										'</tr>'
									))
									$.each(d.data.list_contract, function(index, value) {
										let cls = (value.old)?'old':''
										$('#answer-search-company table .list-contract').append( $('<li>', {
											text: '№' + value.number + ' до ' + format_date('unix', value.date), 
											class: cls,
										}))
									})

									$('#answer-search-company table').append( $(
										'<tr class=\"manager\">' +
											'<td class=\"w-25 py-2\">Менеджер</td>' +
											'<td class=\"w-75 py-2\"></td>' +
										'</tr>' 
									))

									let balance = +d.data.debet - +d.data.credit,
										style = (balance >= 0)?'text-success':'text-danger',
										icon = (balance === 0)?'':(balance > 0)?'<i class=\"icon-arrow-up-circle ms-3\"></i>':'<i class=\"icon-arrow-down-circle ms-3\"></i>' 
									$('#answer-search-company table').append( $(
										'<tr class=\"balance\">' +
											'<td class=\"w-25 py-2\">Баланс</td>' +
											'<td class=\"' + style + '\">' + format_money(balance) + icon + '</td>' +
										'</tr>'
									))
								}
								

								
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