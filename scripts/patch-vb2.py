#!/usr/bin/env python3
"""Insert auto-save block into visual-builder.js at the correct position."""
import re

FILE = '/home/z/my-project/velure-core/assets/js/visual-builder.js'

with open(FILE, 'r') as f:
    lines = f.readlines()

# Find the line after sendToIframe's closing "}," 
insert_idx = None
for i, line in enumerate(lines):
    if 'sendToIframe' in line and 'function' in line:
        # Find the closing }, after this function
        for j in range(i, min(i + 10, len(lines))):
            if lines[j].strip() == '},':
                insert_idx = j + 1
                break
        break

if insert_idx is None:
    print('ERROR: Could not find insertion point')
    exit(1)

print(f'Inserting auto-save block after line {insert_idx}')

AUTO_SAVE = '''
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

                _markUnsaved: function () {
                        if (!VCB._hasUnsaved) {
                                VCB._hasUnsaved = true;
                                $('#vc-unsaved-dot').addClass('show');
                                $(window).on('beforeunload.vc-builder', function () { return true; });
                        }
                },

                _markSaved: function () {
                        VCB._hasUnsaved = false;
                        VCB.pendingChanges = {};
                        $('#vc-unsaved-dot').removeClass('show');
                        $(window).off('beforeunload.vc-builder');
                },

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

lines.insert(insert_idx, AUTO_SAVE)

with open(FILE, 'w') as f:
    f.writelines(lines)

print(f'Done. Auto-save block inserted after line {insert_idx}')