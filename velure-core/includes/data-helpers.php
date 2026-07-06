<?php
/**
 * Velure Core — Data Helpers & Fallbacks
 * Toutes les fonctions de recuperation de donnees utilisees par le theme.
 * Lit depuis wp_options (VELURE_CORE_OPTION) — aucune dependance ACF.
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════
   1. GENERIC HELPERS
   ═══════════════════════════════════════════════ */

/**
 * Recuperer l'URL d'une image (ID entier ou URL brute).
 */
function velure_core_get_image_url( $img, $fallback = '' ) {
	if ( empty( $img ) ) return $fallback;
	if ( is_array( $img ) ) return isset( $img['url'] ) ? $img['url'] : $fallback;
	if ( is_numeric( $img ) ) return wp_get_attachment_url( (int) $img ) ?: $fallback;
	return esc_url( $img );
}

/**
 * Lire un parametre Velure Core avec fallback.
 * Lit depuis l'option wp velure_core_settings.
 */
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

/**
 * Lire un bool Velure Core avec fallback.
 */
function velure_core_opt_bool( $key, $fallback = true ) {
	static $settings = null;
	if ( null === $settings ) {
		$settings = get_option( VELURE_CORE_OPTION, array() );
		if ( ! is_array( $settings ) ) $settings = array();
	}
	if ( ! isset( $settings[ $key ] ) ) return $fallback;
	return (bool) $settings[ $key ];
}

/**
 * SVG whitelist pour wp_kses.
 */
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

/**
 * Map ACF bg style key to CSS class.
 */
function velure_core_bg_class( $style ) {
	$map = array(
		'soft'      => 'velure-section-soft',
		'muted'     => 'velure-section-muted',
		'dark'      => 'velure-section-dark',
		'secondary' => 'velure-section-secondary',
		'base'      => '',
	);
	return isset( $map[ $style ] ) ? $map[ $style ] : '';
}

/**
 * Star SVG HTML.
 */
function velure_core_star_svg() {
	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="#c9a96e"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
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
	$default_order = array( 'hero', 'features', 'categories', 'products', 'split_banner', 'marquee', 'testimonials', 'blog', 'instagram' );
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
   4. SECTION DATA FUNCTIONS (with fallbacks)
   ═══════════════════════════════════════════════ */

function velure_core_theme_uri() {
	return defined( 'VELURE3_URI' ) ? VELURE3_URI : '';
}

/* ── 4A. HERO ── */
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
			array( 'image' => $uri . '/assets/images/hero.jpg', 'eyebrow' => 'Nouvelle Collection', 'title' => "L'Elegance<br/>Minimaliste", 'subtitle' => 'Nouvelle Collection Automne 2026', 'cta_text' => 'DECOUVRIR', 'cta_link' => '/boutique/', 'cta_style' => 'primary' ),
			array( 'image' => $uri . '/assets/images/banner-collection.jpg', 'eyebrow' => 'Notre Engagement', 'title' => "Matieres<br/>Durables", 'subtitle' => 'Vetements ethiques concus pour durer', 'cta_text' => 'NOTRE ENGAGEMENT', 'cta_link' => '/notre-engagement/', 'cta_style' => 'primary' ),
		);
	}

	$show_side = velure_core_opt_bool( 'hero_show_side', false );
	$side_blocks = array();
	if ( $show_side ) {
		$side_blocks = array(
			'bestseller' => array(
				'image'     => velure_core_get_image_url( velure_core_opt( 'hs_bestseller_image', 0 ), $uri . '/assets/images/category-femme.jpg' ),
				'label'     => velure_core_opt( 'hs_bestseller_label', 'Best-Seller' ),
				'title'     => velure_core_opt( 'hs_bestseller_title', 'Sac Elegance' ),
				'price'     => velure_core_opt( 'hs_bestseller_price', '285,00 EUR' ),
				'cta_text'  => velure_core_opt( 'hs_bestseller_cta', 'VOIR LE PRODUIT' ),
				'cta_link'  => velure_core_opt( 'hs_bestseller_link', '#' ),
			),
			'category' => array(
				'image'     => velure_core_get_image_url( velure_core_opt( 'hs_category_image', 0 ), $uri . '/assets/images/category-accessoires.jpg' ),
				'label'     => velure_core_opt( 'hs_category_label', 'Capsule' ),
				'title'     => velure_core_opt( 'hs_category_title', 'Les Accessoires Essentiels' ),
				'cta_link'  => velure_core_opt( 'hs_category_cta_link', '/categorie/accessoires/' ),
			),
		);
	}

	return array(
		'height'          => velure_core_opt( 'hero_height', 'standard' ),
		'autoplay'        => velure_core_opt_bool( 'hero_autoplay', true ),
		'autoplay_speed'  => absint( velure_core_opt( 'hero_autoplay_speed', 6000 ) ),
		'overlay_opacity' => absint( velure_core_opt( 'hero_overlay_opacity', 40 ) ),
		'text_align'      => velure_core_opt( 'hero_text_align', 'left' ),
		'text_color'      => velure_core_opt( 'hero_text_color', 'light' ),
		'slides'          => $slides,
		'show_side'       => $show_side,
		'side_blocks'     => $side_blocks,
	);
}

/* ── 4B. FEATURES ── */
function velure_core_get_features() {
	$features = velure_core_opt( 'trust_features', array() );
	if ( ! empty( $features ) && is_array( $features ) ) {
		$out = array();
		foreach ( $features as $f ) {
			if ( empty( $f['title'] ) ) continue;
			$out[] = array( 'icon' => $f['icon_svg'] ?? '', 'title' => $f['title'], 'desc' => $f['description'] ?? '' );
		}
		if ( ! empty( $out ) ) return $out;
	}
	$icons = velure_core_default_feature_icons();
	return array(
		array( 'icon' => $icons[0], 'title' => 'Livraison Express', 'desc' => 'Sous 24-48h en France' ),
		array( 'icon' => $icons[1], 'title' => 'Retours Gratuits', 'desc' => 'Sous 30 jours, sans frais' ),
		array( 'icon' => $icons[2], 'title' => 'Paiement Securise', 'desc' => 'SSL & cryptage 256 bits' ),
		array( 'icon' => $icons[3], 'title' => 'Service Client 7j/7', 'desc' => 'Par chat, email ou telephone' ),
	);
}

/* ── 4C. CATEGORIES ── */
function velure_core_get_product_categories() {
	$uri   = velure_core_theme_uri();
	$count = absint( velure_core_opt( 'cat_display_count', 10 ) );

	/* Auto mode (WooCommerce) */
	if ( class_exists( 'WooCommerce' ) ) {
		$default_cat = (int) get_option( 'default_product_cat', 0 );
		$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'exclude' => $default_cat, 'number' => $count, 'orderby' => 'count', 'order' => 'DESC' ) );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			return array_map( function( $term ) use ( $uri ) {
				$tid = get_term_meta( $term->term_id, 'thumbnail_id', true );
				return array( 'name' => $term->name, 'slug' => $term->slug, 'link' => get_term_link( $term ), 'image' => $tid ? wp_get_attachment_url( $tid ) : $uri . '/assets/images/category-femme.jpg', 'count' => $term->count );
			}, $terms );
		}
	}

	/* Static fallback */
	return array(
		array( 'name' => 'Femme',        'slug' => 'femme',        'link' => home_url( '/categorie-femme/' ),        'image' => $uri . '/assets/images/category-femme.jpg',        'count' => 0 ),
		array( 'name' => 'Homme',        'slug' => 'homme',        'link' => home_url( '/categorie-homme/' ),        'image' => $uri . '/assets/images/category-homme.jpg',        'count' => 0 ),
		array( 'name' => 'Accessoires',  'slug' => 'accessoires',  'link' => home_url( '/categorie/accessoires/' ),   'image' => $uri . '/assets/images/category-accessoires.jpg','count' => 0 ),
		array( 'name' => 'Chaussures',   'slug' => 'chaussures',   'link' => home_url( '/categorie/chaussures/' ),    'image' => $uri . '/assets/images/category-chaussures.jpg', 'count' => 0 ),
		array( 'name' => 'Maroquinerie', 'slug' => 'maroquinerie', 'link' => home_url( '/categorie/maroquinerie/' ),  'image' => $uri . '/assets/images/category-maroquinerie.jpg','count' => 0 ),
		array( 'name' => 'Sportswear',   'slug' => 'sportswear',   'link' => home_url( '/categorie/sportswear/' ),    'image' => $uri . '/assets/images/category-sportswear.jpg', 'count' => 0 ),
	);
}

/* ── 4D. PRODUCTS ── */
function velure_core_get_featured_products() {
	$uri   = velure_core_theme_uri();
	$mode  = velure_core_opt( 'prod_mode', 'auto' );
	$count = absint( velure_core_opt( 'featured_products_count', 8 ) );
	$sort  = velure_core_opt( 'prod_sort', 'date' );

	/* Auto mode */
	if ( 'auto' === $mode && class_exists( 'WooCommerce' ) ) {
		$wc_args = array( 'limit' => $count, 'visibility' => 'visible', 'return' => 'objects' );
		switch ( $sort ) {
			case 'popularity': $wc_args['orderby'] = 'popularity'; break;
			case 'rating':     $wc_args['orderby'] = 'rating'; break;
			case 'rand':       $wc_args['orderby'] = 'rand'; break;
			default:           $wc_args['orderby'] = 'date'; $wc_args['order'] = 'DESC'; break;
		}
		$wc_products = wc_get_products( $wc_args );
		if ( ! empty( $wc_products ) ) {
			return array_map( function( $p ) {
				$img_id = $p->get_image_id();
				$badge  = '';
				if ( $p->is_on_sale() ) $badge = '<span class="velure-product-card-badge sale">Promo</span>';
				elseif ( $p->is_featured() ) $badge = '<span class="velure-product-card-badge new">Nouveau</span>';
				return array( 'name' => $p->get_name(), 'link' => $p->get_permalink(), 'image' => $img_id ? wp_get_attachment_url( $img_id ) : '', 'price_html' => $p->get_price_html(), 'badge' => $badge );
			}, $wc_products );
		}
	}

	/* Static fallback */
	$demo = array(
		array( 'name' => 'Manteau Camel',  'price' => '485,00 EUR', 'badge' => '<span class="velure-product-card-badge new">Nouveau</span>', 'img' => 'product-manteau-camel.jpg' ),
		array( 'name' => 'Blazer Ivoire',   'price' => '320,00 EUR', 'badge' => '', 'img' => 'product-blazer-ivoire.jpg' ),
		array( 'name' => 'Robe Noire',     'price' => '275,00 EUR', 'badge' => '', 'img' => 'product-robe-noire.jpg' ),
		array( 'name' => 'Pull Beige',      'price' => '145,00 EUR', 'badge' => '<span class="velure-product-card-badge sale">Promo</span>', 'img' => 'product-pull-beige.jpg' ),
		array( 'name' => 'Pantalon Gris',   'price' => '195,00 EUR', 'badge' => '', 'img' => 'product-pantalon-gris.jpg' ),
		array( 'name' => 'Sac Cuir',        'price' => '385,00 EUR', 'badge' => '', 'img' => 'product-sac-cuir.jpg' ),
		array( 'name' => 'Bottines Brun',   'price' => '265,00 EUR', 'badge' => '<span class="velure-product-card-badge new">Nouveau</span>', 'img' => 'product-bottines-brun.jpg' ),
		array( 'name' => 'Chemise Blanc',   'price' => '125,00 EUR', 'badge' => '', 'img' => 'product-chemise-blanc.jpg' ),
	);
	return array_map( function( $p ) use ( $uri ) {
		return array( 'name' => $p['name'], 'link' => home_url( '/boutique/' ), 'image' => $uri . '/assets/images/' . $p['img'], 'price_html' => $p['price'], 'badge' => $p['badge'] );
	}, array_slice( $demo, 0, $count ) );
}

/* ── 4E. SPLIT BANNER ── */
function velure_core_get_split_banner() {
	$uri = velure_core_theme_uri();
	return array(
		'layout' => velure_core_opt( 'sb_layout', '50-50' ),
		'left' => array(
			'image'     => velure_core_get_image_url( velure_core_opt( 'sb_left_image', 0 ), $uri . '/assets/images/banner-collection.jpg' ),
			'eyebrow'   => velure_core_opt( 'sb_left_eyebrow', 'Collection AW25' ),
			'title'     => velure_core_opt( 'sb_left_title', 'La Nouvelle Collection' ),
			'desc'      => velure_core_opt( 'sb_left_desc', 'Des silhouettes audacieuses et des matieres nobles pour une saison inoubliable.' ),
			'cta_text'  => velure_core_opt( 'sb_left_cta_text', 'Decouvrir' ),
			'cta_link'  => velure_core_opt( 'sb_left_cta_link', '/new-collection/' ),
			'cta_style' => velure_core_opt( 'sb_left_cta_style', 'gold' ),
			'style'     => velure_core_opt( 'sb_left_style', 'dark' ),
		),
		'right' => array(
			'image'     => velure_core_get_image_url( velure_core_opt( 'sb_right_image', 0 ), $uri . '/assets/images/category-accessoires.jpg' ),
			'eyebrow'   => velure_core_opt( 'sb_right_eyebrow', 'Edition Limitee' ),
			'title'     => velure_core_opt( 'sb_right_title', "Accessoires d'Exception" ),
			'desc'      => velure_core_opt( 'sb_right_desc', 'Sacs, bijoux et ceintures signes par les meilleurs artisans.' ),
			'cta_text'  => velure_core_opt( 'sb_right_cta_text', 'Explorer' ),
			'cta_link'  => velure_core_opt( 'sb_right_cta_link', '/categorie/accessoires/' ),
			'cta_style' => velure_core_opt( 'sb_right_cta_style', 'outline' ),
			'style'     => velure_core_opt( 'sb_right_style', 'light' ),
		),
	);
}

/* ── 4F. BRANDS ── */
function velure_core_get_brands() {
	$brands = velure_core_opt( 'brand_names', array() );
	if ( ! empty( $brands ) && is_array( $brands ) ) {
		$out = array();
		foreach ( $brands as $b ) {
			$name = $b['name'] ?? '';
			if ( $name ) $out[] = strtoupper( $name );
		}
		if ( ! empty( $out ) ) return $out;
	}
	return array( 'ELIE SAAB', 'VALENTINO', 'BOTTEGA VENETA', 'SAINT LAURENT', 'ACNE STUDIOS', 'THE ROW', 'LOEWE', 'RICK OWENS' );
}

/* ── 4G. TESTIMONIALS ── */
function velure_core_get_testimonials( $count = 3 ) {
	$posts = get_posts( array( 'numberposts' => $count, 'post_type' => 'velure_testimonial', 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( ! empty( $posts ) ) {
		return array_map( function( $p ) {
			return array( 'text' => wp_trim_words( $p->post_content, 40, '...' ), 'author' => get_the_title( $p ), 'role' => get_post_meta( $p->ID, '_velure_role', true ), 'stars' => (int) get_post_meta( $p->ID, '_velure_stars', true ) ?: 5 );
		}, $posts );
	}
	return array(
		array( 'text' => "La qualite des matieres est exceptionnelle. Mon manteau en cachemire est devenu ma piece preferee.", 'author' => 'Camille D.', 'role' => 'Cliente fidele depuis 2023', 'stars' => 5 ),
		array( 'text' => "J'ai commande un blazer pour un evenement et j'ai ete bluffee par la coupe et la finition.", 'author' => 'Antoine M.', 'role' => 'Achat verifie', 'stars' => 5 ),
		array( 'text' => 'Velure est devenue ma reference mode. Les pieces sont intemporelles et la taille guide est tres fiable.', 'author' => 'Sophie L.', 'role' => 'Cliente Premium', 'stars' => 5 ),
	);
}

/* ── 4H. BLOG POSTS ── */
function velure_core_get_blog_posts( $count = 3 ) {
	$uri = velure_core_theme_uri();
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
		array( 'title' => 'Capsule Garderobe Ideale',     'category' => 'Style',      'date' => '8 Jan 2025',  'excerpt' => 'Construisez une garde-robe capsule de 15 pieces.', 'img' => 'blog-capsule-garderobe.jpg' ),
		array( 'title' => 'Entretien du Cachemire',        'category' => 'Entretien',  'date' => '2 Jan 2025',  'excerpt' => "Nos conseils d'experts pour laver, secher et ranger vos pieces en cachemire.", 'img' => 'blog-cachemire-entretien.jpg' ),
	);
	return array_map( function( $p ) use ( $uri ) {
		return array( 'title' => $p['title'], 'excerpt' => $p['excerpt'], 'link' => home_url( '/blog/' ), 'image' => $uri . '/assets/images/' . $p['img'], 'date' => $p['date'], 'category' => $p['category'] );
	}, array_slice( $demo, 0, $count ) );
}

/* ── 4I. INSTAGRAM ── */
function velure_core_get_instagram_items() {
	$uri   = velure_core_theme_uri();
	$items = velure_core_opt( 'instagram_images', array() );
	if ( ! empty( $items ) && is_array( $items ) ) {
		$ig_url = velure_core_opt( 'instagram_url', 'https://instagram.com/' );
		$out = array();
		foreach ( $items as $item ) {
			if ( empty( $item['image'] ) ) continue;
			$out[] = array( 'image' => velure_core_get_image_url( $item['image'] ), 'link' => ! empty( $item['link'] ) ? $item['link'] : $ig_url, 'alt' => $item['alt'] ?? '' );
		}
		if ( ! empty( $out ) ) return $out;
	}
	$defaults = array();
	$ig_url = velure_core_opt( 'instagram_url', 'https://instagram.com/' );
	for ( $i = 1; $i <= 6; $i++ ) {
		$defaults[] = array( 'image' => $uri . '/assets/images/instagram-0' . $i . '.jpg', 'link' => $ig_url, 'alt' => '' );
	}
	return $defaults;
}


/* ═══════════════════════════════════════════════
   5. FRONT-END INJECTION (custom CSS, etc.)
   ═══════════════════════════════════════════════ */
add_action( 'wp_head', 'velure_core_inject_custom_css' );
function velure_core_inject_custom_css() {
	if ( ! is_front_page() ) return;

	$rules = array();

	/* Section padding */
	$pad = velure_core_opt( 'section_padding', 'normal' );
	$pad_map = array( 'compact' => '3rem', 'normal' => '5.5rem', 'spacious' => '8rem', 'none' => '0' );
	if ( isset( $pad_map[ $pad ] ) ) {
		$rules[] = '.velure-section { padding-top: ' . $pad_map[ $pad ] . '; padding-bottom: ' . $pad_map[ $pad ] . '; }';
	}

	/* Custom CSS from admin */
	$custom_css = velure_core_opt( 'custom_css', '' );
	if ( $custom_css ) {
		$rules[] = $custom_css;
	}

	if ( empty( $rules ) ) return;

	echo '<style id="velure-core-custom">' . "\n";
	echo implode( "\n", $rules );
	echo "\n</style>\n";
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
   6. COMPATIBILITY ALIASES (velure3_* → velure_core_*)
   These allow the theme's front-page.php to work
   without modification when the plugin is active.
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