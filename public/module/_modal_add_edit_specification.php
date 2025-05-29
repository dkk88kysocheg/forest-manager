<?php 
/**
 * Модальное окно
 * Добавление/Редактирование спецификации
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
    'module-modal-add-edit-specification', 
    plugins_url() . '/forest-manager/public/js/modal-add-edit-specification.js', 
    array(), 
    get_filemtime( 'modal-add-edit-specification.js' ), 
    true
);

$list_contracts = Forest_Manager_General_Function::get_data('contract', ['clients_id' => $client['id']]);

$guide = Forest_Manager_General_Function::get_data('guide');
// Сортировка
usort($guide, function($a, $b) { return $a['sorting'] <=> $b['sorting']; });

$type_arr = [];
$sort_arr = [];
$okpd_arr = [];
$name_product = [];
foreach ($guide as &$value) {
    if ( $value['key'] === 'type') { $type_arr[] = $value; }
    if ( $value['key'] === 'sort') { $sort_arr[] = $value; }
    if ( $value['key'] === 'okpd') { $okpd_arr[] = $value; }
    if ( $value['key'] === 'name_product') { $name_product[] = $value; }
}


?> 



<!-- Modal --> 
<div class="modal fade" id="addEditSpecificationModal" tabindex="-1" aria-labelledby="addEditSpecificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="formSpecification" class="content-modal px-5 py-4" method="post"> 
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="addEditSpecificationModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal my-4">
                <input type="hidden" name="action" value="">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="clients_id" value="<?= $_GET['id']; ?>">
                <input type="hidden" name="user_id" value="<?= get_current_user_id(); ?>">

                <div class="row">
                    <div class="client col-12 mb-3">
                        <label class="form-label">Клиент</label>
                        <input type="text" class="form-control" value="<?= $client['name']; ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="number-specification" class="form-label">Номер</label>
                        <input type="text" class="form-control" id="number-specification" name="number" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="date-specification" class="form-label">Дата</label>
                        <input type="date" class="form-control" id="date-specification" name="date" required>  
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cash-account" class="form-label">Расчёт</label>
                        <select class="form-select" id="cash-account" name="cash">
                            <option value="0">Безналичный</option>
                            <option value="1">Касса</option>
                            <option value="2">Н/Л</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="contract-account" class="form-label">Договор</label>
                        <select class="form-select" id="contract-account" name="contract_id">
                            <option value="0">Выберите договор</option> 
                            <?php foreach ($list_contracts as $contract) { ?>
                                <option value="<?= $contract['id']; ?>">№<?= $contract['number']; ?> до <?= gmdate("d.m.Y", $contract['date_completion']); ?></option> 
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <!-- Продукция -->
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="border px-3 pt-3 mb-3" id="add-product">
                            <div class="pane-product active" id="search-product">
                                <div class="mb-2 d-flex align-items-center">
                                    <span>Поиск</span>
                                    <button type="button" class="btn btn-sm ms-4 fw-normal text-decoration-underline text-primaryy" data-action="show-new-product">
                                        Создать новый
                                    </button>
                                </div>
                                <div class="d-flex mb-3">
                                    <select class="form-select me-3" data-name="opt" style="max-width: 100px;">
                                        <option value="0" selected>Розница</option>
                                        <option value="1">Опт</option>
                                    </select>
                                    <input class="form-control" data-name="search" list="listProductOptions" id="searchProduct" placeholder="Начните печатать для поиска ...">
                                    <datalist id="listProductOptions"></datalist>
                                    <input type="text" class="count w-25 form-control mx-3 text-center" data-name="count" value="1" data-type="number">  
                                    <button type="button" class="btn btn-primary btn-icon-text px-3" data-action="add-for-list" data-status="search">
                                        <i class="icon-plus btn-icon-prepend m-0"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="pane-product position-relative" id="new-product"> 
                                <div class="mb-2 d-flex align-items-center">
                                    <span>Новый продукт</span>
                                    <button type="button" class="btn btn-sm ms-4 fw-normal text-decoration-underline text-primaryy" data-action="show-search-product">
                                        Поиск
                                    </button>
                                </div>

                                <div class="loader-product position-absolute top-0 start-0 w-100 h-100 d-none">
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <div class="spinner-border" role="status"><span class="visually-hidden"></span></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <select class="form-select" id="new-product-type" data-name="new-name">  
                                            <option selected disabled>Название</option>
                                            <?php foreach ($name_product as &$value) { ?>
                                                <option value="<?= $value['id']; ?>"><?= $value['value']; ?></option>
                                            <?php } ?>
                                        </select> 
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <select class="form-select" id="new-product-type" data-name="new-type">  
                                            <option selected disabled>Тип древесины</option>
                                            <?php foreach ($type_arr as &$value) { ?>
                                                <option value="<?= $value['id']; ?>"><?= $value['value']; ?></option>
                                            <?php } ?>
                                        </select> 
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <select class="form-select" id="new-product-sort" data-name="new-sort">  
                                            <option selected disabled>Сорт</option>
                                            <?php foreach ($sort_arr as &$value) { ?>
                                                <option value="<?= $value['id']; ?>"><?= $value['value']; ?></option>
                                            <?php } ?>
                                        </select>  
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <input type="text" class="form-control" id="new-product-height" data-name="new-height" placeholder="Высота" data-type="number">   
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <input type="text" class="form-control" id="new-product-width" data-name="new-width" placeholder="Ширина" data-type="number">  
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <input type="text" class="form-control" id="new-product-length" data-name="new-length" placeholder="Длина" data-type="number"> 
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <input type="text" class="form-control" id="new-product-weight" data-name="new-weight" placeholder="Вес" data-type="number"> 
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mb-3">
                                    <input type="text" class="count form-control mx-3 text-center w-25" data-name="count" value="1" data-type="number">   
                                    <button type="button" class="btn btn-primary btn-icon-text px-3" data-action="add-for-list" data-status="new">
                                        <i class="icon-plus btn-icon-prepend m-0"></i>
                                    </button> 
                                </div>

                                <div class="error-message mb-3 small text-danger d-none"></div> 
                            </div>
                        </div>

                        <div id="list-product"></div>
                    </div>
                </div>
                <!-- Доставка и Скидка -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div id="delivery" class="border px-3">
                            <div class="form-check form-check-primary my-3">
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="status-delivery">Доставка<i class="input-helper"></i></label>
                            </div>
                            <div class="row" style="display:none;"> 
                                <div class="col-md-4 mb-3">
                                    <label for="count-delivery" class="form-label">Количество</label>
                                    <input type="text" class="form-control" id="count-delivery" name="count_delivery" data-type="number">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="price-delivery" class="form-label">Цена</label>
                                    <input type="text" class="form-control" id="price-delivery" name="price_delivery" data-type="number">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Сумма</label>
                                    <div class="py-3 amount"></div> 
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="delivery-address" class="form-label">Адрес доставки</label>
                                    <textarea class="form-control" id="address-delivery" name="address_delivery" rows="4"></textarea>  
                                </div> 
                            </div>
                        </div> 
                    </div>

                    <div class="col-md-6 mb-3">
                        <div id="discount" class="border px-3">
                            <div class="form-check form-check-primary my-3"> 
                              <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="status-discount">Скидка<i class="input-helper"></i></label>
                            </div>
                            <div class="row" style="display:none;">
                                <div class="col-md-5 mb-3">
                                    <label for="amount-discount" class="form-label">Сумма</label>
                                    <input type="text" class="form-control" id="amount-discount" name="discount" data-type="number">
                                </div>
                                <div class="col-md-7 mb-3">
                                    <button type="button" class="btn btn-link p-0 text-decoration-none text-primaryy" data-action="calculatePercentage"><i class="icon-calculator btn-icon-prepend me-3"></i>Посчитать процент</button>
                                    <div class="calculatePercentage"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Итого к оплате -->
                <table class="table table-striped my-3" id="list-product-total-amount">
                    <tfoot>
                        <tr>
                            <td class="text text-end py-3 fw-bold">Итого к оплате:</td>
                            <td class="amount text-center py-3 fw-bold">
                                <input type="hidden" name="amount" value="0">
                                <span></span>
                            </td>
                            <td class="button"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mb-3">
                <label for="additional-specification" class="form-label">Дополнительное поле</label>
                <input type="text" class="form-control" id="additional-specification" data-name="additional" data-type="number" name="additional">
            </div>
            <div class="footer-modal d-flex justify-content-end"> 
                <button type="submit" class="btn btn-success" data-action="addEditSpecification">Сохранить</button>
            </div>
        </form>
    </div>
    <div id="buffer" class="d-none">
        <ul id="list-okpd">
            <?php foreach ($okpd_arr as &$value) { ?>
                <li data-id="<?= $value['id']; ?>" data-value="<?= $value['value']; ?>"></li>
            <?php } ?>
        </ul>
    </div>
</div>

<!-- Modal - Dispatch -->
<div class="modal fade" id="dispatchSpecificationModal" tabindex="-1" aria-labelledby="dispatchSpecificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form id="formDispatchSpecification" class="content-modal p-3" method="post"> 
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="dispatchSpecificationModalLabel">Отгрузка</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal mt-4">
                <input type="hidden" name="action" value="forest_dispatch_specification"> 
                <input type="hidden" name="id" value="">
                <input type="hidden" name="clients_id" value="<?= $_GET['id']; ?>">
                <input type="hidden" name="user_id" value="<?= get_current_user_id(); ?>">

                <div class="mb-3">
                    <label for="date-dispatch" class="form-label">Дата отгрузки</label>
                    <input type="date" class="form-control" id="date-dispatch" name="date_dispatch" required>   
                </div>
                <div class="mb-3">
                    <label for="car-brand" class="form-label">Марка машины</label>
                    <input type="text" class="form-control" id="car-brand" name="car_brand" required>
                </div>
                <div class="mb-3">
                    <label for="car-number" class="form-label">Номер машины</label>
                    <input type="text" class="form-control" id="car-number" name="car_number" required>
                </div>
                <div class="mb-3">
                    <label for="car-driver" class="form-label">Водитель</label>
                    <input type="text" class="form-control" id="car-driver" name="car_driver" required>
                </div>
                <div class="footer-modal"> 
                    <button type="submit" class="btn btn-success w-100" data-action="dispatchSpecification">Сохранить</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal - Cancel Dispatch -->
<div class="modal fade" id="cancelDispatchSpecificationModal" tabindex="-1" aria-labelledby="cancelDispatchSpecificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form id="formDispatchSpecification" class="content-modal p-3" method="post">  
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="cancelDispatchSpecificationModalLabel">Отмена отгрузки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal mt-4">
                <input type="hidden" name="action" value="forest_cancel_dispatch_dpecification">  
                <input type="hidden" name="id" value="">
                <input type="hidden" name="clients_id" value="<?= $_GET['id']; ?>">
                <input type="hidden" name="user_id" value="<?= get_current_user_id(); ?>">

                <div class="message text-center mb-4"> 
                    <p>Вы действительно хотите отменить отгрузку?</p>
                    <span></span>
                </div>
                <div class="footer-modal"> 
                    <button type="submit" class="btn btn-success w-100" data-action="cancelDispatchSpecification">Да, отменить</button>
                </div>
            </div> 
        </form>
    </div>
</div>


