<?php
/**
 * Velure Core — Canvas Renderer
 * Intercepts the admin page when &mode=canvas is present
 * and outputs ONLY the requested section HTML with theme CSS.
 * This HTML is loaded inside an iframe by the Visual Builder.
 *
 * @package VelureCore
 * @since 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Valid sections that can be rendered in the canvas.
 * Maps slugs to the nav-key used in admin-pages.php.
 */
function velure_core_canvas_valid_sections() {
        return array(
                'hero'         => 'hero',
                'features'     => 'features',
                'categories'   => 'categories',
                'products'     => 'products',
                'split_banner' => 'banner',
                'marquee'      => 'marquee',
                'testimonials' => 'testimonials',
                'blog'         => 'blog',
                'instagram'    => 'instagram',
        );
}

/**
 * Check if the current request is a canvas render request.
 * Returns the section slug if valid, false otherwise.
 */
function velure_core_is_canvas_request() {
        if ( ! is_admin() ) {
                return false;
        }
        if ( ! isset( $_GET['mode'] ) || 'canvas' !== $_GET['mode'] ) {
                return false;
        }
        if ( ! current_user_can( 'edit_theme_options' ) ) {
                return false;
        }
        $section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '';
        $valid   = velure_core_canvas_valid_sections();
        if ( ! isset( $valid[ $section ] ) ) {
                return false;
        }
        return $section;
}

/**
 * Render the canvas output and die.
 * IMPORTANT: Since v3.4.2, this is called from admin_init (via Velure_Core::intercept_canvas_request),
 * which means WordPress admin-header.php has NOT been output yet. The HTML we produce here
 * is the ONLY content sent to the browser — a pure, clean HTML document.
 *
 * @param string $section  The validated section slug.
 */
function velure_core_render_canvas( $section ) {

        /* ═══ NOW CALLED FROM admin_init — NO WordPress admin chrome exists yet ═══ */
        define( 'IFRAME_REQUEST', true );
        show_admin_bar( false );

        /* ── Theme CSS discovery ── */
        $theme     = wp_get_theme();
        $theme_uri = '';
        $css_files = array();

        if ( $theme->exists() ) {
                $theme_uri = $theme->get_template_directory_uri();
                /* Real theme stylesheets — base.css + components.css (where section styles live) */
                $css_files[] = $theme_uri . '/assets/css/base.css';
                $css_files[] = $theme_uri . '/assets/css/components.css';
                /* style.css is just the WP header (14 lines), skip it to avoid useless request */
        }

        /* Velure3 URI for fallback image paths */
        $velure3_uri = '';
        if ( defined( 'VELURE3_URI' ) ) {
                $velure3_uri = VELURE3_URI;
        } elseif ( $theme->get_template() === 'velure3' || $theme->get_stylesheet() === 'velure3' ) {
                $velure3_uri = $theme->get_template_directory_uri();
        }

        /* Section label */
        $sections = velure_core_get_editable_sections();
        $label    = isset( $sections[ $section ] ) ? $sections[ $section ]['label'] : $section;

        /* ── Pure HTML headers — NO WordPress admin assets will be injected ── */
        header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
        header( 'X-Robots-Tag: noindex, nofollow' );
        @ini_set( 'display_errors', 0 );

        /* Google Fonts URL (same as theme) */
        $google_fonts_url = 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap';

        ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo esc_html( $label ); ?> — Canvas</title>

        <?php foreach ( $css_files as $css_url ) : ?>
        <link rel="stylesheet" href="<?php echo esc_url( $css_url ); ?>?v=<?php echo esc_attr( $theme->get( 'Version' ) ); ?>" />
        <?php endforeach; ?>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?php echo esc_url( $google_fonts_url ); ?>" rel="stylesheet">

        <style>
                /* ── Canvas base reset ── */
                *, *::before, *::after { box-sizing: border-box; }
                html, body {
                        margin: 0;
                        padding: 0;
                        background: #FAFAF8;
                        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                        -webkit-font-smoothing: antialiased;
                        -moz-osx-font-smoothing: grayscale;
                }
                /* Safety: hide any WP admin remnants (shouldn't exist now, but belt & suspenders) */
                #wpadminbar, .admin-bar, .update-nag, .notice { display: none !important; }
                body.admin-bar { margin-top: 0 !important; }

                /* Canvas wrapper */
                .vc-canvas-body {
                        width: 100%;
                        min-height: 100vh;
                        overflow-x: hidden;
                }

                /* Section badge */
                .vc-canvas-badge {
                        position: fixed;
                        top: 10px;
                        left: 10px;
                        background: rgba(26, 26, 46, 0.85);
                        color: #C8A97E;
                        font-size: 11px;
                        font-weight: 600;
                        padding: 5px 12px;
                        border-radius: 20px;
                        z-index: 10000;
                        font-family: 'Inter', sans-serif;
                        letter-spacing: 0.5px;
                        text-transform: uppercase;
                        pointer-events: none;
                        backdrop-filter: blur(8px);
                }

                /* Edit button from frontend-edit.php should be hidden in canvas */
                .vc-fe-edit-wrap { display: none !important; }

                /* Widget hover outline (injected by canvas-bridge.js) */
                [data-vc-widget] { cursor: pointer; transition: outline 0.15s ease; }
                [data-vc-widget].vc-widget-selected {
                        outline: 2px dashed #4A90D9 !important;
                        outline-offset: 2px;
                }

                <?php if ( $section === 'hero' ) : ?>
                /* ═══════════════════════════════════════════════════════════════
                   HERO SLIDER — CANVAS EDIT MODE
                   Force the first slide to display statically.
                   Disable transitions, auto-advance, and progress bar.
                   ═══════════════════════════════════════════════════════════════ */
                .v-hero-slide {
                        opacity: 1 !important;
                        transition: none !important;
                        z-index: 1 !important;
                }
                .v-hero-slide--active {
                        opacity: 1 !important;
                        z-index: 2 !important;
                }
                .v-hero-slide-bg {
                        background-size: cover !important;
                        background-position: center !important;
                        background-repeat: no-repeat !important;
                }
                /* Hide non-active slides so only the first is visible */
                .v-hero-slide:not(.v-hero-slide--active) {
                        display: none !important;
                }
                /* Disable progress bar animation */
                .v-hero-progress { display: none !important; }
                /* Disable dot click navigation in canvas */
                .v-hero-dot { pointer-events: none !important; }
                .v-hero-slider {
                        animation: none !important;
                }
                <?php endif; ?>
        </style>
</head>
<body class="vc-canvas-body" data-vc-section="<?php echo esc_attr( $section ); ?>">
        <div class="vc-canvas-badge"><?php echo esc_html( $label ); ?></div>

        <?php
        /* ═══════════════════════════════════════════════════════════
           Render the requested section — reusing the same PHP logic
           as front-page.php but for a single section only.
           ═══════════════════════════════════════════════════════════ */
        velure_core_canvas_render_section( $section );
        ?>

        <!-- Theme JS (hero slider, marquee animations) -->
        <?php if ( $theme_uri ) : ?>
        <script src="<?php echo esc_url( $theme_uri . '/assets/js/theme.js' ); ?>?v=<?php echo esc_attr( $theme->get( 'Version' ) ); ?>"></script>
        <?php endif; ?>

        <!-- Canvas Bridge: widget identification + postMessage -->
        <script src="<?php echo esc_url( VELURE_CORE_URI . 'assets/js/canvas-bridge.js' ); ?>?v=<?php echo VELURE_CORE_VERSION; ?>"></script>
</body>
</html>
        <?php
        /* Stop all further WordPress execution */
        exit;
}

/**
 * Render a single section's HTML inside the canvas.
 * This mirrors the switch-case from front-page.php
 * but only renders the requested section.
 *
 * @param string $section  The section slug.
 */
function velure_core_canvas_render_section( $section ) {

        /* Ensure helper functions are available */
        if ( ! function_exists( 'velure_core_get_hero_data' ) ) {
                echo '<p style="padding:40px;text-align:center;color:#888;">Velure Core data helpers not loaded.</p>';
                return;
        }

        switch ( $section ) {

                /* ─── HERO SLIDER ─── */
                case 'hero' :
                        $hero  = function_exists( 'velure3_get_hero_data' ) ? velure3_get_hero_data() : velure_core_get_hero_data();
                        $h_cls = 'v-hero-slider';
                        if ( 'tall' === $hero['height'] )    $h_cls .= ' v-hero-tall';
                        if ( 'compact' === $hero['height'] ) $h_cls .= ' v-hero-compact';
                        ?>
                        <div class="<?php echo esc_attr( $h_cls ); ?>">
                          <div class="v-hero-main">
                                <?php foreach ( $hero['slides'] as $i => $slide ) : ?>
                                <div class="v-hero-slide<?php echo 0 === $i ? ' v-hero-slide--active' : ''; ?>">
                                  <div class="v-hero-slide-bg" style="background-image:url('<?php echo esc_url( $slide['image'] ); ?>');"></div>
                                  <div class="v-hero-slide-overlay"></div>
                                  <div class="v-hero-slide-content">
                                        <?php if ( $slide['eyebrow'] ) : ?>
                                          <span class="velure-eyebrow" data-vc-field="hero_slides[<?php echo $i; ?>][eyebrow]"><?php echo esc_html( $slide['eyebrow'] ); ?></span>
                                        <?php endif; ?>
                                        <h2 data-vc-field="hero_slides[<?php echo $i; ?>][title]"><?php echo wp_kses_post( $slide['title'] ); ?></h2>
                                        <?php if ( $slide['subtitle'] ) : ?>
                                          <p class="v-hero-subtitle" data-vc-field="hero_slides[<?php echo $i; ?>][subtitle]"><?php echo esc_html( $slide['subtitle'] ); ?></p>
                                        <?php endif; ?>
                                        <?php if ( $slide['cta_text'] ) : ?>
                                          <a href="#" class="velure-btn velure-btn-<?php echo esc_attr( $slide['cta_style'] ); ?>" data-vc-field="hero_slides[<?php echo $i; ?>][cta_text]" onclick="return false;">
                                                <?php echo esc_html( $slide['cta_text'] ); ?>
                                          </a>
                                        <?php endif; ?>
                                  </div>
                                </div>
                                <?php endforeach; ?>

                                <?php if ( count( $hero['slides'] ) > 1 ) : ?>
                                <div class="v-hero-dots">
                                  <?php foreach ( $hero['slides'] as $i => $_s ) : ?>
                                  <button class="v-hero-dot<?php echo 0 === $i ? ' v-hero-dot--active' : ''; ?>" data-goto="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
                                  <?php endforeach; ?>
                                </div>
                                <div class="v-hero-progress"><div class="v-hero-progress-bar"></div></div>
                                <?php endif; ?>
                          </div>

                          <?php if ( $hero['show_side'] && ! empty( $hero['side_blocks'] ) ) : ?>
                          <div class="v-hero-side">
                                <?php
                                $bs = $hero['side_blocks']['bestseller'] ?? null;
                                if ( $bs ) :
                                ?>
                                <a href="#" class="v-hero-side-card" onclick="return false;">
                                  <div class="v-hero-side-card-img" style="background-image:url('<?php echo esc_url( $bs['image'] ); ?>');"></div>
                                  <div class="v-hero-side-card-body">
                                        <span class="velure-eyebrow" style="margin-bottom:0.25rem;font-size:0.6rem;" data-vc-field="hs_bestseller_label"><?php echo esc_html( $bs['label'] ); ?></span>
                                        <div class="v-hero-side-card-title" data-vc-field="hs_bestseller_title"><?php echo esc_html( $bs['title'] ); ?></div>
                                        <?php if ( ! empty( $bs['price'] ) ) : ?>
                                        <span class="v-hero-side-card-price" data-vc-field="hs_bestseller_price"><?php echo esc_html( $bs['price'] ); ?></span>
                                        <?php endif; ?>
                                        <span class="v-hero-side-card-cta" data-vc-field="hs_bestseller_cta"><?php echo esc_html( $bs['cta_text'] ?? 'VOIR' ); ?></span>
                                  </div>
                                </a>
                                <?php endif; ?>

                                <?php
                                $cat = $hero['side_blocks']['category'] ?? null;
                                if ( $cat ) :
                                ?>
                                <a href="#" class="v-hero-side-card" onclick="return false;">
                                  <div class="v-hero-side-card-img" style="background-image:url('<?php echo esc_url( $cat['image'] ); ?>');"></div>
                                  <div class="v-hero-side-card-body">
                                        <span class="velure-eyebrow" style="margin-bottom:0.25rem;font-size:0.6rem;" data-vc-field="hs_category_label"><?php echo esc_html( $cat['label'] ); ?></span>
                                        <div class="v-hero-side-card-title" data-vc-field="hs_category_title"><?php echo esc_html( $cat['title'] ); ?></div>
                                        <span class="v-hero-side-card-cta" data-vc-field="hs_category_cta_link">DECOUVRIR</span>
                                  </div>
                                </a>
                                <?php endif; ?>
                          </div>
                          <?php endif; ?>
                        </div>
                        <?php
                        break;

                /* ─── FEATURES / TRUST BAR ─── */
                case 'features' :
                        $features = function_exists( 'velure3_get_features' ) ? velure3_get_features() : velure_core_get_features();
                        $feat_bg  = function_exists('velure_core_bg_class') ? velure_core_bg_class( velure_core_opt( 'feat_bg_style', 'soft' ) ) : 'velure-section-soft';
                        ?>
                        <div class="velure-features <?php echo esc_attr( $feat_bg ); ?>">
                          <div class="velure-container">
                                <div class="velure-features-grid">
                                  <?php foreach ( $features as $fi => $feat ) : ?>
                                  <div class="velure-feature-item">
                                        <div class="velure-feature-icon">
                                          <?php echo wp_kses( $feat['icon'], velure_core_svg_allowed() ); ?>
                                        </div>
                                        <span class="velure-feature-title" data-vc-field="trust_features[<?php echo $fi; ?>][title]"><?php echo esc_html( $feat['title'] ); ?></span>
                                        <?php if ( $feat['desc'] ) : ?>
                                        <span class="velure-feature-desc" data-vc-field="trust_features[<?php echo $fi; ?>][description]"><?php echo esc_html( $feat['desc'] ); ?></span>
                                        <?php endif; ?>
                                  </div>
                                  <?php endforeach; ?>
                                </div>
                          </div>
                        </div>
                        <?php
                        break;

                /* ─── CATEGORIES ─── */
                case 'categories' :
                        $categories  = function_exists( 'velure3_get_product_categories' ) ? velure3_get_product_categories() : velure_core_get_product_categories();
                        $cat_bg      = function_exists('velure3_bg_class') ? velure3_bg_class( velure3_opt( 'cat_bg_style', 'base' ) ) : 'velure-section-base';
                        $cat_eyebrow = velure3_opt( 'cat_eyebrow', 'Collections' );
                        $cat_title   = velure3_opt( 'section_title_categories', 'Explorer par Univers' );
                        $cat_desc    = velure3_opt( 'cat_description', '' );
                        $cat_cta_txt = velure3_opt( 'cat_cta_text', 'Toutes les categories' );
                        $cat_cta_url = velure3_opt( 'cat_cta_link', '/boutique/' );
                        ?>
                        <section class="velure-section <?php echo esc_attr( $cat_bg ); ?>">
                          <div class="velure-container">
                                <div class="velure-section-heading">
                                  <?php if ( $cat_eyebrow ) : ?>
                                        <span class="velure-eyebrow" data-vc-field="cat_eyebrow"><?php echo esc_html( $cat_eyebrow ); ?></span>
                                  <?php endif; ?>
                                  <h2 data-vc-field="section_title_categories"><?php echo esc_html( $cat_title ); ?></h2>
                                  <?php if ( $cat_desc ) : ?>
                                        <p data-vc-field="cat_description"><?php echo esc_html( $cat_desc ); ?></p>
                                  <?php endif; ?>
                                </div>
                                <div class="velure-carousel">
                                  <button class="velure-carousel-arrow velure-carousel-arrow--prev" aria-label="Precedent">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                                  </button>
                                  <div class="velure-carousel-track">
                                        <?php foreach ( $categories as $cat ) : ?>
                                        <a href="#" class="velure-category-card" onclick="return false;">
                                          <div class="velure-category-card-img">
                                                <img src="<?php echo esc_url( $cat['image'] ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>" loading="lazy">
                                          </div>
                                          <div class="velure-category-card-name"><?php echo esc_html( $cat['name'] ); ?></div>
                                          <?php if ( $cat['count'] > 0 ) : ?>
                                          <div class="velure-category-card-count"><?php echo $cat['count']; ?> articles</div>
                                          <?php endif; ?>
                                        </a>
                                        <?php endforeach; ?>
                                  </div>
                                  <button class="velure-carousel-arrow velure-carousel-arrow--next" aria-label="Suivant">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                  </button>
                                </div>
                                <?php if ( $cat_cta_txt ) : ?>
                                <div style="text-align:center;margin-top:2.5rem;">
                                  <a href="#" class="velure-btn velure-btn-outline" data-vc-field="cat_cta_text" onclick="return false;"><?php echo esc_html( $cat_cta_txt ); ?></a>
                                </div>
                                <?php endif; ?>
                          </div>
                        </section>
                        <?php
                        break;

                /* ─── PRODUCTS ─── */
                case 'products' :
                        $products     = function_exists( 'velure3_get_featured_products' ) ? velure3_get_featured_products() : velure_core_get_featured_products();
                        $prod_cols    = velure3_opt( 'prod_columns', '4' );
                        $prod_eyebrow = velure3_opt( 'prod_eyebrow', 'Selection' );
                        $prod_title   = velure3_opt( 'section_title_products', 'Pieces Vedettes' );
                        $prod_desc    = velure3_opt( 'prod_description', '' );
                        $prod_cta_txt = velure3_opt( 'prod_cta_text', 'Voir toute la boutique' );
                        $prod_cta_url = velure3_opt( 'prod_cta_link', '/boutique/' );
                        $prod_bg      = function_exists('velure3_bg_class') ? velure3_bg_class( velure3_opt( 'prod_bg_style', 'soft' ) ) : 'velure-section-soft';
                        $grid_cls     = 'velure-grid-' . $prod_cols;
                        ?>
                        <section class="velure-section <?php echo esc_attr( $prod_bg ); ?>">
                          <div class="velure-container">
                                <div class="velure-section-heading">
                                  <?php if ( $prod_eyebrow ) : ?>
                                        <span class="velure-eyebrow" data-vc-field="prod_eyebrow"><?php echo esc_html( $prod_eyebrow ); ?></span>
                                  <?php endif; ?>
                                  <h2 data-vc-field="section_title_products"><?php echo esc_html( $prod_title ); ?></h2>
                                  <?php if ( $prod_desc ) : ?>
                                        <p data-vc-field="prod_description"><?php echo esc_html( $prod_desc ); ?></p>
                                  <?php endif; ?>
                                </div>
                                <div class="velure-grid <?php echo esc_attr( $grid_cls ); ?>">
                                  <?php foreach ( $products as $prod ) : ?>
                                  <a href="#" class="velure-product-card" onclick="return false;">
                                        <div class="velure-product-card-img">
                                          <?php if ( ! empty( $prod['image'] ) ) : ?>
                                          <img src="<?php echo esc_url( $prod['image'] ); ?>" alt="<?php echo esc_attr( $prod['name'] ); ?>" loading="lazy">
                                          <?php endif; ?>
                                          <?php echo ! empty( $prod['badge'] ) ? $prod['badge'] : ''; ?>
                                        </div>
                                        <div class="velure-product-card-name"><?php echo esc_html( $prod['name'] ); ?></div>
                                        <div class="velure-product-card-price">
                                          <?php echo isset( $prod['price_html'] ) ? $prod['price_html'] : esc_html( $prod['price'] ?? '' ); ?>
                                        </div>
                                  </a>
                                  <?php endforeach; ?>
                                </div>
                                <?php if ( $prod_cta_txt ) : ?>
                                <div style="text-align:center;margin-top:2.5rem;">
                                  <a href="#" class="velure-btn velure-btn-outline" data-vc-field="prod_cta_text" onclick="return false;"><?php echo esc_html( $prod_cta_txt ); ?></a>
                                </div>
                                <?php endif; ?>
                          </div>
                        </section>
                        <?php
                        break;

                /* ─── SPLIT BANNER ─── */
                case 'split_banner' :
                        $banner = function_exists( 'velure3_get_split_banner' ) ? velure3_get_split_banner() : velure_core_get_split_banner();
                        ?>
                        <section class="velure-split-banner">
                          <?php foreach ( array( 'left', 'right' ) as $side ) :
                                $b     = $banner[ $side ];
                                $style = $b['style'] === 'dark' ? 'velure-split-side--dark' : 'velure-split-side--light';
                          ?>
                          <div class="velure-split-side <?php echo esc_attr( $style ); ?>">
                                <div class="velure-split-side-bg" style="background-image:url('<?php echo esc_url( $b['image'] ); ?>');"></div>
                                <div class="velure-split-side-content">
                                  <?php if ( $b['eyebrow'] ) : ?>
                                        <span class="velure-eyebrow" data-vc-field="sb_<?php echo $side; ?>_eyebrow"><?php echo esc_html( $b['eyebrow'] ); ?></span>
                                  <?php endif; ?>
                                  <h2 data-vc-field="sb_<?php echo $side; ?>_title"><?php echo esc_html( $b['title'] ); ?></h2>
                                  <?php if ( $b['desc'] ) : ?>
                                        <p data-vc-field="sb_<?php echo $side; ?>_desc"><?php echo esc_html( $b['desc'] ); ?></p>
                                  <?php endif; ?>
                                  <?php if ( $b['cta_text'] ) : ?>
                                        <a href="#" class="velure-btn velure-btn-<?php echo esc_attr( $b['cta_style'] ); ?>" data-vc-field="sb_<?php echo $side; ?>_cta_text" onclick="return false;">
                                          <?php echo esc_html( $b['cta_text'] ); ?>
                                        </a>
                                  <?php endif; ?>
                                </div>
                          </div>
                          <?php endforeach; ?>
                        </section>
                        <?php
                        break;

                /* ─── MARQUEE ─── */
                case 'marquee' :
                        $brands      = function_exists( 'velure3_get_brands' ) ? velure3_get_brands() : velure_core_get_brands();
                        $speed       = absint( velure3_opt( 'marquee_speed', 25 ) );
                        $marquee_bg  = velure3_opt( 'marquee_bg', 'base' );
                        $marquee_dir = velure3_opt( 'marquee_direction', 'left' );
                        $marquee_cls = function_exists('velure3_bg_class') ? velure3_bg_class( $marquee_bg ) : '';
                        $marquee_items = '';
                        foreach ( $brands as $name ) {
                                $marquee_items .= '<span class="velure-marquee-item">' . esc_html( $name ) . '</span>';
                        }
                        $marquee_html = $marquee_items . $marquee_items;
                        $dir_style = ( 'right' === $marquee_dir ) ? 'animation-direction:reverse;' : '';
                        ?>
                        <div class="velure-marquee<?php echo $marquee_cls ? ' ' . esc_attr( $marquee_cls ) : ''; ?>">
                          <div class="velure-marquee-track" style="animation-duration:<?php echo $speed; ?>s;<?php echo $dir_style; ?>">
                                <?php echo $marquee_html; ?>
                          </div>
                        </div>
                        <?php
                        break;

                /* ─── TESTIMONIALS ─── */
                case 'testimonials' :
                        $testi_count  = absint( velure3_opt( 'testimonials_count', 3 ) );
                        $testi_cols   = velure3_opt( 'testi_columns', '3' );
                        $testimonials = function_exists( 'velure3_get_testimonials' ) ? velure3_get_testimonials( $testi_count ) : velure_core_get_testimonials( $testi_count );
                        $testi_eyebrow = velure3_opt( 'testi_eyebrow', 'Avis Clients' );
                        $testi_title   = velure3_opt( 'section_title_testimonials', 'Ce Que Disent Nos Clients' );
                        $testi_desc    = velure3_opt( 'testi_description', '' );
                        $testi_bg      = function_exists('velure3_bg_class') ? velure3_bg_class( velure3_opt( 'testi_bg_style', 'base' ) ) : 'velure-section-base';
                        $testi_grid    = 'velure-grid-' . $testi_cols;
                        ?>
                        <section class="velure-section <?php echo esc_attr( $testi_bg ); ?>">
                          <div class="velure-container">
                                <div class="velure-section-heading">
                                  <?php if ( $testi_eyebrow ) : ?>
                                        <span class="velure-eyebrow" data-vc-field="testi_eyebrow"><?php echo esc_html( $testi_eyebrow ); ?></span>
                                  <?php endif; ?>
                                  <h2 data-vc-field="section_title_testimonials"><?php echo esc_html( $testi_title ); ?></h2>
                                  <?php if ( $testi_desc ) : ?>
                                        <p data-vc-field="testi_description"><?php echo esc_html( $testi_desc ); ?></p>
                                  <?php endif; ?>
                                </div>
                                <div class="velure-testimonials-grid <?php echo esc_attr( $testi_grid ); ?>">
                                  <?php foreach ( $testimonials as $t ) : ?>
                                  <div class="velure-testimonial-card">
                                        <div class="velure-testimonial-stars">
                                          <?php for ( $s = 0; $s < $t['stars']; $s++ ) echo velure3_star_svg(); ?>
                                        </div>
                                        <p class="velure-testimonial-text">"<?php echo esc_html( $t['text'] ); ?>"</p>
                                        <div class="velure-testimonial-author"><?php echo esc_html( $t['author'] ); ?></div>
                                        <?php if ( $t['role'] ) : ?>
                                        <div class="velure-testimonial-role"><?php echo esc_html( $t['role'] ); ?></div>
                                        <?php endif; ?>
                                  </div>
                                  <?php endforeach; ?>
                                </div>
                          </div>
                        </section>
                        <?php
                        break;

                /* ─── BLOG ─── */
                case 'blog' :
                        $blog_count   = absint( velure3_opt( 'blog_posts_count', 3 ) );
                        $blog_cols    = velure3_opt( 'blog_columns', '3' );
                        $blog_posts   = function_exists( 'velure3_get_blog_posts' ) ? velure3_get_blog_posts( $blog_count ) : array();
                        $blog_eyebrow = velure3_opt( 'blog_eyebrow', 'Actualites' );
                        $blog_title   = velure3_opt( 'section_title_blog', 'Le Journal' );
                        $blog_desc    = velure3_opt( 'blog_description', '' );
                        $blog_cta_txt = velure3_opt( 'blog_cta_text', 'Voir tous les articles' );
                        $blog_cta_url = velure3_opt( 'blog_cta_link', '/blog/' );
                        $blog_bg      = function_exists('velure3_bg_class') ? velure3_bg_class( velure3_opt( 'blog_bg_style', 'muted' ) ) : 'velure-section-muted';
                        $blog_grid    = 'velure-grid-' . $blog_cols;
                        ?>
                        <section class="velure-section <?php echo esc_attr( $blog_bg ); ?>">
                          <div class="velure-container">
                                <div class="velure-section-heading">
                                  <?php if ( $blog_eyebrow ) : ?>
                                        <span class="velure-eyebrow" data-vc-field="blog_eyebrow"><?php echo esc_html( $blog_eyebrow ); ?></span>
                                  <?php endif; ?>
                                  <h2 data-vc-field="section_title_blog"><?php echo esc_html( $blog_title ); ?></h2>
                                  <?php if ( $blog_desc ) : ?>
                                        <p data-vc-field="blog_description"><?php echo esc_html( $blog_desc ); ?></p>
                                  <?php endif; ?>
                                </div>
                                <div class="velure-grid <?php echo esc_attr( $blog_grid ); ?>">
                                  <?php foreach ( $blog_posts as $post ) : ?>
                                  <a href="#" class="velure-blog-card" onclick="return false;">
                                        <?php if ( ! empty( $post['image'] ) ) : ?>
                                        <div class="velure-blog-card-img">
                                          <img src="<?php echo esc_url( $post['image'] ); ?>" alt="<?php echo esc_attr( $post['title'] ); ?>" loading="lazy">
                                        </div>
                                        <?php endif; ?>
                                        <?php if ( $post['category'] ) : ?>
                                        <div class="velure-blog-card-meta"><?php echo esc_html( $post['category'] ); ?></div>
                                        <?php endif; ?>
                                        <h3 class="velure-blog-card-title"><?php echo esc_html( $post['title'] ); ?></h3>
                                        <?php if ( $post['excerpt'] ) : ?>
                                        <p class="velure-blog-card-excerpt"><?php echo esc_html( $post['excerpt'] ); ?></p>
                                        <?php endif; ?>
                                  </a>
                                  <?php endforeach; ?>
                                </div>
                                <?php if ( $blog_cta_txt ) : ?>
                                <div style="text-align:center;margin-top:2.5rem;">
                                  <a href="#" class="velure-btn velure-btn-outline" data-vc-field="blog_cta_text" onclick="return false;"><?php echo esc_html( $blog_cta_txt ); ?></a>
                                </div>
                                <?php endif; ?>
                          </div>
                        </section>
                        <?php
                        break;

                /* ─── INSTAGRAM ─── */
                case 'instagram' :
                        $ig_items  = function_exists( 'velure3_get_instagram_items' ) ? velure3_get_instagram_items() : array();
                        $ig_handle = velure3_opt( 'instagram_handle', '@velure.paris' );
                        $ig_eyebrow = velure3_opt( 'ig_eyebrow', 'Suivez-nous' );
                        $ig_cols    = velure3_opt( 'ig_columns', '6' );
                        $ig_gap     = velure3_opt( 'ig_gap', 'small' );
                        $gap_map    = array( 'none' => '0', 'small' => '4px', 'medium' => '8px', 'large' => '16px' );
                        $ig_gap_css = isset( $gap_map[ $ig_gap ] ) ? $gap_map[ $ig_gap ] : '4px';
                        ?>
                        <section class="velure-section">
                          <div class="velure-container">
                                <div class="velure-section-heading">
                                  <?php if ( $ig_eyebrow ) : ?>
                                        <span class="velure-eyebrow" data-vc-field="ig_eyebrow"><?php echo esc_html( $ig_eyebrow ); ?></span>
                                  <?php endif; ?>
                                  <h2 data-vc-field="instagram_handle"><?php echo esc_html( $ig_handle ); ?></h2>
                                </div>
                          </div>
                          <div class="velure-instagram-grid velure-grid-<?php echo esc_attr( $ig_cols ); ?>" style="gap:<?php echo esc_attr( $ig_gap_css ); ?>;">
                                <?php foreach ( $ig_items as $item ) : ?>
                                <a href="#" class="velure-instagram-item" onclick="return false;">
                                  <?php if ( ! empty( $item['image'] ) ) : ?>
                                  <img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['alt'] ?? 'Instagram' ); ?>" loading="lazy">
                                  <?php endif; ?>
                                </a>
                                <?php endforeach; ?>
                          </div>
                        </section>
                        <?php
                        break;

                default :
                        echo '<p style="padding:60px 40px;text-align:center;color:#888;font-size:15px;">Section "' . esc_html( $section ) . '" non reconnue.</p>';
                        break;
        }
}