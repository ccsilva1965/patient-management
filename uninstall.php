<?php
/**
 * Uninstall script for Patient Management System
 *
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data from the database.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Patient_Management_System
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Define table names
$patients_table = $wpdb->prefix . 'pms_patients';
$medical_records_table = $wpdb->prefix . 'pms_medical_records';

// Remove custom tables
$wpdb->query( "DROP TABLE IF EXISTS $medical_records_table" );
$wpdb->query( "DROP TABLE IF EXISTS $patients_table" );

// Remove custom post types and their meta
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ('pms_patient', 'pms_medical_record')" );
$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL" );

// Remove custom capabilities from all roles
$roles = wp_roles()->roles;
$pms_capabilities = array(
    'pms_view_patients',
    'pms_edit_patients',
    'pms_delete_patients',
    'pms_view_medical_records',
    'pms_edit_medical_records',
    'pms_delete_medical_records',
    'pms_view_own_records',
    'pms_manage_permissions',
);

foreach ( $roles as $role_name => $role_info ) {
    $role = get_role( $role_name );
    if ( $role ) {
        foreach ( $pms_capabilities as $cap ) {
            $role->remove_cap( $cap );
        }
    }
}

// Remove custom roles
remove_role( 'pms_doctor' );
remove_role( 'pms_nurse' );
remove_role( 'pms_receptionist' );
remove_role( 'pms_patient' );

// Remove plugin options
delete_option( 'pms_options' );
delete_option( 'pms_version' );

// Clear any cached data
wp_cache_flush();

