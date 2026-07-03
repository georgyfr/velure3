<?php
/**
 * Velure3 — Dynamic Content Fields
 * Registers CPT, ACF Options Page, and ACF Field Groups
 * for the database-driven front page.
 *
 * @package Velure3
 * @version 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════
   1. TESTIMONIAL CPT
   ═══════════════════════════════════════════════ */
add_action( 'init', 'velure3_register_testimonial_cpt' );
function velure3_register_testimonial_cpt() {
	register_post_type( 'velure_testimonial', array(
		'labels' => array(
			'name'               => 'Témoignages',
			'singular_name'      => 'Témoignage',
			'menu_name'          => 'Témoignages',
			'add_new'            => 'Ajouter un témoignage',
			'add_new_item'       => 'Ajouter un témoignage',
			'edit_item'          => 'Modifier le témoignage',
			'view_item'          => 'Voir le témoignage',
			'all_items'          => 'Tous les témoignages',
			'search_items'       => 'Rechercher des témoignages',
		),
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'supports'     => array( 'title', 'editor' ),
		'menu_icon'    => 'dashicons-format-quote',
		'capability_type' => 'post',
	) );
}

/* Testimonial meta fields (native — no ACF needed) */
add_action( 'add_meta_boxes', 'velure3_testimonial_meta_boxes' );
function velure3_testimonial_meta_boxes() {
	add_meta_box( 'velure_testimonial_details', 'Détails du témoignage', 'velure3_testimonial_meta_box_callback', 'velure_testimonial', 'normal', 'high' );
}

function velure3_testimonial_meta_box_callback( $post ) {
	wp_nonce_field( 'velure3_testimonial_nonce', 'velure3_testimonial_nonce_field' );
	$stars  = get_post_meta( $post->ID, '_velure_stars', true ) ?: 5;
	$role   = get_post_meta( $post->ID, '_velure_role', true );
	?>
	<p>
		<label><strong>Note (étoiles) :</strong></label><br/>
		<select name="_velure_stars" style="width:100%;max-width:120px;margin-top:4px;">
			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<option value="<?php echo $i; ?>" <?php selected( $stars, $i ); ?>><?php echo $i; ?> étoile<?php echo $i > 1 ? 's' : ''; ?></option>
			<?php endfor; ?>
		</select>
	</p>
	<p>
		<label><strong>Rôle / Statut :</strong></label><br/>
		<input type="text" name="_velure_role" value="<?php echo esc_attr( $role ); ?>" style="width:100%;margin-top:4px;" placeholder="Ex : Cliente fidèle depuis 2023" />
	</p>
	<?php
}

add_action( 'save_post_velure_testimonial', 'velure3_save_testimonial_meta', 10, 2 );
function velure3_save_testimonial_meta( $post_id, $post ) {
	if ( ! isset( $_POST['velure3_testimonial_nonce_field'] ) || ! wp_verify_nonce( $_POST['velure3_testimonial_nonce_field'], 'velure3_testimonial_nonce' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = array( '_velure_stars', '_velure_role' );
	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
		}
	}
}


/* ═══════════════════════════════════════════════
   2. ACF OPTIONS PAGE
   ═══════════════════════════════════════════════ */
add_action( 'acf/init', 'velure3_acf_options_page' );
function velure3_acf_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) return;
	acf_add_options_page( array(
		'page_title'  => 'Velure — Page d\'accueil',
		'menu_title'  => 'Velure Accueil',
		'menu_slug'   => 'velure-front-page',
		'capability'  => 'edit_theme_options',
		'parent_slug' => 'themes.php',
		'position'    => 2,
		'icon_url'    => 'dashicons-layout',
	) );
}


/* ═══════════════════════════════════════════════
   3. ACF FIELD GROUPS (local PHP registration)
   ═══════════════════════════════════════════════ */
add_action( 'acf/init', 'velure3_register_acf_field_groups' );
function velure3_register_acf_field_groups() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	/* ── 3A. Hero Slides ── */
	acf_add_local_field_group( array(
		'key'      => 'group_velure_hero_slides',
		'title'    => 'Hero — Slides',
		'fields'   => array(
			array(
				'key'          => 'field_velure_hero_slides_repeater',
				'label'        => 'Slides du slider',
				'name'         => 'hero_slides',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => 'Ajouter un slide',
				'min'          => 1,
				'max'          => 6,
				'sub_fields'   => array(
					array(
						'key'   => 'field_hero_slide_image',
						'label' => 'Image du slide',
						'name'  => 'image',
						'type'  => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'required' => 1,
					),
					array(
						'key'   => 'field_hero_slide_eyebrow',
						'label' => 'Eyebrow (texte au-dessus)',
						'name'  => 'eyebrow',
						'type'  => 'text',
						'placeholder' => 'Nouvelle Collection',
						'required' => 1,
					),
					array(
						'key'   => 'field_hero_slide_title',
						'label' => 'Titre (autorise <br/>)',
						'name'  => 'title',
						'type'  => 'text',
						'placeholder' => "L'Élégance<br/>Minimaliste",
						'required' => 1,
					),
					array(
						'key'   => 'field_hero_slide_subtitle',
						'label' => 'Sous-titre',
						'name'  => 'subtitle',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_hero_slide_cta_text',
						'label' => 'Texte du bouton CTA',
						'name'  => 'cta_text',
						'type'  => 'text',
						'placeholder' => 'DÉCOUVRIR',
					),
					array(
						'key'   => 'field_hero_slide_cta_link',
						'label' => 'Lien du bouton CTA',
						'name'  => 'cta_link',
						'type'  => 'url',
						'placeholder' => '/boutique/',
					),
				),
			),
		),
		'location' => array(
			array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ),
		),
	) );

	/* ── 3B. Hero Static Blocks ── */
	acf_add_local_field_group( array(
		'key'      => 'group_velure_hero_static',
		'title'    => 'Hero — Blocs Statiques',
		'fields'   => array(
			// Best-Seller block
			array( 'key' => 'field_hs_bestseller_tab', 'label' => 'Bloc Best-Seller (haut droite)', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hs_bs_image', 'label' => 'Image', 'name' => 'hs_bestseller_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
			array( 'key' => 'field_hs_bs_label', 'label' => 'Label', 'name' => 'hs_bestseller_label', 'type' => 'text', 'default_value' => 'Best-Seller' ),
			array( 'key' => 'field_hs_bs_title', 'label' => 'Titre', 'name' => 'hs_bestseller_title', 'type' => 'text', 'default_value' => 'Sac Élégance' ),
			array( 'key' => 'field_hs_bs_price', 'label' => 'Prix affiché', 'name' => 'hs_bestseller_price', 'type' => 'text', 'default_value' => '285,00 €' ),
			array( 'key' => 'field_hs_bs_cta', 'label' => 'Texte bouton', 'name' => 'hs_bestseller_cta_text', 'type' => 'text', 'default_value' => 'VOIR LE PRODUIT' ),
			array( 'key' => 'field_hs_bs_link', 'label' => 'Lien', 'name' => 'hs_bestseller_cta_link', 'type' => 'url' ),
			// Category block
			array( 'key' => 'field_hs_category_tab', 'label' => 'Bloc Catégorie (bas droite)', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_hs_cat_image', 'label' => 'Image', 'name' => 'hs_category_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
			array( 'key' => 'field_hs_cat_label', 'label' => 'Label', 'name' => 'hs_category_label', 'type' => 'text', 'default_value' => 'Capsule' ),
			array( 'key' => 'field_hs_cat_title', 'label' => 'Titre', 'name' => 'hs_category_title', 'type' => 'text', 'default_value' => 'Les Accessoires Essentiels' ),
			array( 'key' => 'field_hs_cat_link', 'label' => 'Lien', 'name' => 'hs_category_cta_link', 'type' => 'url' ),
		),
		'location' => array(
			array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ),
		),
	) );

	/* ── 3C. Features / Trust Bar ── */
	acf_add_local_field_group( array(
		'key'      => 'group_velure_features',
		'title'    => 'Barre de Confiance',
		'fields'   => array(
			array(
				'key'          => 'field_velure_features_repeater',
				'label'        => 'Éléments de confiance',
				'name'         => 'trust_features',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Ajouter un élément',
				'min'          => 2,
				'max'          => 6,
				'sub_fields'   => array(
					array(
						'key'   => 'field_feat_icon',
						'label' => 'Icône SVG (code SVG brut)',
						'name'  => 'icon_svg',
						'type'  => 'textarea',
						'rows'  => 3,
						'instructions' => 'Collez le code <svg>...</svg> de l\'icône',
					),
					array(
						'key'   => 'field_feat_title',
						'label' => 'Titre',
						'name'  => 'title',
						'type'  => 'text',
						'required' => 1,
					),
					array(
						'key'   => 'field_feat_desc',
						'label' => 'Description',
						'name'  => 'description',
						'type'  => 'text',
					),
				),
			),
		),
		'location' => array(
			array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ),
		),
	) );

	/* ── 3D. Split Banner ── */
	acf_add_local_field_group( array(
		'key'      => 'group_velure_split_banner',
		'title'    => 'Bannière Séparée (split)',
		'fields'   => array(
			// Left side
			array( 'key' => 'field_sb_left_tab', 'label' => 'Bannière Gauche', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_sb_left_image', 'label' => 'Image', 'name' => 'sb_left_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
			array( 'key' => 'field_sb_left_eyebrow', 'label' => 'Eyebrow', 'name' => 'sb_left_eyebrow', 'type' => 'text' ),
			array( 'key' => 'field_sb_left_title', 'label' => 'Titre', 'name' => 'sb_left_title', 'type' => 'text' ),
			array( 'key' => 'field_sb_left_desc', 'label' => 'Description', 'name' => 'sb_left_desc', 'type' => 'textarea', 'rows' => 2 ),
			array( 'key' => 'field_sb_left_cta_text', 'label' => 'Texte bouton', 'name' => 'sb_left_cta_text', 'type' => 'text' ),
			array( 'key' => 'field_sb_left_cta_link', 'label' => 'Lien', 'name' => 'sb_left_cta_link', 'type' => 'url' ),
			array( 'key' => 'field_sb_left_cta_style', 'label' => 'Style bouton', 'name' => 'sb_left_cta_style', 'type' => 'select', 'choices' => array( 'gold' => 'Or (velure-btn-gold)', 'primary' => 'Sombre (velure-btn-primary)', 'outline' => 'Contour (velure-btn-outline)' ), 'default_value' => 'gold' ),
			// Right side
			array( 'key' => 'field_sb_right_tab', 'label' => 'Bannière Droite', 'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_sb_right_image', 'label' => 'Image', 'name' => 'sb_right_image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium' ),
			array( 'key' => 'field_sb_right_eyebrow', 'label' => 'Eyebrow', 'name' => 'sb_right_eyebrow', 'type' => 'text' ),
			array( 'key' => 'field_sb_right_title', 'label' => 'Titre', 'name' => 'sb_right_title', 'type' => 'text' ),
			array( 'key' => 'field_sb_right_desc', 'label' => 'Description', 'name' => 'sb_right_desc', 'type' => 'textarea', 'rows' => 2 ),
			array( 'key' => 'field_sb_right_cta_text', 'label' => 'Texte bouton', 'name' => 'sb_right_cta_text', 'type' => 'text' ),
			array( 'key' => 'field_sb_right_cta_link', 'label' => 'Lien', 'name' => 'sb_right_cta_link', 'type' => 'url' ),
			array( 'key' => 'field_sb_right_cta_style', 'label' => 'Style bouton', 'name' => 'sb_right_cta_style', 'type' => 'select', 'choices' => array( 'gold' => 'Or (velure-btn-gold)', 'primary' => 'Sombre (velure-btn-primary)', 'outline' => 'Contour (velure-btn-outline)' ), 'default_value' => 'outline' ),
		),
		'location' => array(
			array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ),
		),
	) );

	/* ── 3E. Brand Marquee ── */
	acf_add_local_field_group( array(
		'key'      => 'group_velure_marquee',
		'title'    => 'Marque — Bande défilante',
		'fields'   => array(
			array(
				'key'          => 'field_velure_brands_repeater',
				'label'        => 'Marques',
				'name'         => 'brand_names',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => 'Ajouter une marque',
				'sub_fields'   => array(
					array(
						'key'   => 'field_brand_name',
						'label' => 'Nom de la marque',
						'name'  => 'name',
						'type'  => 'text',
						'required' => 1,
					),
				),
			),
		),
		'location' => array(
			array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ),
		),
	) );

	/* ── 3F. Instagram ── */
	acf_add_local_field_group( array(
		'key'      => 'group_velure_instagram',
		'title'    => 'Instagram Feed',
		'fields'   => array(
			array( 'key' => 'field_ig_handle', 'label' => 'Handle Instagram', 'name' => 'instagram_handle', 'type' => 'text', 'placeholder' => '@velure.paris', 'instructions' => 'Affiché comme titre de section' ),
			array(
				'key'          => 'field_ig_images',
				'label'        => 'Images Instagram',
				'name'         => 'instagram_images',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => 'Ajouter une image',
				'max'          => 6,
				'sub_fields'   => array(
					array( 'key' => 'field_ig_img', 'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'preview_size' => 'thumbnail', 'required' => 1 ),
					array( 'key' => 'field_ig_link', 'label' => 'Lien (URL)', 'name' => 'link', 'type' => 'url' ),
				),
			),
		),
		'location' => array(
			array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ),
		),
	) );

	/* ── 3G. Section Titles ── */
	acf_add_local_field_group( array(
		'key'      => 'group_velure_section_titles',
		'title'    => 'Titres de Section',
		'fields'   => array(
			array( 'key' => 'field_st_categories', 'label' => 'Titre section Catégories', 'name' => 'section_title_categories', 'type' => 'text', 'default_value' => 'Explorer par Univers' ),
			array( 'key' => 'field_st_products', 'label' => 'Titre section Produits', 'name' => 'section_title_products', 'type' => 'text', 'default_value' => 'Pièces Vedettes' ),
			array( 'key' => 'field_st_testimonials', 'label' => 'Titre section Témoignages', 'name' => 'section_title_testimonials', 'type' => 'text', 'default_value' => 'Ce Que Disent Nos Clients' ),
			array( 'key' => 'field_st_blog', 'label' => 'Titre section Blog', 'name' => 'section_title_blog', 'type' => 'text', 'default_value' => 'Le Journal' ),
			array( 'key' => 'field_st_products_count', 'label' => 'Nombre de produits vedettes', 'name' => 'featured_products_count', 'type' => 'number', 'default_value' => 8, 'min' => 4, 'max' => 12 ),
			array( 'key' => 'field_st_blog_count', 'label' => 'Nombre d\'articles blog', 'name' => 'blog_posts_count', 'type' => 'number', 'default_value' => 3, 'min' => 2, 'max' => 6 ),
			array( 'key' => 'field_st_testimonials_count', 'label' => 'Nombre de témoignages', 'name' => 'testimonials_count', 'type' => 'number', 'default_value' => 3, 'min' => 1, 'max' => 6 ),
		),
		'location' => array(
			array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ),
		),
	) );
}


/* ═══════════════════════════════════════════════
   4. HELPER FUNCTIONS — Data with defaults
   ═══════════════════════════════════════════════ */

/**
 * Get image URL from ACF field (handles array/url/ID formats)
 */
function velure3_get_image_url( $img, $fallback = '' ) {
	if ( empty( $img ) ) return $fallback;
	if ( is_array( $img ) ) return isset( $img['url'] ) ? $img['url'] : $fallback;
	if ( is_numeric( $img ) ) return wp_get_attachment_url( $img ) ?: $fallback;
	return esc_url( $img );
}

/**
 * Get ACF option with fallback
 */
function velure3_opt( $key, $fallback = '' ) {
	if ( ! function_exists( 'get_field' ) ) return $fallback;
	$val = get_field( $key, 'option' );
	return $val !== null && $val !== '' && $val !== array() ? $val : $fallback;
}

/**
 * Default SVG icons for the trust/feature bar
 */
function velure3_default_feature_icons() {
	return array(
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2" ry="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
	);
}

/**
 * Get hero slides data (ACF or defaults)
 */
function velure3_get_hero_slides() {
	$slides = velure3_opt( 'hero_slides', array() );
	if ( ! empty( $slides ) ) {
		return array_map( function( $s ) {
			return array(
				'image'    => velure3_get_image_url( $s['image'] ),
				'eyebrow'  => $s['eyebrow'] ?? '',
				'title'    => $s['title'] ?? '',
				'subtitle' => $s['subtitle'] ?? '',
				'cta_text' => $s['cta_text'] ?? 'DÉCOUVRIR',
				'cta_link' => $s['cta_link'] ?? '#',
			);
		}, $slides );
	}
	// Defaults
	return array(
		array(
			'image'    => VELURE3_URI . '/assets/images/hero.jpg',
			'eyebrow'  => 'Nouvelle Collection',
			'title'    => "L'Élégance<br/>Minimaliste",
			'subtitle' => 'Nouvelle Collection Automne 2026',
			'cta_text' => 'DÉCOUVRIR',
			'cta_link' => '/boutique/',
		),
		array(
			'image'    => VELURE3_URI . '/assets/images/banner-collection.jpg',
			'eyebrow'  => 'Notre Engagement',
			'title'    => "Matières<br/>Durables",
			'subtitle' => 'Vêtements éthiques conçus pour durer',
			'cta_text' => 'NOTRE ENGAGEMENT',
			'cta_link' => '/notre-engagement/',
		),
	);
}

/**
 * Get hero static blocks data
 */
function velure3_get_hero_static_blocks() {
	return array(
		'bestseller' => array(
			'image'     => velure3_get_image_url( velure3_opt( 'hs_bestseller_image' ), VELURE3_URI . '/assets/images/category-femme.jpg' ),
			'label'     => velure3_opt( 'hs_bestseller_label', 'Best-Seller' ),
			'title'     => velure3_opt( 'hs_bestseller_title', 'Sac Élégance' ),
			'price'     => velure3_opt( 'hs_bestseller_price', '285,00 €' ),
			'cta_text'  => velure3_opt( 'hs_bestseller_cta_text', 'VOIR LE PRODUIT' ),
			'cta_link'  => velure3_opt( 'hs_bestseller_cta_link', '#' ),
		),
		'category' => array(
			'image'    => velure3_get_image_url( velure3_opt( 'hs_category_image' ), VELURE3_URI . '/assets/images/category-accessoires.jpg' ),
			'label'    => velure3_opt( 'hs_category_label', 'Capsule' ),
			'title'    => velure3_opt( 'hs_category_title', 'Les Accessoires Essentiels' ),
			'cta_link' => velure3_opt( 'hs_category_cta_link', '/categorie/accessoires/' ),
		),
	);
}

/**
 * Get trust/features bar data
 */
function velure3_get_features() {
	$features = velure3_opt( 'trust_features', array() );
	if ( ! empty( $features ) ) {
		return array_map( function( $f ) {
			return array(
				'icon'   => $f['icon_svg'] ?? '',
				'title'  => $f['title'] ?? '',
				'desc'   => $f['description'] ?? '',
			);
		}, $features );
	}
	// Defaults
	return array(
		array( 'icon' => velure3_default_feature_icons()[0], 'title' => 'Livraison Express', 'desc' => 'Sous 24-48h en France' ),
		array( 'icon' => velure3_default_feature_icons()[1], 'title' => 'Retours Gratuits', 'desc' => 'Sous 30 jours, sans frais' ),
		array( 'icon' => velure3_default_feature_icons()[2], 'title' => 'Paiement Sécurisé', 'desc' => 'SSL & cryptage 256 bits' ),
		array( 'icon' => velure3_default_feature_icons()[3], 'title' => 'Service Client 7j/7', 'desc' => 'Par chat, email ou téléphone' ),
	);
}

/**
 * Get WooCommerce product categories for carousel
 */
function velure3_get_product_categories( $count = 10 ) {
	if ( ! class_exists( 'WooCommerce' ) ) return array();

	$default_cat = (int) get_option( 'default_product_cat', 0 );
	$terms = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => $default_cat,
		'number'     => $count,
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) return array();

	return array_map( function( $term ) {
		$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		$image_url = $thumbnail_id ? wp_get_attachment_url( $thumbnail_id ) : VELURE3_URI . '/assets/images/category-femme.jpg';
		return array(
			'name'  => $term->name,
			'slug'  => $term->slug,
			'link'  => get_term_link( $term ),
			'image' => $image_url,
			'count' => $term->count,
		);
	}, $terms );
}

/**
 * Get featured WooCommerce products
 */
function velure3_get_featured_products( $count = 8 ) {
	if ( ! class_exists( 'WooCommerce' ) ) return array();

	$args = array(
		'limit'   => $count,
		'visibility' => 'visible',
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'objects',
	);

	$products = wc_get_products( $args );
	if ( empty( $products ) ) return array();

	return array_map( function( $product ) {
		$image_id   = $product->get_image_id();
		$image_url  = $image_id ? wp_get_attachment_url( $image_id ) : '';
		$price_html = $product->get_price_html();
		$name       = $product->get_name();
		$link       = $product->get_permalink();
		$badge      = '';

		if ( $product->is_on_sale() ) {
			$badge = '<span class="velure-product-card-badge sale">' . __( 'Promo', 'velure3' ) . '</span>';
		} elseif ( $product->is_featured() ) {
			$badge = '<span class="velure-product-card-badge new">' . __( 'Nouveau', 'velure3' ) . '</span>';
		}

		return array(
			'name'        => $name,
			'link'        => $link,
			'image'       => $image_url,
			'price_html'  => $price_html,
			'badge'       => $badge,
		);
	}, $products );
}

/**
 * Get split banner data
 */
function velure3_get_split_banner() {
	return array(
		'left' => array(
			'image'      => velure3_get_image_url( velure3_opt( 'sb_left_image' ), VELURE3_URI . '/assets/images/banner-collection.jpg' ),
			'eyebrow'    => velure3_opt( 'sb_left_eyebrow', 'Collection AW25' ),
			'title'      => velure3_opt( 'sb_left_title', 'La Nouvelle Collection' ),
			'desc'       => velure3_opt( 'sb_left_desc', 'Des silhouettes audacieuses et des matières nobles pour une saison inoubliable.' ),
			'cta_text'   => velure3_opt( 'sb_left_cta_text', 'Découvrir' ),
			'cta_link'   => velure3_opt( 'sb_left_cta_link', '/new-collection/' ),
			'cta_style'  => velure3_opt( 'sb_left_cta_style', 'gold' ),
		),
		'right' => array(
			'image'      => velure3_get_image_url( velure3_opt( 'sb_right_image' ), VELURE3_URI . '/assets/images/category-accessoires.jpg' ),
			'eyebrow'    => velure3_opt( 'sb_right_eyebrow', 'Édition Limitée' ),
			'title'      => velure3_opt( 'sb_right_title', 'Accessoires d\'Exception' ),
			'desc'       => velure3_opt( 'sb_right_desc', 'Sacs, bijoux et ceintures signés par les meilleurs artisans.' ),
			'cta_text'   => velure3_opt( 'sb_right_cta_text', 'Explorer' ),
			'cta_link'   => velure3_opt( 'sb_right_cta_link', '/categorie/accessoires/' ),
			'cta_style'  => velure3_opt( 'sb_right_cta_style', 'outline' ),
		),
	);
}

/**
 * Get testimonials
 */
function velure3_get_testimonials( $count = 3 ) {
	$posts = get_posts( array(
		'numberposts' => $count,
		'post_type'   => 'velure_testimonial',
		'post_status' => 'publish',
		'orderby'     => 'date',
		'order'       => 'DESC',
	) );

	if ( ! empty( $posts ) ) {
		return array_map( function( $p ) {
			return array(
				'text'  => wp_trim_words( $p->post_content, 40, '...' ),
				'author' => get_the_title( $p ),
				'role'  => get_post_meta( $p->ID, '_velure_role', true ),
				'stars' => (int) get_post_meta( $p->ID, '_velure_stars', true ) ?: 5,
			);
		}, $posts );
	}

	// Defaults
	return array(
		array( 'text' => 'La qualité des matières est exceptionnelle. Mon manteau en cachemire est devenu ma pièce préférée. Le service client est aussi remarquable — toujours à l\'écoute.', 'author' => 'Camille D.', 'role' => 'Cliente fidèle depuis 2023', 'stars' => 5 ),
		array( 'text' => 'J\'ai commandé un blazer pour un événement et j\'ai été bluffée par la coupe et la finition. La livraison était rapide et l\'emballage impeccable. Je recommande vivement.', 'author' => 'Antoine M.', 'role' => 'Achat vérifié', 'stars' => 5 ),
		array( 'text' => 'Velure est devenue ma référence mode. Les pièces sont intemporelles et la taille guide est très fiable. Les retours sont faciles quand nécessaire. Un concept store en ligne parfait.', 'author' => 'Sophie L.', 'role' => 'Cliente Premium', 'stars' => 5 ),
	);
}

/**
 * Get brand names for marquee
 */
function velure3_get_brands() {
	$brands = velure3_opt( 'brand_names', array() );
	if ( ! empty( $brands ) ) {
		return array_map( function( $b ) {
			return strtoupper( $b['name'] ?? '' );
		}, $brands );
	}
	return array( 'ELIE SAAB', 'VALENTINO', 'BOTTEGA VENETA', 'SAINT LAURENT', 'ACNE STUDIOS', 'THE ROW', 'LOEWE', 'RICK OWENS' );
}

/**
 * Get blog posts
 */
function velure3_get_blog_posts( $count = 3 ) {
	$posts = get_posts( array(
		'numberposts' => $count,
		'post_type'   => 'post',
		'post_status' => 'publish',
		'orderby'     => 'date',
		'order'       => 'DESC',
	) );

	if ( empty( $posts ) ) return array();

	return array_map( function( $p ) {
		$image_id = get_post_thumbnail_id( $p->ID );
		$image_url = $image_id ? wp_get_attachment_url( $image_id ) : '';
		$categories = get_the_category( $p->ID );
		$cat_name = ! empty( $categories ) ? $categories[0]->name : '';
		return array(
			'title'   => get_the_title( $p ),
			'excerpt' => wp_trim_words( $p->post_excerpt ?: $p->post_content, 20 ),
			'link'    => get_permalink( $p ),
			'image'   => $image_url,
			'date'    => get_the_date( 'd M Y', $p ),
			'category'=> $cat_name,
		);
	}, $posts );
}

/**
 * Get Instagram images
 */
function velure3_get_instagram_items() {
	$items = velure3_opt( 'instagram_images', array() );
	if ( ! empty( $items ) ) {
		return array_map( function( $item ) {
			return array(
				'image' => velure3_get_image_url( $item['image'] ),
				'link'  => $item['link'] ?: 'https://instagram.com/',
			);
		}, $items );
	}
	// Defaults — local images
	$defaults = array();
	for ( $i = 1; $i <= 6; $i++ ) {
		$defaults[] = array(
			'image' => VELURE3_URI . '/assets/images/instagram-0' . $i . '.jpg',
			'link'  => 'https://instagram.com/',
		);
	}
	return $defaults;
}

/**
 * Render a single star SVG (for testimonials)
 */
function velure3_star_svg() {
	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="#c9a96e"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
}