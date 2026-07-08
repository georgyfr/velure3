/**
 * Velure Core — Admin JS v3.5.1
 * Elementor-inspired: sidebar nav, AJAX publish, templates, accordions, repeaters
 *
 * FIXES v3.2:
 *  - Nav: CSS-only panel switching (no .hide()/.show() conflicts)
 *  - Publish: robust form detection + serialized data with explicit nonce
 *  - Guard: graceful fallback if velureCoreAdmin missing
 *
 * v3.5.2: Complete real-time live preview — all controls synced to canvas
 */
(function($){
'use strict';

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

                        /* Update active state on nav items */
                        $('.vc-nav-item').removeClass('active');
                        $(this).addClass('active');

                        /* Switch panels using ONLY CSS class — no .hide()/.show() */
                        $('.vc-section-panel').removeClass('active');

                        var target = $('.vc-section-panel[data-panel="' + panel + '"]');
                        if (target.length) {
                                target.addClass('active');
                        }

                        /* Update header */
                        var label = $(this).find('.vc-nav-item-label').text() || panel;
                        $('#vc-header-title').text(label);
                        $('#vc-breadcrumb-current').text(label);

                        /* Scroll panel to top */
                        var panelEl = document.querySelector('.vc-panel');
                        if (panelEl) panelEl.scrollTop = 0;

                        /* Init sortables on newly visible panel */
                        setTimeout(function(){
                                VC.initRepeaterSortable(target);
                        }, 60);
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

                        /* In Visual Builder mode, open the wide modal (Proposition B) */
                        if ($('#vc-builder-app').length && typeof self.openMediaModal === 'function') {
                                self.openMediaModal(field);
                                return;
                        }

                        /* Classic admin mode: use inline wp.media */
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

                /* Re-find the form every time (more reliable than caching) */
                $('#vc-publish-btn').on('click', function(e){
                        e.preventDefault();

                        var $form = $('#vc-settings-form, #vc-builder-form').first();
                        if (!$form.length) {
                                self.toast('Formulaire introuvable. Rechargez la page.', 'error');
                                console.error('[Velure Core] #vc-settings-form not found');
                                return;
                        }

                        var btn = $(this);
                        btn.prop('disabled', true).html('<span style="display:inline-flex;align-items:center;gap:6px"><span class="vc-spinner"></span> Publication...</span>');
                        self.toast('Publication en cours...', 'saving');

                        /* Build data object explicitly for reliability */
                        var formData = $form.serialize();
                        var ajaxUrl = (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.ajaxUrl : '/wp-admin/admin-ajax.php';

                        $.ajax({
                                url: ajaxUrl,
                                type: 'POST',
                                data: formData,
                                dataType: 'json',
                                success: function(res){
                                        if (res && res.success) {
                                                self.markSaved();
                                                self.toast(res.data && res.data.message ? res.data.message : 'Publie !', 'success');
                                                $('.vc-footer-status span:last-child').text('Derniere sauvegarde : ' + new Date().toLocaleTimeString());
                                                VC._initialForm = $form.serialize();
                                        } else {
                                                var errMsg = (res && res.data && res.data.message) ? res.data.message : 'Erreur lors de la publication.';
                                                self.toast(errMsg, 'error');
                                                console.error('[Velure Core] Save error:', res);
                                        }
                                },
                                error: function(xhr, status, err){
                                        self.toast('Erreur reseau (' + status + '). Veuillez reessayer.', 'error');
                                        console.error('[Velure Core] AJAX error:', status, err);
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
                var ajaxUrl = (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.ajaxUrl : '/wp-admin/admin-ajax.php';
                var nonce = (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.nonce : '';

                /* Export */
                $(document).on('click', '#vc-export-btn', function(){
                        $.post(ajaxUrl, {
                                action: 'velure_core_get_settings',
                                _wpnonce: nonce
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
                        $.post(ajaxUrl, {
                                action: 'velure_core_import_settings',
                                _wpnonce: nonce,
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
                        $.post(ajaxUrl, {
                                action: 'velure_core_reset',
                                _wpnonce: nonce
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
                if (!el.length) {
                        /* Fallback: create toast if missing */
                        $('body').append('<div id="vc-toast" class="vc-toast"></div>');
                        el = $('#vc-toast');
                }
                var icon = '';
                if (type === 'success') icon = '\u2713 ';
                else if (type === 'error') icon = '\u2717 ';
                else if (type === 'saving') icon = '\u23F3 ';
                else if (type === 'info') icon = '\u2139 ';

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
           15. VISUAL BUILDER — ROUTING + RESPONSIVE + TAB SWITCHING
           ═══════════════════════════════════════ */
        initBuilder: function() {
                /* Only run if we're in builder mode (the #vc-builder-app wrapper exists) */
                var $builderApp = $('#vc-builder-app');
                if ( ! $builderApp.length ) return;

                var self = this;
                var $iframe  = $('#vc-builder-canvas');
                var $panel   = $('#vc-panel-form');
                var $wrap    = $('#vc-builder-canvas-wrap');
                var $modeLabel = $('#vc-builder-topbar-mode');

                /* ── DYNAMIC HORIZONTAL TAB SWITCHING ── */
                $(document).on('click', '.vc-builder-section-tab[data-section]', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var $tab = $(this);
                        var section = $tab.data('section');
                        if (!section) return;

                        /* Don't reload if already active */
                        if ($tab.hasClass('active')) return;

                        /* Update active tab */
                        $('.vc-builder-section-tab').removeClass('active');
                        $tab.addClass('active');

                        /* Get tab label for topbar */
                        var tabLabel = $tab.find('.vc-builder-section-tab-label').text() || section;
                        if ($modeLabel.length) $modeLabel.text(tabLabel);

                        /* Update hidden section field */
                        $('#vc-builder-section').val(section);

                        /* 1) INSTANTLY change iframe src to load new section canvas */
                        var canvasUrl = velureCoreAdmin.ajaxUrl.replace('admin-ajax.php', 'admin.php')
                                + '?page=velure-front-page&mode=canvas&section=' + encodeURIComponent(section);

                        if ($iframe.length) {
                                $wrap.addClass('loading').removeClass('loaded');
                                $iframe.attr('src', canvasUrl);
                                $iframe.on('load.vcTab', function() {
                                        $wrap.removeClass('loading').addClass('loaded');
                                        $iframe.off('load.vcTab');
                                });
                        }

                        /* 2) AJAX: Load the new section's form into the panel */
                        $panel.css('opacity', '0.4');
                        $.ajax({
                                url: velureCoreAdmin.ajaxUrl,
                                type: 'POST',
                                data: {
                                        action: 'velure_core_section_form',
                                        section: section,
                                        _ajax_nonce: velureCoreAdmin.nonce
                                },
                                success: function(resp) {
                                        if (resp.success && resp.data && resp.data.html) {
                                                $panel.html(resp.data.html);
                                                /* Re-init accordions, ranges, colors, media, repeaters for new form */
                                                VC.initAccordions();
                                                VC.initRanges();
                                                VC.initColors();
                                                VC.initMedia();
                                                VC.initRepeaters();
                                                VC.initRepeaterSortable($panel.find('.vc-section-panel'));
                                                VC.initConditional();
                                                VC.initRealTimePreview();
                                                $(document).trigger('vc-panel-loaded');
                                        }
                                        $panel.css('opacity', '1');
                                },
                                error: function() {
                                        $panel.css('opacity', '1');
                                }
                        });

                        /* 3) Update browser URL without full page reload */
                        var newUrl = velureCoreAdmin.ajaxUrl.replace('admin-ajax.php', 'admin.php')
                                + '?page=velure-front-page&section=' + encodeURIComponent(section);
                        if (history.pushState) {
                                history.pushState({ section: section }, '', newUrl);
                        }

                        /* 4) Reset widget editor if open */
                        if (typeof VCB !== 'undefined') {
                                VCB.selected = null;
                                $('#vc-bp-widget-editor').hide();
                                VCB.section = section;
                        }

                        self.toast('Section ' + tabLabel + ' chargée', 'info');
                });

                /* ── PopState: handle browser back/forward ── */
                $(window).on('popstate', function(e) {
                        if (e.state && e.state.section) {
                                var $targetTab = $('.vc-builder-section-tab[data-section="' + e.state.section + '"]');
                                if ($targetTab.length) $targetTab.trigger('click');
                        }
                });

                /* ── Responsive toggle ── */
                $(document).on('click', '.vc-builder-resp-btn', function(e){
                        e.preventDefault();
                        $('.vc-builder-resp-btn').removeClass('active');
                        $(this).addClass('active');
                        var width = $(this).data('width');
                        if ( width === '100%' ) {
                                $wrap.css('max-width', 'none');
                                $wrap.css('padding', '0');
                        } else {
                                var numW = parseInt(width, 10);
                                $wrap.css('max-width', numW + 'px');
                                $wrap.css('padding', '0 20px');
                        }
                        $wrap.css('margin', '0 auto');
                });

                /* ── Mark canvas as loaded once iframe fires ── */
                $iframe.on('load', function() {
                        $wrap.removeClass('loading').addClass('loaded');
                });

                /* ── PANEL TOGGLE (Proposition A: collapse / expand / resize) ── */
                self.initPanelToggle();

                /* ── WIX-LIKE: Listen for vc-activate-control from canvas iframe ── */
                self.initCanvasFieldActivation();

                /* ── v3.5.1: Real-time preview from left panel controls ── */
                self.initRealTimePreview();

                /* ── Toast for builder context ── */
                self.toast('Mode Visual Builder actif. Canvas charge...', 'saving');
        },

        /* ═══════════════════════════════════════════════════
           16. PANEL TOGGLE + RESIZE (Proposition A)
           ═══════════════════════════════════════════════════ */
        initPanelToggle: function() {
                var $panel = $('#vc-builder-panel');
                if (!$panel.length) return;

                var self = this;
                var state = 'normal'; /* 'normal' | 'collapsed' | 'expanded' */

                /* ── Toggle button click ── */
                $(document).on('click', '#vc-panel-toggle', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        if (state === 'normal') {
                                /* Collapse */
                                $panel.addClass('panel-collapsed').removeClass('panel-expanded');
                                state = 'collapsed';
                                $(this).attr('title', 'Afficher le panneau (Ctrl+[)');
                        } else {
                                /* Expand back to normal */
                                $panel.removeClass('panel-collapsed panel-expanded');
                                state = 'normal';
                                $(this).attr('title', 'Masquer le panneau (Ctrl+[)');
                        }
                });

                /* ── Keyboard shortcut: Ctrl+[ to toggle ── */
                $(document).on('keydown', function(e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === '[') {
                                e.preventDefault();
                                $('#vc-panel-toggle').trigger('click');
                        }
                });

                /* ── Resize handle drag (320px ↔ 450px) ── */
                var $handle = $('#vc-panel-resize-handle');
                if (!$handle.length) return;

                var isDragging = false;
                var startX = 0;
                var startWidth = 0;

                $handle.on('mousedown', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        isDragging = true;
                        startX = e.clientX;
                        startWidth = $panel.outerWidth();
                        $handle.addClass('resizing');
                        $('body').css('cursor', 'col-resize');
                        $('body').css('user-select', 'none');
                });

                $(document).on('mousemove.vcResize', function(e) {
                        if (!isDragging) return;
                        var diff = e.clientX - startX;
                        var newWidth = Math.min(450, Math.max(320, startWidth + diff));
                        $panel.css({
                                'width': newWidth + 'px',
                                'min-width': newWidth + 'px',
                                'max-width': newWidth + 'px'
                        });
                });

                $(document).on('mouseup.vcResize', function() {
                        if (!isDragging) return;
                        isDragging = false;
                        $handle.removeClass('resizing');
                        $('body').css('cursor', '');
                        $('body').css('user-select', '');

                        /* Snap to expanded state if close enough */
                        var currentWidth = $panel.outerWidth();
                        if (currentWidth > 400) {
                                $panel.addClass('panel-expanded').css({ width: '', minWidth: '', maxWidth: '' });
                                state = 'expanded';
                        } else {
                                $panel.removeClass('panel-expanded').css({ width: '', minWidth: '', maxWidth: '' });
                                state = 'normal';
                        }
                });
        },

        /* ═══════════════════════════════════════════════════
           17. MODAL SYSTEM (Proposition B)
           Reusable modal for media library, text editing, etc.
           ═══════════════════════════════════════════════════ */
        _modalOverlay: null,

        openModal: function(options) {
                var self = this;
                var opts = $.extend({
                        title: 'Fenetre',
                        titleIcon: '',
                        type: 'generic', /* 'generic' | 'media' | 'text' */
                        content: '',
                        onClose: null,
                        onReady: null
                }, options);

                /* Remove any existing modal */
                self.closeModal();

                var $overlay = $(
                        '<div class="vc-modal-overlay" id="vc-modal-overlay">' +
                                '<div class="vc-modal-container vc-modal-' + opts.type + '">' +
                                        '<div class="vc-modal-header">' +
                                                '<div class="vc-modal-title">' +
                                                        (opts.titleIcon ? '<span class="vc-modal-title-icon">' + opts.titleIcon + '</span>' : '') +
                                                        '<span>' + opts.title + '</span>' +
                                                '</div>' +
                                                '<button type="button" class="vc-modal-close" id="vc-modal-close">' +
                                                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                                                '</button>' +
                                        '</div>' +
                                        '<div class="vc-modal-body" id="vc-modal-body">' + opts.content + '</div>' +
                                '</div>' +
                        '</div>'
                );

                $('body').append($overlay);
                self._modalOverlay = $overlay;

                /* Close handlers */
                $overlay.on('click', '#vc-modal-close', function() {
                        self.closeModal();
                        if (typeof opts.onClose === 'function') opts.onClose();
                });
                $overlay.on('click', function(e) {
                        if (e.target === this) {
                                self.closeModal();
                                if (typeof opts.onClose === 'function') opts.onClose();
                        }
                });
                /* Escape key */
                $(document).on('keydown.vcModal', function(e) {
                        if (e.key === 'Escape' && self._modalOverlay) {
                                self.closeModal();
                                if (typeof opts.onClose === 'function') opts.onClose();
                        }
                });

                /* Animate in */
                requestAnimationFrame(function() {
                        $overlay.addClass('active');
                });

                if (typeof opts.onReady === 'function') {
                        opts.onReady($overlay);
                }

                return $overlay;
        },

        closeModal: function() {
                if (this._modalOverlay) {
                        this._modalOverlay.remove();
                        this._modalOverlay = null;
                }
                $(document).off('keydown.vcModal');
                /* Detach WP media modal if it was appended to our overlay */
                $('.media-modal-backdrop, .media-modal').appendTo('body').hide();
        },

        /**
         * Open the WordPress Media Library in a wide modal (Proposition B).
         * @param {jQuery} $field - The .vc-image-field container
         */
        openMediaModal: function($field) {
                var self = this;

                if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                        self.toast('WordPress Media Library non disponible.', 'error');
                        return;
                }

                var wrap = $field.find('.vc-img-wrap');
                var input = $field.find('.vc-img-id');
                var currentImg = wrap.find('.vc-img-preview').attr('src') || '';

                /* Build modal content with preview + action buttons */
                var content = '';
                if (currentImg) {
                        content += '<img src="' + currentImg + '" class="vc-modal-media-preview" alt="Apercu" />';
                }
                content += '<div class="vc-modal-media-actions">';
                content += '<button type="button" class="vc-btn-modal vc-btn-modal-primary" id="vc-modal-choose-media">';
                content += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';
                content += ' Choisir une image</button>';
                content += '<button type="button" class="vc-btn-modal" id="vc-modal-remove-media" style="' + (currentImg ? '' : 'display:none;') + '">';
                content += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>';
                content += ' Supprimer</button>';
                content += '</div>';

                self.openModal({
                        title: 'Gerer l\'image',
                        titleIcon: '&#128247;',
                        type: 'media',
                        content: content,
                        onReady: function($overlay) {
                                /* "Choose image" opens WP Media Library */
                                $overlay.find('#vc-modal-choose-media').on('click', function() {
                                        var frame = wp.media({
                                                title: 'Choisir une image',
                                                button: { text: 'Utiliser cette image' },
                                                multiple: false,
                                                library: { type: 'image' }
                                        });
                                        frame.on('select', function() {
                                                var att = frame.state().get('selection').first().toJSON();
                                                input.val(att.id);
                                                wrap.html('<img src="' + att.url + '" class="vc-img-preview" />');
                                                $field.find('.vc-img-remove').show();
                                                self.markUnsaved();

                                                /* Update modal preview */
                                                var $preview = $overlay.find('.vc-modal-media-preview');
                                                if ($preview.length) {
                                                        $preview.attr('src', att.url).show();
                                                } else {
                                                        $overlay.find('.vc-modal-body').prepend(
                                                                '<img src="' + att.url + '" class="vc-modal-media-preview" alt="Apercu" />'
                                                        );
                                                }
                                                $overlay.find('#vc-modal-remove-media').show();
                                        });
                                        frame.open();
                                });

                                /* "Remove" clears the image */
                                $overlay.find('#vc-modal-remove-media').on('click', function() {
                                        input.val(0);
                                        wrap.html('<div class="vc-img-placeholder"><span class="vc-img-placeholder-icon">&#128247;</span>Cliquer ou glisser une image</div>');
                                        $field.find('.vc-img-remove').hide();
                                        self.markUnsaved();
                                        self.closeModal();
                                });
                        }
                });
        },

        /**
         * Open a wide modal for text editing (Proposition B).
         * @param {jQuery} $textarea - The original textarea element
         */
        openTextModal: function($textarea) {
                var self = this;
                var fieldName = $textarea.closest('.vc-field').find('label').text() || 'Texte';
                var currentValue = $textarea.val();

                var content = '<textarea class="vc-modal-textarea" id="vc-modal-textarea">' + currentValue + '</textarea>';

                self.openModal({
                        title: 'Editer : ' + fieldName,
                        titleIcon: '&#9998;',
                        type: 'text',
                        content: content,
                        onClose: function() {
                                /* Sync modal content back to original field */
                                var $modalTa = $('#vc-modal-textarea');
                                if ($modalTa.length && $modalTa.val() !== currentValue) {
                                        $textarea.val($modalTa.val()).trigger('change');
                                        self.markUnsaved();
                                }
                        }
                });
        },

        /* ═══════════════════════════════════════════════════
           18. WIX-LIKE CANVAS FIELD ACTIVATION
           When user clicks an element in the iframe, find the
           matching form field in the panel, open accordion/
           repeater, scroll to it, and focus it.
           ═══════════════════════════════════════════════════ */
        initCanvasFieldActivation: function() {
                var self = this;

                window.addEventListener('message', function(e) {
                        if (!e.data || e.data.action !== 'vc-activate-control') return;
                        var fieldKey = e.data.key;
                        if (!fieldKey) return;

                        /* Expand panel if collapsed */
                        var $panel = $('#vc-builder-panel');
                        if ($panel.hasClass('panel-collapsed')) {
                                $panel.removeClass('panel-collapsed panel-expanded');
                        }

                        /* Find the field by name attribute */
                        var $field = null;

                        /* Strategy 1: Direct match by name */
                        $field = $('[name="' + fieldKey + '"]');

                        /* Strategy 2: For repeater template fields, match by data-index
                           e.g. fieldKey="hero_slides[0][title]" → find in repeater row with data-index="0" */
                        if (!$field.length) {
                                /* Extract index from key like hero_slides[0][title] */
                                var match = fieldKey.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
                                if (match) {
                                        var base = match[1];     // hero_slides
                                        var idx  = match[2];     // 0
                                        var sub  = match[3];     // title
                                        /* Look for a repeater row with this index */
                                        var $rows = $('.vc-repeater-row:not(.vc-repeater-template)');
                                        $rows.each(function() {
                                                var rowIdx = $(this).attr('data-index');
                                                if (rowIdx === idx) {
                                                        var $f = $(this).find('[name$="[' + sub + ']"]');
                                                        if ($f.length) {
                                                                $field = $f;
                                                                return false; /* break */
                                                        }
                                                }
                                        });
                                }
                        }

                        /* Strategy 3: Partial match — sometimes the form name uses a slightly
                           different format. Try matching the key ending. */
                        if (!$field.length) {
                                /* Normalize: hero_slides[0][title] → look for name ending with [title] */
                                var bracketMatch = fieldKey.match(/\[(\w+)\]$/);
                                if (bracketMatch) {
                                        var lastKey = bracketMatch[1];
                                        /* Only use this fallback for non-ambiguous short keys */
                                        if (lastKey.length > 2) {
                                                $field = $('[name$="[' + lastKey + ']"]').first();
                                        }
                                }
                        }

                        if (!$field.length) {
                                console.log('[VC] Field not found for key:', fieldKey);
                                return;
                        }

                        /* ── Open parent accordion(s) ── */
                        self._openParentAccordions($field);

                        /* ── Open parent repeater row if collapsed ── */
                        var $repeaterRow = $field.closest('.vc-repeater-row');
                        if ($repeaterRow.length && !$repeaterRow.hasClass('open')) {
                                $repeaterRow.addClass('open');
                        }

                        /* ── Scroll panel to the field ── */
                        var $panelForm = $('#vc-panel-form');
                        if ($panelForm.length) {
                                var fieldTop = $field.offset().top - $panelForm.offset().top + $panelForm.scrollTop();
                                $panelForm.animate({ scrollTop: fieldTop - 20 }, 300);
                        }

                        /* ── Focus the field with highlight animation ── */
                        setTimeout(function() {
                                $field.focus();

                                /* Brief highlight glow */
                                $field.css({
                                        'box-shadow': '0 0 0 3px var(--vc-accent-glow), 0 0 12px var(--vc-accent-glow)',
                                        'transition': 'box-shadow 0.3s ease'
                                });
                                setTimeout(function() {
                                        $field.css({
                                                'box-shadow': '',
                                                'transition': 'box-shadow 0.6s ease'
                                        });
                                }, 1200);
                        }, 350);
                });
        },

        /**
         * Open all ancestor accordions (.vc-accordion-item) for a given $field.
         */
        _openParentAccordions: function($field) {
                $field.parents('.vc-accordion-item').each(function() {
                        if (!$(this).hasClass('open')) {
                                $(this).addClass('open');
                        }
                });
        },

        /* ═══════════════════════════════════════════════════════════════
           19. REAL-TIME LIVE PREVIEW — Left Panel Controls → Canvas
           v3.5.1: Wix-like instant sync for ALL controls in the
           left panel (ranges, selects, colors, text fields, images).
           Uses input event (continuous) instead of change (on blur).
           ═══════════════════════════════════════════════════════════════ */
        initRealTimePreview: function() {
                var self = this;
                var $panel = $('#vc-panel-form');
                var $iframe = $('#vc-builder-canvas');
                if (!$panel.length || !$iframe.length) return;

                /* Send a message to the canvas iframe */
                function send(msg) {
                        var win = $iframe[0] && $iframe[0].contentWindow;
                        if (win) win.postMessage(msg, '*');
                }

                /* Determine current section from the active tab */
                function currentSection() {
                        return $('.vc-builder-section-tab.active').data('section') || '';
                }

                /* ── RANGE SLIDERS: input event (continuous drag) ── */
                $panel.on('input', 'input[type="range"][name]', function() {
                        var name = $(this).attr('name');
                        var val  = $(this).val();
                        self._pushPanelPreview(name, val, currentSection(), send);
                });

                /* ── COLOR PICKERS: input event (continuous) ── */
                $panel.on('input', 'input[type="color"]', function() {
                        var name = $(this).attr('name');
                        var val  = $(this).val();
                        self._pushPanelPreview(name, val, currentSection(), send);
                });

                /* ── SELECT DROPDOWNS: input+change event (continuous) ── */
                $panel.on('input change', 'select[name]', function() {
                        var name = $(this).attr('name');
                        var val  = $(this).val();
                        self._pushPanelPreview(name, val, currentSection(), send);
                });

                /* ── NUMBER INPUTS: input event (continuous typing) ── */
                $panel.on('input', 'input[type="number"][name]', function() {
                        var name = $(this).attr('name');
                        var val  = $(this).val();
                        self._pushPanelPreview(name, val, currentSection(), send);
                });

                /* ── TEXT INPUTS: input with debounce 150ms ── */
                var textTimer = null;
                $panel.on('input', 'input[type="text"][name], textarea[name]', function() {
                        var name = $(this).attr('name');
                        var val  = $(this).val();
                        clearTimeout(textTimer);
                        textTimer = setTimeout(function() {
                                self._pushPanelPreview(name, val, currentSection(), send);
                        }, 150);
                });

                /* ── TOGGLE SWITCHES ── */
                $panel.on('change', 'input[type="checkbox"][name]', function() {
                        var name = $(this).attr('name');
                        var val  = this.checked ? '1' : '0';
                        self._pushPanelPreview(name, val, currentSection(), send);
                });

                /* ── IMAGE CHANGES (after media modal closes) ──
                   We watch for changes to hidden inputs that store attachment IDs */
                var imageObserver = new MutationObserver(function(mutations) {
                        mutations.forEach(function(m) {
                                if (m.type === 'attributes' && m.attributeName === 'value') {
                                        var $input = $(m.target);
                                        if ($input.hasClass('vc-img-id')) {
                                                var name = $input.attr('name');
                                                var val  = $input.val();
                                                /* Get the image URL from the preview */
                                                var url = $input.closest('.vc-image-field')
                                                        .find('.vc-img-preview').attr('src') || '';
                                                if (url) {
                                                        self._pushPanelPreview(name, url, currentSection(), send);
                                                } else {
                                                        /* Fallback: AJAX partial render for image changes without URL */
                                                        self._requestPanelPartialRender(name, val, currentSection());
                                                }
                                        }
                                }
                        });
                });
                /* Observe all existing and future image ID inputs */
                $panel.find('.vc-img-id').each(function() {
                        imageObserver.observe(this, { attributes: true });
                });
                /* Also catch dynamically added inputs via AJAX tab load */
                self._imageObserver = imageObserver;
                $(document).on('vc-panel-loaded', function() {
                        $panel.find('.vc-img-id').each(function() {
                                imageObserver.observe(this, { attributes: true });
                        });
                });
        },

        /**
         * Map a panel field name to a canvas preview action.
         * This is the left-panel equivalent of VCB.onFieldChange.
         * v3.5.2: Complete — handles ALL field types including
         * non-hero text, image URLs, and structural changes.
         * @param {string} name     Form field name attribute
         * @param {string} value    Current value
         * @param {string} section  Current section slug
         * @param {Function} send   postMessage sender
         */
        _pushPanelPreview: function(name, value, section, send) {
                if (!section) return;

                /* ── Convert bracket notation to dot notation for CSS_MAP lookup ──
                   e.g. hero_slides[0][title] → hero_slides.0.title */
                var dotKey = name.replace(/\[/g, '.').replace(/\]/g, '');

                /* Check VCB.CSS_MAP if available (visual-builder.js is loaded) */
                var def = (typeof VCB !== 'undefined' && VCB.CSS_MAP) ? VCB.CSS_MAP[dotKey] : null;
                if (def) {
                        /* TYPE 1: Widget inline CSS */
                        if (def.css) {
                                var cssVal = def.transform ? def.transform(value) : value;
                                var numVal = parseFloat(cssVal);
                                if (!isNaN(numVal) && !def.transform) cssVal = numVal + (def.unit || '');
                                var css = {};
                                css[def.css] = cssVal;
                                if (def.also) {
                                        var av = def.transform ? def.transform(value) : value;
                                        var an = parseFloat(av);
                                        css[def.also] = (!isNaN(an) && !def.transform) ? an + (def.alsoUnit || def.unit || '') : av;
                                }
                                var tw = def.widget;
                                if (tw) send({ action: 'vc-apply-style', widget: tw, css: css });
                                return;
                        }

                        /* TYPE 2: CSS custom property */
                        if (def.cssVar) {
                                var cv = (def.unit && !def.transform) ? value + def.unit : (def.transform ? def.transform(value) : value);
                                send({ action: 'vc-apply-css-var', widget: def.widget || '__section__', property: def.cssVar, value: cv });
                                return;
                        }

                        /* TYPE 3: Section CSS */
                        if (def.sectionCss) {
                                var sv = def.transform ? def.transform(value) : value;
                                var sn = parseFloat(sv);
                                if (!isNaN(sn) && !def.transform) sv = sn + (def.unit || '');
                                var sc = {};
                                sc[def.sectionCss] = sv;
                                if (def.also) {
                                        var asv = def.transform ? def.transform(value) : value;
                                        var asn = parseFloat(asv);
                                        sc[def.also] = (!isNaN(asn) && !def.transform) ? asn + (def.alsoUnit || def.unit || '') : asv;
                                }
                                send({ action: 'vc-apply-section-css', css: sc });
                                return;
                        }

                        /* TYPE 4: Class toggle */
                        if (def['class']) {
                                var cm = def['class'];
                                var cd = cm[String(value)];
                                if (cd) {
                                        if (def.section) {
                                                send({ action: 'vc-apply-class', widget: '__section__', add: cd.add || '', remove: cd.remove || '' });
                                        } else if (def.widget) {
                                                send({ action: 'vc-apply-class', widget: def.widget, add: cd.add || '', remove: cd.remove || '' });
                                        }
                                }
                                return;
                        }

                        /* TYPE 5: Background image */
                        if (def.bgImage) {
                                /* For image IDs, we need the URL — value might be a URL already */
                                var url = (value && !/^\d+$/.test(value)) ? value : '';
                                if (url) {
                                        send({ action: 'vc-update-bg-image', widget: def.widget || '', url: url });
                                }
                                return;
                        }

                        /* TYPE 6: { none } marker — server-render only, fall through */
                        if (def.none) {
                                /* Intentionally no live preview */
                        }
                }

                /* ── TEXT CONTENT: Hero text fields (widget-based) ── */
                var heroTextWidgets = {
                        'hero_slides.0.eyebrow':  'hero-eyebrow',
                        'hero_slides.0.title':    'hero-title',
                        'hero_slides.0.subtitle': 'hero-subtitle',
                        'hero_slides.0.cta_text': 'hero-cta',
                };
                if (heroTextWidgets[dotKey]) {
                        send({ action: 'vc-apply-text', widget: heroTextWidgets[dotKey], text: value, key: dotKey });
                        return;
                }

                /* ── TEXT CONTENT: Non-hero text fields (key-based selector) ──
                   These use the key so canvas-bridge.js TEXT_SELECTOR_MAP
                   can find the right DOM element even without a widget ID. */
                var nonHeroTextKeys = [
                        'cat_eyebrow', 'section_title_categories', 'cat_description', 'cat_cta_text',
                        'prod_eyebrow', 'section_title_products', 'prod_description', 'prod_cta_text',
                        'testi_eyebrow', 'section_title_testimonials', 'testi_description',
                        'blog_eyebrow', 'section_title_blog', 'blog_description', 'blog_cta_text',
                        'ig_eyebrow', 'instagram_handle',
                        'hs_bestseller_label', 'hs_bestseller_title', 'hs_bestseller_price',
                                'hs_bestseller_cta', 'hs_category_label', 'hs_category_title',
                        'sb_left_eyebrow', 'sb_left_title', 'sb_left_desc', 'sb_left_cta_text',
                                'sb_right_eyebrow', 'sb_right_title', 'sb_right_desc', 'sb_right_cta_text',
                ];
                if (nonHeroTextKeys.indexOf(dotKey) !== -1 || nonHeroTextKeys.indexOf(name) !== -1) {
                        send({ action: 'vc-apply-text', widget: '__section__', text: value, key: dotKey || name });
                        return;
                }

                /* ── IMAGE FIELDS: Resolve attachment ID to URL via WordPress ── */
                var imageFields = ['hs_bestseller_image', 'hs_category_image', 'sb_left_image', 'sb_right_image'];
                if (imageFields.indexOf(name) !== -1 || imageFields.indexOf(dotKey) !== -1) {
                        /* If value is already a URL, send directly */
                        if (value && !/^\d+$/.test(value)) {
                                send({ action: 'vc-update-bg-image', widget: '__section__', url: value, key: dotKey || name });
                                return;
                        }
                        /* If value is an attachment ID, resolve URL via REST API */
                        if (value && /^\d+$/.test(value)) {
                                VC._resolveImageUrl(parseInt(value, 10), function(resolvedUrl) {
                                        if (resolvedUrl) {
                                                send({ action: 'vc-update-bg-image', widget: '__section__', url: resolvedUrl, key: dotKey || name });
                                        }
                                });
                                return;
                        }
                }

                /* ── STRUCTURAL CHANGES: AJAX partial render for everything else ──
                   Count changes, column changes, new blocks, sort order, etc.
                   Debounced 300ms to avoid flooding. */
                VC._requestPanelPartialRender(dotKey || name, value, section);
        },

        /**
         * Resolve a WordPress attachment ID to its URL.
         * @param {number}   id       Attachment post ID
         * @param {Function} callback Receives the URL string
         */
        _resolveImageUrl: function(id, callback) {
                if (typeof wp === 'undefined' || !wp.api || !wp.api.request) {
                        callback('');
                        return;
                }
                wp.api.request({ path: '/wp/v2/media/' + id + '?_fields=source_url' })
                        .then(function(res) {
                                callback(res && res.source_url ? res.source_url : '');
                        })
                        .fail(function() {
                                callback('');
                        });
        },

        /**
         * Request a surgical AJAX partial render from the server.
         * Used by the left panel for structural changes that can't be
         * handled by CSS alone (count changes, new blocks, sort, etc.)
         * Debounced to 300ms.
         * @param {string} key     Settings key (dot-notation)
         * @param {string} value   New value
         * @param {string} section Current section slug
         */
        _panelPartialTimer: null,
        _requestPanelPartialRender: function(key, value, section) {
                if (!section) return;
                var $iframe = $('#vc-builder-canvas');

                clearTimeout(VC._panelPartialTimer);
                VC._panelPartialTimer = setTimeout(function() {
                        $.ajax({
                                url:  (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.ajaxUrl : '/wp-admin/admin-ajax.php',
                                type: 'POST',
                                data: {
                                        action:  'vc_render_component_preview',
                                        section: section,
                                        key:     key,
                                        value:   value,
                                        _ajax_nonce: (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.nonce : '',
                                },
                                success: function(res) {
                                        if (res && res.success && res.data && res.data.html) {
                                                var win = $iframe[0] && $iframe[0].contentWindow;
                                                if (win) {
                                                        win.postMessage({ action: 'vc-replace-html', html: res.data.html }, '*');
                                                }
                                        }
                                }
                        });
                }, 300);
        },

        /* ═══════════════════════════════════════
           INIT
           ═══════════════════════════════════════ */
        init: function() {
                /* Route: Builder mode or Classic mode */
                var isBuilder = $('#vc-builder-app').length > 0;

                if ( isBuilder ) {
                        VC.initBuilder();
                        /* initPublish is NOT called here — visual-builder.js handles publish in builder mode */
                        return;
                }

                /* Classic admin mode */
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

$(document).ready(VC.init);

})(jQuery);