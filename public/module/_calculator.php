<?php 
/**
 * Вывод калькулятора
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */


// Подключаем скрипт
wp_enqueue_script( 
    'page-calculator', 
    plugins_url() . '/forest-manager/public/js/page-calculator.js', 
    array(), 
    get_filemtime( 'page-calculator.js' ), 
    true
);

?>  

<div id="calculator" class="card">
	<div class="row">
	    <div class="col-xl-4 col-lg-12">
	        <div class="parameters block p-4">
	            <h3>Укажите параметры</h3>
	            <div class="form-group d-flex align-items-center">
	                <label class="w-100" for="height">Высота</label>
	                <input type="text" class="form-control text-center" id="height" name="height" value="1">
	            </div>
	            <div class="form-group d-flex align-items-center">
	                <label class="w-100" for="width">Ширина</label>
	                <input type="text" class="form-control text-center" id="width" name="width" value="1">
	            </div>
	            <div class="form-group d-flex align-items-center">
	                <label class="w-100" for="length">Длина</label>
	                <input type="text" class="form-control text-center" id="length" name="length" value="1">
	            </div>
	            <div class="form-group d-flex align-items-center">
	                <label class="w-100" for="count">Количество</label>
	                <input type="text" class="form-control text-center" id="count" name="count" value="1">
	            </div>
	            <div class="form-group d-flex align-items-center">
	                <label class="w-100" for="price">Цена за 1 м<sup>3</sup></label>
	                <input type="text" class="form-control text-center" id="price" name="price" value="1">
	            </div>
	            <button class="btn btn-big btn-success w-100" data-action="calculate">Расчитать</button>
	        </div>
	    </div>
	    <div class="col-xl-4 col-lg-6">
	        <div class="original-data block p-4">
	            <h3>Исходные данные</h3>
	            <ul class="list-group list-group-flush">
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Высота</span>
	                    <div class="height w-50 text-right">
	                        <b>- - -</b> <span>мм</span>
	                    </div>
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Ширина</span>
	                    <div class="width w-50 text-right">
	                        <b>- - -</b> <span>мм</span>
	                    </div>
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Длина</span>
	                    <div class="length w-50 text-right">
	                        <b>- - -</b> <span>мм</span>
	                    </div>
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Количество</span>
	                    <div class="count w-50 text-right">
	                        <b>- - -</b> <span>шт</span>
	                    </div>
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Цена за 1 м<sup>3</sup></span>
	                    <div class="price w-50 text-right">
	                        <b>- - -</b><span>руб</span>
	                    </div>
	                </li>
	            </ul>
	        </div>
	    </div>
	    <div class="col-xl-4 col-lg-6">
	        <div class="calculation-result block p-4">
	            <h3>Результаты расчёта</h3>
	            <ul class="list-group list-group-flush">
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Объём 1 шт</span>
	                    <div class="volume-piece w-50 text-right">
	                        <b>- - -</b> <span>м<sup>3</sup></span>
	                    </div>
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Объём</span>
	                    <div class="volume w-50 text-right">
	                        <b>- - -</b> <span>м<sup>3</sup></span>
	                    </div>
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Количество в м<sup>3</sup></span>
	                    <div class="count-cube w-50 text-right">
	                        <b>- - -</b> <span>шт</span>
	                    </div>
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Количество м<sup>2</sup></span>
	                    <div class="count-square w-50 text-right">
	                        <b>- - -</b> <span>м<sup>2</sup></span>
	                    </div>
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Цена за шт</span>
	                    <div class="amount-piece w-50 text-right">
	                        <b>- - -</b> <span>руб</span>
	                    </div class="height">
	                </li>
	                <li class="list-group-item d-flex justify-content-between">
	                    <span>Общая цена</span>
	                    <div class="total-amount w-50 text-right">
	                        <b>- - -</b> <span>руб</span>
	                    </div>
	                </li>
	            </ul>
	        </div>
	    </div>
	</div>
</div>


















