#!/usr/bin/env python3
"""Patch visual-builder.js for Étape 3: auto-save with debounce."""
import re

FILE = '/home/z/my-project/velure-core/assets/js/visual-builder.js'

with open(FILE, 'r') as f:
    content = f.read()

# 1. Update version comment
content = content.replace(
    ' * @since 3.2.1\n */',
    ' * @since 3.2.1\n * @updated 3.3.0 Auto-save with debounce, save indicator, bidirectional sync\n */'
)

# 2. Add new properties after section:     '',
content = content.replace(
    "                section:     '',",
    "                section:     '',\n"
    "                _saveTimer: null,\n"
    "                pendingChanges: {},\n"
    "                _hasUnsaved: false,\n"
    "                _toastTimer: null,"
)

# 3. Add addToChanges call at top of onFieldChange
content = content.replace(
    '                onFieldChange: function (key, value) {\n'
    '                        if (!VCB.selected) return;\n'
    '                        var widgetId = VCB.selected.widget;',
    '                onFieldChange: function (key, value) {\n'
    '                        if (!VCB.selected) return;\n'
    '                        var widgetId = VCB.selected.widget;\n'
    '\n'
    '                        /* ── Queue change for auto-save (debounce 1.5s) ── */\n'
    '                        VCB.addToChanges(key, value);'
)

# 4. Insert auto-save system between sendToIframe and onMessage
AUTO_SAVE_BLOCK = '''
                /* ═══════════════════════════════════════════════════════════════
                   WIDGET CONTENT KEY MAP
                   Maps editable widget base IDs to their settings key for auto-save.
                   Used when contenteditable text is changed inline in the iframe.
                   ═══════════════════════════════════════════════════════════════ */
                WIDGET_CONTENT_KEY: {
                        'hero-eyebrow':  'hero_slides.0.eyebrow',
                        'hero-title':    'hero_slides.0.title',
                        'hero-subtitle': 'hero_slides.0.subtitle',
                        'hero-cta':      'hero_slides.0.cta_text',
                },

                /* ═══════════════════════════════════════════════════════════════
                   AUTO-SAVE ENGINE (debounce 1.5s)
                   ═══════════════════════════════════════════════════════════════ */

                /**
                 * Add a key-value pair to the pending-changes queue
                 * and (re)start the debounce timer.
                 */
                addToChanges: function (key, value) {
                        VCB.pendingChanges[key] = value;
                        VCB._markUnsaved();
                        VCB._debounceSave();
                },

                /** Show the unsaved indicator dot */
                _markUnsaved: function () {
                        if (!VCB._hasUnsaved) {
                                VCB._hasUnsaved = true;
                                $('#vc-unsaved-dot').addClass('show');
                                $(window).on('beforeunload.vc-builder', function () { return true; });
                        }
                },

                /** Hide the unsaved indicator after successful save */
                _markSaved: function () {
                        VCB._hasUnsaved = false;
                        VCB.pendingChanges = {};
                        $('#vc-unsaved-dot').removeClass('show');
                        $(window).off('beforeunload.vc-builder');
                },

                /** (Re)start the 1500ms debounce timer */
                _debounceSave: function () {
                        clearTimeout(VCB._saveTimer);
                        VCB._showSaveIndicator('pending');
                        VCB._saveTimer = setTimeout(function () {
                                VCB.saveChanges();
                        }, 1500);
                },

                /**
                 * Flush all pending changes to the server via AJAX.
                 * Called by the debounce timer or by the Publish button.
                 */
                saveChanges: function () {
                        var keys = Object.keys(VCB.pendingChanges);
                        if (keys.length === 0) {
                                VCB._showSaveIndicator('idle');
                                return;
                        }

                        var changes = JSON.parse(JSON.stringify(VCB.pendingChanges));
                        VCB._showSaveIndicator('saving');

                        $.ajax({
                                url:  (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.ajaxUrl : '/wp-admin/admin-ajax.php',
                                type: 'POST',
                                data: {
                                        action:  'velure_core_auto_save',
                                        _wpnonce: (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.nonce : '',
                                        changes: JSON.stringify(changes),
                                },
                                success: function (res) {
                                        if (res && res.success) {
                                                /* Update local settings model */
                                                keys.forEach(function (k) {
                                                        VCB._setNestedValue(VCB.settings, k, changes[k]);
                                                });
                                                VCB._markSaved();
                                                VCB._showSaveIndicator('saved');
                                        } else {
                                                VCB._showSaveIndicator('error');
                                                VCB.toast((res && res.data && res.data.message) || 'Erreur de sauvegarde.', 'error');
                                        }
                                },
                                error: function () {
                                        VCB._showSaveIndicator('error');
                                        VCB.toast('Erreur reseau lors de la sauvegarde.', 'error');
                                },
                        });
                },

                /** Set a nested value using dot-notation key (e.g. 'hero_slides.0.title') */
                _setNestedValue: function (obj, key, value) {
                        var parts = key.split('.');
                        var current = obj;
                        for (var i = 0; i < parts.length - 1; i++) {
                                if (!current[parts[i]] || typeof current[parts[i]] !== 'object') {
                                        current[parts[i]] = {};
                                }
                                current = current[parts[i]];
                        }
                        current[parts[parts.length - 1]] = value;
                },

                /**
                 * Manage the save indicator in the topbar.
                 * States: idle | pending | saving | saved | error
                 */
                _showSaveIndicator: function (state) {
                        var $ind = $('#vc-save-indicator');
                        if (!$ind.length) return;

                        $ind.removeClass('vc-saving vc-saved vc-error vc-visible');

                        switch (state) {
                                case 'pending':
                                        $ind.find('.vc-save-text').text('Modifications en attente');
                                        $ind.addClass('vc-visible');
                                        break;
                                case 'saving':
                                        $ind.find('.vc-save-text').text('Enregistrement...');
                                        $ind.addClass('vc-visible vc-saving');
                                        break;
                                case 'saved':
                                        $ind.find('.vc-save-text').text('Enregistre');
                                        $ind.addClass('vc-visible vc-saved');
                                        setTimeout(function () { $ind.removeClass('vc-visible'); }, 3000);
                                        break;
                                case 'error':
                                        $ind.find('.vc-save-text').text('Erreur');
                                        $ind.addClass('vc-visible vc-error');
                                        setTimeout(function () { $ind.removeClass('vc-visible'); }, 4000);
                                        break;
                                default:
                                        $ind.removeClass('vc-visible');
                        }
                },

                /** Simple toast (standalone, doesn\\'t depend on VC object) */
                toast: function (msg, type) {
                        var el = document.getElementById('vc-toast');
                        if (!el) return;
                        el.className = 'vc-toast';
                        if (type) el.className += ' vc-toast-' + type;
                        var icon = type === 'success' ? '\\u2713 ' : type === 'error' ? '\\u2717 ' : type === 'saving' ? '\\u23F3 ' : '';
                        el.innerHTML = icon + msg;
                        el.classList.add('show');
                        clearTimeout(VCB._toastTimer);
                        VCB._toastTimer = setTimeout(function () { el.classList.remove('show'); }, 4000);
                },

'''

content = content.replace(
    '                /* ═══════════════════════════════════════════════════════════════\n'
    '                   POST MESSAGE LISTENER — receive events from canvas-bridge.js\n'
    '                   ═══════════════════════════════════════════════════════════════ */',
    AUTO_SAVE_BLOCK +
    '                /* ═══════════════════════════════════════════════════════════════\n'
    '                   POST MESSAGE LISTENER — receive events from canvas-bridge.js\n'
    '                   ═══════════════════════════════════════════════════════════════ */'
)

# 5. Fix vc-content-changed handler
content = content.replace(
    "                                case 'vc-content-changed':\n"
    "                                        /* Update the corresponding field in the panel */\n"
    "                                        var $field = $('[data-vc-key=\"' + e.data.key + '\"]');\n"
    "                                        if ($field.length && $field.is('input[type=\"text\"], textarea')) {\n"
    "                                                $field.val(e.data.value);\n"
    "                                        }\n"
    "                                        break;",
    "                                case 'vc-content-changed':\n"
    "                                        /* Update the corresponding field in the panel */\n"
    "                                        if (e.data.settingKey) {\n"
    "                                                var $cfield = $('[data-vc-key=\"' + e.data.settingKey + '\"]');\n"
    "                                                if ($cfield.length && ($cfield.is('input[type=\"text\"]') || $cfield.is('textarea'))) {\n"
    "                                                        $cfield.val(e.data.value);\n"
    "                                                }\n"
    "                                                /* Queue for auto-save */\n"
    "                                                VCB.addToChanges(e.data.settingKey, e.data.value);\n"
    "                                        }\n"
    "                                        break;"
)

# 6. Add publish button handler in init
content = content.replace(
    '                        /* Ensure empty state is visible on load */\n'
    '                        VCB.showEmpty();\n'
    '                }\n'
    '        };',
    '                        /* Ensure empty state is visible on load */\n'
    '                        VCB.showEmpty();\n'
    '\n'
    '                        /* ── Publish button: flush pending changes immediately ── */\n'
    '                        $(document).on(\'click\', \'#vc-publish-btn\', function (e) {\n'
    '                                e.preventDefault();\n'
    '                                clearTimeout(VCB._saveTimer);\n'
    '                                VCB.saveChanges();\n'
    '                        });\n'
    '                }\n'
    '        };'
)

with open(FILE, 'w') as f:
    f.write(content)

print('visual-builder.js patched successfully')