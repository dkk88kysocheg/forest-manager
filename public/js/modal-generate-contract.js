import { 
	countLinesFactory,
	getListContractFactory
} from './module-public.js'

const count_line = countLinesFactory(jQuery),
	  get_list_contract = getListContractFactory(jQuery)

;(function( $ ) {
	'use strict';

	function check_payment() {
		const modal = $('#generateContractModal')
		if (+modal.find('input[name="payment"]:checked').val() === 2) {
			modal.find('input[data-type="payment_part"]').prop('disabled', false)
		} else {
			modal.find('input[data-type="payment_part"]').removeClass('error')
			modal.find('input[data-type="payment_part"]').val('')
			modal.find('input[data-type="payment_part"]').prop('disabled', true)
		}
	}

	function check_okpd_list() {
		const modal = $('#generateContractModal'),
			  list = $('#okpd-list')
		if (+modal.find('input[name="products"]:checked').val() === 2) {
			list.find('select').prop('disabled', false)
			list.find('input').prop('disabled', false)
			list.find('button').prop('disabled', false)
		} else {
			list.find('table tbody').empty()
			add_row() 
			list.find('select').prop('disabled', true)
			list.find('input').prop('disabled', true)
			list.find('button').prop('disabled', true) 
		}
	}

	function add_row() {
		const list = $('#okpd-list')

		list.find('table tbody').append($(
			'<tr class=\"item\">' +
                '<td class=\"number text-muted\"></td>' +
                '<td class=\"input\">' +
                    '<select class=\"form-select\" data-name=\"okpd\"></select>' +
                '</td>' +
                '<td class=\"volume\">' +
                	'<input type=\"text\" class=\"form-control py-2\" name="volume">' + 
                '</td>' +
                '<td class=\"button\">' +
                    '<button type=\"button\" class=\"btn btn-danger btn-sm btn-icon-text px-2\" data-action=\"delete-row\"><i class=\"icon-close btn-icon-prepend m-0\"></i></button>' +
                '</td>' +
            '</tr>'
		))
		$.each( list.find('ul li'), function() {
			list.find('.item:last select').append($( 
				'<option value="' + $(this).data('id') + '">' + $(this).data('value') + '</option>'
			))
		})
		// Считаем количество строк
		count_line(list.find('table tbody tr')) 
	}
	

	let generateContractModal = new bootstrap.Modal( document.getElementById('generateContractModal'), { 
		backdrop: 'static',
		keyboard: false
	})

	// Открываем модальное окна - Счёт
	$('body').on('click', '[data-open-modal="generateContractModal"]', function () {
		const modal = $('#generateContractModal'),
			  item = $(this).parents('.item');

		modal.find('input[name="contract_id"]').val( item.data('id') );

		check_payment();
		check_okpd_list();

		generateContractModal.show();
	})

	// Закрытие модальное окна
	$('#generateContractModal').on('click', '.btn-close', function(){ generateContractModal.hide() })

	// Очищаем все после того как модальное окно было скрыто
	$('#generateContractModal').on('hidden.bs.modal', function (event) { 
		const modal = $('#generateContractModal');
		// Очищаем
		modal.find('input[name="contract_id"]').val( '' );
		modal.find('input[name="type_contract"]:first').prop('checked', true);
		modal.find('input[name="products"]:first').prop('checked', true);
		modal.find('#okpd-list table tbody').empty();
		modal.find('button[data-action="generateContract"]').prop('disabled', false);
	})

	// Проверка оплаты
	$('#generateContractModal').on('change', 'input[name="payment"]', function() {
		check_payment();
	})

	// Ввод только цифр и проверка суммы
	$('#generateContractModal').on('input', 'input[data-type="payment_part"]', function(){ 
		this.value = this.value.replace(/[^0-9.]/g, '');
	})
	$('#generateContractModal').on('change', 'input[data-type="payment_part"]', function(){ 
		const modal = $('#generateContractModal');
		let one_part = +modal.find('input[name="one_part"]').val(),
			two_part = +modal.find('input[name="two_part"]').val();

		if ((one_part + two_part) !== 100) {
			modal.find('input[data-type="payment_part"]').addClass('error');
			modal.find('button[data-action="generateContract"]').prop('disabled', true);
		} else {
			modal.find('input[data-type="payment_part"]').removeClass('error');
			modal.find('button[data-action="generateContract"]').prop('disabled', false);
		}
	}) 

	// Проверка продукцию
	$('#generateContractModal').on('change', 'input[name="products"]', function() {
		check_okpd_list();
	})
	// Добавляем строку
	$('#okpd-list').on('click', 'button[data-action="add-row"]', function(){ 
		add_row();
	})

	// Удалить строку
	$('#okpd-list').on('click', 'button[data-action="delete-row"]', function(){ 
		$(this).parents('.item').remove()

		const list = $('#okpd-list')

		if (list.find('table tbody tr').length > 0) {
			// Считаем количество строк
			count_line(list.find('table tbody tr'));
		} else {
			add_row();
		}
	})

	// Отправить форму
	$('#generateContractModal').on('click', 'button[data-action="generateContract"]', function(){
		const modal = $('#generateContractModal'),
			  type_contract = modal.find('input[name="type_contract"]:checked').val(),
			  products = modal.find('input[name="products"]:checked').val(),
			  data  = {
					action: modal.find('input[name="action"]').val(),
					contract_id: modal.find('input[name="contract_id"]').val(),
					type_contract: type_contract,
					products: products,
				};

		if (+products === 2) {
			data['list_okpd'] = [];
			$.each(modal.find('#okpd-list .item'), function() {
				data['list_okpd'].push({
					id: $(this).find('select[data-name="okpd"] option:checked').val(),
					volume: $(this).find('input[name="volume"]').val(), 
				})
			})
		}

		// Блокируем кнопку
		modal.find('button[data-action="generateContract"]').prop('disabled', true);
		console.log(data);
		$.post(
			'/wp-admin/admin-post.php', 
			data,
			function(d){
				modal.find('button[data-action="generateContract"]').prop('disabled', false);

				console.log(d);
				generateContractModal.hide();
				get_list_contract( $('#addEditClient input[name="id"]').val() );

				window.open(d.file, '_blank');
			}
		)
	})



})( jQuery );
