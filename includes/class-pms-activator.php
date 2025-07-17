<?php
/**
 * Fired during plugin activation
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 * @author     Manus AI <#>
 */
class PMS_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	    public static function activate() {
        global $wpdb;

        if ( is_multisite() ) {
            // Get all blogs in the network and activate plugin on each one
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ( $blog_ids as $blog_id ) {
                switch_to_blog( $blog_id );
                self::_create_tables();
                restore_current_blog();
            }
        } else {
            self::_create_tables();
        }
    }

    /**
     * Create necessary database tables.
     *
     * @since    1.0.0
     * @access   private
     */
    private static function _create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'pms_patients';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            first_name varchar(255) NOT NULL,
            last_name varchar(255) NOT NULL,
            dob date NOT NULL,
            gender varchar(50) NOT NULL,
            address text NOT NULL,
            phone varchar(50) NOT NULL,
            email varchar(255) NOT NULL,
            profile_picture varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $table_name = $wpdb->prefix . 'pms_medical_records';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            patient_id bigint(20) NOT NULL,
            record_type varchar(255) NOT NULL,
            record_date date NOT NULL,
            content longtext NOT NULL,
            version int(11) DEFAULT 1 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id)
        ) $charset_collate;";

        dbDelta( $sql );
    }