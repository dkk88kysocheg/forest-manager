<?php

/**
 * Виджет плагина. Выводит контент в зависимости от страницы
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

/**
 * УЖЕ ПОЛУЧНО:
 * class-forest-manager-general-function.php
 */

class Forest_Manager_Content_Widget extends WP_Widget {
	public function __construct() {
        $widget_options = array(
            'classname'   => 'content-widget',
            'description' => 'Наполнение в зависимости от текущей страницы', 
        );
        parent::__construct( '', 'Личный кабинет', $widget_options );
	}
	public function widget( $args, $instance ) {
        $post_id = get_the_ID();
        $user_id = get_current_user_id();

        function get_filemtime( $filename ) {
            return filemtime( plugin_dir_path( __DIR__ ) . 'public/js/' . $filename );
        }


        // Главная строница - Сводка
        if ($post_id === PAGE_ID__statistics) {
            require_once plugin_dir_path( __FILE__ ) . '/module/_statistics.php';
        }

        // Калькулятор
        if ($post_id === PAGE_ID__calculator) { 
            require_once plugin_dir_path( __FILE__ ) . '/module/_calculator.php';
        }


        // ПРОВЕРКА
        // Может пользователь просматривать данную страницу
        $user_pages  = get_the_author_meta( 'pages', $user_id );
        $array       = explode(',', $user_pages);
        $array_pages = [0];
        foreach ($array as $val) { $array_pages[] = (int) $val; }

        if (!array_search($post_id, $array_pages)) {
            ?><script>window.location.replace("/");</script><?php
        }


        switch ($post_id) {
            case PAGE_ID__legal_entity:
                // Юридические лица
                require_once plugin_dir_path( __FILE__ ) . '/module/_legal_entity.php';
                break;
            case PAGE_ID__physical_entity:
                // Физические лица
                require_once plugin_dir_path( __FILE__ ) . '/module/_physical_entity.php';
                break;
            case PAGE_ID__client:
                // Клиент
                $get_data__clients = Forest_Manager_General_Function::get_data__clients(['id' => $_GET['id']]);
                $client = array_shift($get_data__clients);

                require_once plugin_dir_path( __FILE__ ) . '/module/_client.php';
                require_once plugin_dir_path( __FILE__ ) . '/module/_modal_add_edit_account.php';
                require_once plugin_dir_path( __FILE__ ) . '/module/_modal_add_edit_specification.php';
                require_once plugin_dir_path( __FILE__ ) . '/module/_modal_deleted_account_specification.php';
                break;
            case PAGE_ID__client_edit:
            case PAGE_ID__client_add:
                // Создать/Редактировать клиента
                require_once plugin_dir_path( __FILE__ ) . '/module/_client_add_edit.php';
                break;
            case PAGE_ID__accounting:
                // Бухгалтерия - Учёт
                require_once plugin_dir_path( __FILE__ ) . '/module/_accounting.php';
                break;
            case PAGE_ID__reports:
                // Отчёты
                require_once plugin_dir_path( __FILE__ ) . '/module/_reports.php';
                break;
        }
	}
}

class Forest_Manager_Menu_Widget extends WP_Widget {
    public function __construct() {
        $widget_options = array(
            'classname'   => 'menu-widget',
            'description' => 'Вывод меню на страницу', 
        );
        parent::__construct( '', 'Меню', $widget_options );
    }
    public function widget( $args, $instance ) {
        $user_id     = get_current_user_id();
        $user_pages  = get_the_author_meta( 'pages', $user_id );
        $array_pages = explode(',', $user_pages);

        $pages = Forest_Manager_General_Function::get_pages([
            PAGE_ID__client,
            PAGE_ID__client_add,
            PAGE_ID__client_edit,
            PAGE_ID__pdf,
            PAGE_ID__excel,
            PAGE_ID__print
        ]); 

        // Проверка. Доступна ли страница пользователю
        if ($user_id !== 1) { // Кроме Администратора
            $id_current_page = (get_the_ID() === 57 || get_the_ID() === 59)?27:get_the_ID();

            $access = false;
            // Список страниц в админке
            foreach ($array_pages as $active_page) { 
                if (+$active_page === $id_current_page) { $access = true; } 
            }
            if (!$access) { ?><script>document.location.href = '/';</script><?php }
        }

        $content = '<div class="collapse show" id="main-menu"><ul class="nav flex-column sub-menu">';
        foreach ($pages as $page) {
            // Администратору доступны все страницы
            // Или страница есть в списке разрешенных
            if ($user_id === 1 || array_search($page->ID, $array_pages) !== false) {
                $content .= '<li class="nav-item">
                        <a class="nav-link" href="' . $page->guid . '">
                            <span class="menu-title">' . $page->post_title . '</span>
                        </a>
                    </li>';
            }
        }
        $content .= '</ul></div>';
        echo $content;
    }
}

class Forest_Manager_User_Widget extends WP_Widget {
    public function __construct() {
        $widget_options = array(
            'classname'   => 'user-widget',
            'description' => 'Вывод данных пользователя на страницу', 
        );
        parent::__construct( '', 'Пользователь', $widget_options );
    }
    public function widget( $args, $instance ) {
        require_once plugin_dir_path( __DIR__ ) . 'public/module/_user.php';
    }
}

class Forest_Manager_Pdf_Widget extends WP_Widget {
    public function __construct() {
        $widget_options = array(
            'classname'   => 'pdf-widget',
            'description' => 'Вывод документа PDF', 
        );
        parent::__construct( '', 'Вывод PDF', $widget_options );
    }
    public function widget( $args, $instance ) {

        if (empty($_GET)) { ?><script>window.location.replace("/");</script><?php } // если нет параметров, перебрасываем на главную

        require_once plugin_dir_path( __DIR__ ) . 'public/module/_pdf.php';
    }
}

class Forest_Manager_Excel_Widget extends WP_Widget {
    private $version = '1.0.0';

    public function __construct() {
        $widget_options = array(
            'classname'   => 'excel-widget',
            'description' => 'Вывод документа Excel', 
        );
        parent::__construct( '', 'Вывод Excel', $widget_options );
    }
    public function widget( $args, $instance ) {
        require_once plugin_dir_path( __DIR__ ) . 'public/module/_excel.php';
    }
}

// file_put_contents('api-log.txt',var_export($request,true).PHP_EOL,FILE_APPEND);