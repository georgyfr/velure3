---
Task ID: 1
Agent: main
Task: Créer le plugin Velure Core pour personnalisation 100% de la page d'accueil

Work Log:
- Analysé les 9 sections du front-page.php (Hero, Features, Categories, Products, Split Banner, Marquee, Testimonials, Blog, Instagram)
- Analysé inc/dynamic-fields.php (619 lignes) et functions.php (422 lignes)
- Créé la structure du plugin velure-core/ avec 5 fichiers
- Implémenté 11 sous-pages ACF (Sections & Order, Hero, Features, Categories, Products, Banner, Marquee, Testimonials, Blog, Instagram, Global Styles)
- Ajouté le drag-and-drop d'ordre des sections via ACF select multiple
- Ajouté les options avancées par section (padding, colonnes, layout, tri, autoplay, overlay, direction marquee, etc.)
- Ajouté les styles globaux (CSS personnalisé, padding sections, toggle animations)
- Déplacé le CPT velure_testimonial dans le plugin
- Créé les aliases de compatibilité velure3_* -> velure_core_* pour zéro modification du thème
- Mis à jour front-page.php pour rendre les sections dans l'ordre dynamique (switch/case dans foreach)
- Mis à jour functions.php pour déléguer au plugin via action hook velure_core_loaded
- Renommé dynamic-fields.php en dynamic-fields-shim.php (fallback si plugin inactif)
- Push sur GitHub commit 5da224a
- Généré velure3-v1.0.0.zip et velure-core-v1.0.0.zip

Stage Summary:
- Plugin Velure Core v1.0.0 créé avec 100% de la page d'accueil configurable
- Architecture propre : plugin = logique métier, thème = affichage
- Synchronisation via aliases de fonctions + action hook
- 2 zips disponibles : thème + plugin (séparés)---
Task ID: 1
Agent: Super Z (main)
Task: Implement complete Real-Time Live Preview (v3.5.2) — zero-latency sync for ALL controls

Work Log:
- Read and analyzed all 3 JS files (admin.js 1280 lines, canvas-bridge.js 712 lines, visual-builder.js 1281 lines)
- Identified 6 critical bugs in v3.5.1: __section__ sentinel not handled, no text preview for non-hero sections, no AJAX fallback for structural changes, image IDs never resolved to URLs, inline styles lost on DOM replacement, select dropdowns only fired on blur
- Rewrote canvas-bridge.js: TEXT_SELECTOR_MAP (28+ text fields), _inlineStyleCache + _snapshotInlineStyles for style preservation across DOM swaps, __section__ sentinel handling in applyClass/applyCssVar, flash animation on HTML replacement
- Rewrote _pushPanelPreview in admin.js: non-hero text preview (28 fields), _resolveImageUrl via WP REST API, _requestPanelPartialRender for structural changes, select input+change for continuous preview
- Enriched CSS_MAP in visual-builder.js: { none: true } for 8 structural fields, hero-side style entries, TYPE 6 handler in onFieldChange, non-hero text key passthrough
- Verified PHP endpoint vc_render_component_preview handles all cases correctly
- Validated JS syntax for all 3 files (node -c)
- Cross-verified all postMessage actions match between sender and receiver
- Committed and pushed to GitHub

Stage Summary:
- v3.5.2 pushed to main (commit 712088e)
- 3 JS files modified: canvas-bridge.js, admin.js, visual-builder.js
- Plugin version bumped to 3.5.2 in velure-core.php
- Zero iframe reloads for CSS/text/class/image changes
- Structural changes (count, sort) use 300ms-debounced AJAX partial render
