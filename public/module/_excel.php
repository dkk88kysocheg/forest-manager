<?php 
/**
 * Вывод Excel файла
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

require ABSPATH . '/wp-content/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


switch ($_GET['action']) {
    case 'report-dispatch':
        return excel__report_dispatch($_GET);
        break;
    case 'report-summary':
        return excel__report_summary($_GET);
        break;
}

// Сгенерировать строку таблицы
function generate_string($row, $array) {
	$response = [];
	// Английский алфавит
	$en = array(
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 
		'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
	);

	foreach ($array AS $index => $value) {
		$letter = $en[ $index ] . $row;
		$response[ $letter ] = $value;
	}

	return $response;
}

// Получить значение - Расчет
function get_value_cash($i) {
    switch ((int)$i) {
        case 0: return 'Безналичный';
        case 1: return 'Касса';
        case 2: return 'R2';
    }
}

// Посчитать объём
function calculate_volume($height, $width, $length, $count) {
    $volume_piece = (+$height/1000) * (+$width/1000) * (+$length/1000);
    // Если нет количество отправляем объёт 1 продукции
    if (!$count) { return $volume_piece; }
    $item_volume = round(($volume_piece * +$count), 3);
    return $item_volume; 
}

// Сохраните xlsx-файл
function save_file($spreadsheet) {
	$file_name = 'export' . strtotime( "now" ) . '.xlsx';
	$dir_path  = 'wp-content/plugins/forest-manager/export/' . $file_name;

	$writer_xlsx = new Xlsx($spreadsheet);
	$writer_xlsx->save( $dir_path );

	delete_old_file();

	wp_redirect( get_site_url( null, '', 'http' ) . '/' . $dir_path );
}

function get_data__specification_full($data) {
    global $wpdb;
    $request = 'SELECT specification.*, clients.name AS client_name, clients.organization_form AS client_organization_form, users.display_name AS user_name 
			    FROM forest_specification AS specification, forest_clients AS clients, wp_users AS users 
			    WHERE specification.deleted = 0 AND specification.clients_id = clients.id AND specification.user_id = users.id '; 

    // ПЕРИОД //
    $date_from = strtotime( date('Y-m-d 00:00:00', strtotime( $data['date_from'] )) );
    $date_to   = strtotime( date('Y-m-d 23:59:59', strtotime( $data['date_to'] )) );

    $request .= 'AND specification.date_dispatch >= ' . $date_from . ' AND specification.date_dispatch <= ' . $date_to . ' ';

    // НАЛИЧНЫЕ //
    if (isset($data['cash'])) {
        $request .= 'AND specification.cash = ' . +$data['cash'] . ' '; 
    }

    $request .= 'GROUP BY specification.id '; 
    $request .= 'ORDER BY specification.date_dispatch ASC';

    return $wpdb->get_results( "$request", ARRAY_A );
}

// Получить группу продукции
function get_gruop_production($name) {
    switch ($name) {
	    case 'Брусок':
	    case 'Брусок, сухой, строганный':
	    case 'Вагонка «Штиль»':
	    case 'Евровагонка':
	    case 'Евровагонка (96)':
	    case 'Евровагонка (88)':
	    case 'Блок-хаус':
	    case 'Доска пола':
	    case 'Имитация бруса':
	    case 'Наличник':
	    case 'Планкен, косой':
	    case 'Планкен, прямой':
	    case 'Стеновые панели':
	    case 'Доска, сухая, сращенная':
	    case 'Доска, сухая, строганная':
	    case 'Доска, сухая, сращенная, строганная':
	    case 'Доска, сухая, сращенная, шип-паз':
	    case 'Брус сухой, строганный, клеенный':
	    case 'Брус сухой, строганный, клееный, сращенный':
	    case 'Полок банный':
	    case 'Пиломатериал, сращенный':
	    case 'Пиломатериал, сращенный, строганный':
	    case 'Пиломатериал, строганный':
	    case 'Палубная доска':
	    case 'Террасная доска':
	    case 'Вагонка «Софтлайн»':
	        $response = 'mouldings';
	        break;
	    case 'Брус':
	    case 'Доска':
	    case 'Доска, сухая':
	    case 'Прокладки':
	    case 'Пиломатериал':
	    case 'Пиломатериал, сухой':
	        $response = 'board';
	        break;
	    case 'Пеллеты, белые':
	    case 'Пеллеты, серые':
	        $response = 'pellet';
	        break;
	    case 'Щепа':
	        $response = 'woodchips';
	        break;
	    case 'Поддоны':
	        $response = 'pallet';
	        break;
	    case 'Отходы дровяные':
	    case 'Отходы дровяные, сухие':
	        $response = 'waste';
	        break;
	    default:
	 		$response = 'not_group';
	 		break;
	}


	return $response;
}


// =============== ОТЧЁТЫ ============= //
// Отчет по отгрузкам
function excel__report_dispatch($data) {
	global $wpdb;

    $list = get_data__specification_full( $data );

    $row = 1;
    $title = generate_string($row++, [
		[
			'value'   => 'Клиент',
			'type'   => 'string',
			'weight' => 'bold'
		],
		[
			'value'   => 'Расчёт',
			'type'   => 'string',
			'weight' => 'bold'
		],
		[
			'value'   => 'Менеджер',
			'type'   => 'string',
			'weight' => 'bold'
		],
		[
			'value'   => 'Дата отгрузки',
			'type'   => 'string',
			'weight' => 'bold'
		],
		[
			'value'   => '№ спецификации',
			'type'   => 'string',
			'weight' => 'bold'
		],
		[
			'value'   => 'Название',
			'type'   => 'string',
			'weight' => 'bold'
		],
		[
			'value'   => 'Количество',
			'type'   => 'string',
			'weight' => 'bold', 
			'align' => 'center'
		],
		[
			'value'   => 'Цена',
			'type'   => 'string',
			'weight' => 'bold', 
			'align' => 'center'
		],
		[
			'value'   => 'Объём',
			'type'   => 'string',
			'weight' => 'bold', 
			'align' => 'center'
		],
		[
			'value'   => 'Вес',
			'type'   => 'string',
			'weight' => 'bold', 
			'align' => 'center'
		],
		[
			'value'   => 'Сумма',
			'type'   => 'string',
			'weight' => 'bold', 
			'align' => 'center'
		],
		[
			'value'   => 'Скидка',
			'type'   => 'string',
			'weight' => 'bold',
			'color'  => 'F2DCDB', 
			'align' => 'center'
		],
		[
			'value'   => 'Доставка',
			'type'   => 'string',
			'weight' => 'bold',
			'color'  => 'DDEBF7', 
			'align' => 'center'
		],
		[
			'value'   => 'R2',
			'type'   => 'string',
			'weight' => 'bold',
			'color'  => 'E2EFDA', 
			'align' => 'center'
		],
		[
			'value'   => 'Итого к оплате (Общая сумма)',
			'type'   => 'string',
			'weight' => 'bold', 
			'align' => 'center'
		]
	]);
	$array_tabs = [
		'all'      => [],
		'cashless' => [],
		'cash'     => [],
		'cashcash' => []
	];

	$product_summaries   = [];

	$data_array_all      = [];
	$data_array_cashless = [];
	$data_array_cash     = [];
	$data_array_cashcash = [];

	$data_array_all[]      = $title;
	$data_array_cashless[] = $title;
	$data_array_cash[]     = $title;
	$data_array_cashcash[] = $title;

	$total_numbe_dispatch_all      = 0;
	$total_numbe_dispatch_cashless = 0;
	$total_numbe_dispatch_cash     = 0;
	$total_numbe_dispatch_cashcash = 0;

    foreach ($list as $item) {
		$border = false;
		$fillability = [ 'total' => 0 ];
		$total = 0;

        // Добавляем данные о клиенте
        $data_client = $wpdb->get_row( "SELECT * FROM `forest_clients` WHERE id = {$item['clients_id']}", ARRAY_A );


        $list_product_specification = $wpdb->get_results( "SELECT conn.*, product.id AS product_id, 
																spec.number as specification_number, 
																product.name, 
																product.name_search, 
																product.height, 
																product.width, 
																product.length, 
																product.granular, 
																product.weight 
														FROM forest_product_specification AS conn, 
															forest_specification AS spec, 
															forest_product AS product 
														WHERE conn.product_id = product.id AND 
															conn.specification_id = spec.id AND 
															conn.specification_id = {$item['id']}
														ORDER BY conn.id ASC", ARRAY_A );

        $str_cash   = get_value_cash($item['cash']);
        $color_cash = ((int)$item['cash'] === 2)?'E2EFDA':'DDEBF7';

        $date_dispatch = gmdate("d.m.Y", $item['date_dispatch']);

        // Сортируем продукцию
        foreach ($list_product_specification as $product) {
        	$price = number_format($product['price'], 2, '.','');
        	$volume = 0;
        	$weight = 0;
        	$amount = 0;

            if (!(int)$product['granular']) {
                $volume = calculate_volume($product['height'], $product['width'], $product['length'], $product['count']);
                $amount = (float)$volume * (float)$price;
				// Форматирование
                $temp_value = $volume = number_format($volume, 3, '.','');
            } else {
                $weight = round(((float)$product['weight'] * (int)$product['count']), 3);
                $amount = round((((float)$weight/1000) * (float)$price), 2);
				// Форматирование
                $temp_value = $weight = number_format($weight, 2, '.','');
            }

			$temp_fillability = (isset($fillability[ $product['name'] ]))?$fillability[ $product['name'] ]:0;
			$fillability[ $product['name'] ] = (float)$temp_value + $temp_fillability;
			$fillability[ 'total' ] += (float)$temp_value;

            $total += (float)$amount;

            $amount = number_format($amount, 2, '.','');

            $temp_arr = [
				[ 'value' => $data_client['name'],             'type' => 'string'      ],  // 0 client   
				[ 'value' => $str_cash,                        'type' => 'string', 'color' => $color_cash ],  // 1 cash
				[ 'value' => $item['user_name'],               'type' => 'string'      ],  // 2 manager
				[ 'value' => $date_dispatch,                   'type' => 'date', 'align' => 'left'],  // 3 date
				[ 'value' => $product['specification_number'], 'type' => 'string'      ],  // 4 specification
				[ 'value' => $product['name_search'],          'type' => 'string'      ],  // 5 name
				[ 'value' => $product['count'],                'type' => 'numeric',     'align' => 'center' ],  // 6 count
				[ 'value' => $price,                           'type' => 'money',       'align' => 'center' ],  // 7 price
				[ 'value' => $volume,                          'type' => 'numeric_000', 'align' => 'center' ],  // 8 volume
				[ 'value' => $weight,                          'type' => 'numeric_00',  'align' => 'center' ],  // 9 weight
				[ 'value' => $amount,                          'type' => 'money',       'align' => 'center' ],  // 10 amount
            ];

            $array_tabs['all'][] = $temp_arr;
            if ((int)$item['cash'] === 0) $array_tabs['cashless'][] = $temp_arr;
            if ((int)$item['cash'] === 1) $array_tabs['cash'][]     = $temp_arr;
            if ((int)$item['cash'] === 2) $array_tabs['cashcash'][] = $temp_arr;


            // Данные по продукции
            $product_weight = (isset($product_summaries[ $product['name'] ]['weight']))?$product_summaries[ $product['name'] ]['weight']:0;
            $product_summaries[ $product['name'] ]['weight'] = (float)$weight + $product_weight;
            $product_volume = (isset($product_summaries[ $product['name'] ]['volume']))?$product_summaries[ $product['name'] ]['volume']:0;
            $product_summaries[ $product['name'] ]['volume'] = (float)$volume + $product_volume;
        }

        // Наполняемость
        $fillability_str = 'Заполняемость:';
        foreach ($fillability as $k => $v) {
        	if ($k === 'total') continue;
        	$percent = ( $v / $fillability['total'] ) * 100;
        	$fillability_str .=  PHP_EOL . $k . ' - - - - ' . round($percent, 2) . '% - - - - ' . $v;

        	if ($k === 'Пеллеты, белые' || $k === 'Пеллеты, серые') {
        		$fillability_str .= ' кг';
        	} else {
        		$fillability_str .= ' м3';
        	}
        }

        // Скидка и доставка
        $delivery   = (int)$item['count_delivery'] * (float)$item['price_delivery'];
        $discount   = ($item['discount'])?number_format($item['discount'], 2, '.',''):0;
        $additional = ($item['additional'])?number_format($item['additional'], 2, '.',''):0;

        $total += $delivery - (float)$discount + (float)$additional;

        $temp_arr = [
			[ 'value' => $data_client['name'],      'type' => 'string',  'border' => 'bottom' ],  // 0 client   
			[ 'value' => $str_cash,                 'type' => 'string',  'border' => 'bottom', 'color' => $color_cash ],  // 1 cash
			[ 'value' => $item['user_name'],        'type' => 'string',  'border' => 'bottom' ],  // 2 manager
			[ 'value' => $date_dispatch,            'type' => 'date',    'border' => 'bottom', 'align' => 'left' ],  // 3 date
			[ 'value' => $item['number'],           'type' => 'string',  'border' => 'bottom' ],  // 4 specification
			[ 'value' => 'Скидка, доставка и итог', 'type' => 'string',  'border' => 'bottom' ],  // 5 name
			[ 'value' => '', 'border' => 'bottom' ],  // 6 count
			[ 'value' => '', 'border' => 'bottom' ],  // 7 price
			[ 'value' => '', 'border' => 'bottom' ],  // 8 volume
			[ 'value' => '', 'border' => 'bottom' ],  // 9 weight
			[ 'value' => '', 'border' => 'bottom' ],  // 10 amount
			[ 'value' => $discount,                 'type' => 'money',  'border' => 'bottom',  'color' => 'F2DCDB', 'align' => 'center' ],  // 11 discount
			[ 'value' => $delivery,                 'type' => 'money',  'border' => 'bottom',  'color' => 'DDEBF7', 'align' => 'center' ],  // 12 delivery
			[ 'value' => $additional,               'type' => 'money',  'border' => 'bottom',  'color' => 'E2EFDA', 'align' => 'center' ],  // 13 additional
			[ 'value' => $total,                    'type' => 'money',  'border' => 'bottom',  'color' => 'FFFF00', 'align' => 'center', 'weight' => 'bold', 'size' => 14  ],  // 14 total
			[ 'value' => $fillability_str,          'type' => 'string_many', 'border' => 'bottom', 'weight' => 'bold'],  // 15 fillability
        ];

        $array_tabs['all'][] = $temp_arr;
        $total_numbe_dispatch_all++;

        if ((int)$item['cash'] === 0) {
        	$array_tabs['cashless'][] = $temp_arr;
        	$total_numbe_dispatch_cashless++;
        }
        if ((int)$item['cash'] === 1) {
        	$array_tabs['cash'][] = $temp_arr;
        	$total_numbe_dispatch_cash++;
        }
        if ((int)$item['cash'] === 2) {
        	$array_tabs['cashcash'][] = $temp_arr;
        	$total_numbe_dispatch_cashcash++;
        }
    }

    // Подготавливаем данные для экспорта
    foreach ($array_tabs as $key => $tabs) {
    	$row_tabs = $row;

    	foreach ($tabs as $item) {
    		switch ($key) {
    			case 'all':
    				$data_array_all[]      = generate_string($row_tabs++, $item);
    				break;
    			case 'cashless':
    				$data_array_cashless[] = generate_string($row_tabs++, $item);
    				break;
    			case 'cash':
    				$data_array_cash[]     = generate_string($row_tabs++, $item);
    				break;
    			case 'cashcash':
    				$data_array_cashcash[] = generate_string($row_tabs++, $item);
    				break;
    		}
    	}
    }

    // ==================================================== //

	// Создайте новый объект электронной таблицы
	$spreadsheet = new Spreadsheet();

	// Создаем листы
	for ($n = 0; $n <= 3; $n++) {
		$row_sheet = 1;

		switch ($n) {
			case 0:
				$data_array = $data_array_all;
				$total_numbe_dispatch = $total_numbe_dispatch_all;
				$sheet = $spreadsheet->getActiveSheet();
				$sheet->setTitle('Общие данные');
				break;
			case 1:
				$data_array = $data_array_cashless;
				$total_numbe_dispatch = $total_numbe_dispatch_cashless;
				$spreadsheet->createSheet();
				$sheet = $spreadsheet->setActiveSheetIndex(1);
				$spreadsheet->getActiveSheet()->setTitle('Безнал');
				break;
			case 2:
				$data_array = $data_array_cash;
				$total_numbe_dispatch = $total_numbe_dispatch_cash;
				$spreadsheet->createSheet();
				$sheet = $spreadsheet->setActiveSheetIndex(2);
				$spreadsheet->getActiveSheet()->setTitle('Касса');
				break;
			case 3:
				$data_array = $data_array_cashcash;
				$total_numbe_dispatch = $total_numbe_dispatch_cashcash;
				$spreadsheet->createSheet();
				$sheet = $spreadsheet->setActiveSheetIndex(3);
				$spreadsheet->getActiveSheet()->setTitle('R2');
				break;
		}

		// Извлеките текущий активный рабочий лист


	    foreach($data_array as $value) {
			foreach($value as $cell => $arr) {
				// заполняем ячейки листа значениями
				$sheet->setCellValue($cell, $arr['value'] );

				if (isset($arr['type'])) {
					switch ($arr['type']) {
						case 'string_many': 
							$sheet->getStyle( $cell )->getAlignment()->setWrapText(true);
							break;
						case 'numeric': 
							$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
							break;
						case 'numeric_00': 
							$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
							break;
						case 'numeric_000': 
							$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_000);
							break;
						case 'money': 
							$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_RUB);
							break;
						case 'date': 
							$dateValue = Date::PHPToExcel(new DateTime( $arr['value'] ));
							$sheet->setCellValue($cell, $dateValue);
							// Устанавливаем формат даты
							$sheet->getStyle($cell)->getNumberFormat()->setFormatCode('DD.MM.YYYY');
							break;
					}
				}

				if (isset($arr['weight'])) {
					$sheet->getStyle( $cell )->getFont()->setBold(true);
				}

				if (isset($arr['border']) && $arr['border'] === 'bottom') {
					$sheet->getStyle( $cell )->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM); // Нижняя граница
				}

				if (isset($arr['color'])) {
					$sheet->getStyle( $cell )->getFill()->setFillType(Fill::FILL_SOLID);
					$sheet->getStyle( $cell )->getFill()->getStartColor()->setARGB( $arr['color'] );
				}

				if (isset($arr['size'])) {
					$sheet->getStyle( $cell )->getFont()->setSize( $arr['size'] );
				}

				if (isset($arr['align'])) {
					switch ($arr['align']) {
						case 'left':   $sheet->getStyle( $cell )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); break;
						case 'center': $sheet->getStyle( $cell )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); break;
						case 'right':  $sheet->getStyle( $cell )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); break;
					}
				}
			}
			$row_sheet++;
	    }

	    // Установка ширины столбцов
		foreach (['A', 'B', 'C', 'D', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'] as $column) {
			$sheet->getColumnDimension($column)->setAutoSize(true);
		}
		// $sheet->setAutoFilter('A1:M1');

		// Автосумма
		$row_total = $row_sheet - 1;
		foreach (['G', 'I', 'J'] as $letter) {
			$sheet->setCellValue($letter . $row_sheet, '=SUM(' . $letter . '1:' . $letter . $row_total . ')');
			$sheet->getStyle($letter . $row_sheet)->getFont()->setBold(true);
			$sheet->getStyle($letter . $row_sheet)->getFont()->setSize(14);
			$sheet->getStyle($letter . $row_sheet)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		}
		foreach (['K', 'L', 'M', 'N'] as $letter) {
			$sheet->setCellValue($letter . $row_sheet, '=SUM(' . $letter . '1:' . $letter . $row_total . ')');
			$sheet->getStyle($letter . $row_sheet)->getFont()->setBold(true);
			$sheet->getStyle($letter . $row_sheet)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_RUB);
			$sheet->getStyle($letter . $row_sheet)->getFont()->setSize(14);
			$sheet->getStyle($letter . $row_sheet)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		}
		foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'] as $letter) {
			$sheet->getStyle($letter . $row_sheet)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM); // Нижняя граница
		}

		// Итого
		$sheet->setCellValue('O'. $row_sheet, '=K'.$row_sheet.'+M'.$row_sheet.'-L'.$row_sheet.'+N'.$row_sheet);
		$sheet->getStyle( 'O'. $row_sheet )->getFont()->setBold(true);
		$sheet->getStyle( 'O'. $row_sheet )->getFill()->setFillType(Fill::FILL_SOLID);
		$sheet->getStyle( 'O'. $row_sheet )->getFill()->getStartColor()->setARGB( 'ffC100' );
		$sheet->getStyle( 'O'. $row_sheet )->getFont()->setSize(16);
		$sheet->getStyle( 'O'. $row_sheet )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_RUB);
		$sheet->getStyle( 'O'. $row_sheet )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

		// Фиксируем первую строку
		$sheet->freezePane('A2');

		// Общее количество отгрузок
		$row_sheet = $row_sheet + 2;
		$sheet->setCellValue('L' . $row_sheet, 'Общее количество отгрузок:');
		$sheet->getStyle('L' . $row_sheet)->getFont()->setBold(true);
		$sheet->getStyle('L' . $row_sheet)->getFont()->setSize(14);
		$sheet->getStyle('L' . $row_sheet)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
		
		$sheet->mergeCells('L' . $row_sheet .':N' . $row_sheet);

		$sheet->setCellValue('O' . $row_sheet, $total_numbe_dispatch);
		$sheet->getStyle('O' . $row_sheet)->getFont()->setBold(true);
		$sheet->getStyle('O' . $row_sheet)->getFont()->setSize(14);
		$sheet->getStyle('O' . $row_sheet)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

		$sheet->getStyle('L' . $row_sheet)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM); // Левая граница
		$sheet->getStyle('O' . $row_sheet)->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM); // Правая  граница
		$sheet->getStyle('L' . $row_sheet .':O' . $row_sheet)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM); // Верхняя граница
		$sheet->getStyle('L' . $row_sheet .':O' . $row_sheet)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM); // Нижняя граница
	}

	// ==================================================== //

	// Сводная по продукции
	$spreadsheet->createSheet();
	$sheet = $spreadsheet->setActiveSheetIndex(4);
	$spreadsheet->getActiveSheet()->setTitle('Сводная по продукции');

	$row_product = 1;
	$product_summaries_array = [];
	$product_summaries_array[] = generate_string($row_product++, [
		[
			'value'   => 'Название',
			'type'   => 'string',
			'weight' => 'bold'
		],
		[
			'value'   => 'Объём',
			'type'   => 'string',
			'weight' => 'bold'
		],
		[
			'value'   => 'Вес',
			'type'   => 'string',
			'weight' => 'bold'
		]
	]);

	$temp = [
        'mouldings' => [
            'name'    => 'Погонажные изделия',
            'volume'  => 0,
            'weight'  => 0
        ],
        'board' => [
            'name'    => 'П/М',
            'volume'  => 0,
            'weight'  => 0
        ],
        'pellet' => [
            'name'    => 'Пеллеты',
            'weight'  => 0,
            'weight'  => 0
        ],
        'woodchips' => [
            'name'    => 'Щепа',
            'volume'  => 0,
            'weight'  => 0
        ],
        'pallet' => [
            'name'    => 'Поддоны',
            'volume'  => 0,
            'weight'  => 0
        ],
        'waste' => [
            'name'    => 'Отходы дровяные',
            'volume'  => 0,
            'weight'  => 0
        ],
    ];

    // Добавляем продукцию
	foreach ($product_summaries as $product_name => $product_value) {
		$product_summaries_array[] = generate_string($row_product++, [
			[
				'value' => $product_name,
				'type'  => 'string'
			],
			[
				'value' => $product_value['volume'],
				'type'  => 'numeric_000'
			],
			[
				'value' => $product_value['weight'],
				'type'  => 'numeric_00'
			]
		]);


		$name = get_gruop_production($product_name);
		$temp[ $name ]['volume'] += $product_value['volume'];
		$temp[ $name ]['weight'] += $product_value['weight'];

		// if ($name === 'not_group') {
		// 	file_put_contents('a-excel.txt',var_export($product_name,true).PHP_EOL,FILE_APPEND);
		// 	file_put_contents('a-excel.txt',var_export($product_value,true).PHP_EOL,FILE_APPEND);
		// }
	}

	$row_product += 2;

	// Добавляем группы
	foreach ($temp as $group) {
		$product_summaries_array[] = generate_string($row_product++, [
			[
				'value' => $group['name'],
				'type'  => 'string'
			],
			[
				'value' => $group['volume'],
				'type'  => 'numeric_000'
			],
			[
				'value' => $group['weight'],
				'type'  => 'numeric_00'
			]
		]);
	}

	// Записываем в таблицу
	foreach($product_summaries_array as $value) {
		foreach($value as $cell => $arr) {
			// заполняем ячейки листа значениями
			$sheet->setCellValue($cell, $arr['value'] );

			if (isset($arr['type'])) {
				switch ($arr['type']) {
					case 'numeric_00': 
						$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
						break;
					case 'numeric_000': 
						$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_000);
						break;
				}
			}

			if (isset($arr['weight'])) {
				$sheet->getStyle( $cell )->getFont()->setBold(true);
			}
		}
    }

    // Установка ширины столбцов
	foreach (['A', 'B', 'C'] as $column) {
		$sheet->getColumnDimension($column)->setAutoSize(true);
	}

	// Выбираем первую вкладку
	$spreadsheet->setActiveSheetIndex(0);

	// Сохраните xlsx-файл
	save_file($spreadsheet);
}

// Общий отчет
function excel__report_summary($data) {
	global $wpdb;

    // ПЕРИОД //
    $list = get_data__specification_full( $data );

    $date_from = date("d.m.Y", strtotime($data['date_from']));
    $date_to   = date("d.m.Y", strtotime($data['date_to']));

    $row = 1;
    $title = [
		[
			'value' => 'Отчет за период с ' . $date_from . ' по ' . $date_to,
			'type' => 'string',
			'weight' => 'bold',
			'size' => 16
		],
	];
    $title2 = [
		[
			'value'  => 'Название',
			'type'   => 'string',
			'weight' => 'bold',
			'border' => 'all',
			'size'   => 12
		],
		[
			'value'  => 'Объём, м3',
			'type'   => 'string',
			'weight' => 'bold',
			'align'  => 'center',
			'border' => 'all',
			'size'   => 12
		],
		[
			'value'  => 'Вес, кг',
			'type'   => 'string',
			'weight' => 'bold',
			'align'  => 'center',
			'border' => 'all',
			'size'   => 12
		],
		[
			'value'  => 'Безнал',
			'type'   => 'string',
			'weight' => 'bold',
			'border' => 'all',
			'align'  => 'center',
			'color'  => 'DDEBF7',
			'size'   => 12
		],
		[
			'value'  => 'В кассу',
			'type'   => 'string',
			'weight' => 'bold',
			'border' => 'all',
			'align'  => 'center',
			'color'  => 'DDEBF7',
			'size'   => 12
		],
		[
			'value'  => 'R2',
			'type'   => 'string',
			'weight' => 'bold',
			'border' => 'all',
			'align'  => 'center',
			'color'  => 'E2EFDA',
			'size'   => 12
		]
	];
	$data_array = [];
	$data_array[] = generate_string($row++, $title);
	$data_array[] = generate_string($row++, $title2);


	// Сортировка //
    $group_array = [
        'mouldings' => [
            'name'    => 'Погонажные изделия',
            'volume'  => 0,
            'weight'  => 0,
            'payment' => [ 
            	'cashless' => 0,
            	'cash'     => 0,
            	'cashcash' => 0
            ]
        ],
        'board' => [
            'name'    => 'П/М',
            'volume'  => 0,
            'weight'  => 0,
            'payment' => [ 
            	'cashless' => 0,
            	'cash'     => 0,
            	'cashcash' => 0
            ]
        ],
        'pellet' => [
            'name'    => 'Пеллеты',
            'volume'  => 0,
            'weight'  => 0,
            'payment' => [ 
            	'cashless' => 0,
            	'cash'     => 0,
            	'cashcash' => 0
            ]
        ],
        'woodchips' => [
            'name'    => 'Щепа',
            'volume'  => 0,
            'weight'  => 0,
            'payment' => [ 
            	'cashless' => 0,
            	'cash'     => 0,
            	'cashcash' => 0
            ]
        ],
        'pallet' => [
            'name'    => 'Поддоны',
            'volume'  => 0,
            'weight'  => 0,
            'payment' => [ 
            	'cashless' => 0,
            	'cash'     => 0,
            	'cashcash' => 0
            ]
        ],
        'waste' => [
            'name'    => 'Отходы дровяные',
            'volume'  => 0,
            'weight'  => 0,
            'payment' => [ 
            	'cashless' => 0,
            	'cash'     => 0,
            	'cashcash' => 0
            ]
        ],
    ];

    $additional_array = [
		'discount' => [ 
			'value'    => 'Скидка:',
			'value2'   => '',
			'value3'   => '',
			'cashless' => 0,
			'cash'     => 0,
			'cashcash' => 0
		],
    	'additional' => [ 
			'value'    => 'R2:',
			'value2'   => '',
			'value3'   => '',
			'cashless' => 0,
			'cash'     => 0,
			'cashcash' => 0
		],
    	'delivery' => [ 
			'value'    => 'Доставка:',
			'value2'   => '',
			'value3'   => '',
			'cashless' => 0,
			'cash'     => 0,
			'cashcash' => 0
		]
    ];

    foreach ($list as $item) {
        $list_product_specification = $wpdb->get_results( "SELECT conn.*, 
        														product.id AS product_id, 
																spec.number as specification_number, 
																product.name, 
																product.name_search, 
																product.height, 
																product.width, 
																product.length, 
																product.granular, 
																product.weight 
														FROM forest_product_specification AS conn, 
															forest_specification AS spec, 
															forest_product AS product 
														WHERE conn.product_id = product.id AND 
															conn.specification_id = spec.id AND 
															conn.specification_id = {$item['id']}
														ORDER BY conn.id ASC", ARRAY_A );


	    // Сортируем продукцию
	    foreach ($list_product_specification as $product) {
	    	$name   = get_gruop_production($product['name']);
	    	$price  = number_format($product['price'], 2, '.','');
	    	$volume = 0;
	    	$weight = 0;
	    	$amount = 0;

	        if (!(int)$product['granular']) {
	            $volume = calculate_volume($product['height'], $product['width'], $product['length'], $product['count']);
	            $amount = (float)$volume * (float)$price;
				// Форматирование
	            $volume = number_format($volume, 3, '.','');
	        } else {
	            $weight = round(((float)$product['weight'] * (int)$product['count']), 3);
	            $amount = round((((float)$weight/1000) * (float)$price), 2);
				// Форматирование
	            $weight = number_format($weight, 2, '.','');
	        }

	        $amount = number_format($amount, 2, '.','');

			if ((int)$item['cash'] === 0) $group_array[$name]['payment']['cashless'] += (float)$amount;
			if ((int)$item['cash'] === 1) $group_array[$name]['payment']['cash']     += (float)$amount;
			if ((int)$item['cash'] === 2) $group_array[$name]['payment']['cashcash'] += (float)$amount;

	        // Данные по продукции
	        $group_array[$name]['weight'] += (float)$weight;
	        $group_array[$name]['volume'] += (float)$volume;
	    }


		if ((int)$item['cash'] === 0) $additional_array['discount']['cashless'] += (float)$item['discount'];
		if ((int)$item['cash'] === 1) $additional_array['discount']['cash']     += (float)$item['discount'];
		if ((int)$item['cash'] === 2) $additional_array['discount']['cashcash'] += (float)$item['discount'];

		if ($item['additional']) {
			$additional_array['additional']['cashcash'] += (float)$item['additional'];
		}

		if ($item['count_delivery']) {
			$delivery = ((int)$item['count_delivery'] * (float)$item['price_delivery']);

			if ((int)$item['cash'] === 0) $additional_array['delivery']['cashless'] += $delivery;
			if ((int)$item['cash'] === 1) $additional_array['delivery']['cash']     += $delivery;
			if ((int)$item['cash'] === 2) $additional_array['delivery']['cashcash'] += $delivery;
		}
    }

    // Добавляем группы
    foreach ($group_array as $group) {
		$data_array[] = generate_string($row++, [
			[
				'value'  => $group['name'],
				'type'   => 'string',
				'align'  => 'left',
				'border' => 'all',
				'size'   => 12
			],
			[
				'value'  => $group['volume'],
				'type'   => 'numeric_000',
				'align'  => 'center',
				'border' => 'all',
				'size'   => 12
			],
			[
				'value'  => $group['weight'],
				'type'   => 'numeric_00',
				'align'  => 'center',
				'border' => 'all',
				'size'   => 12
			],
			[
				'value'  => $group['payment']['cashless'],
				'type'   => 'money',
				'align'  => 'center',
				'border' => 'all',
				'color'  => 'DDEBF7',
				'size'   => 12
			],
			[
				'value'  => $group['payment']['cash'],
				'type'   => 'money',
				'align'  => 'center',
				'border' => 'all',
				'color'  => 'DDEBF7',
				'size'   => 12
			],
			[
				'value'  => $group['payment']['cashcash'],
				'type'   => 'money',
				'align'  => 'center',
				'border' => 'all',
				'color'  => 'E2EFDA',
				'size'   => 12
			]
    	]);
    }

    // Автосумма
    $data_array[] = generate_string($row++, [
		[
			'value'   => ''
		],
		[
			'value'   => '=SUM(B3:B8)',
			'type'   => 'numeric_000',
			'border' => 'all',
			'weight' => true,
			'align'  => 'center',
			'size'   => 14
		],
		[
			'value'   => '=SUM(C3:C8)',
			'type'   => 'numeric_00',
			'border' => 'all',
			'weight' => true,
			'align'  => 'center',
			'size'   => 14
		],
		[
			'value'   => '=SUM(D3:D8)',
			'type'   => 'money',
			'border' => 'all',
			'color'  => 'DDEBF7',
			'weight' => true,
			'align'  => 'center',
			'size'   => 14
		],
		[
			'value'   => '=SUM(E3:E8)',
			'type'   => 'money',
			'border' => 'all',
			'color'  => 'DDEBF7',
			'weight' => true,
			'align'  => 'center',
			'size'   => 14
		],
		[
			'value'   => '=SUM(F3:F8)',
			'type'   => 'money',
			'border' => 'all',
			'color'  => 'E2EFDA',
			'weight' => true,
			'align'  => 'center',
			'size'   => 14
		],
		[
			'value'   => '=D9+E9+F9',
			'type'   => 'money',
			'border' => 'all',
			'align'  => 'center',
			'color'  => 'FCD5B4'
		]
	]);

    // Добавляем Доп данные
    $row ++;
	foreach ($additional_array as $key => $value) {
		$arr = [
			[
				'value'   => $value['value'],
				'type'   => 'string',
				'align'  => 'right'
			],
			[
				'value'   => '',
				'type'   => 'string',
			],
			[
				'value'   => '',
				'type'   => 'string',
			],
			[
				'value'   => $value['cashless'],
				'type'   => 'money',
				'border' => 'all',
				'color'  => 'DDEBF7',
				'align'  => 'center',
				'weight' => true,
				'size'   => 12
			],
			[
				'value'   => $value['cash'],
				'type'   => 'money',
				'border' => 'all',
				'color'  => 'DDEBF7',
				'align'  => 'center',
				'weight' => true,
				'size'   => 12
			],
			[
				'value'   => $value['cashcash'],
				'type'   => 'money',
				'border' => 'all',
				'color'  => 'E2EFDA',
				'align'  => 'center',
				'weight' => true,
				'size'   => 12
			]
    	];

    	if ($key === 'discount') {
    		$arr[3]['color'] = $arr[4]['color'] = $arr[5]['color'] = 'F2DCDB';
    		$arr[] = [
				'value'   => '=D11+E11+F11',
				'type'   => 'money',
				'border' => 'all',
				'align'  => 'center',
				'color'  => 'FCD5B4'
			];
    	}
		$data_array[] = generate_string($row++, $arr);
	}

	// ИТОГ
    $data_array[] = generate_string($row++, [
		[
			'value'  => 'ИТОГО:',
			'type'   => 'string',
			'weight' => true,
			'align'  => 'right',
			'size'   => 14
		],
		[ 'value' => '' ],
		[ 'value' => '' ],
		[
			'value'   => '=SUM(D3:D8)-D11+D12+D13',
			'type'   => 'money',
			'border' => 'all',
			'align'  => 'center',
			'weight' => true,
			'size'   => 14
		],
		[
			'value'   => '=SUM(E3:E8)-E11+E12+E13',
			'type'   => 'money',
			'border' => 'all',
			'align'  => 'center',
			'weight' => true,
			'size'   => 14
		],
		[
			'value'   => '=SUM(F3:F8)-F11+F12+F13',
			'type'   => 'money',
			'border' => 'all',
			'align'  => 'center',
			'weight' => true,
			'size'   => 14
		]
	]);

    // Считаем итоги
	$row ++;
	$data_array[] = generate_string($row++, [
		[
			'value'  => 'Всего отгрузок:',
			'type'   => 'string',
			'weight' => true,
			'align'  => 'right',
			'border' => 'all',
			'size'   => 14
		],
		[ 'value' => '', 'border' => 'all' ],
		[ 'value' => '', 'border' => 'all' ],
		[
			'value'  => count($list),
			'type'   => 'numeric',
			'weight' => true,
			'align'  => 'center',
			'border' => 'all',
			'size'   => 14
		]
	]);
	$data_array[] = generate_string($row++, [
		[
			'value'  => 'Официально:',
			'type'   => 'string',
			'weight' => true,
			'align'  => 'right',
			'border' => 'all',
			'size'   => 14
		],
		[ 'value' => '', 'border' => 'all' ],
		[ 'value' => '', 'border' => 'all' ],
		[
			'value'  => '=D14+E14',
			'type'   => 'money',
			'weight' => true,
			'align'  => 'center',
			'border' => 'all',
			'size'   => 14
		]
	]);
	$data_array[] = generate_string($row++, [
		[
			'value'  => 'R2:',
			'type'   => 'string',
			'weight' => true,
			'align'  => 'right',
			'border' => 'all',
			'size'   => 14
		],
		[ 'value' => '', 'border' => 'all' ],
		[ 'value' => '', 'border' => 'all' ],
		[
			'value'  => '=F14',
			'type'   => 'money',
			'weight' => true,
			'align'  => 'center',
			'border' => 'all',
			'size'   => 14
		]
	]);
	$data_array[] = generate_string($row++, [
		[
			'value'  => 'Сумма:',
			'type'   => 'string',
			'weight' => true,
			'align'  => 'right',
			'border' => 'all',
			'size'   => 14
		],
		[ 'value' => '', 'border' => 'all' ],
		[ 'value' => '', 'border' => 'all' ],
		[
			'value'  => '=D17+D18',
			'type'   => 'money',
			'weight' => true,
			'align'  => 'center',
			'border' => 'all',
			'size'   => 14
		]
	]);

    // Создайте новый объект электронной таблицы
	$spreadsheet = new Spreadsheet();
	// Извлеките текущий активный рабочий лист
	$sheet = $spreadsheet->getActiveSheet();
	$sheet->setTitle('Сводная таблица');

    foreach($data_array as $value) {
		foreach($value as $cell => $arr) {
			// заполняем ячейки листа значениями
			$sheet->setCellValue($cell, $arr['value'] );

			switch ($arr['type']) {
				case 'numeric': 
					$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
					break;
				case 'numeric_00': 
					$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
					break;
				case 'numeric_000': 
					$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_000);
					break;
				case 'money': 
					$sheet->getStyle( $cell )->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_RUB);
					break;
			}

			if (isset($arr['border'])) {
				$sheet->getStyle( $cell )->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN );
			}

			if (isset($arr['color'])) {
				$sheet->getStyle( $cell )->getFill()->setFillType(Fill::FILL_SOLID);
				$sheet->getStyle( $cell )->getFill()->getStartColor()->setARGB( $arr['color'] );
			}

			if (isset($arr['weight'])) {
				$sheet->getStyle( $cell )->getFont()->setBold(true);
			}

			if (isset($arr['size'])) {
				$sheet->getStyle( $cell )->getFont()->setSize( $arr['size'] );
			}

			if (isset($arr['align'])) {
				switch ($arr['align']) {
					case 'left':   $sheet->getStyle( $cell )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); break;
					case 'center': $sheet->getStyle( $cell )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); break;
					case 'right':  $sheet->getStyle( $cell )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); break;
				}
			}
		}
    }

    // Установка ширины столбцов
	foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G'] as $column) {
		$sheet->getColumnDimension($column)->setAutoSize(true);
	}


	// Объединяем ячейки
	$sheet->mergeCells('A1:G1');
	$sheet->mergeCells('A11:C11');
	$sheet->mergeCells('A12:C12');
	$sheet->mergeCells('A13:C13');
	$sheet->mergeCells('A14:C14');

	$sheet->mergeCells('A16:C16');
	$sheet->mergeCells('A17:C17');
	$sheet->mergeCells('A18:C18');
	$sheet->mergeCells('A19:C19');

	// Жирный границы
	$sheet->getStyle('A1:G1')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
	$sheet->getStyle('A8:G8')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
	$sheet->getStyle('A9:G9')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
	$sheet->getStyle('A10:G10')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
	$sheet->getStyle('A14:G14')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
	$sheet->getStyle('A15:G15')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
	$sheet->getStyle('A19:G19')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

	$sheet->getStyle('G1:G19')->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);

	// Закрашиваем не используемые ячейки
	$sheet->getStyle( 'A10:G10' )->getFill()->setFillType(Fill::FILL_SOLID);
	$sheet->getStyle( 'A10:G10' )->getFill()->getStartColor()->setARGB( 'BFBFBF' );

	$sheet->getStyle( 'A15:G15' )->getFill()->setFillType(Fill::FILL_SOLID);
	$sheet->getStyle( 'A15:G15' )->getFill()->getStartColor()->setARGB( 'BFBFBF' );

	$sheet->getStyle( 'E16:G19' )->getFill()->setFillType(Fill::FILL_SOLID);
	$sheet->getStyle( 'E16:G19' )->getFill()->getStartColor()->setARGB( 'BFBFBF' );

    // Сохраните xlsx-файл
	save_file($spreadsheet);
}

// Удаляем старые файлы
function delete_old_file() {
	// Удаляем старые файлы
	$files     = scandir( WP_PLUGIN_DIR . '/forest-manager/export');
	$threshold = strtotime('-1 day');
	foreach ($files as $file) {
	    if (is_file($file)) {
	        if ($threshold >= filemtime($file)) {
	            unlink($file);
	        }
	    }
	}

	return true;
}



// file_put_contents('a-excel.txt',var_export($files,true).PHP_EOL,FILE_APPEND);