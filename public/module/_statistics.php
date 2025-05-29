<?php 
/**
 * Страницо Статистики (главная страница)
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

/**
 * УЖЕ ПОЛУЧНО:
 * class-forest-manager-general-function.php
 * $version
 */

wp_enqueue_script(
    'module-page-statistics',
    plugins_url() . '/forest-manager/public/js/page-statistics.js',
    array(),
    get_filemtime( 'page-statistics.js' ),
    true
);

$user_id = get_current_user_id();
$user_view_list = get_the_author_meta( 'view_list', $user_id );
$array_view_list = ($user_view_list)?explode(',', $user_view_list):[];

$list_clients = [];
if (empty($array_view_list)) {
	$list_clients = Forest_Manager_General_Function::get_data__clients(['parameter' => 'unequal']);
} else {
	foreach ($array_view_list as $user) {
		$list = Forest_Manager_General_Function::get_data__clients(['parameter' => 'unequal', 'user_id' => $user]);
		$list_clients = array_merge($list_clients, $list);
	} 
}

$arr = [
	'plus' => [
		'list'  => [],
		'total' => 0,
	],
	'minus' => [
		'list'  => [],
		'total' => 0,
	],
];

foreach ($list_clients as &$client) {
	$data = [
		'balance'    => +$client['debet'] - +$client['credit'],
		'clients_id' => $client['id'],
		'name'       => $client['name'],
		'inn'        => $client['inn'],
		'user_name'  => $client['user_name'],
	];
	if ($data['balance'] > 0) { 
		$arr['plus']['list'][] = $data;
		$arr['plus']['total'] += $data['balance'];
	}
	if ($data['balance'] < 0) {
		$arr['minus']['list'][] = $data;
		$arr['minus']['total'] += $data['balance'];
	}
}
// Сортировка
usort($arr['plus']['list'], function($a, $b) { return $b['balance'] <=> $a['balance']; }); 
usort($arr['minus']['list'], function($a, $b) { return $a['balance'] <=> $b['balance']; }); 
?> 

<div class="row">
	<div class="col-12 mb-4">
		<div id="balance-client" class="card">
			<div class="card-body">
				<div class="d-sm-flex align-items-center mb-4">
					<h4 class="card-title mb-sm-0">Текущий баланс клиентов</h4>
				</div>
				<div class="row">
					<?php foreach ($arr as $value) { ?>
						<div class="col-12 col-md-6">
							<div class="d-flex flex-column justify-content-between h-100">
								<table class="card-table table table-striped"> 
								    <thead>
								        <tr>
											<th class="text-muted text-center">#</th>
											<th class="name text-muted">Название</th>
											<th class="inn text-muted text-center">ИНН</th>
											<th class="user text-muted text-center">Менеджер</th>
											<th class="balance text-muted text-center">Баланс</th>
								        </tr>
								    </thead>
								    <tbody>
								    	<?php  if (empty($value['list'])) { ?>
								    		<tr><td colspan="5" class="fst-italic text-muted text-center">- - Временно пустует - -</td></tr>
								    	<?php 
								    		} else {
										    	$i = 1;
										    	foreach ($value['list'] as $client) {
									    ?>
								    			<tr class="item" data-client-id="<?= $client['clients_id']; ?>">
								    				<td class="text-muted text-center"><?= $i++; ?></td>
								    				<td class="name"><a data-action="open-client"><?= $client['name']; ?></a></td>
								    				<td class="inn text-center"><?= $client['inn']; ?></td>
								    				<td class="user text-center"><?= $client['user_name']; ?></td>
								    				<td class="balance text-center <?php echo ($client['balance'] >= 0)?'text-success':'text-danger' ?>"><?= number_format($client['balance'], 2, '.',' '); ?> ₽</td>
								    			</tr>
								    	<?php }} ?>
								    </tbody>
								</table>
								<table class="card-table table table-striped">
									<tfoot>
										<tr>
											<td colspan="4" class="text-end"><b>Итого:</b></td>
											<td class="balance text-center <?php echo ($value['total'] >= 0)?'text-success':'text-danger' ?>"><b><?= number_format($value['total'], 2, '.',' '); ?> ₽</b></td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

