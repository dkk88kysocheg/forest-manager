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

// Подключаем скрипт
wp_enqueue_script( 
    'module-page-reports', 
    plugins_url() . '/forest-manager/public/js/page-reports.js', 
    array(), 
    get_filemtime( 'page-reports.js' ), 
    true
);

$list_clients = Forest_Manager_General_Function::get_data('clients');
$list_product = Forest_Manager_General_Function::get_data('product');

?> 

<div class="row">
	<div class="col-12 col-xxl-4 mb-4"> 
		<form id="uploading-report-data" class="card">
			<input type="hidden" name="user_id" value="<?= $user_id; ?>">
			<div class="card-body">
				<div class="d-sm-flex align-items-center mb-4">
					<h4 class="card-title mb-sm-0">Сформировать отчёт</h4>
				</div>
				<div class="period d-flex mb-3">
					<div class="form-group mb-0"> 
						<label>Начало периода</label>
						<input type="date" class="form-control" name="date_from" value="<?= date("Y-m-d"); ?>" required> 
					</div>
					<span class="m-2"></span>
					<div class="form-group mb-0">
						<label>Окончание периода</label>
						<input type="date" class="form-control" name="date_to" value="<?= date("Y-m-d"); ?>" required> 
					</div>
				</div>
				<hr>
				<div class="action mb-3">
					<ul class="m-0 p-0">
						<li class="d-flex">
							<div class="d-flex w-100">
								<div class="form-check form-check-flat form-check-primary w-50">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="action" value="report-dispatch" checked>    
										Отчет по отгрузкам
										<i class="input-helper"></i>
									</label>
								</div>
								<!-- <div class="form-check form-check-flat form-check-primary ms-4 w-50">
									<label class="form-check-label">
										<input type="checkbox" name="signed-document" class="form-check-input" value="1">Подписанный документ<i class="input-helper"></i>
									</label>
								</div> -->
							</div>
						</li>
						<li class="d-flex flex-column">
							<div class="d-flex w-100">
								<div class="form-check form-check-flat form-check-primary w-50">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="action" value="report-product">    
										Отчет по продукции
										<i class="input-helper"></i>
									</label>
								</div>
							</div>

							<div id="listClient" class="form-group" style="display: none">
								<input type="hidden" name="client_id">
								<input class="form-control" list="listClientOptions" id="searchClient" placeholder="Начните печатать для поиска клиента ...">
								<datalist id="listClientOptions">
									<?php 
										foreach ($list_clients as $client) {
											echo "<option value=\"" . $client['name'] . "\" data-client-id=\"" . $client['id'] . "\"></option>";
										}
									?>
								</datalist>
							</div>
							<div id="listProduct" class="form-group" style="display: none">
								<input type="hidden" name="product_id">
								<input class="form-control" list="listProductOptions" id="searchProduct" placeholder="Начните печатать для поиска продукции ...">
								<datalist id="listProductOptions">
									<?php 
										foreach ($list_product as $product) {
											echo "<option value=\"" . $product['name_search'] . "\" data-product-id=\"" . $product['id'] . "\"></option>";
										}
									?>
								</datalist>
							</div>
						</li>
						<li class="d-flex">
							<div class="form-check form-check-flat form-check-primary">
								<label class="form-check-label">
									<input type="radio" class="form-check-input" name="action" value="report-debtor">    
									Отчет по должникам
									<i class="input-helper"></i>
								</label>
							</div>
						</li>
						<li class="d-flex">
							<div class="form-check form-check-flat form-check-primary">
								<label class="form-check-label">
									<input type="radio" class="form-check-input" name="action" value="report-payment">    
									Отчет по платежам
									<i class="input-helper"></i>
								</label>
							</div>
						</li>
						<li class="d-flex">
							<div class="form-check form-check-flat form-check-primary">
								<label class="form-check-label">
									<input type="radio" class="form-check-input" name="action" value="report-clients">    
									Отчет по клиентам
									<i class="input-helper"></i>
								</label>
							</div>
						</li>
					</ul>
				</div>
				<hr>
				<div class="organization-form mb-3">
					<ul class="m-0 p-0">
						<li class="form-check form-check-flat form-check-primary">
							<label class="form-check-label">
								<input type="radio" class="form-check-input" name="organization_form" value="all" checked>
								Все
								<i class="input-helper"></i>
							</label>
						</li>
						<li class="form-check form-check-flat form-check-primary">
							<label class="form-check-label">
								<input type="radio" class="form-check-input" name="organization_form" value="1"> 
								Юридические лица
								<i class="input-helper"></i>
							</label>
						</li>
						<li class="form-check form-check-flat form-check-primary">
							<label class="form-check-label">
								<input type="radio" class="form-check-input" name="organization_form" value="0"> 
								Физические лица
								<i class="input-helper"></i>
							</label>
						</li>
					</ul>
				</div> 
				<hr>
				<div class="cash mb-3">
					<ul class="m-0 p-0">
						<li class="form-check form-check-flat form-check-primary">
							<label class="form-check-label">
								<input type="radio" class="form-check-input" name="cash" value="all" checked>
								Все
								<i class="input-helper"></i>
							</label>
						</li>
						<li class="form-check form-check-flat form-check-primary">
							<label class="form-check-label">
								<input type="radio" class="form-check-input" name="cash" value="0"> 
								Безнал
								<i class="input-helper"></i>
							</label>
						</li>
						<li class="form-check form-check-flat form-check-primary">
							<label class="form-check-label">
								<input type="radio" class="form-check-input" name="cash" value="1"> 
								Касса
								<i class="input-helper"></i>
							</label>
						</li>
						<li class="form-check form-check-flat form-check-primary">
							<label class="form-check-label">
								<input type="radio" class="form-check-input" name="cash" value="2"> 
								Н/Л
								<i class="input-helper"></i>
							</label>
						</li>
					</ul>
				</div> 
				<button class="btn btn-success" data-action="uploadingReportData">Сформировать</button> 
			</div>
		</form>
	</div>

	<?php if (array_search($user_id, [0,1,10,13])): ?>
		<div class="col-12 col-xxl-4 mb-4"> 
			<form id="summary-report-data" class="card">
				<input type="hidden" name="action" value="report-summary">
				<div class="card-body">
					<div class="d-sm-flex align-items-center mb-4">
						<h4 class="card-title mb-sm-0">Сводный отчёт</h4>
					</div>
					<div class="period d-flex mb-3">
						<div class="form-group mb-0"> 
							<label>Начало периода</label>
							<input type="date" class="form-control" name="date_from" value="<?= date("Y-m-d"); ?>" required> 
						</div>
						<span class="m-2"></span>
						<div class="form-group mb-0">
							<label>Окончание периода</label>
							<input type="date" class="form-control" name="date_to" value="<?= date("Y-m-d"); ?>" required> 
						</div>
					</div>
					<button class="btn btn-success" data-action="summaryReportData">Сформировать</button> 
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>