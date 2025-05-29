<?php

/**
 * The public-facing functionality of the plugin.  
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-forest-manager-general-function.php';

class Forest_Manager_Public {
	private $forest_manager;
	
	private $version;
	
	public function __construct( $forest_manager, $version ) {
		$this->forest_manager = $forest_manager;
		$this->version = $version;
	}

	public function run_widget() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-content-widget.php';
		register_widget( 'Forest_Manager_Content_Widget' );
		register_widget( 'Forest_Manager_Menu_Widget' );
		register_widget( 'Forest_Manager_User_Widget' );
		register_widget( 'Forest_Manager_Pdf_Widget' );
		register_widget( 'Forest_Manager_Excel_Widget' );
	}
	
	public function enqueue_styles() {}
	
	public function enqueue_scripts() {
		wp_enqueue_script( 'module-public', plugin_dir_url( __FILE__ ) . 'js/module-public.js', array( 'jquery' ), $this->version, false );
	}

	// КЛИЕНТЫ  ========================================================= //
	// Получить клиентов 
	public function forest_get_clients() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$data = [];
		if (!empty($_GET['hide'])) { $data['hide'] = $_GET['hide']; }
		if (!empty($_GET['id'])) { $data['id'] = $_GET['id']; }
		if (!empty($_GET['inn'])) { $data['inn'] = $_GET['inn']; }
		if (!empty($_GET['view_list'])) { $data['view_list'] = $_GET['view_list']; }

		$list_clients = Forest_Manager_General_Function::get_data__clients( $data );

		foreach ($list_clients as $key => $value) {
			if (!empty($_GET['organization_form'])) {
				if ($_GET['organization_form'] === 'legal-entity') {
					if (+$value['organization_form'] === 84) {
						unset($list_clients[$key]);
					} else {
						$list_clients[$key]['list_contract'] = Forest_Manager_General_Function::get_data('contract', [ 'clients_id' => $value['id']] );
						$list_clients[$key]['list_contact'] = Forest_Manager_General_Function::get_data('contact', [ 'clients_id' => $value['id']] ); 
					}
				}
				if ($_GET['organization_form'] === 'physical-entity') {
					if (+$value['organization_form'] !== 84) {
						unset($list_clients[$key]);
					}
				}
			} else {
				if (+$value['organization_form'] !== 84) { 
					$list_clients[$key]['list_contract'] = Forest_Manager_General_Function::get_data('contract', [ 'clients_id' => $value['id']] );
					$list_clients[$key]['list_contact'] = Forest_Manager_General_Function::get_data('contact', [ 'clients_id' => $value['id']] ); 
				}
			}
		}

		// Сортируем
		usort($list_clients, function($a, $b) { return $b['date_change'] <=> $a['date_change']; });

		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'get_clients',
			'data'    => $list_clients,
		]);
	}
	// Добавить/Редактировать клиента
	public function forest_add_edit_client() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		$code = ((int)$_POST['id'])?'edit_client':'add_client';
		$data = $_POST;
		unset($data['id']);
		unset($data['action']);

		if (isset($_POST['matching_address'])) $data['matching_address'] = (bool) $_POST['matching_address']; 

		if ((int)$_POST['id']) {
			// Обновляем клиента
			if ( !Forest_Manager_General_Function::db_operations('update', 'clients', $data, [ 'id' => $_POST['id'] ]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка записи в базу данных (обновление forest_clients)'
				]);
			}
			$id = $_POST['id'];
		} else {
			// Добавляем клиента
			if ( !$id = Forest_Manager_General_Function::db_operations('insert', 'clients', $data) ) {
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка записи в базу данных (создание forest_clients)' 
				]);
			}
		}

		// Отправляем ответ 
		wp_send_json([ 
			'success' => true,
			'code'    => $code,
			'id'      => $id,
		]);
	}
	// Скрыть клиента
	public function forest_hide_client() {}

	// Получить список договоров
	public function forest_get_list_contract_and_contact() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$contracts_list = Forest_Manager_General_Function::get_data('contract', [ 'clients_id' => $_GET['clients_id']] );
		$contacts_list = Forest_Manager_General_Function::get_data('contact', [ 'clients_id' => $_GET['clients_id']] );

		// Отправляем ответ
		wp_send_json([
			'success'        => true,
			'code'           => 'get_list_contract_and_contact',
			'contracts_list' => $contracts_list,
			'contacts_list'  => $contacts_list, 
		]);
	}

	// Список контактов 
	public function forest_get_list_contact() {
		$get_data_contact = Forest_Manager_General_Function::get_data( 'contact', [ 'clients_id' => $_GET['clients_id'] ] );
		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'get_list_contact',
			'data'    => $get_data_contact,
		]);
	}
	// Добавить/Редактировать контакт
	public function forest_add_edit_contact() {
		// Добавить
		if (!$_POST['id']) {
			if ( !$account_id = Forest_Manager_General_Function::db_operations('insert', 'contact', [
				'name'       => $_POST['name'],
				'phone'      => $_POST['phone'],
				'email'      => $_POST['email'],
				'clients_id' => $_POST['clients_id'],
			]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка записи в базу данных (contact)'
				]);
			}

			// Успех
			wp_send_json([
				'success' => true,
				'code'    => 'add_contact',
			]);
		}

		// Удалить
		if ($_POST['delete']) {
			if ( !Forest_Manager_General_Function::db_operations('delete', 'contact', [
				'id' => $_POST['id'],
			]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка при удалени из базы данных (contact)'
				]);
			}

			// Успех
			wp_send_json([
				'success' => true,
				'code'    => 'delete_contact',
			]);
		}

		// Редактировать
		if ($_POST['id']) {
			if ( !Forest_Manager_General_Function::db_operations('update', 'contact', [ 
				'name'       => $_POST['name'],
				'phone'      => $_POST['phone'],
				'email'      => $_POST['email'],
			], [
				'id'         => $_POST['id'],
			]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка при редактирование в базе данных (contact)'
				]);
			}

			// Успех
			wp_send_json([
				'success' => true,
				'code'    => 'edit_contact',
			]);
		}

		// Отправляем ошибку
		wp_send_json([
			'success' => false,
			'code'    => 'incorrect_data',
			'message' => 'Неверные данные'
		]);
	}

	// Список договоров 
	public function forest_get_list_contract() {
		$get_data_contract = Forest_Manager_General_Function::get_data( 'contract', [ 'clients_id' => $_GET['clients_id'] ] );
		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'get_list_contract',
			'data'    => $get_data_contract,
		]);
	}
	// Добавить/Редактировать договор
	public function forest_add_edit_contract() {
		// Добавить
		if (!$_POST['id']) {
			if ( !$account_id = Forest_Manager_General_Function::db_operations('insert', 'contract', [
				'clients_id'      => $_POST['clients_id'],
				'company_id'      => $_POST['company_id'],
				'number'          => $_POST['number'],
				'days'            => null,
				'date_creation'   => strtotime( $_POST['date_creation'] ),
				'date_completion' => strtotime( date("31.12.Y") ),
			]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка записи в базу данных (contract)'
				]);
			}

			// Успех
			wp_send_json([
				'success' => true,
				'code'    => 'add_contract',
			]);
		}

		// Редактировать
		if ($_POST['id']) {
			if ( !Forest_Manager_General_Function::db_operations('update', 'contract', [ 
				'clients_id'      => $_POST['clients_id'],
				'company_id'      => $_POST['company_id'],
				'number'          => $_POST['number'],
				'days'            => null,
				'date_creation'   => strtotime($_POST['date_creation']),
				'date_completion' => strtotime( date("31.12.Y") ),
			], [
				'id' => $_POST['id'],
			]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка при редактирование в базе данных (contract)'
				]);
			}

			// Успех
			wp_send_json([
				'success' => true,
				'code'    => 'edit_contract',
			]);
		}

		// Отправляем ошибку
		wp_send_json([
			'success' => false,
			'code'    => 'incorrect_data',
			'message' => 'Неверные данные'
		]);
	}

	// Проверить/Удалить договор
	// public function forest_check_delete_contract() {}

	// Сгенерировать договор
	public function forest_generate_contract() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-forest-manager-contract.php';

		$get_data_contract = Forest_Manager_General_Function::get_data( 'contract', ['id' => $_POST['contract_id']] ); 
        $data_contract     = array_shift($get_data_contract); // Договор

		$file = Forest_Manager_Contract::generate_contract( $_POST, $data_contract );

		if ( !Forest_Manager_General_Function::db_operations('update', 'contract', [ 
			'file'        => $file, 
			'individual'  => ((int)$_POST['type_contract'] === 2), 
			'date_update' => strtotime("now")
		], [ 
			'id' => $_POST['contract_id']
		]) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка при редактирование в базе данных (contract)' 
			]);
		}

		file_put_contents('a-content.txt',var_export($file,true).PHP_EOL,FILE_APPEND);

		// Успех
		wp_send_json([
			'success' => true,
			'file'    => $file,
		]);
	}  

	// Загрузить/Удалить PDF договор
	public function forest_add_delete_file_contract() {
		$get_data_contract = Forest_Manager_General_Function::get_data( 'contract', [ 'id' => $_POST['id'] ] );
		$data_contract     = array_shift($get_data_contract);

		// Загрузка документа
		if ($_POST['name']) {
			$dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/contract/' . $data_contract['id'];
			if (!is_dir($dir)) { mkdir($dir, 0777, True); } // Создаем каталог если его нет

			$file     = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/uploads/' . $_POST['name'];
			$new_file = $dir . '/' . str_replace(' ', '_', $data_contract['number']) . '.pdf';
			copy($file, $new_file); // делаем копию
			unlink($file); // удаляем оригинал

			if ( !Forest_Manager_General_Function::db_operations('update', 'contract', [ 
				'file' => '/wp-content/contract/' . $data_contract['id'] . '/' . str_replace(' ', '_', $data_contract['number']) . '.pdf',
			], [
				'id'   => $data_contract['id'],
			]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка при редактирование в базе данных (contract)'
				]);
			}

			// Успех
			wp_send_json([
				'success' => true,
				'code'    => 'add_file_contract',
			]);
		}

		// Удаление документа
		if (!$_POST['name']) {
			unlink($_SERVER['DOCUMENT_ROOT'] . $data_contract['file']); // удаляем файл

			if ( !Forest_Manager_General_Function::db_operations('update', 'contract', [ 
				'file' => null,
			], [
				'id'   => $data_contract['id'],
			]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка при редактирование в базе данных (contract)'
				]);
			}

			// Успех
			wp_send_json([
				'success' => true,
				'code'    => 'delete_file_contract',
			]);
		}

		// Отправляем ошибку
		wp_send_json([
			'success' => false,
			'code'    => 'incorrect_data',
			'message' => 'Неверные данные'
		]);
	}

	// КОММЕНТАРИИ  ========================================================= //
	// Получить список
	public function forest_get_messages() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$data = Forest_Manager_General_Function::get_data__messages_full( $_GET );
		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'get_messages',
			'data'    => $data, 
		]);
	} 
	// Добавить
	public function forest_add_edit_message() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		$guide = Forest_Manager_General_Function::get_data('guide', [ 'value' => $_POST['type_message'] ] );
		$data = [
			'text'          => Forest_Manager_General_Function::text_formatting($_POST['text']),
			'clients_id'    => $_POST['clients_id'],
			'guide_id'      => array_shift($guide)['id'],
			'user_id'       => $_POST['user_id'],
		];
		// Если есть ID редактируем сообщение
		// Если нет ID сознаём новое сообщение
		if ($_POST['id']) {
			if ( !Forest_Manager_General_Function::db_operations('update', 'messages', $data, ['id' => $_POST['id']]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка обновления базы данных'
				]);
			}
		} else {
			$data['date_creation'] = strtotime( date("Y-m-d H:i:s") );

			if ( !Forest_Manager_General_Function::db_operations('insert', 'messages', $data) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка записи в базу данных'
				]);
			}
		}

		// Отправляем ответ
		wp_send_json([
			'success'    => true,
			'code'       => 'add_edit_messages',
		]);
	}
	// Удалить сообщение
	public function forest_delete_message() { 
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		if ( !Forest_Manager_General_Function::db_operations('delete', 'messages', [ 'id' => $_POST['id'], ]) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (messages)'
			]);
		}

		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'delete_messages',
		]);
	}

	// СЧЁТ  ========================================================= //
	// Получить список счетов
	public function forest_get_list_account() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$data = [ 
			'deleted'    => '0',
			'clients_id' => $_GET['clients_id'],
		];
		// Если указан период
		if ( isset($_GET['period']) ) { 
			$data['period'] = $_GET['period'];
			if ( $_GET['period'] === 'period' ) { 
				$data['date_from'] = $_GET['date_from'];
				$data['date_to'] = $_GET['date_to'];
		}}
		// Если не указан cash = all
		if ( isset($_GET['cash']) && $_GET['cash'] !== 'all' ) { 
			$data['cash'] = $_GET['cash'];
		}
		// Если не указан payment = all
		if ( isset($_GET['payment']) && $_GET['payment'] !== 'all' ) { 
			$data['payment'] = $_GET['payment']; 
		}
		// Список пользователей которые можно просматривать
		if (!empty($_GET['view_list'])) { $data['view_list'] = $_GET['view_list']; }

		$data_answer = Forest_Manager_General_Function::get_data__account_full( $data ); 

		// Отправляем ответ
		wp_send_json([
			'success'    => true,
			'code'       => 'get_list_account',
			'data'       => $data_answer,
		]);
	}
	// Получить данные по счёту
	public function forest_get_account() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$get_data_clients =  Forest_Manager_General_Function::get_data( 'clients', [ 'id' => $_GET['clients_id'] ] );
		$client_data =  array_shift( $get_data_clients );

		$account_data  = [];
		$payment_list  = [];
		$contract_list = [];

		// Если есть ID счёта
		if ( isset($_GET['account_id']) ) { 
			$get_data_account = Forest_Manager_General_Function::get_data( 'account', [ 'id' => $_GET['account_id'] ] ); 
			$account_data     = array_shift( $get_data_account );
		}
		// Если клиент юридическое лицо
		if ( isset($client_data['company']) ) {
			$contract_list = Forest_Manager_General_Function::get_data( 'contract', [ 'clients_id' => $client_data['id'] ] );
		}

		// Отправляем ответ
		wp_send_json([
			'success'       => true,
			'code'          => 'get_account', 
			'payment_list'  => $payment_list, 
			'contract_list' => $contract_list, 
			'account_data'  => $account_data, 
			'client_data'   => $client_data, 
		]);
	}
	// Добавить
	public function forest_add_account() {
		global $wpdb;

		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		$clients_id = $_POST['clients_id'];

		$prefix = preg_replace('/[0-9]+/', '', $_POST['number']);
		$number = '';
		if ($prefix === 'СЧ') {
			$matches = Forest_Manager_General_Function::get_list__matches('account', $_POST['number']);
			$number  = $_POST['number'] . '-' . (count($matches) + 1);
		}

		// Новая запись
		if ( !$wpdb->insert( 'forest_account', [
			'clients_id'    => $clients_id,
			'number'        => $number,
			'date_creation' => strtotime($_POST['date']),
			'amount'        => $_POST['amount'],
			'cash'          => +$_POST['cash']?:0,
			'user_id'       => $_POST['user_id'],
		]) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (account)'
			]);
		}
		$account_id = $wpdb->insert_id;

		// Обновляем данные у клиента
		$amount = +$_POST['amount'];
		$query  = $wpdb->prepare( "UPDATE `forest_clients` SET debet = debet + %f, date_change = %d  WHERE id = %d;", $amount, strtotime("now"), $clients_id ); 

		if ( !$wpdb->query( $query ) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (clients)'
			]);
		}

		// Отправляем ответ
		wp_send_json([
			'success'    => true,
			'code'       => 'add_account',
			'account_id' => $account_id,
			'clients_id' => $clients_id
		]);
	}
	// Удаление
	public function forest_deleted_account() {
		global $wpdb;

		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		$query_account = $wpdb->prepare( "SELECT * FROM `forest_account` WHERE `id` = %d", $_POST['id'] ); 
		$data_account  = $wpdb->get_row( $query_account, ARRAY_A );

		// ПРОВЕРКА
		if (empty($data_account)) {
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_deleted_account',
				'message' => 'Счёт отсутствует',
			]);
		} 

		// Обновляем данные у клиента
		$amount = +$data_account['amount'];
		$query  = $wpdb->prepare( "UPDATE `forest_clients` SET debet = debet - %f, date_change = %d  WHERE id = %d;", $amount, strtotime("now"), $data_account['clients_id'] ); 

		if ( !$wpdb->query( $query ) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (clients)'
			]);
		}

		// Удаляем счёт
		if ( !$wpdb->update( 'forest_account', 
			[ 'deleted' => 1 ], 
			[ 'id' => $data_account['id'] ]
		)) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (account)'
			]);
		}

		
		// Отправляем ответ
		wp_send_json([
			'success'    => true,
			'code'       => 'deleted_account',
			'account_id' => $data_account['id'],
			'clients_id' => $data_account['clients_id'],
		]);
	}

	// СПЕЦИФИКАЦИИ   ========================================================= //
	// Получить список спецификаций
	public function forest_get_list_specification() { 
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$data = [ 
			'deleted'    => '0',
			'clients_id' => $_GET['clients_id'],
		];
		// Если указан период
		if ( isset($_GET['period']) ) { 
			$data['period'] = $_GET['period'];

			if ( $_GET['period'] === 'period' ) { 
				$data['date_from'] = $_GET['date_from']; 
				$data['date_to'] = $_GET['date_to'];
		}}
		// Если не указан cash = all
		if ( isset($_GET['cash']) && $_GET['cash'] !== 'all' ) { 
			$data['cash'] = $_GET['cash'];
		}
		// Если не указан dispatch = all
		if ( isset($_GET['dispatch']) && $_GET['dispatch'] !== 'all' ) { 
			$data['dispatch'] = $_GET['dispatch'];
		}
		// Список пользователей которые можно просматривать
		if (isset($_GET['view_list']) && !empty($_GET['view_list'])) { $data['view_list'] = $_GET['view_list']; }

		$data_answer = Forest_Manager_General_Function::get_data__specification_full( $data );

		// Отправляем ответ
		wp_send_json([
			'success'    => true,
			'code'       => 'get_list_specification',
			'data'       => $data_answer,
			'company_id' => ( isset($_GET['company_id']) )?:null,
			'client_id'  => ( isset($_GET['client_id']) )?:null,
		]);
	}
	// Получить список данные спецификаций
	public function forest_get_specification() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		// Если есть ID спецификации
		$product_list = [];
		$specification_data = [];

		if ( isset($_GET['specification_id']) ) {
			$get_data_specification = Forest_Manager_General_Function::get_data( 'specification', [ 'id' => $_GET['specification_id'] ] ); 
			$specification_data     = array_shift( $get_data_specification );

			$product_list           = Forest_Manager_General_Function::get_data__product_specification( $_GET['specification_id'] );
		}

		// Отправляем ответ 
		wp_send_json([
			'success'            => true,
			'code'               => 'get_specification',
			'product_list'       => $product_list,
			'specification_data' => $specification_data
		]);
	}
	// Добавить
	public function forest_add_specification() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		$list_product = $_POST['list_product'];
		$clients_id   = $_POST['clients_id'];

		$prefix  = preg_replace('/[0-9]+/', '', $_POST['number']);
		$postfix = '';
		if ($prefix === 'СП') {
			$matches = Forest_Manager_General_Function::get_list__matches('specification', $_POST['number']);
			$number  = $_POST['number'] . '-' . (count($matches) + 1);
		}

		$data = [
			'clients_id'       => $clients_id,
			'number'           => $number,
			'amount'           => $_POST['amount'],
			'user_id'          => $_POST['user_id'],
			'cash'             => +$_POST['cash'],
			'count_delivery'   => (isset($_POST['count_delivery']))?$_POST['count_delivery']:null,
			'price_delivery'   => (isset($_POST['price_delivery']))?$_POST['price_delivery']:null,
			'address_delivery' => (isset($_POST['address_delivery']))?$_POST['address_delivery']:null,
			'discount'         => (isset($_POST['discount']))?$_POST['discount']:null,
			'additional'       => (isset($_POST['additional']))?$_POST['additional']:null,
			'contract_id'      => (isset($_POST['contract_id']) && +$_POST['contract_id'])?$_POST['contract_id']:null,
			'date_creation'    => strtotime($_POST['date']),
			'date_update'      => strtotime( "now" )
		];

		if ( !$specification_id = Forest_Manager_General_Function::db_operations('insert', 'specification', $data) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (specification)'
			]);
		}

		// Добавление продукции спецификации
		self::add_product_specification($list_product, $specification_id);

		// Делаем отметку об изменении
		Forest_Manager_General_Function::set_date_change($clients_id, 'clients');

		// Отправляем ответ
		wp_send_json([
			'success'          => true,
			'code'             => 'edit_specification',
			'clients_id'       => $clients_id,
			'specification_id' => $specification_id
		]);
	}
	// Редактировать 
	public function forest_edit_specification() {
		global $wpdb;

		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		$specification_id = $_POST['id'];
		$list_product     = $_POST['list_product'];
		$clients_id       = $_POST['clients_id'];

		$data = [
			'clients_id'       => $clients_id, 
			'count_delivery'   => (isset($_POST['count_delivery']))?$_POST['count_delivery']:null,
			'price_delivery'   => (isset($_POST['price_delivery']))?$_POST['price_delivery']:null,
			'address_delivery' => (isset($_POST['address_delivery']))?$_POST['address_delivery']:null,
			'discount'         => (isset($_POST['discount']))?$_POST['discount']:null,
			'additional'       => (isset($_POST['additional']))?$_POST['additional']:null,
			'contract_id'      => (isset($_POST['contract_id']) && +$_POST['contract_id'])?$_POST['contract_id']:null,
			'number'           => $_POST['number'],
			'amount'           => $_POST['amount'],
			'user_id'          => $_POST['user_id'],
			'cash'             => +$_POST['cash'],
			'date_creation'    => strtotime( $_POST['date'] ),
			'date_update'      => strtotime( "now" )
		];

		if ( !Forest_Manager_General_Function::db_operations('update', 'specification', $data, ['id' => $specification_id]) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка обновления записи в базе данных (specification)'
			]);
		}

		// Удаляем старые записи
		$wpdb->delete( 'forest_product_specification', [ 'specification_id' => $specification_id ] );

		// Добавление продукции спецификации
		self::add_product_specification($list_product, $specification_id);

		// Делаем отметку об изменении
		Forest_Manager_General_Function::set_date_change($clients_id, 'clients');

		// Отправляем ответ
		wp_send_json([
			'success'          => true,
			'code'             => 'edit_specification',
			'clients_id'       => $clients_id,
			'specification_id' => $specification_id
		]);
	}
	// Отгрузка
	public function forest_dispatch_specification() {
		global $wpdb;
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		// file_put_contents('api-log.txt',var_export('forest_dispatch_specification',true).PHP_EOL,FILE_APPEND);
		// file_put_contents('api-log.txt',var_export($_POST,true).PHP_EOL,FILE_APPEND);

		$specification_id = $_POST['id'];
		$clients_id       = $_POST['clients_id']; 

		$data = [
			'date_dispatch' => strtotime($_POST['date_dispatch']),
			'block'         => true,
			'data_dispatch' => serialize([
				'car_brand'         => $_POST['car_brand'],
				'car_number'        => $_POST['car_number'],
				'car_driver'        => $_POST['car_driver']
			])
		];

		// Обновляем данные в счёте
		if ( !Forest_Manager_General_Function::db_operations('update', 'specification', $data, ['id' => $specification_id]) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (account)'
			]);
		}

		// Обновляем данные у клиента
		$get_data_specification = Forest_Manager_General_Function::get_data('specification', ['id' => $specification_id]);
		$specification_data     = array_shift( $get_data_specification );

		$amount = +$specification_data['amount'];
		$query  = $wpdb->prepare( "UPDATE `forest_clients` SET credit = credit + %f, date_change = %d  WHERE id = %d;", $amount, strtotime("now"), $clients_id ); 

		if ( !$wpdb->query( $query ) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (clients)'
			]);
		}

		// Отправляем ответ
		wp_send_json([
			'success'    => true,
			'code'       => 'dispatch_specification',
			'clients_id' => $clients_id,
		]);
	}
	// Отмена отгрузки
	public function forest_cancel_dispatch_dpecification() {
		global $wpdb;
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );
 
		$specification_id = $_POST['id'];
		$clients_id       = $_POST['clients_id'];

		$data = [
			'block'         => false,
			'date_dispatch' => null,
			'data_dispatch' => null
		];

		// Обновляем данные в счёте
		if ( !Forest_Manager_General_Function::db_operations('update', 'specification', $data, ['id' => $specification_id]) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (specification)'
			]);
		}

		// Обновляем данные у клиента
		$get_data_specification = Forest_Manager_General_Function::get_data('specification', ['id' => $specification_id]);
		$specification_data     = array_shift( $get_data_specification );

		$amount = +$specification_data['amount'];
		$query  = $wpdb->prepare( "UPDATE `forest_clients` SET credit = credit - %f, date_change = %d  WHERE id = %d;", $amount, strtotime("now"), $clients_id ); 

		if ( !$wpdb->query( $query ) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (clients)'
			]);
		}

		// Отправляем ответ
		wp_send_json([
			'success'    => true,
			'code'       => 'cancel_dispatch_dpecification',
			'clients_id' => $clients_id,
		]);
	}
	// Удаление
	public function forest_deleted_specification() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		$specification_id       = $_POST['id'];
		$get_data_specification = Forest_Manager_General_Function::get_data('specification', ['id' => $specification_id]);
		$specification_data     = array_shift($get_data_specification);
		$clients_id             = $specification_data['clients_id'];

		if ( $specification_data['block'] ) {
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_deleted_specification',
				'message' => 'Спецификация заблокированна',
			]);
		}

		if ( !Forest_Manager_General_Function::db_operations('update', 'specification', ['deleted' => 1], ['id' => $specification_id]) ) { 
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'error_writing_database',
				'message' => 'Ошибка записи в базу данных (specification)'
			]);
		}

		// Делаем отметку об изменении
		Forest_Manager_General_Function::set_date_change($client_id, 'client');
		
		// Отправляем ответ
		wp_send_json([
			'success'    => true,
			'code'       => 'deleted_specification',
			'clients_id' => $clients_id,
		]);
	}
	// Добавление продукции спецификации
	private function add_product_specification($list_product, $specification_id) {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		if ( empty($list_product) || !is_array($list_product) ) {
			// Отправляем ошибку
			wp_send_json([
				'success' => false,
				'code'    => 'no_data_available',
				'message' => 'Нет данных для добавления продукции спецификации'
			]);
		}
		global $wpdb;

		$arr_update_price = [];

		foreach ($list_product as $product) {
			if ( !Forest_Manager_General_Function::db_operations('insert', 'product_specification', [
				'specification_id' => $specification_id,
				'product_id'       => $product['product_id'], 
				'number'           => $product['pack_number'],
				'count'            => $product['count'],
				'price'            => $product['price'],
				'okpd_id'          => (isset($product['okpd_id']))?$product['okpd_id']:null,
				'opt'              => (bool) +$product['opt'],
			]) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка записи в базу данных (product_specification)'
				]);
			}

			$arr_update_price[ $product['product_id'] ] = [
				'price' => $product['price'],
				'opt'   => +$product['opt'],
			];
		}

		// Добавление цены для продукции
		foreach ($arr_update_price as $product__id => $product__value) {
			$opt = ($product__value['opt'])?'price_opt':'price';
			$query_update_price = 'UPDATE `forest_product` SET `'.$opt.'` = '.$product__value['price'].' WHERE `id` = '.$product__id;

			$wpdb->query( $query_update_price );
		}

		return true;
	}

	// ДОКУМЕНТЫ  ========================================================= //
	// Получить список документов
	public function forest_get_list_document() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$data = Forest_Manager_General_Function::get_data('document');
		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'get_list_document',
			'data'    => $data,
		]);
	}
	// Добавить документ
	public function forest_add_document() {
		global $wpdb;

		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		if ( empty($_POST['name']) || empty($_POST['clients_id']) ) {
			wp_send_json([
				'success' => false,
				'code'    => 'no_data_available',
				'message' => 'Нет данных для добавления документа'
			]);
		}

		if ( !isset($_FILES['file']) || !$_FILES['file']['name'] ) {
			wp_send_json([
				'success' => false,
				'code'    => 'no_file_uploaded',
				'message' => 'Не загружен файл'
			]);
		}

        // Загрузка файла
        $upload = Forest_Manager_General_Function::upload_file( $_FILES['file'], 'document', $_POST['clients_id'] );
        if ( !$upload ) {
            wp_send_json([
                'success' => false,
                'code'    => 'error_uploading_file',
                'message' => 'Ошибка при загрузке файла'
            ]);
        }

        // Запись в базу данных
        $data = [
            'name'       => $_POST['name'],
            'clients_id' => $_POST['clients_id'],
            'file'       => $upload['file'],
            'date_add'   => strtotime("now"),
        ];

        if ( !$wpdb->insert( 'forest_document', $data ) ) {
            wp_send_json([
                'success' => false,
                'code'    => 'error_writing_database',
                'message' => 'Ошибка записи в базу данных (document)'
            ]);
        }

        // Отправляем ответ


	// ПРОДУКЦИЯ  ========================================================= //
	// Получить список спецификаций
	public function forest_get_list_product() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$data = [];
		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'get_list_product',
			'data'    => Forest_Manager_General_Function::get_data('product'),
		]); 
	}
	// Добавить продукт
	public function forest_add_product() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('POST', $_POST );

		$name = (!empty($_POST['name']))?$_POST['name']:'';
		$product_name = array_shift(Forest_Manager_General_Function::get_data('guide', ['id' => $name]))['value'];
		$count = (!empty($_POST['count']))?$_POST['count']:'';
		$type = (!empty($_POST['type']))?$_POST['type']:'';
		$type_name = array_shift(Forest_Manager_General_Function::get_data('guide', ['id' => $type]))['value'];
		$sort = (!empty($_POST['sort']))?$_POST['sort']:'';
		$sort_name = array_shift(Forest_Manager_General_Function::get_data('guide', ['id' => $sort]))['value'];
		$height = (!empty($_POST['height']))?+$_POST['height']:0;
		$width = (!empty($_POST['width']))?+$_POST['width']:0;
		$length = (!empty($_POST['length']))?+$_POST['length']:0; 
		$weight = (!empty($_POST['weight']))?+$_POST['weight']:0;

		// Проверяем заполненность
		if ( empty($height) || empty($width) || empty($length) || empty($type) || empty($sort) || empty($name) ) {
			if ( empty($weight) || empty($name) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'no_data_available',
					'message' => 'Форма заполнена не правильно'
				]);
			}
		}

		// Дополнительные параметры которые нужно создать 
		$volume = 0;
		if ( !empty($height) && !empty($width) && !empty($length) ) {
			$name_search = $product_name . ' ' . $height . 'x' . $width . 'x' . $length . ' ' . $sort_name . ' ' . $type_name;

			$request = [
				'name_search' => $name_search,
				'name'        => $product_name,
				'height'      => $height,
				'width'       => $width,
				'length'      => $length,
				'type'        => $type,
				'sort'        => $sort,
				'granular'    => false,
			];
		} 

		if ( !empty($weight) ) {
			$name_search = $product_name . ' ' . $weight . 'кг';
			$request = [
				'name_search' => $name_search,
				'name'        => $product_name,
				'weight'      => $weight,
				'granular'    => true,
			];
		}

		// Проверка на дубликат
		$duplicate = Forest_Manager_General_Function::get_data('product', ['name_search' => $name_search]);
		if (!empty($duplicate)) {
			$product = array_shift($duplicate);

			$data = [
				'product_id'  => $product['id'], 
				'name_search' => $name_search, 
			];

			if (isset($product['granular'])) {
				$data['weight']   = $product['weight'];
				$data['granular'] = $product['granular'];
			} else {
				$data['height']   = $product['height']; 
				$data['width']    = $product['width'];
				$data['length']   = $product['length']; 
				$data['granular'] = $product['granular'];
			}
		} else {
			// Создать продукт
			if ( !$product_id = Forest_Manager_General_Function::db_operations('insert', 'product', $request) ) { 
				// Отправляем ошибку
				wp_send_json([
					'success' => false,
					'code'    => 'error_writing_database',
					'message' => 'Ошибка записи в базу данных'
				]);
			}

			$data = [
				'product_id'  => $product_id, 
				'name_search' => $name_search,
			];

			if (isset($request['granular'])) {
				$data['weight']   = $weight;
				$data['granular'] = 1;
			} else {
				$data['height']   = $height; 
				$data['width']    = $width;
				$data['length']   = $length;
				$data['granular'] = 0;
			}
		}

		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'add_product_specification',
			'count'   => $count,
			'data'    => $data,
		]);
	}
	// Добавить продукт
	public function forest_calculate_product() {
		// Проверка данных и доступа
		Forest_Manager_General_Function::check_data_access('GET', $_GET );

		$volume = Forest_Manager_General_Function::calculate_volume($_GET['height'], $_GET['width'], $_GET['length'], $_GET['count']);

		// Отправляем ответ
		wp_send_json([
			'success' => true,
			'code'    => 'calculate_product',
			'volume'  => $volume,
			'amount'  => $volume * +$_GET['price'],
		]);
	}

	// Учёт
	public function forest_accounting_upload_file() {
		global $wpdb;

        // ВАЖНО! тут должны быть все проверки безопасности передавемых файлов и вывести ошибки если нужно
        $uploaddir  = plugin_dir_path(__FILE__) . '/uploads'; // текущая папка 
        $files      = $_FILES; // полученные файлы
        $done_files = array();

        // переместим файлы из временной директории в указанную
        foreach( $files as $file ){
            $file_name = uniqid() . '-' . date("dmyHis") . '.xlsx';  

            if( move_uploaded_file( $file['tmp_name'], "$uploaddir/$file_name" ) ){
                $done_files[] = realpath( "$uploaddir/$file_name" );
            }
        }

        if (empty($done_files)) wp_send_json([ 'success' => false, 'message' => 'Не удалось загрузить файл' ]);

        // Подключаем библиотеку
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libs/spreadsheet-reader/php-excel-reader/excel_reader2.php';    
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libs/spreadsheet-reader/SpreadsheetReader.php'; 

		// Файл xlsx, xls, csv, ods.
		$Reader = new SpreadsheetReader( "$uploaddir/$file_name" );
		 
		// Выбираем номер листа
		$List_sheets = $Reader -> Sheets();
		$Sheets      = array_search('СГП', $List_sheets);

		// Отсутствует нужный лист
		if (!$Sheets) wp_send_json([ 'success' => false, 'message' => 'Отсутствует лист СГП' ]);

		// Получаем данные
		$Reader -> ChangeSheet( $Sheets );

		$arr = [];

		foreach ($Reader as $row) {
			$arr_temp = [];
			if ( !empty($row[14]) || empty($row[1]) ) continue;

			$arr_temp['name']   = $row[1];
			$arr_temp['sort']   = $row[4];
			$arr_temp['length'] = +$row[7];
			$arr_temp['width']  = +$row[6];
			$arr_temp['height'] = +$row[5];
			$arr_temp['count']  = +$row[8];
			$arr_temp['volume'] = +$row[9];

			$arr[] = $arr_temp;
		}

		// Добавляем данные
		if (!$wpdb->insert( 'forest_accounting', [
			'date' => strtotime(date('d-m-Y H:i:s')) + (60*60*3),
			'data' => serialize($arr)
		])) wp_send_json([ 'success' => false, 'message' => $wpdb->last_error ]);

        // Отправляем ответ
		wp_send_json([ 'success' => true, 'message' => 'Данные обновлены' ]);
	}


}


// file_put_contents('a-Forest_Manager_Public.txt',var_export($request,true).PHP_EOL,FILE_APPEND);
