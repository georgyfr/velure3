/**
 * Velure Core — Admin JS v3.5.3
 * Elementor-inspired: sidebar nav, AJAX publish, templates, accordions, repeaters
 *
 * FIXES v3.2:
 *  - Nav: CSS-only panel switching (no .hide()/.show() conflicts)
 *  - Publish: robust form detection + serialized data with explicit nonce
 *  - Guard: graceful fallback if velureCoreAdmin missing
 *
 * v3.5.3: Media library + dynamic block add/remove synced to canvas — all controls synced to canvas
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
        /* Maps repeater container IDs → canvas widget base IDs */
        _repeaterWidgetMap: {
                'vc-hero-slides-repeater': '',
                'vc-trust-features-repeater': 'feature-item',
                'vc-brand-names-repeater': '',
                'vc-instagram-images-repeater': 'ig-item',
        },

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

                /* v3.5.3: Attach widget-base for canvas sync on delete */
                var containerId = containerEl.attr('id') || '';
                var widgetBase = VC._repeaterWidgetMap[containerId] || '';
                if (widgetBase) {
                        clone.attr('data-widget-base', widgetBase);
                }
        },

        initRepeaters: function() {
                var self = this;

                /* Add button — creates a new block and syncs to canvas */
                $(document).on('click', '.vc-repeater-add', function(e){
                        e.preventDefault();
                        e.stopPropagation();

                        var repeaterId = $(this).data('repeater');
                        var template = $('#' + repeaterId + '-template');
                        var container = $('#' + repeaterId + '');

                        if (!template.length || !container.length) {
                                var parent = $(this).closest('.vc-accordion-body, .vc-section-panel');
                                template = parent.find('.vc-repeater-template').first();
                                container = parent.find('.vc-repeater').first();
                        }
                        if (!template.length || !container.length) return;

                        var label = template.data('label') || container.data('label') || 'Element';
                        self._addRepeaterRow(template, container, label);
                        self.markUnsaved();

                        /* v3.5.3: Sync structural change to canvas iframe */
                        self._syncStructuralChange();
                });

                /* Remove row — deletes block from panel AND canvas */
                $(document).on('click', '.vc-repeater-remove', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var row = $(this).closest('.vc-repeater-row');

                        /* v3.5.3: Tell canvas to remove the widget before DOM removal */
                        var widgetBase = row.data('widget-base');
                        var widgetIndex = row.attr('data-index');
                        if (widgetBase && widgetIndex !== undefined) {
                                var widgetId = widgetBase + '-' + widgetIndex;
                                self._sendToCanvas({ action: 'vc-remove-widget', widget: widgetId });
                        }

                        row.fadeOut(200, function(){
                                $(this).remove();
                                /* Re-index remaining rows */
                                container.find('.vc-repeater-row:not(.vc-repeater-template)').each(function(i){
                                        $(this).attr('data-index', i);
                                        $(this).find('[name]').each(function(){
                                                var name = $(this).attr('name');
                                                if (!name) return;
                                                name = name.replace(/\[\d+\]/g, '[' + i + ']');
                                                $(this).attr('name', name);
                                        });
                                });
                        });

                        self.markUnsaved();
                        /* v3.5.3: Sync after removal */
                        self._syncStructuralChange();
                });

                /* Toggle collapse */
                $(document).on('click', '.vc-repeater-row-header', function(e){
                        if ($(e.target).closest('.vc-repeater-remove, .vc-repeater-toggle-btn').length) return;
                        $(this).closest('.vc-repeater-row').toggleClass('open');
                });

                /* v3.5.3: Tag existing rows with widget-base for canvas sync */
                self._tagRepeaterRows();
        },

        /** Tag all existing repeater rows with data-widget-base */
        _tagRepeaterRows: function() {
                $('.vc-repeater').each(function(){
                        var containerId = $(this).attr('id') || '';
                        var widgetBase = VC._repeaterWidgetMap[containerId] || '';
                        if (widgetBase) {
                                $(this).find('.vc-repeater-row:not(.vc-repeater-template)').each(function(i){
                                        $(this).attr('data-widget-base', widgetBase).attr('data-index', i);
                                });
                        }
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
        /* ═══════════════════════════════════════
           4. MEDIA UPLOAD — with canvas sync
           ═══════════════════════════════════════ */
        initMedia: function() {
                var self = this;
                $(document).on('click', '.vc-img-btn', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var field = $(this).closest('.vc-image-field');

                        /* In Visual Builder mode, open the wide modal */
                        if ($('#vc-builder-app').length && typeof self.openMediaModal === 'function') {
                                self.openMediaModal(field);
                                return;
                        }

                        /* Classic admin mode: use inline wp.media */
                        self._openWpMedia(field);
                });

                $(document).on('click', '.vc-img-remove', function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        var field = $(this).closest('.vc-image-field');
                        self._clearImage(field);
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
        /**
         * Open Media Modal (Builder mode) — Wix-like.
         * Selects image from WP media library, updates panel preview,
         * AND pushes the image to the canvas iframe in real-time.
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
                var fieldName = input.attr('name') || '';
                var currentImg = wrap.find('.vc-img-preview').attr('src') || '';

                /* Build modal content */
                var content = '<div class="vc-modal-media-preview-wrap">';
                if (currentImg) {
                        content += '<img src="' + currentImg + '" class="vc-modal-media-preview" alt="Apercu" />';
                } else {
                        content += '<div class="vc-img-placeholder" style="padding:40px 20px;text-align:center;">&#128247; Aucune image selectionnee</div>';
                }
                content += '</div>';
                content += '<div class="vc-modal-media-actions">';
                content += '<button type="button" class="vc-btn-modal vc-btn-modal-primary" id="vc-modal-choose-media">&#128194; Choisir une image</button>';
                content += '<button type="button" class="vc-btn-modal" id="vc-modal-remove-media" style="' + (currentImg ? '' : 'display:none;') + '">&#128465; Supprimer</button>';
                content += '</div>';

                self.openModal({
                        title: 'Gerer l\'image',
                        titleIcon: '&#128247;',
                        type: 'media',
                        content: content,
                        onReady: function($overlay) {
                                /* Open WP Media Library */
                                $overlay.find('#vc-modal-choose-media').on('click', function() {
                                        var frame = wp.media({
                                                title: 'Choisir une image',
                                                button: { text: 'Utiliser cette image' },
                                                multiple: false,
                                                library: { type: 'image' }
                                        });
                                        frame.on('select', function() {
                                                var att = frame.state().get('selection').first().toJSON();
                                                var url = att.url;
                                                var id = att.id;

                                                /* Update panel */
                                                input.val(id);
                                                wrap.html('<img src="' + url + '" class="vc-img-preview" />');
                                                $field.find('.vc-img-remove').show();
                                                self.markUnsaved();

                                                /* Update modal preview */
                                                var $previewWrap = $overlay.find('.vc-modal-media-preview-wrap');
                                                $previewWrap.html('<img src="' + url + '" class="vc-modal-media-preview" alt="Apercu" />');
                                                $overlay.find('#vc-modal-remove-media').show();

                                                /* ═══ v3.5.3: Push image to canvas iframe ═══ */
                                                self._pushImageToCanvas(fieldName, url);
                                        });
                                        frame.open();
                                });

                                /* Remove image */
                                $overlay.find('#vc-modal-remove-media').on('click', function() {
                                        self._clearImage($field);
                                        self.closeModal();
                                });
                        }
                });
        },

        /* ═══════════════════════════════════════
           UTILITY: Send message to canvas iframe
           ═══════════════════════════════════════ */
        _sendToCanvas: function(msg) {
                var $iframe = $('#vc-builder-canvas');
                if (!$iframe.length) return;
                var win = $iframe[0] && $iframe[0].contentWindow;
                if (win) win.postMessage(msg, '*');
        },

        /* ═══════════════════════════════════════
           UTILITY: Push image URL to canvas after media selection
           ═══════════════════════════════════════ */
        _pushImageToCanvas: function(fieldName, url) {
                if (!url || !fieldName) return;
                var section = $('.vc-builder-section-tab.active').data('section') || '';
                if (!section) return;

                /* Convert bracket to dot notation */
                var dotKey = fieldName.replace(/\\[/g, '.').replace(/\\]/g, '');

                /* Check CSS_MAP for bgImage entries */
                var def = (typeof VCB !== 'undefined' && VCB.CSS_MAP) ? VCB.CSS_MAP[dotKey] : null;
                if (def && def.bgImage) {
                        this._sendToCanvas({ action: 'vc-update-bg-image', widget: def.widget || '__section__', url: url });
                        return;
                }

                /* For hero slide image */
                if (dotKey === 'hero_slides.0.image') {
                        this._sendToCanvas({ action: 'vc-update-bg-image', widget: 'hero-bg', url: url });
                        return;
                }

                /* Fallback: AJAX partial render to handle image in server context */
                this._requestPanelPartialRender(dotKey, url, section);
        },

        /* ═══════════════════════════════════════
           UTILITY: Clear image field + sync canvas
           ═══════════════════════════════════════ */
        _clearImage: function($field) {
                var self = this;
                var wrap = $field.find('.vc-img-wrap');
                var input = $field.find('.vc-img-id');
                var fieldName = input.attr('name') || '';
                wrap.html('<div class="vc-img-placeholder"><span class="vc-img-placeholder-icon">&#128247;</span>Cliquer ou glisser une image</div>');
                input.val(0);
                $field.find('.vc-img-remove').hide();
                this.markUnsaved();

                /* v3.5.3: Push image removal to canvas */
                if (fieldName) this._pushImageToCanvas(fieldName, '');
        },

        /* ═══════════════════════════════════════
           UTILITY: Open WP media inline (classic mode)
           ═══════════════════════════════════════ */
        _openWpMedia: function($field) {
                var self = this;
                if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                        self.toast('WordPress Media Library non disponible.', 'error');
                        return;
                }

                var wrap = $field.find('.vc-img-wrap');
                var input = $field.find('.vc-img-id');
                var fieldName = input.attr('name') || '';

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
                        $field.find('.vc-img-remove').show();
                        self.markUnsaved();
                });
                frame.open();
        },

        /* ═══════════════════════════════════════
           UTILITY: Sync structural change to canvas via AJAX
           Called after add/remove block to re-render the section.
           ═══════════════════════════════════════ */
        _syncStructuralChange: function() {
                var section = $('.vc-builder-section-tab.active').data('section') || '';
                if (!section) return;
                this._requestPanelPartialRender('__structural__', '1', section);
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