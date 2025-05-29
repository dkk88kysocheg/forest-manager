<?php 
/**
 * Модальное окно
 * Удалить счёт/спецификацию
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
    'module-modal-deleted-account-specification', 
    plugins_url() . '/forest-manager/public/js/modal-deleted-account-specification.js', 
    array(), 
    get_filemtime( 'modal-deleted-account-specification.js' ), 
    true
);

?> 

<!-- Modal -->
<div class="modal fade" id="deletedAccountSpecificationModal" tabindex="-1" aria-labelledby="deletedAccountSpecificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formDeletedAccountSpecification" class="content-modal p-3" method="post"> 
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="deletedAccountSpecificationModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal my-4">
                <input type="hidden" name="action" value="">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="user_id" value="<?= get_current_user_id(); ?>">
                
                <div class="message text-center"> 
                    <p>Вы действительно хотите удалить?</p>
                    <span></span>
                </div>
            </div>
            <div class="footer-modal d-flex justify-content-center mb-5"> 
                <button type="submit" class="btn btn-success" data-action="deletedAccountSpecification">Удалить</button>
            </div>
        </form>
    </div>
</div>