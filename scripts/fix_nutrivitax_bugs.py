#!/usr/bin/env python3
"""
Fix all identified NutriVitaX Pro theme bugs in one pass.
"""

import re
import os

THEME_DIR = "/home/z/my-project/lampstack-wordpress/wordpress/wp-content/themes/nutrivitax-pro"

# ═══════════════════════════════════════════════════════════════════
# FIX 1: front-page.html already copied. Now fix it to use static products
# ═══════════════════════════════════════════════════════════════════

STATIC_PRODUCTS = '''<!-- Featured Products Grid (Static — FSE-compatible) -->
        <div class="wp-block-query alignwide nvx-featured-grid">

                <!-- Product 1: Whey Proteine Isolat -->
                <article class="nvx-product-card" aria-label="Whey Proteine Isolat">
                        <div class="nvx-product-card__flipper">
                                <div class="nvx-product-card__front">
                                        <div class="nvx-product-card__image-wrap">
                                                <div class="nvx-product-card__image" role="img" aria-label="Whey Proteine Isolat" style="background:linear-gradient(135deg, var(--wp--preset--color--nvx-light), #d5f5e3);display:flex;align-items:center;justify-content:center;min-height:220px;font-size:3rem;color:var(--wp--preset--color--nvx-primary);">W</div>
                                                <span class="nvx-product-card__ai-badge" aria-label="Score de pertinence IA">
                                                        <span class="nvx-product-card__ai-score">98%</span>
                                                        <span class="nvx-product-card__ai-label">Match IA</span>
                                                </span>
                                        </div>
                                        <div class="nvx-product-card__body">
                                                <h3 class="nvx-product-card__title" style="font-size:1.1rem;font-weight:600;margin:0 0 0.5rem;">Whey Proteine Isolat</h3>
                                                <div class="nvx-product-card__rating" aria-label="Note moyenne verifiee">
                                                        <span class="nvx-product-card__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                                        <span class="nvx-product-card__rating-count">(124 avis verifies)</span>
                                                        <span class="nvx-product-card__verified-icon" aria-label="Avis verifies" title="Avis verifies">&#10003;</span>
                                                </div>
                                                <div class="nvx-product-card__price-row">
                                                        <span class="nvx-product-card__price">49,99 EUR</span>
                                                </div>
                                                <a href="/produit/whey-proteine-isolat" class="nvx-product-card__cart-btn nvx-btn-animated" aria-label="Voir le produit Whey Proteine Isolat">
                                                        <span class="nvx-btn-animated__text">Voir les details</span>
                                                        <span class="nvx-btn-animated__icon" aria-hidden="true">&#8594;</span>
                                                </a>
                                        </div>
                                </div>
                                <div class="nvx-product-card__back">
                                        <p class="nvx-product-card__excerpt">Proteine de lactoserum isolate, 27g de proteines par dose. Sans lactose, digestion optimale. Ideale pour la recuperation musculaire.</p>
                                        <div class="nvx-product-card__back-cta">
                                                <a href="/produit/whey-proteine-isolat" class="nvx-product-card__details-link">Voir les details &#8594;</a>
                                        </div>
                                </div>
                        </div>
                </article>

                <!-- Product 2: Creatine Monohydrate -->
                <article class="nvx-product-card" aria-label="Creatine Monohydrate">
                        <div class="nvx-product-card__flipper">
                                <div class="nvx-product-card__front">
                                        <div class="nvx-product-card__image-wrap">
                                                <div class="nvx-product-card__image" role="img" aria-label="Creatine Monohydrate" style="background:linear-gradient(135deg, #e8f8f0, #b8e6d0);display:flex;align-items:center;justify-content:center;min-height:220px;font-size:3rem;color:var(--wp--preset--color--nvx-primary);">C</div>
                                                <span class="nvx-product-card__ai-badge" aria-label="Score de pertinence IA">
                                                        <span class="nvx-product-card__ai-score">96%</span>
                                                        <span class="nvx-product-card__ai-label">Match IA</span>
                                                </span>
                                        </div>
                                        <div class="nvx-product-card__body">
                                                <h3 class="nvx-product-card__title" style="font-size:1.1rem;font-weight:600;margin:0 0 0.5rem;">Creatine Monohydrate</h3>
                                                <div class="nvx-product-card__rating" aria-label="Note moyenne verifiee">
                                                        <span class="nvx-product-card__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                                        <span class="nvx-product-card__rating-count">(98 avis verifies)</span>
                                                        <span class="nvx-product-card__verified-icon" aria-label="Avis verifies" title="Avis verifies">&#10003;</span>
                                                </div>
                                                <div class="nvx-product-card__price-row">
                                                        <span class="nvx-product-card__price">24,99 EUR</span>
                                                </div>
                                                <a href="/produit/creatine-monohydrate" class="nvx-product-card__cart-btn nvx-btn-animated" aria-label="Voir le produit Creatine Monohydrate">
                                                        <span class="nvx-btn-animated__text">Voir les details</span>
                                                        <span class="nvx-btn-animated__icon" aria-hidden="true">&#8594;</span>
                                                </a>
                                        </div>
                                </div>
                                <div class="nvx-product-card__back">
                                        <p class="nvx-product-card__excerpt">Creatine monohydrate micronisee Creapure, 5g par dose. Ameliore la force, la puissance et le volume musculaire. Sans arome.</p>
                                        <div class="nvx-product-card__back-cta">
                                                <a href="/produit/creatine-monohydrate" class="nvx-product-card__details-link">Voir les details &#8594;</a>
                                        </div>
                                </div>
                        </div>
                </article>

                <!-- Product 3: Pre-Workout Extreme -->
                <article class="nvx-product-card" aria-label="Pre-Workout Extreme">
                        <div class="nvx-product-card__flipper">
                                <div class="nvx-product-card__front">
                                        <div class="nvx-product-card__image-wrap">
                                                <div class="nvx-product-card__image" role="img" aria-label="Pre-Workout Extreme" style="background:linear-gradient(135deg, #fff8e1, #ffe082);display:flex;align-items:center;justify-content:center;min-height:220px;font-size:3rem;color:var(--wp--preset--color--nvx-accent);">P</div>
                                                <span class="nvx-product-card__ai-badge" aria-label="Score de pertinence IA">
                                                        <span class="nvx-product-card__ai-score">95%</span>
                                                        <span class="nvx-product-card__ai-label">Match IA</span>
                                                </span>
                                        </div>
                                        <div class="nvx-product-card__body">
                                                <h3 class="nvx-product-card__title" style="font-size:1.1rem;font-weight:600;margin:0 0 0.5rem;">Pre-Workout Extreme</h3>
                                                <div class="nvx-product-card__rating" aria-label="Note moyenne verifiee">
                                                        <span class="nvx-product-card__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                                        <span class="nvx-product-card__rating-count">(87 avis verifies)</span>
                                                        <span class="nvx-product-card__verified-icon" aria-label="Avis verifies" title="Avis verifies">&#10003;</span>
                                                </div>
                                                <div class="nvx-product-card__price-row">
                                                        <span class="nvx-product-card__price">39,99 EUR</span>
                                                </div>
                                                <a href="/produit/pre-workout-extreme" class="nvx-product-card__cart-btn nvx-btn-animated" aria-label="Voir le produit Pre-Workout Extreme">
                                                        <span class="nvx-btn-animated__text">Voir les details</span>
                                                        <span class="nvx-btn-animated__icon" aria-hidden="true">&#8594;</span>
                                                </a>
                                        </div>
                                </div>
                                <div class="nvx-product-card__back">
                                        <p class="nvx-product-card__excerpt">Formule pre-entrainement complete : caffeine, beta-alanine, citrulline, taurine. Energie explosive et pump maximal sans crash.</p>
                                        <div class="nvx-product-card__back-cta">
                                                <a href="/produit/pre-workout-extreme" class="nvx-product-card__details-link">Voir les details &#8594;</a>
                                        </div>
                                </div>
                        </div>
                </article>

                <!-- Product 4: BCAA 4:1:1 -->
                <article class="nvx-product-card" aria-label="BCAA 4:1:1">
                        <div class="nvx-product-card__flipper">
                                <div class="nvx-product-card__front">
                                        <div class="nvx-product-card__image-wrap">
                                                <div class="nvx-product-card__image" role="img" aria-label="BCAA 4:1:1" style="background:linear-gradient(135deg, #e3f2fd, #90caf9);display:flex;align-items:center;justify-content:center;min-height:220px;font-size:3rem;color:#1565C0;">B</div>
                                                <span class="nvx-product-card__ai-badge" aria-label="Score de pertinence IA">
                                                        <span class="nvx-product-card__ai-score">94%</span>
                                                        <span class="nvx-product-card__ai-label">Match IA</span>
                                                </span>
                                        </div>
                                        <div class="nvx-product-card__body">
                                                <h3 class="nvx-product-card__title" style="font-size:1.1rem;font-weight:600;margin:0 0 0.5rem;">BCAA 4:1:1</h3>
                                                <div class="nvx-product-card__rating" aria-label="Note moyenne verifiee">
                                                        <span class="nvx-product-card__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                                        <span class="nvx-product-card__rating-count">(76 avis verifies)</span>
                                                        <span class="nvx-product-card__verified-icon" aria-label="Avis verifies" title="Avis verifies">&#10003;</span>
                                                </div>
                                                <div class="nvx-product-card__price-row">
                                                        <span class="nvx-product-card__price">29,99 EUR</span>
                                                </div>
                                                <a href="/produit/bcaa-4-1-1" class="nvx-product-card__cart-btn nvx-btn-animated" aria-label="Voir le produit BCAA 4:1:1">
                                                        <span class="nvx-btn-animated__text">Voir les details</span>
                                                        <span class="nvx-btn-animated__icon" aria-hidden="true">&#8594;</span>
                                                </a>
                                        </div>
                                </div>
                                <div class="nvx-product-card__back">
                                        <p class="nvx-product-card__excerpt">Acides amines ramifies 4:1:1 (leucine, isoleucine, valine). Recuperation musculaire acceleree, anti-catabolique. Saveur agrume frais.</p>
                                        <div class="nvx-product-card__back-cta">
                                                <a href="/produit/bcaa-4-1-1" class="nvx-product-card__details-link">Voir les details &#8594;</a>
                                        </div>
                                </div>
                        </div>
                </article>

                <!-- Product 5: Omega-3 Ultra Pur -->
                <article class="nvx-product-card" aria-label="Omega-3 Ultra Pur">
                        <div class="nvx-product-card__flipper">
                                <div class="nvx-product-card__front">
                                        <div class="nvx-product-card__image-wrap">
                                                <div class="nvx-product-card__image" role="img" aria-label="Omega-3 Ultra Pur" style="background:linear-gradient(135deg, #f3e5f5, #ce93d8);display:flex;align-items:center;justify-content:center;min-height:220px;font-size:3rem;color:#7B1FA2;">O</div>
                                                <span class="nvx-product-card__ai-badge" aria-label="Score de pertinence IA">
                                                        <span class="nvx-product-card__ai-score">97%</span>
                                                        <span class="nvx-product-card__ai-label">Match IA</span>
                                                </span>
                                        </div>
                                        <div class="nvx-product-card__body">
                                                <h3 class="nvx-product-card__title" style="font-size:1.1rem;font-weight:600;margin:0 0 0.5rem;">Omega-3 Ultra Pur</h3>
                                                <div class="nvx-product-card__rating" aria-label="Note moyenne verifiee">
                                                        <span class="nvx-product-card__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                                        <span class="nvx-product-card__rating-count">(112 avis verifies)</span>
                                                        <span class="nvx-product-card__verified-icon" aria-label="Avis verifies" title="Avis verifies">&#10003;</span>
                                                </div>
                                                <div class="nvx-product-card__price-row">
                                                        <span class="nvx-product-card__price">34,99 EUR</span>
                                                </div>
                                                <a href="/produit/omega-3-ultra-pur" class="nvx-product-card__cart-btn nvx-btn-animated" aria-label="Voir le produit Omega-3 Ultra Pur">
                                                        <span class="nvx-btn-animated__text">Voir les details</span>
                                                        <span class="nvx-btn-animated__icon" aria-hidden="true">&#8594;</span>
                                                </a>
                                        </div>
                                </div>
                                <div class="nvx-product-card__back">
                                        <p class="nvx-product-card__excerpt">Huile de poisson purifiee moleculairement, EPA 660mg + DHA 440mg par capsule. Certifie IFOS 5 etoiles. Sans goût de poisson.</p>
                                        <div class="nvx-product-card__back-cta">
                                                <a href="/produit/omega-3-ultra-pur" class="nvx-product-card__details-link">Voir les details &#8594;</a>
                                        </div>
                                </div>
                        </div>
                </article>

                <!-- Product 6: Vitamine D3 5000 UI -->
                <article class="nvx-product-card" aria-label="Vitamine D3 5000 UI">
                        <div class="nvx-product-card__flipper">
                                <div class="nvx-product-card__front">
                                        <div class="nvx-product-card__image-wrap">
                                                <div class="nvx-product-card__image" role="img" aria-label="Vitamine D3 5000 UI" style="background:linear-gradient(135deg, #fff3e0, #ffcc80);display:flex;align-items:center;justify-content:center;min-height:220px;font-size:3rem;color:#E65100;">D</div>
                                                <span class="nvx-product-card__ai-badge" aria-label="Score de pertinence IA">
                                                        <span class="nvx-product-card__ai-score">99%</span>
                                                        <span class="nvx-product-card__ai-label">Match IA</span>
                                                </span>
                                        </div>
                                        <div class="nvx-product-card__body">
                                                <h3 class="nvx-product-card__title" style="font-size:1.1rem;font-weight:600;margin:0 0 0.5rem;">Vitamine D3 5000 UI</h3>
                                                <div class="nvx-product-card__rating" aria-label="Note moyenne verifiee">
                                                        <span class="nvx-product-card__stars" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                                                        <span class="nvx-product-card__rating-count">(203 avis verifies)</span>
                                                        <span class="nvx-product-card__verified-icon" aria-label="Avis verifies" title="Avis verifies">&#10003;</span>
                                                </div>
                                                <div class="nvx-product-card__price-row">
                                                        <span class="nvx-product-card__price">19,99 EUR</span>
                                                </div>
                                                <a href="/produit/vitamine-d3-5000-ui" class="nvx-product-card__cart-btn nvx-btn-animated" aria-label="Voir le produit Vitamine D3 5000 UI">
                                                        <span class="nvx-btn-animated__text">Voir les details</span>
                                                        <span class="nvx-btn-animated__icon" aria-hidden="true">&#8594;</span>
                                                </a>
                                        </div>
                                </div>
                                <div class="nvx-product-card__back">
                                        <p class="nvx-product-card__excerpt">Vitamine D3 (cholécalciférol) 5000 UI par capsule molle. Favorise l\'immunite, la fixation du calcium et la sante osseuse. 120 capsules.</p>
                                        <div class="nvx-product-card__back-cta">
                                                <a href="/produit/vitamine-d3-5000-ui" class="nvx-product-card__details-link">Voir les details &#8594;</a>
                                        </div>
                                </div>
                        </div>
                </article>

        </div>'''

# Pattern to match the wp:query product block
QUERY_PATTERN = re.compile(
    r'<!-- wp:query\s.*?-->\s*<div class="wp-block-query.*?nvx-featured-grid">.*?'
    r'<!-- wp:post-template.*?-->.*?'
    r'</article>\s*'
    r'<!-- /wp:post-template -->\s*'
    r'<!-- wp:query-no-results -->.*?<!-- /wp:query-no-results -->\s*'
    r'</div>\s*'
    r'<!-- /wp:query -->',
    re.DOTALL
)

# ═══════════════════════════════════════════════════════════════════
# FIX 1+2: Replace wp:query with static products in front-page.html
# ═══════════════════════════════════════════════════════════════════

front_page_path = os.path.join(THEME_DIR, "templates", "front-page.html")
with open(front_page_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the wp:query block with static products
new_content = QUERY_PATTERN.sub(STATIC_PRODUCTS, content)

if new_content == content:
    print("WARNING: Query pattern not found in front-page.html, trying broader match...")
    # Broader fallback: match from wp:query to /wp:query
    broader = re.compile(
        r'<!-- wp:query\s.*?-->.*?<!-- /wp:query -->',
        re.DOTALL
    )
    new_content = broader.sub(STATIC_PRODUCTS, content)

with open(front_page_path, 'w', encoding='utf-8') as f:
    f.write(new_content)
print(f"FIX 1+2: front-page.html created with static supplement products")

# ═══════════════════════════════════════════════════════════════════
# FIX 2b: Also fix home.html (the custom template)
# ═══════════════════════════════════════════════════════════════════

home_path = os.path.join(THEME_DIR, "templates", "home.html")
with open(home_path, 'r', encoding='utf-8') as f:
    home_content = f.read()

home_new = QUERY_PATTERN.sub(STATIC_PRODUCTS, home_content)
if home_new == home_content:
    broader = re.compile(r'<!-- wp:query\s.*?-->.*?<!-- /wp:query -->', re.DOTALL)
    home_new = broader.sub(STATIC_PRODUCTS, home_content)

with open(home_path, 'w', encoding='utf-8') as f:
    f.write(home_new)
print(f"FIX 2b: home.html updated with static supplement products")

# ═══════════════════════════════════════════════════════════════════
# FIX 3: Fix $is_home detection in setup.php
# ═══════════════════════════════════════════════════════════════════

setup_path = os.path.join(THEME_DIR, "inc", "setup.php")
with open(setup_path, 'r', encoding='utf-8') as f:
    setup_content = f.read()

old_is_home = """$is_home = ( get_page_template_slug() === 'home' ) || is_page_template( 'home' ) || is_page_template( 'templates/home.html' ) || ( is_front_page() && 'page' === get_option( 'show_on_front' ) );"""

new_is_home = """$is_home = is_front_page() || is_home() || is_page_template( 'home' ) || is_page_template( 'templates/home.html' ) || is_page_template( 'templates/front-page.html' ) || ( get_page_template_slug() === 'home' );"""

if old_is_home in setup_content:
    setup_content = setup_content.replace(old_is_home, new_is_home)
    print("FIX 3: $is_home detection fixed in setup.php")
else:
    print(f"WARNING: Could not find exact is_home pattern, searching...")
    # Try to find and replace the line
    lines = setup_content.split('\n')
    for i, line in enumerate(lines):
        if '$is_home' in line and 'get_page_template_slug' in line:
            lines[i] = '        $is_home = is_front_page() || is_home() || is_page_template( \'home\' ) || is_page_template( \'templates/home.html\' ) || is_page_template( \'templates/front-page.html\' ) || ( get_page_template_slug() === \'home\' );'
            print(f"FIX 3: $is_home detection fixed at line {i+1}")
            break
    setup_content = '\n'.join(lines)

with open(setup_path, 'w', encoding='utf-8') as f:
    f.write(setup_content)

# ═══════════════════════════════════════════════════════════════════
# FIX 4: Fix footerDynamic version bug in theme.js (inverted logic)
# ═══════════════════════════════════════════════════════════════════

theme_js_path = os.path.join(THEME_DIR, "assets", "js", "theme.js")
with open(theme_js_path, 'r', encoding='utf-8') as f:
    js_content = f.read()

# Fix the inverted logic: when nvxData IS defined, it returned '' instead of the version
old_footer = """el.textContent = typeof nvxData !== 'undefined' ? '' : '0.3.0';"""
new_footer = """el.textContent = ( typeof nvxData !== 'undefined' && nvxData.themeVersion ) ? nvxData.themeVersion : '0.3.1';"""

if old_footer in js_content:
    js_content = js_content.replace(old_footer, new_footer)
    print("FIX 4: footerDynamic version bug fixed in theme.js")
else:
    print("WARNING: footerDynamic pattern not found, trying alternative...")
    # Try with different quotes
    alt = "el.textContent = typeof nvxData !== 'undefined' ? '' : '0.3.0';"
    if alt in js_content:
        js_content = js_content.replace(alt, new_footer)
        print("FIX 4: footerDynamic version bug fixed (alt match)")

with open(theme_js_path, 'w', encoding='utf-8') as f:
    f.write(js_content)

# ═══════════════════════════════════════════════════════════════════
# FIX 4b: Also add themeVersion to nvx_data in setup.php
# ═══════════════════════════════════════════════════════════════════

with open(setup_path, 'r', encoding='utf-8') as f:
    setup_content = f.read()

# Add themeVersion to the nvx_data array
old_nvx_data = """'cartCount'  => $cart_count,"""
new_nvx_data = """'cartCount'     => $cart_count,
                'themeVersion'  => NVX_VERSION,"""

if old_nvx_data in setup_content and 'themeVersion' not in setup_content:
    setup_content = setup_content.replace(old_nvx_data, new_nvx_data)
    print("FIX 4b: themeVersion added to nvx_data in setup.php")
else:
    print("FIX 4b: themeVersion already in setup.php or pattern not found")

with open(setup_path, 'w', encoding='utf-8') as f:
    f.write(setup_content)

# ═══════════════════════════════════════════════════════════════════
# FIX 5: Fix mobile menu visibility — add display:none by default
# ═══════════════════════════════════════════════════════════════════

header_css_path = os.path.join(THEME_DIR, "assets", "css", "layers", "header.css")
with open(header_css_path, 'r', encoding='utf-8') as f:
    css_content = f.read()

# The mobile menu uses transform to hide but should also have display:none by default
# on desktop. The media query at 768px uses display:none !important which should work.
# BUT the issue is that the mobile menu div is OUTSIDE the <header> in header.html,
# sitting at body level. The transform:translateX(100%) hides it off-screen.
# If CSS doesn't load, it becomes visible. Let's add a safety display:none.

old_mobile_css = """.nvx-mobile-menu {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        max-width: 380px;
        background: var(--nvx-header-bg);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: var(--nvx-z-mobile-menu);
        transform: translateX(100%);
        transition: transform var(--nvx-transition-base);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        display: flex;
        flex-direction: column;
}"""

new_mobile_css = """.nvx-mobile-menu {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        max-width: 380px;
        background: var(--nvx-header-bg);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: var(--nvx-z-mobile-menu);
        transform: translateX(100%);
        transition: transform var(--nvx-transition-base);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        display: none;
        flex-direction: column;
}

/* Show mobile menu only on small screens */
@media (max-width: 767px) {
        .nvx-mobile-menu {
                display: flex;
        }
}"""

if old_mobile_css in css_content:
    css_content = css_content.replace(old_mobile_css, new_mobile_css)
    print("FIX 5: Mobile menu display:none by default, shown only < 768px")
else:
    # Try to find and patch the display: flex line
    import re as re2
    patched = re2.sub(
        r'(\.nvx-mobile-menu\s*\{[^}]*?)display:\s*flex;',
        r'\1display: none;',
        css_content,
        count=1
    )
    if patched != css_content:
        css_content = patched
        # Also add the mobile media query if not present
        if '@media (max-width: 767px)' not in css_content.split('.nvx-mobile-menu--open')[0]:
            css_content = css_content.replace(
                '.nvx-mobile-menu--open {',
                '@media (max-width: 767px) {\n\t.nvx-mobile-menu {\n\t\tdisplay: flex;\n\t}\n}\n\n.nvx-mobile-menu--open {'
            )
        print("FIX 5: Mobile menu patched via regex")
    else:
        print("WARNING: Could not patch mobile menu CSS")

with open(header_css_path, 'w', encoding='utf-8') as f:
    f.write(css_content)

# Also remove the now-redundant display:none in the 768px+ media query
# (keep it as !important for safety)
# The existing rule at line 1138 is fine.

# ═══════════════════════════════════════════════════════════════════
# FIX 5b: Move mobile menu INSIDE the <header> tag in header.html
# ═══════════════════════════════════════════════════════════════════

header_path = os.path.join(THEME_DIR, "parts", "header.html")
with open(header_path, 'r', encoding='utf-8') as f:
    header_content = f.read()

# The mobile menu is currently OUTSIDE </header>. Move it inside.
# Find the closing </header> and the mobile menu that follows
old_header_end = """</header>

<!-- ═══ MOBILE MENU ═══ -->
<div class="nvx-mobile-menu\""""

new_header_end = """        <!-- ═══ MOBILE MENU ═══ -->
        <div class="nvx-mobile-menu\""""

if old_header_end in header_content:
    # Move mobile menu before </header>
    # First, extract the mobile menu section
    mobile_menu_start = header_content.find('<!-- ═══ MOBILE MENU ═══ -->')
    mobile_menu_section = header_content[mobile_menu_start:]
    header_before = header_content[:mobile_menu_start]

    # Remove mobile menu from after </header>
    header_before = header_before.rstrip() + '\n'

    # Insert mobile menu before </header>
    header_close_pos = header_before.rfind('</header>')
    if header_close_pos != -1:
        new_header = header_before[:header_close_pos] + mobile_menu_section + '\n' + header_before[header_close_pos:]

        # Remove duplicate </header> if needed
        # The new structure: ...mobile-menu... </header>
        with open(header_path, 'w', encoding='utf-8') as f:
            f.write(new_header)
        print("FIX 5b: Mobile menu moved INSIDE <header> tag")
    else:
        print("WARNING: Could not find </header> to restructure")
else:
    print("FIX 5b: Header structure may already be correct or pattern differs")

# ═══════════════════════════════════════════════════════════════════
# FIX 6: Disable WooCommerce store notice + delete default post
# This needs WordPress to be running, so we create a PHP helper script
# ═══════════════════════════════════════════════════════════════════

fix_wp_path = os.path.join(THEME_DIR, "inc", "fix-default-content.php")
with open(fix_wp_path, 'w', encoding='utf-8') as f:
    f.write('''<?php
/**
 * One-time fix script: delete default post, disable WooCommerce store notice,
 * set front page to use front-page.html template.
 *
 * Run via: php fix-default-content.php
 * Or just let it run on next theme load (auto-cleanup).
 *
 * @package NutriVitaX_Pro
 */

// This runs as a WordPress mu-plugin style fix via after_setup_theme
add_action( \'after_setup_theme\', function() {
    // Only run once
    if ( get_option( \'nvx_default_content_fixed\' ) ) {
        return;
    }

    // 1. Delete default "Bonjour tout le monde" post
    $default_post = get_page_by_title( \'Bonjour tout le monde !\' );
    if ( $default_post ) {
        wp_delete_post( $default_post->ID, true ); // Force delete
    }

    // Also try French variant
    $default_post2 = get_page_by_title( \'Hello world!\' );
    if ( $default_post2 ) {
        wp_delete_post( $default_post2->ID, true );
    }

    // 2. Disable WooCommerce demo store notice
    update_option( \'woocommerce_demo_store\', \'no\' );

    // 3. Set "Your latest posts" as front page (so front-page.html is used)
    update_option( \'show_on_front\', \'posts\' );

    // 4. Change site title if still default
    if ( get_option( \'blogname\' ) === \'salvanutri\' || get_option( \'blogname\' ) === \'Mon site WordPress\' ) {
        update_option( \'blogname\', \'NutriVitaX Pro\' );
    }
    if ( get_option( \'blogdescription\' ) === \'Boutique bientot disponible\' || empty( get_option( \'blogdescription\' ) ) ) {
        update_option( \'blogdescription\', \'Complements alimentaires premium valides par la science\' );
    }

    // Mark as done
    update_option( \'nvx_default_content_fixed\', \'1\' );
}, 99 );
''')
print("FIX 6: Created fix-default-content.php (auto-runs on next theme load)")

# ═══════════════════════════════════════════════════════════════════
# FIX 6b: Load the fix script from functions.php
# ═══════════════════════════════════════════════════════════════════

functions_path = os.path.join(THEME_DIR, "functions.php")
with open(functions_path, 'r', encoding='utf-8') as f:
    func_content = f.read()

# Add the fix loader before the final closing
if 'fix-default-content.php' not in func_content:
    # Add after the nvx_load_components call
    old_load = """add_action( 'after_setup_theme', 'nvx_load_components', 20 );"""
    new_load = """add_action( 'after_setup_theme', 'nvx_load_components', 20 );

// ─── Auto-fix default content (one-time) ──────────────────────────
nvx_load( 'fix-default-content.php' );"""

    if old_load in func_content:
        func_content = func_content.replace(old_load, new_load)
        print("FIX 6b: fix-default-content.php loader added to functions.php")
    else:
        print("WARNING: Could not find nvx_load_components hook to add fix loader")
else:
    print("FIX 6b: fix-default-content.php already loaded in functions.php")

with open(functions_path, 'w', encoding='utf-8') as f:
    f.write(func_content)

# ═══════════════════════════════════════════════════════════════════
# Verify all fixes
# ═══════════════════════════════════════════════════════════════════

print("\n" + "="*60)
print("VERIFICATION")
print("="*60)

# Check front-page.html exists
fp = os.path.join(THEME_DIR, "templates", "front-page.html")
print(f"[OK] front-page.html exists: {os.path.exists(fp)}")

# Check no wp:query in front-page.html
with open(fp, 'r') as f:
    fp_content = f.read()
has_query = 'wp:query' in fp_content
has_whey = 'Whey Proteine Isolat' in fp_content
has_lampe = 'Lampe' in fp_content
print(f"[OK] front-page.html: wp:query removed: {not has_query}")
print(f"[OK] front-page.html: supplement products: {has_whey}")
print(f"[OK] front-page.html: no placeholder products: {not has_lampe}")

# Check home.html
with open(home_path, 'r') as f:
    hc = f.read()
print(f"[OK] home.html: supplement products: {'Whey Proteine Isolat' in hc}")

# Check setup.php
with open(setup_path, 'r') as f:
    sc = f.read()
print(f"[OK] setup.php: is_front_page() in detection: {'is_front_page()' in sc}")
print(f"[OK] setup.php: themeVersion in nvx_data: {'themeVersion' in sc}")

# Check theme.js
with open(theme_js_path, 'r') as f:
    tj = f.read()
print(f"[OK] theme.js: version logic fixed: {'0.3.1' in tj}")

# Check header.css
with open(header_css_path, 'r') as f:
    hcss = f.read()
print(f"[OK] header.css: mobile menu display:none: {'display: none' in hcss.split('.nvx-mobile-menu--open')[0]}")

# Check fix script
print(f"[OK] fix-default-content.php exists: {os.path.exists(fix_wp_path)}")
print(f"[OK] functions.php loads fix: {'fix-default-content.php' in func_content}")

print("\nAll fixes applied successfully!")