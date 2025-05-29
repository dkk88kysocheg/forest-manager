(function( $ ) {
	'use strict';

	let files; // переменная. будет содержать данные файлов

	$("#update-accounting").on( 'change', 'input[type="file"]', function( event ){
        files = this.files;
	});

	$("#update-accounting").on( 'click', 'button[data-action="recognizeFile"]', function( event ){
        $('#update-accounting .message').empty();

        const btn = $(this),
              input = $('#update-accounting input[type="file"]');

        if (!input.val()) return;

        btn.prop('disabled', true);

        event.stopPropagation(); // остановка всех текущих JS событий
        event.preventDefault();  // остановка дефолтного события для текущего элемента - клик для <a> тега

        // ничего не делаем если files пустой
        if( typeof files == 'undefined' ) return;

        // создадим объект данных формы
        let data = new FormData();

        // заполняем объект данных файлами в подходящем для отправки формате
        $.each( files, function( key, value ){
            data.append( key, value );
        });

        // добавим action для идентификации запроса
        data.append( 'action', 'forest_accounting_upload_file' );

        // AJAX запрос
        $.ajax({
            url         : '/wp-admin/admin-post.php',
            type        : 'POST', // важно!
            data        : data,
            cache       : false,
            dataType    : 'json',
            // отключаем обработку передаваемых данных, пусть передаются как есть
            processData : false,
            // отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
            contentType : false,
            // функция успешного ответа сервера
            success     : function( respond, status, jqXHR ){
                console.log(respond);
                if (respond.success) {
                    location.reload();
                } else {
                    $('#update-accounting .message').append( $('<span class="error mt-3 d-block text-danger small">' + respond.message + '</span>') );
                }
                
                btn.prop('disabled', false);
            },
            // функция ошибки ответа сервера
            error: function( jqXHR, status, errorThrown ){
                console.log('error');
                console.log(errorThrown);
                console.log(status);
                console.log(jqXHR);

                $('#update-accounting .message').append( $('<span class="error mt-3 d-block text-danger small">' + status + '</span>') );
            }
        });
	})

    // Получение cookie по ключу
    function getCookie(name) {
        var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }

    // После загрузки страницы
    $(window).on('load', function () {
        // Проверка cookies
        if ( getCookie('accounting-sorting') === undefined ) {
            let cookie_date = new Date();
            cookie_date.setYear(cookie_date.getFullYear() + 1);
            document.cookie = "accounting-sorting=slwh;path=/;domain=manager.forest-groups.ru;expires=" + cookie_date.toUTCString();
        }

        $.each( $('.select-sorting select option'), function(i,v) {
            let cookie = getCookie('accounting-sorting');
            if ($(this).val() === cookie) $(this).attr('selected', true);
        })
    });

    $('.select-sorting').on('change', 'select', function() {
        let cookie_date = new Date();
        cookie_date.setYear(cookie_date.getFullYear() + 1);
        document.cookie = "accounting-sorting=" + this.value + ";path=/;domain=manager.forest-groups.ru;expires=" + cookie_date.toUTCString();
        location.reload();
    })
 

})( jQuery );