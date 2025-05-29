<?php 
/**
 * Страницо Все клиенты
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
    'module-page-physical-entity', 
    plugins_url() . '/forest-manager/public/js/page-physical-entity.js', 
    array(), 
    get_filemtime( 'page-physical-entity.js' ), 
    true
);


?> 

<div class="row">
	<div class="col-12 col-xxl-8">
		<div id="physical-entity" class="card">
			<div class="card-body">
				<div class="card-top d-sm-flex align-items-center mb-4">
					<h4 class="card-title mb-sm-0">Список физических лиц</h4>
					<div class="card-button ms-auto mb-3 mb-sm-0"></div>
				</div>
				<table id="list-physical-entity" class="card-table table table-striped">
				    <thead>
				        <tr>
							<th class="text-muted text-center">#</th>
							<th class="name text-muted">Имя</th>
							<th class="phone text-muted text-center">Телефон</th>
							<th class="email text-muted text-center">Email</th>
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
</div>


