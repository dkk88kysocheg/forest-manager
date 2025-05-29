(function( $ ) {
	'use strict';

	// Сформировать отчёт
	$('#uploading-report-data').validate({ 
		rules: {
            date_from: {required: true}, 
            date_to: {required: true}, 
        },
        messages: {
            date_from: {required: ''}, 
            date_to: {required: ''},
        },
        submitHandler: function(form) {
			let link = '/excel?' + $('#uploading-report-data').serialize(),
				today = new Date(),
				formattedDate = today.toISOString().substr(0, 10);

			window.open( link, '_blank');

			// Очищаем поля
			$('#uploading-report-data input[type="date"]').val( formattedDate );
			$('#uploading-report-data input[name="signed-document"]').prop('checked', false);
			$('#uploading-report-data #searchClient').val('');
			$('#uploading-report-data #searchProduct').val('');
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element)
        }
	})	

	// Общий отчет
	$('#summary-report-data').validate({ 
		rules: {
            date_from: {required: true}, 
            date_to:   {required: true}, 
        },
        messages: {
            date_from: {required: ''}, 
            date_to:   {required: ''},
        },
        submitHandler: function(form) {
			let link = '/excel?' + $('#summary-report-data').serialize(),
				today = new Date(),
				formattedDate = today.toISOString().substr(0, 10);

			window.open( link, '_blank')

			// Очищаем поля
			$('#summary-report-data input[type="date"]').val( formattedDate );
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element)
        }
	})

	// Смена отчета
	// показываем поиск продукции если выбран пункт "Отчет по продукции"
	$('#uploading-report-data .action').on('change', 'input[name="action"]', function() {
		const listClient = $('#uploading-report-data .action #listClient'),
			  listProduct = $('#uploading-report-data .action #listProduct')

		if ($(this).val() === 'report-product') {
			listClient.show()
			listProduct.show() 
		} else {
			listClient.hide()
			listClient.find('#searchClient').val('')
			listClient.find('input[name="client_id"]').val('') 

			listProduct.hide()
			listProduct.find('#searchProduct').val('')
			listProduct.find('input[name="product_id"]').val('') 
		} 
	})

	// Прописываем client-id
	$('#uploading-report-data #listClient').on('change', 'input#searchClient', function() {
		$('#uploading-report-data #listClient input[name="client_id"]').val( $('#listClientOptions option[value="' + $(this).val() + '"]').data('client-id') ) 
	}) 

	// Прописываем product-id
	$('#uploading-report-data #listProduct').on('change', 'input#searchProduct', function() {
		$('#uploading-report-data #listProduct input[name="product_id"]').val( $('#listProductOptions option[value="' + $(this).val() + '"]').data('product-id') ) 
	}) 


})( jQuery );