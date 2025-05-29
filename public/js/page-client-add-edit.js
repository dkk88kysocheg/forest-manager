import { 
	countLinesFactory,
	formatDateFactory,
	getClientFactory,
	getListLegalEntityFactory,
	getListPhysicalEntityFactory,
} from './module-public.js'

const count_line  = countLinesFactory(jQuery),
	  format_date = formatDateFactory(jQuery),
	  get_client  = getClientFactory(jQuery)

;(function( $ ) {
	'use strict';
	// Проверка. Блокировка полей для Юр лица
	function check_organization_form() {
		if (+$('select[name="organization_form"] option:checked').val() === 84) {
			// Очищаем
			$('#data-company input').val('')
			$('#data-bank input').val('')
			$('#data-address textarea').val('')
			$('#data-address input').prop('checked', false)
			// Блокируем
			$('#data-company input').prop('disabled', true)
			$('#data-bank input').prop('disabled', true)
			$('#data-decision-maker input').prop('disabled', true)
			$('#data-address input').prop('disabled', true)
			$('#data-address textarea').prop('disabled', true)

			// Блок Договора и Контакты
			$('#contracts').hide()
			$('#contacts').hide()
		} else {
			// Разблокироваем
			$('#data-company input').prop('disabled', false) 
			$('#data-bank input').prop('disabled', false)
			$('#data-decision-maker input').prop('disabled', false) 
			$('#data-address input').prop('disabled', false)
			$('#data-address textarea').prop('disabled', false)

			// Блок Договора и Контакты
			$('#contracts').show()
			$('#contacts').show()

			check_decision_maker()
		}
	}

	// Проверка. Лицо принимающее решение
	function check_decision_maker() {
		// Отмечаем первый вариант если нет отмеченных
		if ( !$('input[name="decision_maker"]').is(':checked') ) {
			$('#data-decision-maker input[name="decision_maker"]').last().prop('checked', true)
		}		

		let block = ( +$('input[name="decision_maker"]:checked').val() === 88 )?false:true
		$('#data-decision-maker input[name="decision_maker_own"]').prop('disabled', block)
		$('#data-decision-maker input[name="decision_maker_own"]').val('')
	}

	// Проверка адресов
	function check_address() {
		if ( $('#data-address input[name="matching_address"]').is(':checked') ) {
			$('#data-address textarea[name="postal_address"]').val('')
			$('#data-address textarea[name="postal_address"]').prop('disabled', true)
		} else {
			$('#data-address textarea[name="postal_address"]').prop('disabled', false)
			check_organization_form()
		}
	}
	
	$().ready(function() {
		// Проверки при загрузке страницы
		check_organization_form();
		check_decision_maker();
		check_address();
	})


	// Изменение формы организации
	$('#data-general').on('change', 'select[name="organization_form"]', function() { 
		check_organization_form();
	})

	$('#addEditClient').validate({ 
		rules: {
            name: {required: true},
            inn: {maxlength: 16},
            kpp: {maxlength: 9},
            ogrn: {minlength: 13, maxlength: 15},
            bank_name: {maxlength: 128}, 
            payment_account: {
            	minlength: 20,
            	maxlength: 20,
            },
            correspondent_account: {
            	minlength: 20,
            	maxlength: 20,
            },
            bik: {maxlength: 9},
        },
        messages: {
            name: {required: ''},
            inn: {maxlength: 'Превышено максимальное количество символов'},
            kpp: {maxlength: 'Превышено максимальное количество символов'},
            ogrn: {maxlength: 'Превышено максимальное количество символов'},
            bank_name: {maxlength: 'Превышено максимальное количество символов'},
            payment_account: {
            	minlength: 'Неправильно указан счёт',
            	maxlength: 'Превышено максимальное количество символов'
            },
            correspondent_account: {
            	minlength: 'Неправильно указан счёт',
            	maxlength: 'Превышено максимальное количество символов'
            },
            bik: {maxlength: 'Превышено максимальное количество символов'},
        },
        submitHandler: function(form) {
			$.post(
				'/wp-admin/admin-post.php', 
				$('#addEditClient').serialize(),
				function(d){
					console.log(d)
					if (d.success) {
						// Создание клиента
						if (d.code === 'add_client') {
							window.location.replace ( window.location.protocol + '//' + window.location.hostname + '/client/edit/?id=' + d.id )
						}
						// Редактирование клиента
						if (d.code === 'edit_client') {
							$('#addEditClient i.message').append($('<div class="small">Изменения успешно сохранены</div>'))
							setTimeout(function(){ $('#addEditClient i.message').empty() }, 2000)
						}
					}
				}
			)
		},
		errorPlacement: function(error, element) {
            error.insertAfter(element)
        }
	}) 

	/**
	 * Вводим только цифры в поля
	 * * phone
	 * * inn
	 * * kpp
	 * * ogrn
	 * * payment_account
	 * * correspondent_account
	 */
	$('#addEditClient').on('input', 'input', function() {
		if (
			this.name === 'phone' ||
			this.name === 'inn' ||
			this.name === 'kpp' ||
			this.name === 'ogrn' ||
			this.name === 'payment_account' ||
			this.name === 'correspondent_account'
		) {
			this.value = this.value.replace(/[^0-9]/g, '');
		}
	})

	// Проверка лиц принимающих решение
	$('#addEditClient').on('change', 'input[name="decision_maker"]', function() {
		check_decision_maker()
	})

	// Проверка адресов
	$('#addEditClient').on('change', 'input[name="matching_address"]', function() {
		check_address()
	})

})( jQuery );
