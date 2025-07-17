(function( $ ) {
    'use strict';

    $(document).ready(function() {
        // Patient login form handler
        $('#pms-patient-login-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $messages = $('#pms-login-messages');
            var $submitBtn = $form.find('button[type="submit"]');
            var originalBtnText = $submitBtn.text();
            
            // Clear previous messages
            $messages.empty();
            
            // Show loading state
            $submitBtn.prop('disabled', true).text(pms_ajax.strings.searching);
            
            // Get form data
            var formData = {
                action: 'pms_patient_login',
                nonce: pms_ajax.nonce,
                patient_id: $('#patient_id').val(),
                patient_dob: $('#patient_dob').val()
            };
            
            // Make AJAX request
            $.post(pms_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        // Show success message
                        $messages.html('<div class="pms-alert pms-alert-success">' + response.data.message + '</div>');
                        
                        // Display records if any
                        if (response.data.records && response.data.records.length > 0) {
                            displayPatientRecords(response.data.records);
                            $('#pms-records-section').show();
                        }
                    } else {
                        // Show error message
                        $messages.html('<div class="pms-alert pms-alert-error">' + response.data.message + '</div>');
                    }
                })
                .fail(function() {
                    $messages.html('<div class="pms-alert pms-alert-error">' + pms_ajax.strings.error + '</div>');
                })
                .always(function() {
                    // Reset button state
                    $submitBtn.prop('disabled', false).text(originalBtnText);
                });
        });

        // Patient search form handler
        $('#pms-patient-search-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $results = $('#pms-search-results');
            var $submitBtn = $form.find('button[type="submit"]');
            var originalBtnText = $submitBtn.text();
            
            // Clear previous results
            $results.empty();
            
            // Show loading state
            $submitBtn.prop('disabled', true).text(pms_ajax.strings.searching);
            
            // Get form data
            var formData = {
                action: 'pms_search_patient_records',
                nonce: pms_ajax.nonce,
                search_term: $('#pms-search-input').val()
            };
            
            // Make AJAX request
            $.post(pms_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        if (response.data.results && response.data.results.length > 0) {
                            displaySearchResults(response.data.results);
                        } else {
                            $results.html('<div class="pms-alert pms-alert-info">' + pms_ajax.strings.no_records + '</div>');
                        }
                    } else {
                        $results.html('<div class="pms-alert pms-alert-error">' + pms_ajax.strings.error + '</div>');
                    }
                })
                .fail(function() {
                    $results.html('<div class="pms-alert pms-alert-error">' + pms_ajax.strings.error + '</div>');
                })
                .always(function() {
                    // Reset button state
                    $submitBtn.prop('disabled', false).text(originalBtnText);
                });
        });

        // Function to display patient records
        function displayPatientRecords(records) {
            var $recordsList = $('#pms-records-list');
            $recordsList.empty();
            
            if (records.length === 0) {
                $recordsList.html('<p>' + pms_ajax.strings.no_records + '</p>');
                return;
            }
            
            var html = '<div class="pms-records-grid">';
            
            records.forEach(function(record) {
                html += '<div class="pms-record-card">';
                html += '<div class="pms-record-header">';
                html += '<span class="pms-record-type">' + escapeHtml(record.type) + '</span>';
                html += '<span class="pms-record-date">' + escapeHtml(record.formatted_date) + '</span>';
                html += '</div>';
                html += '<h3 class="pms-record-title">' + escapeHtml(record.title) + '</h3>';
                html += '<p class="pms-record-excerpt">' + escapeHtml(record.excerpt) + '</p>';
                html += '<a href="' + escapeHtml(record.url) + '" class="pms-btn pms-btn-secondary" target="_blank">Ver Registro Completo</a>';
                html += '</div>';
            });
            
            html += '</div>';
            $recordsList.html(html);
        }

        // Function to display search results
        function displaySearchResults(results) {
            var $results = $('#pms-search-results');
            $results.empty();
            
            if (results.length === 0) {
                $results.html('<p>' + pms_ajax.strings.no_records + '</p>');
                return;
            }
            
            var html = '<div class="pms-search-results-list">';
            
            results.forEach(function(record) {
                html += '<div class="pms-search-result-item">';
                html += '<div class="pms-result-meta">';
                html += '<span class="pms-result-type">' + escapeHtml(record.type) + '</span>';
                html += '<span class="pms-result-date">' + escapeHtml(record.formatted_date) + '</span>';
                html += '</div>';
                html += '<h4 class="pms-result-title">' + escapeHtml(record.title) + '</h4>';
                html += '<p class="pms-result-excerpt">' + escapeHtml(record.excerpt) + '</p>';
                html += '<a href="' + escapeHtml(record.url) + '" class="pms-result-link" target="_blank">Ver Registro</a>';
                html += '</div>';
            });
            
            html += '</div>';
            $results.html(html);
        }

        // Utility function to escape HTML
        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Auto-hide alerts after 5 seconds
        $(document).on('click', '.pms-alert', function() {
            $(this).fadeOut();
        });

        // Form validation
        $('#patient_id').on('input', function() {
            var value = $(this).val();
            if (value && (isNaN(value) || value <= 0)) {
                $(this).addClass('pms-input-error');
            } else {
                $(this).removeClass('pms-input-error');
            }
        });

        // Date validation
        $('#patient_dob').on('change', function() {
            var selectedDate = new Date($(this).val());
            var today = new Date();
            
            if (selectedDate > today) {
                $(this).addClass('pms-input-error');
                alert('A data de nascimento não pode ser no futuro.');
            } else {
                $(this).removeClass('pms-input-error');
            }
        });

        // Responsive menu toggle for mobile
        $('.pms-portal-header').on('click', '.pms-menu-toggle', function() {
            $('.pms-portal-content').toggleClass('pms-mobile-menu-open');
        });

        // Print functionality
        $(document).on('click', '.pms-print-record', function(e) {
            e.preventDefault();
            var recordUrl = $(this).attr('href');
            var printWindow = window.open(recordUrl, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        });

        // Copy link functionality
        $(document).on('click', '.pms-copy-link', function(e) {
            e.preventDefault();
            var link = $(this).data('link');
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(function() {
                    alert('Link copiado para a área de transferência!');
                });
            } else {
                // Fallback for older browsers
                var textArea = document.createElement('textarea');
                textArea.value = link;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('Link copiado para a área de transferência!');
            }
        });
    });

})( jQuery );

