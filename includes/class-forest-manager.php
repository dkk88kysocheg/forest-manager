<?php

/**
 * The file that defines the core plugin class
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/includes
 */

class Forest_Manager {

	protected $loader;

	protected $forest_manager;

	public function __construct() {
		if ( defined( 'FOREST_MANAGER_VERSION' ) ) {
			$this->version = FOREST_MANAGER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->forest_manager = 'forest-manager';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_general_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-forest-manager-loader.php'; 
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-forest-manager-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-forest-manager-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-forest-manager-general-function.php';

		$this->loader = new Forest_Manager_Loader();
	}

	private function define_admin_hooks() {
		$plugin_admin = new Forest_Manager_Admin( $this->get_forest_manager(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Hook для добавления страницы в меню администратора
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'additional_settings' );
		// Cправочник
		$this->loader->add_action( 'admin_post_forest_add_edit_guide', $plugin_admin, 'forest_add_edit_guide' );  // Редактирование
		$this->loader->add_action( 'admin_post_forest_delete_guide', $plugin_admin, 'forest_delete_guide' );  // Удаление

		// Обновление плагина
		$this->loader->add_action( 'admin_post_update_forest_manager', $plugin_admin, 'update_forest_manager' );  // Обновыить плагин 
	}

	private function define_public_hooks() {
		$plugin_public = new Forest_Manager_Public( $this->get_forest_manager(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'widgets_init', $plugin_public, 'run_widget' );

		// Клиенты
		$this->loader->add_action( 'admin_post_forest_get_clients', $plugin_public, 'forest_get_clients' ); // Получить список клиентов
		$this->loader->add_action( 'admin_post_forest_add_edit_client', $plugin_public, 'forest_add_edit_client' ); // Добавить/Редактировать
		$this->loader->add_action( 'admin_post_forest_hide_client', $plugin_public, 'forest_hide_client' ); // Скрыть 

		$this->loader->add_action( 'admin_post_forest_get_list_contact', $plugin_public, 'forest_get_list_contact' ); // Список контактов 
		$this->loader->add_action( 'admin_post_forest_add_edit_contact', $plugin_public, 'forest_add_edit_contact' ); // Добавить/Редактировать контакт

		$this->loader->add_action( 'admin_post_forest_get_list_contract', $plugin_public, 'forest_get_list_contract' ); // Список договоров 
		$this->loader->add_action( 'admin_post_forest_add_edit_contract', $plugin_public, 'forest_add_edit_contract' ); // Добавить/Редактировать договор
		$this->loader->add_action( 'admin_post_forest_generate_contract', $plugin_public, 'forest_generate_contract' ); // Сгенерировать договор

		$this->loader->add_action( 'admin_post_forest_check_delete_contract', $plugin_public, 'forest_check_delete_contract' ); // Проверить/Удалить договор
		$this->loader->add_action( 'admin_post_forest_add_delete_file_contract', $plugin_public, 'forest_add_delete_file_contract' ); // Загрузить/Удалить PDF договор

		// Сообщения 
		$this->loader->add_action( 'admin_post_forest_get_messages', $plugin_public, 'forest_get_messages' ); // Получить список комментариев
		$this->loader->add_action( 'admin_post_forest_add_edit_message', $plugin_public, 'forest_add_edit_message' ); // Добавить комментарий 
		$this->loader->add_action( 'admin_post_forest_delete_message', $plugin_public, 'forest_delete_message' ); // Удалить сообщение

		// Счёт
		$this->loader->add_action( 'admin_post_forest_get_list_account', $plugin_public, 'forest_get_list_account' ); // Получить список счетов
		$this->loader->add_action( 'admin_post_forest_get_account', $plugin_public, 'forest_get_account' ); // Получить данные счёта
		$this->loader->add_action( 'admin_post_forest_add_account', $plugin_public, 'forest_add_account' ); // Добавить
		$this->loader->add_action( 'admin_post_forest_edit_account', $plugin_public, 'forest_edit_account' ); // Редактировать
		$this->loader->add_action( 'admin_post_forest_deleted_account', $plugin_public, 'forest_deleted_account' ); // Удалить
		$this->loader->add_action( 'admin_post_forest_payment_account', $plugin_public, 'forest_payment_account' ); // Оплата

		// Спецификации
		$this->loader->add_action( 'admin_post_forest_get_list_specification', $plugin_public, 'forest_get_list_specification' ); // Получить список спецификаций
		$this->loader->add_action( 'admin_post_forest_get_specification', $plugin_public, 'forest_get_specification' ); // Получить список данные спецификаций
		$this->loader->add_action( 'admin_post_forest_add_specification', $plugin_public, 'forest_add_specification' ); // Добавить
		$this->loader->add_action( 'admin_post_forest_edit_specification', $plugin_public, 'forest_edit_specification' ); // Редактировать
		$this->loader->add_action( 'admin_post_forest_deleted_specification', $plugin_public, 'forest_deleted_specification' ); // Удалить 
		$this->loader->add_action( 'admin_post_forest_dispatch_specification', $plugin_public, 'forest_dispatch_specification' ); // Отгрузка  
		$this->loader->add_action( 'admin_post_forest_cancel_dispatch_dpecification', $plugin_public, 'forest_cancel_dispatch_dpecification' ); // Отмена отгрузки  

		// Продукция
		$this->loader->add_action( 'admin_post_forest_get_list_product', $plugin_public, 'forest_get_list_product' ); // Получить список продукции
		$this->loader->add_action( 'admin_post_forest_add_product', $plugin_public, 'forest_add_product' ); // Добавить продукт 
		$this->loader->add_action( 'admin_post_forest_calculate_product', $plugin_public, 'forest_calculate_product' ); // Посчитать продукт 

		// Учёт
		$this->loader->add_action( 'admin_post_forest_accounting_upload_file', $plugin_public, 'forest_accounting_upload_file' ); 
		$this->loader->add_action( 'admin_post_forest_accounting_recognize_file', $plugin_public, 'forest_accounting_recognize_file' ); 
	}

	private function define_general_hooks() {
		$plugin_general = new Forest_Manager_General_Function( $this->get_forest_manager(), $this->get_version() );

		// когда чей-то профиль редактируется админом например
		$this->loader->add_action( 'show_user_profile', $plugin_general, 'show_profile_fields_admin' ); // Показать администратору
		$this->loader->add_action( 'edit_user_profile', $plugin_general, 'show_profile_fields' ); // Показать
		$this->loader->add_action( 'personal_options_update', $plugin_general, 'save_profile_fields' ); // Сохранить
		$this->loader->add_action( 'edit_user_profile_update', $plugin_general, 'save_profile_fields' ); // Сохранить
		// Добавление картинок
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_general, 'load_media_files' ); 
	}

	public function run() {
		$this->loader->run();
	}

	public function get_forest_manager() {
		return $this->forest_manager;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
