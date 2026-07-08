#!/usr/bin/env python3
"""
Build Velure Core v3.1.0 — Full WYSIWYG Hero Editor
Adds Elementor-like typography, button styling, spacing, background, and responsive controls.
"""
import os

BASE = '/home/z/my-project/velure-core'

# ═══════════════════════════════════════════════════════
# 1. VELURE-CORE.PHP
# ═══════════════════════════════════════════════════════
def build_main_plugin():
    return r"""<?php
/**
 * Plugin Name: Velure Core
 * Plugin URI:  https://velure.paris
 * Description: Personnalisation a 100% de la page d'accueil du theme Velure3. Sections, ordre, styles — tout est configurable depuis l'admin. Aucune dependance ACF requise.
 * Version:     3.1.0
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

define( 'VELURE_CORE_VERSION', '3.1.0' );
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
		wp_localize_script( 'velure-core-admin', 'velureCoreAdmin', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'velure_core_save_settings' ),
		) );
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
}

Velure_Core::instance();
"""

# ═══════════════════════════════════════════════════════
# 2. DATA-HELPERS.PHP  (add hero CSS generation)
# ═══════════════════════════════════════════════════════
def build_data_helpers():
    # We'll output the full file since the CSS injection section changes significantly
    return r"""<?php
/**
 * Velure Core — Data Helpers & Fallbacks
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════
   1. GENERIC HELPERS
   ═══════════════════════════════════════════════ */

function velure_core_get_image_url( $img, $fallback = '' ) {
	if ( empty( $img ) ) return $fallback;
	if ( is_array( $img ) ) return isset( $img['url'] ) ? $img['url'] : $fallback;
	if ( is_numeric( $img ) ) return wp_get_attachment_url( (int) $img ) ?: $fallback;
	return esc_url( $img );
}

function velure_core_opt( $key, $fallback = '' ) {
	static $settings = null;
	if ( null === $settings ) {
		$settings = get_option( VELURE_CORE_OPTION, array() );
		if ( ! is_array( $settings ) ) $settings = array();
	}
	$val = isset( $settings[ $key ] ) ? $settings[ $key ] : null;
	if ( $val === null || $val === '' ) return $fallback;
	if ( is_array( $val ) && empty( $val ) ) return $fallback;
	return $val;
}

function velure_core_opt_bool( $key, $fallback = true ) {
	static $settings = null;
	if ( null === $settings ) {
		$settings = get_option( VELURE_CORE_OPTION, array() );
		if ( ! is_array( $settings ) ) $settings = array();
	}
	if ( ! isset( $settings[ $key ] ) ) return $fallback;
	return (bool) $settings[ $key ];
}

function velure_core_svg_allowed() {
	return array(
		'svg'      => array( 'width','height','viewBox','fill','stroke','stroke-width','stroke-linecap','stroke-linejoin','xmlns','class' ),
		'rect'     => array( 'x','y','width','height','rx','ry','fill','stroke' ),
		'circle'   => array( 'cx','cy','r','fill','stroke' ),
		'polygon'  => array( 'points','fill' ),
		'polyline' => array( 'points','fill','stroke' ),
		'path'     => array( 'd','fill','stroke' ),
		'line'     => array( 'x1','y1','x2','y2','stroke' ),
	);
}

function velure_core_bg_class( $style ) {
	$map = array( 'soft'=>'velure-section-soft','muted'=>'velure-section-muted','dark'=>'velure-section-dark','secondary'=>'velure-section-secondary','base'=>'' );
	return isset( $map[ $style ] ) ? $map[ $style ] : '';
}

function velure_core_star_svg() {
	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="#c9a96e"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
}

function velure_core_theme_uri() {
	return defined( 'VELURE3_URI' ) ? VELURE3_URI : '';
}

/* ═══════════════════════════════════════════════
   2. SECTION VISIBILITY & ORDER
   ═══════════════════════════════════════════════ */

function velure_core_get_section_visibility() {
	return array(
		'hero'         => velure_core_opt_bool( 'show_hero', true ),
		'features'     => velure_core_opt_bool( 'show_features', true ),
		'categories'   => velure_core_opt_bool( 'show_categories', true ),
		'products'     => velure_core_opt_bool( 'show_products', true ),
		'split_banner' => velure_core_opt_bool( 'show_split_banner', true ),
		'marquee'      => velure_core_opt_bool( 'show_marquee', true ),
		'testimonials' => velure_core_opt_bool( 'show_testimonials', true ),
		'blog'         => velure_core_opt_bool( 'show_blog', true ),
		'instagram'    => velure_core_opt_bool( 'show_instagram', true ),
	);
}

function velure_core_get_ordered_sections() {
	$default_order = array( 'hero','features','categories','products','split_banner','marquee','testimonials','blog','instagram' );
	$custom_order  = velure_core_opt( 'section_order', array() );
	if ( ! empty( $custom_order ) && is_array( $custom_order ) ) {
		$order = array_values( $custom_order );
	} else {
		$order = $default_order;
	}
	$visibility = velure_core_get_section_visibility();
	return array_filter( $order, function( $sec ) use ( $visibility ) {
		return isset( $visibility[ $sec ] ) && $visibility[ $sec ];
	} );
}

/* ═══════════════════════════════════════════════
   3. DEFAULT FEATURE ICONS
   ═══════════════════════════════════════════════ */
function velure_core_default_feature_icons() {
	return array(
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2" ry="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
	);
}

/* ═══════════════════════════════════════════════
   4. SECTION DATA FUNCTIONS
   ═══════════════════════════════════════════════ */

function velure_core_get_hero_data() {
	$uri = velure_core_theme_uri();
	$slides_raw = velure_core_opt( 'hero_slides', array() );
	$slides = array();
	if ( ! empty( $slides_raw ) && is_array( $slides_raw ) ) {
		foreach ( $slides_raw as $s ) {
			if ( empty( $s['image'] ) && empty( $s['title'] ) ) continue;
			$slides[] = array(
				'image'     => velure_core_get_image_url( $s['image'] ?? 0 ),
				'eyebrow'   => $s['eyebrow'] ?? '',
				'title'     => $s['title'] ?? '',
				'subtitle'  => $s['subtitle'] ?? '',
				'cta_text'  => $s['cta_text'] ?? '',
				'cta_link'  => $s['cta_link'] ?? '#',
				'cta_style' => $s['cta_style'] ?? 'primary',
			);
		}
	}
	if ( empty( $slides ) ) {
		$slides = array(
			array( 'image'=>$uri.'/assets/images/hero.jpg','eyebrow'=>'Nouvelle Collection','title'=>"L'Elegance<br/>Minimaliste",'subtitle'=>'Nouvelle Collection Automne 2026','cta_text'=>'DECOUVRIR','cta_link'=>'/boutique/','cta_style'=>'primary' ),
			array( 'image'=>$uri.'/assets/images/banner-collection.jpg','eyebrow'=>'Notre Engagement','title'=>"Matieres<br/>Durables",'subtitle'=>'Vetements ethiques concus pour durer','cta_text'=>'NOTRE ENGAGEMENT','cta_link'=>'/notre-engagement/','cta_style'=>'primary' ),
		);
	}
	$show_side = velure_core_opt_bool( 'hero_show_side', false );
	$side_blocks = array();
	if ( $show_side ) {
		$side_blocks = array(
			'bestseller' => array(
				'image'=>$velure_core_get_image_url(velure_core_opt('hs_bestseller_image',0),$uri.'/assets/images/category-femme.jpg'),
				'label'=>velure_core_opt('hs_bestseller_label','Best-Seller'),
				'title'=>velure_core_opt('hs_bestseller_title','Sac Elegance'),
				'price'=>velure_core_opt('hs_bestseller_price','285,00 EUR'),
				'cta_text'=>velure_core_opt('hs_bestseller_cta','VOIR LE PRODUIT'),
				'cta_link'=>velure_core_opt('hs_bestseller_link','#'),
			),
			'category' => array(
				'image'=>$velure_core_get_image_url(velure_core_opt('hs_category_image',0),$uri.'/assets/images/category-accessoires.jpg'),
				'label'=>velure_core_opt('hs_category_label','Capsule'),
				'title'=>velure_core_opt('hs_category_title','Les Accessoires Essentiels'),
				'cta_link'=>velure_core_opt('hs_category_cta_link','/categorie/accessoires/'),
			),
		);
	}
	return array(
		'height'=>$velure_core_opt('hero_height','standard'),
		'autoplay'=>velure_core_opt_bool('hero_autoplay',true),
		'autoplay_speed'=>absint(velure_core_opt('hero_autoplay_speed',6000)),
		'overlay_opacity'=>absint(velure_core_opt('hero_overlay_opacity',40)),
		'text_align'=>velure_core_opt('hero_text_align','left'),
		'text_color'=>velure_core_opt('hero_text_color','light'),
		'slides'=>$slides,'show_side'=>$show_side,'side_blocks'=>$side_blocks,
	);
}

function velure_core_get_features() {
	$features = velure_core_opt( 'trust_features', array() );
	if ( ! empty( $features ) && is_array( $features ) ) {
		$out = array();
		foreach ( $features as $f ) {
			if ( empty( $f['title'] ) ) continue;
			$out[] = array( 'icon'=>$f['icon_svg']??'','title'=>$f['title'],'desc'=>$f['description']??'' );
		}
		if ( ! empty( $out ) ) return $out;
	}
	$icons = velure_core_default_feature_icons();
	return array(
		array('icon'=>$icons[0],'title'=>'Livraison Express','desc'=>'Sous 24-48h en France'),
		array('icon'=>$icons[1],'title'=>'Retours Gratuits','desc'=>'Sous 30 jours, sans frais'),
		array('icon'=>$icons[2],'title'=>'Paiement Securise','desc'=>'SSL & cryptage 256 bits'),
		array('icon'=>$icons[3],'title'=>'Service Client 7j/7','desc'=>'Par chat, email ou telephone'),
	);
}

function velure_core_get_product_categories() {
	$uri = velure_core_theme_uri();
	$count = absint( velure_core_opt( 'cat_display_count', 10 ) );
	if ( class_exists( 'WooCommerce' ) ) {
		$default_cat = (int) get_option( 'default_product_cat', 0 );
		$terms = get_terms( array('taxonomy'=>'product_cat','hide_empty'=>false,'exclude'=>$default_cat,'number'=>$count,'orderby'=>'count','order'=>'DESC') );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			return array_map( function( $term ) use ( $uri ) {
				$tid = get_term_meta( $term->term_id, 'thumbnail_id', true );
				return array('name'=>$term->name,'slug'=>$term->slug,'link'=>get_term_link($term),'image'=>$tid?wp_get_attachment_url($tid):$uri.'/assets/images/category-femme.jpg','count'=>$term->count);
			}, $terms );
		}
	}
	return array(
		array('name'=>'Femme','slug'=>'femme','link'=>home_url('/categorie-femme/'),'image'=>$uri.'/assets/images/category-femme.jpg','count'=>0),
		array('name'=>'Homme','slug'=>'homme','link'=>home_url('/categorie-homme/'),'image'=>$uri.'/assets/images/category-homme.jpg','count'=>0),
		array('name'=>'Accessoires','slug'=>'accessoires','link'=>home_url('/categorie/accessoires/'),'image'=>$uri.'/assets/images/category-accessoires.jpg','count'=>0),
		array('name'=>'Chaussures','slug'=>'chaussures','link'=>home_url('/categorie/chaussures/'),'image'=>$uri.'/assets/images/category-chaussures.jpg','count'=>0),
		array('name'=>'Maroquinerie','slug'=>'maroquinerie','link'=>home_url('/categorie/maroquinerie/'),'image'=>$uri.'/assets/images/category-maroquinerie.jpg','count'=>0),
		array('name'=>'Sportswear','slug'=>'sportswear','link'=>home_url('/categorie/sportswear/'),'image'=>$uri.'/assets/images/category-sportswear.jpg','count'=>0),
	);
}

function velure_core_get_featured_products() {
	$uri = velure_core_theme_uri();
	$mode = velure_core_opt( 'prod_mode', 'auto' );
	$count = absint( velure_core_opt( 'featured_products_count', 8 ) );
	$sort = velure_core_opt( 'prod_sort', 'date' );
	if ( 'auto' === $mode && class_exists( 'WooCommerce' ) ) {
		$wc_args = array('limit'=>$count,'visibility'=>'visible','return'=>'objects');
		switch($sort){case'popularity':$wc_args['orderby']='popularity';break;case'rating':$wc_args['orderby']='rating';break;case'rand':$wc_args['orderby']='rand';break;default:$wc_args['orderby']='date';$wc_args['order']='DESC';break;}
		$wc_products = wc_get_products($wc_args);
		if(!empty($wc_products)){return array_map(function($p){$img_id=$p->get_image_id();$badge='';if($p->is_on_sale())$badge='<span class="velure-product-card-badge sale">Promo</span>';elseif($p->is_featured())$badge='<span class="velure-product-card-badge new">Nouveau</span>';return array('name'=>$p->get_name(),'link'=>$p->get_permalink(),'image'=>$img_id?wp_get_attachment_url($img_id):'','price_html'=>$p->get_price_html(),'badge'=>$badge);},$wc_products);}
	}
	$demo=array(array('name'=>'Manteau Camel','price'=>'485,00 EUR','badge'=>'<span class="velure-product-card-badge new">Nouveau</span>','img'=>'product-manteau-camel.jpg'),array('name'=>'Blazer Ivoire','price'=>'320,00 EUR','badge'=>'','img'=>'product-blazer-ivoire.jpg'),array('name'=>'Robe Noire','price'=>'275,00 EUR','badge'=>'','img'=>'product-robe-noire.jpg'),array('name'=>'Pull Beige','price'=>'145,00 EUR','badge'=>'<span class="velure-product-card-badge sale">Promo</span>','img'=>'product-pull-beige.jpg'),array('name'=>'Pantalon Gris','price'=>'195,00 EUR','badge'=>'','img'=>'product-pantalon-gris.jpg'),array('name'=>'Sac Cuir','price'=>'385,00 EUR','badge'=>'','img'=>'product-sac-cuir.jpg'),array('name'=>'Bottines Brun','price'=>'265,00 EUR','badge'=>'<span class="velure-product-card-badge new">Nouveau</span>','img'=>'product-bottines-brun.jpg'),array('name'=>'Chemise Blanc','price'=>'125,00 EUR','badge'=>'','img'=>'product-chemise-blanc.jpg'));
	return array_map(function($p)use($uri){return array('name'=>$p['name'],'link'=>home_url('/boutique/'),'image'=>$uri.'/assets/images/'.$p['img'],'price_html'=>$p['price'],'badge'=>$p['badge']);},array_slice($demo,0,$count));
}

function velure_core_get_split_banner() {
	$uri = velure_core_theme_uri();
	return array(
		'layout'=>velure_core_opt('sb_layout','50-50'),
		'left'=>array('image'=>velure_core_get_image_url(velure_core_opt('sb_left_image',0),$uri.'/assets/images/banner-collection.jpg'),'eyebrow'=>velure_core_opt('sb_left_eyebrow','Collection AW25'),'title'=>velure_core_opt('sb_left_title','La Nouvelle Collection'),'desc'=>velure_core_opt('sb_left_desc','Des silhouettes audacieuses et des matieres nobles pour une saison inoubliable.'),'cta_text'=>velure_core_opt('sb_left_cta_text','Decouvrir'),'cta_link'=>velure_core_opt('sb_left_cta_link','/new-collection/'),'cta_style'=>velure_core_opt('sb_left_cta_style','gold'),'style'=>velure_core_opt('sb_left_style','dark')),
		'right'=>array('image'=>velure_core_get_image_url(velure_core_opt('sb_right_image',0),$uri.'/assets/images/category-accessoires.jpg'),'eyebrow'=>velure_core_opt('sb_right_eyebrow','Edition Limitee'),'title'=>velure_core_opt('sb_right_title',"Accessoires d'Exception"),'desc'=>velure_core_opt('sb_right_desc','Sacs, bijoux et ceintures signes par les meilleurs artisans.'),'cta_text'=>velure_core_opt('sb_right_cta_text','Explorer'),'cta_link'=>velure_core_opt('sb_right_cta_link','/categorie/accessoires/'),'cta_style'=>velure_core_opt('sb_right_cta_style','outline'),'style'=>velure_core_opt('sb_right_style','light')),
	);
}

function velure_core_get_brands() {
	$brands = velure_core_opt( 'brand_names', array() );
	if ( ! empty( $brands ) && is_array( $brands ) ) {
		$out = array();
		foreach ( $brands as $b ) { $name = $b['name'] ?? ''; if ( $name ) $out[] = strtoupper( $name ); }
		if ( ! empty( $out ) ) return $out;
	}
	return array( 'ELIE SAAB','VALENTINO','BOTTEGA VENETA','SAINT LAURENT','ACNE STUDIOS','THE ROW','LOEWE','RICK OWENS' );
}

function velure_core_get_testimonials( $count = 3 ) {
	$posts = get_posts(array('numberposts'=>$count,'post_type'=>'velure_testimonial','post_status'=>'publish','orderby'=>'date','order'=>'DESC'));
	if(!empty($posts)){return array_map(function($p){return array('text'=>wp_trim_words($p->post_content,40,'...'),'author'=>get_the_title($p),'role'=>get_post_meta($p->ID,'_velure_role',true),'stars'=>(int)get_post_meta($p->ID,'_velure_stars',true)?:5);},$posts);}
	return array(array('text'=>"La qualite des matieres est exceptionnelle. Mon manteau en cachemire est devenu ma piece preferee.",'author'=>'Camille D.','role'=>'Cliente fidele depuis 2023','stars'=>5),array('text'=>"J'ai commande un blazer pour un evenement et j'ai ete bluffee par la coupe et la finition.",'author'=>'Antoine M.','role'=>'Achat verifie','stars'=>5),array('text'=>'Velure est devenue ma reference mode. Les pieces sont intemporelles et la taille guide est tres fiable.','author'=>'Sophie L.','role'=>'Cliente Premium','stars'=>5));
}

function velure_core_get_blog_posts( $count = 3 ) {
	$uri = velure_core_theme_uri();
	$posts = get_posts(array('numberposts'=>$count,'post_type'=>'post','post_status'=>'publish','orderby'=>'date','order'=>'DESC'));
	if(!empty($posts)){return array_map(function($p){$img_id=get_post_thumbnail_id($p->ID);$cats=get_the_category($p->ID);return array('title'=>get_the_title($p),'excerpt'=>wp_trim_words($p->post_excerpt?:$p->post_content,20),'link'=>get_permalink($p),'image'=>$img_id?wp_get_attachment_url($img_id):'','date'=>get_the_date('d M Y',$p),'category'=>!empty($cats)?$cats[0]->name:'');},$posts);}
	$demo=array(array('title'=>'Tendances Automne/Hiver 2025','category'=>'Tendances','date'=>'15 Jan 2025','excerpt'=>'Les couleurs, coupes et matieres qui dominent cette saison.','img'=>'blog-tendances-aw25.jpg'),array('title'=>'Capsule Garderobe Ideale','category'=>'Style','date'=>'8 Jan 2025','excerpt'=>'Construisez une garde-robe capsule de 15 pieces.','img'=>'blog-capsule-garderobe.jpg'),array('title'=>'Entretien du Cachemire','category'=>'Entretien','date'=>'2 Jan 2025','excerpt'=>"Nos conseils d'experts pour laver, secher et ranger vos pieces en cachemire.",'img'=>'blog-cachemire-entretien.jpg'));
	return array_map(function($p)use($uri){return array('title'=>$p['title'],'excerpt'=>$p['excerpt'],'link'=>home_url('/blog/'),'image'=>$uri.'/assets/images/'.$p['img'],'date'=>$p['date'],'category'=>$p['category']);},array_slice($demo,0,$count));
}

function velure_core_get_instagram_items() {
	$uri = velure_core_theme_uri();
	$items = velure_core_opt( 'instagram_images', array() );
	if ( ! empty( $items ) && is_array( $items ) ) {
		$ig_url = velure_core_opt( 'instagram_url', 'https://instagram.com/' );
		$out = array();
		foreach ( $items as $item ) {
			if ( empty( $item['image'] ) ) continue;
			$out[] = array('image'=>velure_core_get_image_url($item['image']),'link'=>!empty($item['link'])?$item['link']:$ig_url,'alt'=>$item['alt']??'');
		}
		if ( ! empty( $out ) ) return $out;
	}
	$defaults = array();
	$ig_url = velure_core_opt( 'instagram_url', 'https://instagram.com/' );
	for ( $i = 1; $i <= 6; $i++ ) { $defaults[] = array('image'=>$uri.'/assets/images/instagram-0'.$i.'.jpg','link'=>$ig_url,'alt'=>''); }
	return $defaults;
}

/* ═══════════════════════════════════════════════
   5. FRONT-END CSS INJECTION
   ═══════════════════════════════════════════════ */
add_action( 'wp_head', 'velure_core_inject_custom_css' );
function velure_core_inject_custom_css() {
	if ( ! is_front_page() ) return;
	$rules = array();

	/* Section padding */
	$pad = velure_core_opt( 'section_padding', 'normal' );
	$pad_map = array( 'compact'=>'3rem','normal'=>'5.5rem','spacious'=>'8rem','none'=>'0' );
	if ( isset( $pad_map[ $pad ] ) ) {
		$rules[] = '.velure-section { padding-top: ' . $pad_map[ $pad ] . '; padding-bottom: ' . $pad_map[ $pad ] . '; }';
	}

	/* ── Hero WYSIWYG Styles ── */
	$rules = array_merge( $rules, velure_core_hero_dynamic_css() );

	/* Custom CSS from admin */
	$custom_css = velure_core_opt( 'custom_css', '' );
	if ( $custom_css ) { $rules[] = $custom_css; }

	if ( empty( $rules ) ) return;
	echo '<style id="velure-core-custom">' . "\n";
	echo implode( "\n", $rules );
	echo "\n</style>\n";
}

/**
 * Generate all dynamic CSS for the Hero section based on saved settings.
 */
function velure_core_hero_dynamic_css() {
	$r = array();

	/* ── Eyebrow Typography ── */
	$ff = velure_core_opt( 'hero_eyebrow_font_family', '' );
	$fs = velure_core_opt( 'hero_eyebrow_font_size', '' );
	$fw = velure_core_opt( 'hero_eyebrow_font_weight', '' );
	$cl = velure_core_opt( 'hero_eyebrow_color', '' );
	$ls = velure_core_opt( 'hero_eyebrow_letter_spacing', '' );
	$tt = velure_core_opt( 'hero_eyebrow_text_transform', '' );
	$mb = velure_core_opt( 'hero_eyebrow_margin_bottom', '' );
	$props = array();
	if ( $ff ) $props[] = 'font-family:' . esc_attr( $ff ) . ',sans-serif';
	if ( $fs ) $props[] = 'font-size:' . intval($fs) . 'px';
	if ( $fw ) $props[] = 'font-weight:' . intval($fw);
	if ( $cl ) $props[] = 'color:' . esc_attr( $cl );
	if ( $ls ) $props[] = 'letter-spacing:' . floatval($ls) . 'px';
	if ( $tt && 'none' !== $tt ) $props[] = 'text-transform:' . esc_attr( $tt );
	if ( $mb ) $props[] = 'margin-bottom:' . intval($mb) . 'px';
	if ( $props ) $r[] = '.velure-eyebrow{'.implode(';',$props).';}';

	/* ── Title Typography ── */
	$ff = velure_core_opt( 'hero_title_font_family', '' );
	$fs = velure_core_opt( 'hero_title_font_size', '' );
	$fw = velure_core_opt( 'hero_title_font_weight', '' );
	$cl = velure_core_opt( 'hero_title_color', '' );
	$lh = velure_core_opt( 'hero_title_line_height', '' );
	$ls = velure_core_opt( 'hero_title_letter_spacing', '' );
	$mb = velure_core_opt( 'hero_title_margin_bottom', '' );
	$props = array();
	if ( $ff ) $props[] = 'font-family:' . esc_attr( $ff ) . ',serif';
	if ( $fs ) $props[] = 'font-size:' . intval($fs) . 'px';
	if ( $fw ) $props[] = 'font-weight:' . intval($fw);
	if ( $cl ) $props[] = 'color:' . esc_attr( $cl );
	if ( $lh ) $props[] = 'line-height:' . floatval($lh);
	if ( $ls ) $props[] = 'letter-spacing:' . floatval($ls) . 'px';
	if ( $mb ) $props[] = 'margin-bottom:' . intval($mb) . 'px';
	if ( $props ) $r[] = '.v-hero-slide-content h2{'.implode(';',$props).';}';

	/* ── Subtitle Typography ── */
	$ff = velure_core_opt( 'hero_subtitle_font_family', '' );
	$fs = velure_core_opt( 'hero_subtitle_font_size', '' );
	$fw = velure_core_opt( 'hero_subtitle_font_weight', '' );
	$cl = velure_core_opt( 'hero_subtitle_color', '' );
	$lh = velure_core_opt( 'hero_subtitle_line_height', '' );
	$mb = velure_core_opt( 'hero_subtitle_margin_bottom', '' );
	$props = array();
	if ( $ff ) $props[] = 'font-family:' . esc_attr( $ff ) . ',sans-serif';
	if ( $fs ) $props[] = 'font-size:' . intval($fs) . 'px';
	if ( $fw ) $props[] = 'font-weight:' . intval($fw);
	if ( $cl ) $props[] = 'color:' . esc_attr( $cl );
	if ( $lh ) $props[] = 'line-height:' . floatval($lh);
	if ( $mb ) $props[] = 'margin-bottom:' . intval($mb) . 'px';
	if ( $props ) $r[] = '.v-hero-subtitle{'.implode(';',$props).';}';

	/* ── CTA Button ── */
	$fs = velure_core_opt( 'hero_cta_font_size', '' );
	$fw = velure_core_opt( 'hero_cta_font_weight', '' );
	$ls = velure_core_opt( 'hero_cta_letter_spacing', '' );
	$tt = velure_core_opt( 'hero_cta_text_transform', '' );
	$px = velure_core_opt( 'hero_cta_padding_x', '' );
	$py = velure_core_opt( 'hero_cta_padding_y', '' );
	$br = velure_core_opt( 'hero_cta_border_radius', '' );
	$bg = velure_core_opt( 'hero_cta_bg_color', '' );
	$tc = velure_core_opt( 'hero_cta_text_color', '' );
	$bw = velure_core_opt( 'hero_cta_border_width', '' );
	$bc = velure_core_opt( 'hero_cta_border_color', '' );
	$hbg = velure_core_opt( 'hero_cta_hover_bg_color', '' );
	$htc = velure_core_opt( 'hero_cta_hover_text_color', '' );
	$props = array();
	$props[] = 'display:inline-block';
	if ( $fs ) $props[] = 'font-size:' . intval($fs) . 'px';
	if ( $fw ) $props[] = 'font-weight:' . intval($fw);
	if ( $ls ) $props[] = 'letter-spacing:' . floatval($ls) . 'px';
	if ( $tt && 'none' !== $tt ) $props[] = 'text-transform:' . esc_attr( $tt );
	if ( $px || $py ) $props[] = 'padding:' . intval($py) . 'px ' . intval($px) . 'px';
	if ( $br ) $props[] = 'border-radius:' . intval($br) . 'px';
	if ( $bg ) $props[] = 'background:' . esc_attr( $bg );
	if ( $tc ) $props[] = 'color:' . esc_attr( $tc );
	if ( $bw ) $props[] = 'border:' . intval($bw) . 'px solid ' . esc_attr( $bc ?: $bg );
	$props[] = 'text-decoration:none';
	$props[] = 'transition:all .25s ease';
	$r[] = '.v-hero-slide-content .velure-btn{'.implode(';',$props).';}';
	/* Hover */
	$hprops = array();
	if ( $hbg ) $hprops[] = 'background:' . esc_attr( $hbg );
	if ( $htc ) $hprops[] = 'color:' . esc_attr( $htc );
	if ( $hprops ) $r[] = '.v-hero-slide-content .velure-btn:hover{'.implode(';',$hprops).';}';

	/* ── Background ── */
	$bgp = velure_core_opt( 'hero_bg_position', '' );
	$bgs = velure_core_opt( 'hero_bg_size', '' );
	$bprops = array();
	if ( $bgp && 'center' !== $bgp ) $bprops[] = 'background-position:' . esc_attr( $bgp );
	if ( $bgs && 'cover' !== $bgs ) $bprops[] = 'background-size:' . esc_attr( $bgs );
	if ( $bprops ) $r[] = '.v-hero-slide-bg{'.implode(';',$bprops).';}';

	/* ── Spacing & Layout ── */
	$mw = velure_core_opt( 'hero_content_max_width', '' );
	if ( $mw ) $r[] = '.v-hero-slide-content{max-width:'.intval($mw).'px;}';
	$pv = velure_core_opt( 'hero_padding_v', '' );
	if ( $pv ) $r[] = '.v-hero-slider{padding-top:'.intval($pv).'px;padding-bottom:'.intval($pv).'px;}';

	/* ── Side Blocks ── */
	$sw = velure_core_opt( 'hero_side_width', '' );
	if ( $sw ) $r[] = '.v-hero-side{width:'.intval($sw).'px;min-width:'.intval($sw).'px;}';
	$sg = velure_core_opt( 'hero_side_gap', '' );
	if ( $sg ) $r[] = '.v-hero-side{gap:'.intval($sg).'px;}';
	$scr = velure_core_opt( 'hero_side_card_radius', '' );
	if ( $scr ) $r[] = '.v-hero-side-card{border-radius:'.intval($scr).'px;overflow:hidden;}';
	$sih = velure_core_opt( 'hero_side_card_img_height', '' );
	if ( $sih ) $r[] = '.v-hero-side-card-img{height:'.intval($sih).'px;}';

	/* ── Responsive (Mobile) ── */
	$mfs = velure_core_opt( 'hero_title_size_mobile', '' );
	$mss = velure_core_opt( 'hero_subtitle_size_mobile', '' );
	$mcs = velure_core_opt( 'hero_cta_size_mobile', '' );
	$mes = velure_core_opt( 'hero_eyebrow_size_mobile', '' );
	$mpv = velure_core_opt( 'hero_padding_v_mobile', '' );
	$mhs = velure_core_opt( 'hero_hide_side_mobile', 0 );
	$mobile = array();
	if ( $mfs ) $mobile[] = '.v-hero-slide-content h2{font-size:'.intval($mfs).'px;}';
	if ( $mss ) $mobile[] = '.v-hero-subtitle{font-size:'.intval($mss).'px;}';
	if ( $mcs ) $mobile[] = '.v-hero-slide-content .velure-btn{font-size:'.intval($mcs).'px;}';
	if ( $mes ) $mobile[] = '.velure-eyebrow{font-size:'.intval($mes).'px;}';
	if ( $mpv ) $mobile[] = '.v-hero-slider{padding-top:'.intval($mpv).'px;padding-bottom:'.intval($mpv).'px;}';
	if ( $mhs ) $mobile[] = '.v-hero-side{display:none !important;}';
	if ( $mobile ) $r[] = '@media(max-width:768px){'.implode('',$mobile).'}';

	return $r;
}

/* ── Disable scroll animations if requested ── */
add_filter( 'body_class', 'velure_core_body_classes' );
function velure_core_body_classes( $classes ) {
	if ( is_front_page() && ! velure_core_opt_bool( 'scroll_animations', true ) ) {
		$classes[] = 'velure-no-animations';
	}
	return $classes;
}

/* ═══════════════════════════════════════════════
   6. COMPATIBILITY ALIASES
   ═══════════════════════════════════════════════ */
if ( ! function_exists( 'velure3_get_image_url' ) ) {
	function velure3_get_image_url( $img, $fallback = '' ) { return velure_core_get_image_url( $img, $fallback ); }
}
if ( ! function_exists( 'velure3_opt' ) ) {
	function velure3_opt( $key, $fallback = '' ) { return velure_core_opt( $key, $fallback ); }
}
if ( ! function_exists( 'velure3_opt_bool' ) ) {
	function velure3_opt_bool( $key, $fallback = true ) { return velure_core_opt_bool( $key, $fallback ); }
}
if ( ! function_exists( 'velure3_svg_allowed' ) ) {
	function velure3_svg_allowed() { return velure_core_svg_allowed(); }
}
if ( ! function_exists( 'velure3_bg_class' ) ) {
	function velure3_bg_class( $style ) { return velure_core_bg_class( $style ); }
}
if ( ! function_exists( 'velure3_star_svg' ) ) {
	function velure3_star_svg() { return velure_core_star_svg(); }
}
if ( ! function_exists( 'velure3_get_section_visibility' ) ) {
	function velure3_get_section_visibility() { return velure_core_get_section_visibility(); }
}
if ( ! function_exists( 'velure3_get_hero_data' ) ) {
	function velure3_get_hero_data() { return velure_core_get_hero_data(); }
}
if ( ! function_exists( 'velure3_get_features' ) ) {
	function velure3_get_features() { return velure_core_get_features(); }
}
if ( ! function_exists( 'velure3_get_product_categories' ) ) {
	function velure3_get_product_categories( $count = 10 ) { return velure_core_get_product_categories( $count ); }
}
if ( ! function_exists( 'velure3_get_featured_products' ) ) {
	function velure3_get_featured_products() { return velure_core_get_featured_products(); }
}
if ( ! function_exists( 'velure3_get_split_banner' ) ) {
	function velure3_get_split_banner() { return velure_core_get_split_banner(); }
}
if ( ! function_exists( 'velure3_get_brands' ) ) {
	function velure3_get_brands() { return velure_core_get_brands(); }
}
if ( ! function_exists( 'velure3_get_testimonials' ) ) {
	function velure3_get_testimonials( $count = 3 ) { return velure_core_get_testimonials( $count ); }
}
if ( ! function_exists( 'velure3_get_blog_posts' ) ) {
	function velure3_get_blog_posts( $count = 3 ) { return velure_core_get_blog_posts( $count ); }
}
if ( ! function_exists( 'velure3_get_instagram_items' ) ) {
	function velure3_get_instagram_items() { return velure_core_get_instagram_items(); }
}
"""

print("Building files...")

# Write main plugin file
with open(os.path.join(BASE, 'velure-core.php'), 'w') as f:
    f.write(build_main_plugin())
print("  velure-core.php written")

# Write data-helpers
with open(os.path.join(BASE, 'includes/data-helpers.php'), 'w') as f:
    f.write(build_data_helpers())
print("  data-helpers.php written")

print("Core files done. Now building admin-pages.php, admin.css, admin.js...")