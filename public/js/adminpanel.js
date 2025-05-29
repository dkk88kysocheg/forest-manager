(function( $ ) {
	'use strict';

	function check_input_pages() {
		const list_pages = $('#pages input[name="page"]');
		let pages_active = '2';

		$.each(list_pages, function() {
			if ($(this).is(':checked')) { pages_active += ', ' + String($(this).val()); }
		})

		$('#pages input[name="pages"]').val( pages_active );
	}

	function check_input_view_list() {
		const list_user = $('#view input[name="user"]');
		let users_active = '',
			i = 0;

		$.each(list_user, function() {
			if ($(this).is(':checked')) {
				users_active += ((i)?', ':'') + String($(this).val());
				i++;
			}
		})

		$('#view input[name="view_list"]').val( users_active );
	}
	
	$().ready(function() {
		check_input_pages();
		check_input_view_list();
	})

	// Изменение доступности страницы
	$('#pages').on('change', 'input[name="page"]', function() {
		check_input_pages();
	})
	// Изменение видимости
	$('#view').on('change', 'input[name="view"]', function() {
		if ( +$(this).val() ) {
			$('#view-list').show();
		} else {
			$('#view-list').hide();
			$('#view input[name="view_list"]').val( '0' );
		}
	})
	// Изменение доступности страницы
	$('#view').on('change', 'input[name="user"]', function() {
		check_input_view_list();
	})
	// Добавление аватарки
    $('#profile-img').on('click', 'button', function(e) {
        e.preventDefault()
        let image = wp.media({ 
            title: 'Upload Image',
            multiple: false
        }).open()
        .on('select', function(e){
            let uploaded_image = image.state().get('selection').first(),
            	image_url = uploaded_image.toJSON().url;
            $('#profile-img input').val(image_url);
        })
    })
    // Добавление подписи
    $('#signature-img').on('click', 'button', function(e) {
        e.preventDefault()
        let image = wp.media({ 
            title: 'Upload Image',
            multiple: false
        }).open()
        .on('select', function(e){
            let uploaded_image = image.state().get('selection').first(),
            	image_url = uploaded_image.toJSON().url;
            $('#signature-img input').val(image_url);
        })
    })

})( jQuery );