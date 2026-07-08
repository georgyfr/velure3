/**
 * Velure Core — Canvas Bridge v3.5.2
 * Injected ONLY inside the canvas iframe (&mode=canvas).
 *
 * Responsibilities:
 *  1. Attribute data-vc-widget to all editable elements
 *  2. Hover → light blue outline
 *  3. Click → dashed blue halo + postMessage to parent
 *  4. Double-click on text → contenteditable + green outline
 *  5. Receive postMessage from parent and apply in REAL-TIME:
 *     - vc-apply-style       → inline style on widget element via style.setProperty
 *     - vc-apply-text        → textContent / innerHTML update
 *     - vc-apply-css-var     → CSS custom property on section/widget
 *     - vc-apply-section-css → CSS on the section container
 *     - vc-apply-class       → toggle CSS classes (supports __section__ sentinel)
 *     - vc-replace-html      → surgical HTML fragment replacement (preserves inline styles)
 *     - vc-replace-widget-html → surgical widget HTML replacement
 *     - vc-update-bg-image   → background-image change
 *     - vc-deselect          → deselect all
 *     - vc-select-widget     → programmatic selection
 *
 * @package VelureCore
 * @since 3.2.1
 * @updated 3.5.2 Fixed __section__ sentinel, rich text, style preservation on replace
 */

(function () {
        'use strict';

        /* ── Detect section from body attribute (set by canvas-renderer.php) ── */
        var section = document.body.getAttribute('data-vc-section') || '';
        if (!section) return;

        var selectedEl = null;
        var editingEl  = null;

        /* ═══════════════════════════════════════════════════════════════════
           INLINE STYLE CACHE
           Stores inline styles applied by postMessage so they survive
           section HTML replacements. Keyed by widget ID.
           ═══════════════════════════════════════════════════════════════════ */
        var _inlineStyleCache = {};

        /* ═══════════════════════════════════════════════════════════════════
           SECTION CONTAINER SELECTOR MAP
           Maps section slug → CSS selector for the section wrapper.
           Used by vc-apply-section-css and vc-replace-html to target
           the correct container.
           ═══════════════════════════════════════════════════════════════════ */
        var SECTION_SELECTOR = {
                hero:         '.v-hero-slider',
                features:     '.velure-features',
                categories:   '.velure-section',
                products:     '.velure-section',
                split_banner: '.velure-split-banner',
                marquee:      '.velure-marquee',
                testimonials: '.velure-section',
                blog:         '.velure-section',
                instagram:    '.velure-section',
        };

        /* ═══════════════════════════════════════════════════════════════════
           TEXT ELEMENT SELECTOR MAP
           Maps setting keys (dot-notation) → { selector, property }
           Used by vc-apply-text to find the correct DOM element when
           no data-vc-widget match is found. This handles non-hero text.
           ═══════════════════════════════════════════════════════════════════ */
        var TEXT_SELECTOR_MAP = {
                /* Categories */
                'cat_eyebrow':              { selector: '.velure-section-heading .velure-eyebrow', prop: 'textContent' },
                'section_title_categories': { selector: '.velure-section-heading h2', prop: 'textContent' },
                'cat_description':          { selector: '.velure-section-heading p', prop: 'innerHTML' },
                'cat_cta_text':             { selector: '.velure-section .velure-btn-outline', prop: 'textContent' },

                /* Products */
                'prod_eyebrow':              { selector: '.velure-section-heading .velure-eyebrow', prop: 'textContent' },
                'section_title_products':    { selector: '.velure-section-heading h2', prop: 'textContent' },
                'prod_description':          { selector: '.velure-section-heading p', prop: 'innerHTML' },
                'prod_cta_text':             { selector: '.velure-section .velure-btn-outline', prop: 'textContent' },

                /* Testimonials */
                'testi_eyebrow':              { selector: '.velure-section-heading .velure-eyebrow', prop: 'textContent' },
                'section_title_testimonials':  { selector: '.velure-section-heading h2', prop: 'textContent' },
                'testi_description':          { selector: '.velure-section-heading p', prop: 'innerHTML' },

                /* Blog */
                'blog_eyebrow':              { selector: '.velure-section-heading .velure-eyebrow', prop: 'textContent' },
                'section_title_blog':        { selector: '.velure-section-heading h2', prop: 'textContent' },
                'blog_description':          { selector: '.velure-section-heading p', prop: 'innerHTML' },
                'blog_cta_text':             { selector: '.velure-section .velure-btn-outline', prop: 'textContent' },

                /* Instagram */
                'ig_eyebrow':                { selector: '.velure-section-heading .velure-eyebrow', prop: 'textContent' },
                'instagram_handle':          { selector: '.velure-section-heading h2', prop: 'textContent' },

                /* Hero Side blocks */
                'hs_bestseller_label':  { selector: '.v-hero-side .hs-bestseller-label',  prop: 'textContent' },
                'hs_bestseller_title':  { selector: '.v-hero-side .hs-bestseller-title',  prop: 'textContent' },
                'hs_bestseller_price':  { selector: '.v-hero-side .hs-bestseller-price',  prop: 'textContent' },
                'hs_bestseller_cta':    { selector: '.v-hero-side .hs-bestseller-cta',    prop: 'textContent' },
                'hs_category_label':    { selector: '.v-hero-side .hs-category-label',    prop: 'textContent' },
                'hs_category_title':    { selector: '.v-hero-side .hs-category-title',    prop: 'textContent' },

                /* Split Banner */
                'sb_left_eyebrow':  { selector: '.velure-split-side:first-child .velure-eyebrow',  prop: 'textContent' },
                'sb_left_title':    { selector: '.velure-split-side:first-child h2',              prop: 'textContent' },
                'sb_left_desc':     { selector: '.velure-split-side:first-child p',               prop: 'innerHTML' },
                'sb_left_cta_text': { selector: '.velure-split-side:first-child .velure-btn',      prop: 'textContent' },
                'sb_right_eyebrow':  { selector: '.velure-split-side:last-child .velure-eyebrow',  prop: 'textContent' },
                'sb_right_title':    { selector: '.velure-split-side:last-child h2',              prop: 'textContent' },
                'sb_right_desc':     { selector: '.velure-split-side:last-child p',               prop: 'innerHTML' },
                'sb_right_cta_text': { selector: '.velure-split-side:last-child .velure-btn',      prop: 'textContent' },
        };

        /* ═══════════════════════════════════════════════════════════════════
           WIDGET SELECTOR MAP
           One entry per editable element type, keyed by section slug.
           Multiple items with the same selector use { multiple: true } to
           auto-append an index suffix  (e.g.  feature-item-0, feature-item-1).
           ═══════════════════════════════════════════════════════════════════ */
        var WIDGET_MAP = {
                hero: [
                        { widget: 'hero-bg',       selector: '.v-hero-slide-bg',                  label: 'Image de fond',           editable: false },
                        { widget: 'hero-overlay',   selector: '.v-hero-slide-overlay',              label: 'Overlay',                  editable: false },
                        { widget: 'hero-eyebrow',   selector: '.v-hero-slide-content > .velure-eyebrow', label: 'Sur-titre',           editable: true  },
                        { widget: 'hero-title',     selector: '.v-hero-slide-content > h2',        label: 'Titre principal',         editable: true  },
                        { widget: 'hero-subtitle',  selector: '.v-hero-subtitle',                   label: 'Sous-titre',              editable: true  },
                        { widget: 'hero-cta',       selector: '.v-hero-slide-content > .velure-btn', label: 'Bouton CTA',            editable: false },
                        { widget: 'hero-side',      selector: '.v-hero-side',                       label: 'Blocs lateraux',          editable: false },
                        { widget: 'hero-dots',      selector: '.v-hero-dots',                       label: 'Navigation slides',       editable: false },
                ],
                features: [
                        { widget: 'feature-item',   selector: '.velure-feature-item',               label: 'Element de confiance',    multiple: true, editable: false },
                ],
                categories: [
                        { widget: 'cat-heading',    selector: '.velure-section-heading',            label: 'En-tete section',         editable: false },
                        { widget: 'cat-card',       selector: '.velure-category-card',              label: 'Carte categorie',         multiple: true, editable: false },
                        { widget: 'cat-cta',        selector: '.velure-btn-outline',                label: 'Bouton CTA',              editable: false },
                ],
                products: [
                        { widget: 'prod-heading',   selector: '.velure-section-heading',            label: 'En-tete section',         editable: false },
                        { widget: 'prod-card',      selector: '.velure-product-card',               label: 'Carte produit',           multiple: true, editable: false },
                        { widget: 'prod-cta',       selector: '.velure-btn-outline',                label: 'Bouton CTA',              editable: false },
                ],
                split_banner: [
                        { widget: 'banner-left',    selector: '.velure-split-side:first-child',     label: 'Banniere gauche',         editable: false },
                        { widget: 'banner-right',   selector: '.velure-split-side:last-child',      label: 'Banniere droite',         editable: false },
                ],
                marquee: [
                        { widget: 'marquee-track',  selector: '.velure-marquee',                    label: 'Bandeau defilant',        editable: false },
                ],
                testimonials: [
                        { widget: 'testi-heading',  selector: '.velure-section-heading',            label: 'En-tete section',         editable: false },
                        { widget: 'testi-card',     selector: '.velure-testimonial-card',           label: 'Carte temoignage',        multiple: true, editable: false },
                ],
                blog: [
                        { widget: 'blog-heading',   selector: '.velure-section-heading',            label: 'En-tete section',         editable: false },
                        { widget: 'blog-card',      selector: '.velure-blog-card',                  label: 'Carte article',           multiple: true, editable: false },
                        { widget: 'blog-cta',       selector: '.velure-btn-outline',                label: 'Bouton CTA',              editable: false },
                ],
                instagram: [
                        { widget: 'ig-heading',     selector: '.velure-section-heading',            label: 'En-tete section',         editable: false },
                        { widget: 'ig-item',        selector: '.velure-instagram-item',             label: 'Image Instagram',         multiple: true, editable: false },
                ],
        };

        /* ═══════════════════════════════════════════════════════════════════
           CONTENT KEY MAP
           Maps editable widget base IDs to their settings key.
           Sent to the parent so auto-save knows which option to update.
           ═══════════════════════════════════════════════════════════════════ */
        var CONTENT_KEY_MAP = {
                'hero-eyebrow':  'hero_slides.0.eyebrow',
                'hero-title':    'hero_slides.0.title',
                'hero-subtitle': 'hero_slides.0.subtitle',
                'hero-cta':      'hero_slides.0.cta_text',
        };

        var widgets = WIDGET_MAP[section] || [];

        /* ═══════════════════════════════════════════════════════════════════
           1. INJECT data-vc-widget ATTRIBUTES
           ═══════════════════════════════════════════════════════════════════ */
        function initWidgets() {
                widgets.forEach(function (def) {
                        var els = document.querySelectorAll(def.selector);
                        els.forEach(function (el, index) {
                                var widgetId = def.widget;
                                if (def.multiple) {
                                        widgetId = def.widget + '-' + index;
                                }
                                el.setAttribute('data-vc-widget', widgetId);
                                el.setAttribute('data-vc-label', def.label);
                                el.setAttribute('data-vc-editable', def.editable ? 'true' : 'false');
                                el.setAttribute('data-vc-base', def.widget);
                                if (def.multiple) {
                                        el.setAttribute('data-vc-index', String(index));
                                }

                                /* ═══ Propagate data-vc-field from child text nodes ═══
                                   If the widget wrapper doesn't have data-vc-field but a direct
                                   text child does, copy it up so onClick can find it. */
                                if (!el.getAttribute('data-vc-field')) {
                                        var fieldChild = el.querySelector('[data-vc-field]');
                                        if (fieldChild) {
                                                el.setAttribute('data-vc-field', fieldChild.getAttribute('data-vc-field'));
                                        }
                                }

                                /* ═══ Restore cached inline styles after HTML replacement ═══ */
                                if (_inlineStyleCache[widgetId]) {
                                        var cached = _inlineStyleCache[widgetId];
                                        Object.keys(cached).forEach(function (prop) {
                                                el.style.setProperty(prop, cached[prop]);
                                        });
                                }
                        });
                });
        }

        /* ═══════════════════════════════════════════════════════════════════
           2. SELECTION MANAGEMENT
           ═══════════════════════════════════════════════════════════════════ */
        function selectElement(el) {
                /* Deselect previous */
                if (selectedEl && selectedEl !== el) {
                        selectedEl.classList.remove('vc-widget-selected');
                }
                selectedEl = el;
                el.classList.add('vc-widget-selected');

                /* Scroll into view if needed */
                var rect = el.getBoundingClientRect();
                if (rect.top < 0 || rect.bottom > window.innerHeight) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                /* Notify parent */
                window.parent.postMessage({
                        action:   'vc-widget-selected',
                        widget:   el.getAttribute('data-vc-widget'),
                        base:     el.getAttribute('data-vc-base') || el.getAttribute('data-vc-widget'),
                        section:  section,
                        label:    el.getAttribute('data-vc-label') || '',
                        editable: el.getAttribute('data-vc-editable') === 'true',
                        index:    el.getAttribute('data-vc-index'),
                }, '*');
        }

        function deselectAll() {
                if (selectedEl) {
                        selectedEl.classList.remove('vc-widget-selected');
                        selectedEl = null;
                }
                closeEditing();
                window.parent.postMessage({ action: 'vc-widget-deselected' }, '*');
        }

        function closeEditing() {
                if (editingEl) {
                        editingEl.contentEditable = 'false';
                        editingEl.classList.remove('vc-editing');
                        editingEl = null;
                }
        }

        /* ═══════════════════════════════════════════════════════════════════
           3. EVENT HANDLERS
           ═══════════════════════════════════════════════════════════════════ */

        /* ── Hover: light blue outline ── */
        function onHoverIn(e) {
                var el = e.target.closest('[data-vc-widget]');
                if (el && el !== selectedEl) {
                        el.classList.add('vc-widget-hover');
                }
        }

        function onHoverOut(e) {
                var el = e.target.closest('[data-vc-widget]');
                if (el) {
                        el.classList.remove('vc-widget-hover');
                }
        }

        /* ── Click: select + halo + activate field in parent panel ── */
        function onClick(e) {
                var el = e.target.closest('[data-vc-widget]');
                if (el) {
                        e.preventDefault();
                        e.stopPropagation();
                        selectElement(el);

                        /* ═══ WIX-LIKE: Send field key to parent for panel focus ═══ */
                        var fieldKey = el.getAttribute('data-vc-field');
                        if (fieldKey) {
                                window.parent.postMessage({
                                        action: 'vc-activate-control',
                                        key: fieldKey,
                                        widget: el.getAttribute('data-vc-widget'),
                                        label: el.getAttribute('data-vc-label') || ''
                                }, '*');
                        }
                } else if (!e.target.closest('.vc-editing')) {
                        deselectAll();
                }
        }

        /* ── Double-click: contenteditable for text elements ── */
        function onDblClick(e) {
                var el = e.target.closest('[data-vc-widget]');
                if (!el) return;
                if (el.getAttribute('data-vc-editable') !== 'true') return;

                e.preventDefault();
                e.stopPropagation();

                /* Close previous editing session */
                if (editingEl && editingEl !== el) {
                        notifyContentChange(editingEl);
                        closeEditing();
                }

                editingEl = el;
                el.contentEditable = 'true';
                el.classList.add('vc-editing');
                el.focus();

                /* Select all text inside */
                var range = document.createRange();
                range.selectNodeContents(el);
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
        }

        /* ── Blur: save editing + notify parent ── */
        function onBlur(e) {
                if (editingEl && e.target === editingEl) {
                        notifyContentChange(editingEl);
                        closeEditing();
                }
        }

        function notifyContentChange(el) {
                var base = el.getAttribute('data-vc-base') || el.getAttribute('data-vc-widget');
                window.parent.postMessage({
                        action:     'vc-content-changed',
                        widget:     el.getAttribute('data-vc-widget'),
                        base:       base,
                        section:    section,
                        property:   'text',
                        value:      el.textContent.trim(),
                        settingKey: CONTENT_KEY_MAP[base] || null,
                }, '*');
        }

        /* ── Keyboard ── */
        function onKeyDown(e) {
                /* Escape exits editing */
                if (editingEl && e.key === 'Escape') {
                        editingEl.blur();
                }
                /* Prevent link navigation when clicking inside a widget */
                if (e.target.closest('[data-vc-widget]') && e.key === 'Enter') {
                        if (e.target.getAttribute('contenteditable') !== 'true') {
                                e.preventDefault();
                        }
                }
        }

        /* ═══════════════════════════════════════════════════════════════════
           4. RECEIVE MESSAGES FROM PARENT — REAL-TIME LIVE PREVIEW
           ═══════════════════════════════════════════════════════════════════ */
        function onParentMessage(e) {
                if (!e.data || typeof e.data.action !== 'string') return;

                switch (e.data.action) {

                        /* ── Widget-level inline style — uses style.setProperty for precision ── */
                        case 'vc-apply-style':
                                applyStyleToWidget(e.data.widget, e.data.css);
                                break;

                        /* ── Widget-level text content ── */
                        case 'vc-apply-text':
                                applyTextToWidget(e.data.widget, e.data.text, e.data.key);
                                break;

                        /* ═══════════════════════════════════════════════════
                           REAL-TIME LIVE PREVIEW MESSAGES
                           ═══════════════════════════════════════════════════ */

                        /* Apply CSS custom property on a widget element.
                           E.g. { widget: 'hero-overlay', property: '--overlay-opacity', value: '0.4' }
                           → el.style.setProperty('--overlay-opacity', '0.4') */
                        case 'vc-apply-css-var':
                                applyCssVar(e.data.widget, e.data.property, e.data.value);
                                break;

                        /* Apply CSS properties on the entire section container.
                           E.g. { css: { minHeight: '80vh', textAlign: 'center' } }
                           Used for height, alignment, padding that affect the whole section. */
                        case 'vc-apply-section-css':
                                applySectionCss(e.data.css);
                                break;

                        /* Toggle/add/remove CSS classes on an element.
                           E.g. { widget: 'hero-bg', add: 'v-hero-tall', remove: 'v-hero-compact' }
                           Supports widget: '__section__' sentinel to target section container. */
                        case 'vc-apply-class':
                                applyClass(e.data.widget, e.data.add, e.data.remove);
                                break;

                        /* Update background-image on a widget (hero bg, split banner sides).
                           E.g. { widget: 'hero-bg', url: 'https://...' } */
                        case 'vc-update-bg-image':
                                updateBgImage(e.data.widget, e.data.url);
                                break;

                        /* Surgical HTML replacement — replace the entire section
                           container innerHTML with server-rendered fragment.
                           NO iframe reload — instant DOM swap.
                           E.g. { html: '<div class="velure-features">...</div>' } */
                        case 'vc-replace-html':
                                replaceSectionHtml(e.data.html);
                                break;

                        /* Surgical HTML replacement for a specific widget only.
                           E.g. { widget: 'hero-side', html: '...' } */
                        case 'vc-replace-widget-html':
                                replaceWidgetHtml(e.data.widget, e.data.html);
                                break;

                        /* ── Deselect / Select ── */
                        case 'vc-deselect':
                                deselectAll();
                                break;

                        case 'vc-select-widget':
                                var target = document.querySelector('[data-vc-widget="' + e.data.widget + '"]');
                                if (target) selectElement(target);
                                break;
                }
        }

        /* ─────────────────────────────────────────────────────────────────
           STYLE APPLICATION FUNCTIONS — Real-Time
           All use style.setProperty() for maximum specificity control.
           ───────────────────────────────────────────────────────────────── */

        /**
         * Apply inline CSS to a widget element using style.setProperty().
         * Also caches the applied styles so they survive HTML replacement.
         * @param {string} widgetId  data-vc-widget value
         * @param {Object} css       { cssProp: cssValue, ... }
         */
        function applyStyleToWidget(widgetId, css) {
                if (!css || typeof css !== 'object') return;
                var el = document.querySelector('[data-vc-widget="' + widgetId + '"]');
                if (!el) return;

                /* Cache for restoration after HTML replacement */
                if (!_inlineStyleCache[widgetId]) {
                        _inlineStyleCache[widgetId] = {};
                }

                Object.keys(css).forEach(function (prop) {
                        el.style.setProperty(prop, css[prop]);
                        _inlineStyleCache[widgetId][prop] = css[prop];
                });
        }

        /**
         * Apply text content to a widget element.
         * Falls back to TEXT_SELECTOR_MAP for non-hero text fields.
         * @param {string} widgetId  data-vc-widget value
         * @param {string} text      New text content
         * @param {string} key       Optional settings key for TEXT_SELECTOR_MAP fallback
         */
        function applyTextToWidget(widgetId, text, key) {
                var el = document.querySelector('[data-vc-widget="' + widgetId + '"]');

                /* Fallback 1: Try TEXT_SELECTOR_MAP for non-hero sections */
                if (!el && key) {
                        var textDef = TEXT_SELECTOR_MAP[key];
                        if (textDef) {
                                el = document.querySelector(textDef.selector);
                                if (el) {
                                        el[textDef.prop] = text;
                                        return;
                                }
                        }
                }

                if (!el) return;

                /* Use innerHTML for elements that might contain line breaks,
                   textContent for simple inline elements */
                var tag = el.tagName.toLowerCase();
                if (tag === 'p' || tag === 'div' || tag === 'span') {
                        /* Preserve line breaks from textareas */
                        var escaped = text
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/\n/g, '<br>');
                        el.innerHTML = escaped;
                } else {
                        el.textContent = text;
                }
        }

        /**
         * Apply a CSS custom property on a widget element.
         * @param {string} widgetId  Widget to target
         * @param {string} prop      CSS custom property name (e.g. '--overlay-opacity')
         * @param {string} value     Value to set
         */
        function applyCssVar(widgetId, prop, value) {
                if (!prop) return;

                /* Handle __section__ sentinel */
                var el = (widgetId === '__section__') ? null : document.querySelector('[data-vc-widget="' + widgetId + '"]');

                if (!el) {
                        /* Fallback: apply on section container */
                        el = getSectionContainer();
                        if (!el) return;
                }
                el.style.setProperty(prop, value);

                /* Cache for section-level vars */
                if (widgetId === '__section__' || !el.getAttribute('data-vc-widget')) {
                        if (!_inlineStyleCache['__section__']) _inlineStyleCache['__section__'] = {};
                        _inlineStyleCache['__section__'][prop] = value;
                } else {
                        var wid = el.getAttribute('data-vc-widget');
                        if (!_inlineStyleCache[wid]) _inlineStyleCache[wid] = {};
                        _inlineStyleCache[wid][prop] = value;
                }
        }

        /**
         * Apply CSS properties on the section container using style.setProperty().
         * @param {Object} css  { cssProp: value, ... }
         */
        function applySectionCss(css) {
                if (!css || typeof css !== 'object') return;
                var el = getSectionContainer();
                if (!el) return;

                /* Cache for restoration */
                if (!_inlineStyleCache['__section__']) _inlineStyleCache['__section__'] = {};

                Object.keys(css).forEach(function (prop) {
                        el.style.setProperty(prop, css[prop]);
                        _inlineStyleCache['__section__'][prop] = css[prop];
                });
        }

        /**
         * Toggle CSS classes on a widget element.
         * Supports widget: '__section__' sentinel to target section container.
         * @param {string} widgetId  Widget to target (or '__section__')
         * @param {string} add       Classes to add (space-separated)
         * @param {string} remove    Classes to remove (space-separated)
         */
        function applyClass(widgetId, add, remove) {
                var el = null;

                /* ═══ FIX 3.5.2: Handle __section__ sentinel ═══ */
                if (widgetId === '__section__' || !widgetId) {
                        el = getSectionContainer();
                } else {
                        el = document.querySelector('[data-vc-widget="' + widgetId + '"]');
                        /* Fallback to section container if widget not found */
                        if (!el) el = getSectionContainer();
                }
                if (!el) return;

                if (add) {
                        add.split(/\s+/).forEach(function (c) {
                                if (c) el.classList.add(c);
                        });
                }
                if (remove) {
                        remove.split(/\s+/).forEach(function (c) {
                                if (c) el.classList.remove(c);
                        });
                }
        }

        /**
         * Update background-image on a widget.
         * @param {string} widgetId  Widget to target
         * @param {string} url       New image URL
         */
        function updateBgImage(widgetId, url) {
                var el = document.querySelector('[data-vc-widget="' + widgetId + '"]');
                if (!el) return;
                if (url && url !== '0') {
                        el.style.setProperty('background-image', 'url(' + url + ')');
                        el.style.setProperty('background-size', 'cover');
                        el.style.setProperty('background-position', 'center');

                        /* Cache */
                        if (!_inlineStyleCache[widgetId]) _inlineStyleCache[widgetId] = {};
                        _inlineStyleCache[widgetId]['background-image'] = 'url(' + url + ')';
                        _inlineStyleCache[widgetId]['background-size'] = 'cover';
                        _inlineStyleCache[widgetId]['background-position'] = 'center';
                } else {
                        el.style.setProperty('background-image', 'none');
                        if (_inlineStyleCache[widgetId]) {
                                delete _inlineStyleCache[widgetId]['background-image'];
                        }
                }
        }

        /**
         * Replace the entire section container's innerHTML with new HTML.
         * Used for structural changes (new blocks, image additions) where
         * surgical CSS isn't enough. No iframe reload — instant DOM swap.
         *
         * v3.5.2: Preserves inline styles by caching them before replacement
         * and restoring them after initWidgets() re-runs.
         *
         * @param {string} html  The new HTML fragment
         */
        function replaceSectionHtml(html) {
                if (!html) return;
                var container = getSectionContainer();
                if (!container) return;

                /* Save the parent element */
                var parent = container.parentNode;
                if (!parent) return;

                /* ═══ v3.5.2: Snapshot current inline styles on all widgets ═══
                   before replacing the DOM, so we can restore them after. */
                _snapshotInlineStyles();

                /* Create a temporary element to parse the HTML */
                var temp = document.createElement('div');
                temp.innerHTML = html;
                var newEl = temp.firstElementChild;
                if (!newEl) return;

                /* Copy the section selector class to the new element */
                var oldClasses = container.className;
                newEl.className = oldClasses;

                /* Replace the old container with the new one */
                parent.replaceChild(newEl, container);

                /* Re-initialize widgets on the new DOM (restores cached styles) */
                initWidgets();

                /* Re-freeze hero if needed */
                if (section === 'hero') {
                        freezeHeroSlider();
                }

                /* Flash animation for visual feedback */
                newEl.classList.add('vc-flash-replace');
                setTimeout(function () {
                        newEl.classList.remove('vc-flash-replace');
                }, 350);
        }

        /**
         * Replace a specific widget's HTML content.
         * @param {string} widgetId  Widget to replace
         * @param {string} html      New innerHTML
         */
        function replaceWidgetHtml(widgetId, html) {
                if (!html) return;
                var el = document.querySelector('[data-vc-widget="' + widgetId + '"]');
                if (!el) return;

                el.innerHTML = html;

                /* Re-init widgets for new children */
                initWidgets();

                /* Re-freeze hero if the replaced widget is inside hero */
                if (section === 'hero') {
                        freezeHeroSlider();
                }

                /* Flash animation */
                el.classList.add('vc-flash-replace');
                setTimeout(function () {
                        el.classList.remove('vc-flash-replace');
                }, 350);
        }

        /* ─────────────────────────────────────────────────────────────────
           HELPERS
           ───────────────────────────────────────────────────────────────── */

        /**
         * Get the section container element for the current section.
         */
        function getSectionContainer() {
                var sel = SECTION_SELECTOR[section];
                if (!sel) return document.body.firstElementChild || document.body;
                var el = document.querySelector(sel);
                return el || document.body.firstElementChild || document.body;
        }

        /**
         * Snapshot all currently applied inline styles on widget elements
         * and the section container into _inlineStyleCache.
         * Called before replaceSectionHtml() to preserve styles across DOM swaps.
         */
        function _snapshotInlineStyles() {
                /* Snapshot section container styles */
                var secEl = getSectionContainer();
                if (secEl && secEl.style.cssText) {
                        if (!_inlineStyleCache['__section__']) _inlineStyleCache['__section__'] = {};
                        /* Parse the cssText into individual properties */
                        var secStyle = secEl.style;
                        for (var i = 0; i < secStyle.length; i++) {
                                var prop = secStyle[i];
                                _inlineStyleCache['__section__'][prop] = secStyle.getPropertyValue(prop);
                        }
                }

                /* Snapshot all widget elements that have inline styles */
                var allWidgets = document.querySelectorAll('[data-vc-widget]');
                allWidgets.forEach(function (el) {
                        var wid = el.getAttribute('data-vc-widget');
                        if (el.style.cssText) {
                                if (!_inlineStyleCache[wid]) _inlineStyleCache[wid] = {};
                                var wStyle = el.style;
                                for (var j = 0; j < wStyle.length; j++) {
                                        var wProp = wStyle[j];
                                        _inlineStyleCache[wid][wProp] = wStyle.getPropertyValue(wProp);
                                }
                        }
                });
        }

        /* ═══════════════════════════════════════════════════════════════════
           5. CANVAS-SPECIFIC STYLES
           ═══════════════════════════════════════════════════════════════════ */
        function injectCanvasStyles() {
                var css = document.createElement('style');
                css.id = 'vc-canvas-bridge-css';
                css.textContent = [
                        '/* ── Canvas Bridge: Widget overlay styles ── */',
                        '[data-vc-widget] {',
                        '  cursor: pointer;',
                        '  transition: outline 0.15s ease, outline-offset 0.15s ease;',
                        '  outline: 2px solid transparent;',
                        '  outline-offset: 2px;',
                        '  position: relative;',
                        '}',

                        '/* Hover: light blue outline */',
                        '[data-vc-widget].vc-widget-hover {',
                        '  outline: 2px solid rgba(59, 130, 246, 0.45);',
                        '}',

                        '/* Selected: dashed blue halo */',
                        '[data-vc-widget].vc-widget-selected {',
                        '  outline: 2px dashed #3b82f6;',
                        '  outline-offset: 4px;',
                        '}',

                        '/* Editing: solid green outline */',
                        '[data-vc-editable="true"].vc-editing {',
                        '  outline: 2px solid #22c55e !important;',
                        '  outline-offset: 4px;',
                        '  cursor: text;',
                        '  background: rgba(255, 255, 255, 0.06);',
                        '  border-radius: 2px;',
                        '  min-width: 40px;',
                        '  min-height: 1em;',
                        '}',

                        '/* Widget label tooltip on hover */',
                        '[data-vc-widget].vc-widget-hover::after,',
                        '[data-vc-widget].vc-widget-selected::after {',
                        '  content: attr(data-vc-label);',
                        '  position: absolute;',
                        '  top: -28px;',
                        '  left: 50%;',
                        '  transform: translateX(-50%);',
                        '  background: rgba(26, 26, 46, 0.92);',
                        '  color: #C8A97E;',
                        '  font-family: "Inter", sans-serif;',
                        '  font-size: 11px;',
                        '  font-weight: 600;',
                        '  padding: 3px 10px;',
                        '  border-radius: 4px;',
                        '  white-space: nowrap;',
                        '  pointer-events: none;',
                        '  z-index: 99999;',
                        '  letter-spacing: 0.3px;',
                        '  backdrop-filter: blur(6px);',
                        '}',

                        '/* Make overlays non-blocking so clicks reach widgets behind them */',
                        '.v-hero-slide-overlay, .velure-split-side-bg {',
                        '  pointer-events: none !important;',
                        '}',

                        '/* Prevent links from navigating in canvas */',
                        '[data-vc-widget] a, a[data-vc-widget] {',
                        '  pointer-events: none;',
                        '}',

                        '/* ═══ WIX-LIKE: Editable field hover indicator ═══ */',
                        '[data-vc-field] {',
                        '  position: relative;',
                        '}',
                        '[data-vc-field]:hover::before {',
                        '  content: "\\270F Edit";',
                        '  position: absolute;',
                        '  top: -22px;',
                        '  right: -4px;',
                        '  background: rgba(59, 130, 246, 0.92);',
                        '  color: #fff;',
                        '  font-size: 10px;',
                        '  font-weight: 600;',
                        '  font-family: "Inter", sans-serif;',
                        '  padding: 2px 8px;',
                        '  border-radius: 4px;',
                        '  z-index: 99998;',
                        '  pointer-events: none;',
                        '  letter-spacing: 0.3px;',
                        '  white-space: nowrap;',
                        '  opacity: 0;',
                        '  transition: opacity 0.15s ease;',
                        '}',
                        '[data-vc-widget]:hover [data-vc-field]::before,',
                        '[data-vc-field].vc-widget-hover::before,',
                        '[data-vc-field].vc-widget-selected::before {',
                        '  opacity: 1;',
                        '}',

                        '/* ═══ v3.5.2: Flash animation for HTML replacement ═══ */',
                        '@keyframes vc-flash-replace {',
                        '  0% { opacity: 0.5; transform: scale(0.998); }',
                        '  100% { opacity: 1; transform: scale(1); }',
                        '}',
                        '.vc-flash-replace {',
                        '  animation: vc-flash-replace 0.3s ease-out;',
                        '}',
                ].join('\n');
                document.head.appendChild(css);
        }

        /* ═══════════════════════════════════════════════════════════════════
           6. HERO SLIDER FREEZE (canvas edit mode)
           Prevents auto-advance and ensures first slide stays visible.
           ═══════════════════════════════════════════════════════════════════ */
        function freezeHeroSlider() {
                var slider = document.querySelector('.v-hero-slider');
                if (!slider) return;

                /* Force first slide active if none are */
                var slides = slider.querySelectorAll('.v-hero-slide');
                var hasActive = false;
                slides.forEach(function(s) {
                        if (s.classList.contains('v-hero-slide--active')) hasActive = true;
                });
                if (!hasActive && slides.length > 0) {
                        slides[0].classList.add('v-hero-slide--active');
                }

                /* Remove all event listeners from slider by cloning */
                var main = slider.querySelector('.v-hero-main');
                if (main) {
                        var clone = main.cloneNode(true);
                        main.parentNode.replaceChild(clone, main);
                }
        }

        /* ═══════════════════════════════════════════════════════════════════
           INIT
           ═══════════════════════════════════════════════════════════════════ */
        function init() {
                injectCanvasStyles();
                initWidgets();

                /* Freeze hero slider in canvas mode (no auto-advance) */
                if (section === 'hero') {
                        freezeHeroSlider();
                }

                /* Capture phase so we intercept before any default handlers */
                document.addEventListener('mouseover',  onHoverIn,  true);
                document.addEventListener('mouseout',   onHoverOut, true);
                document.addEventListener('click',       onClick,     true);
                document.addEventListener('dblclick',    onDblClick,  true);
                document.addEventListener('focusout',    onBlur,      true);
                document.addEventListener('keydown',     onKeyDown,   true);
                window.addEventListener('message',      onParentMessage);
        }

        if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
        } else {
                init();
        }

})();