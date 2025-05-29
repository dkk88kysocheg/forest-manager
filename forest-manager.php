<?php

/**
 * The plugin bootstrap file
 *
 * @package           Forest_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Forest manager
 * Description:       Система ведения клиентов ООО Форест групп
 * Version:           1.3.2 
 * Author:            Dobrokhotov Anatoly
 * Author URI:        https://dobrokhotov.pro/
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'FOREST_MANAGER_VERSION', '1.3.2' );

define( 'PAGE_ID__client', 27 );
define( 'PAGE_ID__client_add', 59 );
define( 'PAGE_ID__client_edit', 57 );
define( 'PAGE_ID__statistics', 2 );
define( 'PAGE_ID__legal_entity', 7 );
define( 'PAGE_ID__physical_entity', 25 );
define( 'PAGE_ID__accounting', 36 );
define( 'PAGE_ID__reports', 38 );
define( 'PAGE_ID__calculator', 89 );
define( 'PAGE_ID__pdf', 93 );
define( 'PAGE_ID__excel', 95 );
define( 'PAGE_ID__print', 12 );

function activate_forest_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-forest-manager-activator.php';
	Forest_Manager_Activator::activate();
}

function deactivate_forest_manager() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-forest-manager-deactivator.php';
	Forest_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_forest_manager' );
register_deactivation_hook( __FILE__, 'deactivate_forest_manager' );

require plugin_dir_path( __FILE__ ) . 'includes/class-forest-manager.php';

function run_forest_manager() {

	$plugin = new Forest_Manager();
	$plugin->run();

}
run_forest_manager();
