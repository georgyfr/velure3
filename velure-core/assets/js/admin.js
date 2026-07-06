/**
 * Velure Core — Admin JS
 * Handles: media upload, repeaters, section sort
 */
(function($) {
	'use strict';

	if (typeof velureCoreAdmin === 'undefined') return;

	/* ═════════════════════════════════════════
	   1. MEDIA UPLOAD
	   ═════════════════════════════════════════ */
	$(document).on('click', '.vc-img-btn', function(e) {
		e.preventDefault();
		var btn = $(this);
		var wrap = btn.closest('.vc-image-field').find('.vc-img-wrap');
		var input = btn.closest('.vc-image-field').find('.vc-img-id');
		var removeBtn = btn.closest('.vc-image-field').find('.vc-img-remove');

		var frame = wp.media({
			title: 'Choisir une image',
			button: { text: 'Utiliser cette image' },
			multiple: false,
			library: { type: 'image' }
		});

		frame.on('select', function() {
			var attachment = frame.state().get('selection').first().toJSON();
			input.val(attachment.id);
			wrap.html('<img src="' + attachment.url + '" class="vc-img-preview" />');
			removeBtn.show();
		});

		frame.open();
	});

	$(document).on('click', '.vc-img-remove', function(e) {
		e.preventDefault();
		var wrap = $(this).closest('.vc-image-field').find('.vc-img-wrap');
		var input = $(this).closest('.vc-image-field').find('.vc-img-id');
		wrap.html('<div class="vc-img-placeholder">Aucune image</div>');
		input.val(0);
		$(this).hide();
	});

	/* ═══════════════════════════════════════════════
	   2. REPEATER — Add Row
	   ═══════════════════════════════════════════════ */
	$(document).on('click', '.vc-repeater-add', function(e) {
		e.preventDefault();
		var repeaterId = $(this).data('repeater');
		var template = $('#' + repeaterId + '-template');
		var container = $('#' + repeaterId + '-rows');
		if (!template.length || !container.length) return;

		var clone = template.clone();
		clone.removeClass('vc-repeater-template').removeAttr('id').show();

		// Increment all name indices
		var idx = container.find('.vc-repeater-row').length;
		clone.find('[name]').each(function() {
			var name = $(this).attr('name');
			name = name.replace(/\[\d+\]/, '[' + idx + ']');
			$(this).attr('name', name);
		});

		// Update title
		var label = template.data('label') || 'Ligne';
		clone.find('.vc-repeater-title').text(label + ' ' + (idx + 1));

		container.append(clone);
	});

	/* ═══════════════════════════════════════════════
	   3. REPEATER — Remove Row
	   ═══════════════════════════════════════════════ */
	$(document).on('click', '.vc-repeater-remove', function(e) {
		e.preventDefault();
		$(this).closest('.vc-repeater-row').fadeOut(200, function() {
			$(this).remove();
			// Re-index remaining rows
			var container = $(this).closest('.vc-repeater-rows');
			container.find('.vc-repeater-row').each(function(i) {
				$(this).find('[name]').each(function() {
					var name = $(this).attr('name');
					name = name.replace(/\[\d+\]/, '[' + i + ']');
					$(this).attr('name', name);
				});
				var label = container.data('label') || 'Ligne';
				$(this).find('.vc-repeater-title').text(label + ' ' + (i + 1));
			});
		});
	});

	/* ═══════════════════════════════════════════════
	   4. REPEATER — Toggle collapse
	   ═══════════════════════════════════════════════ */
	$(document).on('click', '.vc-repeater-row-header', function(e) {
		if ($(e.target).closest('.vc-repeater-remove').length) return;
		$(this).closest('.vc-repeater-row').find('.vc-repeater-fields').slideToggle(150);
	});

	/* ═══════════════════════════════════════════════
	   5. SECTION ORDER — Sortable
	   ═══════════════════════════════════════════════ */
	$('.vc-section-order-list').sortable({
		handle: '.vc-section-order-handle',
		axis: 'y',
		opacity: 0.7,
		placeholder: 'ui-sortable-placeholder',
		update: function() {
			// Re-index hidden inputs
			$(this).find('li').each(function(i) {
				$(this).find('input[name^="section_order"]').val($(this).data('section'));
			});
		}
	});

	// Prevent section order inputs from being dragged
	$('.vc-section-order-list input').on('mousedown', function(e) {
		e.stopPropagation();
	});

})(jQuery);