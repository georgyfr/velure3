/**
 * Velure Core — Visual Builder JS v3.5.1
 * Loaded in the PARENT (WordPress admin) when #vc-builder-app is present.
 *
 * Responsibilities:
 *  1. Listen for postMessage from canvas-bridge.js inside the iframe
 *  2. Show contextual 3-tab panel (Contenu / Style / Avancé)
 *  3. Render fields dynamically per widget type
 *  4. Send REAL-TIME style/text/structural updates back to iframe
 *
 * @package VelureCore
 * @since 3.2.1
 * @updated 3.5.2 Complete real-time preview + non-hero text + structural AJAX fallback
 */

/* ═══ v3.5.1: Utility helpers for CSS_MAP class patterns ═══ */
function _vcBgStyleMap() {
        return {
                base:  { add: 'velure-section-base',  remove: 'velure-section-soft velure-section-muted' },
                soft:  { add: 'velure-section-soft',  remove: 'velure-section-base velure-section-muted' },
                muted: { add: 'velure-section-muted', remove: 'velure-section-base velure-section-soft' },
        };
}
function _vcColMap() {
        return {
                '2': { add: 'velure-grid-2', remove: 'velure-grid-3 velure-grid-4' },
                '3': { add: 'velure-grid-3', remove: 'velure-grid-2 velure-grid-4' },
                '4': { add: 'velure-grid-4', remove: 'velure-grid-2 velure-grid-3' },
        };
}
function _vcIgColMap() {
        return {
                '4': { add: 'velure-grid-4', remove: 'velure-grid-5 velure-grid-6' },
                '5': { add: 'velure-grid-5', remove: 'velure-grid-4 velure-grid-6' },
                '6': { add: 'velure-grid-6', remove: 'velure-grid-4 velure-grid-5' },
        };
}

(function ($) {
        'use strict';

        var VCB = {
                iframe:      null,
                selected:    null,   // current widget info from iframe
                currentTab:  'content',
                settings:    {},
                section:     '',
                _saveTimer: null,
                pendingChanges: {},
                _hasUnsaved: false,
                _toastTimer: null,
                _saveTimer: null,
                pendingChanges: {},
                _hasUnsaved: false,
                _toastTimer: null,

                /* ═══════════════════════════════════════════════════════════════
                   WIDGET FIELD REGISTRY
                   Maps widget base ID → { label, tabs: { content:[], style:[], advanced:[] } }
                   Each field: { type, key, label, unit?, min?, max?, step?, options?, placeholder? }
                   ═════════════════════════════════════════════════════════════ */
                FIELDS: {

                        /* ──────────────── HERO ──────────────── */
                        'hero-bg': {
                                label: 'Image de fond',
                                tabs: {
                                        content: [
                                                { type: 'media', key: 'hero_slides.0.image', label: 'Image du slide' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'hero_bg_position', label: 'Position du fond', options: [
                                                        {v:'center',l:'Centre'},{v:'top',l:'Haut'},{v:'bottom',l:'Bas'},{v:'left',l:'Gauche'},{v:'right',l:'Droite'}
                                                ]},
                                                { type: 'select', key: 'hero_bg_size', label: 'Taille du fond', options: [
                                                        {v:'cover',l:'Cover'},{v:'contain',l:'Contain'},{v:'auto',l:'Auto'}
                                                ]},
                                                { type: 'range', key: 'hero_overlay_opacity', label: 'Opacite de l\'overlay', min:0, max:100, step:5, unit:'%' },
                                        ],
                                        advanced: [
                                                { type: 'text', key: 'hero_height', label: 'Hauteur hero', placeholder: 'standard / tall / compact' },
                                        ]
                                }
                        },
                        'hero-overlay': {
                                label: 'Overlay',
                                tabs: {
                                        content: [],
                                        style: [
                                                { type: 'range', key: 'hero_overlay_opacity', label: 'Opacite', min:0, max:100, step:5, unit:'%' },
                                        ],
                                        advanced: []
                                }
                        },
                        'hero-eyebrow': {
                                label: 'Sur-titre',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'hero_slides.0.eyebrow', label: 'Texte du sur-titre' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'hero_eyebrow_font_family', label: 'Police', options: [
                                                        {v:'Inter',l:'Inter'},{v:'Playfair Display',l:'Playfair Display'},{v:'Cormorant Garamond',l:'Cormorant Garamond'}
                                                ]},
                                                { type: 'number', key: 'hero_eyebrow_font_size', label: 'Taille', unit:'px' },
                                                { type: 'select', key: 'hero_eyebrow_font_weight', label: 'Graisse', options: [
                                                        {v:'300',l:'Light'},{v:'400',l:'Regular'},{v:'500',l:'Medium'},{v:'600',l:'Semi Bold'},{v:'700',l:'Bold'}
                                                ]},
                                                { type: 'color', key: 'hero_eyebrow_color', label: 'Couleur' },
                                                { type: 'number', key: 'hero_eyebrow_letter_spacing', label: 'Espacement lettres', unit:'px' },
                                                { type: 'select', key: 'hero_eyebrow_text_transform', label: 'Casse', options: [
                                                        {v:'uppercase',l:'Majuscules'},{v:'lowercase',l:'Minuscules'},{v:'none',l:'Normal'}
                                                ]},
                                                { type: 'number', key: 'hero_eyebrow_margin_bottom', label: 'Marge bas', unit:'px' },
                                        ],
                                        advanced: []
                                }
                        },
                        'hero-title': {
                                label: 'Titre principal',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'hero_slides.0.title', label: 'Titre' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'hero_title_font_family', label: 'Police', options: [
                                                        {v:'Playfair Display',l:'Playfair Display'},{v:'Inter',l:'Inter'},{v:'Cormorant Garamond',l:'Cormorant Garamond'}
                                                ]},
                                                { type: 'number', key: 'hero_title_font_size', label: 'Taille', unit:'px' },
                                                { type: 'select', key: 'hero_title_font_weight', label: 'Graisse', options: [
                                                        {v:'400',l:'Regular'},{v:'500',l:'Medium'},{v:'600',l:'Semi Bold'},{v:'700',l:'Bold'}
                                                ]},
                                                { type: 'color', key: 'hero_title_color', label: 'Couleur' },
                                                { type: 'number', key: 'hero_title_line_height', label: 'Hauteur de ligne', step:0.1 },
                                                { type: 'number', key: 'hero_title_letter_spacing', label: 'Espacement lettres', unit:'px' },
                                                { type: 'number', key: 'hero_title_margin_bottom', label: 'Marge bas', unit:'px' },
                                        ],
                                        advanced: []
                                }
                        },
                        'hero-subtitle': {
                                label: 'Sous-titre',
                                tabs: {
                                        content: [
                                                { type: 'textarea', key: 'hero_slides.0.subtitle', label: 'Sous-titre' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'hero_subtitle_font_family', label: 'Police', options: [
                                                        {v:'Inter',l:'Inter'},{v:'Playfair Display',l:'Playfair Display'}
                                                ]},
                                                { type: 'number', key: 'hero_subtitle_font_size', label: 'Taille', unit:'px' },
                                                { type: 'select', key: 'hero_subtitle_font_weight', label: 'Graisse', options: [
                                                        {v:'300',l:'Light'},{v:'400',l:'Regular'},{v:'500',l:'Medium'}
                                                ]},
                                                { type: 'color', key: 'hero_subtitle_color', label: 'Couleur' },
                                                { type: 'number', key: 'hero_subtitle_line_height', label: 'Hauteur de ligne', step:0.1 },
                                                { type: 'number', key: 'hero_subtitle_margin_bottom', label: 'Marge bas', unit:'px' },
                                        ],
                                        advanced: []
                                }
                        },
                        'hero-cta': {
                                label: 'Bouton CTA',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'hero_slides.0.cta_text', label: 'Texte du bouton' },
                                                { type: 'text', key: 'hero_slides.0.cta_link', label: 'Lien destination', placeholder: 'https://...' },
                                                { type: 'select', key: 'hero_slides.0.cta_style', label: 'Style du bouton', options: [
                                                        {v:'primary',l:'Principal (fond sombre)'},{v:'gold',l:'Or'},{v:'outline',l:'Contour'}
                                                ]},
                                        ],
                                        style: [
                                                { type: 'number', key: 'hero_cta_font_size', label: 'Taille police', unit:'px' },
                                                { type: 'select', key: 'hero_cta_font_weight', label: 'Graisse', options: [
                                                        {v:'400',l:'Regular'},{v:'500',l:'Medium'},{v:'600',l:'Semi Bold'},{v:'700',l:'Bold'}
                                                ]},
                                                { type: 'number', key: 'hero_cta_letter_spacing', label: 'Espacement lettres', unit:'px' },
                                                { type: 'select', key: 'hero_cta_text_transform', label: 'Casse', options: [
                                                        {v:'uppercase',l:'Majuscules'},{v:'none',l:'Normal'}
                                                ]},
                                                { type: 'number', key: 'hero_cta_padding_x', label: 'Padding horizontal', unit:'px' },
                                                { type: 'number', key: 'hero_cta_padding_y', label: 'Padding vertical', unit:'px' },
                                                { type: 'number', key: 'hero_cta_border_radius', label: 'Border radius', unit:'px' },
                                                { type: 'color', key: 'hero_cta_bg_color', label: 'Couleur fond' },
                                                { type: 'color', key: 'hero_cta_text_color', label: 'Couleur texte' },
                                                { type: 'color', key: 'hero_cta_hover_bg_color', label: 'Couleur fond (hover)' },
                                                { type: 'color', key: 'hero_cta_hover_text_color', label: 'Couleur texte (hover)' },
                                                { type: 'number', key: 'hero_cta_border_width', label: 'Largeur bordure', unit:'px' },
                                                { type: 'color', key: 'hero_cta_border_color', label: 'Couleur bordure' },
                                        ],
                                        advanced: []
                                }
                        },
                        'hero-side': {
                                label: 'Blocs lateraux',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'hs_bestseller_label', label: 'Label best-seller' },
                                                { type: 'text', key: 'hs_bestseller_title', label: 'Titre best-seller' },
                                                { type: 'text', key: 'hs_bestseller_price', label: 'Prix best-seller' },
                                                { type: 'text', key: 'hs_bestseller_cta', label: 'Texte CTA best-seller' },
                                                { type: 'text', key: 'hs_category_label', label: 'Label categorie' },
                                                { type: 'text', key: 'hs_category_title', label: 'Titre categorie' },
                                        ],
                                        style: [
                                                { type: 'number', key: 'hero_side_width', label: 'Largeur panneau', unit:'px' },
                                                { type: 'number', key: 'hero_side_gap', label: 'Espacement cartes', unit:'px' },
                                                { type: 'number', key: 'hero_side_card_radius', label: 'Border radius carte', unit:'px' },
                                                { type: 'number', key: 'hero_side_card_img_height', label: 'Hauteur image carte', unit:'px' },
                                        ],
                                        advanced: [
                                                { type: 'toggle', key: 'hero_hide_side_mobile', label: 'Masquer sur mobile' },
                                        ]
                                }
                        },
                        'hero-dots': {
                                label: 'Navigation slides',
                                tabs: {
                                        content: [
                                                { type: 'toggle', key: 'hero_autoplay', label: 'Lecture automatique' },
                                                { type: 'number', key: 'hero_autoplay_speed', label: 'Vitesse de defilement', unit:'ms' },
                                        ],
                                        style: [],
                                        advanced: []
                                }
                        },

                        /* ──────────────── FEATURES ──────────────── */
                        'feature-item': {
                                label: 'Element de confiance',
                                tabs: {
                                        content: [],
                                        style: [
                                                { type: 'select', key: 'feat_bg_style', label: 'Fond de section', options: [
                                                        {v:'base',l:'Base'},{v:'soft',l:'Doux'},{v:'muted',l:'Mute'},{v:'dark',l:'Sombre'}
                                                ]},
                                                { type: 'select', key: 'feat_padding', label: 'Espacement', options: [
                                                        {v:'compact',l:'Compact'},{v:'normal',l:'Normal'},{v:'wide',l:'Large'}
                                                ]},
                                                { type: 'toggle', key: 'feat_bottom_border', label: 'Bordure inferieure' },
                                        ],
                                        advanced: []
                                }
                        },

                        /* ──────────────── CATEGORIES ──────────────── */
                        'cat-heading': {
                                label: 'En-tete Categories',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'cat_eyebrow', label: 'Sur-titre (eyebrow)' },
                                                { type: 'text', key: 'section_title_categories', label: 'Titre de section' },
                                                { type: 'textarea', key: 'cat_description', label: 'Description' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'cat_bg_style', label: 'Fond de section', options: [
                                                        {v:'base',l:'Base'},{v:'soft',l:'Doux'},{v:'muted',l:'Mute'},{v:'dark',l:'Sombre'}
                                                ]},
                                        ],
                                        advanced: []
                                }
                        },
                        'cat-card': {
                                label: 'Carte categorie',
                                tabs: {
                                        content: [],
                                        style: [
                                                { type: 'number', key: 'cat_display_count', label: 'Nombre de categories' },
                                        ],
                                        advanced: []
                                }
                        },
                        'cat-cta': {
                                label: 'Bouton CTA',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'cat_cta_text', label: 'Texte du bouton' },
                                                { type: 'text', key: 'cat_cta_link', label: 'Lien', placeholder: '/boutique/' },
                                        ],
                                        style: [],
                                        advanced: []
                                }
                        },

                        /* ──────────────── PRODUCTS ──────────────── */
                        'prod-heading': {
                                label: 'En-tete Produits',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'prod_eyebrow', label: 'Sur-titre (eyebrow)' },
                                                { type: 'text', key: 'section_title_products', label: 'Titre de section' },
                                                { type: 'textarea', key: 'prod_description', label: 'Description' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'prod_bg_style', label: 'Fond de section', options: [
                                                        {v:'base',l:'Base'},{v:'soft',l:'Doux'},{v:'muted',l:'Mute'},{v:'dark',l:'Sombre'}
                                                ]},
                                                { type: 'select', key: 'prod_columns', label: 'Colonnes', options: [
                                                        {v:'2',l:'2 colonnes'},{v:'3',l:'3 colonnes'},{v:'4',l:'4 colonnes'}
                                                ]},
                                        ],
                                        advanced: [
                                                { type: 'select', key: 'prod_sort', label: 'Tri', options: [
                                                        {v:'date',l:'Date'},{v:'title',l:'Titre'},{v:'price',l:'Prix'},{v:'popularity',l:'Popularite'}
                                                ]},
                                        ]
                                }
                        },
                        'prod-card': {
                                label: 'Carte produit',
                                tabs: {
                                        content: [
                                                { type: 'number', key: 'featured_products_count', label: 'Nombre de produits' },
                                        ],
                                        style: [],
                                        advanced: []
                                }
                        },
                        'prod-cta': {
                                label: 'Bouton CTA',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'prod_cta_text', label: 'Texte du bouton' },
                                                { type: 'text', key: 'prod_cta_link', label: 'Lien', placeholder: '/boutique/' },
                                        ],
                                        style: [],
                                        advanced: []
                                }
                        },

                        /* ──────────────── SPLIT BANNER ──────────────── */
                        'banner-left': {
                                label: 'Banniere gauche',
                                tabs: {
                                        content: [
                                                { type: 'media', key: 'sb_left_image', label: 'Image' },
                                                { type: 'text', key: 'sb_left_eyebrow', label: 'Sur-titre' },
                                                { type: 'text', key: 'sb_left_title', label: 'Titre' },
                                                { type: 'textarea', key: 'sb_left_desc', label: 'Description' },
                                                { type: 'text', key: 'sb_left_cta_text', label: 'Texte CTA' },
                                                { type: 'text', key: 'sb_left_cta_link', label: 'Lien CTA' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'sb_left_cta_style', label: 'Style CTA', options: [
                                                        {v:'primary',l:'Principal'},{v:'gold',l:'Or'},{v:'outline',l:'Contour'}
                                                ]},
                                                { type: 'select', key: 'sb_left_style', label: 'Variante', options: [
                                                        {v:'dark',l:'Sombre'},{v:'light',l:'Clair'}
                                                ]},
                                        ],
                                        advanced: []
                                }
                        },
                        'banner-right': {
                                label: 'Banniere droite',
                                tabs: {
                                        content: [
                                                { type: 'media', key: 'sb_right_image', label: 'Image' },
                                                { type: 'text', key: 'sb_right_eyebrow', label: 'Sur-titre' },
                                                { type: 'text', key: 'sb_right_title', label: 'Titre' },
                                                { type: 'textarea', key: 'sb_right_desc', label: 'Description' },
                                                { type: 'text', key: 'sb_right_cta_text', label: 'Texte CTA' },
                                                { type: 'text', key: 'sb_right_cta_link', label: 'Lien CTA' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'sb_right_cta_style', label: 'Style CTA', options: [
                                                        {v:'primary',l:'Principal'},{v:'gold',l:'Or'},{v:'outline',l:'Contour'}
                                                ]},
                                                { type: 'select', key: 'sb_right_style', label: 'Variante', options: [
                                                        {v:'dark',l:'Sombre'},{v:'light',l:'Clair'}
                                                ]},
                                        ],
                                        advanced: [
                                                { type: 'select', key: 'sb_layout', label: 'Disposition', options: [
                                                        {v:'50-50',l:'50 / 50'},{v:'60-40',l:'60 / 40'},{v:'40-60',l:'40 / 60'}
                                                ]},
                                        ]
                                }
                        },

                        /* ──────────────── MARQUEE ──────────────── */
                        'marquee-track': {
                                label: 'Bandeau defilant',
                                tabs: {
                                        content: [],
                                        style: [
                                                { type: 'number', key: 'marquee_speed', label: 'Vitesse de defilement', unit:'s' },
                                                { type: 'select', key: 'marquee_direction', label: 'Direction', options: [
                                                        {v:'left',l:'Gauche'},{v:'right',l:'Droite'}
                                                ]},
                                                { type: 'select', key: 'marquee_bg', label: 'Fond', options: [
                                                        {v:'base',l:'Base'},{v:'soft',l:'Doux'},{v:'muted',l:'Mute'},{v:'dark',l:'Sombre'}
                                                ]},
                                        ],
                                        advanced: []
                                }
                        },

                        /* ──────────────── TESTIMONIALS ──────────────── */
                        'testi-heading': {
                                label: 'En-tete Temoignages',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'testi_eyebrow', label: 'Sur-titre' },
                                                { type: 'text', key: 'section_title_testimonials', label: 'Titre de section' },
                                                { type: 'textarea', key: 'testi_description', label: 'Description' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'testi_bg_style', label: 'Fond', options: [
                                                        {v:'base',l:'Base'},{v:'soft',l:'Doux'},{v:'muted',l:'Mute'},{v:'dark',l:'Sombre'}
                                                ]},
                                                { type: 'select', key: 'testi_columns', label: 'Colonnes', options: [
                                                        {v:'2',l:'2 colonnes'},{v:'3',l:'3 colonnes'}
                                                ]},
                                        ],
                                        advanced: [
                                                { type: 'number', key: 'testimonials_count', label: 'Nombre de temoignages' },
                                        ]
                                }
                        },
                        'testi-card': {
                                label: 'Carte temoignage',
                                tabs: {
                                        content: [],
                                        style: [],
                                        advanced: []
                                }
                        },

                        /* ──────────────── BLOG ──────────────── */
                        'blog-heading': {
                                label: 'En-tete Blog',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'blog_eyebrow', label: 'Sur-titre' },
                                                { type: 'text', key: 'section_title_blog', label: 'Titre de section' },
                                                { type: 'textarea', key: 'blog_description', label: 'Description' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'blog_bg_style', label: 'Fond', options: [
                                                        {v:'base',l:'Base'},{v:'soft',l:'Doux'},{v:'muted',l:'Mute'},{v:'dark',l:'Sombre'}
                                                ]},
                                                { type: 'select', key: 'blog_columns', label: 'Colonnes', options: [
                                                        {v:'2',l:'2 colonnes'},{v:'3',l:'3 colonnes'}
                                                ]},
                                        ],
                                        advanced: [
                                                { type: 'number', key: 'blog_posts_count', label: 'Nombre d\'articles' },
                                        ]
                                }
                        },
                        'blog-card': {
                                label: 'Carte article',
                                tabs: {
                                        content: [],
                                        style: [],
                                        advanced: []
                                }
                        },
                        'blog-cta': {
                                label: 'Bouton CTA',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'blog_cta_text', label: 'Texte du bouton' },
                                                { type: 'text', key: 'blog_cta_link', label: 'Lien', placeholder: '/blog/' },
                                        ],
                                        style: [],
                                        advanced: []
                                }
                        },

                        /* ──────────────── INSTAGRAM ──────────────── */
                        'ig-heading': {
                                label: 'En-tete Instagram',
                                tabs: {
                                        content: [
                                                { type: 'text', key: 'ig_eyebrow', label: 'Sur-titre' },
                                                { type: 'text', key: 'instagram_handle', label: 'Nom du compte' },
                                        ],
                                        style: [
                                                { type: 'select', key: 'ig_columns', label: 'Colonnes', options: [
                                                        {v:'4',l:'4 colonnes'},{v:'5',l:'5 colonnes'},{v:'6',l:'6 colonnes'}
                                                ]},
                                                { type: 'select', key: 'ig_gap', label: 'Espacement', options: [
                                                        {v:'none',l:'Aucun'},{v:'small',l:'Petit'},{v:'medium',l:'Moyen'},{v:'large',l:'Grand'}
                                                ]},
                                        ],
                                        advanced: []
                                }
                        },
                        'ig-item': {
                                label: 'Image Instagram',
                                tabs: {
                                        content: [],
                                        style: [],
                                        advanced: []
                                }
                        },
                },

                /* ═══════════════════════════════════════════════════════════════
                   CSS PREVIEW MAP — EXTENDED v3.5.1
                   Maps setting keys → preview definition.
                   Types:
                     { css, unit?, widget? }      → apply inline style on widget
                     { cssVar, widget?, unit? }  → set CSS custom property on widget
                     { sectionCss, unit? }       → apply inline style on section container
                     { class: { val: {add,remove} } } → toggle CSS classes
                     { bgImage, widget? }        → update background-image on widget
                     { none }                    → no live preview (server-render)
                   ═════════════════════════════════════════════════════════════ */
                CSS_MAP: {
                        /* ── Hero Overlay Opacity (CSS var) ── */
                        'hero_overlay_opacity': { cssVar: 'opacity', widget: 'hero-overlay', unit: '%' },

                        /* ── Hero Height (class toggle on section) ── */
                        'hero_height': { 'class': {
                                standard: { add: '', remove: 'v-hero-tall v-hero-compact' },
                                tall:     { add: 'v-hero-tall', remove: 'v-hero-compact' },
                                compact:  { add: 'v-hero-compact', remove: 'v-hero-tall' },
                        }, section: true },

                        /* ── Hero Text Align ── */
                        'hero_text_align': { sectionCss: 'textAlign' },

                        /* ── Hero BG Position / Size ── */
                        'hero_bg_position': { css: 'backgroundPosition', widget: 'hero-bg' },
                        'hero_bg_size': { css: 'backgroundSize', widget: 'hero-bg' },

                        /* ── Hero Content Max Width ── */
                        'hero_content_max_width': { sectionCss: 'maxWidth', unit: 'px' },

                        /* ── Hero Padding Vertical ── */
                        'hero_padding_v': { sectionCss: 'paddingTop', unit: 'px', also: 'paddingBottom', alsoUnit: 'px' },

                        /* ── Hero Text Color ── */
                        'hero_text_color': { sectionCss: 'color', transform: function(v) {
                                return v === 'dark' ? '#1A1A1A' : '#FFFFFF';
                        }},

                        /* ── Hero Side Blocks ── */
                        'hero_show_side': { 'class': {
                                '1': { add: 'v-hero-side-visible', remove: '' },
                                '0': { add: '', remove: 'v-hero-side-visible' },
                        }, section: true },
                        'hero_side_width': { css: 'width', widget: 'hero-side', unit: 'px' },

                        /* ── Hero BG Image ── */
                        'hero_slides.0.image': { bgImage: 'hero-bg' },

                        /* Hero Eyebrow */
                        'hero_eyebrow_font_size':      { css: 'fontSize',      unit: 'px' },
                        'hero_eyebrow_font_weight':    { css: 'fontWeight' },
                        'hero_eyebrow_color':          { css: 'color' },
                        'hero_eyebrow_letter_spacing': { css: 'letterSpacing', unit: 'px' },
                        'hero_eyebrow_text_transform': { css: 'textTransform' },
                        'hero_eyebrow_margin_bottom':  { css: 'marginBottom',  unit: 'px' },
                        'hero_eyebrow_font_family':    { css: 'fontFamily' },

                        /* Hero Title */
                        'hero_title_font_size':      { css: 'fontSize',      unit: 'px' },
                        'hero_title_font_weight':    { css: 'fontWeight' },
                        'hero_title_color':          { css: 'color' },
                        'hero_title_line_height':    { css: 'lineHeight' },
                        'hero_title_letter_spacing': { css: 'letterSpacing', unit: 'px' },
                        'hero_title_margin_bottom':  { css: 'marginBottom',  unit: 'px' },
                        'hero_title_font_family':    { css: 'fontFamily' },

                        /* Hero Subtitle */
                        'hero_subtitle_font_size':      { css: 'fontSize',      unit: 'px' },
                        'hero_subtitle_font_weight':    { css: 'fontWeight' },
                        'hero_subtitle_color':          { css: 'color' },
                        'hero_subtitle_line_height':    { css: 'lineHeight' },
                        'hero_subtitle_margin_bottom':  { css: 'marginBottom',  unit: 'px' },
                        'hero_subtitle_font_family':    { css: 'fontFamily' },

                        /* Hero CTA */
                        'hero_cta_font_size':         { css: 'fontSize',      unit: 'px' },
                        'hero_cta_font_weight':       { css: 'fontWeight' },
                        'hero_cta_letter_spacing':    { css: 'letterSpacing', unit: 'px' },
                        'hero_cta_text_transform':    { css: 'textTransform' },
                        'hero_cta_padding_x':         { css: 'paddingLeft',   unit: 'px', also: 'paddingRight', alsoUnit: 'px' },
                        'hero_cta_padding_y':         { css: 'paddingTop',    unit: 'px', also: 'paddingBottom', alsoUnit: 'px' },
                        'hero_cta_border_radius':     { css: 'borderRadius',  unit: 'px' },
                        'hero_cta_bg_color':          { css: 'backgroundColor' },
                        'hero_cta_text_color':        { css: 'color' },

                        /* ── Section BG Style Classes ── */
                        'cat_bg_style':     { 'class': _vcBgStyleMap(), section: true },
                        'prod_bg_style':    { 'class': _vcBgStyleMap(), section: true },
                        'testi_bg_style':   { 'class': _vcBgStyleMap(), section: true },
                        'blog_bg_style':    { 'class': _vcBgStyleMap(), section: true },
                        'feat_bg_style':    { 'class': { base: {add:'velure-section-base',remove:'velure-section-soft'}, soft: {add:'velure-section-soft',remove:'velure-section-base'} }, section: true },
                        'marquee_bg':       { 'class': { base: {add:'velure-section-base',remove:'velure-section-soft'}, soft: {add:'velure-section-soft',remove:'velure-section-base'} }, section: true },

                        /* ── Grid Columns ── */
                        'prod_columns':  { 'class': _vcColMap(), section: true },
                        'testi_columns': { 'class': _vcColMap(), section: true },
                        'blog_columns':  { 'class': _vcColMap(), section: true },
                        'ig_columns':    { 'class': _vcIgColMap(), section: true },

                        /* ── Instagram Gap ── */
                        'ig_gap': { sectionCss: 'gap', transform: function(v) {
                                var map = { none: '0', small: '4px', medium: '8px', large: '16px' };
                                return map[v] || '4px';
                        }},

                        /* ── Marquee ── */
                        'marquee_speed': { css: 'animationDuration', widget: 'marquee-track', transform: function(v) { return v + 's'; } },
                        'marquee_direction': { css: 'animationDirection', widget: 'marquee-track' },

                        /* ── Split Banner ── */
                        'sb_layout': { 'class': {
                                '50-50': { add: 'velure-split-equal',   remove: 'velure-split-wide velure-split-narrow' },
                                '60-40': { add: 'velure-split-wide',    remove: 'velure-split-equal velure-split-narrow' },
                                '40-60': { add: 'velure-split-narrow',  remove: 'velure-split-equal velure-split-wide' },
                        }, section: true },
                        'sb_left_image':  { bgImage: 'banner-left' },
                        'sb_right_image': { bgImage: 'banner-right' },

                        /* ═══ v3.5.2: Structural / count fields → server-render only ═══ */
                        'featured_products_count': { none: true },
                        'cat_display_count':       { none: true },
                        'testimonials_count':      { none: true },
                        'blog_posts_count':        { none: true },
                        'prod_sort':               { none: true },
                        'hero_hide_side_mobile':   { none: true },
                        'hero_autoplay':           { none: true },
                        'hero_autoplay_speed':     { none: true },

                        /* ═══ v3.5.2: Hero side block styles ═══ */
                        'hero_side_width':           { css: 'width',             widget: 'hero-side', unit: 'px' },
                        'hero_side_gap':             { css: 'gap',              widget: 'hero-side', unit: 'px' },
                        'hero_side_card_radius':     { css: 'borderRadius',     widget: 'hero-side', unit: 'px' },
                        'hero_side_card_img_height': { css: '--card-img-height', widget: 'hero-side', unit: 'px' },
                },

                /* ═══════════════════════════════════════════════════════════════
                   SETTINGS HELPER — deep get by dot-notation key
                   ═════════════════════════════════════════════════════════════ */
                getVal: function (key) {
                        if (!key) return '';
                        var parts = key.split('.');
                        var val = VCB.settings;
                        for (var i = 0; i < parts.length; i++) {
                                if (val === null || val === undefined) return '';
                                val = val[parts[i]];
                        }
                        return (val !== null && val !== undefined) ? val : '';
                },

                /* ═══════════════════════════════════════════════════════════════
                   FIELD HTML RENDERERS
                   ═════════════════════════════════════════════════════════════ */

                /**
                 * Render a single field and return jQuery element.
                 * @param {Object} f  Field definition
                 * @returns {jQuery}
                 */
                renderField: function (f) {
                        var val = String(VCB.getVal(f.key));
                        var $w = $('<div class="vc-bp-field" data-key="' + f.key + '"></div>');

                        switch (f.type) {

                                case 'text':
                                        $w.append('<label>' + f.label + '</label>');
                                        $w.append($('<input type="text" />')
                                                .attr({ 'data-vc-key': f.key, value: val, placeholder: f.placeholder || '' }));
                                        break;

                                case 'textarea':
                                        $w.append('<label>' + f.label + '</label>');
                                        $w.append($('<textarea rows="3"></textarea>')
                                                .attr({ 'data-vc-key': f.key, placeholder: f.placeholder || '' })
                                                .val(val));
                                        break;

                                case 'number':
                                        $w.append('<label>' + f.label + (f.unit ? ' <span class="vc-bp-unit">' + f.unit + '</span>' : '') + '</label>');
                                        $w.append($('<input type="number" />')
                                                .attr({ 'data-vc-key': f.key, step: f.step || 1, min: f.min !== undefined ? f.min : '', max: f.max !== undefined ? f.max : '', value: val }));
                                        break;

                                case 'color':
                                        $w.append('<label>' + f.label + '</label>');
                                        $w.append($('<div class="vc-bp-color-wrap"></div>')
                                                .append($('<input type="color" />').attr({ 'data-vc-key': f.key, value: val || '#000000' }))
                                                .append($('<input type="text" class="vc-bp-color-text" />').attr({ 'data-vc-key': f.key + '_hex', value: val, placeholder: '#000000' })));
                                        break;

                                case 'select':
                                        $w.append('<label>' + f.label + '</label>');
                                        var $sel = $('<select></select>').attr('data-vc-key', f.key);
                                        (f.options || []).forEach(function (o) {
                                                var optVal = o.v !== undefined ? o.v : o.value;
                                                var optLbl = o.l !== undefined ? o.l : o.label;
                                                $sel.append($('<option></option>').attr({ value: optVal, selected: optVal === val ? 'selected' : '' }).text(optLbl));
                                        });
                                        $w.append($sel);
                                        break;

                                case 'toggle':
                                        var isOn = val === '1' || val === 1 || val === true;
                                        $w.html(
                                                '<label class="vc-bp-toggle-label">' +
                                                        '<span>' + f.label + '</span>' +
                                                        '<span class="vc-bp-toggle' + (isOn ? ' active' : '') + '" data-vc-key="' + f.key + '">' +
                                                                '<span class="vc-bp-toggle-knob"></span>' +
                                                        '</span>' +
                                                '</label>'
                                        );
                                        break;

                                case 'range':
                                        var numVal = parseFloat(val) || f.min || 0;
                                        $w.append('<label>' + f.label + ' <span class="vc-bp-range-val">' + numVal + (f.unit || '') + '</span></label>');
                                        $w.append($('<input type="range" />')
                                                .attr({ 'data-vc-key': f.key, min: f.min || 0, max: f.max || 100, step: f.step || 1, value: numVal }));
                                        break;

                                case 'media':
                                        $w.append('<label>' + f.label + '</label>');
                                        var $mediaWrap = $('<div class="vc-bp-media-wrap"></div>');
                                        var $mediaInput = $('<input type="text" class="vc-bp-media-input" readonly />').attr({ 'data-vc-key': f.key, value: val });
                                        var $mediaBtn = $('<button type="button" class="vc-bp-media-btn">Choisir</button>');
                                        if (val) {
                                                $mediaWrap.append($('<div class="vc-bp-media-thumb"><img src="' + val + '" alt=""></div>'));
                                        }
                                        $mediaWrap.append($mediaInput).append($mediaBtn);
                                        $w.append($mediaWrap);
                                        break;
                        }

                        return $w;
                },

                /* ═══════════════════════════════════════════════════════════════
                   PANEL RENDERING — show fields for the selected widget
                   ═════════════════════════════════════════════════════════════ */

                /**
                 * Render the full panel for a widget.
                 * @param {Object} info  Widget info from postMessage (widget, base, label, index, etc.)
                 */
                renderPanel: function (info) {
                        var base = info.base || info.widget;
                        var def  = VCB.FIELDS[base];
                        if (!def) {
                                VCB.showEmpty('Aucun champ defini pour : ' + base);
                                return;
                        }

                        /* Update header */
                        var idxLabel = (info.index !== null && info.index !== undefined) ? ' #' + (parseInt(info.index, 10) + 1) : '';
                        $('.vc-bp-header-title').text(def.label + idxLabel);
                        $('.vc-bp-header-label').text(info.widget);

                        /* Show widget editor */
                        $('#vc-bp-widget-editor').show();

                        /* Render each tab */
                        ['content', 'style', 'advanced'].forEach(function (tabKey) {
                                var fields = def.tabs[tabKey] || [];
                                var $pane  = $('.vc-bp-tab-pane[data-tab="' + tabKey + '"]').empty();

                                if (fields.length === 0) {
                                        $pane.append('<p class="vc-bp-no-fields">Aucune option disponible pour cet onglet.</p>');
                                        return;
                                }

                                fields.forEach(function (f) {
                                        $pane.append(VCB.renderField(f));
                                });
                        });

                        /* Activate first non-empty tab, fallback to 'content' */
                        var firstTab = 'content';
                        if (def.tabs.content && def.tabs.content.length === 0 && def.tabs.style && def.tabs.style.length > 0) {
                                firstTab = 'style';
                        }
                        VCB.switchTab(firstTab);
                },

                /**
                 * Hide the widget editor (deselect state).
                 */
                showEmpty: function (msg) {
                        $('#vc-bp-widget-editor').hide();
                },

                /**
                 * Switch active tab.
                 */
                switchTab: function (tabKey) {
                        VCB.currentTab = tabKey;
                        $('.vc-bp-tab-btn').removeClass('active');
                        $('.vc-bp-tab-btn[data-tab="' + tabKey + '"]').addClass('active');
                        $('.vc-bp-tab-pane').removeClass('active');
                        $('.vc-bp-tab-pane[data-tab="' + tabKey + '"]').addClass('active');
                },

                /* ═══════════════════════════════════════════════════════════════
                   LIVE PREVIEW — push REAL-TIME changes to iframe (Wix-like)
                   ═════════════════════════════════════════════════════════════ */

                /**
                 * When a field changes, determine the preview type and push
                 * the appropriate message to the iframe canvas.
                 * v3.5.1: EXTENDED — handles ALL control types in real-time:
                 *   - Widget inline styles (css)
                 *   - CSS custom properties (cssVar)
                 *   - Section-level styles (sectionCss)
                 *   - Class toggles (class)
                 *   - Background image swaps (bgImage)
                 *   - Text content updates (text)
                 *   - Structural changes → AJAX partial render (vc-replace-html)
                 *
                 * @param {string} key    Settings key (dot-notation)
                 * @param {string} value  New value
                 */
                onFieldChange: function (key, value) {
                        if (!VCB.iframe || !VCB.iframe.contentWindow) return;
                        var widgetId = VCB.selected ? VCB.selected.widget : null;

                        /* ── Queue change for auto-save (debounce 1.5s) ── */
                        VCB.addToChanges(key, value);

                        /* ── Check CSS_MAP for real-time preview ── */
                        var def = VCB.CSS_MAP[key];
                        if (def) {
                                /* TYPE 1: Widget-level inline CSS */
                                if (def.css) {
                                        var cssVal = def.transform ? def.transform(value) : value;
                                        var numVal = parseFloat(cssVal);
                                        if (!isNaN(numVal) && !def.transform) {
                                                cssVal = numVal + (def.unit || '');
                                        }
                                        var css = {};
                                        css[def.css] = cssVal;
                                        if (def.also) {
                                                var alsoVal = def.transform ? def.transform(value) : value;
                                                var alsoNum = parseFloat(alsoVal);
                                                if (!isNaN(alsoNum) && !def.transform) {
                                                        alsoVal = alsoNum + (def.alsoUnit || def.unit || '');
                                                }
                                                css[def.also] = alsoVal;
                                        }
                                        var targetWidget = def.widget || widgetId;
                                        if (targetWidget) {
                                                VCB.sendToIframe({ action: 'vc-apply-style', widget: targetWidget, css: css });
                                        }
                                        return;
                                }

                                /* TYPE 2: CSS custom property on widget */
                                if (def.cssVar) {
                                        var cssVarVal = (def.unit && !def.transform) ? value + def.unit : (def.transform ? def.transform(value) : value);
                                        var varWidget = def.widget || widgetId;
                                        VCB.sendToIframe({
                                                action:   'vc-apply-css-var',
                                                widget:   varWidget,
                                                property: def.cssVar,
                                                value:    cssVarVal
                                        });
                                        return;
                                }

                                /* TYPE 3: Section-level inline CSS */
                                if (def.sectionCss) {
                                        var secCssVal = def.transform ? def.transform(value) : value;
                                        var numSecVal = parseFloat(secCssVal);
                                        if (!isNaN(numSecVal) && !def.transform) {
                                                secCssVal = numSecVal + (def.unit || '');
                                        }
                                        var secCss = {};
                                        secCss[def.sectionCss] = secCssVal;
                                        if (def.also) {
                                                var alsoSecVal = def.transform ? def.transform(value) : value;
                                                var alsoSecNum = parseFloat(alsoSecVal);
                                                if (!isNaN(alsoSecNum) && !def.transform) {
                                                        alsoSecVal = alsoSecNum + (def.alsoUnit || def.unit || '');
                                                }
                                                secCss[def.also] = alsoSecVal;
                                        }
                                        VCB.sendToIframe({ action: 'vc-apply-section-css', css: secCss });
                                        return;
                                }

                                /* TYPE 4: Class toggle */
                                if (def['class']) {
                                        var classMap = def['class'];
                                        var classDef = classMap[String(value)];
                                        if (classDef) {
                                                var target = def.section ? null : (def.widget || widgetId);
                                                if (target) {
                                                        VCB.sendToIframe({
                                                                action: 'vc-apply-class',
                                                                widget: target,
                                                                add: classDef.add || '',
                                                                remove: classDef.remove || ''
                                                        });
                                                } else {
                                                        /* Section-level class toggle */
                                                        VCB.sendToIframe({
                                                                action: 'vc-apply-class',
                                                                widget: '__section__',
                                                                add: classDef.add || '',
                                                                remove: classDef.remove || ''
                                                        });
                                                }
                                        }
                                        return;
                                }

                                /* TYPE 5: Background image swap */
                                if (def.bgImage) {
                                        var bgWidget = def.widget || widgetId;
                                        VCB.sendToIframe({
                                                action: 'vc-update-bg-image',
                                                widget: bgWidget,
                                                url: value
                                        });
                                        return;
                                }

                                /* TYPE 6: { none: true } — explicit server-render only */
                                if (def.none) {
                                        /* Fall through to AJAX partial render below */
                                }
                        }

                        /* ── Text content updates for known editable widgets ── */
                        var base = VCB.selected ? (VCB.selected.base || widgetId) : '';
                        if (base === 'hero-eyebrow' || base === 'hero-title' || base === 'hero-subtitle') {
                                VCB.sendToIframe({ action: 'vc-apply-text', widget: widgetId, text: value, key: key });
                                return;
                        }
                        if (base === 'hero-cta' && key.indexOf('cta_text') !== -1) {
                                VCB.sendToIframe({ action: 'vc-apply-text', widget: widgetId, text: value, key: key });
                                return;
                        }

                        /* ── v3.5.2: Non-hero text fields → send with key for TEXT_SELECTOR_MAP ── */
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
                        if (nonHeroTextKeys.indexOf(key) !== -1) {
                                VCB.sendToIframe({ action: 'vc-apply-text', widget: '__section__', text: value, key: key });
                                return;
                        }

                        /* ── Structural changes: trigger AJAX partial render ──
                           For keys not in CSS_MAP and not text, OR for keys
                           explicitly marked { none: true }, do a surgical
                           section re-render via AJAX (no iframe reload). */
                        VCB._requestPartialRender(key, value);
                },

                /**
                 * Request a surgical HTML fragment from the server and inject it
                 * into the iframe. Only used for structural changes that can't
                 * be handled by CSS alone (new blocks, image additions, etc.)
                 * Debounced to 300ms to avoid flooding on rapid changes.
                 */
                _partialRenderTimer: null,
                _requestPartialRender: function(key, value) {
                        /* Only attempt if we have a valid section */
                        if (!VCB.section) return;

                        clearTimeout(VCB._partialRenderTimer);
                        VCB._partialRenderTimer = setTimeout(function () {
                                $.ajax({
                                        url:  (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.ajaxUrl : '/wp-admin/admin-ajax.php',
                                        type: 'POST',
                                        data: {
                                                action:  'vc_render_component_preview',
                                                section: VCB.section,
                                                key:     key,
                                                value:   value,
                                                _ajax_nonce: (typeof velureCoreAdmin !== 'undefined') ? velureCoreAdmin.nonce : '',
                                        },
                                        success: function (res) {
                                                if (res && res.success && res.data && res.data.html) {
                                                        VCB.sendToIframe({ action: 'vc-replace-html', html: res.data.html });
                                                }
                                        }
                                });
                        }, 300);
                },

                /**
                 * Send a message to the canvas iframe.
                 */
                sendToIframe: function (data) {
                        if (!VCB.iframe || !VCB.iframe.contentWindow) return;
                        VCB.iframe.contentWindow.postMessage(data, '*');
                },

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
                        var icon = type === 'success' ? '\u2713 ' : type === 'error' ? '\u2717 ' : type === 'saving' ? '\u23F3 ' : '';
                        el.innerHTML = icon + msg;
                        el.classList.add('show');
                        clearTimeout(VCB._toastTimer);
                        VCB._toastTimer = setTimeout(function () { el.classList.remove('show'); }, 4000);
                },


                /* ═══════════════════════════════════════════════════════════════
                   POST MESSAGE LISTENER — receive events from canvas-bridge.js
                   ═════════════════════════════════════════════════════════════ */
                onMessage: function (e) {
                        if (!e.data || typeof e.data.action !== 'string') return;

                        switch (e.data.action) {

                                case 'vc-widget-selected':
                                        VCB.selected = {
                                                widget:   e.data.widget,
                                                base:     e.data.base,
                                                section:  e.data.section,
                                                label:    e.data.label,
                                                editable: e.data.editable,
                                                index:    e.data.index,
                                        };
                                        VCB.renderPanel(VCB.selected);
                                        break;

                                case 'vc-widget-deselected':
                                        VCB.selected = null;
                                        VCB.showEmpty();
                                        break;

                                case 'vc-content-changed':
                                        /* Update the corresponding field in the panel */
                                        if (e.data.settingKey) {
                                                var $cfield = $('[data-vc-key="' + e.data.settingKey + '"]');
                                                if ($cfield.length && ($cfield.is('input[type="text"]') || $cfield.is('textarea'))) {
                                                        $cfield.val(e.data.value);
                                                }
                                                /* Queue for auto-save */
                                                VCB.addToChanges(e.data.settingKey, e.data.value);
                                        }
                                        break;
                        }
                },

                /* ═══════════════════════════════════════════════════════════════
                   EVENT BINDING
                   ═════════════════════════════════════════════════════════════ */
                bindEvents: function () {
                        /* Tab switching */
                        $(document).on('click', '.vc-bp-tab-btn', function (e) {
                                e.preventDefault();
                                VCB.switchTab($(this).attr('data-tab'));
                        });

                        /* Deselect button */
                        $(document).on('click', '.vc-bp-close-btn', function () {
                                VCB.selected = null;
                                VCB.showEmpty();
                                VCB.sendToIframe({ action: 'vc-deselect' });
                        });

                        /* Field changes — inputs */
                        $(document).on('input change', '.vc-bp-field input[type="text"], .vc-bp-field input[type="number"], .vc-bp-field textarea, .vc-bp-field select', function () {
                                var key   = $(this).attr('data-vc-key');
                                var value = $(this).val();
                                VCB.onFieldChange(key, value);
                        });

                        /* Color picker sync */
                        $(document).on('input', '.vc-bp-field input[type="color"]', function () {
                                var key   = $(this).attr('data-vc-key');
                                var value = $(this).val();
                                $(this).siblings('.vc-bp-color-text').val(value);
                                VCB.onFieldChange(key, value);
                        });
                        $(document).on('input', '.vc-bp-color-text', function () {
                                var key   = $(this).attr('data-vc-key').replace('_hex', '');
                                var value = $(this).val();
                                $(this).siblings('input[type="color"]').val(value);
                                VCB.onFieldChange(key, value);
                        });

                        /* Range sliders */
                        $(document).on('input', '.vc-bp-field input[type="range"]', function () {
                                var key   = $(this).attr('data-vc-key');
                                var value = $(this).val();
                                var unit  = $(this).closest('.vc-bp-field').find('.vc-bp-unit').text() || '';
                                $(this).siblings('label').find('.vc-bp-range-val').text(value + unit);
                                VCB.onFieldChange(key, value);
                        });

                        /* Toggle switches */
                        $(document).on('click', '.vc-bp-toggle', function () {
                                var $t = $(this);
                                var isOn = $t.hasClass('active');
                                $t.toggleClass('active');
                                var key   = $t.attr('data-vc-key');
                                var value = isOn ? '0' : '1';
                                VCB.onFieldChange(key, value);
                        });

                        /* Media library button */
                        $(document).on('click', '.vc-bp-media-btn', function () {
                                var $wrap = $(this).closest('.vc-bp-media-wrap');
                                var $input = $wrap.find('.vc-bp-media-input');
                                var key = $input.attr('data-vc-key');

                                if (typeof wp === 'undefined' || !wp.media) return;

                                var frame = wp.media({ title: 'Choisir une image', multiple: false, library: { type: 'image' } });
                                frame.on('select', function () {
                                        var attachment = frame.state().get('selection').first().toJSON();
                                        var url = attachment.url;
                                        $input.val(url);
                                        /* Update thumbnail */
                                        var $thumb = $wrap.find('.vc-bp-media-thumb');
                                        if ($thumb.length) {
                                                $thumb.find('img').attr('src', url);
                                        } else {
                                                $wrap.prepend('<div class="vc-bp-media-thumb"><img src="' + url + '" alt=""></div>');
                                        }
                                        VCB.onFieldChange(key, url);
                                });
                                frame.open();
                        });
                },

                /* ═══════════════════════════════════════════════════════════════
                   INIT
                   ═════════════════════════════════════════════════════════════ */
                init: function () {
                        /* Only run inside the visual builder */
                        if (!$('#vc-builder-app').length) return;

                        VCB.section  = $('#vc-builder-section').val() || '';
                        VCB.iframe   = document.getElementById('vc-builder-canvas');

                        /* Parse settings JSON */
                        try {
                                var $json = $('#vc-builder-settings');
                                if ($json.length) {
                                        VCB.settings = JSON.parse($json.text()) || {};
                                }
                        } catch (err) {
                                VCB.settings = {};
                        }

                        /* Bind all events */
                        VCB.bindEvents();

                        /* Listen for postMessage from iframe */
                        window.addEventListener('message', VCB.onMessage);

                        /* Widget editor is hidden by default (inline style), no empty state needed */

                        /* ── Publish button: flush pending changes immediately ── */
                        $(document).on('click', '#vc-publish-btn', function (e) {
                                e.preventDefault();
                                clearTimeout(VCB._saveTimer);
                                VCB.saveChanges();
                        });
                }
        };

        /* Boot when DOM is ready */
        $(document).ready(VCB.init);

})(jQuery);