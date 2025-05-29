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

// Подключаем скрипт
wp_enqueue_script( 
    'module-page-client', 
    plugins_url() . '/forest-manager/public/js/page-client.js', 
    array(), 
    get_filemtime( 'page-client.js' ), 
    true
);

?> 

<div class="row"> 
	<div class="col-12 col-xxl-4 order-1 order-xxl-2 mb-3">
		<div id="about-client" data-id="<?= $_GET['id']; ?>" class="card item mb-4">  
			<div class="card-body card-client">
				<div class="card-top d-sm-flex align-items-center mb-4">
					<h4 id="name-client" class="card-title mb-sm-0"><div class="loading-block h-32"></div></h4>
					<div class="card-button ms-auto mb-3 mb-sm-0"></div>
				</div>
				<table id="list-about-client" class="card-table table mb-3">
					<tbody>
						<tr class="phone">
							<td><div class="loading-block h-14"></div></td>
							<td><div class="loading-block h-14"></div></td>
						</tr>
						<tr class="email">
							<td><div class="loading-block h-14"></div></td>
							<td><div class="loading-block h-14"></div></td>
						</tr>
						<tr class="balance">
							<td><div class="loading-block h-14"></div></td>
							<td><div class="loading-block h-14"></div></td>
						</tr>
					</tbody>
				</table>
				<div id="messages">
					<h4 class="card-title mb-4">Сообщения</h4> 
					<div id="messages-view" class="mb-2"> 
						<div class="scrollable scrollable-y border mb-3"> 
							<div class="container-inner px-3"></div>
						</div>
					</div>
					<form id="comments-inter">
						<input type="hidden" name="id" value="">
						<div class="form-info d-flex mb-2"></div>
						<div class="form-input d-flex">
							<textarea row="3" type="text" class="form-control w-100" name="comment" placeholder="Добавить комментарий"></textarea>
			                <button type="button" class="btn btn-success btn-sm ms-3" data-action="commentInter">Добавить</button>
						</div> 
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="col-12 col-xxl-8 order-2 order-xxl-1 mb-3">
		<div id="specification-client" class="card mb-4"> 
			<div class="card-body card-client">
				<div class="card-top d-sm-flex align-items-center mb-4">
					<h4 class="card-title mb-sm-0">Спецификации</h4>
					<div class="card-button ms-auto mb-3 mb-sm-0"></div>
				</div>
				<div class="card-middle">
					<table id="list-specification-client" class="card-table table table-striped">
					    <thead>
					        <tr>
								<th class="text-muted text-center">#</th>
								<th class="number text-muted text-center">Номер</th>
								<th class="date text-muted text-center">Дата</th>
								<th class="cash text-muted text-center">Расчёт</th> 
								<th class="user text-muted text-center">Менеджер</th>
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
				<div class="card-bottom pagination-container d-flex mt-3">
					<button type="button" class="btn btn-dark btn-md py-2 px-3 mx-1" data-action="pagination" data-page="0">∞</button>
					<div class="list m-0 p-0 d-flex justify-content-start"></div>
				</div>
			</div>
		</div>

		<div id="account-client" class="card mb-4"> 
			<div class="card-body card-client">
				<div class="card-top d-sm-flex align-items-center mb-3"> 
					<h4 class="card-title mb-sm-0">Счета</h4>
					<div class="card-button ms-auto mb-3 mb-sm-0"></div>
				</div>
				<div class="card-middle">
					<table id="list-account-client" class="card-table table table-striped">
					    <thead>
					        <tr>
								<th class="text-muted text-center">#</th>
								<th class="number text-muted text-center">Номер</th>
								<th class="date text-muted text-center">Дата оплаты</th>
								<th class="cash text-muted text-center">Расчёт</th>
								<th class="user text-muted text-center">Менеджер</th>
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
				<div class="card-bottom pagination-container d-flex mt-3">
					<button type="button" class="btn btn-dark btn-md py-2 px-3 mx-1" data-action="pagination" data-page="0">∞</button>
					<div class="list m-0 p-0 d-flex justify-content-start"></div>
				</div>
			</div>
		</div>
	</div>

</div> 