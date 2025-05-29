<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/admin
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-forest-manager-general-function.php';

class Forest_Manager_Admin {
	
	private $forest_manager;
	
	private $version;
	
	public function __construct( $forest_manager, $version ) {
		$this->forest_manager = $forest_manager;
		$this->version = $version;
	}
	
	public function enqueue_styles() {
		wp_enqueue_style( $this->forest_manager, plugin_dir_url( __FILE__ ) . 'css/forest-manager-admin.css', array(), $this->version, 'all' );
	}
	
	public function enqueue_scripts() {
		wp_enqueue_script( $this->forest_manager, plugin_dir_url( __FILE__ ) . 'js/forest-manager-admin.js', array( 'jquery' ), $this->version, false );
	}

	public function additional_settings() {
        add_menu_page('Справочник', 'Справочник', 'administrator', 'guide_page', array( $this, 'guide_page' ), 'dashicons-admin-site' );
        add_menu_page('Обновление плагина', 'Обновление плагина', 'administrator', 'plugin_update', array( $this, 'plugin_update' ), 'dashicons-update' );
    }

    public function forest_add_edit_guide() {
    	// Если key и value пустое то показываем ошибку
    	if(empty($_POST['key']) || empty($_POST['value'])) {
    		// Отправляем ошибку
			echo wp_send_json([
				'success' => false,
				'code'    => 'error_data',
				'message' => 'key или value незаполнены'
			]);
			wp_die();
    	}

    	if (empty($_POST['id'])) { // Если id не указан - просто создаём новую запись
    		// Создаём справочник
			if ( !Forest_Manager_General_Function::db_operations('insert', 'guide', [
				'key'     => $_POST['key'],
				'value'   => $_POST['value'],
				'sorting' => (!empty($_POST['sorting']))?$_POST['sorting']:1,
			]) ) { 
				// Отправляем ошибку
				echo wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка записи в базу данных (создание guide)'
				]);
				wp_die();
			}
    	} else { // Если id указан - проверяем есть ли такая запись
    		$get_data_guide = Forest_Manager_General_Function::get_data('guide', ['id' => $_POST['id']]);
    		if (empty($get_data_guide)) {
    			// Создаём справочник
				if ( !Forest_Manager_General_Function::db_operations('insert', 'guide', [
					'id'      => $_POST['id'],
					'key'     => $_POST['key'],
					'value'   => $_POST['value'],
					'sorting' => (!empty($_POST['sorting']))?$_POST['sorting']:1,
				]) ) { 
					// Отправляем ошибку
					echo wp_send_json([
						'success' => false,
						'code'    => 'error_writing_database',
						'message' => 'Ошибка записи в базу данных (создание guide)'
					]);
					wp_die();
				}
    		} else {
    			// Редактируем справочник
				if ( !Forest_Manager_General_Function::db_operations('update', 'guide', [
					'key'     => $_POST['key'],
					'value'   => $_POST['value'],
					'sorting' => (!empty($_POST['sorting']))?$_POST['sorting']:1,
				], [
					'id'      => $_POST['id']
				]) ) { 
					// Отправляем ошибку
					echo wp_send_json([
						'success' => false,
						'code'    => 'error_writing_database',
						'message' => 'Ошибка записи в базу данных (редактирование guide)'
					]);
					wp_die();
				}
    		}
    	}
    	// Отправляем ответ
		echo wp_send_json([
			'success'    => true,
			'code'       => 'add_edit_guide',
		]);
		wp_die(); 
    }

    public function forest_delete_guide() {
    	// Удалить запись
		if ( !Forest_Manager_General_Function::db_operations('delete', 'guide', [
			'id' => $_POST['id'],
		]) ) { 
			// Отправляем ошибку
			echo wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка удаление из базы данных (guide)'
			]);
			wp_die();
		}

		// Отправляем ответ
		echo wp_send_json([
			'success'    => true,
			'code'       => 'delete_guide',
		]);
		wp_die(); 
    }

    public function guide_page() { 
    	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    	$sorting = Forest_Manager_General_Function::get_url_query($url, $key = 'sorting');

    	if ($sorting === 'key') { 
    		$get_data_guide = Forest_Manager_General_Function::get_data('guide', [], ['key' => 'ASC', 'value' => 'ASC']);
    	} else {
    		$get_data_guide = Forest_Manager_General_Function::get_data('guide');
    	}
    ?>
    	<style type="text/css">
    		#guide_page button {cursor: pointer;}
			#guide_page table tr td {
			    padding: 0.25rem 1rem;
			    margin: 0;
			    border-bottom: 1px solid;
			}
			#guide_page table tr td.btn { position: relative; }
			#guide_page table tr td.btn .delete {
				display:none;
			    position: absolute;
			    left: 105%;
			    top: 0;
			    width: 100px;
			    text-align: center;
			    border: 1px solid #767676;
			    border-radius: 0.25rem;
			    padding: 0.25rem;
			    -webkit-box-shadow: 4px 4px 8px 0px rgba(34, 60, 80, 0.2);
			    -moz-box-shadow: 4px 4px 8px 0px rgba(34, 60, 80, 0.2);
			    box-shadow: 4px 4px 8px 0px rgba(34, 60, 80, 0.2);
			}
			#guide_page table tr td.btn .delete.show { display:block; }
			#guide_page table tr td.btn .delete button {
			    color: #fff;
			    background-color: #d52a2a;
			    border: none;
			    border-radius: 0.25rem;
			    padding-bottom: 0.25rem;
			} 
			#guide_page .row { display: flex; }
			#guide_page .row .w-50 { width: 50%; }
			#guide_page .row .form form {
			    position: fixed;
			    width: 300px;
			    padding: 1rem; 
			    border: 1px solid #8c8f94;
			}
			#guide_page .row .form .form-control {
			    width: 100%;
			    display: flex;
			    justify-content: space-between;
			    margin-bottom: 0.5rem;
			}
			#guide_page .row .form .form-control label { width: 20%; }
			#guide_page .row .form .form-control input { width: 80%; }
			#guide_page .row .form .form-error div {
			    text-align: center;
			    font-style: italic;
			    color: #d10000;
			    margin-bottom: 0.5rem;
			}
			#guide_page .row .form button {
				width: 100%;
				padding: 0.5rem;
			}
			#guide_page .row .form button.off {
				border: none;
				color: #c50c0c;
			}
			#guide_page .row .form .hint {
				font-size: smaller;
				margin-top: 1rem;
				text-align: center;
			}
    	</style>
    	<div class="wrap" id="guide_page">
    		<h1>Справочник</h1>
    		<div class="row">
    			<div class="table w-50">
    				<table>
		    			<thead>
		    				<tr>
		    					<th>
		    						<form>
		    							<input type="hidden" name="page" value="guide_page">
		    							<button type="submit">ID</button>
		    						</form>
		    					</th>
		    					<th>
		    						<form>
		    							<input type="hidden" name="page" value="guide_page">
		    							<input type="hidden" name="sorting" value="key">
		    							<button type="submit">key</button>
		    						</form>
			    				</th>
		    					<th>value</th>
		    					<th>sorting</th>
		    					<th>button</th>
		    				</tr>
		    			</thead>
		    			<tbody>
		    				<?php foreach ($get_data_guide as $item) { ?>
		    					<tr class="item">
		    						<td class="id"><?= $item['id']; ?></td>
		    						<td class="key"><?= $item['key']; ?></td>
		    						<td class="value"><?= $item['value']; ?></td>
		    						<td class="sorting"><?= $item['sorting']; ?></td>
		    						<td class="btn">
		    							<button type="button" data-action="edit"><div class="dashicons dashicons-welcome-write-blog"></div></button>
		    							<button type="button" data-open="delete"><div class="dashicons dashicons-trash"></div></button> 
		    							<div class="delete">
		    								<div>Вы уверены?</div>
		    								<button type="button" data-action="delete">Да, удалить!</button> 
		    							</div>
		    						</td>
		    					</tr>
		    				<?php } ?>
		    			</tbody>
		    		</table>
    			</div>
    			<div class="form w-50">
    				<form id="add_edit_guide">
    					<input type="hidden" name="action" value="forest_add_edit_guide">
    					<div class="form-control">
    						<label>ID</label>
    						<input type="text" name="id">
    					</div>
    					<div class="form-control">
    						<label>key</label>
    						<input type="text" name="key">
    					</div>
    					<div class="form-control">
    						<label>value</label>
    						<input type="text" name="value">
    					</div>
    					<div class="form-control">
    						<label>sorting</label>
    						<input type="text" name="sorting">
    					</div>
    					<div class="form-error"></div>  
    					<button type="button" data-action="add_edit_guide">Сохранить</button>
    					<button type="button" class="off" data-action="throw_off">Сбросить</button>
    					<div class="hint">
    						Если указан ID, то Вы отредактируете эту строку.
    						Если указанного ID нет в системе или ID не указан, то система создаст новую запись.
    					</div>
    				</form>
    			</div>
    		</div>
    	</div>
    	<script type="text/javascript">
    		const $ = jQuery 
    		// Редактировать
    		$('#guide_page').on('click', 'button[data-action="edit"]', function() {
    			const item = $(this).parents('.item'),
    				  form = $('#add_edit_guide')

    			form.find('input[name="id"]').val( item.find('td.id').text() )
    			form.find('input[name="key"]').val( item.find('td.key').text() )
    			form.find('input[name="value"]').val( item.find('td.value').text() )
    			form.find('input[name="sorting"]').val( item.find('td.sorting').text() )
    		})

    		// Удалить
    		// Показываем окошко подтверждения
    		$('#guide_page').on('click', 'button[data-open="delete"]', function() {
    			const item = $(this).parents('.item')
    			item.find('.btn .delete').addClass('show')
    		})
    		// Скрываем окошко подтверждения
			$('#guide_page').on('mouseup', function(e){
				let div = $('.delete.show')
				if ( !div.is(e.target)
				    && div.has(e.target).length === 0 ) { 
					div.removeClass('show')
				}
			})
			// Удалить запись
			$('#guide_page').on('click', 'button[data-action="delete"]', function() {
    			const item = $(this).parents('.item')
    			$.post(
    				'/wp-admin/admin-post.php', 
    				{
    					action: 'forest_delete_guide',
    					id: item.find('td.id').text(),
    				},
    				function(d){
    					console.log(d)
    					if ( d.success ) {
    						location.reload()
    					}
				})
    		})

			// ФОРМА
			// Сохранить форму
			$('#add_edit_guide').on('click', 'button[data-action="add_edit_guide"]', function() {
    			const form = $('#add_edit_guide')
    			$.post(
    				'/wp-admin/admin-post.php', 
    				form.serialize(),
    				function(d){
    					console.log(d)
    					if ( d.success ) {
    						location.reload()
    					} else {
    						form.find('.form-error').empty()
    						form.find('.form-error').append($( '<div>' + d.message + '</div>' ))
    					}
				})
    		})
    		// Сбросить форму
    		$('#add_edit_guide').on('click', 'button[data-action="throw_off"]', function() {
    			const form = $('#add_edit_guide')

    			form.find('input[name="id"]').val('')
    			form.find('input[name="key"]').val('')
    			form.find('input[name="value"]').val('')
    			form.find('input[name="sorting"]').val('')
    		})
    	</script>
    <?php }

    public function plugin_update() {
	?>
		<style type="text/css">
			#plugin_update .done p {
			    text-decoration: line-through;
			}
		</style>
		<div class="wrap" id="plugin_update">
    		<h1>Обновление плагина</h1>
			<div class="none">
				<p>Необходимо обновить плагин</p>
				<button data-action="update_forest_manager">Обновить</button>
				<div class="form-error"></div>
				<hr>
			</div>

    	</div>
    	<script type="text/javascript">
    		const $ = jQuery 
    		// Обновить плагин
			$('#plugin_update').on('click', 'button', function() {
				const item = $(this).parent();

				$(this).prop('disabled', true);
    			$.post(
    				'/wp-admin/admin-post.php', 
    				{ action: $(this).data('action') },
    				function(d){
    					console.log(d)
    					if ( d.success ) {
    						location.reload()
    					} else {
    						item.find('.form-error').empty()
    						item.find('.form-error').append($( '<div>' + d.message + '</div>' ))
    					}
				})
    		})
    	</script>
	<?php } 


	public function update_forest_manager() {
		global $wpdb;

		$wpdb->query( "ALTER TABLE `forest_specification` ADD `cash`        INT(1) NOT NULL DEFAULT 0 AFTER `user_id`;" );
		$wpdb->query( "ALTER TABLE `forest_specification` ADD `contract_id` INT(11) NULL AFTER `cash`;" );
		$wpdb->query( "ALTER TABLE `forest_specification` ADD `additional`  VARCHAR(255) NULL AFTER `contract_id`;" );
		$wpdb->query( "ALTER TABLE `forest_specification` ADD `address_delivery`  VARCHAR(255) NULL AFTER `count_delivery`;" );

		$wpdb->query( "UPDATE `forest_specification` AS specification
						SET cash = (SELECT cash FROM `forest_account` AS account WHERE account.id = specification.account_id)
						WHERE EXISTS (SELECT cash FROM `forest_account` AS account WHERE account.id = specification.account_id);" );
		$wpdb->query( "UPDATE `forest_specification` AS specification
						SET contract_id = (SELECT contract_id FROM `forest_account` AS account WHERE account.id = specification.account_id)
						WHERE EXISTS (SELECT contract_id FROM `forest_account` AS account WHERE account.id = specification.account_id);" );


		// Успех
		wp_send_json([ 
			'success' => true
		]);
	}

}

// file_put_contents('api-log.txt',var_export($request,true).PHP_EOL,FILE_APPEND);


