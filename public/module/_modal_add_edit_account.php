<?php 
/**
 * Модальное окно
 * Добавление/Редактирование счёта
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
    'module-modal-add-edit-account', 
    plugins_url() . '/forest-manager/public/js/modal-add-edit-account.js', 
    array(), 
    get_filemtime( 'modal-add-edit-account.js' ), 
    true
);

?> 

<!-- Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formAccount" class="content-modal px-5 py-4" method="post"> 
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="addAccountModalLabel">Добавить счёт</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal my-4">
                <input type="hidden" name="action"     value="forest_add_account">
                <input type="hidden" name="clients_id" value="<?= $_GET['id']; ?>"> 
                <input type="hidden" name="user_id"    value="<?= get_current_user_id(); ?>">

                <div class="client mb-3">
                    <label class="form-label">Клиент</label>
                    <input type="text" class="form-control" value="<?= $client['name']; ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="cash-account" class="form-label">Расчёт</label>
                    <select class="form-select" id="cash-account" name="cash">
                        <option value="0">Безналичный</option>
                        <option value="1">Касса</option>
                        <option value="2">Н/Л</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="number-account" class="form-label">Номер</label>
                    <input type="text" class="form-control" id="number-account" name="number" required>
                </div>

                <div class="mb-3">
                    <label for="date-account" class="form-label">Дата оплаты</label>
                    <input type="date" class="form-control" id="date-account" name="date" required>  
                </div>

                <div class="mb-3">
                    <label for="amount-account" class="form-label">Сумма</label>
                    <input type="text" class="form-control" id="amount-account" name="amount" data-type="number" required>
                </div>
            </div>
            <div class="footer-modal d-flex justify-content-end"> 
                <button type="submit" class="btn btn-success" data-action="addAccount">Сохранить</button>
            </div>
        </form>
    </div>
</div>
