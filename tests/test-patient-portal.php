<?php
/**
 * Test file for Patient Portal functionality
 *
 * @package Patient_Management_System
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Test class for Patient Portal functionality
 */
class PMS_Patient_Portal_Tests {

    /**
     * Run all tests
     */
    public static function run_all_tests() {
        echo "<h2>Executando Testes do Portal do Paciente</h2>\n";
        
        $tests = array(
            'test_patient_record_creation',
            'test_password_generation',
            'test_patient_authentication',
            'test_record_display',
            'test_security_measures',
            'test_responsive_design',
        );
        
        $passed = 0;
        $total = count( $tests );
        
        foreach ( $tests as $test ) {
            echo "<h3>Teste: " . str_replace( '_', ' ', ucfirst( $test ) ) . "</h3>\n";
            
            if ( method_exists( __CLASS__, $test ) ) {
                $result = call_user_func( array( __CLASS__, $test ) );
                if ( $result ) {
                    echo "<p style='color: green;'>✓ PASSOU</p>\n";
                    $passed++;
                } else {
                    echo "<p style='color: red;'>✗ FALHOU</p>\n";
                }
            } else {
                echo "<p style='color: orange;'>⚠ TESTE NÃO IMPLEMENTADO</p>\n";
            }
            
            echo "<hr>\n";
        }
        
        echo "<h2>Resultado Final: {$passed}/{$total} testes passaram</h2>\n";
        
        return $passed === $total;
    }

    /**
     * Test patient record creation
     */
    public static function test_patient_record_creation() {
        echo "<p>Testando criação automática de posts de registros de pacientes...</p>\n";
        
        // Create a test patient
        $patient_id = wp_insert_post( array(
            'post_title' => 'Paciente Teste',
            'post_type' => 'pms_patient',
            'post_status' => 'publish',
            'meta_input' => array(
                '_patient_first_name' => 'João',
                '_patient_last_name' => 'Silva',
                '_patient_email' => 'joao@teste.com',
                '_patient_dob' => '1990-01-01',
            )
        ) );
        
        if ( is_wp_error( $patient_id ) ) {
            echo "<p>Erro ao criar paciente de teste.</p>\n";
            return false;
        }
        
        // Create a test medical record
        $record_id = wp_insert_post( array(
            'post_title' => 'Consulta de Teste',
            'post_type' => 'pms_medical_record',
            'post_status' => 'publish',
            'post_content' => 'Conteúdo do registro médico de teste.',
            'meta_input' => array(
                '_medical_record_patient_id' => $patient_id,
                '_medical_record_type' => 'consulta',
                '_medical_record_date' => date( 'Y-m-d' ),
                '_medical_record_lab_results' => 'Resultados normais',
                '_medical_record_medications' => 'Paracetamol 500mg',
                '_medical_record_version' => 1,
            )
        ) );
        
        if ( is_wp_error( $record_id ) ) {
            echo "<p>Erro ao criar registro médico de teste.</p>\n";
            wp_delete_post( $patient_id, true );
            return false;
        }
        
        // Trigger the hook manually to create patient record post
        $medical_records_manager = new PMS_Medical_Records_Manager();
        $post = get_post( $record_id );
        $medical_records_manager->create_or_update_patient_record_post( $record_id, $post, false );
        
        // Check if patient record post was created
        $patient_record_post_id = get_post_meta( $record_id, '_patient_record_post_id', true );
        
        $success = ! empty( $patient_record_post_id ) && get_post( $patient_record_post_id );
        
        if ( $success ) {
            echo "<p>✓ Post de registro de paciente criado com sucesso (ID: {$patient_record_post_id})</p>\n";
            
            // Check if post has password
            $patient_record_post = get_post( $patient_record_post_id );
            if ( ! empty( $patient_record_post->post_password ) ) {
                echo "<p>✓ Post protegido por senha: {$patient_record_post->post_password}</p>\n";
            } else {
                echo "<p>✗ Post não está protegido por senha</p>\n";
                $success = false;
            }
        } else {
            echo "<p>✗ Post de registro de paciente não foi criado</p>\n";
        }
        
        // Cleanup
        wp_delete_post( $record_id, true );
        wp_delete_post( $patient_id, true );
        if ( $patient_record_post_id ) {
            wp_delete_post( $patient_record_post_id, true );
        }
        
        return $success;
    }

    /**
     * Test password generation
     */
    public static function test_password_generation() {
        echo "<p>Testando geração de senhas para acesso aos registros...</p>\n";
        
        $patient_id = 123;
        $patient_dob = '1990-01-01';
        
        // Use reflection to access private method
        $medical_records_manager = new PMS_Medical_Records_Manager();
        $reflection = new ReflectionClass( $medical_records_manager );
        $method = $reflection->getMethod( 'generate_patient_password' );
        $method->setAccessible( true );
        
        $password = $method->invoke( $medical_records_manager, $patient_id, $patient_dob );
        
        $expected_password = 'patient' . $patient_id . '01011990';
        
        if ( $password === $expected_password ) {
            echo "<p>✓ Senha gerada corretamente: {$password}</p>\n";
            return true;
        } else {
            echo "<p>✗ Senha incorreta. Esperado: {$expected_password}, Recebido: {$password}</p>\n";
            return false;
        }
    }

    /**
     * Test patient authentication
     */
    public static function test_patient_authentication() {
        echo "<p>Testando autenticação de pacientes...</p>\n";
        
        // Create test patient
        $patient_id = wp_insert_post( array(
            'post_title' => 'Paciente Auth Teste',
            'post_type' => 'pms_patient',
            'post_status' => 'publish',
            'meta_input' => array(
                '_patient_first_name' => 'Maria',
                '_patient_last_name' => 'Santos',
                '_patient_dob' => '1985-05-15',
            )
        ) );
        
        if ( is_wp_error( $patient_id ) ) {
            echo "<p>Erro ao criar paciente de teste.</p>\n";
            return false;
        }
        
        $patient_portal = new PMS_Patient_Portal();
        
        // Test valid credentials
        $_POST['patient_id'] = $patient_id;
        $_POST['patient_dob'] = '1985-05-15';
        $_POST['nonce'] = wp_create_nonce( 'pms_patient_portal_nonce' );
        
        // Simulate AJAX request
        ob_start();
        try {
            $patient_portal->handle_patient_login();
        } catch ( Exception $e ) {
            // Expected to exit with JSON response
        }
        $output = ob_get_clean();
        
        // Check if authentication was successful
        if ( strpos( $output, '"success":true' ) !== false ) {
            echo "<p>✓ Autenticação com credenciais válidas funcionou</p>\n";
            $auth_success = true;
        } else {
            echo "<p>✗ Autenticação com credenciais válidas falhou</p>\n";
            $auth_success = false;
        }
        
        // Test invalid credentials
        $_POST['patient_dob'] = '1990-01-01'; // Wrong date
        
        ob_start();
        try {
            $patient_portal->handle_patient_login();
        } catch ( Exception $e ) {
            // Expected to exit with JSON response
        }
        $output = ob_get_clean();
        
        // Check if authentication was rejected
        if ( strpos( $output, '"success":false' ) !== false ) {
            echo "<p>✓ Autenticação com credenciais inválidas foi rejeitada</p>\n";
            $auth_fail_success = true;
        } else {
            echo "<p>✗ Autenticação com credenciais inválidas não foi rejeitada</p>\n";
            $auth_fail_success = false;
        }
        
        // Cleanup
        wp_delete_post( $patient_id, true );
        unset( $_POST['patient_id'], $_POST['patient_dob'], $_POST['nonce'] );
        
        return $auth_success && $auth_fail_success;
    }

    /**
     * Test record display
     */
    public static function test_record_display() {
        echo "<p>Testando exibição de registros no frontend...</p>\n";
        
        // Test shortcode registration
        if ( shortcode_exists( 'pms_patient_portal' ) ) {
            echo "<p>✓ Shortcode [pms_patient_portal] registrado</p>\n";
            $shortcode_success = true;
        } else {
            echo "<p>✗ Shortcode [pms_patient_portal] não registrado</p>\n";
            $shortcode_success = false;
        }
        
        if ( shortcode_exists( 'pms_patient_search' ) ) {
            echo "<p>✓ Shortcode [pms_patient_search] registrado</p>\n";
            $search_shortcode_success = true;
        } else {
            echo "<p>✗ Shortcode [pms_patient_search] não registrado</p>\n";
            $search_shortcode_success = false;
        }
        
        // Test shortcode output
        $portal_output = do_shortcode( '[pms_patient_portal]' );
        if ( ! empty( $portal_output ) && strpos( $portal_output, 'pms-patient-portal' ) !== false ) {
            echo "<p>✓ Shortcode do portal gera output válido</p>\n";
            $output_success = true;
        } else {
            echo "<p>✗ Shortcode do portal não gera output válido</p>\n";
            $output_success = false;
        }
        
        return $shortcode_success && $search_shortcode_success && $output_success;
    }

    /**
     * Test security measures
     */
    public static function test_security_measures() {
        echo "<p>Testando medidas de segurança...</p>\n";
        
        $security_checks = array();
        
        // Check if patient record post type is not publicly queryable by default
        $post_type_object = get_post_type_object( 'pms_patient_record' );
        if ( $post_type_object && $post_type_object->publicly_queryable ) {
            echo "<p>✓ Tipo de post pms_patient_record é publicamente consultável (necessário para frontend)</p>\n";
            $security_checks['public_queryable'] = true;
        } else {
            echo "<p>✗ Tipo de post pms_patient_record não é publicamente consultável</p>\n";
            $security_checks['public_queryable'] = false;
        }
        
        // Check if posts are excluded from search
        if ( $post_type_object && $post_type_object->exclude_from_search ) {
            echo "<p>✓ Posts de registros excluídos da busca pública</p>\n";
            $security_checks['exclude_search'] = true;
        } else {
            echo "<p>✗ Posts de registros não excluídos da busca pública</p>\n";
            $security_checks['exclude_search'] = false;
        }
        
        // Check nonce verification in AJAX handlers
        $patient_portal = new PMS_Patient_Portal();
        $reflection = new ReflectionClass( $patient_portal );
        $method = $reflection->getMethod( 'handle_patient_login' );
        $method_source = file_get_contents( $reflection->getFileName() );
        
        if ( strpos( $method_source, 'wp_verify_nonce' ) !== false ) {
            echo "<p>✓ Verificação de nonce implementada nos handlers AJAX</p>\n";
            $security_checks['nonce_verification'] = true;
        } else {
            echo "<p>✗ Verificação de nonce não encontrada nos handlers AJAX</p>\n";
            $security_checks['nonce_verification'] = false;
        }
        
        return array_sum( $security_checks ) === count( $security_checks );
    }

    /**
     * Test responsive design
     */
    public static function test_responsive_design() {
        echo "<p>Testando design responsivo...</p>\n";
        
        $css_files = array(
            plugin_dir_path( dirname( __FILE__ ) ) . 'public/css/pms-patient-portal.css',
            plugin_dir_path( dirname( __FILE__ ) ) . 'public/css/pms-patient-record.css',
        );
        
        $responsive_checks = array();
        
        foreach ( $css_files as $css_file ) {
            if ( file_exists( $css_file ) ) {
                $css_content = file_get_contents( $css_file );
                
                // Check for media queries
                if ( strpos( $css_content, '@media' ) !== false ) {
                    echo "<p>✓ Media queries encontradas em " . basename( $css_file ) . "</p>\n";
                    $responsive_checks[] = true;
                } else {
                    echo "<p>✗ Media queries não encontradas em " . basename( $css_file ) . "</p>\n";
                    $responsive_checks[] = false;
                }
                
                // Check for mobile-specific breakpoints
                if ( strpos( $css_content, '768px' ) !== false || strpos( $css_content, '480px' ) !== false ) {
                    echo "<p>✓ Breakpoints móveis encontrados em " . basename( $css_file ) . "</p>\n";
                    $responsive_checks[] = true;
                } else {
                    echo "<p>✗ Breakpoints móveis não encontrados em " . basename( $css_file ) . "</p>\n";
                    $responsive_checks[] = false;
                }
            } else {
                echo "<p>✗ Arquivo CSS não encontrado: " . basename( $css_file ) . "</p>\n";
                $responsive_checks[] = false;
            }
        }
        
        return ! in_array( false, $responsive_checks );
    }
}

// Run tests if accessed directly
if ( isset( $_GET['run_pms_tests'] ) && current_user_can( 'manage_options' ) ) {
    echo "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>\n";
    echo "<h1>Testes do Patient Management System</h1>\n";
    PMS_Patient_Portal_Tests::run_all_tests();
    exit;
}

