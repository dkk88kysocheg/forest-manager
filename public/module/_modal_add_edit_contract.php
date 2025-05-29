<?php 
/**
 * Модальное окно
 * Добавление/Редактирование договоров
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

wp_enqueue_style( 'modal-add-edit-contract', plugins_url() . '/forest-manager/public/css/modal-add-edit-contract.css', array(), '1.0.0', 'all' );

// Подключаем скрипт
wp_enqueue_script( 
    'module-modal-add-edit-contract', 
    plugins_url() . '/forest-manager/public/js/modal-add-edit-contract.js', 
    array(), 
    get_filemtime( 'modal-add-edit-contract.js' ), 
    true
);

$get_data_company = Forest_Manager_General_Function::get_data( 'company' );
?> 

<!-- Modal -->
<div class="modal fade" id="addEditContractModal" tabindex="-1" aria-labelledby="addEditContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form id="formContract" class="content-modal p-3" method="post"> 
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="addEditContractModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal mt-4"> 
                <input type="hidden" name="action" value="forest_add_edit_contract">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="clients_id" value="<?= $_GET['id']; ?>">

                <div class="mb-3">
                    <label class="form-label">С кем договор</label>
                    <select class="form-select" name="company_id">
                        <?php foreach ($get_data_company as $company) { ?>
                            <option value="<?= $company['id']; ?>"><?= $company['name']; ?></option>
                        <?php } ?>
                    </select> 
                </div>
                <div class="mb-3">
                    <label class="form-label">Номер</label>
                    <input type="text" class="form-control" name="number" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Дата создания</label>
                    <input type="date" class="form-control" name="date_creation">
                </div>
                <button type="submit" class="w-100 btn btn-success" data-action="addEditContract">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addDeleteFileContractModal" tabindex="-1" aria-labelledby="addDeleteFileContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="content-modal p-3"> 
            <div class="header-modal d-flex justify-content-between">
                <h5 class="modal-title" id="addDeleteFileContractModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="body-modal mt-4"> 
                <input type="hidden" name="action" value="forest_add_delete_file_contract">
                <input type="hidden" name="id" value="">

                <div id="upload" class="block" style="display: none">
                    <label class="w-100 input-file">
                        <input type="file" name="file">
                        <span class="input-file-btn">Выберите файл</span>           
                        <i class="text-muted text-center small px-3 mt-3">Разрешается загружить документы только с расширением .pdf</i>
                    </label>
                </div>
                <div id="delete" class="block" style="display: none">
                    <div class="w-100 text-center">Вы уверены, что хотите удалить данный документ?</div>
                    <b class="name-file my-3"></b>
                    <span class="btn btn-success" data-action="addDeleteFileContract">Удалить</span> 
                </div>
                <div id="result" class="block" style="display: none"></div> 
            </div>  
        </div>
    </div>
</div>