<?php
/**
 * Medical records management functionality
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 */

/**
 * Medical records management class.
 *
 * This class defines all code necessary to manage medical records.
 *
 * @since      1.0.0
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 * @author     Manus AI <#>
 */
class PMS_Medical_Records_Manager {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_medical_record_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_medical_record_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_medical_record_meta' ) );
        add_filter( 'manage_pms_medical_record_posts_columns', array( $this, 'set_custom_edit_medical_record_columns' ) );
        add_action( 'manage_pms_medical_record_posts_custom_column', array( $this, 'custom_medical_record_column' ), 10, 2 );
        
        // New hooks for patient record posts
        add_action( 'save_post_pms_medical_record', array( $this, 'create_or_update_patient_record_post' ), 10, 3 );
        add_action( 'delete_post', array( $this, 'delete_patient_record_post' ) );
        add_action( 'init', array( $this, 'register_patient_record_post_type' ) );
        add_filter( 'the_content', array( $this, 'display_patient_record_content' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_patient_record_styles' ) );
        add_filter( 'single_template', array( $this, 'load_patient_record_template' ) );
    }

    /**
     * Register the Medical Record custom post type.
     *
     * @since    1.0.0
     */
    public function register_medical_record_post_type() {
        $labels = array(
            'name'                  => _x( 'Registros Médicos', 'Post type general name', 'patient-management-system' ),
            'singular_name'         => _x( 'Registro Médico', 'Post type singular name', 'patient-management-system' ),
            'menu_name'             => _x( 'Registros Médicos', 'Admin Menu text', 'patient-management-system' ),
            'name_admin_bar'        => _x( 'Registro Médico', 'Add New on Toolbar', 'patient-management-system' ),
            'add_new'               => __( 'Adicionar Novo', 'patient-management-system' ),
            'add_new_item'          => __( 'Adicionar Novo Registro', 'patient-management-system' ),
            'new_item'              => __( 'Novo Registro', 'patient-management-system' ),
            'edit_item'             => __( 'Editar Registro', 'patient-management-system' ),
            'view_item'             => __( 'Ver Registro', 'patient-management-system' ),
            'all_items'             => __( 'Todos os Registros', 'patient-management-system' ),
            'search_items'          => __( 'Buscar Registros', 'patient-management-system' ),
            'parent_item_colon'     => __( 'Registros Pai:', 'patient-management-system' ),
            'not_found'             => __( 'Nenhum registro encontrado.', 'patient-management-system' ),
            'not_found_in_trash'    => __( 'Nenhum registro encontrado na lixeira.', 'patient-management-system' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'medical-record' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => array( 'title', 'editor' ),
        );

        register_post_type( 'pms_medical_record', $args );
    }

    /**
     * Add meta boxes for medical record information.
     *
     * @since    1.0.0
     */
    public function add_medical_record_meta_boxes() {
        add_meta_box(
            'medical-record-info',
            __( 'Informações do Registro Médico', 'patient-management-system' ),
            array( $this, 'medical_record_info_meta_box_callback' ),
            'pms_medical_record'
        );
    }

    /**
     * Medical record information meta box callback.
     *
     * @since    1.0.0
     * @param    WP_Post $post The post object.
     */
    public function medical_record_info_meta_box_callback( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'medical_record_info_meta_box', 'medical_record_info_meta_box_nonce' );

        // Use get_post_meta() to retrieve an existing value from the database.
        $patient_id = get_post_meta( $post->ID, '_medical_record_patient_id', true );
        $record_type = get_post_meta( $post->ID, '_medical_record_type', true );
        $record_date = get_post_meta( $post->ID, '_medical_record_date', true );
        $lab_results = get_post_meta( $post->ID, '_medical_record_lab_results', true );
        $medications = get_post_meta( $post->ID, '_medical_record_medications', true );
        $version = get_post_meta( $post->ID, '_medical_record_version', true );

        if ( empty( $version ) ) {
            $version = 1;
        }

        // Get all patients for dropdown
        $patients = get_posts( array(
            'post_type' => 'pms_patient',
            'numberposts' => -1,
            'post_status' => 'publish'
        ) );

        // Display the form, using the current value.
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="medical_record_patient_id">' . __( 'Paciente', 'patient-management-system' ) . '</label></th>';
        echo '<td>';
        echo '<select id="medical_record_patient_id" name="medical_record_patient_id" required>';
        echo '<option value="">' . __( 'Selecione um paciente', 'patient-management-system' ) . '</option>';
        foreach ( $patients as $patient ) {
            $first_name = get_post_meta( $patient->ID, '_patient_first_name', true );
            $last_name = get_post_meta( $patient->ID, '_patient_last_name', true );
            $patient_name = $first_name . ' ' . $last_name;
            echo '<option value="' . $patient->ID . '"' . selected( $patient_id, $patient->ID, false ) . '>' . esc_html( $patient_name ) . '</option>';
        }
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="medical_record_type">' . __( 'Tipo de Registro', 'patient-management-system' ) . '</label></th>';
        echo '<td>';
        echo '<select id="medical_record_type" name="medical_record_type" required>';
        echo '<option value="">' . __( 'Selecione', 'patient-management-system' ) . '</option>';
        echo '<option value="consulta"' . selected( $record_type, 'consulta', false ) . '>' . __( 'Consulta', 'patient-management-system' ) . '</option>';
        echo '<option value="exame"' . selected( $record_type, 'exame', false ) . '>' . __( 'Exame', 'patient-management-system' ) . '</option>';
        echo '<option value="procedimento"' . selected( $record_type, 'procedimento', false ) . '>' . __( 'Procedimento', 'patient-management-system' ) . '</option>';
        echo '<option value="internacao"' . selected( $record_type, 'internacao', false ) . '>' . __( 'Internação', 'patient-management-system' ) . '</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="medical_record_date">' . __( 'Data do Registro', 'patient-management-system' ) . '</label></th>';
        echo '<td><input type="date" id="medical_record_date" name="medical_record_date" value="' . esc_attr( $record_date ) . '" required /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="medical_record_lab_results">' . __( 'Resultados de Laboratório', 'patient-management-system' ) . '</label></th>';
        echo '<td><textarea id="medical_record_lab_results" name="medical_record_lab_results" rows="5" cols="50">' . esc_textarea( $lab_results ) . '</textarea></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="medical_record_medications">' . __( 'Medicamentos', 'patient-management-system' ) . '</label></th>';
        echo '<td><textarea id="medical_record_medications" name="medical_record_medications" rows="5" cols="50">' . esc_textarea( $medications ) . '</textarea></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="medical_record_version">' . __( 'Versão', 'patient-management-system' ) . '</label></th>';
        echo '<td><input type="number" id="medical_record_version" name="medical_record_version" value="' . esc_attr( $version ) . '" min="1" readonly /></td>';
        echo '</tr>';
        echo '</table>';
    }

    /**
     * Save medical record meta data.
     *
     * @since    1.0.0
     * @param    int $post_id The post ID.
     */
    public function save_medical_record_meta( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['medical_record_info_meta_box_nonce'] ) ) {
            return;
        }

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['medical_record_info_meta_box_nonce'], 'medical_record_info_meta_box' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'pms_medical_record' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        // Check if this is an update and increment version
        $current_version = get_post_meta( $post_id, '_medical_record_version', true );
        if ( empty( $current_version ) ) {
            $new_version = 1;
        } else {
            // Only increment version if content has changed
            $old_content = get_post_meta( $post_id, '_medical_record_lab_results', true ) . get_post_meta( $post_id, '_medical_record_medications', true );
            $new_content = sanitize_textarea_field( $_POST['medical_record_lab_results'] ) . sanitize_textarea_field( $_POST['medical_record_medications'] );
            
            if ( $old_content !== $new_content ) {
                $new_version = intval( $current_version ) + 1;
            } else {
                $new_version = intval( $current_version );
            }
        }

        // Sanitize user input.
        $patient_id = intval( $_POST['medical_record_patient_id'] );
        $record_type = sanitize_text_field( $_POST['medical_record_type'] );
        $record_date = sanitize_text_field( $_POST['medical_record_date'] );
        $lab_results = sanitize_textarea_field( $_POST['medical_record_lab_results'] );
        $medications = sanitize_textarea_field( $_POST['medical_record_medications'] );

        // Update the meta field in the database.
        update_post_meta( $post_id, '_medical_record_patient_id', $patient_id );
        update_post_meta( $post_id, '_medical_record_type', $record_type );
        update_post_meta( $post_id, '_medical_record_date', $record_date );
        update_post_meta( $post_id, '_medical_record_lab_results', $lab_results );
        update_post_meta( $post_id, '_medical_record_medications', $medications );
        update_post_meta( $post_id, '_medical_record_version', $new_version );
    }

    /**
     * Set custom columns for medical record list.
     *
     * @since    1.0.0
     * @param    array $columns The columns array.
     * @return   array The modified columns array.
     */
    public function set_custom_edit_medical_record_columns( $columns ) {
        unset( $columns['date'] );
        $columns['record_patient'] = __( 'Paciente', 'patient-management-system' );
        $columns['record_type'] = __( 'Tipo', 'patient-management-system' );
        $columns['record_date'] = __( 'Data do Registro', 'patient-management-system' );
        $columns['record_version'] = __( 'Versão', 'patient-management-system' );
        $columns['date'] = __( 'Data de Criação', 'patient-management-system' );

        return $columns;
    }

    /**
     * Display custom column content.
     *
     * @since    1.0.0
     * @param    string $column  The column name.
     * @param    int    $post_id The post ID.
     */
    public function custom_medical_record_column( $column, $post_id ) {
        switch ( $column ) {
            case 'record_patient':
                $patient_id = get_post_meta( $post_id, '_medical_record_patient_id', true );
                if ( $patient_id ) {
                    $first_name = get_post_meta( $patient_id, '_patient_first_name', true );
                    $last_name = get_post_meta( $patient_id, '_patient_last_name', true );
                    echo esc_html( $first_name . ' ' . $last_name );
                }
                break;

            case 'record_type':
                $record_type = get_post_meta( $post_id, '_medical_record_type', true );
                echo esc_html( ucfirst( $record_type ) );
                break;

            case 'record_date':
                $record_date = get_post_meta( $post_id, '_medical_record_date', true );
                if ( $record_date ) {
                    echo esc_html( date( 'd/m/Y', strtotime( $record_date ) ) );
                }
                break;

            case 'record_version':
                $version = get_post_meta( $post_id, '_medical_record_version', true );
                echo esc_html( 'v' . $version );
                break;
        }
    }



    /**
     * Register the Patient Record custom post type for frontend display.
     *
     * @since    1.0.0
     */
    public function register_patient_record_post_type() {
        $labels = array(
            'name'                  => _x( 'Registros de Pacientes', 'Post type general name', 'patient-management-system' ),
            'singular_name'         => _x( 'Registro de Paciente', 'Post type singular name', 'patient-management-system' ),
            'menu_name'             => _x( 'Registros de Pacientes', 'Admin Menu text', 'patient-management-system' ),
            'name_admin_bar'        => _x( 'Registro de Paciente', 'Add New on Toolbar', 'patient-management-system' ),
            'add_new'               => __( 'Adicionar Novo', 'patient-management-system' ),
            'add_new_item'          => __( 'Adicionar Novo Registro', 'patient-management-system' ),
            'new_item'              => __( 'Novo Registro', 'patient-management-system' ),
            'edit_item'             => __( 'Editar Registro', 'patient-management-system' ),
            'view_item'             => __( 'Ver Registro', 'patient-management-system' ),
            'all_items'             => __( 'Todos os Registros', 'patient-management-system' ),
            'search_items'          => __( 'Buscar Registros', 'patient-management-system' ),
            'parent_item_colon'     => __( 'Registros Pai:', 'patient-management-system' ),
            'not_found'             => __( 'Nenhum registro encontrado.', 'patient-management-system' ),
            'not_found_in_trash'    => __( 'Nenhum registro encontrado na lixeira.', 'patient-management-system' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => false, // Hide from admin menu
            'show_in_menu'       => false,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'patient-record' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor' ),
            'exclude_from_search' => true,
        );

        register_post_type( 'pms_patient_record', $args );
    }

    /**
     * Create or update a patient record post when a medical record is saved.
     *
     * @since    1.0.0
     * @param    int     $post_id The post ID.
     * @param    WP_Post $post    The post object.
     * @param    bool    $update  Whether this is an existing post being updated or not.
     */
    public function create_or_update_patient_record_post( $post_id, $post, $update ) {
        // Avoid infinite loops
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Get medical record data
        $patient_id = get_post_meta( $post_id, '_medical_record_patient_id', true );
        $record_type = get_post_meta( $post_id, '_medical_record_type', true );
        $record_date = get_post_meta( $post_id, '_medical_record_date', true );
        $lab_results = get_post_meta( $post_id, '_medical_record_lab_results', true );
        $medications = get_post_meta( $post_id, '_medical_record_medications', true );
        $version = get_post_meta( $post_id, '_medical_record_version', true );

        if ( empty( $patient_id ) ) {
            return;
        }

        // Get patient information
        $patient_first_name = get_post_meta( $patient_id, '_patient_first_name', true );
        $patient_last_name = get_post_meta( $patient_id, '_patient_last_name', true );
        $patient_email = get_post_meta( $patient_id, '_patient_email', true );
        $patient_phone = get_post_meta( $patient_id, '_patient_phone', true );
        $patient_dob = get_post_meta( $patient_id, '_patient_dob', true );

        // Check if patient record post already exists
        $existing_post_id = get_post_meta( $post_id, '_patient_record_post_id', true );
        
        // Generate password based on patient data (you can customize this logic)
        $password = $this->generate_patient_password( $patient_id, $patient_dob );

        // Prepare post content
        $content = $this->format_patient_record_content( array(
            'patient_name' => $patient_first_name . ' ' . $patient_last_name,
            'patient_email' => $patient_email,
            'patient_phone' => $patient_phone,
            'patient_dob' => $patient_dob,
            'record_type' => $record_type,
            'record_date' => $record_date,
            'lab_results' => $lab_results,
            'medications' => $medications,
            'version' => $version,
            'medical_record_title' => $post->post_title,
            'medical_record_content' => $post->post_content
        ) );

        $post_title = sprintf( 
            __( 'Registro Médico - %s - %s', 'patient-management-system' ), 
            $patient_first_name . ' ' . $patient_last_name,
            date( 'd/m/Y', strtotime( $record_date ) )
        );

        if ( $existing_post_id && get_post( $existing_post_id ) ) {
            // Update existing post
            $patient_record_post = array(
                'ID'           => $existing_post_id,
                'post_title'   => $post_title,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_password' => $password,
            );
            
            wp_update_post( $patient_record_post );
        } else {
            // Create new post
            $patient_record_post = array(
                'post_title'   => $post_title,
                'post_content' => $content,
                'post_status'  => 'publish',
                'post_type'    => 'pms_patient_record',
                'post_password' => $password,
                'meta_input'   => array(
                    '_pms_medical_record_id' => $post_id,
                    '_pms_patient_id' => $patient_id,
                    '_pms_record_type' => $record_type,
                    '_pms_record_date' => $record_date,
                )
            );

            $new_post_id = wp_insert_post( $patient_record_post );
            
            if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {
                // Store the relationship
                update_post_meta( $post_id, '_patient_record_post_id', $new_post_id );
            }
        }
    }

    /**
     * Delete patient record post when medical record is deleted.
     *
     * @since    1.0.0
     * @param    int $post_id The post ID being deleted.
     */
    public function delete_patient_record_post( $post_id ) {
        $post = get_post( $post_id );
        
        if ( $post && $post->post_type === 'pms_medical_record' ) {
            $patient_record_post_id = get_post_meta( $post_id, '_patient_record_post_id', true );
            
            if ( $patient_record_post_id ) {
                wp_delete_post( $patient_record_post_id, true );
            }
        }
    }

    /**
     * Generate a password for patient record access.
     *
     * @since    1.0.0
     * @param    int    $patient_id The patient ID.
     * @param    string $patient_dob The patient date of birth.
     * @return   string The generated password.
     */
    private function generate_patient_password( $patient_id, $patient_dob ) {
        // Simple password generation - you can make this more sophisticated
        // Using patient ID + formatted DOB (DDMMYYYY)
        $dob_formatted = '';
        if ( $patient_dob ) {
            $dob_formatted = date( 'dmY', strtotime( $patient_dob ) );
        }
        
        return 'patient' . $patient_id . $dob_formatted;
    }

    /**
     * Format patient record content for frontend display.
     *
     * @since    1.0.0
     * @param    array $data The patient and record data.
     * @return   string The formatted content.
     */
    private function format_patient_record_content( $data ) {
        $content = '<div class="pms-patient-record">';
        
        // Patient Information Section
        $content .= '<div class="pms-patient-info">';
        $content .= '<h2>' . __( 'Informações do Paciente', 'patient-management-system' ) . '</h2>';
        $content .= '<div class="pms-info-grid">';
        $content .= '<div class="pms-info-item"><strong>' . __( 'Nome:', 'patient-management-system' ) . '</strong> ' . esc_html( $data['patient_name'] ) . '</div>';
        if ( $data['patient_email'] ) {
            $content .= '<div class="pms-info-item"><strong>' . __( 'Email:', 'patient-management-system' ) . '</strong> ' . esc_html( $data['patient_email'] ) . '</div>';
        }
        if ( $data['patient_phone'] ) {
            $content .= '<div class="pms-info-item"><strong>' . __( 'Telefone:', 'patient-management-system' ) . '</strong> ' . esc_html( $data['patient_phone'] ) . '</div>';
        }
        if ( $data['patient_dob'] ) {
            $content .= '<div class="pms-info-item"><strong>' . __( 'Data de Nascimento:', 'patient-management-system' ) . '</strong> ' . date( 'd/m/Y', strtotime( $data['patient_dob'] ) ) . '</div>';
        }
        $content .= '</div>';
        $content .= '</div>';

        // Medical Record Section
        $content .= '<div class="pms-medical-record-info">';
        $content .= '<h2>' . __( 'Registro Médico', 'patient-management-system' ) . '</h2>';
        $content .= '<div class="pms-record-header">';
        $content .= '<div class="pms-record-meta">';
        $content .= '<span class="pms-record-type">' . esc_html( ucfirst( $data['record_type'] ) ) . '</span>';
        $content .= '<span class="pms-record-date">' . date( 'd/m/Y', strtotime( $data['record_date'] ) ) . '</span>';
        $content .= '<span class="pms-record-version">v' . esc_html( $data['version'] ) . '</span>';
        $content .= '</div>';
        $content .= '</div>';

        if ( $data['medical_record_title'] ) {
            $content .= '<h3>' . esc_html( $data['medical_record_title'] ) . '</h3>';
        }

        if ( $data['medical_record_content'] ) {
            $content .= '<div class="pms-record-content">' . wpautop( esc_html( $data['medical_record_content'] ) ) . '</div>';
        }

        if ( $data['lab_results'] ) {
            $content .= '<div class="pms-lab-results">';
            $content .= '<h4>' . __( 'Resultados de Laboratório', 'patient-management-system' ) . '</h4>';
            $content .= '<div class="pms-lab-content">' . wpautop( esc_html( $data['lab_results'] ) ) . '</div>';
            $content .= '</div>';
        }

        if ( $data['medications'] ) {
            $content .= '<div class="pms-medications">';
            $content .= '<h4>' . __( 'Medicamentos', 'patient-management-system' ) . '</h4>';
            $content .= '<div class="pms-medications-content">' . wpautop( esc_html( $data['medications'] ) ) . '</div>';
            $content .= '</div>';
        }

        $content .= '</div>';
        $content .= '</div>';

        return $content;
    }

    /**
     * Display custom content for patient record posts.
     *
     * @since    1.0.0
     * @param    string $content The post content.
     * @return   string The modified content.
     */
    public function display_patient_record_content( $content ) {
        global $post;
        
        if ( is_single() && $post->post_type === 'pms_patient_record' ) {
            // Add custom styling and structure
            $custom_content = '<div class="pms-patient-record-wrapper">';
            $custom_content .= $content;
            $custom_content .= '<div class="pms-record-footer">';
            $custom_content .= '<p class="pms-disclaimer">' . __( 'Este é um registro médico confidencial. Não compartilhe estas informações.', 'patient-management-system' ) . '</p>';
            $custom_content .= '<p class="pms-generated-date">' . sprintf( __( 'Gerado em: %s', 'patient-management-system' ), date( 'd/m/Y H:i' ) ) . '</p>';
            $custom_content .= '</div>';
            $custom_content .= '</div>';
            
            return $custom_content;
        }
        
        return $content;
    }

    /**
     * Enqueue styles for patient record posts.
     *
     * @since    1.0.0
     */
    public function enqueue_patient_record_styles() {
        global $post;
        
        if ( is_single() && $post && $post->post_type === 'pms_patient_record' ) {
            wp_enqueue_style( 
                'pms-patient-record-styles', 
                plugin_dir_url( dirname( __FILE__ ) ) . 'public/css/pms-patient-record.css',
                array(),
                '1.0.0'
            );
        }
    }


    /**
     * Load custom template for patient record posts.
     *
     * @since    1.0.0
     * @param    string $template The template path.
     * @return   string The modified template path.
     */
    public function load_patient_record_template( $template ) {
        global $post;
        
        if ( $post->post_type === 'pms_patient_record' && is_single() ) {
            $plugin_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/single-pms_patient_record.php';
            
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        
        return $template;
    }

