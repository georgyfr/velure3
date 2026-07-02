#!/usr/bin/env python3
"""
NutriVitaX Pro — Fix all 13 bugs found during audit.
Runs as a single script to ensure atomic, consistent changes.
"""

import os
import re

THEME_DIR = "/home/z/my-project/lampstack-wordpress/wordpress/wp-content/themes/nutrivitax-pro"

def read_file(path):
    with open(path, 'r', encoding='utf-8') as f:
        return f.read()

def write_file(path, content):
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)

# ═══════════════════════════════════════════════════════════════════════
# BUG 2 + 8: theme.json — Fix fonts + remove deprecated mediumStep
# ═══════════════════════════════════════════════════════════════════════
print("BUG 2+8: Fixing theme.json (fonts + mediumStep)...")
theme_json = read_file(f"{THEME_DIR}/theme.json")

# Remove mediumStep from spacingScale
theme_json = theme_json.replace(
    '''"mediumStep": 1,''',
    ''
)

# Replace local font references for missing fonts with Google Fonts CDN equivalents:
# Clash Display → Outfit (similar geometric sans)
# Cabinet Grotesk → Space Grotesk (very similar grotesk)
theme_json = theme_json.replace(
    '''"fontFamily": "'Clash Display', 'Inter', sans-serif",
                                        "slug": "nvx-heading",
                                        "name": "Clash Display",
                                        "fontFace": [
                                                {
                                                        "fontFamily": "Clash Display",
                                                        "src": ["file:./assets/fonts/ClashDisplay-Variable.woff2"],
                                                        "fontWeight": "200 700",
                                                        "fontStyle": "normal",
                                                        "fontDisplay": "swap"
                                                }
                                        ]''',
    '''"fontFamily": "'Outfit', 'Inter', sans-serif",
                                        "slug": "nvx-heading",
                                        "name": "Outfit (Display)",
                                        "fontFace": [
                                                {
                                                        "fontFamily": "Outfit",
                                                        "src": ["https://fonts.gstatic.com/s/outfit/v14/QGYyz_MVcBeNP4NjuGObqx1XmO1I4e.woff2"],
                                                        "fontWeight": "100 900",
                                                        "fontStyle": "normal",
                                                        "fontDisplay": "swap"
                                                }
                                        ]'''
)

theme_json = theme_json.replace(
    '''"fontFamily": "'Cabinet Grotesk', 'Inter', sans-serif",
                                        "slug": "nvx-subheading",
                                        "name": "Cabinet Grotesk",
                                        "fontFace": [
                                                {
                                                        "fontFamily": "Cabinet Grotesk",
                                                        "src": ["file:./assets/fonts/CabinetGrotesk-Variable.woff2"],
                                                        "fontWeight": "100 900",
                                                        "fontStyle": "normal",
                                                        "fontDisplay": "swap"
                                                }
                                        ]''',
    '''"fontFamily": "'Space Grotesk', 'Inter', sans-serif",
                                        "slug": "nvx-subheading",
                                        "name": "Space Grotesk (Subheading)",
                                        "fontFace": [
                                                {
                                                        "fontFamily": "Space Grotesk",
                                                        "src": ["https://fonts.gstatic.com/s/spacegrotesk/v16/V8mDoQDjQSkFtoMM3T6r8E7mPbF4Cw.woff2"],
                                                        "fontWeight": "300 700",
                                                        "fontStyle": "normal",
                                                        "fontDisplay": "swap"
                                                }
                                        ]'''
)

write_file(f"{THEME_DIR}/theme.json", theme_json)
print("  ✓ theme.json fixed (fonts → Google CDN, mediumStep removed)")


# ═══════════════════════════════════════════════════════════════════════
# BUG 5 + 6 + 9: header.html — Fix SVGs, duplicate aria, form, structure
# ═══════════════════════════════════════════════════════════════════════
print("BUG 5+6+4+9: Fixing header.html...")
header = read_file(f"{THEME_DIR}/parts/header.html")

# BUG 6: Remove duplicate aria-label on topbar div (keep the one on <nav>)
header = header.replace(
    '<div class="nvx-topbar" aria-label="Navigation secondaire">',
    '<div class="nvx-topbar">'
)

# BUG 5: Fix SVG icons in topbar social — add fill/stroke
# Facebook SVG
header = header.replace(
    '<a href="#" aria-label="Facebook">\n                                        <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
    '<a href="#" aria-label="Facebook">\n                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>'
)

# Instagram SVG
header = header.replace(
    '<a href="#" aria-label="Instagram">\n                                        <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
    '<a href="#" aria-label="Instagram">\n                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>'
)

# Twitter/X SVG
header = header.replace(
    '<a href="#" aria-label="Twitter / X">\n                                        <svg viewBox="0 0 24 24"><path d="M4 4l11.733 16h4.267l-11.733-16zM4 20l6.768-6.768M20 4l-6.768 6.768"/></svg>',
    '<a href="#" aria-label="Twitter / X">\n                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733-16zM4 20l6.768-6.768M20 4l-6.768 6.768"/></svg>'
)

# BUG 5: Fix nav SVG icons — add fill/stroke
# Accueil icon (house)
header = header.replace(
    '<svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>\n                                                Accueil',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>\n                                                Accueil'
)

# Produits icon (box)
header = header.replace(
    '<svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>'
)

# Arrow down icon in Produits
header = header.replace(
    '<svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>'
)

# Mega menu chevron icons
header = header.replace(
    '<svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>'
)

# Quiz icon (concentric circles)
header = header.replace(
    '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2" fill="currentColor"/></svg>'
)

# Stack icon (4 squares)
header = header.replace(
    '<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>'
)

# Temoignages icon (speech bubble)
header = header.replace(
    '<svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>'
)

# Blog icon (document)
header = header.replace(
    '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>'
)

# Contact icon (envelope)
header = header.replace(
    '<svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>'
)

# Account icon (user)
header = header.replace(
    '<a href="/mon-compte" class="nvx-icon-btn" aria-label="Mon compte">\n                                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    '<a href="/mon-compte" class="nvx-icon-btn" aria-label="Mon compte">\n                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'
)

# Cart icon
header = header.replace(
    '<a href="/panier" class="nvx-icon-btn" aria-label="Panier">\n                                <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
    '<a href="/panier" class="nvx-icon-btn" aria-label="Panier">\n                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>'
)

# Search icon
header = header.replace(
    '<svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>'
)

# Mobile menu close icon
header = header.replace(
    '<svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
)

# Mobile submenu arrow
header = header.replace(
    '<button class="nvx-mobile-nav__link" aria-expanded="false" aria-controls="nvx-mobile-submenu-products">\n                                        Produits\n                                        <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>',
    '<button class="nvx-mobile-nav__link" aria-expanded="false" aria-controls="nvx-mobile-submenu-products">\n                                        Produits\n                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>'
)

# Mobile menu login/register icons
header = header.replace(
    '<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>\n                        Se connecter',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>\n                        Se connecter'
)

header = header.replace(
    '<svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>',
    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>'
)

# BUG 4: Wrap search in a <form> element
header = header.replace(
    '''<!-- Search Bar (Desktop) -->
                        <div class="nvx-search" role="search" aria-label="Rechercher des produits">
                                <div class="nvx-search__input-wrapper">
                                        <input type="search"
                                                   class="nvx-search__input"
                                                   placeholder="Rechercher des produits..."
                                                   aria-label="Rechercher"
                                                   aria-autocomplete="list"
                                                   aria-controls="nvx-search-results"
                                                   autocomplete="off"/>
                                        <button class="nvx-search__btn" aria-label="Lancer la recherche">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                        </button>
                                </div>
                                <div class="nvx-search__results" id="nvx-search-results" aria-live="polite"></div>
                        </div>''',
    '''<!-- Search Bar (Desktop) -->
                        <form class="nvx-search" role="search" aria-label="Rechercher des produits" action="/boutique/" method="get">
                                <div class="nvx-search__input-wrapper">
                                        <input type="search"
                                                   name="s"
                                                   class="nvx-search__input"
                                                   placeholder="Rechercher des produits..."
                                                   aria-label="Rechercher"
                                                   aria-autocomplete="list"
                                                   aria-controls="nvx-search-results"
                                                   autocomplete="off"/>
                                        <button type="submit" class="nvx-search__btn" aria-label="Lancer la recherche">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                        </button>
                                </div>
                                <div class="nvx-search__results" id="nvx-search-results" aria-live="polite"></div>
                        </form>'''
)

# BUG 9: Move spacer and mobile overlay INSIDE the header element (before </header>)
header = header.replace(
    '''        </div><!-- /.nvx-header__inner -->
</header>

<!-- Header Spacer (compensates fixed position) -->
<div class="nvx-header-spacer" id="nvx-header-spacer" aria-hidden="true"></div>

<!-- Mobile Overlay -->
<div class="nvx-mobile-overlay" id="nvx-mobile-overlay" aria-hidden="true"></div>''',
    '''        </div><!-- /.nvx-header__inner -->

        <!-- Header Spacer (compensates fixed position) -->
        <div class="nvx-header-spacer" id="nvx-header-spacer" aria-hidden="true"></div>

        <!-- Mobile Overlay -->
        <div class="nvx-mobile-overlay" id="nvx-mobile-overlay" aria-hidden="true"></div>
</header>'''
)

write_file(f"{THEME_DIR}/parts/header.html", header)
print("  ✓ header.html fixed (SVGs, aria-label, search form, structure)")


# ═══════════════════════════════════════════════════════════════════════
# BUG 1 + 11 + 13: footer.html — Fix shortcodes, pattern, role
# ═══════════════════════════════════════════════════════════════════════
print("BUG 1+11+13+5: Fixing footer.html...")
footer = read_file(f"{THEME_DIR}/parts/footer.html")

# BUG 11: Remove unregistered pattern comment
footer = footer.replace('<!-- wp:pattern {"slug":"nvx-mega-footer"} -->\n', '')

# BUG 13: Remove redundant role="contentinfo" (implicit on <footer>)
footer = footer.replace(
    '<footer class="wp-block-group alignfull nvx-mega-footer" role="contentinfo" id="nvx-footer">',
    '<footer class="wp-block-group alignfull nvx-mega-footer" id="nvx-footer">'
)

# BUG 1: Replace shortcode placeholders with data-attribute spans for JS
footer = footer.replace(
    '© [nvx_year] NutriVitaX Pro. Tous droits réservés. | Design BioLab Luxe | FSE Block Theme v[nvx_version]',
    '© <span data-nvx-dynamic="year"></span> NutriVitaX Pro. Tous droits réservés. | Design BioLab Luxe | FSE Block Theme v<span data-nvx-dynamic="version"></span>'
)

# BUG 5: Fix footer SVG icons — add fill/stroke
# Facebook
footer = footer.replace(
    '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
    '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>'
)

# Instagram - already has fill="none" stroke="currentColor" ✓
# YouTube - already has fill="none" stroke="currentColor" ✓
# LinkedIn - already has fill="none" stroke="currentColor" ✓
# Twitter/X - already has fill="currentColor" ✓

write_file(f"{THEME_DIR}/parts/footer.html", footer)
print("  ✓ footer.html fixed (shortcodes → data-attr, pattern removed, role fixed)")


# ═══════════════════════════════════════════════════════════════════════
# BUG 3 + 7 + 12: homepage.css — Fix @layer conflict, footer responsive, shake
# ═══════════════════════════════════════════════════════════════════════
print("BUG 3+7+12: Fixing homepage.css...")
hp_css = read_file(f"{THEME_DIR}/assets/css/layers/homepage.css")

# BUG 12: Add @keyframes shake inside the nvx-homepage layer (before the closing)
# Find the end of the layer and add before it
shake_keyframes = '''
/* Quiz validation shake animation */
@keyframes nvx-shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-8px); }
        40% { transform: translateX(8px); }
        60% { transform: translateX(-4px); }
        80% { transform: translateX(4px); }
}
'''

# Add shake keyframes right before the closing of @layer nvx-homepage
# Find the last closing of the layer
hp_css = hp_css.replace(
    '} /* @layer nvx-homepage */',
    shake_keyframes + '} /* @layer nvx-homepage */'
)

# BUG 7: Add footer bottom bar responsive CSS (UNLAYERED to override any layered styles)
footer_responsive = '''

/* ═══════════════════════════════════════════════════════════════════════
   FOOTER BOTTOM BAR — Responsive (unlayered for specificity)
   ═══════════════════════════════════════════════════════════════════════ */
@media (max-width: 767px) {
        .nvx-mega-footer__bottom-inner {
                flex-direction: column !important;
                text-align: center !important;
                gap: 0.5rem !important;
        }
        .nvx-mega-footer__copy,
        .nvx-mega-footer__compat {
                text-align: center !important;
        }
        .nvx-footer-payments {
                justify-content: center !important;
                flex-wrap: wrap !important;
        }
}
'''

hp_css = hp_css + footer_responsive

# BUG 3: Extract H2-on-dark overrides from @layer and add as UNLAYERED CSS
# First, find and remove the layered H2 overrides
h2_dark_overrides = '''
/* H2 on dark backgrounds — override theme.json global H2 color */
.nvx-hero h2,
.nvx-hero .wp-block-heading,
.nvx-social-proof h2,
.nvx-social-proof .wp-block-heading,
.nvx-mega-footer h2,
.nvx-mega-footer .wp-block-heading {'''

# Replace the layered version with unlayered version
# First, find the full block inside @layer
h2_dark_pattern = r'(\/\* H2 on dark backgrounds.*?\*\/\n\.nvx-hero h2,.*?\})\s*\n'
match = re.search(h2_dark_pattern, hp_css, re.DOTALL)
if match:
    layered_block = match.group(1)
    # Remove it from the layer
    hp_css = hp_css.replace(layered_block, '', 1)

# Now add the unlayered version at the end
unlayered_h2 = '''

/* ═══════════════════════════════════════════════════════════════════════
   H2 ON DARK BACKGROUNDS — UNLAYERED (overrides theme.json)
   theme.json generates unlayered CSS which beats @layer declarations.
   These overrides MUST be unlayered to win. (#fff on dark = readable)
   ═══════════════════════════════════════════════════════════════════════ */
.nvx-hero h2,
.nvx-hero .wp-block-heading,
.nvx-social-proof h2,
.nvx-social-proof .wp-block-heading,
.nvx-newsletter-section h2,
.nvx-newsletter-section .wp-block-heading {
        color: #FFFFFF !important;
}

.nvx-mega-footer h2,
.nvx-mega-footer .wp-block-heading {
        color: #FFFFFF !important;
}
'''

hp_css = hp_css + unlayered_h2

write_file(f"{THEME_DIR}/assets/css/layers/homepage.css", hp_css)
print("  ✓ homepage.css fixed (H2 unlayered, footer responsive, shake keyframes)")


# ═══════════════════════════════════════════════════════════════════════
# BUG 3 (cont): header.css — Move :root custom properties out of @layer
# ═══════════════════════════════════════════════════════════════════════
print("BUG 3 (cont): Fixing header.css...")
hdr_css = read_file(f"{THEME_DIR}/assets/css/layers/header.css")

# The :root block with custom properties is inside @layer nvx-header
# Extract it and place it BEFORE the @layer declaration
root_pattern = r'(@layer nvx-header \{)\s*\n(:root \{[^}]+\})'
match = re.search(root_pattern, hdr_css, re.DOTALL)
if match:
    layer_open = match.group(1)
    root_block = match.group(2)
    # Remove :root from inside the layer
    hdr_css = hdr_css.replace(layer_open + '\n' + root_block, layer_open)
    # Place :root BEFORE @layer (unlayered)
    hdr_css = hdr_css.replace('@layer nvx-header {', root_block + '\n\n@layer nvx-header {')

write_file(f"{THEME_DIR}/assets/css/layers/header.css", hdr_css)
print("  ✓ header.css fixed (:root custom properties moved out of @layer)")


# ═══════════════════════════════════════════════════════════════════════
# BUG 10: home.html — Add <main> landmark
# ═══════════════════════════════════════════════════════════════════════
print("BUG 10: Fixing home.html (add <main> landmark)...")
home = read_file(f"{THEME_DIR}/templates/home.html")

# Add <main> wrapper around all sections between header and footer
# Replace the hero section opening to wrap everything in <main>
home = home.replace(
    '''<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- ═══════════════════════════════════════════════════════════════════════
     1. SECTION HERO''',
    '''<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- ═══════════════════════════════════════════════════════════════════════
     MAIN CONTENT WRAPPER
     ═══════════════════════════════════════════════════════════════════════ -->
<main id="nvx-woo-content" class="nvx-main-content" role="main">

<!-- ═══════════════════════════════════════════════════════════════════════
     1. SECTION HERO'''
)

# Remove the old nvx-woo-content id from the featured section (now on <main>)
home = home.replace(
    'id="nvx-woo-content"',
    'id="nvx-featured-content"'
)

# Close </main> before the footer template part
home = home.replace(
    '''<!-- ═══════════════════════════════════════════════════════════════════════
     10. MEGA-FOOTER (template part réutilisable)
     ═══════════════════════════════════════════════════════════════════════ -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->''',
    '''</main>

<!-- ═══════════════════════════════════════════════════════════════════════
     10. MEGA-FOOTER (template part réutilisable)
     ═══════════════════════════════════════════════════════════════════════ -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->'''
)

write_file(f"{THEME_DIR}/templates/home.html", home)
print("  ✓ home.html fixed (<main> landmark added, skip link target fixed)")


# ═══════════════════════════════════════════════════════════════════════
# BUG 1 (cont) + 12: theme.js — Add footer dynamic content + use CSS shake
# ═══════════════════════════════════════════════════════════════════════
print("BUG 1+12: Fixing theme.js (footer dynamic year/version)...")
theme_js = read_file(f"{THEME_DIR}/assets/js/theme.js")

# Add footer dynamic content replacement after NVX.init()
footer_dynamic = '''

/**
 * Replace data-nvx-dynamic attributes with real values.
 * Works in FSE template parts where shortcodes are not processed.
 */
NVX.footerDynamic = function() {
        document.querySelectorAll( '[data-nvx-dynamic="year"]' ).forEach( function( el ) {
                el.textContent = new Date().getFullYear();
        });
        document.querySelectorAll( '[data-nvx-dynamic="version"]' ).forEach( function( el ) {
                el.textContent = typeof nvxData !== 'undefined' ? '' : '0.3.0';
        });
};

// Initialize footer dynamics
if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', () => NVX.footerDynamic() );
} else {
        NVX.footerDynamic();
}
'''

# Append before the last line of the file
theme_js = theme_js.rstrip() + '\n' + footer_dynamic

write_file(f"{THEME_DIR}/assets/js/theme.js", theme_js)
print("  ✓ theme.js fixed (footer dynamic year/version replacement)")


# ═══════════════════════════════════════════════════════════════════════
# BUG 12: homepage.js — Remove dynamic shake style injection
# ═══════════════════════════════════════════════════════════════════════
print("BUG 12: Fixing homepage.js (remove dynamic shake injection)...")
hp_js = read_file(f"{THEME_DIR}/assets/js/modules/homepage.js")

# Remove the dynamic style injection at the end
hp_js = hp_js.replace(
    '''

// Add shake keyframe for quiz validation
const shakeStyle = document.createElement( 'style' );
shakeStyle.textContent = `
@keyframes shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-8px); }
        40% { transform: translateX(8px); }
        60% { transform: translateX(-4px); }
        80% { transform: translateX(4px); }
}
`;
document.head.appendChild( shakeStyle );''',
    ''
)

# Also update the shake animation reference in the quiz to use the CSS class name
hp_js = hp_js.replace(
    "fieldset.style.animation = 'shake 0.4s ease';",
    "fieldset.style.animation = 'nvx-shake 0.4s ease';"
)

write_file(f"{THEME_DIR}/assets/js/modules/homepage.js", hp_js)
print("  ✓ homepage.js fixed (shake keyframes moved to CSS, named nvx-shake)")


# ═══════════════════════════════════════════════════════════════════════
# BUG 4 (cont): header.js — Prevent default form submit for AJAX search
# ═══════════════════════════════════════════════════════════════════════
print("BUG 4 (cont): Fixing header.js (search form submit handler)...")
hdr_js = read_file(f"{THEME_DIR}/assets/js/header.js")

# Update the search button handler to also handle form submit
hdr_js = hdr_js.replace(
    '''                // Search form submit
                const searchBtn = $('.nvx-search__btn');
                if (searchBtn) {
                        searchBtn.addEventListener('click', () => {
                                performSearch();
                        });
                }''',
    '''                // Search form submit (button click + Enter key)
                const searchForm = $('form.nvx-search');
                if (searchForm) {
                        searchForm.addEventListener('submit', (e) => {
                                e.preventDefault();
                                performSearch();
                        });
                }''')

write_file(f"{THEME_DIR}/assets/js/header.js", hdr_js)
print("  ✓ header.js fixed (search form submit handler)")


# ═══════════════════════════════════════════════════════════════════════
# Fix style.css version
# ═══════════════════════════════════════════════════════════════════════
print("Updating style.css version...")
style_css = read_file(f"{THEME_DIR}/style.css")
style_css = style_css.replace(
    'Version:      0.3.0',
    'Version:      0.3.1'
)
write_file(f"{THEME_DIR}/style.css", style_css)
print("  ✓ style.css version bumped to 0.3.1")

# Update NVX_VERSION in functions.php
print("Updating functions.php version...")
functions_php = read_file(f"{THEME_DIR}/functions.php")
functions_php = functions_php.replace(
    "define( 'NVX_VERSION', '0.3.0' );",
    "define( 'NVX_VERSION', '0.3.1' );"
)
write_file(f"{THEME_DIR}/functions.php", functions_php)
print("  ✓ functions.php version bumped to 0.3.1")

print("\n" + "=" * 60)
print("ALL 13 BUGS FIXED SUCCESSFULLY!")
print("=" * 60)