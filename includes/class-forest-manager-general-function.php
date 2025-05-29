<?php

/**
 * Общие функции который используются в плагине
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/includes 
 */

class Forest_Manager_General_Function {
    private $forest_manager;
    
    private $version;
    
    public function __construct( $forest_manager, $version ) {
        $this->forest_manager = $forest_manager;
        $this->version = $version;
    }

    public static function get_version() { 
        return '1.3.2';
    }

    // ФУНКЦИИ не попадающие под категории
    // Посчитать объём
    public static function calculate_volume($height, $width, $length, $count) {
        $volume_piece = (+$height/1000) * (+$width/1000) * (+$length/1000);
        // Если нет количество отправляем объёт 1 продукции
        if (!$count) { return $volume_piece; }
        $item_volume = round(($volume_piece * +$count), 3);
        return $item_volume; 
    }
    // Форматирование текста для записи в БД
    public static function text_formatting($text) {
        $text = str_replace(PHP_EOL, '<br>', $text); // Перенос строки 
        return $text;
    }
    // Дата изменения
    public static function set_date_change($id, $name) {
        self::db_operations('update', $name, ['date_change' => strtotime( "now" )], ['id' => $id]); 
    }
    // Добавляем лог
    public static function add_system_message($data) {
        if ($data['action'] === 'forest_add_account') {}
    }
    // Получить get параметры из url
    public static function get_url_query($url, $key = null) {
        $parts = parse_url($url); 
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query); 
            if (is_null($key)) {
                return $query;
            } elseif (isset($query[$key])) {
                return $query[$key];
            }        
        }
     
        return false;
    }
    // Получить значение - Расчет
    private static function get_value_cash($i) {
        switch (+$i) {
            case 0: return 'Безналичный';
            case 1: return 'Касса';
            case 2: return 'Н/Л';
        }
    }
    // Строка в номер
    private static function string_to_number($str) {
        $number = str_replace([' ', ','], ['','.'], $str);
        return ($number)?+$number:0;
    }


    // РАБОТА С БД
    // Простые запросы в БД (создать/обновить/удалить)
    public static function db_operations($action, $name, $data, $data_secondary = null) {
        global $wpdb;
        if ($action === 'insert') {
            return ( $wpdb->insert("forest_{$name}", $data) ) ? $wpdb->insert_id : false;
        }
        if ($action === 'update') {
            return ( $wpdb->update("forest_{$name}", $data, $data_secondary) ) ? true : false;
        }
        if ($action === 'delete') {
            return ( $wpdb->delete("forest_{$name}", $data) ) ? true : false;
        } 
    }
    // Добавить столбец
    public static function db_add_column($name_table, $name_column, $parameters) {
        global $wpdb;
        $request = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "' . $name_table . '" AND COLUMN_NAME = "' . $name_column . '"';

        if (!empty($wpdb->query("$request"))) {
            return true;
        } else {
            $request = 'ALTER TABLE ' . $name_table . ' ADD COLUMN ' . $name_column . ' ' . $parameters;
            return ( $wpdb->query("$request") )?true:false;
        }
    }
    // Удалить столбец
    public static function db_drop_column($name_table, $name_column) {
        global $wpdb;
        $request = 'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = "' . $name_table . '" AND COLUMN_NAME = "' . $name_column . '"';

        if (empty($wpdb->query("$request"))) {
            return true;
        } else {
            $request = 'ALTER TABLE ' . $name_table . ' DROP COLUMN ' . $name_column;
            return ( $wpdb->query("$request") )?true:false;
        }
    }
    // Удалить таблицу
    public static function db_drop_table($name) {
        global $wpdb;
        $request = 'DROP TABLE ' . $name;
        return ( $wpdb->query("$request") )?true:false;
    }

    // ПРОВЕРКА
    // Проверка запроса
    public static function check_data_access($type, $data) {
        if ( $_SERVER['REQUEST_METHOD'] !== $type ){ 
            // Отправляем ошибку
            echo wp_send_json([
                'success' => false,
                'code'    => 'data_error',
                'message' => 'Ошибка данных'
            ]);
            wp_die();
        }
        // Проверка hash (написать отдельно в general-function.php) 
        if ( false ){
            // Отправляем ошибку
            echo wp_send_json([
                'success' => false,
                'code'    => 'access_error',
                'message' => 'Отказано в доступе'
            ]);
            wp_die();
        }
    }
    // Проверяем значения 2ух массивов
    public static function check_match($data_main, $data_secondary) {
        // data_main - новый массив который нужно проверить
        // data_secondary - массив из бд с которым сравниваем
        foreach ($data_main as $key => $value) {
            if ($data_secondary[$key] != $value) { return true; }
        }
        // Нет совпадений
        return false;
    }

    // Функции ПОЛУЧИТЬ
    // Список доступных страниц
    public static function get_pages($exclude = []) {
        return get_pages([
            'sort_order' => 'ASC', 
            'sort_column' => 'menu_order', 
            'exclude' => $exclude
        ]); 
    }

    // Простая функция получения всей информации из таблиц
    public static function get_data( $name, $parameter = [], $order = [] ) {
        global $wpdb;
        $request = 'SELECT * FROM `forest_' . $name . '`';
        // Параметры запроса. Можно указать несколько параметров
        if (!empty($parameter)) {
            $n = count($parameter);
            $i = 1;
            $request .= ' WHERE';
            foreach ($parameter as $key => $value) {
                $request .= ' ' . $key . ' = ';
                $request .= (gettype($value) === 'integer' || gettype($value) === 'number')?$value:'\'' . $value . '\'';
                if ($n !== $i) { $request .= ' AND'; }
                $i += 1;
            }
        }
        // Сортировка. Можно указать несколько полей
        if (!empty($order)) {
            $n = count($order);
            $i = 1;
            $request .= ' ORDER BY';
            foreach ($order as $key => $value) {
                $request .= ' `' . $key . '` ' . $value;
                if ($n !== $i) { $request .= ','; }
                $i += 1;
            }
        }
        return $wpdb->get_results( "$request", ARRAY_A );
    }

    public static function get_data__clients($data = []) {
        /**
         * id - конкретный клиент
         * user_id - только конкретного пользователя
         * inn - конкретный клиент
         * hide - только скрытые или нет клиенты
         * view_list - клиенты каких пользователей доступны к просмотру
         * parameter - параметр показывающий каких именно клиентов нужно выбрать
         * * по умолчанию показываем всех
         * * plus - с положительным балансом
         * * minus - с отрицательным балансом
         * * null - только с нулевым балансом
         * * unequal - с неравным балансом (плюс и минус)
         */
        global $wpdb;
        $request = 'SELECT clients.*, users.display_name as user_name ';
        $request .= 'FROM `forest_clients` AS clients, `wp_users` AS users ';
        $request .= 'WHERE clients.user_id = users.id ';

        // Если параметры не переданы показываем всех
        if (!empty($data)) {
            // ID
            if (isset($data['id']))         { $request .= 'AND clients.id = ' . $data['id'] . ' '; } 
            // INN
            if (isset($data['inn']))        { $request .= 'AND clients.inn = ' . $data['inn'] . ' '; } 
            // User_id
            if (isset($data['user_id']))    { $request .= 'AND clients.user_id = ' . $data['user_id'] . ' '; } 
            // Hide
            if (isset($data['hide']) && !empty($data['hide']))           { $request .= 'AND clients.hide = ' . $data['hide'] . ' '; }
            // View list
            if (isset($data['view_list']) && !empty($data['view_list'])) { $request .= 'AND clients.user_id IN (' . $data['view_list'] . ') '; } 
            // Parameter
            if (isset($data['parameter'])) {
                switch ($data['parameter']) {
                    case 'plus':
                        $request .= 'AND clients.debet > clients.credit ';
                        break;
                    case 'minus':
                        $request .= 'AND clients.debet < clients.credit ';
                        break;
                    case 'null':
                        $request .= 'AND clients.debet = clients.credit ';
                        break;
                    case 'unequal':
                        $request .= 'AND clients.debet <> clients.credit ';
                        break;
                }
            }
        }
        return $wpdb->get_results( "$request", ARRAY_A );
    }

    public static function get_data__product_specification($specification_id) {
        global $wpdb;
        $request = 'SELECT conn.*, product.id AS product_id, spec.number as specification_number, ';
        $request .= 'product.name, product.name_search, product.height, product.width, product.length, product.granular, product.weight ';
        $request .= 'FROM forest_product_specification AS conn, forest_specification AS spec, forest_product AS product ';
        $request .= 'WHERE conn.product_id = product.id AND conn.specification_id = spec.id AND conn.specification_id = ' . $specification_id;
        $request .= ' ORDER BY conn.id ASC';
        return $wpdb->get_results( "$request", ARRAY_A );
    }


    public static function get_data__messages_full($data) {
        global $wpdb;
        $request = 'SELECT messages.*, guide.id AS guide_id, guide.value AS type_message, users.id AS user_id, users.display_name AS user_name ';
        $request .= 'FROM forest_messages AS messages, forest_guide AS guide, wp_users AS users ';
        $request .= 'WHERE messages.guide_id = guide.id AND messages.user_id = users.id AND messages.clients_id = ' . $data['clients_id'] . ' ';
        $request .= 'GROUP BY messages.id ';
        $request .= 'ORDER BY messages.date_creation ASC';
        return $wpdb->get_results( "$request", ARRAY_A );
    }

    public static function get_data__account_full($data) {
        global $wpdb;
        $request = 'SELECT account.*, clients.name AS client_name, users.display_name AS user_name  ';
        $request .= 'FROM forest_account AS account, forest_clients AS clients, wp_users AS users  ';
        $request .= 'WHERE account.deleted = ' . $data['deleted'] . ' AND account.clients_id = clients.id AND account.user_id = users.id ';

        // Клиент
        if ($data['clients_id']) {
            $request .= 'AND account.clients_id = ' . $data['clients_id'] . ' '; 
        }

        // ПЕРИОД //
        if (isset($data['period'])) {
            switch ($data['period']) {
                case 'today':
                    $date_from = strtotime( date('Y-m-d 00:00:00') ); 
                    break;
                case 'week':
                    $date_from = strtotime( date('Y-m-d 00:00:00', strtotime("-1 week")) );
                    break;
                case 'month':
                    $date_from = strtotime( date('Y-m-d 00:00:00', strtotime("-1 month")) );
                    break;
                case 'period':
                    $date_from = strtotime( date('Y-m-d 00:00:00', strtotime( $data['date_from'] )) );
                    $date_to   = strtotime( date('Y-m-d 23:59:59', strtotime( $data['date_to'] )) );
                    break;
            }

            $request .= 'AND account.date_creation >= ' . $date_from . ' AND account.date_creation <= ' . $date_to . ' ';
        }
        // НАЛИЧНЫЕ //
        if (isset($data['cash'])) {
            $request .= 'AND account.cash = ' . +$data['cash'] . ' ';
        }
        // ОПЛАТА //
        if (isset($data['payment'])) {
            $request .= 'AND account.payment = ' . $data['payment'] . ' '; 
        }
        // View list
        if (!empty($data['view_list'])) { $request .= 'AND account.user_id IN (' . $data['view_list'] . ') '; } 

        $request .= 'GROUP BY account.id '; 
        $request .= 'ORDER BY account.date_creation ASC';

        return $wpdb->get_results( "$request", ARRAY_A ); 
    }

    public static function get_data__specification_full($data) {
        global $wpdb;
        $request = 'SELECT specification.*, clients.name AS client_name, clients.organization_form AS client_organization_form, users.display_name AS user_name ';
        $request .= 'FROM forest_specification AS specification, forest_clients AS clients, wp_users AS users ';
        $request .= 'WHERE specification.deleted = ' . $data['deleted'] . ' AND specification.clients_id = clients.id AND specification.user_id = users.id '; 

        // Отсутствие клиента и компании
        if (isset($data['clients_id'])) {
            $request .= 'AND specification.clients_id = ' . $data['clients_id'] . ' '; 
        }
        // ПЕРИОД //
        if (isset($data['period'])) {
            $date_to = strtotime( date('Y-m-d 23:59:59') );
            switch ($data['period']) {
                case 'today':
                    $date_from = strtotime( date('Y-m-d 00:00:00') ); 
                    break;
                case 'week':
                    $date_from = strtotime( date('Y-m-d 00:00:00', strtotime("-1 week")) );
                    break;
                case 'month':
                    $date_from = strtotime( date('Y-m-d 00:00:00', strtotime("-1 month")) );
                    break;
                case 'period':
                    $date_from = strtotime( date('Y-m-d 00:00:00', strtotime( $data['date_from'] )) );
                    $date_to   = strtotime( date('Y-m-d 23:59:59', strtotime( $data['date_to'] )) );
                    break;
            }

            if (+$data['dispatch']) {
                $request .= 'AND specification.date_dispatch >= ' . $date_from . ' AND specification.date_dispatch <= ' . $date_to . ' ';
            } else {
                $request .= 'AND specification.date_creation >= ' . $date_from . ' AND specification.date_creation <= ' . $date_to . ' ';
            }
        }
        // НАЛИЧНЫЕ //
        if (isset($data['cash'])) {
            $request .= 'AND specification.cash = ' . +$data['cash'] . ' '; 
        }
        // ДОСТАВКА //
        if (isset($data['dispatch'])) {
            $request .= 'AND specification.date_dispatch IS ' . ((+$data['dispatch'])?'NOT ':'') . 'NULL '; 
        }
        // View list
        if (!empty($data['view_list'])) { $request .= 'AND specification.user_id IN (' . $data['view_list'] . ') '; } 

        $request .= 'GROUP BY specification.id '; 
        $request .= 'ORDER BY specification.date_creation ASC';

        return $wpdb->get_results( "$request", ARRAY_A );
    }

    public static function get_list__matches($table, $number) {
        global $wpdb;
        $request = 'SELECT * FROM forest_' . $table . ' WHERE `number` LIKE "%' . $number . '%"';
        return $wpdb->get_results( "$request", ARRAY_A );
    }

    // Функции ПЕЧАТЬ ДОКУМЕНТОВ
    public static function output__pdf_file($data) {
        $answer = [];
        // Потом пожно будет сделать переменной в бд
        $accountant = 'Матухненко Н.А.';
        $security   = 'Сычёв А.Н.';

        // Стили
        $style = '<style>
            h1 div {font-size:0.7em;}
            #signature td { height: 36px; vertical-align: bottom; }    
            #signature, 
            #additionally, #product-board, #product-granular { margin-bottom: 16px; }  
            #client, #invoice-head, #total { margin-bottom: 32px; } 
            #additionally .row-number, #product-board .row-number, #product-granular .row-number { width:2%; }

            #additionally .number, #product-board .number, #product-granular .number,
            #additionally .count, #product-board .count, #product-granular .count { width:9%; }
            #additionally .volume, #product-board .volume, #product-granular .weight { width:11%; }

            #additionally .price, #product-board .price, #product-granular .price, 
            #additionally .amount, #product-board .amount, #product-granular .amount { width:15%; } 

            #additionally, #product-board, #product-granular {
                vertical-align: top;
                border-color: #e8ecf1;
                caption-side: bottom;
                border-collapse: collapse;
                page-break-inside: avoid;
            }
            #delivery tr th, #delivery tr td,
            #additionally tr td, #additionally tr th,
            #product-board tr td, #product-board tr th,
            #product-granular tr td, #product-granular tr th {
                padding: 0 0.25rem 0.25rem; 
                border-bottom-width: 1px; 
            }
            thead, tbody, tfoot, tr, td, th {
                border-color: inherit;
                border-style: solid;
                border-width: 0;
            }
            #delivery tr th, #delivery tr td,
            #additionally thead tr th,
            #product-board thead tr th,
            #product-granular thead tr th { line-height: 1; font-weight: inherit; }
            #product-board .total_group td { font-weight: bold; }
            #product-board .okpd td { background-color: rgb(0 0 0 / 5%); }
            #product-board tfoot tr td, #product-granular tfoot tr td { font-size: 105%; font-weight: bold; border: none; }
            #total b { font-size: 0.9rem; } 

            thead tr th.name { text-align: left; }

            #list-in-report tr.item ul li {list-style: none; margin: 0; padding: 0;}
        </style>';

        // СПЕЦИФИКАЦИЯ //
        if ($data['action'] === 'print-specification') { 
            return self::print__specification([ 
                'style'            => $style,
                'specification_id' => $data['id'],
                'accountant'       => $accountant
            ]);
        }

        // НАКЛАДНАЯ на выезд//
        if ($data['action'] === 'print-invoice') { 
            return self::print__invoice([ 
                'style'            => $style,
                'specification_id' => $data['id'],
                'accountant'       => $accountant
            ]);
        }
    }
    // СПЕЦИФИКАЦИЯ //
    private static function print__specification($data) {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-forest-manager-contract.php';

        global $wpdb;

        $answer  = [];
        $content = $data['style']; // Добавляем стили

        $query_specification = $wpdb->prepare( "SELECT * FROM `forest_specification` WHERE id = %d", $data['specification_id'] );
        $data_specification  = $wpdb->get_row( $query_specification, ARRAY_A );

        // ДОГОВОР //
        if ($data_specification['contract_id'])
        {   // Договор указан //
            $query_contract = $wpdb->prepare( "SELECT * FROM `forest_contract` WHERE id = %d", $data_specification['contract_id'] );
            $data_contract  = $wpdb->get_row( $query_contract, ARRAY_A );

            $query_clients = $wpdb->prepare( "SELECT * FROM `forest_clients` WHERE id = %d", $data_contract['clients_id'] );
            $data_clients  = $wpdb->get_row( $query_clients, ARRAY_A );

            $query_company = $wpdb->prepare( "SELECT * FROM `forest_company` WHERE id = %d", $data_contract['company_id'] );
            $data_company  = $wpdb->get_row( $query_company, ARRAY_A );


            // ДОБАВЛЯЕМ ФИРМЕННЫЙ СТИЛЬ
            $content .= '<style>
                @page { margin-top: 32px; margin-bottom: 32px; margin-left: 0; margin-right: 0; } 
                ul {margin: 0; padding: 0;}
                ul li {list-style: none;}
                #delivery tr td { border-bottom: 1px solid; }
                #delivery tr:last-child td { border-bottom: none; }
                #product-granular tr th, #product-granular tr td,
                #product-board tr th, #product-board tr td { border: 1px solid; }
                h3 { font-size: 12px; margin: 0; padding: 0;}
                table.signature { white-space: nowrap; }
                table.signature .border { border-bottom: 1px solid #333333; }
            </style>';
            $content .= '<main>';

            $list_product = self::get_data__product_specification( $data_specification['id'] ); // Список продукции в спецификации
            // Сортировка товаров
            $list = [
                'board' => [],
                'granular' => [],
            ];
            foreach ($list_product as &$value) {
                if (!+$value['granular']) {
                    $list['board'][ $value['okpd_id'] ][] = $value;
                } else {
                    $list['granular'][] = $value;
                }
            }

            $content .= '<table id="annex" width="100%">
                <tr>
                    <td align="right">
                        Приложение №1<br/>
                        к договору купли продажи<br/>
                        № '. $data_contract['number'] .' от '. gmdate("d.m.Y", $data_contract['date_creation']) .'
                    </td>
                </tr>
            </table>';

            $content .= '<div id="title" style="text-align: center; font-size: 1rem; margin: 1rem 0;">СПЕЦИФИКАЦИЯ № ' . $data_specification['number'] . '</div>';

            $content .= '<table id="subtitle" width="100%">
                <tr>
                    <td>Смоленская обл., г. Вязьма</td>
                    <td align="right">'. gmdate("d.m.Y", $data_specification['date_creation']) .'</td>
                </tr>
            </table>';

            $content .= '<p>Во исполнения договора купли продажи № '. $data_contract['number'] .' от '. gmdate("d.m.Y", $data_contract['date_creation']) .'<br/>Стороны согласовали следующие:</p>';
            $content .= '<ul>';

            // 1. Наименование, количество и цена Товара:
            $content .= '<li><p>1. Наименование, количество и цена Товара:</p>';
            // Генерируем таблицу с продукцией
            $generate_table = self::generate_table( false, $list, +$data_specification['discount'] );
            $content .= $generate_table['content'];
            $content .= '<p>Общая сумма по заявке № ______________ от ______________ составляет <b>' . number_format($generate_table['total_amount'], 2, '.',' ') . ' в т.ч. НДС 20%</b></p>';
            $content .= '<p>В данную сумму так же включена погрузка отгружаемого Товара Поставщиком.</p>';
            $content .= '</li>';

            // 2. Порядок оплаты:
            $content .= '<li><p>2. Порядок оплаты:</p>';
            if ($data_contract['individual']) {
                $content .= '<ul><li><b>100% в день загрузки товара в транспортное средство.</b></li></ul>';
            } else {
                $content .= '<ul>
                    <li><b>1 платеж (Авансовый) в размере 25%</b> от договорной цены оплачивается Покупателем в течение 3 (трех) банковских дней с момента утверждения Сторонами Спецификации к настоящему Договору по выставленному счету. </li>
                    <li><b>2 платеж в размере 25%</b> от договорной цены оплачивается Покупателем в течении 3 (трех) банковских дней с момента получения уведомления от Поставщика о готовности 50% объема передаваемого Товара с приложением фотоотчета по выставленному счету.</li>
                    <li><b>3 платеж в размере 25%</b> от договорной цены оплачивается Покупателем в течении 3 (трех) банковских дней с момента получения уведомления от Поставщика о готовности 75% объема передаваемого Товара с приложением фотоотчета по выставленному счету.</li>
                    <li><b>4 платеж (окончательный расчет) в размере 25%</b> от договорной цены оплачивается Покупателем в течении 3 (трех) банковских дней с момента получения уведомления от Поставщика о готовности Товара к отгрузке по выставленному счету.</li>
                </ul>';
            }
            $content .= '</li>';

            // 3. Транспортировка ...
            if ((int)$data_specification['count_delivery']) {
                $count_delivery = (int) $data_specification['count_delivery'];
                $price_delivery = (float) $data_specification['price_delivery'];
                $total_price_delivery = $price_delivery * $count_delivery;
                $content .= '<li>
                    <p>3. Транспортировка Товара организуется силами Поставщика.</p>
                    <table width="100%" id="delivery" margin="0" padding="0">
                        <tbody>
                            <tr>
                                <td width="30%">Адрес доставки</td>
                                <td width="70%">' . $data_specification['address_delivery'] . '</td>
                            </tr>
                            <tr>
                                <td width="30%">Количество доставок</td>
                                <td width="70%">' . $count_delivery . '</td>
                            </tr>
                            <tr>
                                <td width="30%">Стоимость одной доставки</td>
                                <td width="70%">' . number_format($price_delivery, 2, '.',' ') . ' Р</td>
                            </tr>
                            <tr>
                                <td width="30%"><b>Общая стоимость доставки</b></td>
                                <td width="70%"><b>' . number_format($total_price_delivery, 2, '.',' ') . ' Р *</b></td>
                            </tr>
                        </tbody>
                    </table>
                    <p>* Доставка Товара специализированным транспортным средством Поставщика не входит в общую стоимость Товара и оплачивается отдельно на основании выставленного счета</p>
                </li>';
            } else {
                $content .= '<li>
                    <p>3. Транспортировка Товара организуется силами Покупателя до своего склада.</p>
                </li>';
            }

            // Формируем подписи
            $sides_provider = Forest_Manager_Contract::formatting_sides_contract($data_company);
            $sides_buyer    = Forest_Manager_Contract::formatting_sides_contract($data_clients, true);
            

            // 4. Подпись ...
            $content .= '<li>
                <p>4. Подписывая настоящую спецификацию Стороны, утвердили форму уведомления, направляемую Покупателю по готовности к передаче каждой отдельной партии Товара.</p>
                <table width="100%" class="provider">
                    <tr>
                        <td width="45%" valign="top">
                            ' . $sides_provider['reduction'] . '
                        </td>
                        <td width="10%"></td>
                        <td width="45%" valign="top">
                            ' . $sides_buyer['reduction'] . '
                        </td>
                    </tr>
                    <tr>
                        <td width="45%" valign="top">
                            ' . $sides_provider['signature'] . '
                        </td>
                        <td width="10%"></td>
                        <td width="45%" valign="top">
                            ' . $sides_buyer['signature'] . '
                        </td>
                    </tr>
                </table>
            </li>';

            $content .= '</ul>';
        } else 
        {   // Договор НЕ указан //

            // ДОБАВЛЯЕМ ФИРМЕННЫЙ СТИЛЬ
            $content .= require_once plugin_dir_path( __FILE__ ) . 'company/company_1.php';
            $content .= '<main>';

            $list_product = self::get_data__product_specification( $data_specification['id'] ); // Список продукции в спецификации
            // Сортировка товаров
            $list = [
                'board' => [],
                'granular' => [],
            ];
            foreach ($list_product as &$value) {
                if (!+$value['granular']) {
                    $list['board'][ $value['okpd_id'] ][] = $value;
                } else {
                    $list['granular'][] = $value;
                }
            }

            $content .='<h1 class="text-center">Спецификация №' . $data_specification['number'] . ' от ' . gmdate("d.m.Y", $data_specification['date_creation']) . '</h1>';

            // Добавляем данные о клиенте
            $get_data_clients = self::get_data( 'clients', ['id' => $data_specification['clients_id']] );
            $data_client = array_shift( $get_data_clients );
            $client_table = '<table id="client" width="100%">';
            if (+$data_client['organization_form'] === 84) {
                $client_table .= '<tr>
                                <td valign="top" width="50">Клиент:</td>
                                <td>' . $data_client['name'] . '</td>
                            </tr>
                            <tr>
                                <td valign="top" width="50">Телефон:</td> 
                                <td>' . $data_client['phone'] . '</td>
                            </tr>';
            } else {
                $client_table .= '<tr>
                                <td valign="top" width="50">Клиент:</td>
                                <td>' . $data_client['name'] . '</td>
                            </tr>
                            <tr>
                                <td valign="top" width="50">ИНН:</td> 
                                <td>' . $data_client['inn'] . '</td>
                            </tr>';
            }

            $str_cash = self::get_value_cash($data_specification['cash']);

            $client_table .= '<tr>
                                <td valign="top" width="50">Расчёт:</td>
                                <td>' . $str_cash . '</td>
                            </tr>';
            $client_table .= '</table>';
            $content .= $client_table;

            // Доставка
            if ((int)$data_specification['count_delivery']) {
                $count = (int) $data_specification['count_delivery'];
                $price = (float) $data_specification['price_delivery'];
                $list['delivery'] = [
                    'name'   => 'Доставка по адресу: ' . $data_specification['address_delivery'],
                    'count'  => $count,
                    'price'  => $price,
                    'amount' => ($count * $price),
                ];
            }

            // Генерируем таблицу с продукцией
            $generate_table = self::generate_table( false, $list, +$data_specification['discount'] );
            $content .= $generate_table['content'];


            // Получаем данные менеджера
            $user_signature_img = get_the_author_meta( 'signature_img', $data_specification['user_id'] );
            $user               = get_userdata( $data_specification['user_id']);
            $manager            = $user->data->display_name;

            $content .= '<div class="sign" style="position: relative;"> 
            <img width="150" src="' . $user_signature_img . '"  style="position: absolute; right: 100px; top: -10px; z-index: 1;">  
            <table id="signature" width="100%" style="position: relative; z-index: 10;">
                <tr>
                    <td width="17%">Бухгалтер</td> 
                    <td width="1%"></td>
                    <td style="border-bottom: 0.5pt solid;"></td>
                    <td>/ ' . $data['accountant'] . ' /</td>
                    <td width="10%">Менеджер</td> 
                    <td width="1%"></td>
                    <td style="border-bottom: 0.5pt solid;"></td>
                    <td>/ ' . $manager . ' /</td>
                </tr>
            </table>';
        }

        $content .= '</main>';

        $answer['name_file'] = 'specification-report-number-' . sprintf('%04d', $data_specification['id'] );
        $answer['content']   = $content;

        return $answer;
    }
    // НАКЛАДНАЯ на выезд //
    private static function print__invoice($data) {
        $answer     = [];
        $company_id = 1; // Форест групп
        $content    = $data['style']; // Добавляем стили

        $get_data_specification = self::get_data( 'specification', ['id' => $data['specification_id']] );
        $data_specification     = array_shift($get_data_specification); // Спецификация

        // ДОБАВЛЯЕМ ФИРМЕННЫЙ СТИЛЬ
        if (!$data_specification['cash']) {
            $content .= require_once plugin_dir_path( __FILE__ ) . 'company/company_' . $company_id . '.php';
        }

        $list_product = self::get_data__product_specification( $data_specification['id'] );

        // Сортировка товаров
        $list = [
            'board' => [],
            'granular' => [],
        ];
        foreach ($list_product as &$value) {
            if (!+$value['granular']) {
                $list['board'][ $value['okpd_id'] ][] = $value;
            } else {
                $list['granular'][] = $value;
            }
        }

        $content .= '<main>';
        $content .='<h1 class="text-center">
            Накладная на выезд
            <div>к спецификации №' . $data_specification['number'] . ' от ' . gmdate("d.m.Y", $data_specification['date_creation']) . '</div>
        </h1>';

        // Добавляем данные о клиенте
        $get_data_clients = self::get_data( 'clients', ['id' => $data_specification['clients_id']] );
        $data_client = array_shift( $get_data_clients );

        $client_table = '<table id="client" width="100%">';
        if (+$data_client['organization_form'] === 84) {
            $client_table .= '<tr>
                            <td valign="top" width="50">Клиент:</td>
                            <td>' . $data_client['name'] . '</td>
                        </tr>
                        <tr>
                            <td valign="top" width="50">Телефон:</td> 
                            <td>' . $data_client['phone'] . '</td>
                        </tr>'; 
        } else {
            $client_table .= '<tr>
                            <td valign="top" width="50">Клиент:</td>
                            <td>' . $data_client['name'] . '</td>
                        </tr>
                        <tr>
                            <td valign="top" width="50">ИНН:</td> 
                            <td>' . $data_client['inn'] . '</td>
                        </tr>';
        }

        $str_cash = self::get_value_cash($data_specification['cash']);

        $client_table .= '<tr>
                            <td valign="top" width="50">Расчёт:</td>
                            <td>' . $str_cash . '</td>
                        </tr>';
        $client_table .= '</table>';

        // Добавляем данные об отгрузке
        $data_dispatch = unserialize( $data_specification['data_dispatch'] );
        $dispatch_table = '<table id="dispatch" width="100%">
            <tr>
                <td valign="top" width="75">Марка машины:</td> 
                <td>' . (($data_dispatch['car_brand'])?$data_dispatch['car_brand']:'-') . '</td>
            </tr>
            <tr>
                <td valign="top" width="75">Номер машины:</td>
                <td>' . (($data_dispatch['car_number'])?$data_dispatch['car_number']:'-') . '</td>
            </tr>
            <tr>
                <td valign="top" width="75">Водитель:</td> 
                <td>' . $data_dispatch['car_driver'] . '</td> 
            </tr>
            <tr>
                <td valign="top" width="75">Дата отгрузки:</td>  
                <td>' . gmdate("d.m.Y", $data_specification['date_dispatch']) . '</td>
            </tr>
            <tr>
                <td valign="top" width="75">Адрес доставки:</td>  
                <td>' . ((!empty($data_specification['address_delivery']))?$data_specification['address_delivery']:'Самовывоз') . '</td>
            </tr>
        </table>';

        $content .= '<table id="invoice-head" width="100%"><tr>';
        $content .= '<td valign="top" width="40%">' . $client_table . '</td>';  
        $content .= '<td valign="top" width="60%">' . $dispatch_table . '</td>'; 
        $content .= '</tr></table>';

        $generate_table = self::generate_table( true, $list, +$data_specification['discount'] );
        $content .= $generate_table['content'];

        $additionally = [];
        // Доставка
        if ($data_specification['count_delivery']) {
            $additionally[] = [
                'name' => 'Доставка',
                'count' => $data_specification['count_delivery'],
                'price' => $data_specification['price_delivery'],
                'amount' => (+$data_specification['count_delivery'] * +$data_specification['price_delivery']),
            ];
        }


        // Получаем данные менеджера
        $user_signature_img = get_the_author_meta( 'signature_img', $data_specification['user_id'] );
        $user               = get_userdata( $data_specification['user_id'] );
        $manager            = $user->data->display_name;

        if (!$data_specification['cash']) {
            $content .= '<div class="sign" style="position: relative;"> 
                            <img width="150" src="' . $user_signature_img . '" style="position: absolute; right: 100px; top: -10px; z-index: 1;">  
                            <table id="signature" width="100%" style="position: relative; z-index: 10;">
                                <tr>
                                    <td width="17%">Выезд разрешаю</td> 
                                    <td width="1%"></td>
                                    <td style="border-bottom: 0.5pt solid;"></td>
                                    <td>/ Чечулин С.Д. /</td>
                                    <td width="10%">Менеджер</td> 
                                    <td width="1%"></td>
                                    <td style="border-bottom: 0.5pt solid;"></td>
                                    <td>/ ' . $manager . ' /</td>
                                </tr>
                                <tr>
                                    <td width="17%"></td>
                                    <td width="1%"></td>
                                    <td style="border-bottom: 0.5pt solid;"></td> 
                                    <td>/  Беляев Г.А. /</td>
                                    <td width="10%">Бухгалтерия</td>
                                    <td width="1%"></td>
                                    <td style="border-bottom: 0.5pt solid;"></td>
                                    <td>/ ' . $data['accountant'] . ' /</td>
                                </tr>
                                <tr>
                                    <td width="17%">Отгрузил</td>
                                    <td width="1%"></td>
                                    <td style="border-bottom: 0.5pt solid;"></td> 
                                    <td>/  Киселева И.И. /</td>
                                    <td width="10%">Принял</td> 
                                    <td width="1%"></td>
                                    <td style="border-bottom: 0.5pt solid;"></td>
                                    <td>/ ' . $data_dispatch['car_driver'] . ' /</td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                    <td colspan="4" style="padding-left:37%;">
                                        <i>УПД 2шт., QR код 1 шт., ТН 2шт.,<br>счёт 1шт., спецификация 1шт.</i>
                                    </td>
                                </tr>
                            </table>
                        </div>';
        }
        
        $content .= '</main>';

        $answer['name_file'] = 'specification-report-number-' . sprintf('%04d', $data_specification['id'] );
        $answer['content']   = $content;
        $answer['file']      = false;


        return $answer;
    }


    // Функции Excel файл
    public static function excel($data) {
        switch ($data['action']) {
            case 'report-dispatch':
                return self::excel__report_dispatch($data);
            case 'report-product':
                return self::excel__report_product($data);
            case 'report-debtor':
                return self::excel__report_debtor($data);
            case 'report-payment':
                return self::excel__report_payment($data);
            case 'report-clients':
                return self::excel__report_clients($data);
            case 'report-summary':
                return self::excel__report_summary($data);
        }
    }

    // ОТЧЁТ Отгрузки
    private static function excel__report_dispatch($data) {
        $physical_entity_id = 84; // ID взят из таблицы forest_guide

        $data['deleted']  = 0;
        $data['period']   = 'period';
        $data['dispatch'] = 1;

        $list = [];
        // Только безнал
        if ($data['cash'] === '0') {
            $list['cashless'] = self::get_data__specification_full( $data );
        }
        // Только наличные
        if ($data['cash'] === '1') {
            $list['cash'] = self::get_data__specification_full( $data );
        }
        // Только Н/Л
        if ($data['cash'] === '2') {
            $list['cashcash'] = self::get_data__specification_full( $data );
        }
        // Всё
        if ($data['cash'] === 'all') {
            $data['cash'] = '0';
            $list['cashless'] = self::get_data__specification_full( $data );
            $data['cash'] = '1';
            $list['cash'] = self::get_data__specification_full( $data );
            $data['cash'] = '2';
            $list['cashcash'] = self::get_data__specification_full( $data );
        }

        $total_amount    = 0;
        $total_product   = [];
        $manager_product = [];

        // Расчет по счету
        $account_settlement = [
            'cash'     => 'В кассу',
            'cashcash' => 'Н/Л',
            'cashless' => 'Безналичный расчёт',
        ];

        $answer = [];
        $answer[] = [
            'client'        => 'Клиент',
            'cash'          => 'Расчёт',
            'manager'       => 'Менеджер',
            'date'          => 'Дата отгрузки',
            'specification' => '№ спецификации',
            'name'          => 'Название',
            'count'         => 'Количество',
            'price'         => 'Цена',
            'volume'        => 'Объём',
            'weight'        => 'Вес',
            'amount'        => 'Сумма',
            'discount'      => 'Скидка',
            'delivery'      => 'Доставка',
        ];

        foreach ($list as $key => $settlement) {
            
            if (empty($settlement)) { break; } // Если нет данных - прерываем

            usort($settlement, function($a, $b) { return $b['date_dispatch'] <=> $a['date_dispatch']; });  // Сортировка по дате отгрузки

            $total_product_settlement = [];
            $total_amount_settlement  = 0;

            foreach ($settlement as $item) {
                // Показываем определённого Форму организации
                if ( $data['organization_form'] !== 'all') {
                    if ( 
                        (+$data['organization_form'] && +$item['client_organization_form'] === $physical_entity_id ) || 
                        (+!$data['organization_form'] && +$item['client_organization_form'] !== $physical_entity_id )
                    ) { continue; }
                }  

                // Добавляем данные о клиенте
                $get_data_clients = self::get_data( 'clients', ['id' => $item['clients_id']] );
                $data_client      = array_shift( $get_data_clients );

                $list_product_specification = self::get_data__product_specification( $item['id'] );

                $str_cash = self::get_value_cash($item['cash']);

                // Сортируем продукцию
                foreach ($list_product_specification as $value) {
                    $temp_arr = [
                        'client'        => (string) $data_client['name'],
                        'cash'          => $str_cash,
                        'manager'       => (string) $item['user_name'],
                        'date'          => (string) gmdate("d.m.Y", $item['date_dispatch']),
                        'specification' => (string) $value['specification_number'],
                        'name'          => (string) $value['name_search'],
                        'count'         => (string) $value['count'],
                        'price'         => number_format($value['price'], 2, ',',' '),
                        'volume'        => 0,
                        'weight'        => 0,
                        'amount'        => 0,
                        'discount'      => 0,
                        'delivery'      => 0,
                    ];

                    if (!+$value['granular']) {
                        $volume = self::calculate_volume($value['height'], $value['width'], $value['length'], $value['count']);
                        $temp_arr['volume'] = number_format($volume, 3, ',',' ');
                        $amount = +$volume * +$value['price'];
                    } else {
                        $weight = round((+$value['weight'] * +$value['count']), 3);
                        $temp_arr['weight'] = number_format($weight, 2, ',',' ');
                        $amount = round(((+$weight/1000) * +$value['price']), 2); 
                    }

                    $temp_arr['amount'] = number_format($amount, 2, ',',' ');

                    $answer[] = $temp_arr;
                }

                // Скидка и доставка
                $delivery = +$item['count_delivery'] * +$item['price_delivery'];

                $str_cash = self::get_value_cash($item['cash']);

                $answer[] = [
                    'client'        => (string) $data_client['name'],
                    'cash'          => $str_cash,
                    'manager'       => (string) $item['user_name'],
                    'date'          => (string) gmdate("d.m.Y", $item['date_dispatch']),
                    'specification' => (string) $item['number'],
                    'name'          => 'Скидка и доставка',
                    'count'         => 0,
                    'price'         => 0,
                    'volume'        => 0,
                    'weight'        => 0,
                    'amount'        => 0,
                    'discount'      => ($item['discount'])?number_format($item['discount'], 2, ',',' '):0,
                    'delivery'      => number_format($delivery, 2, ',',' '),
                ];
            }
        }

        return $answer;
    }
    // ОТЧЁТ По продукции
    private static function excel__report_product($data) {
        $data['deleted']  = 0;
        $data['period']   = 'period';
        $data['dispatch'] = 1;

        $list = [];
        // Только безнал
        if ($data['cash'] === '0') {
            $list['cashless'] = self::get_data__specification_full( $data );
        }
        // Только наличные
        if ($data['cash'] === '1') {
            $list['cash'] = self::get_data__specification_full( $data );
        }
        // Только Н/Л
        if ($data['cash'] === '2') {
            $list['cashcash'] = self::get_data__specification_full( $data );
        }
        // Всё
        if ($data['cash'] === 'all') {
            $data['cash'] = '0';
            $list['cashless'] = self::get_data__specification_full( $data );
            $data['cash'] = '1';
            $list['cash'] = self::get_data__specification_full( $data );
            $data['cash'] = '2';
            $list['cashcash'] = self::get_data__specification_full( $data );
        }

        $all_product = [];

        foreach ($list as $key => $settlement) {
            // Если есть данные
            if (!empty($settlement)) {
                usort($settlement, function($a, $b) { return $b['date_dispatch'] <=> $a['date_dispatch']; }); // Сортировка по дате отгрузке

                foreach ($settlement as $item) {
                    // Показываем определённого Форму организации
                    if ( $data['organization_form'] !== 'all') {
                        if ( 
                            (+$data['organization_form']  && +$item['client_organization_form'] === $physical_entity_id ) ||
                            (+!$data['organization_form'] && +$item['client_organization_form'] !== $physical_entity_id )
                        ) { continue; }
                    }

                    // Получаем список продукции
                    $list_product_specification = self::get_data__product_specification( $item['id'] );

                    foreach ($list_product_specification as $ps) {
                        $item_volume = self::calculate_volume($ps['height'], $ps['width'], $ps['length'], $ps['count']);
                        $item_amount = $item_volume * +$ps['price'];

                        if ( $all_product[ $ps['product_id'] ] ) {
                            $all_product[ $ps['product_id'] ]['count']  += +$ps['count'];
                            $all_product[ $ps['product_id'] ]['amount'] += +$item_amount;
                        } else {
                            $all_product[ $ps['product_id'] ] = [
                                'count'       => +$ps['count'],
                                'name_search' => $ps['name_search'],
                                'height'      => $ps['height'],
                                'width'       => $ps['width'],
                                'length'      => $ps['length'],
                                'granular'    => $ps['granular'],
                                'weight'      => $ps['weight'],
                                'amount'      => $item_amount,
                            ];
                        }
                    }
                }
            }
        }

        $answer = [];
        $answer[] = [
            'number' => '#',
            'name'   => 'Название',
            'count'  => 'Количество',
            'volume' => 'Объём',
            'amount' => 'Сумма',
        ];

        if ( !empty($all_product) ) {
            usort($all_product, function($a, $b) { return $a['name_search'] <=> $b['name_search']; }); // Сортировка по дате отгрузке
            $n = 1;

            foreach ($all_product as $product) {
                $volume = (+$product['height']/1000) * (+$product['width']/1000) * (+$product['length']/1000) * +$product['count'];
                $answer[] = [
                    'number' => (string) $n,
                    'name'   => (string) $product['name_search'],
                    'count'  => (string) $product['count'],
                    'volume' => number_format($volume, 3, ',',' '),
                    'amount' => number_format($product['amount'], 2, ',',' '),
                ];
                $n++;
            }
        }

        return $answer;
    }
    // ОТЧЁТ По должникам
    private static function excel__report_debtor($data) {
        $list_clients = Forest_Manager_General_Function::get_data__clients(['parameter' => 'unequal']);

        $answer = [];
        $title = [
            'clients_id' => '#',
            'name'       => 'Название',
            'inn'        => 'ИНН',
            'user_name'  => 'Менеджер',
            'balance'    => 'Баланс',
        ];

        foreach ($list_clients as &$client) {
            $balance = +$client['debet'] - +$client['credit'];
            $answer[] = [
                'clients_id' => (string) $client['id'],
                'name'       => (string) $client['name'],
                'inn'        => (string) $client['inn'],
                'user_name'  => (string) $client['user_name'],
                'balance'    => number_format($balance, 2, ',',' '),
            ];
        }

        // Сортировка
        usort($answer, function($a, $b) { return $a['balance'] <=> $b['balance']; }); 

        array_unshift($answer, $title);

        return $answer; 
    }
    // ОТЧЁТ По платежам
    private static function excel__report_payment($data) {
        global $wpdb;

        $answer = [
            'payment_id'     => '#',
            'date'           => 'Дата оплаты',
            'amount'         => 'Сумма оплаты',
            'number'         => 'Номер счёта',
            'account_amount' => 'Сумма счёта',
            'cash'           => 'Форма оплаты',
            'client_name'    => 'Клиент',
            'user_name'      => 'Менеджер',
        ];

        return $answer; 
    }
    // ОТЧЁТ По клиентам
    private static function excel__report_clients($data) {
        $physical_entity_id = 84; // ID взят из таблицы forest_guide

        $data['deleted']  = 0;
        $data['period']   = 'period';
        $data['dispatch'] = 1;

        $list = [];
        // Только безнал
        if ($data['cash'] === '0') {
            $list['cashless'] = self::get_data__specification_full( $data );
        }
        // Только наличные
        if ($data['cash'] === '1') {
            $list['cash'] = self::get_data__specification_full( $data );
        }
        // Только Н/Л
        if ($data['cash'] === '2') {
            $list['cashcash'] = self::get_data__specification_full( $data );
        }
        // Всё
        if ($data['cash'] === 'all') {
            $data['cash'] = '0';
            $list['cashless'] = self::get_data__specification_full( $data );
            $data['cash'] = '1';
            $list['cash'] = self::get_data__specification_full( $data );
            $data['cash'] = '2';
            $list['cashcash'] = self::get_data__specification_full( $data );
        }

        $total_amount    = 0;
        $total_product   = [];
        $manager_product = [];

        // Расчет по счету
        $account_settlement = [
            'cash'     => 'В кассу',
            'cashcash' => 'Н/Л',
            'cashless' => 'Безналичный расчёт',
        ];

        $answer = [];
        $answer[] = [
            'client'        => 'Клиент',
            'count'         => 'Количество',
            'volume'        => 'Объём',
            'weight'        => 'Вес',
            'amount'        => 'Сумма',
            'discount'      => 'Скидка',
            'delivery'      => 'Доставка',
        ];

        foreach ($list as $key => $settlement) {
            
            if (empty($settlement)) { break; } // Если нет данных - прерываем

            usort($settlement, function($a, $b) { return $b['date_dispatch'] <=> $a['date_dispatch']; });  // Сортировка по дате отгрузки

            $total_product_settlement = [];
            $total_amount_settlement  = 0;

            foreach ($settlement as $item) {
                // Показываем определённого Форму организации
                if ( $data['organization_form'] !== 'all') {
                    if ( 
                        (+$data['organization_form'] && +$item['client_organization_form'] === $physical_entity_id ) || 
                        (+!$data['organization_form'] && +$item['client_organization_form'] !== $physical_entity_id )
                    ) { continue; }
                }  

                // Добавляем данные о клиенте
                $get_data_clients = self::get_data( 'clients', ['id' => $item['clients_id']] );
                $data_client      = array_shift( $get_data_clients );

                $str_cash = self::get_value_cash($item['cash']);

                $list_product_specification = self::get_data__product_specification( $item['id'] );
                // Сортируем продукцию
                foreach ($list_product_specification as $value) {
                    $count  = 0;
                    $volume = 0;
                    $weight = 0;
                    $amount = 0;

                    $a_count  = self::string_to_number($answer[ $data_client['id'] ]['count']);
                    $total_volume = $a_volume = self::string_to_number($answer[ $data_client['id'] ]['volume']);
                    $total_weight = $a_weight = self::string_to_number($answer[ $data_client['id'] ]['weight']);
                    $total_amount = $a_amount = self::string_to_number($answer[ $data_client['id'] ]['amount']);


                    if (!+$value['granular']) {
                        $volume = self::calculate_volume($value['height'], $value['width'], $value['length'], $value['count']);
                        $total_volume = +$a_volume + $volume;

                        $amount = +$volume * +$value['price'];
                    } else {
                        $weight = round((+$value['weight'] * +$value['count']), 3);
                        $total_weight = +$a_weight + $weight;

                        $amount = round(((+$weight/1000) * +$value['price']), 2); 
                    }

                    $total_amount = +$a_amount + $amount;

                    $answer[ $data_client['id'] ] = [
                        'client' => (string) $data_client['name'],
                        'count'  => +$a_count + $value['count'],
                        'volume' => number_format($total_volume, 3, ',',' '),
                        'weight' => number_format($total_weight, 2, ',',' '),
                        'amount' => number_format($total_amount, 2, ',',' '),
                        'discount' => $answer[ $data_client['id'] ]['discount'],
                        'delivery' => $answer[ $data_client['id'] ]['delivery'],
                    ];
                }

                // Скидка и доставка
                $delivery = +$item['count_delivery'] * +$item['price_delivery'];

                $a_discount = self::string_to_number($answer[ $data_client['id'] ]['discount']);
                $a_delivery = self::string_to_number($answer[ $data_client['id'] ]['delivery']);

                $discount = ($item['discount'])?:0;

                $total_discount = $a_discount + $discount;
                $total_delivery = $a_delivery + $delivery;

                $answer[ $data_client['id'] ]['discount'] = number_format($total_discount, 2, ',',' ');
                $answer[ $data_client['id'] ]['delivery'] = number_format($total_delivery, 2, ',',' ');
            }
        }

        return $answer;
    }
    // ОТЧЁТ Сводный 
    private static function excel__report_summary($data) {
        $physical_entity_id = 84; // ID взят из таблицы forest_guide

        $data['deleted']  = 0;
        $data['period']   = 'period';
        $data['dispatch'] = 1;

        $list = [];

        $data['cash']     = '0';
        $list['cashless'] = self::get_data__specification_full( $data );

        $data['cash']     = '1';
        $list['cash']     = self::get_data__specification_full( $data );

        $data['cash']     = '2';
        $list['cashcash'] = self::get_data__specification_full( $data );


        $total_amount    = 0;
        $total_product   = [];
        $manager_product = [];

        // Расчет по счету
        $account_settlement = [
            'cash'     => 'В кассу',
            'cashcash' => 'Н/Л',
            'cashless' => 'Безналичный расчёт',
        ];

        $answer = [];
        $answer[] = [
            'client'        => 'Клиент',
            'count'         => 'Количество',
            'volume'        => 'Объём',
            'weight'        => 'Вес',
            'amount'        => 'Сумма',
            'discount'      => 'Скидка',
            'delivery'      => 'Доставка',
        ];


        return $answer;
    }


    // $remove_price - если true - удаляю цену
    private static function generate_table($remove_price, $data, $discount = 0) {
        $guide_data = self::get_data('guide');
        $content = '';
        $answer = [
            'total_amount' => 0,
        ];

        // Доски
        if (!empty($data['board'])) {
            $amount = [
                'all_volume' => 0,
                'all_amount' => 0,
            ];

            $content .= '<table id="product-board" width="100%">';
            $content .= '<thead>
                            <tr>
                                <th class="row-number text-muted text-center">#</th>
                                <th align="left" class="number text-muted">Номер</th>
                                <th align="left" class="name text-muted">Название</th>
                                <th class="count text-muted text-center">Кол-во, шт.</th>' .
                                ((!$remove_price)?'<th class="price text-muted text-center">Цена, с НДС</th>':'') .
                                '<th class="volume text-muted text-center">Объём, м<sup>3</sup></th>' . 
                                ((!$remove_price)?'<th class="amount text-muted text-center">Сумма, с НДС</th>':'') .
                            '</tr>
                        </thead>';
            $content .= '<tbody>';

            foreach ( $data['board'] as $key => $value ) {
                $amount['group_volume'] = 0;
                $amount['group_amount'] = 0;

                if (!!$key) {
                    foreach ($guide_data as &$guide) { if (+$guide['id'] === +$key) { $text = $guide['value']; }}
                    $content .= '<tr class="okpd"><td colspan="' . ((!$remove_price)?7:5) . '" class="text-center">ОКПД ' . $text . '</td></tr>';
                }
                // Сортировка
                usort($value, function($a, $b) { return $a['name_search'] <=> $b['name_search']; });
                foreach ($value as $index => $val) {
                    $row_number = $index + 1;
                    $item_volume = self::calculate_volume($val['height'], $val['width'], $val['length'], $val['count']);
                    $item_amount = round(($item_volume * +$val['price']), 2);
                    $amount['group_volume'] += $item_volume;
                    $amount['group_amount'] += $item_amount;

                    $content .= '<tr>
                        <td class="row-number text-muted text-center">' . $row_number . '</td> 
                        <td class="number">' . $val['number'] . '</td>
                        <td class="name">' . $val['name_search'] . '</td>
                        <td class="count text-center">' . $val['count'] . '</td>' .
                        ((!$remove_price)?'<td class="price text-center">' . number_format($val['price'], 2, '.',' ') . ' Р</td>':'') .
                        '<td class="volume text-center">' . $item_volume . '</td>' .
                        ((!$remove_price)?'<td class="amount text-center">' . number_format($item_amount, 2, '.',' ') . ' Р</td>':'') .
                    '</tr>';
                }

                $amount['all_volume'] += $amount['group_volume'];
                $amount['all_amount'] += $amount['group_amount'];
            }

            if (!empty($data['delivery'])) {
                $delivery = $data['delivery'];
                $row_number += 1;
                $amount['all_amount'] += $delivery['amount'];

                $content .= '<tr>
                    <td class="row-number text-muted text-center">' . $row_number . '</td> 
                    <td class="number"></td>
                    <td class="name">' . $delivery['name'] . '</td>
                    <td class="count text-center">' . $delivery['count'] . '</td>' .
                    ((!$remove_price)?'<td class="price text-center">' . number_format($delivery['price'], 2, '.',' ') . ' Р</td>':'') .
                    '<td class="volume text-center"></td>' .
                    ((!$remove_price)?'<td class="amount text-center">' . number_format($delivery['amount'], 2, '.',' ') . ' Р</td>':'') .
                '</tr>';
            }

            $content .= '</tbody>';

            $content .= self::generate_table__tfoot($remove_price, $discount, $amount['all_volume'], $amount['all_amount']);
            
            $content .= '</table>';
        }
        
        // Пеллеты
        if (!empty($data['granular'])) {
            $amount = [
                'all_weight' => 0,
                'all_amount' => 0,
            ];

            $content .= '<table id="product-granular" width="100%">';
            $content .= '<thead>
                            <tr>
                                <th class="row-number text-muted text-center">#</th> 
                                <th align="left" class="number text-muted">Номер</th>
                                <th align="left" class="name text-muted">Название</th>
                                <th class="count text-muted text-center">Кол-во</th>' .
                                ((!$remove_price)?'<th class="price text-muted text-center">Цена</th>':'') .
                                '<th class="weight text-muted text-center">Вес, кг</th>' .
                                ((!$remove_price)?'<th class="amount text-muted text-center">Сумма</th>':'') .
                            '</tr>
                        </thead>';
            $content .= '<tbody>';

            foreach ($data['granular'] as $index => $val) {
                $row_number = $index++;
                $item_weight= round((+$val['weight'] * +$val['count']), 3);
                $item_amount = round(((+$item_weight/1000) * +$val['price']), 2);

                $content .= '<tr>
                    <td class="row-number text-muted text-center">' . $index . '</td> 
                    <td class="number">' . $val['number'] . '</td>
                    <td class="name">' . $val['name_search'] . '</td>
                    <td class="count text-center">' . $val['count'] . '</td>' .
                    ((!$remove_price)?'<td class="price text-center">' . number_format($val['price'], 2, '.',' ') . ' Р</td>':'') .
                    '<td class="weight text-center">' . $item_weight . '</td>' .
                    ((!$remove_price)?'<td class="amount text-center">' . number_format($item_amount, 2, '.',' ') . ' Р</td>':'') .
                '</tr>'; 
                
                $amount['all_weight'] += $item_weight;
                $amount['all_amount'] += $item_amount;
            }

            if (!empty($data['delivery'])) {
                $delivery = $data['delivery'];
                $row_number += 2;
                $amount['all_amount'] += $delivery['amount'];

                $content .= '<tr>
                    <td class="row-number text-muted text-center">' . $row_number . '</td> 
                    <td class="number"></td>
                    <td class="name">' . $delivery['name'] . '</td>
                    <td class="count text-center">' . $delivery['count'] . '</td>' .
                    ((!$remove_price)?'<td class="price text-center">' . number_format($delivery['price'], 2, '.',' ') . ' Р</td>':'') .
                    '<td class="volume text-center"></td>' .
                    ((!$remove_price)?'<td class="amount text-center">' . number_format($delivery['amount'], 2, '.',' ') . ' Р</td>':'') .
                '</tr>';
            }

            $content .= '</tbody>';

            $content .= self::generate_table__tfoot($remove_price, $discount, $amount['all_weight'], $amount['all_amount']);

            $content .= '</table>';
        }

        $answer['content'] = $content;
        $answer['total_amount'] += $amount['all_amount'] - $discount;

        return $answer;
    }
    private static function generate_table__tfoot($remove_price, $discount, $all_vw, $all_amount) {
        $tfoot  = '<tfoot>';
        $tfoot .= '<tr>
                        <td colspan="' . ((!$remove_price)?5:4) . '" class="text-end">Итого:</td>
                        <td class="text-center fw-normal">' . $all_vw . '</td>' .
                        ((!$remove_price)?'<td class="text-center fw-normal">' . number_format($all_amount, 2, '.',' ') . ' Р</td>':'') .
                    '</tr>';

        if (!$remove_price && $discount) {
            $all_amount -= $discount;
            $tfoot .= '<tr>
                        <td colspan="5" class="text-end">Сумма скидки:</td> 
                        <td class="text-center"></td>
                        <td class="text-center">' . number_format($discount, 2, '.',' ') . ' Р</td>
                    </tr>';
            $tfoot .= '<tr>
                        <td colspan="5" class="text-end">Итого к оплате:</td> 

                        <td class="text-center"></td>
                        <td class="text-center">' . number_format($all_amount, 2, '.',' ') . ' Р</td>
                    </tr>';
        }

        $tfoot .= '</tfoot>';

        return $tfoot;
    }
    // Сортируем продукцию
    private static function sorting_products($list) {
        $temp = [];
        foreach ($list as $product) {
            if (array_key_exists($product['name'], $temp)) {
                $temp[ $product['name'] ]['volume'] += self::calculate_volume($product['height'], $product['width'], $product['length'], $product['count']);
                $temp[ $product['name'] ]['weight'] += round((+$product['weight'] * +$product['count']), 3);
            } else {
                $item_volume = self::calculate_volume($product['height'], $product['width'], $product['length'], $product['count']);
                $item_weight = round((+$product['weight'] * +$product['count']), 3);

                $temp[ $product['name'] ] = [
                    'name'   => $product['name'],
                    'volume' => $item_volume,
                    'weight' => $item_weight,
                ];
            }
        }
        return $temp;
    }
    



    // ДОПОЛНИТЕЛЬНЫЕ ПОЛЯ //
    // Показать поля администратору
    public static function show_profile_fields_admin( $user ) { 
        if ($user->ID === 1) {
            require_once plugin_dir_path( __DIR__ ) . 'public/module/_adminpanel.php';
        }
    }
    // Показать поля
    public static function show_profile_fields( $user ) { 
        require_once plugin_dir_path( __DIR__ ) . 'public/module/_adminpanel.php';
    }
    // Сохранить поля
    public static function save_profile_fields( $user_id ) {
        file_put_contents('api-log.txt',var_export($_POST,true).PHP_EOL,FILE_APPEND);
        // Вводим новые поля
        update_user_meta( $user_id, 'profile_img', sanitize_text_field( $_POST[ 'profile_img' ] ) );
        update_user_meta( $user_id, 'signature_img', sanitize_text_field( $_POST[ 'signature_img' ] ) );
        update_user_meta( $user_id, 'post', sanitize_text_field( $_POST[ 'post' ] ) );
        update_user_meta( $user_id, 'pages', sanitize_text_field( $_POST[ 'pages' ] ) );
        update_user_meta( $user_id, 'edit', sanitize_text_field( $_POST[ 'edit' ] ) );
        update_user_meta( $user_id, 'view', sanitize_text_field( $_POST[ 'view' ] ) );
        update_user_meta( $user_id, 'view_list', sanitize_text_field( $_POST[ 'view_list' ] ) );
    }
    // Загрузка медиа
    public static function load_media_files() {
        wp_enqueue_media();
    }

}
 

// file_put_contents('api-log.txt',var_export($request,true).PHP_EOL,FILE_APPEND);