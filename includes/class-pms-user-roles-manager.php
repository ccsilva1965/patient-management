<?php
/**
 * User roles and permissions management functionality
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 */

/**
 * User roles and permissions management class.
 *
 * This class defines all code necessary to manage user roles and permissions.
 *
 * @since      1.0.0
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 * @author     Manus AI <#>
 */
class PMS_User_Roles_Manager {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action( 'init', array( $this, 'add_custom_roles' ) );
        add_action( 'admin_menu', array( $this, 'add_permissions_menu' ) );
        add_action( 'admin_init', array( $this, 'handle_permissions_form' ) );
        add_filter( 'map_meta_cap', array( $this, 'map_meta_capabilities' ), 10, 4 );
    }

    /**
     * Add custom user roles for the plugin.
     *
     * @since    1.0.0
     */
    public function add_custom_roles() {
        // Add Doctor role
        add_role(
            'pms_doctor',
            __( 'Médico', 'patient-management-system' ),
            array(
                'read'                      => true,
                'pms_view_patients'         => true,
                'pms_edit_patients'         => true,
                'pms_delete_patients'       => true,
                'pms_view_medical_records'  => true,
                'pms_edit_medical_records'  => true,
                'pms_delete_medical_records'=> true,
            )
        );

        // Add Nurse role
        add_role(
            'pms_nurse',
            __( 'Enfermeiro(a)', 'patient-management-system' ),
            array(
                'read'                      => true,
                'pms_view_patients'         => true,
                'pms_edit_patients'         => true,
                'pms_view_medical_records'  => true,
                'pms_edit_medical_records'  => true,
            )
        );

        // Add Receptionist role
        add_role(
            'pms_receptionist',
            __( 'Recepcionista', 'patient-management-system' ),
            array(
                'read'                      => true,
                'pms_view_patients'         => true,
                'pms_edit_patients'         => true,
            )
        );

        // Add Patient role
        add_role(
            'pms_patient',
            __( 'Paciente', 'patient-management-system' ),
            array(
                'read'                      => true,
                'pms_view_own_records'      => true,
            )
        );

        // Add capabilities to administrator
        $admin_role = get_role( 'administrator' );
        if ( $admin_role ) {
            $admin_role->add_cap( 'pms_manage_permissions' );
            $admin_role->add_cap( 'pms_view_patients' );
            $admin_role->add_cap( 'pms_edit_patients' );
            $admin_role->add_cap( 'pms_delete_patients' );
            $admin_role->add_cap( 'pms_view_medical_records' );
            $admin_role->add_cap( 'pms_edit_medical_records' );
            $admin_role->add_cap( 'pms_delete_medical_records' );
        }
    }

    /**
     * Add permissions management menu to admin.
     *
     * @since    1.0.0
     */
    public function add_permissions_menu() {
        add_submenu_page(
            'users.php',
            __( 'Permissões PMS', 'patient-management-system' ),
            __( 'Permissões PMS', 'patient-management-system' ),
            'pms_manage_permissions',
            'pms-permissions',
            array( $this, 'permissions_page_callback' )
        );
    }

    /**
     * Permissions page callback.
     *
     * @since    1.0.0
     */
    public function permissions_page_callback() {
        if ( ! current_user_can( 'pms_manage_permissions' ) ) {
            wp_die( __( 'Você não tem permissão para acessar esta página.', 'patient-management-system' ) );
        }

        $roles = wp_roles()->roles;
        $pms_capabilities = array(
            'pms_view_patients'         => __( 'Ver Pacientes', 'patient-management-system' ),
            'pms_edit_patients'         => __( 'Editar Pacientes', 'patient-management-system' ),
            'pms_delete_patients'       => __( 'Excluir Pacientes', 'patient-management-system' ),
            'pms_view_medical_records'  => __( 'Ver Registros Médicos', 'patient-management-system' ),
            'pms_edit_medical_records'  => __( 'Editar Registros Médicos', 'patient-management-system' ),
            'pms_delete_medical_records'=> __( 'Excluir Registros Médicos', 'patient-management-system' ),
            'pms_view_own_records'      => __( 'Ver Próprios Registros', 'patient-management-system' ),
            'pms_manage_permissions'    => __( 'Gerenciar Permissões', 'patient-management-system' ),
        );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <?php if ( isset( $_GET['updated'] ) && $_GET['updated'] == 'true' ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e( 'Permissões atualizadas com sucesso!', 'patient-management-system' ); ?></p>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field( 'pms_update_permissions', 'pms_permissions_nonce' ); ?>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Função', 'patient-management-system' ); ?></th>
                            <?php foreach ( $pms_capabilities as $cap => $label ) : ?>
                                <th><?php echo esc_html( $label ); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $roles as $role_name => $role_info ) : ?>
                            <?php $role = get_role( $role_name ); ?>
                            <tr>
                                <td><strong><?php echo esc_html( $role_info['name'] ); ?></strong></td>
                                <?php foreach ( $pms_capabilities as $cap => $label ) : ?>
                                    <td>
                                        <input type="checkbox" 
                                               name="permissions[<?php echo esc_attr( $role_name ); ?>][<?php echo esc_attr( $cap ); ?>]" 
                                               value="1" 
                                               <?php checked( $role->has_cap( $cap ) ); ?> />
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="<?php _e( 'Salvar Permissões', 'patient-management-system' ); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Handle permissions form submission.
     *
     * @since    1.0.0
     */
    public function handle_permissions_form() {
        if ( ! isset( $_POST['pms_permissions_nonce'] ) || ! wp_verify_nonce( $_POST['pms_permissions_nonce'], 'pms_update_permissions' ) ) {
            return;
        }

        if ( ! current_user_can( 'pms_manage_permissions' ) ) {
            return;
        }

        if ( isset( $_POST['permissions'] ) ) {
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

            $roles = wp_roles()->roles;
            
            foreach ( $roles as $role_name => $role_info ) {
                $role = get_role( $role_name );
                
                foreach ( $pms_capabilities as $cap ) {
                    if ( isset( $_POST['permissions'][$role_name][$cap] ) ) {
                        $role->add_cap( $cap );
                    } else {
                        $role->remove_cap( $cap );
                    }
                }
            }

            wp_redirect( add_query_arg( 'updated', 'true', wp_get_referer() ) );
            exit;
        }
    }

    /**
     * Map meta capabilities for custom post types.
     *
     * @since    1.0.0
     * @param    array  $caps    The user's capabilities.
     * @param    string $cap     Capability name.
     * @param    int    $user_id The user ID.
     * @param    array  $args    Adds the context to the cap. Typically the object ID.
     * @return   array  The user's capabilities.
     */
    public function map_meta_capabilities( $caps, $cap, $user_id, $args ) {
        // Map capabilities for patient post type
        if ( 'edit_post' == $cap || 'delete_post' == $cap || 'read_post' == $cap ) {
            $post = get_post( $args[0] );
            $post_type = get_post_type_object( $post->post_type );

            if ( 'pms_patient' == $post->post_type ) {
                if ( 'edit_post' == $cap ) {
                    $caps = array( 'pms_edit_patients' );
                } elseif ( 'delete_post' == $cap ) {
                    $caps = array( 'pms_delete_patients' );
                } elseif ( 'read_post' == $cap ) {
                    $caps = array( 'pms_view_patients' );
                }
            } elseif ( 'pms_medical_record' == $post->post_type ) {
                if ( 'edit_post' == $cap ) {
                    $caps = array( 'pms_edit_medical_records' );
                } elseif ( 'delete_post' == $cap ) {
                    $caps = array( 'pms_delete_medical_records' );
                } elseif ( 'read_post' == $cap ) {
                    // Check if user is trying to view their own records
                    $user = get_userdata( $user_id );
                    if ( in_array( 'pms_patient', $user->roles ) ) {
                        $patient_id = get_post_meta( $post->ID, '_medical_record_patient_id', true );
                        $user_patient_post = get_posts( array(
                            'post_type' => 'pms_patient',
                            'meta_query' => array(
                                array(
                                    'key' => '_patient_email',
                                    'value' => $user->user_email,
                                    'compare' => '='
                                )
                            ),
                            'numberposts' => 1
                        ) );
                        
                        if ( ! empty( $user_patient_post ) && $user_patient_post[0]->ID == $patient_id ) {
                            $caps = array( 'pms_view_own_records' );
                        } else {
                            $caps = array( 'pms_view_medical_records' );
                        }
                    } else {
                        $caps = array( 'pms_view_medical_records' );
                    }
                }
            }
        }

        return $caps;
    }

    /**
     * Check if current user can access patients.
     *
     * @since    1.0.0
     * @return   bool True if user can access, false otherwise.
     */
    public static function can_access_patients() {
        return current_user_can( 'pms_view_patients' );
    }

    /**
     * Check if current user can access medical records.
     *
     * @since    1.0.0
     * @return   bool True if user can access, false otherwise.
     */
    public static function can_access_medical_records() {
        return current_user_can( 'pms_view_medical_records' ) || current_user_can( 'pms_view_own_records' );
    }
}

