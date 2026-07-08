<?php
/**
 * Velure Core — ACF Field Groups
 * 100% de la page d'accueil est configurable.
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'acf/init', 'velure_core_register_all_fields' );
function velure_core_register_all_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	$loc_main = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ) );
	$loc_hero = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-hero' ) ) );
	$loc_feat = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-features' ) ) );
	$loc_cats = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-categories' ) ) );
	$loc_prod = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-products' ) ) );
	$loc_bann = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-banner' ) ) );
	$loc_mrq  = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-marquee' ) ) );
	$loc_test = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-testimonials' ) ) );
	$loc_blog = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-blog' ) ) );
	$loc_ig   = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-section-instagram' ) ) );
	$loc_glob = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-global-styles' ) ) );
	$loc_ord  = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-sections-order' ) ) );

	/* ═══════════════════════════════════════════════════════════
	   1. SECTIONS VISIBILITY & ORDER
	   ═══════════════════════════════════════════════════════════ */
	$section_keys = array(
		'hero'         => 'Hero Slider',
		'features'     => 'Barre de Confiance',
		'categories'   => 'Categories',
		'products'     => 'Produits Vedettes',
		'split_banner' => 'Banniere Split',
		'marquee'      => 'Bandeau Marques',
		'testimonials' => 'Temoignages',
		'blog'         => 'Blog / Journal',
		'instagram'    => 'Instagram Feed',
	);

	$vis_fields = array();
	foreach ( $section_keys as $key => $label ) {
		$vis_fields[] = array(
			'key'   => 'field_vc_show_' . $key,
			'label' => $label,
			'name'  => 'show_' . $key,
			'type'  => 'true_false',
			'default_value' => 1,
			'ui' => 1,
			'ui_on_text'  => __( 'Afficher', 'velure-core' ),
			'ui_off_text' => __( 'Masquer', 'velure-core' ),
		);
	}

	acf_add_local_field_group( array(
		'key'    => 'group_vc_sections_visibility',
		'title'  => __( 'Visibilite des Sections', 'velure-core' ),
		'fields' => $vis_fields,
		'location' => $loc_ord,
	) );

	acf_add_local_field_group( array(
		'key'    => 'group_vc_sections_order',
		'title'  => __( 'Ordre des Sections', 'velure-core' ),
		'fields' => array(
			array(
				'key'           => 'field_vc_section_order',
				'label'         => __( 'Ordre d\'affichage', 'velure-core' ),
				'name'          => 'section_order',
				'type'          => 'select',
				'choices'       => $section_keys,
				'multiple'      => 1,
				'ui'            => 1,
				'ajax'          => 1,
				'instructions'  => __( 'Glissez-deplacez pour reordonner les sections.', 'velure-core' ),
				'default_value' => array_keys( $section_keys ),
			),
		),
		'location' => $loc_ord,
	) );


	/* ═══════════════════════════════════════════════════════════
	   2. HERO SLIDER
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_hero',
		'title'  => __( 'Hero — Configuration', 'velure-core' ),
		'fields' => array(
			/* General */
			array( 'key' => 'field_vc_hero_height',     'label' => __( 'Hauteur du slider', 'velure-core' ),         'name' => 'hero_height',         'type' => 'select', 'choices' => array( 'standard' => __( 'Standard (75vh)', 'velure-core' ), 'tall' => __( 'Grand (90vh)', 'velure-core' ), 'compact' => __( 'Compact (60vh)', 'velure-core' ) ), 'default_value' => 'standard' ),
			array( 'key' => 'field_vc_hero_autoplay',   'label' => __( 'Defilement automatique', 'velure-core' ),       'name' => 'hero_autoplay',      'type' => 'true_false', 'default_value' => 1, 'ui' => 1, 'ui_on_text' => 'Oui', 'ui_off_text' => 'Non' ),
			array( 'key' => 'field_vc_hero_speed',      'label' => __( 'Vitesse (ms)', 'velure-core' ),               'name' => 'hero_autoplay_speed','type' => 'number', 'default_value' => 6000, 'min' => 2000, 'max' => 15000, 'step' => 500 ),
			array( 'key' => 'field_vc_hero_overlay',    'label' => __( 'Opacite de l\'overlay (%)', 'velure-core' ),   'name' => 'hero_overlay_opacity','type' => 'number', 'default_value' => 40, 'min' => 0, 'max' => 80 ),
			array( 'key' => 'field_vc_hero_text_align', 'label' => __( 'Alignement du texte', 'velure-core' ),        'name' => 'hero_text_align',    'type' => 'select', 'choices' => array( 'left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite' ), 'default_value' => 'left' ),
			array( 'key' => 'field_vc_hero_text_color', 'label' => __( 'Couleur du texte', 'velure-core' ),          'name' => 'hero_text_color',    'type' => 'select', 'choices' => array( 'light' => 'Clair (sur fond sombre)', 'dark' => 'Sombre (sur fond clair)' ), 'default_value' => 'light' ),

			/* Side blocks tab */
			array( 'key' => 'field_vc_hero_side_tab',   'label' => __( 'Blocs lateraux', 'velure-core' ),             'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_vc_hero_show_side',  'label' => __( 'Afficher blocs lateraux', 'velure-core' ),     'name' => 'hero_show_side',     'type' => 'true_false', 'default_value' => 0, 'ui' => 1, 'ui_on_text' => 'Oui', 'ui_off_text' => 'Non' ),
			array( 'key' => 'field_vc_hs_bs_image',     'label' => 'Best-Seller — Image',           'name' => 'hs_bestseller_image',  'type' => 'image', 'return_format' => 'array' ),
			array( 'key' => 'field_vc_hs_bs_label',     'label' => 'Best-Seller — Label',           'name' => 'hs_bestseller_label',  'type' => 'text', 'default_value' => 'Best-Seller' ),
			array( 'key' => 'field_vc_hs_bs_title',     'label' => 'Best-Seller — Titre',           'name' => 'hs_bestseller_title',  'type' => 'text', 'default_value' => 'Sac Elegance' ),
			array( 'key' => 'field_vc_hs_bs_price',     'label' => 'Best-Seller — Prix',            'name' => 'hs_bestseller_price',  'type' => 'text', 'default_value' => '285,00 EUR' ),
			array( 'key' => 'field_vc_hs_bs_cta_text',  'label' => 'Best-Seller — Texte bouton',    'name' => 'hs_bestseller_cta',    'type' => 'text', 'default_value' => 'VOIR LE PRODUIT' ),
			array( 'key' => 'field_vc_hs_bs_cta_link',  'label' => 'Best-Seller — Lien',            'name' => 'hs_bestseller_link',   'type' => 'url' ),
			array( 'key' => 'field_vc_hs_cat_image',    'label' => 'Categorie — Image',              'name' => 'hs_category_image',    'type' => 'image', 'return_format' => 'array' ),
			array( 'key' => 'field_vc_hs_cat_label',    'label' => 'Categorie — Label',              'name' => 'hs_category_label',    'type' => 'text', 'default_value' => 'Capsule' ),
			array( 'key' => 'field_vc_hs_cat_title',    'label' => 'Categorie — Titre',              'name' => 'hs_category_title',    'type' => 'text', 'default_value' => 'Les Accessoires Essentiels' ),
			array( 'key' => 'field_vc_hs_cat_cta_link', 'label' => 'Categorie — Lien',               'name' => 'hs_category_cta_link', 'type' => 'url' ),

			/* Slides repeater tab */
			array( 'key' => 'field_vc_hero_slides_tab', 'label' => __( 'Slides du slider', 'velure-core' ),            'name' => '', 'type' => 'tab' ),
			array(
				'key'          => 'field_vc_hero_slides_rep',
				'label'        => __( 'Slides', 'velure-core' ),
				'name'         => 'hero_slides',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => __( 'Ajouter un slide', 'velure-core' ),
				'min'          => 0,
				'max'          => 8,
				'sub_fields'   => array(
					array( 'key' => 'field_vc_slide_image',    'label' => 'Image',       'name' => 'image',     'type' => 'image', 'return_format' => 'array', 'required' => 1 ),
					array( 'key' => 'field_vc_slide_eyebrow',  'label' => 'Eyebrow',     'name' => 'eyebrow',   'type' => 'text', 'placeholder' => 'Nouvelle Collection', 'required' => 1 ),
					array( 'key' => 'field_vc_slide_title',    'label' => 'Titre',       'name' => 'title',     'type' => 'text', 'placeholder' => "L'Elegance Minimaliste" ),
					array( 'key' => 'field_vc_slide_subtitle', 'label' => 'Sous-titre',  'name' => 'subtitle',  'type' => 'text' ),
					array( 'key' => 'field_vc_slide_cta_text', 'label' => 'Texte bouton','name' => 'cta_text',  'type' => 'text', 'placeholder' => 'DECOUVRIR' ),
					array( 'key' => 'field_vc_slide_cta_link', 'label' => 'Lien bouton', 'name' => 'cta_link',  'type' => 'url',   'placeholder' => '/boutique/' ),
					array( 'key' => 'field_vc_slide_btn_style','label' => 'Style bouton','name' => 'cta_style', 'type' => 'select', 'choices' => array( 'primary' => 'Sombre', 'gold' => 'Or', 'outline-light' => 'Contour clair' ), 'default_value' => 'primary' ),
				),
			),
		),
		'location' => $loc_hero,
	) );


	/* ═══════════════════════════════════════════════════════════
	   3. FEATURES / TRUST BAR
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_features',
		'title'  => __( 'Barre de Confiance', 'velure-core' ),
		'fields' => array(
			array( 'key' => 'field_vc_feat_bg',    'label' => __( 'Fond de section', 'velure-core' ),     'name' => 'feat_bg_style', 'type' => 'select', 'choices' => array( 'soft' => 'Couleur douce', 'muted' => 'Couleur musee', 'base' => 'Couleur de base' ), 'default_value' => 'soft' ),
			array( 'key' => 'field_vc_feat_pad',   'label' => __( 'Espacement vertical', 'velure-core' ), 'name' => 'feat_padding',  'type' => 'select', 'choices' => array( 'compact' => 'Compact', 'normal' => 'Normal', 'spacious' => 'Genereux' ), 'default_value' => 'normal' ),
			array( 'key' => 'field_vc_feat_border','label' => __( 'Bordure basse', 'velure-core' ),      'name' => 'feat_bottom_border', 'type' => 'true_false', 'default_value' => 0, 'ui' => 1, 'ui_on_text' => 'Oui', 'ui_off_text' => 'Non' ),
			array(
				'key'          => 'field_vc_features_rep',
				'label'        => __( 'Elements de confiance', 'velure-core' ),
				'name'         => 'trust_features',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => __( 'Ajouter un element', 'velure-core' ),
				'min'          => 0,
				'max'          => 6,
				'sub_fields'   => array(
					array( 'key' => 'field_vc_feat_icon',  'label' => 'Icone SVG',   'name' => 'icon_svg',   'type' => 'textarea', 'rows' => 3, 'instructions' => __( 'Collez le code <svg>...</svg>', 'velure-core' ) ),
					array( 'key' => 'field_vc_feat_title', 'label' => 'Titre',       'name' => 'title',      'type' => 'text', 'required' => 1 ),
					array( 'key' => 'field_vc_feat_desc',  'label' => 'Description', 'name' => 'description','type' => 'text' ),
				),
			),
		),
		'location' => $loc_feat,
	) );


	/* ═══════════════════════════════════════════════════════════
	   4. CATEGORIES
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_categories',
		'title'  => __( 'Section Categories', 'velure-core' ),
		'fields' => array(
			array( 'key' => 'field_vc_cat_eyebrow',   'label' => 'Eyebrow',               'name' => 'cat_eyebrow',   'type' => 'text', 'default_value' => 'Collections' ),
			array( 'key' => 'field_vc_cat_title',     'label' => 'Titre',                 'name' => 'section_title_categories', 'type' => 'text', 'default_value' => 'Explorer par Univers' ),
			array( 'key' => 'field_vc_cat_desc',      'label' => 'Description',           'name' => 'cat_description', 'type' => 'text', 'default_value' => 'Explorez nos univers et trouvez la piece qui vous correspond.' ),
			array( 'key' => 'field_vc_cat_cta_text',  'label' => 'Texte bouton CTA',      'name' => 'cat_cta_text',  'type' => 'text', 'default_value' => 'Toutes les categories' ),
			array( 'key' => 'field_vc_cat_cta_link',  'label' => 'Lien bouton CTA',       'name' => 'cat_cta_link',  'type' => 'url',  'default_value' => '/boutique/' ),
			array( 'key' => 'field_vc_cat_bg',        'label' => 'Fond de section',        'name' => 'cat_bg_style',  'type' => 'select', 'choices' => array( 'base' => 'Couleur de base', 'soft' => 'Couleur douce', 'muted' => 'Couleur musee' ), 'default_value' => 'base' ),
			array( 'key' => 'field_vc_cat_count',     'label' => 'Nombre de categories',   'name' => 'cat_display_count', 'type' => 'number', 'default_value' => 10, 'min' => 2, 'max' => 20 ),
			array( 'key' => 'field_vc_cat_manual',    'label' => 'Categories manuelles',   'name' => 'cat_manual',    'type' => 'relationship', 'post_type' => array(), 'taxonomy' => array( 'product_cat' ), 'return_format' => 'object', 'multiple' => 1, 'ui' => 1, 'instructions' => __( 'Si renseigne, remplace les categories automatiques.', 'velure-core' ) ),
		),
		'location' => $loc_cats,
	) );


	/* ═══════════════════════════════════════════════════════════
	   5. PRODUCTS
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_products',
		'title'  => __( 'Section Produits Vedettes', 'velure-core' ),
		'fields' => array(
			array( 'key' => 'field_vc_prod_eyebrow',  'label' => 'Eyebrow',                'name' => 'prod_eyebrow',  'type' => 'text', 'default_value' => 'Selection' ),
			array( 'key' => 'field_vc_prod_title',    'label' => 'Titre',                  'name' => 'section_title_products', 'type' => 'text', 'default_value' => 'Pieces Vedettes' ),
			array( 'key' => 'field_vc_prod_desc',     'label' => 'Description',             'name' => 'prod_description', 'type' => 'text', 'default_value' => 'Nos pieces les plus appreciees, choisies pour vous.' ),
			array( 'key' => 'field_vc_prod_cta_text', 'label' => 'Texte bouton CTA',        'name' => 'prod_cta_text', 'type' => 'text', 'default_value' => 'Voir toute la boutique' ),
			array( 'key' => 'field_vc_prod_cta_link', 'label' => 'Lien bouton CTA',         'name' => 'prod_cta_link', 'type' => 'url',  'default_value' => '/boutique/' ),
			array( 'key' => 'field_vc_prod_columns',  'label' => 'Nombre de colonnes',      'name' => 'prod_columns',  'type' => 'select', 'choices' => array( '3' => '3 colonnes', '4' => '4 colonnes' ), 'default_value' => '4' ),
			array( 'key' => 'field_vc_prod_mode',     'label' => 'Mode de selection',       'name' => 'prod_mode',     'type' => 'select', 'choices' => array( 'auto' => 'Automatique (derniers produits)', 'manual' => 'Manuel (selection ci-dessous)' ), 'default_value' => 'auto' ),
			array( 'key' => 'field_vc_prod_count',    'label' => 'Nombre de produits',     'name' => 'featured_products_count', 'type' => 'number', 'default_value' => 8, 'min' => 4, 'max' => 12 ),
			array( 'key' => 'field_vc_prod_manual',   'label' => 'Produits (manuel)',       'name' => 'prod_manual',   'type' => 'relationship', 'post_type' => array( 'product' ), 'return_format' => 'object', 'multiple' => 1, 'ui' => 1, 'instructions' => __( 'Affiche uniquement si le mode "Manuel" est selectionne.', 'velure-core' ) ),
			array( 'key' => 'field_vc_prod_bg',       'label' => 'Fond de section',         'name' => 'prod_bg_style', 'type' => 'select', 'choices' => array( 'soft' => 'Couleur douce', 'muted' => 'Couleur musee', 'base' => 'Couleur de base' ), 'default_value' => 'soft' ),
			array( 'key' => 'field_vc_prod_sort',     'label' => 'Tri (mode auto)',         'name' => 'prod_sort',     'type' => 'select', 'choices' => array( 'date' => 'Plus recents', 'popularity' => 'Plus populaires', 'rating' => 'Mieux notes', 'rand' => 'Aleatoire' ), 'default_value' => 'date' ),
		),
		'location' => $loc_prod,
	) );


	/* ═══════════════════════════════════════════════════════════
	   6. SPLIT BANNER
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_split_banner',
		'title'  => __( 'Banniere Split', 'velure-core' ),
		'fields' => array(
			array( 'key' => 'field_vc_sb_layout', 'label' => __( 'Disposition', 'velure-core' ), 'name' => 'sb_layout', 'type' => 'select', 'choices' => array( '50-50' => '50 / 50', '60-40' => '60 / 40', '40-60' => '40 / 60', 'full-left' => 'Plein gauche + texte', 'full-right' => 'Plein droit + texte' ), 'default_value' => '50-50' ),

			/* Left side */
			array( 'key' => 'field_vc_sb_left_tab',       'label' => 'Cote Gauche',      'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_vc_sb_left_image',      'label' => 'Image',            'name' => 'sb_left_image',      'type' => 'image', 'return_format' => 'array' ),
			array( 'key' => 'field_vc_sb_left_eyebrow',    'label' => 'Eyebrow',          'name' => 'sb_left_eyebrow',    'type' => 'text' ),
			array( 'key' => 'field_vc_sb_left_title',      'label' => 'Titre',            'name' => 'sb_left_title',      'type' => 'text' ),
			array( 'key' => 'field_vc_sb_left_desc',       'label' => 'Description',      'name' => 'sb_left_desc',       'type' => 'textarea', 'rows' => 2 ),
			array( 'key' => 'field_vc_sb_left_cta_text',   'label' => 'Texte bouton',     'name' => 'sb_left_cta_text',   'type' => 'text' ),
			array( 'key' => 'field_vc_sb_left_cta_link',   'label' => 'Lien',             'name' => 'sb_left_cta_link',   'type' => 'url' ),
			array( 'key' => 'field_vc_sb_left_cta_style',  'label' => 'Style bouton',     'name' => 'sb_left_cta_style',  'type' => 'select', 'choices' => array( 'gold' => 'Or', 'primary' => 'Sombre', 'outline' => 'Contour' ), 'default_value' => 'gold' ),
			array( 'key' => 'field_vc_sb_left_overlay',    'label' => 'Style du cote',     'name' => 'sb_left_style',      'type' => 'select', 'choices' => array( 'dark' => 'Fonce (texte clair)', 'light' => 'Clair (texte sombre)' ), 'default_value' => 'dark' ),

			/* Right side */
			array( 'key' => 'field_vc_sb_right_tab',      'label' => 'Cote Droit',       'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_vc_sb_right_image',     'label' => 'Image',            'name' => 'sb_right_image',     'type' => 'image', 'return_format' => 'array' ),
			array( 'key' => 'field_vc_sb_right_eyebrow',   'label' => 'Eyebrow',          'name' => 'sb_right_eyebrow',   'type' => 'text' ),
			array( 'key' => 'field_vc_sb_right_title',     'label' => 'Titre',            'name' => 'sb_right_title',     'type' => 'text' ),
			array( 'key' => 'field_vc_sb_right_desc',      'label' => 'Description',      'name' => 'sb_right_desc',      'type' => 'textarea', 'rows' => 2 ),
			array( 'key' => 'field_vc_sb_right_cta_text',  'label' => 'Texte bouton',     'name' => 'sb_right_cta_text',  'type' => 'text' ),
			array( 'key' => 'field_vc_sb_right_cta_link',  'label' => 'Lien',             'name' => 'sb_right_cta_link',  'type' => 'url' ),
			array( 'key' => 'field_vc_sb_right_cta_style', 'label' => 'Style bouton',     'name' => 'sb_right_cta_style', 'type' => 'select', 'choices' => array( 'gold' => 'Or', 'primary' => 'Sombre', 'outline' => 'Contour' ), 'default_value' => 'outline' ),
			array( 'key' => 'field_vc_sb_right_overlay',   'label' => 'Style du cote',     'name' => 'sb_right_style',     'type' => 'select', 'choices' => array( 'dark' => 'Fonce (texte clair)', 'light' => 'Clair (texte sombre)' ), 'default_value' => 'light' ),
		),
		'location' => $loc_bann,
	) );


	/* ═══════════════════════════════════════════════════════════
	   7. BRAND MARQUEE
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_marquee',
		'title'  => __( 'Bandeau Defilant (Marquees)', 'velure-core' ),
		'fields' => array(
			array( 'key' => 'field_vc_marquee_speed', 'label' => __( 'Vitesse (secondes pour un cycle)', 'velure-core' ), 'name' => 'marquee_speed', 'type' => 'number', 'default_value' => 25, 'min' => 10, 'max' => 60 ),
			array( 'key' => 'field_vc_marquee_bg',    'label' => __( 'Couleur de fond', 'velure-core' ),  'name' => 'marquee_bg',    'type' => 'select', 'choices' => array( 'base' => 'Couleur de base', 'soft' => 'Couleur douce', 'secondary' => 'Couleur or', 'dark' => 'Fonce' ), 'default_value' => 'base' ),
			array( 'key' => 'field_vc_marquee_dir',   'label' => __( 'Direction', 'velure-core' ),       'name' => 'marquee_direction', 'type' => 'select', 'choices' => array( 'left' => 'Gauche vers Droite', 'right' => 'Droite vers Gauche' ), 'default_value' => 'left' ),
			array(
				'key'          => 'field_vc_brands_rep',
				'label'        => __( 'Marques', 'velure-core' ),
				'name'         => 'brand_names',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => __( 'Ajouter une marque', 'velure-core' ),
				'sub_fields'   => array(
					array( 'key' => 'field_vc_brand_name', 'label' => 'Nom', 'name' => 'name', 'type' => 'text', 'required' => 1 ),
				),
			),
		),
		'location' => $loc_mrq,
	) );


	/* ═══════════════════════════════════════════════════════════
	   8. TESTIMONIALS
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_testimonials',
		'title'  => __( 'Section Temoignages', 'velure-core' ),
		'fields' => array(
			array( 'key' => 'field_vc_testi_eyebrow', 'label' => 'Eyebrow',             'name' => 'testi_eyebrow',  'type' => 'text', 'default_value' => 'Avis Clients' ),
			array( 'key' => 'field_vc_testi_title',   'label' => 'Titre',               'name' => 'section_title_testimonials', 'type' => 'text', 'default_value' => 'Ce Que Disent Nos Clients' ),
			array( 'key' => 'field_vc_testi_desc',    'label' => 'Description',          'name' => 'testi_description', 'type' => 'text' ),
			array( 'key' => 'field_vc_testi_count',   'label' => 'Nombre a afficher',   'name' => 'testimonials_count', 'type' => 'number', 'default_value' => 3, 'min' => 1, 'max' => 6 ),
			array( 'key' => 'field_vc_testi_bg',      'label' => 'Fond de section',      'name' => 'testi_bg_style', 'type' => 'select', 'choices' => array( 'base' => 'Couleur de base', 'soft' => 'Couleur douce', 'muted' => 'Couleur musee', 'dark' => 'Fonce' ), 'default_value' => 'base' ),
			array( 'key' => 'field_vc_testi_cols',    'label' => 'Colonnes',             'name' => 'testi_columns',  'type' => 'select', 'choices' => array( '2' => '2 colonnes', '3' => '3 colonnes' ), 'default_value' => '3' ),
		),
		'location' => $loc_test,
	) );


	/* ═══════════════════════════════════════════════════════════
	   9. BLOG
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_blog',
		'title'  => __( 'Section Blog / Journal', 'velure-core' ),
		'fields' => array(
			array( 'key' => 'field_vc_blog_eyebrow',  'label' => 'Eyebrow',           'name' => 'blog_eyebrow',   'type' => 'text', 'default_value' => 'Actualites' ),
			array( 'key' => 'field_vc_blog_title',    'label' => 'Titre',             'name' => 'section_title_blog', 'type' => 'text', 'default_value' => 'Le Journal' ),
			array( 'key' => 'field_vc_blog_desc',     'label' => 'Description',        'name' => 'blog_description', 'type' => 'text' ),
			array( 'key' => 'field_vc_blog_cta_text', 'label' => 'Texte bouton CTA',   'name' => 'blog_cta_text',  'type' => 'text', 'default_value' => 'Voir tous les articles' ),
			array( 'key' => 'field_vc_blog_cta_link', 'label' => 'Lien bouton CTA',    'name' => 'blog_cta_link',  'type' => 'url',  'default_value' => '/blog/' ),
			array( 'key' => 'field_vc_blog_count',    'label' => 'Nombre d\'articles', 'name' => 'blog_posts_count', 'type' => 'number', 'default_value' => 3, 'min' => 2, 'max' => 6 ),
			array( 'key' => 'field_vc_blog_bg',       'label' => 'Fond de section',    'name' => 'blog_bg_style',   'type' => 'select', 'choices' => array( 'muted' => 'Couleur musee', 'soft' => 'Couleur douce', 'base' => 'Couleur de base' ), 'default_value' => 'muted' ),
			array( 'key' => 'field_vc_blog_cols',     'label' => 'Colonnes',           'name' => 'blog_columns',   'type' => 'select', 'choices' => array( '2' => '2 colonnes', '3' => '3 colonnes' ), 'default_value' => '3' ),
		),
		'location' => $loc_blog,
	) );


	/* ═══════════════════════════════════════════════════════════
	   10. INSTAGRAM
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_instagram',
		'title'  => __( 'Section Instagram', 'velure-core' ),
		'fields' => array(
			array( 'key' => 'field_vc_ig_handle',      'label' => 'Handle Instagram',    'name' => 'instagram_handle',  'type' => 'text', 'placeholder' => '@velure.paris' ),
			array( 'key' => 'field_vc_ig_profile_url', 'label' => 'URL du profil',       'name' => 'instagram_url',     'type' => 'url',  'default_value' => 'https://instagram.com/' ),
			array( 'key' => 'field_vc_ig_eyebrow',     'label' => 'Eyebrow',             'name' => 'ig_eyebrow',        'type' => 'text', 'default_value' => 'Suivez-nous' ),
			array( 'key' => 'field_vc_ig_cols',        'label' => 'Colonnes',            'name' => 'ig_columns',       'type' => 'select', 'choices' => array( '4' => '4 colonnes', '5' => '5 colonnes', '6' => '6 colonnes' ), 'default_value' => '6' ),
			array( 'key' => 'field_vc_ig_gap',         'label' => 'Espacement',          'name' => 'ig_gap',           'type' => 'select', 'choices' => array( 'none' => 'Aucun', 'small' => 'Petit', 'medium' => 'Moyen' ), 'default_value' => 'small' ),
			array(
				'key'          => 'field_vc_ig_images_rep',
				'label'        => 'Images',
				'name'         => 'instagram_images',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => __( 'Ajouter une image', 'velure-core' ),
				'max'          => 8,
				'sub_fields'   => array(
					array( 'key' => 'field_vc_ig_img',  'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'required' => 1 ),
					array( 'key' => 'field_vc_ig_link', 'label' => 'Lien',  'name' => 'link',  'type' => 'url' ),
					array( 'key' => 'field_vc_ig_alt',  'label' => 'Texte alternatif', 'name' => 'alt', 'type' => 'text' ),
				),
			),
		),
		'location' => $loc_ig,
	) );


	/* ═══════════════════════════════════════════════════════════
	   11. GLOBAL STYLES (per-section padding, topbar, etc.)
	   ═══════════════════════════════════════════════════════════ */
	acf_add_local_field_group( array(
		'key'    => 'group_vc_global_styles',
		'title'  => __( 'Styles Globaux & Avances', 'velure-core' ),
		'fields' => array(
			/* Topbar */
			array( 'key' => 'field_vc_glob_topbar_text',  'label' => __( 'Texte de la top bar', 'velure-core' ),        'name' => 'topbar_text',     'type' => 'text', 'default_value' => 'Livraison offerte des 150 EUR d\'achat &bull; Retours gratuits 30 jours' ),
			array( 'key' => 'field_vc_glob_topbar_show',  'label' => __( 'Afficher la top bar', 'velure-core' ),        'name' => 'show_topbar',     'type' => 'true_false', 'default_value' => 1, 'ui' => 1, 'ui_on_text' => 'Oui', 'ui_off_text' => 'Non' ),

			/* Footer */
			array( 'key' => 'field_vc_glob_footer_text',  'label' => __( 'Copyright footer', 'velure-core' ),           'name' => 'footer_copyright','type' => 'text', 'default_value' => '&copy; 2026 Velure. Tous droits reserves.' ),

			/* Section spacing */
			array( 'key' => 'field_vc_glob_sec_pad_tab', 'label' => __( 'Espacement des sections', 'velure-core' ),     'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_vc_glob_section_pad', 'label' => __( 'Padding vertical global', 'velure-core' ),    'name' => 'section_padding', 'type' => 'select', 'choices' => array( 'compact' => 'Compact (3rem)', 'normal' => 'Normal (5.5rem)', 'spacious' => 'Genereux (8rem)', 'none' => 'Aucun' ), 'default_value' => 'normal' ),

			/* Animations */
			array( 'key' => 'field_vc_glob_anim_tab',    'label' => __( 'Animations', 'velure-core' ),                'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_vc_glob_anim_enable', 'label' => __( 'Activer les animations au scroll', 'velure-core' ), 'name' => 'scroll_animations', 'type' => 'true_false', 'default_value' => 1, 'ui' => 1, 'ui_on_text' => 'Oui', 'ui_off_text' => 'Non' ),

			/* Custom CSS */
			array( 'key' => 'field_vc_glob_css_tab',     'label' => __( 'CSS Personnalise', 'velure-core' ),           'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_vc_glob_custom_css',  'label' => __( 'CSS personnalise', 'velure-core' ),          'name' => 'custom_css',      'type' => 'textarea', 'rows' => 8, 'instructions' => __( 'Ce CSS sera injecte en front-end uniquement.', 'velure-core' ) ),
		),
		'location' => $loc_glob,
	) );

}