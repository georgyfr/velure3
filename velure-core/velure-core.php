<?php
/**
 * Plugin Name: Velure Core
 * Plugin URI:  https://velure.paris
 * Description: Personnalisation a 100% de la page d'accueil du theme Velure3. Sections, ordre, styles — tout est configurable depuis l'admin. Aucune dependance ACF requise.
 * Version:     3.0.0
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

define( 'VELURE_CORE_VERSION', '3.0.0' );
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
        }

        private function hooks() {
                add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
                add_action( 'plugins_loaded', array( $this, 'signal_loaded' ), 1 );
                add_action( 'admin_menu', array( $this, 'register_menu' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
                add_action( 'wp_ajax_velure_core_get_settings', array( $this, 'ajax_get_settings' ) );
                add_action( 'wp_ajax_velure_core_import_settings', array( $this, 'ajax_import_settings' ) );
                add_action( 'wp_ajax_velure_core_reset', array( $this, 'ajax_reset' ) );
                add_filter( 'plugin_action_links_' . plugin_basename( VELURE_CORE_FILE ), array( $this, 'settings_link' ) );
                register_activation_hook( VELURE_CORE_FILE, array( $this, 'activate' ) );
        }

        /* ── Activation ── */
        public function activate() {
                $defaults = $this->default_settings();
                if ( false === get_option( VELURE_CORE_OPTION ) ) {
                        add_option( VELURE_CORE_OPTION, $defaults, '', 'no' );
                }
                flush_rewrite_rules();
        }

        /* ── Default settings (used on first activation) ── */
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

                        /* Hero */
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

                        /* Features */
                        'feat_bg_style'      => 'soft',
                        'feat_padding'       => 'normal',
                        'feat_bottom_border' => 0,
                        'trust_features'     => array(),

                        /* Categories */
                        'cat_eyebrow'             => 'Collections',
                        'section_title_categories'=> 'Explorer par Univers',
                        'cat_description'         => 'Explorez nos univers et trouvez la piece qui vous correspond.',
                        'cat_cta_text'            => 'Toutes les categories',
                        'cat_cta_link'            => '/boutique/',
                        'cat_bg_style'            => 'base',
                        'cat_display_count'       => 10,

                        /* Products */
                        'prod_eyebrow'          => 'Selection',
                        'section_title_products'=> 'Pieces Vedettes',
                        'prod_description'      => 'Nos pieces les plus appreciees, choisies pour vous.',
                        'prod_cta_text'         => 'Voir toute la boutique',
                        'prod_cta_link'         => '/boutique/',
                        'prod_columns'          => '4',
                        'prod_mode'             => 'auto',
                        'featured_products_count'=> 8,
                        'prod_bg_style'         => 'soft',
                        'prod_sort'             => 'date',

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
                        'testi_eyebrow'                => 'Avis Clients',
                        'section_title_testimonials'  => 'Ce Que Disent Nos Clients',
                        'testi_description'            => '',
                        'testimonials_count'           => 3,
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
                wp_localize_script( 'velure-core-admin', 'velureCoreAdmin', array(
                        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                        'nonce'   => wp_create_nonce( 'velure_core_save_settings' ),
                ) );
        }

        /* ── Hooks ── */
        public function signal_loaded() {
                do_action( 'velure_core_loaded' );
        }

        public function load_textdomain() {
                load_plugin_textdomain( 'velure-core', false, dirname( plugin_basename( VELURE_CORE_FILE ) ) . '/languages' );
        }

        public function settings_link( $links ) {
                $url = admin_url( 'admin.php?page=velure-front-page' );
                $settings = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Personnaliser', 'velure-core' ) . '</a>';
                array_unshift( $links, $settings );
                return $links;
        }

        /* ── AJAX: Get settings for export ── */
        public function ajax_get_settings() {
                check_ajax_referer( 'velure_core_save_settings' );
                if ( ! current_user_can( 'edit_theme_options' ) ) wp_send_json_error();
                wp_send_json_success( get_option( VELURE_CORE_OPTION, array() ) );
        }

        /* ── AJAX: Import settings ── */
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

        /* ── AJAX: Reset to defaults ── */
        public function ajax_reset() {
                check_ajax_referer( 'velure_core_save_settings' );
                if ( ! current_user_can( 'edit_theme_options' ) ) wp_send_json_error();
                update_option( VELURE_CORE_OPTION, $this->default_settings() );
                wp_send_json_success( array( 'message' => 'Reinitialisation reussie.' ) );
        }
}

Velure_Core::instance();