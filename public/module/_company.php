<?php 
/**
 * Страницо Все компании
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
	'module-page-legal-entity', 
	plugins_url() . '/forest-manager/public/js/page-legal-entity.js', 
	array(), 
	get_filemtime( 'page-legal-entity.js' ), 
	true
);

?>


<div class="row">
	<div class="col-12 col-xxl-8">
		<div id="all-company" class="card">
			<div class="card-body">

				<div class="d-sm-flex align-items-center mb-4">
					<h4 class="card-title mb-sm-0">Список юридических лиц</h4>
					<div class="card-button ms-auto mb-3 mb-sm-0">
						<button type="button" class="btn btn-primary btn-icon btn-sm" data-open-modal="addEditCompanyModal" data-action-modal="forest_add_company"><i class="icon-plus btn-icon-prepend"></i></button>
					</div>
				</div>

				<table id="list-all-company" class="card-table table table-striped">
				    <thead>
				        <tr>
							<th class="text-muted text-center">#</th>
							<th class="name text-muted">Название</th>
							<th class="inn text-muted text-center">ИНН</th>
							<th class="contract text-muted text-center">Договор</th>
							<th class="user text-muted text-center">Менеджер</th> 
							<th class="button"></th> 
				        </tr>
				    </thead>
				    <tbody>
				    	<tr>
				    		<td colspan="6" class="table loading">~</td> 
				    	</tr>
				    </tbody>
				</table>

			</div>
		</div>
	</div>
	<div class="col-12 col-xxl-4">
		<div id="search-company" class="card">
			<div class="card-body">
				<div class="d-sm-flex align-items-center mb-4">
					<h4 class="card-title mb-sm-0">Поиск юридического лица по ИНН</h4> 
				</div>
				<form id="form-search-company" class="d-flex mb-3">
					<input type="text" class="form-control w-100" name="inn" placeholder="Введите ИНН" required>
					<button class="btn btn-success ms-3" data-action="formSearchCompany">Поиск</button>
				</form>
				<div id="answer-search-company"></div> 
			</div>
		</div>
	</div>
</div>


