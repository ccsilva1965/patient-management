<?php
/**
 * Patient portal functionality for frontend access
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 */

/**
 * Patient portal class.
 *
 * This class defines all code necessary to manage patient portal functionality.
 *
 * @since      1.0.0
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 * @author     Manus AI <#>
 */
class PMS_Patient_Portal {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action( 'init', array( $this, 'init_shortcodes' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_portal_scripts' ) );
        add_action( 'wp_ajax_pms_patient_login', array( $this, 'handle_patient_login' ) );
        add_action( 'wp_ajax_nopriv_pms_patient_login', array( $this, 'handle_patient_login' ) );
        add_action( 'wp_ajax_pms_search_patient_records', array( $this, 'search_patient_records' ) );
        add_action( 'wp_ajax_nopriv_pms_search_patient_records', array( $this, 'search_patient_records' ) );
    }

    /**
     * Initialize shortcodes.
     *
     * @since    1.0.0
     */
    public function init_shortcodes() {
        add_shortcode( 'pms_patient_portal', array( $this, 'patient_portal_shortcode' ) );
        add_shortcode( 'pms_patient_search', array( $this, 'patient_search_shortcode' ) );
    }

    /**
     * Enqueue portal scripts and styles.
     *
     * @since    1.0.0
     */
    public function enqueue_portal_scripts() {
        wp_enqueue_script( 
            'pms-patient-portal', 
            plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/pms-patient-portal.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );

        wp_enqueue_style( 
            'pms-patient-portal-styles', 
            plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/pms-patient-portal.css',
            array(),
            '1.0.0'
        );

        // Localize script for AJAX
        wp_localize_script( 'pms-patient-portal', 'pms_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'pms_patient_portal_nonce' ),
            'strings' => array(
                'searching' => __( 'Buscando...', 'patient-management-system' ),
                'no_records' => __( 'Nenhum registro encontrado.', 'patient-management-system' ),
                'error' => __( 'Erro ao buscar registros.', 'patient-management-system' ),
                'invalid_credentials' => __( 'Credenciais inválidas.', 'patient-management-system' ),
            )
        ) );
    }

    /**
     * Patient portal shortcode.
     *
     * @since    1.0.0
     * @param    array $atts Shortcode attributes.
     * @return   string The shortcode output.
     */
    public function patient_portal_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'title' => __( 'Portal do Paciente', 'patient-management-system' ),
            'description' => __( 'Acesse seus registros médicos de forma segura.', 'patient-management-system' ),
        ), $atts, 'pms_patient_portal' );

        ob_start();
        ?>
        <div class="pms-patient-portal">
            <div class="pms-portal-header">
                <h1><?php echo esc_html( $atts['title'] ); ?></h1>
                <p><?php echo esc_html( $atts['description'] ); ?></p>
            </div>

            <div class="pms-portal-content">
                <div class="pms-login-section">
                    <h2><?php _e( 'Acesso aos Registros Médicos', 'patient-management-system' ); ?></h2>
                    <p><?php _e( 'Para acessar seus registros médicos, você precisa do ID do paciente e sua data de nascimento.', 'patient-management-system' ); ?></p>
                    
                    <form id="pms-patient-login-form" class="pms-login-form">
                        <div class="pms-form-group">
                            <label for="patient_id"><?php _e( 'ID do Paciente:', 'patient-management-system' ); ?></label>
                            <input type="number" id="patient_id" name="patient_id" required 
                                   placeholder="<?php _e( 'Digite seu ID de paciente', 'patient-management-system' ); ?>">
                        </div>

                        <div class="pms-form-group">
                            <label for="patient_dob"><?php _e( 'Data de Nascimento:', 'patient-management-system' ); ?></label>
                            <input type="date" id="patient_dob" name="patient_dob" required>
                        </div>

                        <button type="submit" class="pms-btn pms-btn-primary">
                            <?php _e( 'Buscar Registros', 'patient-management-system' ); ?>
                        </button>
                    </form>

                    <div id="pms-login-messages" class="pms-messages"></div>
                </div>

                <div id="pms-records-section" class="pms-records-section" style="display: none;">
                    <h2><?php _e( 'Seus Registros Médicos', 'patient-management-system' ); ?></h2>
                    <div id="pms-records-list" class="pms-records-list"></div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Patient search shortcode.
     *
     * @since    1.0.0
     * @param    array $atts Shortcode attributes.
     * @return   string The shortcode output.
     */
    public function patient_search_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'placeholder' => __( 'Digite o ID do paciente ou data de nascimento...', 'patient-management-system' ),
        ), $atts, 'pms_patient_search' );

        ob_start();
        ?>
        <div class="pms-patient-search-widget">
            <form id="pms-patient-search-form" class="pms-search-form">
                <div class="pms-search-input-group">
                    <input type="text" id="pms-search-input" name="search_term" 
                           placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>" required>
                    <button type="submit" class="pms-search-btn">
                        <?php _e( 'Buscar', 'patient-management-system' ); ?>
                    </button>
                </div>
            </form>
            <div id="pms-search-results" class="pms-search-results"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle patient login AJAX request.
     *
     * @since    1.0.0
     */
    public function handle_patient_login() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pms_patient_portal_nonce' ) ) {
            wp_die( __( 'Erro de segurança.', 'patient-management-system' ) );
        }

        $patient_id = intval( $_POST['patient_id'] );
        $patient_dob = sanitize_text_field( $_POST['patient_dob'] );

        // Validate patient credentials
        $patient = get_post( $patient_id );
        if ( ! $patient || $patient->post_type !== 'pms_patient' ) {
            wp_send_json_error( array( 'message' => __( 'Paciente não encontrado.', 'patient-management-system' ) ) );
        }

        $stored_dob = get_post_meta( $patient_id, '_patient_dob', true );
        if ( $stored_dob !== $patient_dob ) {
            wp_send_json_error( array( 'message' => __( 'Data de nascimento incorreta.', 'patient-management-system' ) ) );
        }

        // Get patient records
        $records = $this->get_patient_records( $patient_id );
        
        if ( empty( $records ) ) {
            wp_send_json_success( array( 
                'message' => __( 'Nenhum registro médico encontrado.', 'patient-management-system' ),
                'records' => array()
            ) );
        }

        wp_send_json_success( array( 
            'message' => __( 'Registros encontrados com sucesso.', 'patient-management-system' ),
            'records' => $records
        ) );
    }

    /**
     * Search patient records AJAX handler.
     *
     * @since    1.0.0
     */
    public function search_patient_records() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'pms_patient_portal_nonce' ) ) {
            wp_die( __( 'Erro de segurança.', 'patient-management-system' ) );
        }

        $search_term = sanitize_text_field( $_POST['search_term'] );
        
        // Search logic here - you can customize this based on your needs
        $results = array();
        
        // Search by patient ID if numeric
        if ( is_numeric( $search_term ) ) {
            $patient = get_post( intval( $search_term ) );
            if ( $patient && $patient->post_type === 'pms_patient' ) {
                $records = $this->get_patient_records( $patient->ID );
                if ( ! empty( $records ) ) {
                    $results = $records;
                }
            }
        }

        wp_send_json_success( array( 'results' => $results ) );
    }

    /**
     * Get patient records for display.
     *
     * @since    1.0.0
     * @param    int $patient_id The patient ID.
     * @return   array The patient records.
     */
    private function get_patient_records( $patient_id ) {
        $records = array();

        // Get all patient record posts for this patient
        $patient_record_posts = get_posts( array(
            'post_type' => 'pms_patient_record',
            'meta_query' => array(
                array(
                    'key' => '_pms_patient_id',
                    'value' => $patient_id,
                    'compare' => '='
                )
            ),
            'numberposts' => -1,
            'post_status' => 'publish'
        ) );

        foreach ( $patient_record_posts as $post ) {
            $record_type = get_post_meta( $post->ID, '_pms_record_type', true );
            $record_date = get_post_meta( $post->ID, '_pms_record_date', true );
            
            $records[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'type' => $record_type,
                'date' => $record_date,
                'url' => get_permalink( $post->ID ),
                'excerpt' => wp_trim_words( $post->post_content, 20 ),
                'formatted_date' => $record_date ? date( 'd/m/Y', strtotime( $record_date ) ) : '',
            );
        }

        // Sort by date (newest first)
        usort( $records, function( $a, $b ) {
            return strtotime( $b['date'] ) - strtotime( $a['date'] );
        } );

        return $records;
    }

    /**
     * Generate patient access instructions.
     *
     * @since    1.0.0
     * @param    int $patient_id The patient ID.
     * @return   string The access instructions.
     */
    public function generate_patient_access_instructions( $patient_id ) {
        $patient_dob = get_post_meta( $patient_id, '_patient_dob', true );
        $password = 'patient' . $patient_id . date( 'dmY', strtotime( $patient_dob ) );
        
        $instructions = sprintf(
            __( 'Para acessar seus registros médicos online:\n\n1. Acesse o portal do paciente em: %s\n2. Use seu ID de paciente: %d\n3. Use sua data de nascimento: %s\n4. Para acessar registros específicos, use a senha: %s\n\nMantenha essas informações em segurança.', 'patient-management-system' ),
            home_url( '/portal-paciente/' ),
            $patient_id,
            date( 'd/m/Y', strtotime( $patient_dob ) ),
            $password
        );

        return $instructions;
    }
}

