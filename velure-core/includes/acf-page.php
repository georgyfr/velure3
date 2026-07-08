<?php
/**
 * Velure Core — ACF Options Page
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'acf/init', 'velure_core_acf_options_pages' );
function velure_core_acf_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) return;

	/* ── Page principale ── */
	acf_add_options_page( array(
		'page_title'  => __( 'Velure — Page d\'accueil', 'velure-core' ),
		'menu_title'  => __( 'Velure Accueil', 'velure-core' ),
		'menu_slug'   => 'velure-front-page',
		'capability'  => 'edit_theme_options',
		'parent_slug' => 'themes.php',
		'position'    => 2,
		'icon_url'    => 'dashicons-layout',
		'updated_message' => __( 'Page d\'accueil mise a jour !', 'velure-core' ),
	) );

	/* ── Sous-pages pour chaque section ── */
	$sections = array(
		array(
			'menu_title' => __( 'Sections & Ordre', 'velure-core' ),
			'menu_slug'  => 'velure-sections-order',
		),
		array(
			'menu_title' => __( 'Hero Slider', 'velure-core' ),
			'menu_slug'  => 'velure-section-hero',
		),
		array(
			'menu_title' => __( 'Barre Confiance', 'velure-core' ),
			'menu_slug'  => 'velure-section-features',
		),
		array(
			'menu_title' => __( 'Categories', 'velure-core' ),
			'menu_slug'  => 'velure-section-categories',
		),
		array(
			'menu_title' => __( 'Produits Vedettes', 'velure-core' ),
			'menu_slug'  => 'velure-section-products',
		),
		array(
			'menu_title' => __( 'Banniere Split', 'velure-core' ),
			'menu_slug'  => 'velure-section-banner',
		),
		array(
			'menu_title' => __( 'Bandeau Marques', 'velure-core' ),
			'menu_slug'  => 'velure-section-marquee',
		),
		array(
			'menu_title' => __( 'Temoignages', 'velure-core' ),
			'menu_slug'  => 'velure-section-testimonials',
		),
		array(
			'menu_title' => __( 'Blog / Journal', 'velure-core' ),
			'menu_slug'  => 'velure-section-blog',
		),
		array(
			'menu_title' => __( 'Instagram Feed', 'velure-core' ),
			'menu_slug'  => 'velure-section-instagram',
		),
		array(
			'menu_title' => __( 'Styles Globaux', 'velure-core' ),
			'menu_slug'  => 'velure-global-styles',
		),
	);

	foreach ( $sections as $sec ) {
		acf_add_options_sub_page( array(
			'page_title'  => 'Velure — ' . $sec['menu_title'],
			'menu_title'  => $sec['menu_title'],
			'menu_slug'   => $sec['menu_slug'],
			'parent_slug' => 'velure-front-page',
			'capability'  => 'edit_theme_options',
		) );
	}
}