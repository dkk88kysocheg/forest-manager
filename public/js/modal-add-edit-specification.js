import { 
	countLinesFactory,
	formatDateFactory,
	formatMoneyFactory,
	getListAccountFactory,
	getListSpecificationFactory, 
	getClientFactory,
	randomLineFactory
} from './module-public.js'

const count_line = countLinesFactory(jQuery),
	  format_date = formatDateFactory(jQuery),
	  format_money = formatMoneyFactory(jQuery),
	  get_list_account = getListAccountFactory(jQuery),
	  get_list_specification = getListSpecificationFactory(jQuery),
	  get_client = getClientFactory(jQuery),
	  random_line = randomLineFactory(jQuery)

;(function( $ ) {
	'use strict';

	function get_list_product() {
		$('#searchProduct').prop('disabled', true)
		$('#listProductOptions').empty()
		$.get(
			'/wp-admin/admin-post.php', 
			{ 'action' : 'forest_get_list_product' },
			function(d){
				console.log(d)
				if ( d.success ) {
					$.each(d.data, function(index, value) {
						$('#listProductOptions').append($('<option>', {
							value : value.name_search,
							'data-product-id' : value.id,
							'data-height'     : value.height,
							'data-width'      : value.width,
							'data-length'     : value.length,
							'data-weight'     : value.weight,
							'data-granular'   : value.granular,
							'data-price'      : value.price,
							'data-price-opt'  : value.price_opt,
						}))
					})
					$('#searchProduct').prop('disabled', false)
				}
			}
		)
	}

	function get_list_account_select(account_id) {
		let select = $('#addEditSpecificationModal select[name="account_id"]')
		select.prop('disabled', true)
		const data = {'action' : 'forest_get_list_account'}

		if (+$('#addEditSpecificationModal select[name="person"]').val()) {
			data.client_id = $('#addEditSpecificationModal select[name="client_id"]').val()
		} else {
			data.company_id = $('#addEditSpecificationModal select[name="company_id"]').val()
		}

		if ( data.client_id || data.company_id ) {
			$.get(
				'/wp-admin/admin-post.php', 
				data,
				function(d){
					console.log(d)
					if ( d.success ) {
						select.empty()
						let obj = {
							text: 'Выберите счёт',
							disabled: 'disabled',
						}
						obj.selected = (!account_id)?true:false
						select.append($('<option>', obj))
						$.each(d.data, function(index, value) {
							obj = {
								text: value.number,
								value: value.id,
							}
							obj.selected = (+value.id === +account_id)?true:false
							select.append($('<option>', obj))
						})
						select.prop('disabled', false)
					}
				}
			)
		}
	}

	function add_table_product(e) {
		if (e === 'board') {
			$('#list-product').prepend($(
				'<table class=\"table table-striped my-3\" id=\"list-product-board\">' +
					'<thead>' +
						'<tr>' +
							'<th class=\"text-muted text-center\">#</th>' +
							'<th class=\"name text-muted\">Название</th>' +
							'<th class=\"count text-muted text-center\">Кол-во</th>' +
							'<th class=\"price text-muted text-center\">Цена <span class=\"hint\" data-hint=\"Цена за 1 куб\">i</span></th>' +
							'<th class=\"volume text-muted text-center\">Объём, м<sup>3</sup></th>' +
							'<th class=\"amount text-muted text-center\">Сумма, ₽</th>' + 
							'<th class=\"button\"></th>' +
						'</tr>' +
					'</thead>' +
					'<tbody>' +
					'<tfoot>' +
				'</table>'
			))
		}
		if (e === 'granular') {
			$('#list-product').append($(
				'<table class="table table-striped my-3" id="list-product-granular">' +
					'<thead>' +
						'<tr>' +
							'<th class="text-muted text-center">#</th>' +
							'<th class="name text-muted">Название</th>' +
							'<th class="count text-muted text-center">Кол-во</th>' +
							'<th class="price text-muted text-center">Цена <span class=\"hint\" data-hint=\"Цена за 1 тонну\">i</span></th>' +
							'<th class="weight text-muted text-center">Вес, кг</th>' +
							'<th class="amount text-muted text-center">Сумма, ₽</th>' + 
							'<th class="button"></th>' +
						'</tr>' +
					'</thead>' +
					'<tbody>' +
					'<tfoot>' +
				'</table>'
			))
		}
	}

	function add_row(data) {
		let elem,
			id_product = random_line(8);

		if (!+data.granular) {
			// Добавляем таблицу если ее нет
			if ( $('#list-product-board').length === 0 ) { add_table_product('board'); }
			elem = $('#list-product-board');
			// Доска
			elem.append($(
				'<tr class="item" data-id=\"'          + ((data.id)?data.id:'') + '\"' + 
								 'data-name-search=\"' + data.name_search + '\"' +
								 'data-granular=\"'    + data.granular + '\"' +
								 'data-height=\"'      + data.height + '\"' +
								 'data-width=\"'       + data.width + '\"' +
								 'data-length=\"'      + data.length + '\"' +
								 'data-opt=\"'         + data.opt + '\">' +
					'<td class=\"text-muted text-center\"></td>' +
					'<td class=\"name\">' + 
						'<div class=\"mb-1\">' + data.name_search + '</div>' +
						'<div class=\"d-flex additional-option\">' + 
							'<select class="form-select w-50 me-3" data-name="okpd" name="list_product['+id_product+'][okpd_id]"></select>' +
							'<input type="text" class="form-control w-50" data-name="pack_number" name="list_product['+id_product+'][pack_number]" placeholder="Номер пачки">' +
							'<input type="hidden" name="list_product['+id_product+'][product_id]" value="' + data.product_id + '">' +
							'<input type="hidden" name="list_product['+id_product+'][opt]"        value="' + (data.opt?data.opt:0) + '">' + 
						'</div>' +
					'</td>' +
					'<td class=\"count\"><input type="text" class="form-control text-center" data-name="count" name="list_product['+id_product+'][count]" data-type="number"></td>' +
					'<td class=\"price\"><input type="text" class="form-control text-center" data-name="price" name="list_product['+id_product+'][price]" data-type="number"></td>' +
					'<td class=\"volume text-center\"></td>' +
					'<td class=\"amount text-center\"></td>' +
					'<td class=\"button py-0 text-center\">' +
						'<button type="button" class="btn btn-danger btn-sm btn-icon-text px-2" data-action="delete-row">' +
                            '<i class="icon-close btn-icon-prepend m-0"></i>' +
                        '</button>' +
					'</td>' +
				'</tr>')
			);
		} else if (+data.granular) {
			// Добавляем таблицу если ее нет
			if ( $('#list-product-granular').length === 0 ) { add_table_product('granular'); }
			elem = $('#list-product-granular');
			// Сыпучий продукт
			elem.append($(
				'<tr class="item" data-id=\"'          + ((data.id)?data.id:'') + '\"' + 
								 'data-name-search=\"' + data.name_search + '\"' +
								 'data-granular=\"'    + data.granular + '\"' +
								 'data-weight=\"'      + data.weight + '\"' +
								 'data-opt=\"'         + data.opt + '\">' +
					'<td class=\"text-muted text-center\"></td>' +
					'<td class=\"name\">' + 
						'<div class=\"mb-1\">' + data.name_search + '</div>' +
						'<div class=\"d-flex additional-option\">' + 
							'<input type="text" class="form-control w-50" data-name="pack_number" name="list_product['+id_product+'][pack_number]" placeholder="Номер пачки">' +
							'<input type="hidden" name="list_product['+id_product+'][product_id]" value="' + data.product_id + '">' +
							'<input type="hidden" name="list_product['+id_product+'][opt]"        value="' + (data.opt?data.opt:0) + '">' +
						'</div>' +
					'</td>' +
					'<td class=\"count\"><input type="text" class="form-control text-center" data-name="count" name="list_product['+id_product+'][count]" data-type="number"></td>' +
					'<td class=\"price\"><input type="text" class="form-control text-center" data-name="price" name="list_product['+id_product+'][price]" data-type="number"></td>' +
					'<td class=\"weight text-center\"></td>' +
					'<td class=\"amount text-center\"></td>' +
					'<td class=\"button py-0 text-end\">' +
						'<button type="button" class="btn btn-danger btn-sm btn-icon-text px-2" data-action="delete-row">' +
                            '<i class="icon-close btn-icon-prepend m-0"></i>' +
                        '</button>' +
					'</td>' +
				'</tr>')
			);
		}

		// Записываем данные если необходимо
		let last_child = elem.find('.item:last-child');

		last_child.find('input[data-name="count"]').val( (data.count)?data.count:1 );
		last_child.find('input[data-name="price"]').val( (data.price)?data.price:1 );
		last_child.find('input[data-name="pack_number"]').val( (data.number)?data.number:'' );

		if (!+data.granular) {
			// Наполняем select ОКПД
			let okpd = last_child.find('select[data-name="okpd"]');

			okpd.append($('<option>', {
				text : 'ОКПД',
				value: 0
			}))
			$.each( $('#list-okpd li'), function() {
				let data_option = {};

				data_option.text  = $(this).data('value');
				data_option.value = $(this).data('id');

				if (data.okpd_id) { if (+$(this).data('id') === +data.okpd_id) { data_option.selected = true; }}

				okpd.append($('<option>', data_option));
			})
		}

		count_line( elem.find('.item') );
		calculate_item( last_child );
	}

	function check_delivery(count = 0, price = 0, address = '') {
		const delivery = $('#addEditSpecificationModal #delivery');
		console.log(count)
		console.log(price)
		if ( count && price ) {
			delivery.find('input[name="status-delivery"]').prop('checked', true);
			delivery.find('input[name="status-delivery"]').val( 1 );
			delivery.find('input[name="count_delivery"]').val( count );
			delivery.find('input[name="price_delivery"]').val( price );
			delivery.find('textarea[name="address_delivery"]').val( address );
			calculate_delivery();
			delivery.find('.row').show();
		} else {
			delivery.find('input[name="status-delivery"]').prop('checked', false);
			delivery.find('.row').hide();
			delivery.find('textarea').val('');
			delivery.find('input').val('');
			delivery.find('input').empty();
			delivery.find('.amount').empty();
		}
	}

	function check_discount(active = false, price = 0) {
		const discount = $('#addEditSpecificationModal #discount');
		if (active) {
			discount.find('input[name="status-discount"]').prop('checked', true);
			discount.find('input[name="status-discount"]').val( 1 );
			discount.find('input[name="discount"]').val( price );
			discount.find('.row').show();
		} else {
			discount.find('input[name="status-discount"]').prop('checked', false);
			discount.find('input').val('');
			discount.find('.row').hide();

			discount.find('.calculatePercentage').empty();
		}
	}

	function calculate_item(data) {
		if (!+data.data('granular')) {
			$.get(
				'/wp-admin/admin-post.php', 
				{
					action:'forest_calculate_product',
					count:  +data.find('input[data-name="count"]').val(),
					price:  +data.find('input[data-name="price"]').val(),
					height: +data.data('height'),
					width:  +data.data('width'),
					length: +data.data('length')
				},
				function(d){
					console.log(d)
					data.find('.volume').text( (d.success)?d.volume.toFixed(3):'- - -' ); 
					data.find('.amount').text( (d.success)?format_money(d.amount):'- - -'  );
					data.find('.amount').attr( 'data-amount', d.amount.toFixed(2) );

					calculate_amount( $('#list-product-board') ); 
				}
			)
		}

		if (+data.data('granular')) {
			let count  = +data.find('input[data-name="count"]').val(),
				price  = +data.find('input[data-name="price"]').val(),
				weight = +data.data('weight'),
				amount = ((count * weight/1000) * price).toFixed(2);

			data.find('.weight').text( count * weight );
			data.find('.amount').text( format_money(amount) );
			data.find('.amount').attr( 'data-amount', amount );

			calculate_amount( $('#list-product-granular') );
		}
	}

	function calculate_amount(table) {
		let am = 0,
			vw = 0,
			v = '';
		$.each(table.find('.item'), function() {
			v = (+$(this).data('granular'))?'weight':'volume';
			am += +$(this).find('.amount').attr('data-amount');
			vw += +$(this).find( '.' + v ).text();
		})
		table.find('tfoot').empty();
		// Итог по продукции
		table.find('tfoot').append($(
			'<tr>' +
				'<td colspan=\"4\" class=\"text-end py-3 fw-bold\">Итого:</td>' +
				'<td class=\"' + v + ' text-center py-3 fw-bold\">' + ((v === 'volume')?vw.toFixed(3):vw) + '</td>' +
				'<td class=\"amount text-center py-3 fw-bold\" data-amount=\"' + am.toFixed(2) + '\">' + format_money(am.toFixed(2)) + '</td>' +
				'<td class=\"button\"></td>' +
			'</tr>'
		))

		calculate_total_amount();
	}

	function calculate_total_amount() {
		let total_amount = 0,
			elem = $('#addEditSpecificationModal #list-product-total-amount');

		if ($('#addEditSpecificationModal #list-product-board').length) {
			total_amount += +$('#addEditSpecificationModal #list-product-board tfoot .amount').attr('data-amount');
		}

		if ($('#addEditSpecificationModal #list-product-granular').length) {
			total_amount += +$('#addEditSpecificationModal #list-product-granular tfoot .amount').attr('data-amount');
		}

		if ($('#addEditSpecificationModal #delivery input[name="status-delivery"]').is(':checked')){
			total_amount += +$('#addEditSpecificationModal #delivery .amount').attr('data-amount');
		}

		if ($('#addEditSpecificationModal #discount input[name="status-discount"]').is(':checked')){
			total_amount -= +$('#addEditSpecificationModal #discount input[name="discount"]').val();
		}

		elem.find('input').val( total_amount );
		elem.find('.amount span').text( format_money(total_amount.toFixed(2)) );
	}

	function calculate_delivery() {
		let delivery = $('#addEditSpecificationModal #delivery'),
			count    = +delivery.find('input[name="count_delivery"]').val(),
			price    = +delivery.find('input[name="price_delivery"]').val(),
			amount   = count * price;

		delivery.find('.amount').attr( 'data-amount', amount.toFixed(2) );
		delivery.find('.amount').text( format_money(amount.toFixed(2)) );

		calculate_total_amount();
	}

	function active_pane(pane) {
		$('.pane-product').removeClass('active')
		$('#' + pane).addClass('active')
	}

	function check_input() {
		const modal  = $('#addEditSpecificationModal');

		modal.find('input[data-name="new-height"]').prop('disabled', false);
		modal.find('input[data-name="new-width"]' ).prop('disabled', false);
		modal.find('input[data-name="new-length"]').prop('disabled', false);
		modal.find('input[data-name="new-weight"]').prop('disabled', false);

		if (
			modal.find('input[data-name="new-height"]').val() || 
			modal.find('input[data-name="new-width"]' ).val() || 
			modal.find('input[data-name="new-length"]').val()
		) { 
			modal.find('input[data-name="new-weight"]').prop('disabled', true);
		}

		if (modal.find('input[data-name="new-weight"]').val()) {
			modal.find('input[data-name="new-height"]').prop('disabled', true);
			modal.find('input[data-name="new-width"]' ).prop('disabled', true);
			modal.find('input[data-name="new-length"]').prop('disabled', true);
		}
	}
	
	$().ready(function() {
		get_list_product() // Получаем список продукции
	})

	// Посчитать процент
	$('body').on('click', '#discount button[data-action="calculatePercentage"]', function () {
		const modal = $(this).parents('#addEditSpecificationModal'),
			amount = +modal.find('#list-product tfoot .amount').attr('data-amount'),
			cp = modal.find('.calculatePercentage'),
			percentage5 = (amount * 5)/100,
			percentage7 = (amount * 7)/100,
			percentage10 = (amount * 10)/100,
			percentage15 = (amount * 15)/100,
			percentage20 = (amount * 20)/100,
			percentage25 = (amount * 25)/100;

		let str = '';

		str += '5% = ' + format_money(percentage5) + '</br>';
		str += '7% = ' + format_money(percentage7) + '</br>';
		str += '10% = ' + format_money(percentage10) + '</br>';
		str += '15% = ' + format_money(percentage15) + '</br>';
		str += '20% = ' + format_money(percentage20) + '</br>';
		str += '25% = ' + format_money(percentage25) + '</br>';

		cp.empty();
		cp.append(str);
	})

	let addEditSpecificationModal = new bootstrap.Modal( document.getElementById('addEditSpecificationModal'), {
		backdrop: 'static', 
		keyboard: false
	}),
		dispatchSpecificationModal = new bootstrap.Modal( document.getElementById('dispatchSpecificationModal'), {
		backdrop: 'static'
	}),
		cancelDispatchSpecificationModal = new bootstrap.Modal( document.getElementById('cancelDispatchSpecificationModal'), {
		backdrop: 'static'
	});

	// Открываем модальное окна - Спецификация
	$('body').on('click', '[data-open-modal="addEditSpecificationModal"]', function () {
		const modal = $('#addEditSpecificationModal')
		let elem,
			specification_id = 0,
			account_id = 0,
			clients_id

		// Добавить
		if ( $(this).data('action-modal') === 'forest_add_specification') {
			modal.find('#addEditSpecificationModalLabel').text( 'Добавить спецификацию' );
			clients_id = $('#about-client').data('id');

			let datetime = new Date(),
				month    = '0' + (datetime.getMonth() + 1),
				day      = '0'  + datetime.getDate(),
				number   = "СП" + String(datetime.getFullYear()).substr(2) + month.slice(-2) + day.slice(-2);

			modal.find('input[name="number"]').val( number );
			modal.find('input[name="user_id"]').val( $('#user-name').data('id-user') );

			calculate_total_amount();
		}

		// Редактировать
		if ( $(this).data('action-modal') === 'forest_edit_specification') {
			modal.find('#addEditSpecificationModalLabel').text( 'Редактировать спецификацию' );

			elem = $(this).parents('.item');
			specification_id = elem.data('id');
			clients_id = elem.data('clients-id');

			// Запрос на сервер
			$.get(
				'/wp-admin/admin-post.php', 
				{ 
					action: 'forest_get_specification', 
					specification_id: specification_id, 
					clients_id: clients_id
				}, 
				function(d){
					console.log(d)
					if ( d.success ) {
						// Добавляем если есть данные спецификации
						if (!$.isEmptyObject(d.specification_data)) {
							const data = d.specification_data;
							// ID пользователя
							modal.find('input[name="user_id"]').val(data.user_id);
							// Расчёт
							modal.find('select[name="cash"] option[value="' + data.cash + '"]').prop('selected', true);
							// Договор
							modal.find('select[name="contract_id"] option[value="' + data.contract_id + '"]').prop('selected', true);
							// Номер спецификации
							modal.find('input[name="number"]').val(data.number);
							// Дата выставления
							modal.find('input[name="date"]').val( format_date('unix', data.date_creation, '-', 'rtl') ) ;
							// Доставка
							check_delivery(+data.count_delivery, +data.price_delivery, data.address_delivery);
							// Скидка
							check_discount(Boolean(+data.discount), data.discount);
							// Дополнительное поле
							modal.find('input[name="additional"]').val(data.additional);
							// Указываем account_id
							account_id = data.account_id;
						}

						// Добавляем продукцию если она есть
						if (d.product_list.length !== 0) {
							$.each(d.product_list, function(key, val) {
								add_row(val);
							})
						}
					}
				}
			)
		}

		modal.find('input[name="action"]').val( $(this).data('action-modal') )
		modal.find('input[name="id"]').val( specification_id )
		modal.find('input[name="clients_id"]').val( clients_id )
		
		addEditSpecificationModal.show()
	})
	// Открываем модальное окна - Отгрузка
	$('body').on('click', '[data-open-modal="dispatchSpecificationModal"]', function () {
		$('#dispatchSpecificationModal input[name="id"]').val( $(this).parents('.item').data('id') ) ;
		dispatchSpecificationModal.show();
	})
	// Открываем модальное окна - Отмена отгрузки   
	$('body').on('click', '[data-open-modal="cancelDispatchSpecificationModal"]', function () { 
		const item = $(this).parents('.item');

		$('#cancelDispatchSpecificationModal input[name="id"]').val( item.data('id') ) 
		$('#cancelDispatchSpecificationModal .message span').text('Спецификацию №' + item.data('number'))
		cancelDispatchSpecificationModal.show() 
	})

	// Закрытие модальное окна
	$('#addEditSpecificationModal').on('click', '.btn-close', function(){
		addEditSpecificationModal.hide()
	})
	$('#dispatchSpecificationModal').on('click', '.btn-close', function(){
		dispatchSpecificationModal.hide()
	})
	$('#cancelDispatchSpecificationModal').on('click', '.btn-close', function(){
		cancelDispatchSpecificationModal.hide()
	})

	// Очищаем все после того как модальное окно было скрыто
	$('#addEditSpecificationModal').on('hidden.bs.modal', function (event) { 
		const modal = $('#addEditSpecificationModal')
		//Очищаем input и убираем ошибки
		modal.find('label.error').remove()
		$.each(modal.find('input'), function() {
			if ($(this).hasClass('error')) {
				$(this).removeClass('error');
			}
			if (
				$(this).attr('data-name') === 'search'     ||
				$(this).attr('data-name') === 'additional' ||
				$(this).attr('data-name') === 'new-height' ||
				$(this).attr('data-name') === 'new-width'  ||
				$(this).attr('data-name') === 'new-length' ||
				$(this).attr('data-name') === 'new-weight'
			) {
				$(this).val( '' );
			}
		})
		// В списках выбираем первый вариант
		$.each(modal.find('select'), function() {
			$(this).find('option:first').prop('selected', true);
		})
		// Проверка input (Создать новый продукт)
		check_input()
		// Очищаем список продукции
		modal.find('#list-product').empty()
		// Очищаем список счётов клиента
		modal.find('#account-specification').empty()
		// Доставка
		check_delivery()
		// Скидка
		check_discount()
		// Показываем панель поиска
		active_pane('search-product') 
	})
	$('#dispatchSpecificationModal').on('hidden.bs.modal', function (event) {
		const modal = $('#dispatchSpecificationModal')

		modal.find('input[type="text"]').val('');
		modal.find('input[type="date"]').val('');
		modal.find('button[data-action="dispatchSpecification"]').prop('disabled', false);
		modal.find('.error-message').remove();
	})
	$('#cancelDispatchSpecificationModal').on('hidden.bs.modal', function (event) {
		$('#cancelDispatchSpecificationModal input[name="id"]').val('');
	})

	// Добавить|Редактировать спецификацию
	$('#addEditSpecificationModal form').validate({ 
		rules: {
            number: {required: true},
            date: {required: true},
            account_id: {required: true},
        },
        messages: {
            number: {required: ''},
            date: {required: ''},  
            account_id: {required: ''},  
        },
        submitHandler: function(form) { 
			if ( +$('#list-product-total-amount input[name="amount"]').val() ) {
				// console.log($('#addEditSpecificationModal form').serialize());
				$.post(
					'/wp-admin/admin-post.php', 
					$('#addEditSpecificationModal form').serialize(),
					function(d){
						console.log(d)
						if ( d.success ) {
							get_list_account( {clients_id: d.clients_id} );
							get_list_specification( {clients_id: d.clients_id} );
							get_list_product(); // Получаем список продукции
							addEditSpecificationModal.hide();
						}
					}
				)
			} else {
				console.log('Нет данных для отправки формы');
			}
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element)
        }
	})

	// Добавить продукцию в список
	$('#addEditSpecificationModal').on('click', 'button[data-action="add-for-list"]', function(){
		if (!$('#new-product .error-message').hasClass('d-none')) {
			$('#new-product .error-message').addClass('d-none')
		}

		// Новый продукт
		if ( $(this).data('status') === 'new' ) {
			$('#new-product .loader-product').removeClass('d-none')
			let e = $('#new-product')
			let data = {
				'action' : 'forest_add_product',
				'name'   : e.find('select[data-name="new-name"]').val(), 
				'count'  : e.find('input[data-name="count"]').val(),
				'type'   : e.find('select[data-name="new-type"]').val(),
				'sort'   : e.find('select[data-name="new-sort"]').val(),
				'height' : e.find('input[data-name="new-height"]').val(),
				'width'  : e.find('input[data-name="new-width"]').val(),
				'length' : e.find('input[data-name="new-length"]').val(),
				'weight' : e.find('input[data-name="new-weight"]').val(),
			}
			$.post(
				'/wp-admin/admin-post.php', 
				data,
				function(d){
					console.log(d)
					if ( d.success ) {
						get_list_product() // Получаем список продукции
						for (let i = 0; i < +d.count; i++) { 
							add_row(d.data) // Добавляем строку
						}
						// Очищаем поля в форме
						const e = $('#new-product')
						e.find('input').val('')
						e.find('input[data-name="count"]').val('1')
						e.find('select').find('option:first').prop('selected', true);
					} else {
						$('#new-product .error-message').text( d.message )
						$('#new-product .error-message').removeClass('d-none')
					}
					$('#new-product .loader-product').addClass('d-none') 
				}
			)
		}
		// Существующий продукт
		if ( $(this).data('status') === 'search' ) {
			// Если поле поиска пустое - ни чего не делаем
			if (!$('#searchProduct').val()) return false;

			const elem   = $(this).parents('#search-product'), 
				  input  = elem.find('input[data-name="search"]').val(), 
				  count  = elem.find('input[data-name="count"]').val(),
				  opt    = +elem.find('select[data-name="opt"]').val(),
				  option = $('#listProductOptions option[value="'+input+'"]'),
				  data   = {
				  	"product_id":  option.data('product-id'),
				  	"name_search": option.val(),
				  	"granular":    option.data('granular'),
				  	"price":       (opt)?option.data('price-opt'):option.data('price'),
				  	"opt":         opt
				  };

		 	if (!option.data('granular')) {
				data.height = option.data('height')
				data.width  = option.data('width')
				data.length = option.data('length')
			} else {
				data.weight = option.data('weight')
			}

			console.log(data)

			if (!$.isEmptyObject(data)) {
				for (let i = 0; i < +count; i++) { 
					add_row(data); // Добавляем строку
				}
				// Очищаем строку поиска
				elem.find('#searchProduct').val('');
				elem.find('input[data-name="count"]').val('1');
			}
		}
	})

	// Удалить строку
	$('#addEditSpecificationModal').on('click', 'button[data-action="delete-row"]', function(){
		const table = $(this).parents('table');
		$(this).parents('.item').remove();
		if ( table.find('tbody tr').length === 0 ) {
			table.remove();
		} else {
			count_line( table.find('.item') );
			calculate_amount( table );
		}
	})

	//Добавить отгрузку
	$('#dispatchSpecificationModal').on('click', 'button[data-action="dispatchSpecification"]', function () {  
		$('#dispatchSpecificationModal form').validate({ 
			rules: {
				date_dispatch: {required: true},
				car_brand: {required: true},
				car_number: {required: true},
				car_driver: {required: true}
            },
            messages: {
				date_dispatch: {required: ''},
				car_brand: {required: ''},
				car_number: {required: ''},
				car_driver: {required: ''}
            },
            submitHandler: function(form) {
            	const modal = $('#dispatchSpecificationModal')
            	$(this).prop('disabled', true)
				$.post(
					'/wp-admin/admin-post.php', 
					$('#dispatchSpecificationModal form').serialize(), 
					function(d){
						console.log(d)
						if ( d.success ) {
							get_list_specification( {clients_id: d.clients_id} )
							get_client( d.clients_id )
							dispatchSpecificationModal.hide()
						}
					}
				)
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element)
            }
		})
	})

	//Отмена отгрузки
	$('#cancelDispatchSpecificationModal').on('click', 'button[data-action="cancelDispatchSpecification"]', function () {  
		$('#cancelDispatchSpecificationModal form').validate({ 
			rules: { id: {required: true} },
            messages: { id: {required: ''} },
            submitHandler: function(form) {
            	$(this).prop('disabled', true)
				$.post(
					'/wp-admin/admin-post.php', 
					$('#cancelDispatchSpecificationModal form').serialize(), 
					function(d){
						console.log(d)
						if ( d.success ) {
							get_list_specification( {clients_id: d.clients_id} )
							get_client( d.clients_id )
							cancelDispatchSpecificationModal.hide()
						}
					}
				)
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element)
            }
		})
	})

	//Печать
	$('body').on('click', '[data-action="print-pdf"]', function () {  
		let link = '/pdf?id=' + $(this).parents('.item').data('id') + '&action=print-' + $(this).data('doc')
		window.open( link, '_blank')
	}) 

	// -- ПРОДУКЦИЯ -- //
	$('#addEditSpecificationModal').on('click', 'button[data-action="show-new-product"]', function(){ 
		active_pane('new-product')
	})
	$('#addEditSpecificationModal').on('click', 'button[data-action="show-search-product"]', function(){ 
		active_pane('search-product')
	})

	// -- ПОВЕРКА -- //
	// Ввод только цифр
	$('#addEditSpecificationModal').on('input', 'input[data-type="number"]', function(){ 
		this.value = this.value.replace(/[^0-9.]/g, '')
	})

	// Редактирование input (потеря фокуса)
	$('#addEditSpecificationModal').on('blur', 'input', function() { 
		switch ($(this).attr('data-name')) {
			case 'new-height':
			case 'new-width':
			case 'new-length':
			case 'new-weight':
				check_input();
				break;
			case 'count':
			case 'price':
				calculate_item( $(this).parents('.item') );
				break;
		}
	})
	// Доставка
	$('#addEditSpecificationModal #delivery').on('change', 'input[name="status-delivery"]', function(){
		if ($(this).is(':checked')){
			check_delivery(1, 1, $('#list-about-client .address td:last').text());
		} else {
			check_delivery();
		}
		calculate_total_amount();
	})
	// Стоимость доставки
	$('#addEditSpecificationModal #delivery').on('change', 'input[data-type="number"]', function(){
		// Проверка 
		calculate_delivery();
		calculate_total_amount();
	})
	// Скидка
	$('#addEditSpecificationModal #discount').on('change', 'input[name="status-discount"]', function(){
		check_discount($(this).is(':checked'));
		calculate_total_amount();
	})
	// Стоимость скидки
	$('#addEditSpecificationModal #discount').on('change', 'input[data-type="number"]', function(){
		calculate_total_amount();
	})
	// Отгрузка - Адрес доставки
	$('#dispatchSpecificationModal').on('change', 'input#edit-address', function() {
		check_address()
	})
	// -- КОЕНЦ - ПОВЕРКА -- //

})( jQuery );
