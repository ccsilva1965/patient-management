<?php
/**
 * Plugin Name: Patient Management System
 * Description: A comprehensive WordPress plugin for managing patient data and medical records.
 * Version: 1.1.1
 * Author: Manus AI
 * Text Domain: patient-management-system
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'PMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PMS_URL', plugin_dir_url( __FILE__ ) );
define( 'PMS_VERSION', '1.1.0' );

// Include core files.
require_once PMS_PATH . 'includes/class-pms-activator.php';
require_once PMS_PATH . 'includes/class-pms-deactivator.php';
require_once PMS_PATH . 'includes/class-pms-loader.php';
require_once PMS_PATH . 'includes/class-pms-core.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pms-activator.php
 */
function activate_patient_management_system() {
    PMS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pms-deactivator.php
 */
function deactivate_patient_management_system() {
    PMS_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_patient_management_system' );
register_deactivation_hook( __FILE__, 'deactivate_patient_management_system' );

/**
 * Begins execution of the plugin.
 * Since everything within the plugin is registered via hooks,
 * then another class should be called to run the plugin.
 */
function run_patient_management_system() {
    $plugin = new PMS_Core();
    $plugin->run();
}
run_patient_management_system();


