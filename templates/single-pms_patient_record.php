<?php
/**
 * Template for displaying single patient record posts
 *
 * @package Patient_Management_System
 * @since 1.0.0
 */

get_header(); ?>

<div class="pms-patient-record-template">
    <?php while ( have_posts() ) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'pms-patient-record-single' ); ?>>
            
            <!-- Header Section -->
            <header class="pms-record-header-section">
                <div class="pms-container">
                    <div class="pms-header-content">
                        <div class="pms-breadcrumb">
                            <a href="<?php echo home_url(); ?>"><?php _e( 'Início', 'patient-management-system' ); ?></a>
                            <span class="pms-separator">›</span>
                            <span><?php _e( 'Registro Médico', 'patient-management-system' ); ?></span>
                        </div>
                        
                        <h1 class="pms-record-title"><?php the_title(); ?></h1>
                        
                        <div class="pms-record-meta-header">
                            <?php
                            $record_type = get_post_meta( get_the_ID(), '_pms_record_type', true );
                            $record_date = get_post_meta( get_the_ID(), '_pms_record_date', true );
                            $patient_id = get_post_meta( get_the_ID(), '_pms_patient_id', true );
                            ?>
                            
                            <?php if ( $record_type ) : ?>
                                <span class="pms-meta-badge pms-type-badge">
                                    <?php echo esc_html( ucfirst( $record_type ) ); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ( $record_date ) : ?>
                                <span class="pms-meta-badge pms-date-badge">
                                    <i class="pms-icon-calendar"></i>
                                    <?php echo date( 'd/m/Y', strtotime( $record_date ) ); ?>
                                </span>
                            <?php endif; ?>
                            
                            <span class="pms-meta-badge pms-secure-badge">
                                <i class="pms-icon-lock"></i>
                                <?php _e( 'Acesso Seguro', 'patient-management-system' ); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="pms-record-main-content">
                <div class="pms-container">
                    
                    <!-- Security Notice -->
                    <div class="pms-security-notice">
                        <div class="pms-notice-content">
                            <i class="pms-icon-shield"></i>
                            <div class="pms-notice-text">
                                <strong><?php _e( 'Documento Médico Confidencial', 'patient-management-system' ); ?></strong>
                                <p><?php _e( 'Este registro contém informações médicas confidenciais. Não compartilhe com terceiros.', 'patient-management-system' ); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Record Content -->
                    <div class="pms-record-content-wrapper">
                        <?php the_content(); ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pms-record-actions">
                        <button onclick="window.print()" class="pms-action-btn pms-print-btn">
                            <i class="pms-icon-print"></i>
                            <?php _e( 'Imprimir', 'patient-management-system' ); ?>
                        </button>
                        
                        <button onclick="pmsDownloadPDF()" class="pms-action-btn pms-download-btn">
                            <i class="pms-icon-download"></i>
                            <?php _e( 'Baixar PDF', 'patient-management-system' ); ?>
                        </button>
                        
                        <button onclick="pmsShareRecord()" class="pms-action-btn pms-share-btn">
                            <i class="pms-icon-share"></i>
                            <?php _e( 'Compartilhar', 'patient-management-system' ); ?>
                        </button>
                    </div>

                    <!-- Footer Information -->
                    <footer class="pms-record-footer">
                        <div class="pms-footer-grid">
                            <div class="pms-footer-section">
                                <h4><?php _e( 'Informações do Sistema', 'patient-management-system' ); ?></h4>
                                <ul class="pms-footer-list">
                                    <li><?php _e( 'Gerado em:', 'patient-management-system' ); ?> <?php echo date( 'd/m/Y H:i' ); ?></li>
                                    <li><?php _e( 'ID do Registro:', 'patient-management-system' ); ?> #<?php echo get_the_ID(); ?></li>
                                    <?php if ( $patient_id ) : ?>
                                        <li><?php _e( 'ID do Paciente:', 'patient-management-system' ); ?> #<?php echo esc_html( $patient_id ); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            
                            <div class="pms-footer-section">
                                <h4><?php _e( 'Suporte', 'patient-management-system' ); ?></h4>
                                <ul class="pms-footer-list">
                                    <li><a href="mailto:suporte@exemplo.com"><?php _e( 'Contato por Email', 'patient-management-system' ); ?></a></li>
                                    <li><a href="tel:+5511999999999"><?php _e( 'Telefone de Suporte', 'patient-management-system' ); ?></a></li>
                                    <li><a href="<?php echo home_url( '/portal-paciente/' ); ?>"><?php _e( 'Portal do Paciente', 'patient-management-system' ); ?></a></li>
                                </ul>
                            </div>
                            
                            <div class="pms-footer-section">
                                <h4><?php _e( 'Privacidade', 'patient-management-system' ); ?></h4>
                                <ul class="pms-footer-list">
                                    <li><a href="<?php echo home_url( '/politica-privacidade/' ); ?>"><?php _e( 'Política de Privacidade', 'patient-management-system' ); ?></a></li>
                                    <li><a href="<?php echo home_url( '/termos-uso/' ); ?>"><?php _e( 'Termos de Uso', 'patient-management-system' ); ?></a></li>
                                    <li><?php _e( 'Dados protegidos por LGPD', 'patient-management-system' ); ?></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="pms-footer-bottom">
                            <p class="pms-copyright">
                                <?php printf( 
                                    __( '© %s - Sistema de Gerenciamento de Pacientes. Todos os direitos reservados.', 'patient-management-system' ),
                                    date( 'Y' )
                                ); ?>
                            </p>
                        </div>
                    </footer>

                </div>
            </main>

        </article>

    <?php endwhile; ?>
</div>

<!-- Custom Styles for this template -->
<style>
.pms-patient-record-template {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    line-height: 1.6;
    color: #333;
}

.pms-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.pms-record-header-section {
    background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
    color: white;
    padding: 40px 0;
    margin-bottom: 0;
}

.pms-breadcrumb {
    margin-bottom: 20px;
    font-size: 14px;
    opacity: 0.9;
}

.pms-breadcrumb a {
    color: white;
    text-decoration: none;
}

.pms-breadcrumb a:hover {
    text-decoration: underline;
}

.pms-separator {
    margin: 0 10px;
}

.pms-record-title {
    font-size: 32px;
    font-weight: 700;
    margin: 0 0 20px 0;
    line-height: 1.2;
}

.pms-record-meta-header {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.pms-meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
}

.pms-type-badge {
    background: rgba(40,167,69,0.9);
}

.pms-date-badge {
    background: rgba(255,193,7,0.9);
}

.pms-secure-badge {
    background: rgba(220,53,69,0.9);
}

.pms-record-main-content {
    background: #f8f9fa;
    min-height: 60vh;
    padding: 40px 0;
}

.pms-security-notice {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    border-left: 5px solid #ffc107;
}

.pms-notice-content {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.pms-icon-shield {
    font-size: 24px;
    color: #856404;
}

.pms-notice-text strong {
    color: #856404;
    display: block;
    margin-bottom: 5px;
}

.pms-notice-text p {
    margin: 0;
    color: #856404;
    font-size: 14px;
}

.pms-record-content-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    overflow: hidden;
}

.pms-record-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.pms-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.pms-print-btn {
    background: #6c757d;
    color: white;
}

.pms-print-btn:hover {
    background: #545b62;
    transform: translateY(-2px);
}

.pms-download-btn {
    background: #28a745;
    color: white;
}

.pms-download-btn:hover {
    background: #218838;
    transform: translateY(-2px);
}

.pms-share-btn {
    background: #17a2b8;
    color: white;
}

.pms-share-btn:hover {
    background: #138496;
    transform: translateY(-2px);
}

.pms-record-footer {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    padding: 40px;
}

.pms-footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.pms-footer-section h4 {
    color: #0073aa;
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 15px 0;
}

.pms-footer-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pms-footer-list li {
    margin-bottom: 8px;
    font-size: 14px;
}

.pms-footer-list a {
    color: #0073aa;
    text-decoration: none;
}

.pms-footer-list a:hover {
    text-decoration: underline;
}

.pms-footer-bottom {
    border-top: 1px solid #e9ecef;
    padding-top: 20px;
    text-align: center;
}

.pms-copyright {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
}

/* Icons (using CSS pseudo-elements) */
.pms-icon-calendar::before { content: "📅"; }
.pms-icon-lock::before { content: "🔒"; }
.pms-icon-shield::before { content: "🛡️"; }
.pms-icon-print::before { content: "🖨️"; }
.pms-icon-download::before { content: "⬇️"; }
.pms-icon-share::before { content: "📤"; }

/* Responsive Design */
@media (max-width: 768px) {
    .pms-container {
        padding: 0 15px;
    }
    
    .pms-record-header-section {
        padding: 30px 0;
    }
    
    .pms-record-title {
        font-size: 24px;
    }
    
    .pms-record-meta-header {
        flex-direction: column;
        gap: 10px;
    }
    
    .pms-meta-badge {
        align-self: flex-start;
    }
    
    .pms-record-main-content {
        padding: 30px 0;
    }
    
    .pms-record-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .pms-action-btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
    
    .pms-footer-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .pms-record-footer {
        padding: 30px 20px;
    }
}

/* Print Styles */
@media print {
    .pms-record-header-section {
        background: #f8f9fa !important;
        color: #333 !important;
        -webkit-print-color-adjust: exact;
    }
    
    .pms-record-actions,
    .pms-breadcrumb {
        display: none;
    }
    
    .pms-record-content-wrapper,
    .pms-record-footer {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .pms-meta-badge {
        background: #f8f9fa !important;
        color: #333 !important;
        border: 1px solid #ddd;
        -webkit-print-color-adjust: exact;
    }
}
</style>

<!-- Custom JavaScript for this template -->
<script>
function pmsDownloadPDF() {
    // Simple implementation - opens print dialog
    // You can enhance this to generate actual PDF
    window.print();
}

function pmsShareRecord() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: 'Registro Médico',
            url: window.location.href
        });
    } else {
        // Fallback - copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Link copiado para a área de transferência!');
        });
    }
}

// Add smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Add loading animation for buttons
document.querySelectorAll('.pms-action-btn').forEach(button => {
    button.addEventListener('click', function() {
        this.style.opacity = '0.7';
        setTimeout(() => {
            this.style.opacity = '1';
        }, 1000);
    });
});
</script>

<?php get_footer(); ?>

