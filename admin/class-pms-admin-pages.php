<?php
/**
 * Dashboard and admin pages functionality
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/admin
 */

/**
 * Dashboard and admin pages class.
 *
 * This class defines all code necessary to create admin pages and dashboard.
 *
 * @since      1.0.0
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/admin
 * @author     Manus AI <#>
 */
class PMS_Admin_Pages {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
    }

    /**
     * Add admin menu pages.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __( 'Patient Management', 'patient-management-system' ),
            __( 'Patient Management', 'patient-management-system' ),
            'pms_view_patients',
            'pms-dashboard',
            array( $this, 'dashboard_page_callback' ),
            'dashicons-heart',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'pms-dashboard',
            __( 'Dashboard', 'patient-management-system' ),
            __( 'Dashboard', 'patient-management-system' ),
            'pms_view_patients',
            'pms-dashboard',
            array( $this, 'dashboard_page_callback' )
        );

        // Patients submenu
        add_submenu_page(
            'pms-dashboard',
            __( 'Pacientes', 'patient-management-system' ),
            __( 'Pacientes', 'patient-management-system' ),
            'pms_view_patients',
            'edit.php?post_type=pms_patient'
        );

        // Medical Records submenu
        add_submenu_page(
            'pms-dashboard',
            __( 'Registros Médicos', 'patient-management-system' ),
            __( 'Registros Médicos', 'patient-management-system' ),
            'pms_view_medical_records',
            'edit.php?post_type=pms_medical_record'
        );

        // Reports submenu
        add_submenu_page(
            'pms-dashboard',
            __( 'Relatórios', 'patient-management-system' ),
            __( 'Relatórios', 'patient-management-system' ),
            'pms_view_patients',
            'pms-reports',
            array( $this, 'reports_page_callback' )
        );

        // Settings submenu
        add_submenu_page(
            'pms-dashboard',
            __( 'Configurações', 'patient-management-system' ),
            __( 'Configurações', 'patient-management-system' ),
            'manage_options',
            'pms-settings',
            array( $this, 'settings_page_callback' )
        );
    }

    /**
     * Initialize admin settings.
     *
     * @since    1.0.0
     */
    public function admin_init() {
        register_setting( 'pms_settings', 'pms_options' );
    }

    /**
     * Dashboard page callback.
     *
     * @since    1.0.0
     */
    public function dashboard_page_callback() {
        if ( ! current_user_can( 'pms_view_patients' ) ) {
            wp_die( __( 'Você não tem permissão para acessar esta página.', 'patient-management-system' ) );
        }

        // Get statistics
        $total_patients = wp_count_posts( 'pms_patient' )->publish;
        $total_records = wp_count_posts( 'pms_medical_record' )->publish;
        $recent_patients = get_posts( array(
            'post_type' => 'pms_patient',
            'numberposts' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ) );
        $recent_records = get_posts( array(
            'post_type' => 'pms_medical_record',
            'numberposts' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ) );

        ?>
        <div class="wrap pms-admin-page">
            <div class="pms-admin-header">
                <h1><?php _e( 'Dashboard - Patient Management System', 'patient-management-system' ); ?></h1>
                <p><?php _e( 'Bem-vindo ao sistema de gerenciamento de pacientes. Aqui você pode visualizar estatísticas e acessar rapidamente as principais funcionalidades.', 'patient-management-system' ); ?></p>
            </div>

            <div class="pms-stats-grid">
                <div class="pms-stat-card">
                    <div class="pms-stat-number"><?php echo esc_html( $total_patients ); ?></div>
                    <div class="pms-stat-label"><?php _e( 'Total de Pacientes', 'patient-management-system' ); ?></div>
                </div>
                <div class="pms-stat-card">
                    <div class="pms-stat-number"><?php echo esc_html( $total_records ); ?></div>
                    <div class="pms-stat-label"><?php _e( 'Registros Médicos', 'patient-management-system' ); ?></div>
                </div>
                <div class="pms-stat-card">
                    <div class="pms-stat-number"><?php echo esc_html( count( $recent_patients ) ); ?></div>
                    <div class="pms-stat-label"><?php _e( 'Novos Pacientes (Últimos 30 dias)', 'patient-management-system' ); ?></div>
                </div>
                <div class="pms-stat-card">
                    <div class="pms-stat-number"><?php echo esc_html( count( $recent_records ) ); ?></div>
                    <div class="pms-stat-label"><?php _e( 'Registros Recentes', 'patient-management-system' ); ?></div>
                </div>
            </div>

            <div class="pms-quick-actions">
                <h2><?php _e( 'Ações Rápidas', 'patient-management-system' ); ?></h2>
                <div class="pms-action-buttons">
                    <?php if ( current_user_can( 'pms_edit_patients' ) ) : ?>
                        <a href="<?php echo admin_url( 'post-new.php?post_type=pms_patient' ); ?>" class="pms-action-btn">
                            <span class="dashicons dashicons-plus"></span>
                            <?php _e( 'Novo Paciente', 'patient-management-system' ); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ( current_user_can( 'pms_edit_medical_records' ) ) : ?>
                        <a href="<?php echo admin_url( 'post-new.php?post_type=pms_medical_record' ); ?>" class="pms-action-btn">
                            <span class="dashicons dashicons-clipboard"></span>
                            <?php _e( 'Novo Registro', 'patient-management-system' ); ?>
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo admin_url( 'admin.php?page=pms-reports' ); ?>" class="pms-action-btn secondary">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php _e( 'Ver Relatórios', 'patient-management-system' ); ?>
                    </a>
                    
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <a href="<?php echo admin_url( 'admin.php?page=pms-settings' ); ?>" class="pms-action-btn secondary">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php _e( 'Configurações', 'patient-management-system' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="pms-recent-activity">
                    <h2><?php _e( 'Pacientes Recentes', 'patient-management-system' ); ?></h2>
                    <?php if ( ! empty( $recent_patients ) ) : ?>
                        <ul class="pms-activity-list">
                            <?php foreach ( $recent_patients as $patient ) : ?>
                                <?php
                                $first_name = get_post_meta( $patient->ID, '_patient_first_name', true );
                                $last_name = get_post_meta( $patient->ID, '_patient_last_name', true );
                                $patient_name = $first_name . ' ' . $last_name;
                                ?>
                                <li class="pms-activity-item">
                                    <div class="pms-activity-text">
                                        <a href="<?php echo get_edit_post_link( $patient->ID ); ?>">
                                            <?php echo esc_html( $patient_name ); ?>
                                        </a>
                                    </div>
                                    <div class="pms-activity-time">
                                        <?php echo esc_html( human_time_diff( strtotime( $patient->post_date ), current_time( 'timestamp' ) ) . ' atrás' ); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p><?php _e( 'Nenhum paciente encontrado.', 'patient-management-system' ); ?></p>
                    <?php endif; ?>
                </div>

                <div class="pms-recent-activity">
                    <h2><?php _e( 'Registros Recentes', 'patient-management-system' ); ?></h2>
                    <?php if ( ! empty( $recent_records ) ) : ?>
                        <ul class="pms-activity-list">
                            <?php foreach ( $recent_records as $record ) : ?>
                                <?php
                                $patient_id = get_post_meta( $record->ID, '_medical_record_patient_id', true );
                                $record_type = get_post_meta( $record->ID, '_medical_record_type', true );
                                $patient_name = '';
                                if ( $patient_id ) {
                                    $first_name = get_post_meta( $patient_id, '_patient_first_name', true );
                                    $last_name = get_post_meta( $patient_id, '_patient_last_name', true );
                                    $patient_name = $first_name . ' ' . $last_name;
                                }
                                ?>
                                <li class="pms-activity-item">
                                    <div class="pms-activity-text">
                                        <a href="<?php echo get_edit_post_link( $record->ID ); ?>">
                                            <?php echo esc_html( ucfirst( $record_type ) . ' - ' . $patient_name ); ?>
                                        </a>
                                    </div>
                                    <div class="pms-activity-time">
                                        <?php echo esc_html( human_time_diff( strtotime( $record->post_date ), current_time( 'timestamp' ) ) . ' atrás' ); ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p><?php _e( 'Nenhum registro encontrado.', 'patient-management-system' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Reports page callback.
     *
     * @since    1.0.0
     */
    public function reports_page_callback() {
        if ( ! current_user_can( 'pms_view_patients' ) ) {
            wp_die( __( 'Você não tem permissão para acessar esta página.', 'patient-management-system' ) );
        }

        ?>
        <div class="wrap pms-admin-page">
            <div class="pms-admin-header">
                <h1><?php _e( 'Relatórios', 'patient-management-system' ); ?></h1>
                <p><?php _e( 'Visualize relatórios e estatísticas detalhadas do sistema.', 'patient-management-system' ); ?></p>
            </div>

            <div class="pms-quick-actions">
                <h2><?php _e( 'Relatórios Disponíveis', 'patient-management-system' ); ?></h2>
                <div class="pms-action-buttons">
                    <button class="pms-action-btn" onclick="generateReport('patients')">
                        <span class="dashicons dashicons-groups"></span>
                        <?php _e( 'Relatório de Pacientes', 'patient-management-system' ); ?>
                    </button>
                    <button class="pms-action-btn" onclick="generateReport('records')">
                        <span class="dashicons dashicons-clipboard"></span>
                        <?php _e( 'Relatório de Registros', 'patient-management-system' ); ?>
                    </button>
                    <button class="pms-action-btn secondary" onclick="exportData()">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e( 'Exportar Dados', 'patient-management-system' ); ?>
                    </button>
                </div>
            </div>

            <div id="pms-report-content">
                <p><?php _e( 'Selecione um relatório acima para visualizar os dados.', 'patient-management-system' ); ?></p>
            </div>
        </div>

        <script>
        function generateReport(type) {
            document.getElementById('pms-report-content').innerHTML = '<p>Gerando relatório de ' + type + '...</p>';
            // Aqui seria implementada a lógica de geração de relatórios via AJAX
        }

        function exportData() {
            alert('Funcionalidade de exportação será implementada em breve.');
        }
        </script>
        <?php
    }

    /**
     * Settings page callback.
     *
     * @since    1.0.0
     */
    public function settings_page_callback() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Você não tem permissão para acessar esta página.', 'patient-management-system' ) );
        }

        ?>
        <div class="wrap pms-admin-page">
            <div class="pms-admin-header">
                <h1><?php _e( 'Configurações', 'patient-management-system' ); ?></h1>
                <p><?php _e( 'Configure as opções do sistema de gerenciamento de pacientes.', 'patient-management-system' ); ?></p>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'pms_settings' );
                do_settings_sections( 'pms_settings' );
                ?>
                
                <div class="pms-quick-actions">
                    <h2><?php _e( 'Configurações Gerais', 'patient-management-system' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e( 'Permitir auto-salvamento', 'patient-management-system' ); ?></th>
                            <td>
                                <input type="checkbox" name="pms_options[auto_save]" value="1" <?php checked( 1, get_option( 'pms_options' )['auto_save'] ?? 0 ); ?> />
                                <p class="description"><?php _e( 'Salva automaticamente os formulários enquanto o usuário digita.', 'patient-management-system' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'Notificações por email', 'patient-management-system' ); ?></th>
                            <td>
                                <input type="checkbox" name="pms_options[email_notifications]" value="1" <?php checked( 1, get_option( 'pms_options' )['email_notifications'] ?? 0 ); ?> />
                                <p class="description"><?php _e( 'Envia notificações por email para eventos importantes.', 'patient-management-system' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

