// ЗАГРУЗЧИК
export const loaderFactory = ($) => (action) => {
	if (action === 'show') {
		// Показываем загрузчик
		$('body').addClass('loader-open')
		$('.loader-container .loader').show()
	}
	if (action === 'hide') {
		setTimeout(function(){
			// Скрываем загрузчик
			$('body').removeClass('loader-open')
			$('.loader-container .loader').hide()
		}, 250);
	}
}
const loader = loaderFactory(jQuery)

// Генерирование рандомной строки
export const randomLineFactory = ($) => (i) =>  {
	const abc = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
	let st = '';
	while (st.length < i) {
		st += abc[Math.floor(Math.random() * abc.length)];
	}
	return st
}
const random_line = randomLineFactory(jQuery)

// Форматирование даты в человеческую
export const formatDateFactory = ($) => (type, date, spacer = '.', dir='ltr', time=false) => {
	let newDate
	if (type === 'unix') { newDate = new Date( date * 1000 ) }
	if (type === 'input') { newDate = new Date( date ) }

	let day = (newDate.getDate() < 10 ? '0' : '') + newDate.getDate(),
		month = (newDate.getMonth() < 9 ? '0' : '') + (newDate.getMonth() + 1),
		year = newDate.getFullYear(),
		hours = (newDate.getHours() < 10 ? '0' : '') + newDate.getHours(),
		minutes = (newDate.getMinutes() < 10 ? '0' : '') + newDate.getMinutes()

	if (dir === 'rtl') { return year + spacer + month + spacer + day }
	return day + spacer + month + spacer + year + ((time)?' ' + hours +':' + minutes:'')
}
const format_date = formatDateFactory(jQuery) 
// Форматирование отображения денег
export const formatMoneyFactory = ($) => (number) => {
	/*
	* В переменной price приводим получаемую переменную в нужный вид:
	* 1. принудительно приводим тип в число с плавающей точкой,
	*    учли результат 'NAN' то по умолчанию 0
	* 2. фиксируем, что после точки только в сотых долях
	*/
	var price     = Number.prototype.toFixed.call(parseFloat(number) || 0, 2),
		//заменяем точку на запятую
		price_sep = price.replace(/(\D)/g, "."), 
		//добавляем пробел как разделитель в целых
		price_sep = price_sep.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ");  

	return price_sep + ' ₽'
}
const format_money = formatMoneyFactory(jQuery) 

// Посчитать строки
export const countLinesFactory = ($) => (data, indent = 0) =>  { 
	let n = ++indent
	$.each(data,function(index,value){
		$(this).find('td:first').text( n )  
		++n
	})
}
const count_line = countLinesFactory(jQuery) 

// Погинация 
export const paginationFactory = ($) => (btn) => {
	const container = btn.parents('.pagination-container'),
		  table     = btn.parents('.card').find('table'),
		  number    = +btn.data('page'),
		  max       = number * 10,
		  min       = max - 10,
		  p_max     = number + 3,
		  p_min     = number - 3;

	container.find('button').prop('disabled', false);
	container.find('button').removeClass('d-none');
	
	btn.prop('disabled', true);
	table.find('tfoot').addClass('d-none'); // Скрываем Итого

	// Скрываем данные которые не входят в диапозон
	$.each(table.find('.item'), function(key, value){
		let n = key++;
		if (!+number) {
			$(this).removeClass('d-none');
			table.find('tfoot').removeClass('d-none'); // Скрываем Итого
		} else {
			if (n >= min && n < max) {
				$(this).removeClass('d-none');
			} else {
				$(this).addClass('d-none');
			}
		}
	})

	// Скрываем лишние пункты
	$.each(container.find('button'), function(key, value){
		if (!key) return;

		if (!(key >= p_min && key <= p_max)) {
			$(this).addClass('d-none');
		}
	})
}
const pagination = paginationFactory(jQuery) 

// == ПОЛУЧИТЬ == //
// Получить список Юридических лиц
export const getListLegalEntityFactory = ($) => {
	$.get(
		'/wp-admin/admin-post.php',
		{ 
			action: 'forest_get_clients',  
			organization_form: 'legal-entity',
			view_list: $('#user-name').data('view-list')
		},
		function(d){
			console.log(d)
			if ( d.success) {
				// Очищаем
				$('#list-legal-entity tbody').empty();
				$('#legal-entity .card-button').empty();

				// Проверка. Есть ли элементы в списке
				if (d.data.length !== 0) {
					$.each(d.data,function(index,value){
						let id_button = random_line(8)
						$('#list-legal-entity tbody').append( $(
							'<tr class="item" data-id=\"' + value.id + '\">' +
								'<td class=\"text-muted text-center\"></td>' +
								'<td class=\"name\"><a data-action=\"open-client\">' + value.name + '</a></td>' +
								'<td class=\"inn text-center\">' + ((value.inn)?value.inn:'- - -') + '</td>' +
								'<td class=\"user text-center\">' + value.user_name + '</td>' + 
								'<td class=\"button py-0 text-end\">' +
									'<div class="dropdown dropstart">' +
										'<button type=\"button\" class=\"btn btn-sm btn-icon\" title=\"Опции\" id=\"' + id_button + '\" data-bs-toggle=\"dropdown\" data-bs-auto-close=\"true\" aria-expanded=\"false\" >' +
											'<i class=\"icon-options\"></i>' +
										'</button>' +
										'<ul class=\"dropdown-menu marker-right\" aria-labelledby=\"' + id_button + '\">' +
											'<li class=\"dropdown-item\" data-action=\"open-client\">Подробнее</li>' +
											'<li class=\"dropdown-item\" data-action=\"forest_edit_client\" data-id=\"' + value.id + '\">Редактировать</li>' +
										'</ul>' +
									'</div>' +
								'</td>' +
							'</tr>'
						));
					})
					count_line($('#list-legal-entity tbody tr'));
				} else {
					$('#list-legal-entity tbody').append( $('<tr><td colspan=\"6\" class=\"fst-italic text-muted text-center\">- - Временно пустует - -</td></tr>') );
				}

				// Если у пользователя есть права на редактирование - добавляем кнопку
				if (+$('#user-name').data('edit')) {
					$('#legal-entity .card-button').append($(
						'<button type=\"button\" class=\"btn btn-primary btn-icon btn-sm\" data-action=\"forest_add_client\" data-type=\"legal\">' +
							'<i class=\"icon-plus btn-icon-prepend\"></i>' +
						'</button>'
					));
				}	
			}
		}
	)
}  
// Получить список Физических лиц
export const getListPhysicalEntityFactory = ($) => { 
	$.get(
		'/wp-admin/admin-post.php',
		{ 
			action: 'forest_get_clients', 
			organization_form: 'physical-entity',
			view_list: $('#user-name').data('view-list'),
		},
		function(d){
			console.log(d)
			if ( d.success) {
				// Очищаем
				$('#list-physical-entity tbody').empty()
				$('#physical-entity .card-button').empty()

				// Проверка. Есть ли элементы в списке 
				if (d.data.length !== 0) {
					$.each(d.data,function(index,value){
						let id_button = random_line(8)
						$('#list-physical-entity tbody').append($(
							'<tr class="item" data-id=\"' + value.id + '\">' +
								'<td class=\"text-muted text-center\"></td>' +
								'<td class=\"name\"><a data-action=\"open-client\">' + value.name + '</a></td>' +
								'<td class=\"phone text-center\">' + ((value.phone)?value.phone:'- - -') + '</td>' +
								'<td class=\"email text-center\">' + ((value.email)?value.email:'- - -') + '</td>' +
								'<td class=\"user text-center\">' + value.user_name + '</td>' +
								'<td class=\"button py-0 text-end\">' +
									'<div class="dropdown dropstart">' +
										'<button type=\"button\" class=\"btn btn-sm btn-icon\" title=\"Опции\" id=\"' + id_button + '\" data-bs-toggle=\"dropdown\" data-bs-auto-close=\"true\" aria-expanded=\"false\" >' +
											'<i class=\"icon-options\"></i>' +
										'</button>' +
										'<ul class=\"dropdown-menu marker-right\" aria-labelledby=\"' + id_button + '\">' +
											'<li class=\"dropdown-item\" data-action=\"open-client\">Подробнее</li>' +
											'<li class=\"dropdown-item\" data-action=\"forest_edit_client\" data-id=\"' + value.id + '\">Редактировать</li>' +
										'</ul>' +
									'</div>' +
								'</td>' +
							'</tr>')
						)						
					})
					count_line($('#list-physical-entity tbody tr'))
				} else {
					$('#list-physical-entity tbody').append( $('<tr><td colspan=\"6\" class="fst-italic text-muted text-center">- - Временно пустует - -</td></tr>') ) 
				}

				// Если у пользователя есть права на редактирование - добавляем кнопку
				if (+$('#user-name').data('edit')) {
					$('#physical-entity .card-button').append($(
						'<button type=\"button\" class=\"btn btn-primary btn-icon btn-sm\" data-action=\"forest_add_client\" data-type=\"physical\">' +
							'<i class=\"icon-plus btn-icon-prepend\"></i>' +
						'</button>'
					))
				}
			}
		}
	)
}
// Получить данные Клиента
export const getClientFactory = ($) => (id) => {
	$.get(
		'/wp-admin/admin-post.php',
		{ action: 'forest_get_clients', id: id },
		function(d){
			console.log(d)
			if ( d.success) { 
				const data = d.data[0]

				// Сначала очищаем
				$('#name-client').empty()
				$('#list-about-client tbody').empty()
				$('#about-client .card-button').empty()

				
				if (+data.organization_form === 84) {
					// КЛИЕНТ //
					// Потом заполняем
					$('#name-client').text( data.name )
					$('#list-about-client tbody').append( $(
						'<tr class=\"phone\">' +
							'<td class=\"text-muted\">Телефон</td>' +
							'<td>' + ((data.phone)?data.phone:'- - -') + '</td>' +
						'</tr>' +
						'<tr class=\"email\">' +
							'<td class=\"text-muted\">Email</td>' +
							'<td>' + ((data.email)?data.email:'- - -') + '</td>' +
						'</tr>'
					))	
				} else {
					// КОМПАНИЯ //
					// Потом заполняем
					$('#name-client').text( data.name )
					$('#list-about-client tbody').append( $(
						'<tr class=\"inn\">' +
							'<td class=\"text-muted\">ИНН</td>' +
							'<td>' + ((data.inn)?data.inn:'- - -') + '</td>' +
						'</tr>' +
						'<tr class=\"address\">' +
							'<td class=\"text-muted\">Юр. адрес</td>' +
							'<td>' + ((data.legal_address)?data.legal_address:'- - -') + '</td>' +
						'</tr>' +
						'<tr class=\"contract\">' +
							'<td class=\"text-muted\">Договор</td>' +
							'<td class=\"list-contract\">' + ((+data.offer)?'<li>Работают по оферте</li>':'') + '</td>' +
						'</tr>' +
						'<tr class=\"contact\">' +
							'<td class=\"text-muted\">Контакты</td>' +
							'<td class=\"list-contact\"></td>' +
						'</tr>'
					))

					$.each(data.list_contract, function(index, value) {
						let cls = (value.old)?'old':''
						$('#list-about-client tbody .list-contract').append( $('<li>', {
							text: '№' + value.number + ' до ' + format_date('unix', value.date_completion), 
							class: cls,
						}))
					})
					$.each(data.list_contact, function(index, value) {
						$('#list-about-client tbody .list-contact').append( $(
							'<li class="d-flex mb-1"><div class="w-50">' + value.name + '</div><div class="w-50">' + value.phone + '</div></li>'
						))
					})
				}

				// Считаем баланс
				let total = +data.debet - +data.credit,
					sign = (total >= 0)?'':'- ',
					balance = (total >= 0)?total:-total,
					style = (total >= 0)?'text-success':'text-danger',
					icon = (total === 0)?'':(total > 0)?'<i class="icon-arrow-up-circle ms-3"></i>':'<i class="icon-arrow-down-circle ms-3"></i>'

				$('#list-about-client tbody').append( $(
					'<tr class=\"balance\">' +
						'<td class=\"text-muted\">Баланс</td>' +
						'<td class=\"' + style + '\">' + sign + format_money(balance) + icon + '</td>' +
					'</tr>'
				))

				// Если у пользователя есть права на редактирование - добавляем кнопку
				if (+$('#user-name').data('edit')) {
					$('#about-client .card-button').append($(
						'<button type=\"button\" class=\"btn btn-light btn-icon btn-sm\" data-action=\"forest_edit_client\" data-id=\"' + data.id + '\">' +
							'<i class=\"icon-pencil btn-icon-prepend m-0\"></i>' +
						'</button>'
					))
				}
			}
		}
	)
}

// Получить список Счетов
export const getListAccountFactory = ($) => (data) => {
	data['action'] = 'forest_get_list_account'

	// Если clients_id есть добавляем в список клиента
	// Если нет clients_id добавляем в общий список
	const elem = (data.clients_id)?$('#list-account-client'):$('#list-account-all')
	elem.addClass('loading')

	$.get(
		'/wp-admin/admin-post.php',
		data,
		function(d){
			console.log(d)
			if ( d.success ) {
				// Очищаем таблицу
				elem.find('tbody').empty()
				$('#account-client .card-button').empty()

				// Проверка. Есть ли элементы в списке 
				if (d.data.length !== 0) {
					$.each(d.data,function(index,value){
						let id_button = random_line(8),
							cash;

						switch (+value.cash) {
							case 0: cash = 'Безнал'; break;
							case 1: cash = 'Касса'; break;
							case 2: cash = 'Н/Л'; break;
						}

						elem.append($(
							'<tr class="item" data-id=\"' + value.id + '\" ' +
											 'data-client-id=\"' + value.clients_id + '\" ' + 
											 'data-number=\"' + value.number + '\" ' +  
											 'data-amount=\"' + value.amount + '\">' + 
								'<td class=\"text-muted text-center\"></td>' +
								'<td class=\"number text-center\">№' + value.number + '</td>' + 
								'<td class=\"date text-center\">' + format_date('unix', value.date_creation) + '</td>' +
								'<td class=\"cash text-center\">' + cash + '</td>' +
								'<td class=\"user text-center\">' + value.user_name + '</td>' +
								'<td class=\"amount text-center\">' + format_money(value.amount) + '</td>' + 
								'<td class=\"button py-0 text-end\">' +
									'<div class="dropdown dropstart">' +
										'<button type=\"button\" class=\"btn btn-sm btn-icon\" title=\"Опции\" id=\"' + id_button + '\" data-bs-toggle=\"dropdown\" data-bs-auto-close=\"true\" aria-expanded=\"false\" >' +
											'<i class=\"icon-options\"></i>' +
										'</button>' +
										'<ul class=\"dropdown-menu marker-right\" aria-labelledby=\"' + id_button + '\">' +
											'<li class=\"dropdown-item\" data-open-modal=\"deletedAccountSpecificationModal\" data-action-modal=\"forest_deleted_account\">Удалить</li>' +
										'</ul>' +
									'</div>' +
								'</td>' +
							'</tr>')
						)
					})
					// Считаем количество строк
					count_line(elem.find('tbody tr')) 

					// Считаем сумму всех счетов и сумму платежей
					let total_amount = 0,
						total_amount_paid = 0
					$.each(elem.find('tbody tr'), function() {
						if(!$(this).hasClass('d-none')) {
							total_amount += +$(this).data('amount')
							total_amount_paid += +$(this).data('amount-paid')
						}
					})
					elem.find('tfoot').empty()
					elem.find('tfoot').append($(
						'<tr>' +
							'<td colspan="6" class="text-end"><b>Общий итог:</b></td>' + 
							'<td class="amount text-center"><b>' + format_money(total_amount.toFixed(2)) + '</b></td>' +
							'<td class="amount-paid text-center"><b>' + format_money(total_amount_paid.toFixed(2)) + '</b></td>' +
							'<td></td>' +
						'</tr>'
					))

					if (data.clients_id) { // Добавляем погинацию
						const pagination_list = $('#account-client').find('.pagination-container .list')
						pagination_list.empty() // Очищаем 

						let l = d.data.length,
							c = Math.ceil(l / 10),
							i = 1

						while (i <= c) {
							pagination_list.append($('<button type="button" class="btn btn-secondary btn-md p-2 mx-1 text-dark" data-action="pagination" data-page="' + i + '">' + i + '</button>'))
							if (i === c) { pagination( pagination_list.find('button:last-child') ) }
							i++
						}
					}
				} else {
					elem.append( $('<tr><td colspan=\"9\" class="fst-italic text-muted text-center">- - Временно пустует - -</td></tr>') )  
				}

				// Если у пользователя есть права на редактирование - добавляем кнопку 
				if (+$('#user-name').data('edit') && data.clients_id) {
					$('#account-client .card-button').append($(
						'<button type=\"button\" class=\"btn btn-primary btn-icon btn-sm\" data-open-modal=\"addAccountModal\" data-action-modal=\"forest_add_account\">' +
							'<i class="icon-plus btn-icon-prepend"></i>' +
						'</button>'
					))
				}

				elem.removeClass('loading')
			}
		}
	)
}
// Получить список Сертификаций
export const getListSpecificationFactory = ($) => (data) => { 
	data['action'] = 'forest_get_list_specification'

	// Если clients_id есть добавляем в список клиента
	// Если нет clients_id добавляем в общий список
	const elem = (data.clients_id)?$('#list-specification-client'):$('#list-specification-all')
	elem.addClass('loading')
	
	$.get(
		'/wp-admin/admin-post.php', 
		data,
		function(d){
			console.log(d)
			if ( d.success ) { 
				// Очищаем таблицу
				elem.find('tbody').empty()
				$('#specification-client .card-button').empty()

				// Проверка. Есть ли элементы в списке
				if (d.data.length !== 0) {
					$.each(d.data,function(index,value){
						let id_button = random_line(8),
							cash      = 'Безнал';

						switch (+value.cash) {
							case 1: cash = 'Касса'; break;
							case 2: cash = 'Н/Л'; break;
						}
						elem.append($(
							'<tr class="item" data-id=\"' + value.id + '\"' + 
											 'data-clients-id=\"' + value.clients_id + '\"' +
											 'data-amount=\"' + value.amount + '\" ' + 
											 'data-number=\"' + value.number + '\">' +
								'<td class=\"text-muted text-center\"></td>' +
								'<td class=\"number text-center\">№' + value.number + '</td>' + 
								'<td class=\"date text-center\">' + format_date('unix', value.date_creation) + '</td>' +
								'<td class=\"cash text-center\">' + cash + '</td>' +
								'<td class=\"user text-center\">' + value.user_name + '</td>' +
								'<td class=\"date-dispatch text-center\">' + ((!!value.date_dispatch)?format_date('unix', value.date_dispatch):'-') + '</td>' +
								'<td class=\"amount text-center\">' + format_money(value.amount) + '</td>' + 
								'<td class=\"button py-0 text-end\">' +
									'<div class="dropdown dropstart">' + 
										'<button type=\"button\" class=\"btn btn-sm btn-icon\" title=\"Опции\" id=\"' + id_button + '\" data-bs-toggle=\"dropdown\" data-bs-auto-close=\"true\" aria-expanded=\"false\" >' +
											'<i class=\"icon-options\"></i>' +
										'</button>' +
										'<ul class=\"dropdown-menu marker-right\" aria-labelledby=\"' + id_button + '\">' +
											'<li class=\"dropdown-item\" data-action=\"print-pdf\" data-doc=\"specification\">Напечатать спецификацию</li>' +   
										'</ul>' + 
									'</div>' +
								'</td>' +
							'</tr>')
						);
						// Если спец не заблокирована и у пользователя есть права на редактирование - добавляем кнопки
						if (!+value.block && +$('#user-name').data('edit')) {
							elem.find('.item:last .button .dropdown-menu').append($(
								'<li class=\"dropdown-item\" data-open-modal=\"addEditSpecificationModal\" data-action-modal=\"forest_edit_specification\">Редактировать</li>' +
								'<li class=\"dropdown-item\" data-open-modal=\"dispatchSpecificationModal\">Отгрузка</li>' + 
								'<li class=\"dropdown-item\" data-open-modal=\"deletedAccountSpecificationModal\" data-action-modal=\"forest_deleted_specification\">Удалить</li>'
							))
						}
						// Если спец заблокирована - добавляем кнопки
						if (+value.block) {
							elem.find('.item:last .button .dropdown-menu').append($( '<li class=\"dropdown-item\" data-action=\"print-pdf\" data-doc=\"invoice\">Напечатать накладную</li>' ))
						}
						// ДОПОЛНИТЕЛЬНО 
						// У администратора у возможность редактировать счёт даже после блокировки
						if (+value.block && +$('#user-name').data('id-user') === 1) {
							elem.find('.item:last .button .dropdown-menu').append($(
								'<li class=\"dropdown-item\" data-open-modal=\"addEditSpecificationModal\" data-action-modal=\"forest_edit_specification\">Редактировать</li>' + 
								'<li class=\"dropdown-item\" data-open-modal=\"cancelDispatchSpecificationModal\">Отмена отгрузки</li>'
							))
						}
					})
					// Считаем количество строк
					count_line(elem.find('tbody tr'))

					// Считаем сумму спецификаций
					let total_amount = 0
					$.each(elem.find('tbody tr'), function() {
						if(!$(this).hasClass('d-none')) {
							total_amount += +$(this).data('amount')
						}
					})
					elem.find('tfoot').empty()
					elem.find('tfoot').append($(
						'<tr>' +
							'<td colspan="7" class="text-end"><b>Общий итог:</b></td>' + 
							'<td class="amount text-center"><b>' + format_money(total_amount.toFixed(2)) + '</b></td>' +
							'<td></td>' +
						'</tr>'
					))
					
					if (data.clients_id) { // Добавляем погинацию
						const pagination_list = $('#specification-client').find('.pagination-container .list')
						pagination_list.empty() // Очищаем 

						let l = d.data.length,
							c = Math.ceil(l / 10),
							i = 1

						while (i <= c) {
							pagination_list.append($('<button type="button" class="btn btn-secondary btn-md p-2 mx-1 text-dark" data-action="pagination" data-page="' + i + '">' + i + '</button>'))
							if (i === c) { pagination( pagination_list.find('button:last-child') ) }
							i++
						}
					}
				} else {
					elem.append( $('<tr><td colspan=\"9\" class="fst-italic text-muted text-center">- - Временно пустует - -</td></tr>') ) 
				}

				// Если у пользователя есть права на редактирование - добавляем кнопку 
				if (+$('#user-name').data('edit') && data.clients_id) {
					$('#specification-client .card-button').append($(
						'<button type=\"button\" class=\"btn btn-primary btn-icon btn-sm\" data-open-modal=\"addEditSpecificationModal\" data-action-modal=\"forest_add_specification\">' +
							'<i class="icon-plus btn-icon-prepend"></i>' +
						'</button>'
					))
				}

				elem.removeClass('loading')
			}
		}
	)
}

// Получить список Договоров
export const getListContractFactory = ($) => (clients_id) => { 
	$.get(
		'/wp-admin/admin-post.php',
		{ 
			action: 'forest_get_list_contract',  
			clients_id: clients_id,
		},
		function(d){
			console.log(d)

			const table = $('#list-contract')
			table.find('tbody').empty()

			if (d.data.length !== 0) {
				$.each(d.data, function(key, val) {
					let id_button = random_line(8),
						arr_file = (val.file)?val.file.split('/'):'',
						name_file = (arr_file[4])?arr_file[4]:'' 

					table.find('tbody').append($(
						'<tr class=\"item\" data-id=\"' + val.id + '\" ' +
										   'data-clients-id=\"' + val.clients_id + '\" ' +
										   'data-number=\"' + val.number + '\" ' +
										   'data-date-creation=\"' + format_date('unix', val.date_creation, '-', 'rtl') + '\" ' +
										   'data-date-completion=\"' + format_date('unix', val.date_completion, '-', 'rtl')+ '\" ' +  
										   'data-days=\"' + val.days + '\" >' +
							'<td class=\"text-muted text-center\"></td>' +
							'<td class=\"number-contract\">' + val.number + '</td>' +
							'<td class=\"date-contract\">' + format_date('unix', val.date_completion) + '</td>' +
							'<td class=\"file-contract\">' + ((val.file)?'<a class="link-icon" href="' + val.file + '" target="_blank"><i class="icon-doc"></i></a>':'') + '</td>' + 
							'<td class=\"button py-0 text-end\">' +
								'<div class=\"dropdown dropstart\">' +
									'<button type=\"button\" class=\"btn btn-sm btn-icon\" title=\"Опции\" id=\"' + id_button + '\" data-bs-toggle=\"dropdown\" data-bs-auto-close=\"true\" aria-expanded=\"false\" >' +
										'<i class=\"icon-options\"></i>' +
									'</button>' +
									'<ul class=\"dropdown-menu marker-right\" aria-labelledby=\"' + id_button + '\">' +
										'<li class=\"dropdown-item\" data-open-modal=\"addEditContractModal\" data-action=\"edit\">Редактировать</li>' +
										'<li class=\"dropdown-item\" data-open-modal=\"generateContractModal\">Сгенерировать файл</li>' +
										'<li class=\"dropdown-item\" data-open-modal=\"addDeleteFileContractModal\" data-action=\"' + ((val.file)?'delete':'add') + '\" data-name=\"' + name_file + '\">' + ((val.file)?'Удалить файл':'Добавить файл') + '</li>' +
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

// Получить список комментариев
export const getMessagesFactory = ($) => (id) => {
	const data = { 
		action: 'forest_get_messages',
		clients_id: id,
	}
	$.get(
		'/wp-admin/admin-post.php',
		data,
		function(d){
			console.log(d)
			let container = $('#messages-view .container-inner')
			// Очищаем
			container.empty()
			$.each(d.data, function(kety, item) {
				let owner = (+item.user_id === +$('#user-name').data('id-user') || +$('#user-name').data('id-user') === 1)?true:false
				container.append($(
					'<div class="item mb-3 me-2 ' + ((owner)?'owner':'') + '" data-id="' + item.id + '">' +
						'<div class="item-container w-100">' + 
							'<div class="item-text w-75 border rounded-3 px-3 py-2">' + item.text + '</div>' +  
						'</div>' + 
						'<span class="pt-1">' + item.user_name + ', ' + format_date('unix', item.date_creation, '.', 'ltr', true) + '</span>' +  
					'</div>' 
				))
				if (owner) {
					container.find('.item:last .item-container').prepend($(
						'<div class="item-button">' + 
							'<button class="btn btn-warning btn-sm btn-icon-text px-1 me-1" data-action="editMessage"><i class="icon-pencil btn-icon-prepend m-0"></i></button>' +
							'<button class="btn btn-danger btn-sm btn-icon-text px-1 me-1" data-action="deleteMessageModal"><i class="icon-trash btn-icon-prepend m-0"></i></button>' +
						'</div>'
					))
				}
			})
			// Прокручиваем вниз 
			$('#messages-view .scrollable').scrollTop($('#messages-view .scrollable')[0].scrollHeight)
		}
	)
}
const get_messages = getMessagesFactory(jQuery)
// Добавить комментарий
export const addEditMessagesFactory = ($) => (data) => { 
	data.action = 'forest_add_edit_message'
	let form
	if (data.type_message === 'comment') {
		form = $('#comments-inter')
		form.find('textarea[name="comment"]').attr('disabled', true)
		form.find('button').attr('disabled', true)
	}
	$.post( 
		'/wp-admin/admin-post.php',
		data,
		function(d){
			console.log(d)
			// Очищаем информационное поле
			form.find('.form-info').empty()

			if (d.success) { 
				if (data.type_message === 'comment') {
					form.find('textarea[name="comment"]').val('')
					form.find('input[name="id"]').val('')
				}
				get_messages(data.clients_id)
			} else {
				form.find('.form-info').append($('<div>', {
					class: 'error small text-danger',
					text: d.message
				}))
			}
			form.find('textarea[name="comment"]').attr('disabled', false)
			form.find('button').attr('disabled', false)
		}
	)
}
// Удалить сообщение
export const deleteMessagesFactory = ($) => (data) => { 
	$.post(
		'/wp-admin/admin-post.php',
		{
			action: 'forest_delete_message', 
			id: data.id
		},
		function(d){
			console.log(d)
			const form = $('#comments-inter')
			// Очищаем информационное поле
			form.find('.form-info').empty()

			if (d.success) {
				get_messages(data.clients_id)
			} else {
				form.find('.form-info').append($('<div>', {
					class: 'error small text-danger',
					text: d.message
				}))
			}
		}
	)
}