(function( $ ) {
	'use strict';

	$(document).ready(function() {
		// Dashboard statistics animation
		$('.pms-stat-number').each(function() {
			var $this = $(this);
			var countTo = parseInt($this.text());
			
			$({ countNum: 0 }).animate({
				countNum: countTo
			}, {
				duration: 2000,
				easing: 'swing',
				step: function() {
					$this.text(Math.floor(this.countNum));
				},
				complete: function() {
					$this.text(this.countNum);
				}
			});
		});

		// Form validation
		$('.pms-form').on('submit', function(e) {
			var isValid = true;
			var $form = $(this);
			
			$form.find('input[required], select[required], textarea[required]').each(function() {
				var $field = $(this);
				if (!$field.val()) {
					$field.addClass('error');
					isValid = false;
				} else {
					$field.removeClass('error');
				}
			});
			
			if (!isValid) {
				e.preventDefault();
				alert('Por favor, preencha todos os campos obrigatórios.');
			}
		});

		// Search functionality
		$('#pms-search').on('keyup', function() {
			var value = $(this).val().toLowerCase();
			$('.pms-table tbody tr').filter(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
			});
		});

		// Confirmation dialogs
		$('.pms-delete-btn').on('click', function(e) {
			if (!confirm('Tem certeza que deseja excluir este item?')) {
				e.preventDefault();
			}
		});

		// Auto-save functionality for forms
		var autoSaveTimer;
		$('.pms-form input, .pms-form textarea, .pms-form select').on('change', function() {
			clearTimeout(autoSaveTimer);
			autoSaveTimer = setTimeout(function() {
				// Auto-save logic here
				console.log('Auto-saving...');
			}, 2000);
		});

		// Mobile menu toggle
		$('.pms-mobile-menu-toggle').on('click', function() {
			$('.pms-sidebar').toggleClass('active');
		});

		// Tooltip initialization
		$('[data-tooltip]').hover(
			function() {
				var tooltip = $(this).attr('data-tooltip');
				$(this).append('<div class="pms-tooltip">' + tooltip + '</div>');
			},
			function() {
				$(this).find('.pms-tooltip').remove();
			}
		);
	});

})( jQuery );

