 <?php 
/**
 * Страница Клиента
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

/**
 * УЖЕ ПОЛУЧНО:
 * class-forest-manager-general-function.php
 * $version
 */

parse_str($_SERVER['QUERY_STRING'], $get);
$data_client = array_shift(Forest_Manager_General_Function::get_data('client', [ 'id' => $get['id'] ]));

wp_enqueue_script(
    'module-page-client-item',
    plugins_url() . '/forest-manager/public/js/page-client-item.js',
    array(),
    get_filemtime( 'page-client-item.js' ),
    true
);
?> 

<div class="row">
	<div class="col-12 col-xxl-4 order-1 order-xxl-2 mb-3">
		<div id="about-client" data-type="client" data-id="<?= $get['id']; ?>" class="card item mb-4">  
			<div class="card-body">
				<div class="card-top d-sm-flex align-items-center mb-4">
					<h4 id="name-client" class="card-title mb-sm-0"><div class="loading-block h-32"></div></h4>
					<div class="card-button ms-auto mb-3 mb-sm-0"></div>
				</div>
				<table id="list-about-client" class="card-table table mb-3">
					<tbody>
						<tr class="phone">
							<td class="text-muted">Телефон</td>
							<td><div class="loading-block h-14"></div></td>
						</tr>
						<tr class="email">
							<td class="text-muted">Email</td>
							<td><div class="loading-block h-14"></div></td>
						</tr>
						<tr class="balance">
							<td class="text-muted">Баланс</td>
							<td><div class="loading-block h-14"></div></td>
						</tr>
					</tbody>
				</table>
				<div id="messages">
					<h4 class="card-title mb-4">Сообщения</h4> 
					<div id="messages-view" class="mb-3">
						<div class="scrollable scrollable-y border mb-3">
							<div class="container-inner px-3"></div>
						</div>
					</div>
					<form id="comments-inter" class="d-flex"> 
						<input type="hidden" name="id" value="<?= $get['id']; ?>">
						<input type="hidden" name="user_id" value="<?= get_current_user_id(); ?>"> 
		                <textarea row="3" type="text" class="form-control w-100" name="comment" placeholder="Добавить комментарий"></textarea>
		                <button type="button" class="btn btn-success btn-sm ms-3" data-action="commentInter">Добавить</button>  
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="col-12 col-xxl-8 order-2 order-xxl-1 mb-3">
		<div id="account-client" class="card mb-4">
			<div class="card-body">
				<div class="d-sm-flex align-items-center mb-3"> 
					<h4 class="card-title mb-sm-0">Счета</h4>
					<div class="card-button ms-auto mb-3 mb-sm-0"> 
						<button type="button" class="btn btn-primary btn-icon btn-sm" data-open-modal="addEditAccountModal" data-action-modal="forest_add_account" data-client-id="<?= $data_client['id']; ?>">
							<i class="icon-plus btn-icon-prepend"></i>
						</button>
					</div>
				</div>
				<table id="list-account-client" class="card-table table table-striped">
				    <thead>
				        <tr>
							<th class="text-muted text-center">#</th>
							<th class="company-client text-muted">Название</th> 
							<th class="number text-muted text-center">Номер</th>
							<th class="specification text-muted text-center">Спецификация</th>
							<th class="cash text-muted text-center">Расчёт</th>
							<th class="date text-muted text-center">Дата</th>
							<th class="amount text-muted text-center">Сумма</th>
							<th class="amount-paid text-muted text-center">Оплачено</th> 
							<th class="button"></th> 
				        </tr>
				    </thead>
				    <tbody>
				    	<tr>
				    		<td colspan="9" class="table loading">~</td> 
				    	</tr>
				    </tbody>
				    <tfoot></tfoot>
				</table>
			</div>
		</div>

		<div id="specification-client" class="card"> 
			<div class="card-body">
				<div class="d-sm-flex align-items-center mb-4">
					<h4 class="card-title mb-sm-0">Спецификации</h4>
					<div class="card-button ms-auto mb-3 mb-sm-0">
						<button type="button" class="btn btn-primary btn-icon btn-sm" data-open-modal="addEditSpecificationModal" data-action-modal="forest_add_specification" data-client-id="<?= $data_client['id']; ?>"><i class="icon-plus btn-icon-prepend"></i></button> 
					</div>
				</div>
				<table id="list-specification-client" class="card-table table table-striped">
				    <thead>
				        <tr>
							<th class="text-muted text-center">#</th>
							<th class="company-client text-muted">Клиент</th>
							<th class="number text-muted text-center">Номер</th>
							<th class="account text-muted text-center">Счёт</th>
							<th class="cash text-muted text-center">Расчёт</th> 
							<th class="date text-muted text-center">Дата</th>
							<th class="dispatch text-muted text-center">Отгрузка</th>
							<th class="amount text-muted text-center">Сумма</th> 
							<th class="button"></th> 
				        </tr>
				    </thead>
				    <tbody>
				    	<tr>
				    		<td colspan="9" class="table loading">~</td>   
				    	</tr>
				    </tbody>
				    <tfoot></tfoot>
				</table>
			</div>
		</div>
	</div>

</div> 