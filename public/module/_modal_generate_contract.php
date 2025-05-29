<?php 
/**
 * Модальное окно
 * Генерирование договоров
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
    'module-modal-generate-contract', 
    plugins_url() . '/forest-manager/public/js/modal-generate-contract.js', 
    array(), 
    get_filemtime( 'modal-generate-contract.js' ), 
    true
);

?>  

<!-- Modal -->
<div class="modal fade" id="generateContractModal" tabindex="-1" aria-labelledby="generateContractModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formContract" class="content-modal p-3" method="post"> 
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="generateContractModalLabel">Сгенерировать</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal mt-4"> 
                <input type="hidden" name="action" value="forest_generate_contract">
                <input type="hidden" name="contract_id" value=""> 

                <label class="form-label">Тип договора</label>
                <div class="form-check form-check-primary mb-2">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="type_contract" value="1" checked> Общий <i class="input-helper"></i>
                    </label>
                </div>
                <div class="form-check form-check-primary mb-2">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="type_contract" value="2"> Индивидуальный <i class="input-helper"></i>
                    </label>
                </div>

                <label class="form-label">Вид продукции</label>
                <div class="form-check form-check-primary mb-2">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="products" value="3"> Пеллеты <i class="input-helper"></i>
                    </label>
                </div>
                <div class="form-check form-check-primary mb-2">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="products" value="1" checked> Профилированная древесина <i class="input-helper"></i>
                    </label>
                </div>
                <div class="form-check form-check-primary mb-2">
                    <label class="form-check-label">
                        <input type="radio" class="form-check-input" name="products" value="2"> Пиломатериалы <i class="input-helper"></i>
                    </label>
                    <div id="okpd-list">
                        <ul class="d-none">
                            <?php foreach ($get_data_guide as $o) {  
                                if ($o['key'] === 'okpd') { ?> 
                                    <li data-id="<?= $o['id']; ?>" data-value="<?= $o['value']; ?>"></li>
                            <?php }} ?>
                        </ul>
                        <label class="form-label mt-2">ОКПД</label>  
                        <table class="table table-striped"> 
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm btn-icon-text px-2" data-action="add-row"><i class="icon-plus btn-icon-prepend m-0"></i></button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="d-flex align-items-center">
                    <button type="button" class="btn btn-success" data-action="generateContract">Сгенерировать</button>
                    <i class="w-100 message text-muted ps-3"></i>
                </div>
            </div>
        </form>
    </div>
</div>

