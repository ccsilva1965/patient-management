<?php
/**
 * Patient management functionality
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 */

/**
 * Patient management class.
 *
 * This class defines all code necessary to manage patients.
 *
 * @since      1.0.0
 * @package    Patient_Management_System
 * @subpackage Patient_Management_System/includes
 * @author     Manus AI <#>
 */
class PMS_Patient_Manager {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        add_action( 'init', array( $this, 'register_patient_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_patient_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_patient_meta' ) );
        add_filter( 'manage_pms_patient_posts_columns', array( $this, 'set_custom_edit_patient_columns' ) );
        add_action( 'manage_pms_patient_posts_custom_column', array( $this, 'custom_patient_column' ), 10, 2 );
    }

    /**
     * Register the Patient custom post type.
     *
     * @since    1.0.0
     */
    public function register_patient_post_type() {
        $labels = array(
            'name'                  => _x( 'Pacientes', 'Post type general name', 'patient-management-system' ),
            'singular_name'         => _x( 'Paciente', 'Post type singular name', 'patient-management-system' ),
            'menu_name'             => _x( 'Pacientes', 'Admin Menu text', 'patient-management-system' ),
            'name_admin_bar'        => _x( 'Paciente', 'Add New on Toolbar', 'patient-management-system' ),
            'add_new'               => __( 'Adicionar Novo', 'patient-management-system' ),
            'add_new_item'          => __( 'Adicionar Novo Paciente', 'patient-management-system' ),
            'new_item'              => __( 'Novo Paciente', 'patient-management-system' ),
            'edit_item'             => __( 'Editar Paciente', 'patient-management-system' ),
            'view_item'             => __( 'Ver Paciente', 'patient-management-system' ),
            'all_items'             => __( 'Todos os Pacientes', 'patient-management-system' ),
            'search_items'          => __( 'Buscar Pacientes', 'patient-management-system' ),
            'parent_item_colon'     => __( 'Pacientes Pai:', 'patient-management-system' ),
            'not_found'             => __( 'Nenhum paciente encontrado.', 'patient-management-system' ),
            'not_found_in_trash'    => __( 'Nenhum paciente encontrado na lixeira.', 'patient-management-system' ),
            'featured_image'        => _x( 'Foto do Paciente', 'Overrides the "Featured Image" phrase for this post type. Added in 4.3', 'patient-management-system' ),
            'set_featured_image'    => _x( 'Definir foto do paciente', 'Overrides the "Set featured image" phrase for this post type. Added in 4.3', 'patient-management-system' ),
            'remove_featured_image' => _x( 'Remover foto do paciente', 'Overrides the "Remove featured image" phrase for this post type. Added in 4.3', 'patient-management-system' ),
            'use_featured_image'    => _x( 'Usar como foto do paciente', 'Overrides the "Use as featured image" phrase for this post type. Added in 4.3', 'patient-management-system' ),
            'archives'              => _x( 'Arquivo de Pacientes', 'The post type archive label used in nav menus. Default "Post Archives". Added in 4.4', 'patient-management-system' ),
            'insert_into_item'      => _x( 'Inserir no paciente', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post). Added in 4.4', 'patient-management-system' ),
            'uploaded_to_this_item' => _x( 'Enviado para este paciente', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post). Added in 4.4', 'patient-management-system' ),
            'filter_items_list'     => _x( 'Filtrar lista de pacientes', 'Screen reader text for the filter links heading on the post type listing screen. Default "Filter posts list"/"Filter pages list". Added in 4.4', 'patient-management-system' ),
            'items_list_navigation' => _x( 'Navegação da lista de pacientes', 'Screen reader text for the pagination heading on the post type listing screen. Default "Posts list navigation"/"Pages list navigation". Added in 4.4', 'patient-management-system' ),
            'items_list'            => _x( 'Lista de pacientes', 'Screen reader text for the items list heading on the post type listing screen. Default "Posts list"/"Pages list". Added in 4.4', 'patient-management-system' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'patient' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => array( 'title', 'thumbnail' ),
        );

        register_post_type( 'pms_patient', $args );
    }

    /**
     * Add meta boxes for patient information.
     *
     * @since    1.0.0
     */
    public function add_patient_meta_boxes() {
        add_meta_box(
            'patient-info',
            __( 'Informações do Paciente', 'patient-management-system' ),
            array( $this, 'patient_info_meta_box_callback' ),
            'pms_patient'
        );
    }

    /**
     * Patient information meta box callback.
     *
     * @since    1.0.0
     * @param    WP_Post $post The post object.
     */
    public function patient_info_meta_box_callback( $post ) {
        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'patient_info_meta_box', 'patient_info_meta_box_nonce' );

        // Use get_post_meta() to retrieve an existing value from the database.
        $first_name = get_post_meta( $post->ID, '_patient_first_name', true );
        $last_name = get_post_meta( $post->ID, '_patient_last_name', true );
        $dob = get_post_meta( $post->ID, '_patient_dob', true );
        $gender = get_post_meta( $post->ID, '_patient_gender', true );
        $address = get_post_meta( $post->ID, '_patient_address', true );
        $phone = get_post_meta( $post->ID, '_patient_phone', true );
        $email = get_post_meta( $post->ID, '_patient_email', true );

        // Display the form, using the current value.
        echo '<table class="form-table">';
        echo '<tr>';
        echo '<th><label for="patient_first_name">' . __( 'Nome', 'patient-management-system' ) . '</label></th>';
        echo '<td><input type="text" id="patient_first_name" name="patient_first_name" value="' . esc_attr( $first_name ) . '" size="25" /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="patient_last_name">' . __( 'Sobrenome', 'patient-management-system' ) . '</label></th>';
        echo '<td><input type="text" id="patient_last_name" name="patient_last_name" value="' . esc_attr( $last_name ) . '" size="25" /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="patient_dob">' . __( 'Data de Nascimento', 'patient-management-system' ) . '</label></th>';
        echo '<td><input type="date" id="patient_dob" name="patient_dob" value="' . esc_attr( $dob ) . '" /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="patient_gender">' . __( 'Gênero', 'patient-management-system' ) . '</label></th>';
        echo '<td>';
        echo '<select id="patient_gender" name="patient_gender">';
        echo '<option value="">' . __( 'Selecione', 'patient-management-system' ) . '</option>';
        echo '<option value="masculino"' . selected( $gender, 'masculino', false ) . '>' . __( 'Masculino', 'patient-management-system' ) . '</option>';
        echo '<option value="feminino"' . selected( $gender, 'feminino', false ) . '>' . __( 'Feminino', 'patient-management-system' ) . '</option>';
        echo '<option value="outro"' . selected( $gender, 'outro', false ) . '>' . __( 'Outro', 'patient-management-system' ) . '</option>';
        echo '</select>';
        echo '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="patient_address">' . __( 'Endereço', 'patient-management-system' ) . '</label></th>';
        echo '<td><textarea id="patient_address" name="patient_address" rows="3" cols="50">' . esc_textarea( $address ) . '</textarea></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="patient_phone">' . __( 'Telefone', 'patient-management-system' ) . '</label></th>';
        echo '<td><input type="tel" id="patient_phone" name="patient_phone" value="' . esc_attr( $phone ) . '" size="25" /></td>';
        echo '</tr>';
        echo '<tr>';
        echo '<th><label for="patient_email">' . __( 'Email', 'patient-management-system' ) . '</label></th>';
        echo '<td><input type="email" id="patient_email" name="patient_email" value="' . esc_attr( $email ) . '" size="25" /></td>';
        echo '</tr>';
        echo '</table>';
    }

    /**
     * Save patient meta data.
     *
     * @since    1.0.0
     * @param    int $post_id The post ID.
     */
    public function save_patient_meta( $post_id ) {
        // Check if our nonce is set.
        if ( ! isset( $_POST['patient_info_meta_box_nonce'] ) ) {
            return;
        }

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['patient_info_meta_box_nonce'], 'patient_info_meta_box' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'pms_patient' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        // Sanitize user input.
        $first_name = sanitize_text_field( $_POST['patient_first_name'] );
        $last_name = sanitize_text_field( $_POST['patient_last_name'] );
        $dob = sanitize_text_field( $_POST['patient_dob'] );
        $gender = sanitize_text_field( $_POST['patient_gender'] );
        $address = sanitize_textarea_field( $_POST['patient_address'] );
        $phone = sanitize_text_field( $_POST['patient_phone'] );
        $email = sanitize_email( $_POST['patient_email'] );

        // Update the meta field in the database.
        update_post_meta( $post_id, '_patient_first_name', $first_name );
        update_post_meta( $post_id, '_patient_last_name', $last_name );
        update_post_meta( $post_id, '_patient_dob', $dob );
        update_post_meta( $post_id, '_patient_gender', $gender );
        update_post_meta( $post_id, '_patient_address', $address );
        update_post_meta( $post_id, '_patient_phone', $phone );
        update_post_meta( $post_id, '_patient_email', $email );
    }

    /**
     * Set custom columns for patient list.
     *
     * @since    1.0.0
     * @param    array $columns The columns array.
     * @return   array The modified columns array.
     */
    public function set_custom_edit_patient_columns( $columns ) {
        unset( $columns['date'] );
        $columns['patient_name'] = __( 'Nome Completo', 'patient-management-system' );
        $columns['patient_dob'] = __( 'Data de Nascimento', 'patient-management-system' );
        $columns['patient_gender'] = __( 'Gênero', 'patient-management-system' );
        $columns['patient_phone'] = __( 'Telefone', 'patient-management-system' );
        $columns['patient_email'] = __( 'Email', 'patient-management-system' );
        $columns['date'] = __( 'Data', 'patient-management-system' );

        return $columns;
    }

    /**
     * Display custom column content.
     *
     * @since    1.0.0
     * @param    string $column  The column name.
     * @param    int    $post_id The post ID.
     */
    public function custom_patient_column( $column, $post_id ) {
        switch ( $column ) {
            case 'patient_name':
                $first_name = get_post_meta( $post_id, '_patient_first_name', true );
                $last_name = get_post_meta( $post_id, '_patient_last_name', true );
                echo esc_html( $first_name . ' ' . $last_name );
                break;

            case 'patient_dob':
                $dob = get_post_meta( $post_id, '_patient_dob', true );
                if ( $dob ) {
                    echo esc_html( date( 'd/m/Y', strtotime( $dob ) ) );
                }
                break;

            case 'patient_gender':
                $gender = get_post_meta( $post_id, '_patient_gender', true );
                echo esc_html( ucfirst( $gender ) );
                break;

            case 'patient_phone':
                $phone = get_post_meta( $post_id, '_patient_phone', true );
                echo esc_html( $phone );
                break;

            case 'patient_email':
                $email = get_post_meta( $post_id, '_patient_email', true );
                echo esc_html( $email );
                break;
        }
    }
}

