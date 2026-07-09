<?php
/**
 * Plugin Name: Velure Core
 * Plugin URI:  https://velure.paris
 * Description: Personnalisation a 100% de la page d'accueil du theme Velure3. Sections, ordre, styles — tout est configurable depuis l'admin. Aucune dependance ACF requise.
 * Version:     3.5.3
 * Author:      Velure
 * Author URI:  https://velure.paris
 * License:     GPL-2.0+
 * Text Domain: velure-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* Flag checked by the theme shim to prevent double-loading */
define( 'VELURE_CORE_ACTIVE', true );

define( 'VELURE_CORE_VERSION', '3.5.1' );
define( 'VELURE_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'VELURE_CORE_URI', plugin_dir_url( __FILE__ ) );
define( 'VELURE_CORE_FILE', __FILE__ );
define( 'VELURE_CORE_OPTION', 'velure_core_settings' );

/* ═══════════════════════════════════════════════
   BOOTSTRAP
   ═══════════════════════════════════════════════ */
final class Velure_Core {

        private static $instance = null;

        public static function instance() {
                if ( null === self::$instance ) {
                        self::$instance = new self();
                }
                return self::$instance;
        }

        private function __construct() {
                $this->includes();
                $this->hooks();
        }

        private function includes() {
                require_once VELURE_CORE_DIR . 'includes/cpt-testimonial.php';
                require_once VELURE_CORE_DIR . 'includes/data-helpers.php';
                require_once VELURE_CORE_DIR . 'includes/admin-pages.php';
                require_once VELURE_CORE_DIR . 'includes/admin-notices.php';
                require_once VELURE_CORE_DIR . 'includes/canvas-renderer.php';
                require_once VELURE_CORE_DIR . 'includes/frontend-edit.php';
        }

        private function hooks() {
                add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
                add_action( 'plugins_loaded', array( $this, 'signal_loaded' ), 1 );
                add_action( 'admin_init', array( $this, 'intercept_canvas_request' ), 1 );
                add_action( 'admin_menu', array( $this, 'register_menu' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
                add_action( 'wp_enqueue_scripts', array( $this, 'frontend_edit_assets' ) );
                add_action( 'wp_ajax_velure_core_get_settings', array( $this, 'ajax_get_settings' ) );
                add_action( 'wp_ajax_velure_core_import_settings', array( $this, 'ajax_import_settings' ) );
                add_action( 'wp_ajax_velure_core_auto_save', array( $this, 'ajax_auto_save' ) );
                add_action( 'wp_ajax_vc_render_component_preview', array( $this, 'ajax_render_component_preview' ) );
                add_action( 'wp_ajax_velure_core_reset', array( $this, 'ajax_reset' ) );
                add_filter( 'plugin_action_links_' . plugin_basename( VELURE_CORE_FILE ), array( $this, 'settings_link' ) );
                register_activation_hook( VELURE_CORE_FILE, array( $this, 'activate' ) );
        }

        /**
         * Intercept canvas iframe requests BEFORE WordPress outputs admin-header.php.
         * Hooked on admin_init (priority 1) — runs before any admin page rendering.
         *
         * @since 3.4.2
         */
        public function intercept_canvas_request() {
                if ( ! function_exists( 'velure_core_is_canvas_request' ) ) return;
                $section = velure_core_is_canvas_request();
                if ( $section ) {
                        velure_core_render_canvas( $section );
                        /* velure_core_render_canvas() calls exit; internally */
                }
        }

        /* ── Activation ── */
        public function activate() {
                $defaults = $this->default_settings();
                if ( false === get_option( VELURE_CORE_OPTION ) ) {
                        add_option( VELURE_CORE_OPTION, $defaults, '', 'no' );
                }
                flush_rewrite_rules();
        }

        /* ── Default settings ── */
        public function default_settings() {
                return array(
                        /* Sections visibility */
                        'show_hero'         => 1,
                        'show_features'     => 1,
                        'show_categories'   => 1,
                        'show_products'     => 1,
                        'show_split_banner' => 1,
                        'show_marquee'      => 1,
                        'show_testimonials' => 1,
                        'show_blog'         => 1,
                        'show_instagram'    => 1,
                        'section_order'     => array( 'hero','features','categories','products','split_banner','marquee','testimonials','blog','instagram' ),

                        /* ── Hero: Content ── */
                        'hero_height'          => 'standard',
                        'hero_autoplay'        => 1,
                        'hero_autoplay_speed'  => 6000,
                        'hero_overlay_opacity' => 40,
                        'hero_text_align'      => 'left',
                        'hero_text_color'      => 'light',
                        'hero_show_side'       => 0,
                        'hs_bestseller_image'  => 0,
                        'hs_bestseller_label'  => 'Best-Seller',
                        'hs_bestseller_title'  => 'Sac Elegance',
                        'hs_bestseller_price'  => '285,00 EUR',
                        'hs_bestseller_cta'    => 'VOIR LE PRODUIT',
                        'hs_bestseller_link'   => '#',
                        'hs_category_image'    => 0,
                        'hs_category_label'    => 'Capsule',
                        'hs_category_title'    => 'Les Accessoires Essentiels',
                        'hs_category_cta_link' => '/categorie/accessoires/',
                        'hero_slides'          => array(),

                        /* ── Hero: Typography — Eyebrow ── */
                        'hero_eyebrow_font_family'    => 'Inter',
                        'hero_eyebrow_font_size'      => 12,
                        'hero_eyebrow_font_weight'    => 500,
                        'hero_eyebrow_color'          => '#C8A97E',
                        'hero_eyebrow_letter_spacing' => 2,
                        'hero_eyebrow_text_transform' => 'uppercase',
                        'hero_eyebrow_margin_bottom'  => 12,

                        /* ── Hero: Typography — Title ── */
                        'hero_title_font_family'    => 'Playfair Display',
                        'hero_title_font_size'      => 56,
                        'hero_title_font_weight'    => 700,
                        'hero_title_color'          => '#FFFFFF',
                        'hero_title_line_height'    => 1.1,
                        'hero_title_letter_spacing' => -0.5,
                        'hero_title_margin_bottom'  => 16,

                        /* ── Hero: Typography — Subtitle ── */
                        'hero_subtitle_font_family'    => 'Inter',
                        'hero_subtitle_font_size'      => 18,
                        'hero_subtitle_font_weight'    => 400,
                        'hero_subtitle_color'          => 'rgba(255,255,255,0.8)',
                        'hero_subtitle_line_height'    => 1.6,
                        'hero_subtitle_margin_bottom'  => 28,

                        /* ── Hero: CTA Button Styling ── */
                        'hero_cta_font_size'         => 13,
                        'hero_cta_font_weight'       => 600,
                        'hero_cta_letter_spacing'    => 1.5,
                        'hero_cta_text_transform'    => 'uppercase',
                        'hero_cta_padding_x'         => 32,
                        'hero_cta_padding_y'         => 15,
                        'hero_cta_border_radius'     => 0,
                        'hero_cta_bg_color'          => '#1A1A1A',
                        'hero_cta_text_color'        => '#FFFFFF',
                        'hero_cta_hover_bg_color'    => '#C8A97E',
                        'hero_cta_hover_text_color'  => '#1A1A1A',
                        'hero_cta_border_width'      => 0,
                        'hero_cta_border_color'      => '#1A1A1A',

                        /* ── Hero: Background Image ── */
                        'hero_bg_position'     => 'center',
                        'hero_bg_size'         => 'cover',

                        /* ── Hero: Spacing & Layout ── */
                        'hero_content_max_width' => 580,
                        'hero_padding_v'         => 80,

                        /* ── Hero: Side Blocks Styling ── */
                        'hero_side_width'           => 320,
                        'hero_side_gap'             => 12,
                        'hero_side_card_radius'     => 8,
                        'hero_side_card_img_height' => 160,

                        /* ── Hero: Responsive (Mobile) ── */
                        'hero_title_size_mobile'    => 36,
                        'hero_subtitle_size_mobile'=> 15,
                        'hero_cta_size_mobile'      => 12,
                        'hero_eyebrow_size_mobile'  => 11,
                        'hero_padding_v_mobile'     => 50,
                        'hero_hide_side_mobile'     => 0,

                        /* Features */
                        'feat_bg_style'      => 'soft',
                        'feat_padding'       => 'normal',
                        'feat_bottom_border' => 0,
                        'trust_features'     => array(),

                        /* Categories */
                        'cat_eyebrow'              => 'Collections',
                        'section_title_categories' => 'Explorer par Univers',
                        'cat_description'          => 'Explorez nos univers et trouvez la piece qui vous correspond.',
                        'cat_cta_text'             => 'Toutes les categories',
                        'cat_cta_link'             => '/boutique/',
                        'cat_bg_style'             => 'base',
                        'cat_display_count'        => 10,

                        /* Products */
                        'prod_eyebrow'           => 'Selection',
                        'section_title_products' => 'Pieces Vedettes',
                        'prod_description'       => 'Nos pieces les plus appreciees, choisies pour vous.',
                        'prod_cta_text'          => 'Voir toute la boutique',
                        'prod_cta_link'          => '/boutique/',
                        'prod_columns'           => '4',
                        'prod_mode'              => 'auto',
                        'featured_products_count'=> 8,
                        'prod_bg_style'          => 'soft',
                        'prod_sort'              => 'date',

                        /* Split Banner */
                        'sb_layout'          => '50-50',
                        'sb_left_image'      => 0,
                        'sb_left_eyebrow'    => 'Collection AW25',
                        'sb_left_title'      => 'La Nouvelle Collection',
                        'sb_left_desc'       => 'Des silhouettes audacieuses et des matieres nobles pour une saison inoubliable.',
                        'sb_left_cta_text'   => 'Decouvrir',
                        'sb_left_cta_link'   => '/new-collection/',
                        'sb_left_cta_style'  => 'gold',
                        'sb_left_style'      => 'dark',
                        'sb_right_image'     => 0,
                        'sb_right_eyebrow'   => 'Edition Limitee',
                        'sb_right_title'     => "Accessoires d'Exception",
                        'sb_right_desc'      => 'Sacs, bijoux et ceintures signes par les meilleurs artisans.',
                        'sb_right_cta_text'  => 'Explorer',
                        'sb_right_cta_link'  => '/categorie/accessoires/',
                        'sb_right_cta_style' => 'outline',
                        'sb_right_style'     => 'light',

                        /* Marquee */
                        'marquee_speed'    => 25,
                        'marquee_bg'       => 'base',
                        'marquee_direction'=> 'left',
                        'brand_names'      => array(),

                        /* Testimonials */
                        'testi_eyebrow'               => 'Avis Clients',
                        'section_title_testimonials' => 'Ce Que Disent Nos Clients',
                        'testi_description'           => '',
                        'testimonials_count'          => 3,
                        'testi_bg_style'              => 'base',
                        'testi_columns'               => '3',

                        /* Blog */
                        'blog_eyebrow'          => 'Actualites',
                        'section_title_blog'   => 'Le Journal',
                        'blog_description'     => '',
                        'blog_cta_text'        => 'Voir tous les articles',
                        'blog_cta_link'        => '/blog/',
                        'blog_posts_count'     => 3,
                        'blog_bg_style'        => 'muted',
                        'blog_columns'         => '3',

                        /* Instagram */
                        'instagram_handle' => '@velure.paris',
                        'instagram_url'    => 'https://instagram.com/',
                        'ig_eyebrow'       => 'Suivez-nous',
                        'ig_columns'       => '6',
                        'ig_gap'           => 'small',
                        'instagram_images' => array(),

                        /* Global Styles */
                        'topbar_text'        => 'Livraison offerte des 150 EUR d\'achat &bull; Retours gratuits 30 jours',
                        'show_topbar'        => 1,
                        'footer_copyright'   => '&copy; 2026 Velure. Tous droits reserves.',
                        'section_padding'    => 'normal',
                        'scroll_animations'  => 1,
                        'custom_css'         => '',
                );
        }

        /* ── Admin Menu ── */
        public function register_menu() {
                add_menu_page(
                        __( 'Velure Accueil', 'velure-core' ),
                        __( 'Velure Accueil', 'velure-core' ),
                        'edit_theme_options',
                        'velure-front-page',
                        'velure_core_render_admin_page',
                        'dashicons-layout',
                        2
                );
        }

        /* ── Admin Assets ── */
        public function admin_assets( $hook ) {
                if ( 'toplevel_page_velure-front-page' !== $hook ) return;
                wp_enqueue_media();
                wp_enqueue_style( 'velure-core-admin', VELURE_CORE_URI . 'assets/css/admin.css', array(), VELURE_CORE_VERSION );
                wp_enqueue_script( 'velure-core-admin', VELURE_CORE_URI . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable' ), VELURE_CORE_VERSION, true );
                /* Visual Builder: contextual panel + postMessage bridge (loaded only in builder mode) */
                wp_enqueue_script( 'velure-visual-builder', VELURE_CORE_URI . 'assets/js/visual-builder.js', array( 'jquery' ), VELURE_CORE_VERSION, true );
                wp_localize_script( 'velure-core-admin', 'velureCoreAdmin', array(
                        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                        'nonce'   => wp_create_nonce( 'velure_core_save_settings' ),
                ) );
        }

        /* ── Frontend Edit Button Assets ── */
        public function frontend_edit_assets() {
                velure_core_frontend_edit_assets();
        }

        public function signal_loaded() { do_action( 'velure_core_loaded' ); }
        public function load_textdomain() { load_plugin_textdomain( 'velure-core', false, dirname( plugin_basename( VELURE_CORE_FILE ) ) . '/languages' ); }
        public function settings_link( $links ) {
                $url = admin_url( 'admin.php?page=velure-front-page' );
                $settings = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Personnaliser', 'velure-core' ) . '</a>';
                array_unshift( $links, $settings );
                return $links;
        }
        public function ajax_get_settings() {
                check_ajax_referer( 'velure_core_save_settings' );
                if ( ! current_user_can( 'edit_theme_options' ) ) wp_send_json_error();
                wp_send_json_success( get_option( VELURE_CORE_OPTION, array() ) );
        }
        public function ajax_import_settings() {
                check_ajax_referer( 'velure_core_save_settings' );
                if ( ! current_user_can( 'edit_theme_options' ) ) wp_send_json_error();
                $json = isset( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : '';
                $data = json_decode( $json, true );
                if ( ! is_array( $data ) ) wp_send_json_error( array( 'message' => 'JSON invalide.' ) );
                $merged = wp_parse_args( $data, $this->default_settings() );
                update_option( VELURE_CORE_OPTION, $merged );
                wp_send_json_success( array( 'message' => 'Import reussi.' ) );
        }
        public function ajax_reset() {
                check_ajax_referer( 'velure_core_save_settings' );
                if ( ! current_user_can( 'edit_theme_options' ) ) wp_send_json_error();
                update_option( VELURE_CORE_OPTION, $this->default_settings() );
                wp_send_json_success( array( 'message' => 'Reinitialisation reussie.' ) );
        }

        /**
         * AJAX auto-save: receives partial changes and merges into velure_core_settings.
         * Called by the Visual Builder with debounced field changes.
         *
         * @since 3.3.0
         */
        public function ajax_auto_save() {
                check_ajax_referer( 'velure_core_save_settings' );
                if ( ! current_user_can( 'edit_theme_options' ) ) wp_send_json_error();

                $raw = isset( $_POST['changes'] ) ? wp_unslash( $_POST['changes'] ) : '';
                $changes = json_decode( $raw, true );
                if ( ! is_array( $changes ) ) {
                        wp_send_json_error( array( 'message' => 'Donnees invalides.' ) );
                }

                /* Get current settings and merge changes using dot-notation keys */
                $settings = get_option( VELURE_CORE_OPTION, array() );

                foreach ( $changes as $key => $value ) {
                        $this->set_nested_value( $settings, $key, $value );
                }

                update_option( VELURE_CORE_OPTION, $settings );
                update_option( 'velure_core_last_saved', current_time( 'H:i' ) );

                wp_send_json_success( array( 'message' => 'Sauvegarde reussie.' ) );
        }

        /**
         * AJAX: Render a section HTML fragment for surgical canvas replacement.
         * Called by VCB._requestPartialRender() for structural changes
         * that can't be handled by CSS alone (new blocks, image additions, etc.)
         *
         * @since 3.5.1
         */
        public function ajax_render_component_preview() {
                check_ajax_referer( 'velure_core_save_settings' );
                if ( ! current_user_can( 'edit_theme_options' ) ) wp_send_json_error();

                $section = isset( $_POST['section'] ) ? sanitize_text_field( $_POST['section'] ) : '';
                $key     = isset( $_POST['key'] )     ? sanitize_text_field( $_POST['key'] ) : '';
                $value   = isset( $_POST['value'] )   ? wp_unslash( $_POST['value'] ) : '';

                if ( ! $section ) wp_send_json_error( array( 'message' => 'Section manquante.' ) );

                /* Temporarily apply the change to the settings so the
                   renderer uses the new value when generating HTML. */
                $settings = get_option( VELURE_CORE_OPTION, array() );

                /* Convert dot-notation key to nested array path and set value */
                if ( $key ) {
                        $this->set_nested_value( $settings, $key, $value );
                }

                /* Verify the section is valid */
                $valid   = velure_core_canvas_valid_sections();
                if ( ! isset( $valid[ $section ] ) ) {
                        wp_send_json_error( array( 'message' => 'Section invalide.' ) );
                }

                /* Temporarily override the option so velure_core_canvas_render_section
                   uses the updated settings. We don't persist this — it's only for preview. */
                $backup = get_option( VELURE_CORE_OPTION, array() );
                update_option( VELURE_CORE_OPTION, $settings, false );

                /* Capture the rendered section HTML */
                ob_start();
                if ( function_exists( 'velure_core_canvas_render_section' ) ) {
                        velure_core_canvas_render_section( $section );
                } else {
                        echo '<p>Erreur: canvas renderer non disponible.</p>';
                }
                $html = ob_get_clean();

                /* Restore original settings (preview only, not saved) */
                update_option( VELURE_CORE_OPTION, $backup, false );

                wp_send_json_success( array( 'html' => $html ) );
        }

        /**
         * Set a nested value in an array using dot-notation key.
         * E.g. set_nested_value($arr, 'hero_slides.0.title', 'Hello')
         * sets $arr['hero_slides'][0]['title'] = 'Hello'.
         */
        private function set_nested_value( &$array, $key, $value ) {
                $parts = explode( '.', $key );
                $current = &$array;
                foreach ( $parts as $i => $k ) {
                        if ( $i === count( $parts ) - 1 ) {
                                $current[ $k ] = $value;
                        } else {
                                if ( ! isset( $current[ $k ] ) || ! is_array( $current[ $k ] ) ) {
                                        $current[ $k ] = array();
                                }
                                $current = &$current[ $k ];
                        }
                }
        }
}

Velure_Core::instance();
