<?php
/**
 * Velure3 — Dynamic Content Fields
 * Registers CPT, ACF Options Page, and ACF Field Groups
 * for the database-driven front page.
 *
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════
   1. TESTIMONIAL CPT
   ═══════════════════════════════════════════════ */
add_action( 'init', 'velure3_register_testimonial_cpt' );
function velure3_register_testimonial_cpt() {
	register_post_type( 'velure_testimonial', array(
		'labels' => array(
			'name'               => 'Temoignages',
			'singular_name'      => 'Temoignage',
			'menu_name'          => 'Temoignages',
			'add_new'            => 'Ajouter un temoignage',
			'add_new_item'       => 'Ajouter un temoignage',
			'edit_item'          => 'Modifier le temoignage',
			'view_item'          => 'Voir le temoignage',
			'all_items'          => 'Tous les temoignages',
			'search_items'       => 'Rechercher des temoignages',
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'supports'      => array( 'title', 'editor' ),
		'menu_icon'     => 'dashicons-format-quote',
		'capability_type' => 'post',
	) );
}

/* Testimonial meta boxes */
add_action( 'add_meta_boxes', 'velure3_testimonial_meta_boxes' );
function velure3_testimonial_meta_boxes() {
	add_meta_box( 'velure_testimonial_details', 'Details du temoignage', 'velure3_testimonial_meta_box_callback', 'velure_testimonial', 'normal', 'high' );
}

function velure3_testimonial_meta_box_callback( $post ) {
	wp_nonce_field( 'velure3_testimonial_nonce', 'velure3_testimonial_nonce_field' );
	$stars = get_post_meta( $post->ID, '_velure_stars', true ) ?: 5;
	$role  = get_post_meta( $post->ID, '_velure_role', true );
	?>
	<p>
		<label><strong>Note (etoiles) :</strong></label><br/>
		<select name="_velure_stars" style="width:100%;max-width:120px;margin-top:4px;">
			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<option value="<?php echo $i; ?>" <?php selected( $stars, $i ); ?>><?php echo $i; ?> etoile<?php echo $i > 1 ? 's' : ''; ?></option>
			<?php endfor; ?>
		</select>
	</p>
	<p>
		<label><strong>Role / Statut :</strong></label><br/>
		<input type="text" name="_velure_role" value="<?php echo esc_attr( $role ); ?>" style="width:100%;margin-top:4px;" placeholder="Ex : Cliente fidele depuis 2023" />
	</p>
	<?php
}

add_action( 'save_post_velure_testimonial', 'velure3_save_testimonial_meta', 10, 2 );
function velure3_save_testimonial_meta( $post_id, $post ) {
	if ( ! isset( $_POST['velure3_testimonial_nonce_field'] ) || ! wp_verify_nonce( $_POST['velure3_testimonial_nonce_field'], 'velure3_testimonial_nonce' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	foreach ( array( '_velure_stars', '_velure_role' ) as $field ) {
		if ( isset( $_POST[ $field ] ) ) update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
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
   3. ACF FIELD GROUPS
   ═══════════════════════════════════════════════ */
add_action( 'acf/init', 'velure3_register_acf_field_groups' );
function velure3_register_acf_field_groups() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;
	$loc = array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'velure-front-page' ) ) );

	/* Hero Slides */
	acf_add_local_field_group( array(
		'key'    => 'group_velure_hero_slides',
		'title'  => 'Hero — Slides',
		'fields' => array(
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
					array( 'key' => 'field_hero_slide_image',    'label' => 'Image',    'name' => 'image',    'type' => 'image', 'return_format' => 'array', 'preview_size' => 'medium', 'required' => 1 ),
					array( 'key' => 'field_hero_slide_eyebrow',  'label' => 'Eyebrow',  'name' => 'eyebrow',  'type' => 'text',  'placeholder' => 'Nouvelle Collection', 'required' => 1 ),
					array( 'key' => 'field_hero_slide_title',    'label' => 'Titre',    'name' => 'title',    'type' => 'text',  'placeholder' => "L'Elegance Minimaliste" ),
					array( 'key' => 'field_hero_slide_subtitle', 'label' => 'Sous-titre','name' => 'subtitle', 'type' => 'text' ),
					array( 'key' => 'field_hero_slide_cta_text', 'label' => 'Texte bouton','name' => 'cta_text', 'type' => 'text', 'placeholder' => 'DECOUVRIR' ),
					array( 'key' => 'field_hero_slide_cta_link', 'label' => 'Lien bouton','name' => 'cta_link', 'type' => 'url',   'placeholder' => '/boutique/' ),
				),
			),
		),
		'location' => $loc,
	) );

	/* Features Bar */
	acf_add_local_field_group( array(
		'key'    => 'group_velure_features',
		'title'  => 'Barre de Confiance',
		'fields' => array(
			array(
				'key'          => 'field_velure_features_repeater',
				'label'        => 'Elements de confiance',
				'name'         => 'trust_features',
				'type'         => 'repeater',
				'layout'       => 'table',
				'button_label' => 'Ajouter un element',
				'min'          => 2,
				'max'          => 6,
				'sub_fields'   => array(
					array( 'key' => 'field_feat_icon', 'label' => 'Icone SVG',   'name' => 'icon_svg',   'type' => 'textarea', 'rows' => 3, 'instructions' => 'Collez le code <svg>...</svg>' ),
					array( 'key' => 'field_feat_title','label' => 'Titre',       'name' => 'title',      'type' => 'text',    'required' => 1 ),
					array( 'key' => 'field_feat_desc', 'label' => 'Description', 'name' => 'description','type' => 'text' ),
				),
			),
		),
		'location' => $loc,
	) );

	/* Split Banner */
	acf_add_local_field_group( array(
		'key'    => 'group_velure_split_banner',
		'title'  => 'Banniere Separee (split)',
		'fields' => array(
			array( 'key' => 'field_sb_left_tab',       'label' => 'Banniere Gauche',  'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_sb_left_image',      'label' => 'Image',           'name' => 'sb_left_image',      'type' => 'image', 'return_format' => 'array' ),
			array( 'key' => 'field_sb_left_eyebrow',    'label' => 'Eyebrow',         'name' => 'sb_left_eyebrow',    'type' => 'text' ),
			array( 'key' => 'field_sb_left_title',      'label' => 'Titre',           'name' => 'sb_left_title',      'type' => 'text' ),
			array( 'key' => 'field_sb_left_desc',       'label' => 'Description',     'name' => 'sb_left_desc',       'type' => 'textarea', 'rows' => 2 ),
			array( 'key' => 'field_sb_left_cta_text',   'label' => 'Texte bouton',    'name' => 'sb_left_cta_text',   'type' => 'text' ),
			array( 'key' => 'field_sb_left_cta_link',   'label' => 'Lien',            'name' => 'sb_left_cta_link',   'type' => 'url' ),
			array( 'key' => 'field_sb_left_cta_style',  'label' => 'Style bouton',    'name' => 'sb_left_cta_style',  'type' => 'select', 'choices' => array( 'gold' => 'Or', 'primary' => 'Sombre', 'outline' => 'Contour' ), 'default_value' => 'gold' ),
			array( 'key' => 'field_sb_right_tab',      'label' => 'Banniere Droite',  'name' => '', 'type' => 'tab' ),
			array( 'key' => 'field_sb_right_image',     'label' => 'Image',           'name' => 'sb_right_image',     'type' => 'image', 'return_format' => 'array' ),
			array( 'key' => 'field_sb_right_eyebrow',   'label' => 'Eyebrow',         'name' => 'sb_right_eyebrow',   'type' => 'text' ),
			array( 'key' => 'field_sb_right_title',     'label' => 'Titre',           'name' => 'sb_right_title',     'type' => 'text' ),
			array( 'key' => 'field_sb_right_desc',      'label' => 'Description',     'name' => 'sb_right_desc',      'type' => 'textarea', 'rows' => 2 ),
			array( 'key' => 'field_sb_right_cta_text',  'label' => 'Texte bouton',    'name' => 'sb_right_cta_text',  'type' => 'text' ),
			array( 'key' => 'field_sb_right_cta_link',  'label' => 'Lien',            'name' => 'sb_right_cta_link',  'type' => 'url' ),
			array( 'key' => 'field_sb_right_cta_style', 'label' => 'Style bouton',    'name' => 'sb_right_cta_style', 'type' => 'select', 'choices' => array( 'gold' => 'Or', 'primary' => 'Sombre', 'outline' => 'Contour' ), 'default_value' => 'outline' ),
		),
		'location' => $loc,
	) );

	/* Instagram */
	acf_add_local_field_group( array(
		'key'    => 'group_velure_instagram',
		'title'  => 'Instagram Feed',
		'fields' => array(
			array( 'key' => 'field_ig_handle', 'label' => 'Handle Instagram', 'name' => 'instagram_handle', 'type' => 'text', 'placeholder' => '@velure.paris' ),
			array(
				'key'          => 'field_ig_images',
				'label'        => 'Images Instagram',
				'name'         => 'instagram_images',
				'type'         => 'repeater',
				'layout'       => 'row',
				'button_label' => 'Ajouter une image',
				'max'          => 6,
				'sub_fields'   => array(
					array( 'key' => 'field_ig_img',  'label' => 'Image', 'name' => 'image', 'type' => 'image', 'return_format' => 'array', 'required' => 1 ),
					array( 'key' => 'field_ig_link', 'label' => 'Lien',  'name' => 'link',  'type' => 'url' ),
				),
			),
		),
		'location' => $loc,
	) );

	/* Section Titles */
	acf_add_local_field_group( array(
		'key'    => 'group_velure_section_titles',
		'title'  => 'Titres de Section',
		'fields' => array(
			array( 'key' => 'field_st_categories',       'label' => 'Titre Categories',    'name' => 'section_title_categories',    'type' => 'text',   'default_value' => 'Explorer par Univers' ),
			array( 'key' => 'field_st_products',         'label' => 'Titre Produits',      'name' => 'section_title_products',        'type' => 'text',   'default_value' => 'Pieces Vedettes' ),
			array( 'key' => 'field_st_testimonials',     'label' => 'Titre Temoignages',   'name' => 'section_title_testimonials',    'type' => 'text',   'default_value' => 'Ce Que Disent Nos Clients' ),
			array( 'key' => 'field_st_blog',             'label' => 'Titre Blog',          'name' => 'section_title_blog',            'type' => 'text',   'default_value' => 'Le Journal' ),
			array( 'key' => 'field_st_products_count',   'label' => 'Nb produits vedettes','name' => 'featured_products_count',       'type' => 'number', 'default_value' => 8, 'min' => 4, 'max' => 12 ),
			array( 'key' => 'field_st_blog_count',       'label' => 'Nb articles blog',    'name' => 'blog_posts_count',              'type' => 'number', 'default_value' => 3, 'min' => 2, 'max' => 6 ),
			array( 'key' => 'field_st_testimonials_count','label' => 'Nb temoignages',     'name' => 'testimonials_count',            'type' => 'number', 'default_value' => 3, 'min' => 1, 'max' => 6 ),
		),
		'location' => $loc,
	) );
}

/* ═══════════════════════════════════════════════
   4. HELPER FUNCTIONS
   ═══════════════════════════════════════════════ */

function velure3_get_image_url( $img, $fallback = '' ) {
	if ( empty( $img ) ) return $fallback;
	if ( is_array( $img ) ) return isset( $img['url'] ) ? $img['url'] : $fallback;
	if ( is_numeric( $img ) ) return wp_get_attachment_url( $img ) ?: $fallback;
	return esc_url( $img );
}

function velure3_opt( $key, $fallback = '' ) {
	if ( ! function_exists( 'get_field' ) ) return $fallback;
	$val = get_field( $key, 'option' );
	return ( $val !== null && $val !== '' && $val !== array() ) ? $val : $fallback;
}

function velure3_default_feature_icons() {
	return array(
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2" ry="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
		'<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
	);
}

function velure3_get_hero_slides() {
	$slides = velure3_opt( 'hero_slides', array() );
	if ( ! empty( $slides ) ) {
		return array_map( function( $s ) {
			return array(
				'image'    => velure3_get_image_url( $s['image'] ),
				'eyebrow'  => $s['eyebrow'] ?? '',
				'title'    => $s['title'] ?? '',
				'subtitle' => $s['subtitle'] ?? '',
				'cta_text' => $s['cta_text'] ?? 'DECOUVRIR',
				'cta_link' => $s['cta_link'] ?? '#',
			);
		}, $slides );
	}
	return array(
		array( 'image' => VELURE3_URI . '/assets/images/hero.jpg', 'eyebrow' => 'Nouvelle Collection', 'title' => "L'Elegance<br/>Minimaliste", 'subtitle' => 'Nouvelle Collection Automne 2026', 'cta_text' => 'DECOUVRIR', 'cta_link' => '/boutique/' ),
		array( 'image' => VELURE3_URI . '/assets/images/banner-collection.jpg', 'eyebrow' => 'Notre Engagement', 'title' => "Matieres<br/>Durables", 'subtitle' => 'Vetements ethiques concus pour durer', 'cta_text' => 'NOTRE ENGAGEMENT', 'cta_link' => '/notre-engagement/' ),
	);
}

function velure3_get_features() {
	$features = velure3_opt( 'trust_features', array() );
	if ( ! empty( $features ) ) {
		return array_map( function( $f ) {
			return array( 'icon' => $f['icon_svg'] ?? '', 'title' => $f['title'] ?? '', 'desc' => $f['description'] ?? '' );
		}, $features );
	}
	return array(
		array( 'icon' => velure3_default_feature_icons()[0], 'title' => 'Livraison Express', 'desc' => 'Sous 24-48h en France' ),
		array( 'icon' => velure3_default_feature_icons()[1], 'title' => 'Retours Gratuits', 'desc' => 'Sous 30 jours, sans frais' ),
		array( 'icon' => velure3_default_feature_icons()[2], 'title' => 'Paiement Securise', 'desc' => 'SSL & cryptage 256 bits' ),
		array( 'icon' => velure3_default_feature_icons()[3], 'title' => 'Service Client 7j/7', 'desc' => 'Par chat, email ou telephone' ),
	);
}

function velure3_get_product_categories( $count = 10 ) {
	if ( class_exists( 'WooCommerce' ) ) {
		$default_cat = (int) get_option( 'default_product_cat', 0 );
		$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'exclude' => $default_cat, 'number' => $count, 'orderby' => 'count', 'order' => 'DESC' ) );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			return array_map( function( $term ) {
				$tid = get_term_meta( $term->term_id, 'thumbnail_id', true );
				return array( 'name' => $term->name, 'slug' => $term->slug, 'link' => get_term_link( $term ), 'image' => $tid ? wp_get_attachment_url( $tid ) : VELURE3_URI . '/assets/images/category-femme.jpg', 'count' => $term->count );
			}, $terms );
		}
	}
	return array(
		array( 'name' => 'Femme',       'slug' => 'femme',       'link' => home_url( '/categorie-femme/' ),       'image' => VELURE3_URI . '/assets/images/category-femme.jpg',       'count' => 0 ),
		array( 'name' => 'Homme',       'slug' => 'homme',       'link' => home_url( '/categorie-homme/' ),       'image' => VELURE3_URI . '/assets/images/category-homme.jpg',       'count' => 0 ),
		array( 'name' => 'Accessoires',  'slug' => 'accessoires',  'link' => home_url( '/categorie/accessoires/' ),  'image' => VELURE3_URI . '/assets/images/category-accessoires.jpg','count' => 0 ),
		array( 'name' => 'Chaussures',   'slug' => 'chaussures',   'link' => home_url( '/categorie/chaussures/' ),   'image' => VELURE3_URI . '/assets/images/category-chaussures.jpg', 'count' => 0 ),
		array( 'name' => 'Maroquinerie', 'slug' => 'maroquinerie', 'link' => home_url( '/categorie/maroquinerie/' ), 'image' => VELURE3_URI . '/assets/images/category-maroquinerie.jpg','count' => 0 ),
		array( 'name' => 'Sportswear',   'slug' => 'sportswear',   'link' => home_url( '/categorie/sportswear/' ),   'image' => VELURE3_URI . '/assets/images/category-sportswear.jpg', 'count' => 0 ),
	);
}

function velure3_get_featured_products( $count = 8 ) {
	if ( class_exists( 'WooCommerce' ) ) {
		$products = wc_get_products( array( 'limit' => $count, 'visibility' => 'visible', 'orderby' => 'date', 'order' => 'DESC', 'return' => 'objects' ) );
		if ( ! empty( $products ) ) {
			return array_map( function( $p ) {
				$img_id = $p->get_image_id();
				$badge = '';
				if ( $p->is_on_sale() ) $badge = '<span class="velure-product-card-badge sale">Promo</span>';
				elseif ( $p->is_featured() ) $badge = '<span class="velure-product-card-badge new">Nouveau</span>';
				return array( 'name' => $p->get_name(), 'link' => $p->get_permalink(), 'image' => $img_id ? wp_get_attachment_url( $img_id ) : '', 'price_html' => $p->get_price_html(), 'badge' => $badge );
			}, $products );
		}
	}
	$demo = array(
		array( 'name' => 'Manteau Camel',  'price' => '485,00 &euro;', 'badge' => '<span class="velure-product-card-badge new">Nouveau</span>', 'img' => 'product-manteau-camel.jpg' ),
		array( 'name' => 'Blazer Ivoire',   'price' => '320,00 &euro;', 'badge' => '', 'img' => 'product-blazer-ivoire.jpg' ),
		array( 'name' => 'Robe Noire',     'price' => '275,00 &euro;', 'badge' => '', 'img' => 'product-robe-noire.jpg' ),
		array( 'name' => 'Pull Beige',      'price' => '145,00 &euro;', 'badge' => '<span class="velure-product-card-badge sale">Promo</span>', 'img' => 'product-pull-beige.jpg' ),
		array( 'name' => 'Pantalon Gris',   'price' => '195,00 &euro;', 'badge' => '', 'img' => 'product-pantalon-gris.jpg' ),
		array( 'name' => 'Sac Cuir',        'price' => '385,00 &euro;', 'badge' => '', 'img' => 'product-sac-cuir.jpg' ),
		array( 'name' => 'Bottines Brun',   'price' => '265,00 &euro;', 'badge' => '<span class="velure-product-card-badge new">Nouveau</span>', 'img' => 'product-bottines-brun.jpg' ),
		array( 'name' => 'Chemise Blanc',   'price' => '125,00 &euro;', 'badge' => '', 'img' => 'product-chemise-blanc.jpg' ),
	);
	return array_map( function( $p ) {
		return array( 'name' => $p['name'], 'link' => home_url( '/boutique/' ), 'image' => VELURE3_URI . '/assets/images/' . $p['img'], 'price_html' => $p['price'], 'badge' => $p['badge'] );
	}, array_slice( $demo, 0, $count ) );
}

function velure3_get_split_banner() {
	return array(
		'left' => array(
			'image'     => velure3_get_image_url( velure3_opt( 'sb_left_image' ), VELURE3_URI . '/assets/images/banner-collection.jpg' ),
			'eyebrow'   => velure3_opt( 'sb_left_eyebrow', 'Collection AW25' ),
			'title'     => velure3_opt( 'sb_left_title', 'La Nouvelle Collection' ),
			'desc'      => velure3_opt( 'sb_left_desc', 'Des silhouettes audacieuses et des matieres nobles pour une saison inoubliable.' ),
			'cta_text'  => velure3_opt( 'sb_left_cta_text', 'Decouvrir' ),
			'cta_link'  => velure3_opt( 'sb_left_cta_link', '/new-collection/' ),
			'cta_style' => velure3_opt( 'sb_left_cta_style', 'gold' ),
		),
		'right' => array(
			'image'     => velure3_get_image_url( velure3_opt( 'sb_right_image' ), VELURE3_URI . '/assets/images/category-accessoires.jpg' ),
			'eyebrow'   => velure3_opt( 'sb_right_eyebrow', 'Edition Limitee' ),
			'title'     => velure3_opt( 'sb_right_title', 'Accessoires d\'Exception' ),
			'desc'      => velure3_opt( 'sb_right_desc', 'Sacs, bijoux et ceintures signes par les meilleurs artisans.' ),
			'cta_text'  => velure3_opt( 'sb_right_cta_text', 'Explorer' ),
			'cta_link'  => velure3_opt( 'sb_right_cta_link', '/categorie/accessoires/' ),
			'cta_style' => velure3_opt( 'sb_right_cta_style', 'outline' ),
		),
	);
}

function velure3_get_testimonials( $count = 3 ) {
	$posts = get_posts( array( 'numberposts' => $count, 'post_type' => 'velure_testimonial', 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( ! empty( $posts ) ) {
		return array_map( function( $p ) {
			return array( 'text' => wp_trim_words( $p->post_content, 40, '...' ), 'author' => get_the_title( $p ), 'role' => get_post_meta( $p->ID, '_velure_role', true ), 'stars' => (int) get_post_meta( $p->ID, '_velure_stars', true ) ?: 5 );
		}, $posts );
	}
	return array(
		array( 'text' => 'La qualite des matieres est exceptionnelle. Mon manteau en cachemire est devenu ma piece preferee. Le service client est aussi remarquable.', 'author' => 'Camille D.', 'role' => 'Cliente fidele depuis 2023', 'stars' => 5 ),
		array( 'text' => 'J\'ai commande un blazer pour un evenement et j\'ai ete bluffee par la coupe et la finition. La livraison etait rapide et l\'emballage impeccable.', 'author' => 'Antoine M.', 'role' => 'Achat verifie', 'stars' => 5 ),
		array( 'text' => 'Velure est devenue ma reference mode. Les pieces sont intemporelles et la taille guide est tres fiable. Un concept store en ligne parfait.', 'author' => 'Sophie L.', 'role' => 'Cliente Premium', 'stars' => 5 ),
	);
}

function velure3_get_brands() {
	$brands = velure3_opt( 'brand_names', array() );
	if ( ! empty( $brands ) ) return array_map( function( $b ) { return strtoupper( $b['name'] ?? '' ); }, $brands );
	return array( 'ELIE SAAB', 'VALENTINO', 'BOTTEGA VENETA', 'SAINT LAURENT', 'ACNE STUDIOS', 'THE ROW', 'LOEWE', 'RICK OWENS' );
}

function velure3_get_blog_posts( $count = 3 ) {
	$posts = get_posts( array( 'numberposts' => $count, 'post_type' => 'post', 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( ! empty( $posts ) ) {
		return array_map( function( $p ) {
			$img_id = get_post_thumbnail_id( $p->ID );
			$cats = get_the_category( $p->ID );
			return array( 'title' => get_the_title( $p ), 'excerpt' => wp_trim_words( $p->post_excerpt ?: $p->post_content, 20 ), 'link' => get_permalink( $p ), 'image' => $img_id ? wp_get_attachment_url( $img_id ) : '', 'date' => get_the_date( 'd M Y', $p ), 'category' => ! empty( $cats ) ? $cats[0]->name : '' );
		}, $posts );
	}
	$demo = array(
		array( 'title' => 'Tendances Automne/Hiver 2025', 'category' => 'Tendances', 'date' => '15 Jan 2025', 'excerpt' => 'Les couleurs, coupes et matieres qui dominent cette saison.', 'img' => 'blog-tendances-aw25.jpg' ),
		array( 'title' => 'Capsule Garderobe Ideale',     'category' => 'Style',      'date' => '8 Jan 2025',  'excerpt' => 'Construisez une garde-robe capsule de 15 pieces qui couvrent toutes les occasions.', 'img' => 'blog-capsule-garderobe.jpg' ),
		array( 'title' => 'Entretien du Cachemire',        'category' => 'Entretien',  'date' => '2 Jan 2025',  'excerpt' => 'Nos conseils d\'experts pour laver, secher et ranger vos pieces en cachemire.', 'img' => 'blog-cachemire-entretien.jpg' ),
	);
	return array_map( function( $p ) {
		return array( 'title' => $p['title'], 'excerpt' => $p['excerpt'], 'link' => home_url( '/blog/' ), 'image' => VELURE3_URI . '/assets/images/' . $p['img'], 'date' => $p['date'], 'category' => $p['category'] );
	}, array_slice( $demo, 0, $count ) );
}

function velure3_get_instagram_items() {
	$items = velure3_opt( 'instagram_images', array() );
	if ( ! empty( $items ) ) {
		return array_map( function( $item ) {
			return array( 'image' => velure3_get_image_url( $item['image'] ), 'link' => $item['link'] ?: 'https://instagram.com/' );
		}, $items );
	}
	$defaults = array();
	for ( $i = 1; $i <= 6; $i++ ) {
		$defaults[] = array( 'image' => VELURE3_URI . '/assets/images/instagram-0' . $i . '.jpg', 'link' => 'https://instagram.com/' );
	}
	return $defaults;
}

function velure3_star_svg() {
	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="#c9a96e"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
}