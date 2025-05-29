 <?php 
/**
 * Страница Создать/Редактировать клиента
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

/**
 * УЖЕ ПОЛУЧНО:
 * class-forest-manager-general-function.php
 * $version
 */

wp_enqueue_style( 'page-client', plugins_url() . '/forest-manager/public/css/page-client-add-edit.css', array(), '1.0.0', 'all' );
// Подключаем скрипт
wp_enqueue_script( 
    'module-page-client', 
    plugins_url() . '/forest-manager/public/js/page-client-add-edit.js', 
    array(), 
    get_filemtime( 'page-client-add-edit.js' ), 
    true
);

$get_data_guide = Forest_Manager_General_Function::get_data( 'guide', [], ['sorting' => 'ASC'] );

$data_client = [ 'user_id' => $user_id ]; 

$client_id = '';

if (isset($_GET['id'])) {
    $get_data_client = Forest_Manager_General_Function::get_data( 'clients', ['id' => $_GET['id']] );
    $data_client     = array_shift($get_data_client);

    $client_id = $_GET['id'];
}

?> 

<div class="row"> 
    <div id="data-client" class="col-12 mb-3">
        <div class="card">
            <div class="card-body">
                <form id="addEditClient">
                    <input type="hidden" name="action" value="forest_add_edit_client">
                    <input type="hidden" name="id" value="<?= $client_id; ?>">
                    <div class="row">
                        <div class="col-12 <?= ($user_id === 1)?'':'d-none'; ?>" >
                            <div id="user-client" class="form-block mb-3">
                                <label class="form-label">Менеджер</label>
                                <select class="form-select" name="user_id">
                                    <option value="1">Администратор</option>
                                    <?php
                                        $users = get_users( [ 'role' => 'manager' ] );
                                        foreach ($users as $u) {
                                            $selected = ((int)$data_client['user_id'] === (int)$u->ID)?'selected':'';
                                            echo '<option value="' . $u->ID . '" ' . $selected . '>' . $u->data->display_name . '</option>';
                                        }
                                    ?>
                                </select> 
                            </div>
                        </div>
                        <div id="data-general" class="col-3">
                            <div class="form-block mb-3">
                                <label class="form-label">Форма организации</label>
                                <select class="form-select" name="organization_form">
                                    <?php 
                                        foreach ($get_data_guide as $o) { 
                                            if ($o['key'] !== 'organization_form') continue;
                                            $selected = '';

                                            if (isset($_GET['id'])) {
                                                $selected = (isset($data_client['organization_form']) && (int)$data_client['organization_form'] === +$o['id'])?'selected':'';
                                                echo '<option value="' . $o['id'] . '" ' . $selected . ' >' . $o['value'] . '</option>';
                                            } else {
                                                if (
                                                    ($_GET['type'] === 'legal'    && (int)$o['id'] === 81) || 
                                                    ($_GET['type'] === 'physical' && (int)$o['id'] === 84)
                                                ) $selected = 'selected';
                                                echo '<option value="' . $o['id'] . '" ' . $selected . '>' . $o['value'] . '</option>';
                                            }
                                        }
                                    ?>
                                </select>
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">Название</label>
                                <input type="text" class="form-control" name="name" value="<?= (isset($data_client['name']))?$data_client['name']:''; ?>" required>
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">Телефон</label>
                                <input type="text" class="form-control" name="phone" value="<?= (isset($data_client['phone']))?$data_client['phone']:''; ?>">
                            </div>
                            <div class="form-block col-12 mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" name="email" value="<?= (isset($data_client['email']))?$data_client['email']:''; ?>">
                            </div>
                        </div>
                        <div id="data-decision-maker" class="col-3"> 
                            <div class="form-block mb-3">
                                <label class="form-label">Лицо принимающее решение</label>
                                <?php 
                                    $i = true;
                                    foreach ($get_data_guide as $d) {  
                                        if ($d['key'] !== 'decision_maker') continue;

                                        $checked = '';
                                        if (isset($data_client['decision_maker'])) {
                                            $checked = (+$data_client['decision_maker'] === +$d['id'])?'checked':'';
                                        } else {
                                            if ($i) {
                                                $checked = 'checked';
                                                $i = false;
                                            }
                                        }

                                        echo '<div class="form-check form-check-primary mb-2">' . 
                                                '<label class="form-check-label">' .
                                                    '<input type="radio" class="form-check-input" name="decision_maker" value="' . $d['id'] . '" ' . $checked . ' >' .
                                                    $d['value'] .
                                                    '<i class="input-helper"></i>' .
                                                '</label>';
                                        if ( +$d['id'] === 88 ) {
                                            echo '<input type="text" class="form-control mt-2" name="decision_maker_own" value="">';
                                        } 

                                        echo '</div>';
                                    }
                                ?>
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">ФИО <span class="small text-muted">(полностью, в род. падеже, Иванова Ивана Ивановича)</span></label>
                                <input type="text" class="form-control" name="director_name" value="<?= (isset($data_client['director_name']))?$data_client['director_name']:''; ?>"> 
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">ФИО <span class="small text-muted">(сокращённо, Иванов И.И.)</span></label> 
                                <input type="text" class="form-control" name="director_name_reduction" value="<?= (isset($data_client['director_name_reduction']))?$data_client['director_name_reduction']:''; ?>">
                            </div>
                        </div>
                        <div id="data-company" class="col-3">
                            <div class="form-block mb-3">
                                <label class="form-label">ИНН</label>
                                <input type="text" class="form-control" name="inn" value="<?= (isset($data_client['inn']))?$data_client['inn']:''; ?>">
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">КПП</label>
                                <input type="text" class="form-control" name="kpp" value="<?= (isset($data_client['kpp']))?$data_client['kpp']:''; ?>">
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">ОГРН</label>
                                <input type="text" class="form-control" name="ogrn" value="<?= (isset($data_client['ogrn']))?$data_client['ogrn']:''; ?>">
                            </div>
                        </div>
                        <div id="data-bank" class="col-3"> 
                            <div class="form-block mb-3">
                                <label class="form-label">Название банка</label>
                                <input type="text" class="form-control" name="bank_name" value="<?= (isset($data_client['bank_name']))?$data_client['bank_name']:''; ?>">
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">Расчётный счёт</label>
                                <input type="text" class="form-control" name="payment_account" value="<?= (isset($data_client['payment_account']))?$data_client['payment_account']:''; ?>">
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">Корреспонденский счёт</label>
                                <input type="text" class="form-control" name="correspondent_account" value="<?= (isset($data_client['correspondent_account']))?$data_client['correspondent_account']:''; ?>">
                            </div>
                            <div class="form-block mb-3">
                                <label class="form-label">БИК</label>
                                <input type="text" class="form-control" name="bik" value="<?= (isset($data_client['bik']))?$data_client['bik']:''; ?>">
                            </div>
                        </div>
                    </div>
                    <div id="data-address" class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Юридический адрес</label>
                                <textarea class="form-control" name="legal_address" rows="6"><?= (isset($data_client['legal_address']))?$data_client['legal_address']:''; ?></textarea> 
                            </div>
                            <div class="mb-3 form-check form-check-flat form-check-primary">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="matching_address" value="1" <?= (isset($data_client['matching_address']) && $data_client['matching_address'])?'checked':''; ?> > Юридический адресс совпадает с почтовым<i class="input-helper"></i>
                                </label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Почтовый адрес</label>
                                <textarea class="form-control" name="postal_address" rows="6"><?= (isset($data_client['postal_address']))?$data_client['postal_address']:''; ?></textarea> 
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-success" data-action="addEditClient">Сохранить</button> 
                            <i class="w-100 message text-muted ps-3"></i> 
                            <a href="<?= get_the_permalink(wp_get_post_parent_id()) . '?id=' . $client_id; ?> " class="btn btn-link">Перейти на страницу клиента</a> 
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if (isset($_GET['id'])) { ?>
        <div id="contracts" class="col-12 col-xxl-6">
            <div class="card">
                <div class="card-body">
                    <div class="card-top d-sm-flex align-items-center mb-3"> 
                        <h4 class="card-title mb-sm-0">Договор</h4>
                        <div class="card-button ms-auto mb-3 mb-sm-0">
                            <button type="button" class="btn btn-primary btn-icon btn-sm" data-open-modal="addEditContractModal" data-action="add"><i class="icon-plus btn-icon-prepend"></i></button>
                        </div>
                    </div>
                    <table id="list-contract" class="table table-striped">
                        <thead>
                            <tr>
                                <th class="text-muted text-center">#</th>
                                <th class="number-contract text-muted">Номер</th>
                                <th class="date-contract text-muted">Дата окончания</th>
                                <th class="file-contract text-muted">Файл</th>
                                <th class="button"></th> 
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div id="contacts" class="col-12 col-xxl-6">
            <div class="card">
                <div class="card-body">
                    <div class="card-top d-sm-flex align-items-center mb-3"> 
                        <h4 class="card-title mb-sm-0">Контактное лицо</h4>
                        <div class="card-button ms-auto mb-3 mb-sm-0">
                            <button type="button" class="btn btn-primary btn-icon btn-sm" data-open-modal="addEditContactModal" data-action="add"><i class="icon-plus btn-icon-prepend"></i></button>
                        </div>
                    </div>
                    <table id="list-contact" class="table table-striped">
                        <thead>
                            <tr>
                                <th class="text-muted text-center">#</th>
                                <th class="name-contact text-muted">Имя</th>
                                <th class="phone-contact text-muted">Телефон</th>
                                <th class="email-contact text-muted">E-mail</th>
                                <th class="button"></th> 
                            </tr>
                        </thead> 
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php 
        require_once plugin_dir_path( __FILE__ ) . '/_modal_add_edit_contact.php';
        require_once plugin_dir_path( __FILE__ ) . '/_modal_add_edit_contract.php';
        require_once plugin_dir_path( __FILE__ ) . '/_modal_generate_contract.php'; 
    } ?>
</div>
