/**
 * Velure Core — Admin JS v3.1
 * Elementor-inspired: sidebar nav, AJAX publish, templates, accordions, repeaters
 */
(function($){
'use strict';

/* ═══════════════════════════════════════
   SAFETY: bail if localized data missing
   ═══════════════════════════════════════ */
if (typeof velureCoreAdmin === 'undefined') {
        console.warn('[Velure Core] velureCoreAdmin not defined — scripts not loaded correctly.');
        return;
}

var VC = {
        $form: null,
        unsaved: false,
        _initialForm: '',
        _toastTimer: null,

        /* ═══════════════════════════════════════
           1. SIDEBAR NAVIGATION
           ═══════════════════════════════════════ */
        initNav: function() {
                var self = this;
                $(document).on('click', '.vc-nav-item', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var panel = $(this).attr('data-nav');
                        if (!panel) return;

                        $('.vc-nav-item').removeClass('active');
                        $(this).addClass('active');

                        $('.vc-section-panel').removeClass('active').hide();
                        var target = $('.vc-section-panel[data-panel="' + panel + '"]');
                        if (target.length) {
                                target.addClass('active').show();
                        }

                        var label = $(this).find('.vc-nav-item-label').text() || panel;
                        $('#vc-header-title').text(label);
                        $('#vc-breadcrumb-current').text(label);

                        var panelEl = document.querySelector('.vc-panel');
                        if (panelEl) panelEl.scrollTop = 0;
                });
        },

        /* ═══════════════════════════════════════
           2. ACCORDIONS
           ═══════════════════════════════════════ */
        initAccordions: function() {
                $(document).on('click', '.vc-accordion-header', function(e){
                        e.stopPropagation();
                        $(this).closest('.vc-accordion-item').toggleClass('open');
                });
        },

        /* ═══════════════════════════════════════
           3. REPEATER — Add / Remove / Sort
           ═══════════════════════════════════════ */
        _addRepeaterRow: function(templateEl, containerEl, label) {
                var clone = templateEl.clone().removeClass('vc-repeater-template').removeAttr('id').show();
                var idx = containerEl.find('.vc-repeater-row:not(.vc-repeater-template)').length;

                clone.find('[name]').each(function(){
                        var name = $(this).attr('name');
                        if (!name) return;
                        name = name.replace(/\[\d+\]/g, '[' + idx + ']');
                        name = name.replace(/__INDEX__/g, idx);
                        name = name.replace(/__NUM__/g, (idx + 1));
                        $(this).attr('name', name);
                });
                clone.find('[data-index]').attr('data-index', idx);

                clone.find('.vc-repeater-title').text((label || 'Element') + ' ' + (idx + 1));
                clone.addClass('open');
                containerEl.append(clone);
        },

        initRepeaters: function() {
                var self = this;

                /* Add button */
                $(document).on('click', '.vc-repeater-add', function(e){
                        e.preventDefault();
                        e.stopPropagation();

                        var repeaterId = $(this).data('repeater');
                        var template = $('#' + repeaterId + '-template');
                        var container = $('#' + repeaterId);

                        if (!template.length || !container.length) {
                                /* Fallback: search within parent */
                                var parent = $(this).closest('.vc-accordion-body, .vc-section-panel');
                                template = parent.find('.vc-repeater-template').first();
                                container = parent.find('.vc-repeater').first();
                        }
                        if (!template.length || !container.length) return;

                        var label = template.data('label') || container.data('label') || 'Element';
                        self._addRepeaterRow(template, container, label);
                        self.markUnsaved();
                });

                /* Remove row */
                $(document).on('click', '.vc-repeater-remove', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var row = $(this).closest('.vc-repeater-row');
                        row.fadeOut(200, function(){ $(this).remove(); });
                        self.markUnsaved();
                });

                /* Toggle collapse */
                $(document).on('click', '.vc-repeater-row-header', function(e){
                        if ($(e.target).closest('.vc-repeater-remove, .vc-repeater-toggle-btn').length) return;
                        $(this).closest('.vc-repeater-row').toggleClass('open');
                });
        },

        /* Init sortable on visible repeaters — call after panel switch */
        initRepeaterSortable: function(container) {
                var self = this;
                var $containers = container ? container.find('.vc-repeater') : $('.vc-repeater');
                $containers.each(function(){
                        if ($(this).data('ui-sortable')) return; // already initialized
                        $(this).sortable({
                                handle: '.vc-repeater-handle',
                                items: '> .vc-repeater-row:not(.vc-repeater-template)',
                                opacity: 0.7,
                                placeholder: 'ui-sortable-placeholder',
                                update: function(){ self.markUnsaved(); }
                        });
                });
        },

        /* ═══════════════════════════════════════
           4. MEDIA UPLOAD
           ═══════════════════════════════════════ */
        initMedia: function() {
                var self = this;
                $(document).on('click', '.vc-img-btn', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var field = $(this).closest('.vc-image-field');
                        var wrap = field.find('.vc-img-wrap');
                        var input = field.find('.vc-img-id');
                        var removeBtn = field.find('.vc-img-remove');

                        if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                                self.toast('WordPress Media Library non disponible.', 'error');
                                return;
                        }

                        var frame = wp.media({
                                title: 'Choisir une image',
                                button: { text: 'Utiliser cette image' },
                                multiple: false,
                                library: { type: 'image' }
                        });
                        frame.on('select', function(){
                                var att = frame.state().get('selection').first().toJSON();
                                input.val(att.id);
                                wrap.html('<img src="' + att.url + '" class="vc-img-preview" />');
                                removeBtn.show();
                                self.markUnsaved();
                        });
                        frame.open();
                });

                $(document).on('click', '.vc-img-remove', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var field = $(this).closest('.vc-image-field');
                        var wrap = field.find('.vc-img-wrap');
                        var input = field.find('.vc-img-id');
                        wrap.html('<div class="vc-img-placeholder"><span class="vc-img-placeholder-icon">&#128247;</span>Cliquer ou glisser une image</div>');
                        input.val(0);
                        $(this).hide();
                        VC.markUnsaved();
                });
        },

        /* ═══════════════════════════════════════
           5. RANGE SLIDERS
           ═══════════════════════════════════════ */
        initRanges: function() {
                $(document).on('input', '[data-range]', function(){
                        var val = $(this).val();
                        var display = $(this).closest('.vc-range-wrap').find('.vc-range-value');
                        if (display.length) {
                                var currentText = display.text();
                                var unit = currentText.replace(/[\d\s.,]+/, '').trim();
                                display.text(val + (unit ? ' ' + unit : ''));
                        }
                        VC.markUnsaved();
                });
        },

        /* ═══════════════════════════════════════
           6. COLOR PICKERS
           ═══════════════════════════════════════ */
        initColors: function() {
                $(document).on('input', '[data-color]', function(){
                        var color = $(this).val();
                        $(this).closest('.vc-color-wrap').find('.vc-color-swatch').css('background', color);
                        var textInput = $(this).closest('.vc-color-wrap').find('[data-color-text]');
                        if (textInput.length) textInput.val(color);
                        VC.markUnsaved();
                });
                $(document).on('change', '[data-color-text]', function(){
                        var color = $(this).val();
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
        initSectionOrder: function() {
                var self = this;
                $('.vc-section-order-list').sortable({
                        handle: '.vc-section-order-handle',
                        axis: 'y',
                        opacity: 0.7,
                        placeholder: 'ui-sortable-placeholder',
                        update: function(){ self.markUnsaved(); }
                });
                $('.vc-section-order-list input').on('mousedown', function(e){ e.stopPropagation(); });
        },

        /* ═══════════════════════════════════════
           8. HERO SIDE BLOCKS TOGGLE
           ═══════════════════════════════════════ */
        initConditional: function() {
                $(document).on('change', '[name="hero_show_side"]', function(){
                        var el = document.getElementById('vc-hero-side-blocks');
                        if (el) el.style.display = this.checked ? '' : 'none';
                });
        },

        /* ═══════════════════════════════════════
           9. TEMPLATES SYSTEM
           ═══════════════════════════════════════ */
        initTemplates: function() {
                var self = this;
                $(document).on('click', '.vc-template-card', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        if (!confirm('Appliquer ce modele ? Les reglages actuels de cette section seront remplaces.')) return;

                        var section = $(this).data('template-section');
                        var name = $(this).data('template');
                        var templates = {};
                        try {
                                templates = JSON.parse($('#vc-templates-data').text() || '{}');
                        } catch(ex) {
                                self.toast('Erreur de lecture des modeles.', 'error');
                                return;
                        }

                        var sectionTemplates = templates[section] || [];
                        var tmpl = null;
                        for (var i = 0; i < sectionTemplates.length; i++) {
                                if (sectionTemplates[i].name === name) { tmpl = sectionTemplates[i]; break; }
                        }
                        if (!tmpl || !tmpl.data) {
                                self.toast('Modele introuvable.', 'error');
                                return;
                        }

                        /* Fill form fields from template data */
                        var skipKeys = ['hero_slides','trust_features','brand_names','instagram_images'];
                        $.each(tmpl.data, function(key, val){
                                if (skipKeys.indexOf(key) !== -1) return;
                                var field = $('[name="' + key + '"]');
                                if (field.length) {
                                        if (field.is(':checkbox')) {
                                                field.prop('checked', !!val);
                                        } else {
                                                field.val(val).trigger('change');
                                        }
                                }
                        });

                        /* Trigger conditional fields */
                        $('[name="hero_show_side"]').trigger('change');

                        self.markUnsaved();
                        self.toast('Modele "' + name + '" applique !', 'success');
                });
        },

        /* ═══════════════════════════════════════
           10. UNSAVED CHANGES TRACKING
           ═══════════════════════════════════════ */
        markUnsaved: function() {
                VC.unsaved = true;
                $('#vc-unsaved-dot').addClass('active');
        },
        markSaved: function() {
                VC.unsaved = false;
                $('#vc-unsaved-dot').removeClass('active');
        },

        /* ═══════════════════════════════════════
           11. AJAX PUBLISH
           ═══════════════════════════════════════ */
        initPublish: function() {
                var self = this;

                $('#vc-publish-btn').on('click', function(){
                        if (!VC.$form || !VC.$form.length) {
                                self.toast('Formulaire introuvable.', 'error');
                                return;
                        }

                        var btn = $(this);
                        btn.prop('disabled', true).html('<span style="display:inline-flex;align-items:center;gap:6px"><span class="vc-spinner"></span> Publication...</span>');
                        self.toast('Publication en cours...', 'saving');

                        var formData = VC.$form.serialize();

                        $.ajax({
                                url: velureCoreAdmin.ajaxUrl,
                                type: 'POST',
                                data: formData,
                                dataType: 'json',
                                success: function(res){
                                        if (res && res.success) {
                                                self.markSaved();
                                                self.toast(res.data && res.data.message ? res.data.message : 'Publie !', 'success');
                                                $('.vc-footer-status span:last-child').text('Derniere sauvegarde : ' + new Date().toLocaleTimeString());
                                                VC._initialForm = VC.$form.serialize();
                                        } else {
                                                var errMsg = (res && res.data && res.data.message) ? res.data.message : 'Erreur lors de la publication.';
                                                self.toast(errMsg, 'error');
                                        }
                                },
                                error: function(xhr, status, err){
                                        self.toast('Erreur reseau (' + status + '). Veuillez reessayer.', 'error');
                                },
                                complete: function(){
                                        btn.prop('disabled', false).html('&#10003; Publier');
                                }
                        });
                });

                /* Discard */
                $('#vc-discard-btn').on('click', function(){
                        if (VC.unsaved && !confirm('Voulez-vous vraiment annuler toutes les modifications non publiees ?')) return;
                        window.location.reload();
                });
        },

        /* ═══════════════════════════════════════
           12. IMPORT / EXPORT
           ═══════════════════════════════════════ */
        initIO: function() {
                var self = this;

                /* Export */
                $(document).on('click', '#vc-export-btn', function(){
                        $.post(velureCoreAdmin.ajaxUrl, {
                                action: 'velure_core_get_settings',
                                _wpnonce: velureCoreAdmin.nonce
                        }, function(res){
                                if (res && res.success) {
                                        $('#vc-export-text').val(JSON.stringify(res.data, null, 2));
                                        $('#vc-export-output').show();
                                        $('#vc-import-input').hide();
                                }
                        });
                });

                /* Copy */
                $(document).on('click', '#vc-copy-export', function(){
                        var ta = document.getElementById('vc-export-text');
                        if (ta) {
                                ta.select();
                                try { document.execCommand('copy'); } catch(e){}
                        }
                        var btn = $(this);
                        btn.text('\u2713 Copie !');
                        setTimeout(function(){ btn.text('&#128203; Copier'); }, 2000);
                });

                /* Show import */
                $(document).on('click', '#vc-import-btn', function(){
                        $('#vc-import-input').toggle();
                        $('#vc-export-output').hide();
                });

                /* Apply import */
                $(document).on('click', '#vc-do-import', function(){
                        var json = $('#vc-import-text').val().trim();
                        if (!json) { self.toast('Veuillez coller du JSON.', 'error'); return; }
                        try {
                                JSON.parse(json);
                        } catch(e) {
                                self.toast('JSON invalide.', 'error');
                                return;
                        }
                        $.post(velureCoreAdmin.ajaxUrl, {
                                action: 'velure_core_import_settings',
                                _wpnonce: velureCoreAdmin.nonce,
                                settings: json
                        }, function(res){
                                if (res && res.success) {
                                        self.toast('Import reussi ! Rechargement...', 'success');
                                        setTimeout(function(){ window.location.reload(); }, 1500);
                                } else {
                                        self.toast((res && res.data && res.data.message) ? res.data.message : "Erreur d'import.", 'error');
                                }
                        });
                });

                /* Reset */
                $(document).on('click', '#vc-reset-btn', function(){
                        if (!confirm('Reinitialiser tous les reglages aux valeurs par defaut ? Cette action est irreversible.')) return;
                        $.post(velureCoreAdmin.ajaxUrl, {
                                action: 'velure_core_reset',
                                _wpnonce: velureCoreAdmin.nonce
                        }, function(res){
                                if (res && res.success) {
                                        self.toast('Reinitialisation reussie. Rechargement...', 'success');
                                        setTimeout(function(){ window.location.reload(); }, 1500);
                                }
                        });
                });
        },

        /* ═══════════════════════════════════════
           13. TOAST NOTIFICATIONS
           ═══════════════════════════════════════ */
        toast: function(msg, type) {
                var el = $('#vc-toast');
                if (!el.length) return;
                var icon = '';
                if (type === 'success') icon = '\u2713 ';
                else if (type === 'error') icon = '\u2717 ';
                else if (type === 'saving') icon = '\u23F3 ';

                el.removeClass('show vc-toast-success vc-toast-error vc-toast-saving');
                if (type) el.addClass('vc-toast-' + type);
                el.html(icon + msg).addClass('show');
                clearTimeout(VC._toastTimer);
                VC._toastTimer = setTimeout(function(){ el.removeClass('show'); }, 4000);
        },

        /* ═══════════════════════════════════════
           14. TRACK CHANGES
           ═══════════════════════════════════════ */
        initChangeTracking: function() {
                if (!VC.$form || !VC.$form.length) return;
                VC._initialForm = VC.$form.serialize();
                VC.$form.on('change input', 'input, select, textarea', function(){
                        if (VC.$form.serialize() !== VC._initialForm) { VC.markUnsaved(); }
                        else { VC.markSaved(); }
                });
                $(window).on('beforeunload', function(){
                        if (VC.unsaved) return true;
                });
        },

        /* ═══════════════════════════════════════
           INIT
           ═══════════════════════════════════════ */
        init: function() {
                VC.$form = $('#vc-settings-form');

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

                /* Init sortable on the currently visible panel */
                VC.initRepeaterSortable($('.vc-section-panel.active'));
        }
};

/* Override initNav to also init sortables on panel switch */
var _origInitNav = VC.initNav;
VC.initNav = function() {
        _origInitNav.call(this);
        /* After nav click, init sortable on newly visible panel */
        $(document).on('click', '.vc-nav-item', function(){
                setTimeout(function(){
                        VC.initRepeaterSortable($('.vc-section-panel.active'));
                }, 50);
        });
};

$(document).ready(VC.init);

})(jQuery);