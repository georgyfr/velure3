/**
 * Velure Core — Admin JS v3.0
 * Elementor-inspired: sidebar nav, AJAX publish, templates, accordions, repeaters
 */
(function($){
'use strict';
if (typeof velureCoreAdmin === 'undefined') return;

const VC = {
	form: $('#vc-settings-form'),
	unsaved: false,
	initialForm: '',

	/* ═══════════════════════════════════════
	   1. SIDEBAR NAVIGATION
	   ═══════════════════════════════════════ */
	initNav() {
		$(document).on('click', '.vc-nav-item', function(e){
			e.preventDefault();
			const panel = $(this).data('nav');
			if (!panel) return;
			$('.vc-nav-item').removeClass('active');
			$(this).addClass('active');
			$('.vc-section-panel').removeClass('active').hide();
			const target = $(`.vc-section-panel[data-panel="${panel}"]`);
			if (target.length) { target.addClass('active').show(); }
			$('#vc-header-title').text($(this).find('.vc-nav-item-label').text());
			$('#vc-breadcrumb-current').text($(this).find('.vc-nav-item-label').text());
			$('.vc-panel').scrollTop(0);
		});
	},

	/* ═══════════════════════════════════════
	   2. ACCORDIONS
	   ═══════════════════════════════════════ */
	initAccordions() {
		$(document).on('click', '.vc-accordion-header', function(){
			$(this).closest('.vc-accordion-item').toggleClass('open');
		});
	},

	/* ═══════════════════════════════════════
	   3. REPEATER — Add
	   ═══════════════════════════════════════ */
	initRepeaters() {
		$(document).on('click', '.vc-repeater-add', function(e){
			e.preventDefault();
			const repeaterId = $(this).data('repeater');
			const template = $('#' + repeaterId + '-template');
			const container = $('#' + repeaterId + '-rows');
			if (!template.length) {
				// Fallback: find .vc-repeater-template inside the parent
				const parent = $(this).closest('.vc-accordion-body, .vc-section-panel');
				const tmpl = parent.find('.vc-repeater-template').first();
				const cont = parent.find('.vc-repeater').first();
				if (!tmpl.length || !cont.length) return;
				const clone = tmpl.clone().removeClass('vc-repeater-template').removeAttr('id').show();
				const idx = cont.find('.vc-repeater-row:not(.vc-repeater-template)').length;
				clone.find('[name]').each(function(){
					let name = $(this).attr('name');
					name = name.replace(/\[\d+\]/g, '[' + idx + ']');
					name = name.replace(/__INDEX__/g, idx);
					$(this).attr('name', name);
				});
				const label = cont.data('label') || tmpl.data('label') || 'Element';
				clone.find('.vc-repeater-title').text(label + ' ' + (idx + 1));
				clone.addClass('open');
				cont.append(clone);
				return;
			}
			const clone = template.clone().removeClass('vc-repeater-template').removeAttr('id').show();
			const idx = container.find('.vc-repeater-row:not(.vc-repeater-template)').length;
			clone.find('[name]').each(function(){
				let name = $(this).attr('name');
				name = name.replace(/\[\d+\]/g, '[' + idx + ']');
				name = name.replace(/__INDEX__/g, idx);
				$(this).attr('name', name);
			});
			const label = template.data('label') || 'Element';
			clone.find('.vc-repeater-title').text(label + ' ' + (idx + 1));
			clone.addClass('open');
			container.append(clone);
		});

		/* Remove */
		$(document).on('click', '.vc-repeater-remove', function(e){
			e.stopPropagation();
			const row = $(this).closest('.vc-repeater-row');
			row.fadeOut(200, function(){
				$(this).remove();
			});
			VC.markUnsaved();
		});

		/* Toggle collapse */
		$(document).on('click', '.vc-repeater-row-header', function(e){
			if ($(e.target).closest('.vc-repeater-remove, .vc-repeater-toggle-btn').length) return;
			$(this).closest('.vc-repeater-row').toggleClass('open');
		});

		/* Sortable */
		$('.vc-repeater').sortable({
			handle: '.vc-repeater-handle',
			items: '> .vc-repeater-row:not(.vc-repeater-template)',
			opacity: 0.7,
			placeholder: 'ui-sortable-placeholder',
			update: function(){ VC.markUnsaved(); }
		});
	},

	/* ═══════════════════════════════════════
	   4. MEDIA UPLOAD
	   ═══════════════════════════════════════ */
	initMedia() {
		$(document).on('click', '.vc-img-btn', function(e){
			e.preventDefault();
			const wrap = $(this).closest('.vc-image-field').find('.vc-img-wrap');
			const input = $(this).closest('.vc-image-field').find('.vc-img-id');
			const removeBtn = $(this).closest('.vc-image-field').find('.vc-img-remove');
			const frame = wp.media({ title: 'Choisir une image', button: { text: 'Utiliser cette image' }, multiple: false, library: { type: 'image' } });
			frame.on('select', function(){
				const att = frame.state().get('selection').first().toJSON();
				input.val(att.id);
				wrap.html('<img src="' + att.url + '" class="vc-img-preview" />');
				removeBtn.show();
				VC.markUnsaved();
			});
			frame.open();
		});

		$(document).on('click', '.vc-img-remove', function(e){
			e.preventDefault();
			const wrap = $(this).closest('.vc-image-field').find('.vc-img-wrap');
			const input = $(this).closest('.vc-image-field').find('.vc-img-id');
			wrap.html('<div class="vc-img-placeholder"><span class="vc-img-placeholder-icon">&#128247;</span>Cliquer ou glisser une image</div>');
			input.val(0);
			$(this).hide();
			VC.markUnsaved();
		});
	},

	/* ═════════════════════════════════════
	   5. RANGE SLIDERS
	   ═══════════════════════════════════════ */
	initRanges() {
		$(document).on('input', '[data-range]', function(){
			const val = $(this).val();
			const display = $(this).closest('.vc-range-wrap').find('.vc-range-value');
			const unit = display.text().replace(/[\d\s.]+/, '').trim();
			display.text(val + (unit ? ' ' + unit : ''));
			VC.markUnsaved();
		});
	},

	/* ═══════════════════════════════════════
	   6. COLOR PICKERS
	   ═══════════════════════════════════════ */
	initColors() {
		$(document).on('input', '[data-color]', function(){
			const color = $(this).val();
			$(this).closest('.vc-color-wrap').find('.vc-color-swatch').css('background', color);
			const textInput = $(this).closest('.vc-color-wrap').find('[data-color-text]');
			if (textInput.length) textInput.val(color);
			VC.markUnsaved();
		});
		$(document).on('input', '[data-color-text]', function(){
			const color = $(this).val();
			if (/^#[0-9a-fA-F]{6}$/.test(color)) {
				$(this).closest('.vc-color-wrap').find('[data-color]').val(color);
				$(this).closest('.vc-color-wrap').find('.vc-color-swatch').css('background', color);
			}
			VC.markUnsaved();
		});
	},

	/* ═══════════════════════════════════════
	   7. SECTION ORDER SORTABLE
	   ═══════════════════════════════════════ */
	initSectionOrder() {
		$('.vc-section-order-list').sortable({
			handle: '.vc-section-order-handle',
			axis: 'y',
			opacity: 0.7,
			update: function(){ VC.markUnsaved(); }
		});
		$('.vc-section-order-list input').on('mousedown', function(e){ e.stopPropagation(); });
	},

	/* ═══════════════════════════════════════
	   8. HERO SIDE BLOCKS TOGGLE
	   ═══════════════════════════════════════ */
	initConditional() {
		$(document).on('change', '[name="hero_show_side"]', function(){
			$('#vc-hero-side-blocks').toggle(this.checked);
		});
	},

	/* ═══════════════════════════════════════
	   9. TEMPLATES SYSTEM
	   ═══════════════════════════════════════ */
	initTemplates() {
		$(document).on('click', '.vc-template-card', function(e){
			e.preventDefault();
			if (!confirm('Appliquer ce modele ? Les reglages actuels de cette section seront remplaces.')) return;

			const section = $(this).data('template-section');
			const name = $(this).data('template');
			let templates = {};
			try { templates = JSON.parse($('#vc-templates-data').text() || '{}'); } catch(e){}
			const sectionTemplates = templates[section] || [];
			const tmpl = sectionTemplates.find(t => t.name === name);
			if (!tmpl || !tmpl.data) { VC.toast('Modele introuvable.', 'error'); return; }

			// Fill form fields from template data
			Object.entries(tmpl.data).forEach(([key, val]) => {
				if (key === 'hero_slides' || key === 'trust_features' || key === 'brand_names' || key === 'instagram_images') return; // Skip repeaters for now
				const field = $(`[name="${key}"]`);
				if (field.is(':checkbox')) { field.prop('checked', !!val); }
				else if (field.length) { field.val(val).trigger('change'); }
			});
			VC.markUnsaved();
			VC.toast('Modele "' + name + '" applique !', 'success');
		});
	},

	/* ═══════════════════════════════════════
	   10. UNSAVED CHANGES TRACKING
	   ═══════════════════════════════════════ */
	markUnsaved() {
		VC.unsaved = true;
		$('#vc-unsaved-dot').addClass('active');
	},
	markSaved() {
		VC.unsaved = false;
		$('#vc-unsaved-dot').removeClass('active');
	},

	/* ═══════════════════════════════════════
	   11. AJAX PUBLISH
	   ═══════════════════════════════════════ */
	initPublish() {
		$('#vc-publish-btn').on('click', function(){
			const btn = $(this);
			btn.prop('disabled', true).html('<span style="display:inline-flex;align-items:center;gap:6px"><span class="vc-spinner"></span> Publication...</span>');
			VC.toast('Publication en cours...', 'saving');

			const formData = VC.form.serialize();
			$.ajax({
				url: velureCoreAdmin.ajaxUrl,
				type: 'POST',
				data: formData + '&nonce=' + velureCoreAdmin.nonce,
				success: function(res){
					if (res.success) {
						VC.markSaved();
						VC.toast(res.data.message || 'Publie !', 'success');
						$('.vc-footer-status span:last-child').text('Derniere sauvegarde : ' + new Date().toLocaleTimeString());
						// Update initial state
						VC.initialForm = VC.form.serialize();
					} else {
						VC.toast(res.data?.message || 'Erreur lors de la publication.', 'error');
					}
				},
				error: function(){
					VC.toast('Erreur reseau. Veuillez reessayer.', 'error');
				},
				complete: function(){
					btn.prop('disabled', false).html('&#10003; Publier');
				}
			});
		});

		// Discard
		$('#vc-discard-btn').on('click', function(){
			if (VC.unsaved && !confirm('Voulez-vous vraiment annuler toutes les modifications non publiees ?')) return;
			window.location.reload();
		});
	},

	/* ═══════════════════════════════════════
	   12. IMPORT / EXPORT
	   ═══════════════════════════════════════ */
	initIO() {
		// Export
		$(document).on('click', '#vc-export-btn', function(){
			$.post(velureCoreAdmin.ajaxUrl, {action:'velure_core_get_settings', nonce:velureCoreAdmin.nonce}, function(res){
				if (res.success) {
					$('#vc-export-text').val(JSON.stringify(res.data, null, 2));
					$('#vc-export-output').show();
					$('#vc-import-input').hide();
				}
			});
		});

		// Copy
		$(document).on('click', '#vc-copy-export', function(){
			const ta = document.getElementById('vc-export-text');
			ta.select();
			document.execCommand('copy');
			$(this).text('&#10003; Copie !');
			setTimeout(()=> $(this).text('&#128203; Copier'), 2000);
		});

		// Show import
		$(document).on('click', '#vc-import-btn', function(){
			$('#vc-import-input').toggle();
			$('#vc-export-output').hide();
		});

		// Apply import
		$(document).on('click', '#vc-do-import', function(){
			const json = $('#vc-import-text').val().trim();
			if (!json) { VC.toast('Veuillez coller du JSON.', 'error'); return; }
			try {
				const data = JSON.parse(json);
				$.post(velureCoreAdmin.ajaxUrl, {action:'velure_core_import_settings', nonce:velureCoreAdmin.nonce, settings:json}, function(res){
					if (res.success) {
						VC.toast('Import reussi ! Rechargement...', 'success');
						setTimeout(()=> window.location.reload(), 1500);
					} else {
						VC.toast(res.data?.message || 'Erreur d\'import.', 'error');
					}
				});
			} catch(e) {
				VC.toast('JSON invalide.', 'error');
			}
		});

		// Reset
		$(document).on('click', '#vc-reset-btn', function(){
			if (!confirm('Reinitialiser tous les reglages aux valeurs par defaut ? Cette action est irreversible.')) return;
			$.post(velureCoreAdmin.ajaxUrl, {action:'velure_core_reset', nonce:velureCoreAdmin.nonce}, function(res){
				if (res.success) {
					VC.toast('Reinitialisation reussie. Rechargement...', 'success');
					setTimeout(()=> window.location.reload(), 1500);
				}
			});
		});
	},

	/* ═══════════════════════════════════════
	   13. TOAST NOTIFICATIONS
	   ═══════════════════════════════════════ */
	toast(msg, type) {
		const el = $('#vc-toast');
		el.removeClass('show vc-toast-success vc-toast-error vc-toast-saving').addClass('vc-toast-' + (type||'success'));
		el.html((type==='success'?'&#10003; ':(type==='error'?'&#10007; ':'&#9203; ')) + msg).addClass('show');
		clearTimeout(VC._toastTimer);
		VC._toastTimer = setTimeout(()=> el.removeClass('show'), 4000);
	},
	_toastTimer: null,

	/* ═══════════════════════════════════════
	   14. TRACK CHANGES
	   ═══════════════════════════════════════ */
	initChangeTracking() {
		VC.initialForm = VC.form.serialize();
		VC.form.on('change input', 'input, select, textarea', function(){
			if (VC.form.serialize() !== VC.initialForm) { VC.markUnsaved(); }
			else { VC.markSaved(); }
		});
		// Warn before leaving
		$(window).on('beforeunload', function(){
			if (VC.unsaved) return true;
		});
	},

	/* ═══════════════════════════════════════
	   INIT
	   ═══════════════════════════════════════ */
	init() {
		VC.initNav();
		VC.initAccordions();
		VC.initRepeaters();
		VC.initMedia();
		VC.initRanges();
		VC.initColors();
		VC.initSectionOrder();
		VC.initConditional();
		VC.initTemplates();
		VC.initPublish();
		VC.initIO();
		VC.initChangeTracking();
	}
};

$(document).ready(VC.init);
})(jQuery);