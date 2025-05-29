<?php 
/**
 * Модальное окно
 * Добавление/Редактирование контактов
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
    'module-modal-add-edit-contact', 
    plugins_url() . '/forest-manager/public/js/modal-add-edit-contact.js', 
    array(), 
    get_filemtime( 'modal-add-edit-contact.js' ), 
    true
);

?> 

<!-- Modal -->
<div class="modal fade" id="addEditContactModal" tabindex="-1" aria-labelledby="addEditContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form id="formContact" class="content-modal p-3" method="post"> 
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="addEditContactModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal mt-4"> 
                <input type="hidden" name="action" value="forest_add_edit_contact">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="clients_id" value="<?= $get['id']; ?>">
                <input type="hidden" name="delete" value="">

                <div class="mb-3">
                    <label class="form-label">Имя</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Телефон</label>
                    <input type="text" class="form-control" name="phone">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" class="form-control" name="email">
                </div>
                <button type="submit" class="w-100 btn btn-success" data-action="addEditContact">Сохранить</button>
            </div>
        </form>
    </div>
</div>