<?php
/**
 * Velure3 Theme Functions
 *
 * @package Velure3
 * @version 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

define( 'VELURE3_VERSION', '2.1.0' );
define( 'VELURE3_DIR', get_template_directory() );
define( 'VELURE3_URI', get_template_directory_uri() );

/* ═══════════════════════════════════════════════
   1. THEME SETUP
   ═══════════════════════════════════════════════ */
add_action( 'after_setup_theme', 'velure3_setup' );
function velure3_setup() {
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'align-wide' );
        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
        add_theme_support( 'custom-logo', array(
                'height'      => 60,
                'width'       => 200,
                'flex-height' => true,
                'flex-width'  => true,
        ) );

        register_nav_menus( array(
                'primary'        => __( 'Menu Principal', 'velure3' ),
                'footer_boutique' => __( 'Footer — Boutique', 'velure3' ),
                'footer_info'     => __( 'Footer — Informations', 'velure3' ),
                'footer_help'     => __( 'Footer — Aide', 'velure3' ),
                'social'          => __( 'Liens Sociaux', 'velure3' ),
        ) );

        /* WooCommerce support — only when WooCommerce is active */
        if ( class_exists( 'WooCommerce' ) ) {
                add_theme_support( 'woocommerce' );
                add_theme_support( 'wc-product-gallery-zoom' );
                add_theme_support( 'wc-product-gallery-lightbox' );
                add_theme_support( 'wc-product-gallery-slider' );
        }
}

/* ═══════════════════════════════════════════════
   2. ENQUEUE ASSETS
   ═══════════════════════════════════════════════ */
add_action( 'wp_enqueue_scripts', 'velure3_assets', 20 );
function velure3_assets() {
        /* Dequeue WP block library & FSE global styles — this is a classic PHP theme */
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
        wp_dequeue_style( 'wp-block-library-css' );
        wp_dequeue_style( 'global-styles' );
        wp_dequeue_style( 'classic-theme-styles' );

        wp_enqueue_style( 'velure3-base', VELURE3_URI . '/assets/css/base.css', array(), VELURE3_VERSION );
        wp_enqueue_style( 'velure3-components', VELURE3_URI . '/assets/css/components.css', array( 'velure3-base' ), VELURE3_VERSION );
        wp_enqueue_script( 'velure3-theme', VELURE3_URI . '/assets/js/theme.js', array(), VELURE3_VERSION, true );
}

/* ── Google Fonts ── */
add_action( 'wp_enqueue_scripts', 'velure3_fonts' );
function velure3_fonts() {
        wp_enqueue_style( 'velure3-google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&display=swap', array(), null );
}

/* ═══════════════════════════════════════════════
   3. MEGA MENU WALKER CLASS
   Renders the primary nav with mega-menu dropdowns.
   Menu structure in WP Admin:
     depth 0  → nav link (if has children → mega menu)
     depth 1  → mega column heading (or visual CTA with class "mega-visual")
     depth 2  → links inside column
   ═══════════════════════════════════════════════ */
class Velure3_Mega_Menu_Walker extends Walker_Nav_Menu {

        public function start_lvl( &$output, $depth = 0, $args = null ) {
                if ( 0 === $depth ) {
                        $output .= "\n<div class=\"velure-mega-menu\"><div class=\"velure-container\"><div class=\"velure-mega-grid\">\n";
                } elseif ( 1 === $depth ) {
                        $output .= "\n<ul class=\"velure-mega-links\">\n";
                }
        }

        public function end_lvl( &$output, $depth = 0, $args = null ) {
                if ( 0 === $depth ) {
                        $output .= "\n</div></div></div>\n";
                } elseif ( 1 === $depth ) {
                        $output .= "\n</ul>\n";
                }
        }

        public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
                $classes   = empty( $item->classes ) ? array() : (array) $item->classes;
                $url       = esc_url( $item->url );
                $title     = esc_html( $item->title );
                $has_child = in_array( 'menu-item-has-children', $classes, true );

                if ( 0 === $depth ) {
                        $highlight = ( in_array( 'highlight', $classes, true ) || in_array( 'velure-nav-highlight', $classes, true ) )
                                     ? ' velure-nav-highlight' : '';
                        $output .= "<li class=\"velure-nav-item\">\n";
                        $output .= "<a href=\"{$url}\" class=\"velure-nav-link{$highlight}\">{$title}</a>\n";

                } elseif ( 1 === $depth ) {
                        $is_visual = in_array( 'mega-visual', $classes, true );
                        if ( $is_visual ) {
                                $label = esc_html( $item->attr_title ?: '' );
                                $output .= "<div class=\"velure-mega-col velure-mega-visual\">\n";
                                $output .= "<a href=\"{$url}\" class=\"velure-mega-cta\">\n";
                                if ( $label ) {
                                        $output .= "<span class=\"velure-mega-cta-label\">{$label}</span>\n";
                                }
                                $output .= "<span class=\"velure-mega-cta-title\">{$title}</span>\n";
                                $output .= "</a></div>\n";
                        } else {
                                $output .= "<div class=\"velure-mega-col\">\n";
                                $output .= "<h4 class=\"velure-mega-title\">{$title}</h4>\n";
                        }

                } elseif ( 2 === $depth ) {
                        $output .= "<li><a href=\"{$url}\">{$title}</a></li>\n";
                }
        }

        public function end_el( &$output, $item, $depth = 0, $args = null ) {
                if ( 0 === $depth ) {
                        $output .= "</li>\n";
                } elseif ( 1 === $depth ) {
                        $classes   = empty( $item->classes ) ? array() : (array) $item->classes;
                        $is_visual = in_array( 'mega-visual', $classes, true );
                        if ( ! $is_visual ) {
                                $output .= "</div>\n";
                        }
                }
        }
}

/* ═══════════════════════════════════════════════
   4. MENU FALLBACK FUNCTIONS
   Used when no menu is assigned in Appearance > Menus.
   Outputs the same HTML as the original static header/footer.
   ═══════════════════════════════════════════════ */

/**
 * Fallback for primary desktop menu — full mega menu structure.
 */
function velure3_default_primary_menu( $args ) {
        $h = home_url( '/' );
        $menu_items = array(
                array( 'title' => 'Boutique', 'url' => $h . 'boutique/', 'highlight' => false,
                        'children' => array(
                                array( 'title' => 'Femme', 'url' => $h . 'categorie-femme/', 'visual' => false,
                                        'children' => array(
                                                array( 'title' => 'Vetements',   'url' => $h . 'categorie-femme/vetements/' ),
                                                array( 'title' => 'Robes',       'url' => $h . 'categorie-femme/robes/' ),
                                                array( 'title' => 'Accessoires', 'url' => $h . 'categorie-femme/accessoires/' ),
                                                array( 'title' => 'Chaussures',  'url' => $h . 'categorie-femme/chaussures/' ),
                                        ),
                                ),
                                array( 'title' => 'Homme', 'url' => $h . 'categorie-homme/', 'visual' => false,
                                        'children' => array(
                                                array( 'title' => 'Vetements',   'url' => $h . 'categorie-homme/vetements/' ),
                                                array( 'title' => 'Chemises',    'url' => $h . 'categorie-homme/chemises/' ),
                                                array( 'title' => 'Accessoires', 'url' => $h . 'categorie-homme/accessoires/' ),
                                                array( 'title' => 'Chaussures',  'url' => $h . 'categorie-homme/chaussures/' ),
                                        ),
                                ),
                                array( 'title' => 'Collections', 'url' => '#', 'visual' => false,
                                        'children' => array(
                                                array( 'title' => 'Nouvelle Collection', 'url' => $h . 'new-collection/' ),
                                                array( 'title' => 'Lookbook',           'url' => $h . 'lookbook/' ),
                                                array( 'title' => 'Best-sellers',        'url' => $h . 'categorie/bestsellers/' ),
                                                array( 'title' => 'Promotions',          'url' => $h . 'categorie/promotions/' ),
                                        ),
                                ),
                                array( 'title' => 'Automne/Hiver 2025', 'url' => $h . 'new-collection/', 'visual' => true, 'label' => 'Automne/Hiver 2025', 'cta' => 'Decouvrir la collection' ),
                        ),
                ),
                array( 'title' => 'Nouveautes',  'url' => $h . 'new-collection/',            'highlight' => true ),
                array( 'title' => 'Best-sellers', 'url' => $h . 'categorie/bestsellers/',    'highlight' => false ),
                array( 'title' => 'Lookbook',     'url' => $h . 'lookbook/',                 'highlight' => false ),
                array( 'title' => 'La Marque',    'url' => $h . 'a-propos/',                  'highlight' => false ),
        );

        echo '<ul class="' . esc_attr( $args->menu_class ) . '">';
        foreach ( $menu_items as $item ) {
                $hl = ! empty( $item['highlight'] ) ? ' velure-nav-highlight' : '';
                echo '<li class="velure-nav-item">';
                echo '<a href="' . esc_url( $item['url'] ) . '" class="velure-nav-link' . $hl . '">' . esc_html( $item['title'] ) . '</a>';

                if ( ! empty( $item['children'] ) ) {
                        echo '<div class="velure-mega-menu"><div class="velure-container"><div class="velure-mega-grid">';
                        foreach ( $item['children'] as $child ) {
                                if ( ! empty( $child['visual'] ) ) {
                                        echo '<div class="velure-mega-col velure-mega-visual">';
                                        echo '<a href="' . esc_url( $child['url'] ) . '" class="velure-mega-cta">';
                                        if ( ! empty( $child['label'] ) ) {
                                                echo '<span class="velure-mega-cta-label">' . esc_html( $child['label'] ) . '</span>';
                                        }
                                        echo '<span class="velure-mega-cta-title">' . esc_html( $child['cta'] ?? $child['title'] ) . '</span>';
                                        echo '</a></div>';
                                } else {
                                        echo '<div class="velure-mega-col">';
                                        echo '<h4 class="velure-mega-title">' . esc_html( $child['title'] ) . '</h4>';
                                        if ( ! empty( $child['children'] ) ) {
                                                echo '<ul class="velure-mega-links">';
                                                foreach ( $child['children'] as $gc ) {
                                                        echo '<li><a href="' . esc_url( $gc['url'] ) . '">' . esc_html( $gc['title'] ) . '</a></li>';
                                                }
                                                echo '</ul>';
                                        }
                                        echo '</div>';
                                }
                        }
                        echo '</div></div></div>';
                }
                echo '</li>';
        }
        echo '</ul>';
}

/**
 * Fallback for mobile menu — flat list, depth 0 only.
 */
function velure3_default_mobile_menu( $args ) {
        $h = home_url( '/' );
        $items = array(
                array( 'title' => 'Boutique',     'url' => $h . 'boutique/',             'highlight' => false ),
                array( 'title' => 'Nouveautes',   'url' => $h . 'new-collection/',        'highlight' => true ),
                array( 'title' => 'Best-sellers',  'url' => $h . 'categorie/bestsellers/', 'highlight' => false ),
                array( 'title' => 'Lookbook',      'url' => $h . 'lookbook/',              'highlight' => false ),
                array( 'title' => 'La Marque',     'url' => $h . 'a-propos/',              'highlight' => false ),
                array( 'title' => 'Journal',       'url' => $h . 'blog/',                  'highlight' => false ),
                array( 'title' => 'Contact',       'url' => $h . 'contact/',               'highlight' => false ),
        );

        echo '<ul class="' . esc_attr( $args->menu_class ) . '">';
        foreach ( $items as $item ) {
                $hl = ! empty( $item['highlight'] ) ? ' velure-nav-highlight' : '';
                echo '<li><a href="' . esc_url( $item['url'] ) . '" class="' . $hl . '">' . esc_html( $item['title'] ) . '</a></li>';
        }
        echo '</ul>';
}

/**
 * Helper: render a simple <ul> fallback for footer columns.
 */
function velure3_render_footer_fallback( $args, $links ) {
        echo '<ul class="' . esc_attr( $args->menu_class ) . '">';
        foreach ( $links as $link ) {
                echo '<li><a href="' . esc_url( home_url( $link['url'] ) ) . '">' . esc_html( $link['title'] ) . '</a></li>';
        }
        echo '</ul>';
}

function velure3_default_footer_boutique_menu( $args ) {
        velure3_render_footer_fallback( $args, array(
                array( 'title' => 'Femme',               'url' => '/categorie-femme/' ),
                array( 'title' => 'Homme',               'url' => '/categorie-homme/' ),
                array( 'title' => 'Accessoires',         'url' => '/categorie/accessoires/' ),
                array( 'title' => 'Chaussures',          'url' => '/categorie/chaussures/' ),
                array( 'title' => 'Nouvelle Collection', 'url' => '/new-collection/' ),
                array( 'title' => 'Promotions',          'url' => '/categorie/promotions/' ),
        ) );
}

function velure3_default_footer_info_menu( $args ) {
        velure3_render_footer_fallback( $args, array(
                array( 'title' => 'Notre Histoire',    'url' => '/a-propos/' ),
                array( 'title' => 'Guide des Tailles', 'url' => '/size-guide/' ),
                array( 'title' => 'Contactez-nous',    'url' => '/contact/' ),
                array( 'title' => 'Le Journal',        'url' => '/blog/' ),
                array( 'title' => 'FAQ',               'url' => '/faq/' ),
                array( 'title' => 'Lookbook',          'url' => '/lookbook/' ),
        ) );
}

function velure3_default_footer_help_menu( $args ) {
        velure3_render_footer_fallback( $args, array(
                array( 'title' => 'Livraison',          'url' => '/livraison/' ),
                array( 'title' => 'Retours & Echanges', 'url' => '/retours/' ),
                array( 'title' => 'Paiement Securise',  'url' => '/paiement/' ),
                array( 'title' => 'CGV',                'url' => '/cgv/' ),
                array( 'title' => 'Confidentialite',    'url' => '/confidentialite/' ),
                array( 'title' => 'Mentions Legales',   'url' => '/mentions-legales/' ),
        ) );
}

function velure3_default_social_menu( $args ) {
        $social_links = array(
                'Instagram'  => '#',
                'TikTok'     => '#',
                'Pinterest'  => '#',
                'Facebook'   => '#',
        );
        echo '<div class="velure-footer-social">';
        foreach ( $social_links as $name => $url ) {
                echo '<a href="' . esc_url( $url ) . '" class="velure-social-link" aria-label="' . esc_attr( $name ) . '">';
                echo velure3_social_svg( $name );
                echo '</a>';
        }
        echo '</div>';
}

/**
 * Return SVG icon for a social network name.
 */
function velure3_social_svg( $name ) {
        $svgs = array(
                'Instagram' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>',
                'TikTok'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 0 0-.79-.05A6.34 6.34 0 0 0 3.15 15.2a6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.34-6.34V8.76a8.27 8.27 0 0 0 4.76 1.5v-3.4a4.85 4.85 0 0 1-1-.17z"/></svg>',
                'Pinterest'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.236 2.636 7.855 6.356 9.312-.088-.791-.167-2.005.035-2.868.182-.78 1.172-4.97 1.172-4.97s-.299-.598-.299-1.482c0-1.388.806-2.425 1.808-2.425.853 0 1.265.64 1.265 1.408 0 .858-.546 2.14-.828 3.33-.236.995.5 1.807 1.48 1.807 1.778 0 3.144-1.874 3.144-4.58 0-2.393-1.72-4.068-4.177-4.068-2.845 0-4.515 2.135-4.515 4.34 0 .859.331 1.781.745 2.282a.3.3 0 0 1 .069.288l-.278 1.133c-.044.183-.145.222-.335.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.473 6.165 5.776 0 3.447-2.173 6.22-5.19 6.22-1.013 0-1.965-.527-2.291-1.148l-.623 2.378c-.226.869-.835 1.958-1.244 2.621.937.29 1.931.446 2.962.446 5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>',
                'Facebook'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
        );
        return isset( $svgs[ $name ] ) ? $svgs[ $name ] : '';
}

/* ═══════════════════════════════════════════════
   5. WOOCOMMERCE CUSTOMIZATIONS
   ═══════════════════════════════════════════════ */
add_action( 'after_setup_theme', 'velure3_woo_setup' );
function velure3_woo_setup() {
        if ( ! class_exists( 'WooCommerce' ) ) {
                return;
        }
        remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
        remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
        add_action( 'woocommerce_before_main_content', 'velure3_wrapper_start', 10 );
        add_action( 'woocommerce_after_main_content', 'velure3_wrapper_end', 10 );
}

function velure3_wrapper_start() {
        echo '<div class="velure-content-wrapper">';
}

function velure3_wrapper_end() {
        echo '</div>';
}

add_filter( 'loop_shop_columns', 'velure3_loop_columns' );
function velure3_loop_columns() {
        return 4;
}

add_filter( 'loop_shop_per_page', 'velure3_products_per_page' );
function velure3_products_per_page() {
        return 12;
}

add_filter( 'woocommerce_enqueue_styles', 'velure3_dequeue_woo_styles' );
function velure3_dequeue_woo_styles( $enqueue_styles ) {
        unset( $enqueue_styles['woocommerce-general'] );
        return $enqueue_styles;
}

/* ── WooCommerce AJAX Cart Count ── */
add_action( 'wp_ajax_velure_get_cart_count', 'velure_get_cart_count' );
add_action( 'wp_ajax_nopriv_velure_get_cart_count', 'velure_get_cart_count' );
function velure_get_cart_count() {
        if ( ! class_exists( 'WooCommerce' ) ) {
                wp_send_json( array( 'count' => 0 ) );
        }
        wp_send_json( array( 'count' => WC()->cart->get_cart_contents_count() ) );
}

/* ── Localize script for AJAX ── */
add_action( 'wp_enqueue_scripts', 'velure3_localize' );
function velure3_localize() {
        if ( ! class_exists( 'WooCommerce' ) ) {
                return;
        }
        wp_localize_script( 'velure3-theme', 'velure3_ajax', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'velure3_nonce' ),
        ) );
}

/* ═══════════════════════════════════════════════
   6. DYNAMIC CONTENT: CPT + ACF + HELPERS
   ═══════════════════════════════════════════════ */
require_once VELURE3_DIR . '/inc/dynamic-fields.php';

/* ═══════════════════════════════════════════════
   7. ADMIN NOTICES
   ═══════════════════════════════════════════════ */
add_action( 'admin_notices', 'velure3_acf_notice' );
function velure3_acf_notice() {
        if ( function_exists( 'acf_add_options_page' ) ) return;
        echo '<div class="notice notice-warning"><p>';
        echo '<strong>Velure3 :</strong> Le plugin <em>Advanced Custom Fields</em> (ACF) n\'est pas actif. ';
        echo 'La page d\'accueil utilisera le contenu par défaut. ';
        echo '<a href="' . esc_url( admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=term' ) ) . '">Installer ACF</a>';
        echo '</p></div>';
}