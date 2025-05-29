 <?php 
/**
 * Страница Учёта
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

/**
 * УЖЕ ПОЛУЧНО:
 * class-forest-manager-general-function.php
 * $version
 */


// Подключаем скрипт
wp_enqueue_script( 
    'page-accounting', 
    plugins_url() . '/forest-manager/public/js/page-accounting.js', 
    array(), 
    get_filemtime( 'page-accounting.js' ), 
    true
);

// Отображение данных
global $wpdb;
$accounting = $wpdb->get_row( "SELECT * FROM `forest_accounting` ORDER BY `date` DESC", ARRAY_A );

if (!empty($accounting)) {
	function creating_list($array, $level = 0) {
		$level++;
		$out = '';
		foreach ($array as $i => $row) {
			$uniqid = uniqid();
			$out .= '<li>';
			$out .= '<label class="d-flex w-100" for="' . $uniqid . '"><span class="name">' . $i . '</span><span class="count ms-auto me-3">' . $row['count'] . '</span><span class="volume">' . $row['volume'] . '</span></label>';
			$out .= '<input type="checkbox" class="d-none" id="' . $uniqid . '">';
	 
			if (!empty($row['list'])) {
				$list = $row['list'];
				ksort($list);
				
				$out .= '<ul>';
				$out .= creating_list($list, $level);
				$out .= '</ul>';
			}

			$out .= '</li>';
		}
		return $out;
	}

	$data = unserialize($accounting['data']);
	$arr = [];

	switch ($_COOKIE['accounting-sorting']) {
		case 'shwl':
			foreach ($data as $value) {
				$name   = $value['name'];
				$sort   = $value['sort'];
				$length = +$value['length'];
				$width  = +$value['width'];
				$height = +$value['height'];
				$count  = +$value['count'];
				$volume = +$value['volume'];



				$length_count  = $arr[ $name ]['list'][ $sort ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['count'];
				$length_volume = $arr[ $name ]['list'][ $sort ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['volume'];

				$length_count  = ($length_count)?($length_count + $count):$count;
				$length_volume = ($length_volume)?($length_volume + $volume):$volume;

				$arr[ $name ]['list'][ $sort ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['count']  = round($length_count);
				$arr[ $name ]['list'][ $sort ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['volume'] = round($length_volume, 3);



				$width_count  = $arr[ $name ]['list'][ $sort ]['list'][ $height ]['list'][ $width ]['count'];
				$width_volume = $arr[ $name ]['list'][ $sort ]['list'][ $height ]['list'][ $width ]['volume'];

				$width_count  = ($width_count)?($width_count + $count):$count;
				$width_volume = ($width_volume)?($width_volume + $volume):$volume;

				$arr[ $name ]['list'][ $sort ]['list'][ $height ]['list'][ $width ]['count']  = round($width_count);
				$arr[ $name ]['list'][ $sort ]['list'][ $height ]['list'][ $width ]['volume'] = round($width_volume, 3);



				$height_count  = $arr[ $name ]['list'][ $sort ]['list'][ $height ]['count'];
				$height_volume = $arr[ $name ]['list'][ $sort ]['list'][ $height ]['volume'];

				$height_count  = ($height_count)?($height_count + $count):$count;
				$height_volume = ($height_volume)?($height_volume + $volume):$volume;

				$arr[ $name ]['list'][ $sort ]['list'][ $height ]['count']  = round($height_count);
				$arr[ $name ]['list'][ $sort ]['list'][ $height ]['volume'] = round($height_volume, 3);



				$sort_count  = $arr[ $name ]['list'][ $sort ]['count'];
				$sort_volume = $arr[ $name ]['list'][ $sort ]['volume'];

				$sort_count  = ($sort_count)?($sort_count + $count):$count;
				$sort_volume = ($sort_volume)?($sort_volume + $volume):$volume;

				$arr[ $name ]['list'][ $sort ]['count']  = round($sort_count);
				$arr[ $name ]['list'][ $sort ]['volume'] = round($sort_volume, 3);



				$name_count  = $arr[ $name ]['count'];
				$name_volume = $arr[ $name ]['volume'];

				$name_count  = ($name_count)?($name_count + $count):$count;
				$name_volume = ($name_volume)?($name_volume + $volume):$volume;

				$arr[ $name ]['count']  = round($name_count);
				$arr[ $name ]['volume'] = round($name_volume, 3);
			}
			break;
		case 'hwls':
			foreach ($data as $value) {
				$name   = $value['name'];
				$sort   = $value['sort'];
				$length = +$value['length'];
				$width  = +$value['width'];
				$height = +$value['height'];
				$count  = +$value['count'];
				$volume = +$value['volume'];



				$sort_count  = $arr[ $name ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['list'][ $sort ]['count'];
				$sort_volume = $arr[ $name ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['list'][ $sort ]['volume'];

				$sort_count  = ($sort_count)?($sort_count + $count):$count;
				$sort_volume = ($sort_volume)?($sort_volume + $volume):$volume;

				$arr[ $name ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['list'][ $sort ]['count']  = round($sort_count);
				$arr[ $name ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['list'][ $sort ]['volume'] = round($sort_volume, 3);




				$length_count  = $arr[ $name ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['count'];
				$length_volume = $arr[ $name ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['volume'];

				$length_count  = ($length_count)?($length_count + $count):$count;
				$length_volume = ($length_volume)?($length_volume + $volume):$volume;

				$arr[ $name ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['count']  = round($length_count);
				$arr[ $name ]['list'][ $height ]['list'][ $width ]['list'][ $length ]['volume'] = round($length_volume, 3);




				$width_count  = $arr[ $name ]['list'][ $height ]['list'][ $width ]['count'];
				$width_volume = $arr[ $name ]['list'][ $height ]['list'][ $width ]['volume'];

				$width_count  = ($width_count)?($width_count + $count):$count;
				$width_volume = ($width_volume)?($width_volume + $volume):$volume;

				$arr[ $name ]['list'][ $height ]['list'][ $width ]['count']  = round($width_count);
				$arr[ $name ]['list'][ $height ]['list'][ $width ]['volume'] = round($width_volume, 3);




				$height_count  = $arr[ $name ]['list'][ $height ]['count'];
				$height_volume = $arr[ $name ]['list'][ $height ]['volume'];

				$height_count  = ($height_count)?($height_count + $count):$count;
				$height_volume = ($height_volume)?($height_volume + $volume):$volume;

				$arr[ $name ]['list'][ $height ]['count']  = round($height_count);
				$arr[ $name ]['list'][ $height ]['volume'] = round($height_volume, 3);



				
				$name_count  = $arr[ $name ]['count'];
				$name_volume = $arr[ $name ]['volume'];

				$name_count  = ($name_count)?($name_count + $count):$count;
				$name_volume = ($name_volume)?($name_volume + $volume):$volume;

				$arr[ $name ]['count']  = round($name_count);
				$arr[ $name ]['volume'] = round($name_volume, 3);
			}
			break;
		case 'lwhs':
			foreach ($data as $value) {
				$name   = $value['name'];
				$sort   = $value['sort'];
				$length = +$value['length'];
				$width  = +$value['width'];
				$height = +$value['height'];
				$count  = +$value['count'];
				$volume = +$value['volume'];



				$sort_count  = $arr[ $name ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['list'][ $sort ]['count'];
				$sort_volume = $arr[ $name ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['list'][ $sort ]['volume'];

				$sort_count  = ($sort_count)?($sort_count + $count):$count;
				$sort_volume = ($sort_volume)?($sort_volume + $volume):$volume;

				$arr[ $name ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['list'][ $sort ]['count']  = round($sort_count);
				$arr[ $name ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['list'][ $sort ]['volume'] = round($sort_volume, 3);



				$height_count  = $arr[ $name ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['count'];
				$height_volume = $arr[ $name ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['volume'];

				$height_count  = ($height_count)?($height_count + $count):$count;
				$height_volume = ($height_volume)?($height_volume + $volume):$volume;

				$arr[ $name ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['count']  = round($height_count);
				$arr[ $name ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['volume'] = round($height_volume, 3);



				$width_count  = $arr[ $name ]['list'][ $length ]['list'][ $width ]['count'];
				$width_volume = $arr[ $name ]['list'][ $length ]['list'][ $width ]['volume'];

				$width_count  = ($width_count)?($width_count + $count):$count;
				$width_volume = ($width_volume)?($width_volume + $volume):$volume;

				$arr[ $name ]['list'][ $length ]['list'][ $width ]['count']  = round($width_count);
				$arr[ $name ]['list'][ $length ]['list'][ $width ]['volume'] = round($width_volume, 3);



				$length_count  = $arr[ $name ]['list'][ $length ]['count'];
				$length_volume = $arr[ $name ]['list'][ $length ]['volume'];

				$length_count  = ($length_count)?($length_count + $count):$count;
				$length_volume = ($length_volume)?($length_volume + $volume):$volume;

				$arr[ $name ]['list'][ $length ]['count']  = round($length_count);
				$arr[ $name ]['list'][ $length ]['volume'] = round($length_volume, 3);



				$name_count  = $arr[ $name ]['count'];
				$name_volume = $arr[ $name ]['volume'];

				$name_count  = ($name_count)?($name_count + $count):$count;
				$name_volume = ($name_volume)?($name_volume + $volume):$volume;

				$arr[ $name ]['count']  = round($name_count);
				$arr[ $name ]['volume'] = round($name_volume, 3);
			}
			break;
		case 'hslw':
			foreach ($data as $value) {
				$name   = $value['name'];
				$sort   = $value['sort'];
				$length = +$value['length'];
				$width  = +$value['width'];
				$height = +$value['height'];
				$count  = +$value['count'];
				$volume = +$value['volume'];



				$width_count  = $arr[ $name ]['list'][ $height ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['count'];
				$width_volume = $arr[ $name ]['list'][ $height ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['volume'];

				$width_count  = ($width_count)?($width_count + $count):$count;
				$width_volume = ($width_volume)?($width_volume + $volume):$volume;

				$arr[ $name ]['list'][ $height ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['count']  = round($width_count);
				$arr[ $name ]['list'][ $height ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['volume'] = round($width_volume, 3);



				$length_count  = $arr[ $name ]['list'][ $height ]['list'][ $sort ]['list'][ $length ]['count'];
				$length_volume = $arr[ $name ]['list'][ $height ]['list'][ $sort ]['list'][ $length ]['volume'];

				$length_count  = ($length_count)?($length_count + $count):$count;
				$length_volume = ($length_volume)?($length_volume + $volume):$volume;

				$arr[ $name ]['list'][ $height ]['list'][ $sort ]['list'][ $length ]['count']  = round($length_count);
				$arr[ $name ]['list'][ $height ]['list'][ $sort ]['list'][ $length ]['volume'] = round($length_volume, 3);



				$sort_count  = $arr[ $name ]['list'][ $height ]['list'][ $sort ]['count'];
				$sort_volume = $arr[ $name ]['list'][ $height ]['list'][ $sort ]['volume'];

				$sort_count  = ($sort_count)?($sort_count + $count):$count;
				$sort_volume = ($sort_volume)?($sort_volume + $volume):$volume;

				$arr[ $name ]['list'][ $height ]['list'][ $sort ]['count']  = round($sort_count);
				$arr[ $name ]['list'][ $height ]['list'][ $sort ]['volume'] = round($sort_volume, 3);



				$height_count  = $arr[ $name ]['list'][ $height ]['count'];
				$height_volume = $arr[ $name ]['list'][ $height ]['volume'];

				$height_count  = ($height_count)?($height_count + $count):$count;
				$height_volume = ($height_volume)?($height_volume + $volume):$volume;

				$arr[ $name ]['list'][ $height ]['count']  = round($height_count);
				$arr[ $name ]['list'][ $height ]['volume'] = round($height_volume, 3);


				
				$name_count  = $arr[ $name ]['count'];
				$name_volume = $arr[ $name ]['volume'];

				$name_count  = ($name_count)?($name_count + $count):$count;
				$name_volume = ($name_volume)?($name_volume + $volume):$volume;

				$arr[ $name ]['count']  = round($name_count);
				$arr[ $name ]['volume'] = round($name_volume, 3);
			}
			break;
		default:
			foreach ($data as $value) {
				$name   = $value['name'];
				$sort   = $value['sort'];
				$length = +$value['length'];
				$width  = +$value['width'];
				$height = +$value['height'];
				$count  = +$value['count'];
				$volume = +$value['volume'];



				$height_count  = $arr[ $name ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['count'];
				$height_volume = $arr[ $name ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['volume'];

				$height_count  = ($height_count)?($height_count + $count):$count;
				$height_volume = ($height_volume)?($height_volume + $volume):$volume;

				$arr[ $name ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['count']  = round($height_count);
				$arr[ $name ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['list'][ $height ]['volume'] = round($height_volume, 3);



				$width_count  = $arr[ $name ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['count'];
				$width_volume = $arr[ $name ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['volume'];

				$width_count  = ($width_count)?($width_count + $count):$count;
				$width_volume = ($width_volume)?($width_volume + $volume):$volume;

				$arr[ $name ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['count']  = round($width_count);
				$arr[ $name ]['list'][ $sort ]['list'][ $length ]['list'][ $width ]['volume'] = round($width_volume, 3);



				$length_count  = $arr[ $name ]['list'][ $sort ]['list'][ $length ]['count'];
				$length_volume = $arr[ $name ]['list'][ $sort ]['list'][ $length ]['volume'];

				$length_count  = ($length_count)?($length_count + $count):$count;
				$length_volume = ($length_volume)?($length_volume + $volume):$volume;

				$arr[ $name ]['list'][ $sort ]['list'][ $length ]['count']  = round($length_count);
				$arr[ $name ]['list'][ $sort ]['list'][ $length ]['volume'] = round($length_volume, 3);



				$sort_count  = $arr[ $name ]['list'][ $sort ]['count'];
				$sort_volume = $arr[ $name ]['list'][ $sort ]['volume'];

				$sort_count  = ($sort_count)?($sort_count + $count):$count;
				$sort_volume = ($sort_volume)?($sort_volume + $volume):$volume;

				$arr[ $name ]['list'][ $sort ]['count']  = round($sort_count);
				$arr[ $name ]['list'][ $sort ]['volume'] = round($sort_volume, 3);



				$name_count  = $arr[ $name ]['count'];
				$name_volume = $arr[ $name ]['volume'];

				$name_count  = ($name_count)?($name_count + $count):$count;
				$name_volume = ($name_volume)?($name_volume + $volume):$volume;

				$arr[ $name ]['count']  = round($name_count);
				$arr[ $name ]['volume'] = round($name_volume, 3);
			}
			break;
	}

	ksort($arr);
	$list = creating_list( $arr );
}

?> 

<div class="row">
	<div class="col-12 col-xxl-8 order-1 order-xxl-1 mb-3">
		<div id="accounting" class="card item mb-4">
			<div class="card-body">
				<div class="d-flex justify-content-between">
					<div class="title">
						<h5>Склад готовой продукции</h5>
						<p class="small">Данные обновлены <?= gmdate("d.m.Y H:i:s", $accounting['date']); ?></p>
					</div>
					<div class="select-sorting">
						<select class="form-select pe-5">
							<option value="slwh">Сорт-Длина-Ширина-Высота</option>
							<option value="shwl">Сорт-Высота-Ширина-Длина</option>
							<option value="hwls">Высота-Ширина-Длина-Сорт</option>
							<option value="lwhs">Длина-Ширина-Высота-Сорт</option>
							<option value="hslw">Высота-Сорт-Длина-Ширина</option>
						</select>
					</div>
					
				</div>
				<ul class="list-accounting"><?= $list; ?></ul>
			</div>
		</div>
	</div>
	<div class="col-12 col-xxl-4 order-2 order-xxl-2 mb-3">
		<div id="update-accounting" class="card item mb-4">
			<div class="card-body">
				<div class="mb-3">
					<label for="formFile" class="form-label">Обновить данные</label>
					<input class="form-control" type="file" multiple="multiple" accept=".xlsx">
				</div>
				<button type="submit" class="btn btn-success w-100" data-action="recognizeFile"><i>Распознать</i><div class="spinner-border mx-auto" role="status"></div></button>
				<div class="message"></div>
			</div>
		</div>
	</div>
</div>