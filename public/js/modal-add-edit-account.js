import { 
	countLinesFactory,
	formatDateFactory,
	formatMoneyFactory,
	getListAccountFactory,
	getListSpecificationFactory,
	getClientFactory, 
} from './module-public.js'

const count_line = countLinesFactory(jQuery),
	  format_date = formatDateFactory(jQuery),
	  format_money = formatMoneyFactory(jQuery), 
	  get_list_account = getListAccountFactory(jQuery),
	  get_list_specification = getListSpecificationFactory(jQuery),
	  get_client = getClientFactory(jQuery)  

;(function( $ ) {
	'use strict';

	let addAccountModal = new bootstrap.Modal( document.getElementById('addAccountModal'), { backdrop: 'static', keyboard: false });

	// Открываем модальное окна - Счёт
	$('body').on('click', '[data-open-modal="addAccountModal"]', function () {
		const modal = $('#addAccountModal');
		let datetime   = new Date(),
			month      = '0' + (datetime.getMonth() + 1),
			day        = '0' + datetime.getDate(),
			number     = "СЧ" + String(datetime.getFullYear()).substr(2) + month.slice(-2) + day.slice(-2);

		modal.find('input[name="number"]').val( number );

		addAccountModal.show();
	})

	// Закрытие модальное окна
	$('#addAccountModal').on('click', '.btn-close', function(){ addAccountModal.hide(); })

	// Очищаем все после того как модальное окно было скрыто
	$('#addAccountModal').on('hidden.bs.modal', function (event) { 
		const modal = $('#addAccountModal');

		// Убираем ошибки input
		$.each( modal.find('input'), function() {
		 	if ($(this).hasClass('error')) {
		 		$(this).removeClass('error');
		 	}
		}) 
		modal.find('label.error').remove();
		// Очищаем списки
		modal.find('select[name="cash"] option:first').prop('selected', true);
		modal.find('input[name="amount"]').val('');
		modal.find('input[name="date"]').val('');
	})

	// Добавить счет
	$('#addAccountModal').on('click', 'button[data-action="addAccount"]', function () { 
		$('#addAccountModal form').validate({ 
			rules: {
                company_id: {required: true},
                number: {required: true},
                date: {required: true},
                amount: {required: true}
            },
            messages: {
                company_id: {required: ''},
                number: {required: ''},
                date: {required: ''}, 
                amount: {required: ''}
            },
            submitHandler: function(form) {
				// console.log($('#addAccountModal form').serialize());
				$.post(
					'/wp-admin/admin-post.php', 
					$('#addAccountModal form').serialize(),
					function(d){
						console.log(d)
						if ( d.success ) {
							addAccountModal.hide()

							get_list_account( {clients_id: d.clients_id} );
							get_list_specification( {clients_id: d.clients_id} );
							get_client( d.clients_id );
						}
					}
				)
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            }
		})
	})


	// -- ПОВЕРКА -- //
	// ввод только цифр и не больше максимального значени
	$('#addAccountModal').on('input', 'input[data-type="number"]', function(){ 
		this.value = this.value.replace(/[^0-9.]/g, '');
	})
	$('#accountPaymentModal').on('input', 'input[data-type="number"]', function(){ 
		this.value = this.value.replace(/[^0-9.]/g, '');
	})
	$('#accountPaymentModal').on('change', 'input[name="amount_paid"]', function(){
		let value = this.value;

		if (value < $(this).data('min')) {
			this.value = $(this).data('min');
		} else if (value > $(this).data('max')) {
			this.value = $(this).data('max');
		} else {
			this.value = value ;
		}
	})
	// -- КОЕНЦ - ПОВЕРКА -- //


})( jQuery );
