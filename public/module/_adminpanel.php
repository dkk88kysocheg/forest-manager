<?php 
/**
 * Настройки в личном кабинете пользователя
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

wp_enqueue_script( 'adminpanel', plugins_url() . '/forest-manager/public/js/adminpanel.js', array(), '1.0.0', true );

// загружаем сохранённые  данные
$avatar_default = get_template_directory_uri() . '/assets/images/avatar_default.png'; 
// Аватарка
$img = get_the_author_meta( 'profile_img', $user->ID );
$user_profile_img = ($img)?$img:$avatar_default;
// Подпись
$signature = get_the_author_meta( 'signature_img', $user->ID );
$user_signature_img = ($signature)?$signature:'';

$user_initials = get_the_author_meta( 'initials', $user->ID );   // Иницыалы
$user_post     = get_the_author_meta( 'post', $user->ID );       // Должность
$user_edit     = (get_the_author_meta( 'edit', $user->ID ))?1:0; // Права 

$user_pages  = get_the_author_meta( 'pages', $user->ID );
$array       = explode(',', $user_pages);
$array_pages = [0];
foreach ($array as $val) { $array_pages[] = (int) $val; }

$user_view       = (get_the_author_meta( 'view', $user->ID ))?1:0;
$user_view_list  = get_the_author_meta( 'view_list', $user->ID );
$array_view_list = ($user_view_list)?explode(',', $user_view_list):[];

$users = get_users(['role' => 'manager']);

// Список доступных страниц
$pages = Forest_Manager_General_Function::get_pages([ PAGE_ID__pdf, PAGE_ID__excel, PAGE_ID__print ]);
?> 

<h2>Дополнительные настройки</h2>
<table class="form-table">
	<tr id="profile-img">
		<th>
			<label>Аватарка1</label>
		</th>
		<td>
			<input type="text" name="profile_img" value="<?= $user_profile_img; ?>" readonly>
			<button type="button" id="add-profile-img">Добавить</button> 
		</td>
	</tr>
	<tr id="signature-img">
		<th>
			<label>Подпись</label>
		</th>
		<td>
			<input type="text" name="signature_img" value="<?= $user_signature_img; ?>" readonly>
			<button type="button" id="add-signature-img">Добавить</button> 
		</td>
	</tr>
	<tr id="post">
		<th>
			<label>Должность</label>
		</th>
		<td>
			<input type="text" value="<?= $user_post; ?>" name="post">
		</td>
	</tr>
	<tr id="pages">
		<th>
			<label>Доступные страницы</label>
		</th>
		<td>
			<?php 
				foreach ($pages as $page) {
					// Проверка
					$checked = false;

					if (array_search($page->ID, $array_pages)) $checked = true;

					// Вывод страниц
		            if ($page->ID !== 2) { 
		                echo '<div style="margin-bottom:.5rem">
		                	<label for="' . $page->post_name . '">
                                <input type="checkbox" id="' . $page->post_name . '" value="' . $page->ID . '" name="page" ' . (($checked)?'checked':'') . '> 
                                ' . $page->post_title . '
	                        </label>
	                    </div>';
	                }
	            } 
	        ?>
			<input type="hidden" value="" name="pages">
		</td>
	</tr>
	<tr id="edit"> 
		<th>
			<label>Права</label>
		</th>
		<td>
			<div style="margin-bottom:.5rem">
				<label for="edit-viewing">
	                <input type="radio" id="edit-viewing" value="0" name="edit" <?= (!+$user_edit)?'checked':''; ?>> 
	                Только просмотр
	            </label>
            </div>
            <div style="margin-bottom:.5rem">
	            <label for="edit-editing">
	                <input type="radio" id="edit-editing" value="1" name="edit" <?= (+$user_edit)?'checked':''; ?>> 
	                Редактирование
	            </label>
            </div>
		</td>
	</tr>
	<tr id="view">
		<th>
			<label>Отображение</label>
		</th>
		<td>
			<div style="margin-bottom:.5rem">
				<label for="view-all">
	                <input type="radio" id="view-all" value="0" name="view" <?= (!+$user_view)?'checked':''; ?>> 
	                Все
	            </label>
            </div>
            <div style="margin-bottom:.5rem">
	            <label for="view-selectively">
	                <input type="radio" id="view-selectively" value="1" name="view" <?= (+$user_view)?'checked':''; ?>>
	                Выборочно
	                <div id="view-list" style="margin-left:1rem; margin-top:.5rem; <?= (+$user_view)?'':'display:none;'; ?>">
	                	<?php 
	                		foreach ($users as $u) { 
								$id = uniqid();
								// Проверка
								$checked = false;
								foreach ($array_view_list as $active_user) { if (+$active_user === $u->ID) { $checked = true; }}
	                	?> 
			                <div style="margin-bottom:.5rem">
			                	<label for="<?= $id; ?>">
	                                <input type="checkbox" id="<?= $id; ?>" value="<?= $u->ID; ?>" name="user" <?= ($checked || $u->ID === $user->ID)?'checked':''; ?> <?= ($u->ID === $user->ID)?'disabled':''; ?>> 
	                                <?= $u->data->display_name; ?>
		                        </label>
		                    </div>
					    <?php } ?>
						<input type="hidden" value="" name="view_list">
	                </div>
	            </label>
            </div>
		</td>
	</tr>
</table>

