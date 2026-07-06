<?php
/**
 * Velure Core — Admin Page
 * All settings are stored in a single wp_options row keyed by VELURE_CORE_OPTION.
 * Uses native WordPress Settings API patterns and custom forms — NO ACF dependency.
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════
   1. SETTINGS RETRIEVAL
   ═══════════════════════════════════════════════ */

/**
 * Retrieve the full settings array, merged with plugin defaults.
 *
 * @return array
 */
function velure_core_get_settings() {
	$saved   = get_option( VELURE_CORE_OPTION, array() );
	$default = Velure_Core::instance()->default_settings();
	return wp_parse_args( $saved, $default );
}

/* ═══════════════════════════════════════════════
   2. FIELD HELPER FUNCTIONS
   ═══════════════════════════════════════════════ */

/**
 * Render a standard text input field.
 *
 * @param array $args { label, name, value, description?, placeholder? }
 */
function velure_core_field_text( $args ) {
	$id          = 'vc-' . sanitize_html_class( $args['name'] );
	$val         = esc_attr( $args['value'] ?? '' );
	$desc        = ! empty( $args['description'] ) ? '<p class="description">' . esc_html( $args['description'] ) . '</p>' : '';
	$placeholder = ! empty( $args['placeholder'] ) ? ' placeholder="' . esc_attr( $args['placeholder'] ) . '"' : '';

	echo '<div class="vc-field">';
	echo '<label for="' . $id . '">' . esc_html( $args['label'] ) . '</label>';
	echo '<input type="text" id="' . $id . '" name="' . esc_attr( $args['name'] ) . '" value="' . $val . '"' . $placeholder . ' class="regular-text" />';
	echo $desc;
	echo '</div>';
}

/**
 * Render a textarea field.
 *
 * @param array $args { label, name, value, description?, rows? }
 */
function velure_core_field_textarea( $args ) {
	$id   = 'vc-' . sanitize_html_class( $args['name'] );
	$val  = esc_textarea( $args['value'] ?? '' );
	$desc = ! empty( $args['description'] ) ? '<p class="description">' . esc_html( $args['description'] ) . '</p>' : '';
	$rows = ! empty( $args['rows'] ) ? intval( $args['rows'] ) : 5;

	echo '<div class="vc-field">';
	echo '<label for="' . $id . '">' . esc_html( $args['label'] ) . '</label>';
	echo '<textarea id="' . $id . '" name="' . esc_attr( $args['name'] ) . '" rows="' . $rows . '" class="large-text">' . $val . '</textarea>';
	echo $desc;
	echo '</div>';
}

/**
 * Render a select dropdown.
 *
 * @param array $args { label, name, value, choices (assoc array), description? }
 */
function velure_core_field_select( $args ) {
	$id   = 'vc-' . sanitize_html_class( $args['name'] );
	$val  = isset( $args['value'] ) ? $args['value'] : '';
	$desc = ! empty( $args['description'] ) ? '<p class="description">' . esc_html( $args['description'] ) . '</p>' : '';
	$choices = ! empty( $args['choices'] ) ? $args['choices'] : array();

	echo '<div class="vc-field">';
	echo '<label for="' . $id . '">' . esc_html( $args['label'] ) . '</label>';
	echo '<select id="' . $id . '" name="' . esc_attr( $args['name'] ) . '">';
	foreach ( $choices as $ck => $cv ) {
		$selected = ( string ) $val === ( string ) $ck ? ' selected' : '';
		echo '<option value="' . esc_attr( $ck ) . '"' . $selected . '>' . esc_html( $cv ) . '</option>';
	}
	echo '</select>';
	echo $desc;
	echo '</div>';
}

/**
 * Render a number input field.
 *
 * @param array $args { label, name, value, description?, min?, max?, step? }
 */
function velure_core_field_number( $args ) {
	$id   = 'vc-' . sanitize_html_class( $args['name'] );
	$val  = isset( $args['value'] ) ? intval( $args['value'] ) : 0;
	$desc = ! empty( $args['description'] ) ? '<p class="description">' . esc_html( $args['description'] ) . '</p>' : '';
	$min  = isset( $args['min'] ) ? ' min="' . intval( $args['min'] ) . '"' : '';
	$max  = isset( $args['max'] ) ? ' max="' . intval( $args['max'] ) . '"' : '';
	$step = isset( $args['step'] ) ? ' step="' . esc_attr( $args['step'] ) . '"' : '';

	echo '<div class="vc-field">';
	echo '<label for="' . $id . '">' . esc_html( $args['label'] ) . '</label>';
	echo '<input type="number" id="' . $id . '" name="' . esc_attr( $args['name'] ) . '" value="' . $val . '" class="small-text"' . $min . $max . $step . ' />';
	echo $desc;
	echo '</div>';
}

/**
 * Render a toggle switch (checkbox styled as toggle).
 *
 * @param array $args { label, name, value, description?, on_text?, off_text? }
 */
function velure_core_field_toggle( $args ) {
	$name    = esc_attr( $args['name'] );
	$checked = ! empty( $args['value'] ) ? 'checked' : '';
	$on      = $args['on_text'] ?? 'Oui';
	$off     = $args['off_text'] ?? 'Non';

	echo '<div class="vc-field vc-toggle-field">';
	echo '<label>' . esc_html( $args['label'] ) . '</label>';
	echo '<label class="vc-toggle">';
	echo '<input type="checkbox" name="' . $name . '" value="1" ' . $checked . ' />';
	echo '<span class="vc-toggle-slider"></span>';
	echo '<span class="vc-toggle-labels"><span class="vc-toggle-on">' . esc_html( $on ) . '</span><span class="vc-toggle-off">' . esc_html( $off ) . '</span></span>';
	echo '</label>';
	if ( ! empty( $args['description'] ) ) {
		echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
	}
	echo '</div>';
}

/**
 * Render an image upload field with "Choose Image" button and preview.
 *
 * @param array $args { label, name, value (attachment ID), description? }
 */
function velure_core_field_image( $args ) {
	$name    = esc_attr( $args['name'] );
	$val     = intval( $args['value'] ?? 0 );
	$url     = $val ? wp_get_attachment_url( $val ) : '';
	$preview = $url
		? '<img src="' . esc_url( $url ) . '" class="vc-img-preview" />'
		: '<div class="vc-img-placeholder">Aucune image</div>';

	echo '<div class="vc-field vc-image-field">';
	echo '<label>' . esc_html( $args['label'] ) . '</label>';
	echo '<div class="vc-img-wrap">' . $preview . '</div>';
	echo '<input type="hidden" name="' . $name . '" value="' . $val . '" class="vc-img-id" />';
	echo '<button type="button" class="button vc-img-btn">Choisir une image</button>';
	echo '<button type="button" class="button vc-img-remove" style="' . ( $val ? '' : 'display:none;' ) . '">Supprimer</button>';
	if ( ! empty( $args['description'] ) ) {
		echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
	}
	echo '</div>';
}

/**
 * Render the header for a repeater section with an "Add" button.
 *
 * @param array $args { label, button_label?, description? }
 */
function velure_core_field_repeater_header( $args ) {
	$button_label = $args['button_label'] ?? 'Ajouter un element';
	echo '<div class="vc-repeater-header-wrap">';
	echo '<h3>' . esc_html( $args['label'] ) . '</h3>';
	echo '<button type="button" class="button button-secondary vc-repeater-add">' . esc_html( $button_label ) . '</button>';
	if ( ! empty( $args['description'] ) ) {
		echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
	}
	echo '</div>';
}

/**
 * Output the opening markup of a repeater row.
 *
 * @param int   $index  Current row index.
 * @param string $title Row title (e.g. "Slide 1").
 */
function velure_core_field_repeater_row_start( $index, $title = '' ) {
	echo '<div class="vc-repeater-row" data-index="' . intval( $index ) . '">';
	echo '<div class="vc-repeater-header">';
	echo '<span class="vc-repeater-handle" title="Glisser pour reordonner">&#9776;</span>';
	echo '<span class="vc-repeater-title">' . esc_html( $title ) . '</span>';
	echo '<button type="button" class="button vc-repeater-remove">Supprimer</button>';
	echo '</div>';
	echo '<div class="vc-repeater-fields">';
}

/**
 * Output the closing markup of a repeater row.
 */
function velure_core_field_repeater_row_end() {
	echo '</div><!-- .vc-repeater-fields -->';
	echo '</div><!-- .vc-repeater-row -->';
}

/* ═══════════════════════════════════════════════
   3. TAB RENDERING FUNCTIONS
   ═══════════════════════════════════════════════ */

/**
 * Tab: Sections — Visibility toggles & drag-and-drop ordering.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_sections( $s ) {
	echo '<div class="vc-section vc-section-sections">';

	/* ── Visibility toggles ── */
	echo '<h3>Visibilite des sections</h3>';
	echo '<p class="description">Activez ou desactivez chaque section de la page d\'accueil.</p>';

	$visibility_sections = array(
		'show_hero'         => 'Hero (Slider)',
		'show_features'     => 'Barre de confiance',
		'show_categories'   => 'Categories',
		'show_products'     => 'Produits vedettes',
		'show_split_banner' => 'Banniere splittee',
		'show_marquee'      => 'Marquee (marques)',
		'show_testimonials' => 'Temoignages',
		'show_blog'         => 'Blog',
		'show_instagram'    => 'Instagram',
	);

	echo '<div class="vc-grid vc-grid-2">';
	foreach ( $visibility_sections as $key => $label ) {
		velure_core_field_toggle( array(
			'label' => $label,
			'name'  => $key,
			'value' => $s[ $key ] ?? 0,
		) );
	}
	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Section ordering ── */
	echo '<h3>Ordre des sections</h3>';
	echo '<p class="description">Glissez-deposez pour reordonner les sections sur la page d\'accueil.</p>';

	$section_labels = array(
		'hero'         => 'Hero (Slider)',
		'features'     => 'Barre de confiance',
		'categories'   => 'Categories',
		'products'     => 'Produits vedettes',
		'split_banner' => 'Banniere splittee',
		'marquee'      => 'Marquee (marques)',
		'testimonials' => 'Temoignages',
		'blog'         => 'Blog',
		'instagram'    => 'Instagram',
	);

	$current_order = $s['section_order'] ?? array_keys( $section_labels );
	if ( ! is_array( $current_order ) || empty( $current_order ) ) {
		$current_order = array_keys( $section_labels );
	}

	echo '<ul class="vc-sortable-list">';
	foreach ( $current_order as $i => $sec ) {
		$label   = $section_labels[ $sec ] ?? $sec;
		$visible = ! empty( $s[ 'show_' . $sec ] ) ? ' visible' : '';
		echo '<li class="vc-sortable-item' . $visible . '">';
		echo '<span class="vc-sortable-handle" title="Glisser pour reordonner">&#9776;</span>';
		echo '<span class="vc-sortable-label">' . esc_html( $label ) . '</span>';
		echo '<input type="hidden" name="section_order[]" value="' . esc_attr( $sec ) . '" />';
		echo '</li>';
	}
	echo '</ul>';

	echo '</div>';
}

/**
 * Tab: Hero — Slider configuration with slides repeater and side blocks.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_hero( $s ) {
	echo '<div class="vc-section vc-section-hero">';

	/* ── Slider settings ── */
	echo '<h3>Parametres du slider</h3>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_select( array(
		'label'   => 'Hauteur du hero',
		'name'    => 'hero_height',
		'value'   => $s['hero_height'] ?? 'standard',
		'choices' => array(
			'small'    => 'Petite',
			'standard' => 'Standard',
			'large'    => 'Grande',
			'fullscreen' => 'Plein ecran',
		),
	) );

	velure_core_field_toggle( array(
		'label' => 'Lecture automatique',
		'name'  => 'hero_autoplay',
		'value' => $s['hero_autoplay'] ?? 1,
	) );

	velure_core_field_number( array(
		'label' => 'Vitesse autoplay (ms)',
		'name'  => 'hero_autoplay_speed',
		'value' => $s['hero_autoplay_speed'] ?? 6000,
		'min'   => 1000,
		'max'   => 20000,
		'step'  => 500,
	) );

	velure_core_field_number( array(
		'label'       => 'Opacite de l\'overlay (%)',
		'name'        => 'hero_overlay_opacity',
		'value'       => $s['hero_overlay_opacity'] ?? 40,
		'min'         => 0,
		'max'         => 100,
		'step'        => 5,
		'description' => '0 = transparent, 100 = noir total.',
	) );

	velure_core_field_select( array(
		'label'   => 'Alignement du texte',
		'name'    => 'hero_text_align',
		'value'   => $s['hero_text_align'] ?? 'left',
		'choices' => array(
			'left'   => 'Gauche',
			'center' => 'Centre',
			'right'  => 'Droite',
		),
	) );

	velure_core_field_select( array(
		'label'   => 'Couleur du texte',
		'name'    => 'hero_text_color',
		'value'   => $s['hero_text_color'] ?? 'light',
		'choices' => array(
			'light' => 'Clair (sur fond fonce)',
			'dark'  => 'Fonce (sur fond clair)',
		),
	) );

	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Side blocks ── */
	echo '<h3>Blocs lateraux</h3>';
	velure_core_field_toggle( array(
		'label'       => 'Afficher les blocs lateraux',
		'name'        => 'hero_show_side',
		'value'       => $s['hero_show_side'] ?? 0,
		'description' => 'Affiche un bloc best-seller et un bloc categorie a cote du slider.',
	) );

	echo '<div class="vc-side-blocks ' . ( ! empty( $s['hero_show_side'] ) ? '' : 'vc-hidden' ) . '" id="vc-hero-side-blocks">';

	echo '<h4>Best-Seller</h4>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_image( array(
		'label' => 'Image best-seller',
		'name'  => 'hs_bestseller_image',
		'value' => $s['hs_bestseller_image'] ?? 0,
	) );
	velure_core_field_text( array(
		'label'       => 'Label',
		'name'        => 'hs_bestseller_label',
		'value'       => $s['hs_bestseller_label'] ?? 'Best-Seller',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre du produit',
		'name'        => 'hs_bestseller_title',
		'value'       => $s['hs_bestseller_title'] ?? 'Sac Elegance',
	) );
	velure_core_field_text( array(
		'label'       => 'Prix',
		'name'        => 'hs_bestseller_price',
		'value'       => $s['hs_bestseller_price'] ?? '285,00 EUR',
	) );
	velure_core_field_text( array(
		'label'       => 'Texte du bouton CTA',
		'name'        => 'hs_bestseller_cta',
		'value'       => $s['hs_bestseller_cta'] ?? 'VOIR LE PRODUIT',
	) );
	velure_core_field_text( array(
		'label'       => 'Lien du bouton CTA',
		'name'        => 'hs_bestseller_link',
		'value'       => $s['hs_bestseller_link'] ?? '#',
	) );

	echo '</div>';

	echo '<h4>Bloc Categorie</h4>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_image( array(
		'label' => 'Image categorie',
		'name'  => 'hs_category_image',
		'value' => $s['hs_category_image'] ?? 0,
	) );
	velure_core_field_text( array(
		'label'       => 'Label',
		'name'        => 'hs_category_label',
		'value'       => $s['hs_category_label'] ?? 'Capsule',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre de la categorie',
		'name'        => 'hs_category_title',
		'value'       => $s['hs_category_title'] ?? 'Les Accessoires Essentiels',
	) );
	velure_core_field_text( array(
		'label'       => 'Lien vers la categorie',
		'name'        => 'hs_category_cta_link',
		'value'       => $s['hs_category_cta_link'] ?? '/categorie/accessoires/',
	) );

	echo '</div>';
	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Slides repeater ── */
	$slides = $s['hero_slides'] ?? array();
	if ( ! is_array( $slides ) ) {
		$slides = array();
	}

	velure_core_field_repeater_header( array(
		'label'         => 'Slides du hero',
		'button_label'  => 'Ajouter un slide',
		'description'   => 'Chaque slide comprend une image, un texte et un bouton CTA.',
	) );

	echo '<div class="vc-repeater" id="vc-hero-slides-repeater">';

	/* Existing rows */
	foreach ( $slides as $i => $slide ) {
		if ( ! is_array( $slide ) ) {
			$slide = array();
		}
		velure_core_field_repeater_row_start( $i, 'Slide ' . ( $i + 1 ) );

		velure_core_field_image( array(
			'label' => 'Image du slide',
			'name'  => 'hero_slides[' . $i . '][image]',
			'value' => $slide['image'] ?? 0,
		) );
		velure_core_field_text( array(
			'label'       => 'Eyebrow',
			'name'        => 'hero_slides[' . $i . '][eyebrow]',
			'value'       => $slide['eyebrow'] ?? '',
			'placeholder' => 'Nouvelle Collection',
		) );
		velure_core_field_text( array(
			'label'       => 'Titre',
			'name'        => 'hero_slides[' . $i . '][title]',
			'value'       => $slide['title'] ?? '',
			'placeholder' => "L'Elegance Minimaliste",
		) );
		velure_core_field_text( array(
			'label'       => 'Sous-titre',
			'name'        => 'hero_slides[' . $i . '][subtitle]',
			'value'       => $slide['subtitle'] ?? '',
			'placeholder' => 'Nouvelle Collection Automne 2026',
		) );
		velure_core_field_text( array(
			'label'       => 'Texte du bouton CTA',
			'name'        => 'hero_slides[' . $i . '][cta_text]',
			'value'       => $slide['cta_text'] ?? '',
			'placeholder' => 'DECOUVRIR',
		) );
		velure_core_field_text( array(
			'label'       => 'Lien du bouton CTA',
			'name'        => 'hero_slides[' . $i . '][cta_link]',
			'value'       => $slide['cta_link'] ?? '#',
		) );
		velure_core_field_select( array(
			'label'   => 'Style du bouton CTA',
			'name'    => 'hero_slides[' . $i . '][cta_style]',
			'value'   => $slide['cta_style'] ?? 'primary',
			'choices' => array(
				'primary' => 'Principal',
				'secondary' => 'Secondaire',
				'gold'    => 'Dore',
				'outline' => 'Contour',
			),
		) );

		velure_core_field_repeater_row_end();
	}

	/* Hidden template row for JavaScript cloning */
	echo '<div class="vc-repeater-template" style="display:none;">';
	velure_core_field_repeater_row_start( '__INDEX__', 'Slide __NUM__' );

	velure_core_field_image( array(
		'label' => 'Image du slide',
		'name'  => 'hero_slides[__INDEX__][image]',
		'value' => 0,
	) );
	velure_core_field_text( array(
		'label'       => 'Eyebrow',
		'name'        => 'hero_slides[__INDEX__][eyebrow]',
		'value'       => '',
		'placeholder' => 'Nouvelle Collection',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre',
		'name'        => 'hero_slides[__INDEX__][title]',
		'value'       => '',
		'placeholder' => "L'Elegance Minimaliste",
	) );
	velure_core_field_text( array(
		'label'       => 'Sous-titre',
		'name'        => 'hero_slides[__INDEX__][subtitle]',
		'value'       => '',
		'placeholder' => 'Nouvelle Collection Automne 2026',
	) );
	velure_core_field_text( array(
		'label'       => 'Texte du bouton CTA',
		'name'        => 'hero_slides[__INDEX__][cta_text]',
		'value'       => '',
		'placeholder' => 'DECOUVRIR',
	) );
	velure_core_field_text( array(
		'label'       => 'Lien du bouton CTA',
		'name'        => 'hero_slides[__INDEX__][cta_link]',
		'value'       => '#',
	) );
	velure_core_field_select( array(
		'label'   => 'Style du bouton CTA',
		'name'    => 'hero_slides[__INDEX__][cta_style]',
		'value'   => 'primary',
		'choices' => array(
			'primary'   => 'Principal',
			'secondary' => 'Secondaire',
			'gold'      => 'Dore',
			'outline'   => 'Contour',
		),
	) );

	velure_core_field_repeater_row_end();
	echo '</div><!-- .vc-repeater-template -->';

	echo '</div><!-- .vc-repeater -->';

	echo '</div>';
}

/**
 * Tab: Features — Trust bar with features repeater.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_features( $s ) {
	echo '<div class="vc-section vc-section-features">';

	echo '<h3>Parametres de la barre de confiance</h3>';
	echo '<div class="vc-grid vc-grid-3">';

	velure_core_field_select( array(
		'label'   => 'Style de fond',
		'name'    => 'feat_bg_style',
		'value'   => $s['feat_bg_style'] ?? 'soft',
		'choices' => array(
			'base'      => 'Base',
			'soft'      => 'Doux',
			'muted'     => 'Mute',
			'dark'      => 'Fonce',
			'secondary' => 'Secondaire',
		),
	) );

	velure_core_field_select( array(
		'label'   => 'Espacement vertical',
		'name'    => 'feat_padding',
		'value'   => $s['feat_padding'] ?? 'normal',
		'choices' => array(
			'compact' => 'Compact',
			'normal'  => 'Normal',
			'spacious'=> 'Espacieux',
		),
	) );

	velure_core_field_toggle( array(
		'label'       => 'Bordure inferieure',
		'name'        => 'feat_bottom_border',
		'value'       => $s['feat_bottom_border'] ?? 0,
		'description' => 'Affiche une ligne separatrice en bas de la section.',
	) );

	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Features repeater ── */
	$features = $s['trust_features'] ?? array();
	if ( ! is_array( $features ) ) {
		$features = array();
	}

	velure_core_field_repeater_header( array(
		'label'        => 'Elements de confiance',
		'button_label' => 'Ajouter un element',
		'description'  => 'Chaque element peut avoir une icone SVG personnalisee, un titre et une description.',
	) );

	echo '<div class="vc-repeater" id="vc-trust-features-repeater">';

	foreach ( $features as $i => $feat ) {
		if ( ! is_array( $feat ) ) {
			$feat = array();
		}
		velure_core_field_repeater_row_start( $i, 'Element ' . ( $i + 1 ) );

		velure_core_field_textarea( array(
			'label'       => 'Icone SVG',
			'name'        => 'trust_features[' . $i . '][icon_svg]',
			'value'       => $feat['icon_svg'] ?? '',
			'rows'        => 4,
			'description' => 'Collez le code SVG de l\'icone (balise <svg>...</svg>).',
		) );
		velure_core_field_text( array(
			'label'       => 'Titre',
			'name'        => 'trust_features[' . $i . '][title]',
			'value'       => $feat['title'] ?? '',
			'placeholder' => 'Livraison Express',
		) );
		velure_core_field_text( array(
			'label'       => 'Description',
			'name'        => 'trust_features[' . $i . '][description]',
			'value'       => $feat['description'] ?? '',
			'placeholder' => 'Sous 24-48h en France',
		) );

		velure_core_field_repeater_row_end();
	}

	/* Hidden template row */
	echo '<div class="vc-repeater-template" style="display:none;">';
	velure_core_field_repeater_row_start( '__INDEX__', 'Element __NUM__' );

	velure_core_field_textarea( array(
		'label'       => 'Icone SVG',
		'name'        => 'trust_features[__INDEX__][icon_svg]',
		'value'       => '',
		'rows'        => 4,
		'description' => 'Collez le code SVG de l\'icone (balise <svg>...</svg>).',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre',
		'name'        => 'trust_features[__INDEX__][title]',
		'value'       => '',
		'placeholder' => 'Livraison Express',
	) );
	velure_core_field_text( array(
		'label'       => 'Description',
		'name'        => 'trust_features[__INDEX__][description]',
		'value'       => '',
		'placeholder' => 'Sous 24-48h en France',
	) );

	velure_core_field_repeater_row_end();
	echo '</div><!-- .vc-repeater-template -->';

	echo '</div><!-- .vc-repeater -->';

	echo '</div>';
}

/**
 * Tab: Categories — Category section settings.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_categories( $s ) {
	echo '<div class="vc-section vc-section-categories">';

	echo '<h3>Section Categories</h3>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_text( array(
		'label'       => 'Eyebrow',
		'name'        => 'cat_eyebrow',
		'value'       => $s['cat_eyebrow'] ?? 'Collections',
		'placeholder' => 'Collections',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre de la section',
		'name'        => 'section_title_categories',
		'value'       => $s['section_title_categories'] ?? 'Explorer par Univers',
		'placeholder' => 'Explorer par Univers',
	) );
	velure_core_field_textarea( array(
		'label'       => 'Description',
		'name'        => 'cat_description',
		'value'       => $s['cat_description'] ?? '',
		'rows'        => 3,
		'placeholder' => 'Explorez nos univers et trouvez la piece qui vous correspond.',
	) );
	velure_core_field_text( array(
		'label'       => 'Texte du bouton CTA',
		'name'        => 'cat_cta_text',
		'value'       => $s['cat_cta_text'] ?? 'Toutes les categories',
		'placeholder' => 'Toutes les categories',
	) );
	velure_core_field_text( array(
		'label'       => 'Lien du bouton CTA',
		'name'        => 'cat_cta_link',
		'value'       => $s['cat_cta_link'] ?? '/boutique/',
		'placeholder' => '/boutique/',
	) );
	velure_core_field_select( array(
		'label'   => 'Style de fond',
		'name'    => 'cat_bg_style',
		'value'   => $s['cat_bg_style'] ?? 'base',
		'choices' => array(
			'base'      => 'Base',
			'soft'      => 'Doux',
			'muted'     => 'Mute',
			'dark'      => 'Fonce',
			'secondary' => 'Secondaire',
		),
	) );
	velure_core_field_number( array(
		'label'       => 'Nombre de categories a afficher',
		'name'        => 'cat_display_count',
		'value'       => $s['cat_display_count'] ?? 10,
		'min'         => 1,
		'max'         => 50,
	) );

	echo '</div>';
	echo '</div>';
}

/**
 * Tab: Products — Featured products settings.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_products( $s ) {
	echo '<div class="vc-section vc-section-products">';

	echo '<h3>Section Produits Vedettes</h3>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_text( array(
		'label'       => 'Eyebrow',
		'name'        => 'prod_eyebrow',
		'value'       => $s['prod_eyebrow'] ?? 'Selection',
		'placeholder' => 'Selection',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre de la section',
		'name'        => 'section_title_products',
		'value'       => $s['section_title_products'] ?? 'Pieces Vedettes',
		'placeholder' => 'Pieces Vedettes',
	) );
	velure_core_field_textarea( array(
		'label'       => 'Description',
		'name'        => 'prod_description',
		'value'       => $s['prod_description'] ?? '',
		'rows'        => 3,
		'placeholder' => 'Nos pieces les plus appreciees, choisies pour vous.',
	) );
	velure_core_field_text( array(
		'label'       => 'Texte du bouton CTA',
		'name'        => 'prod_cta_text',
		'value'       => $s['prod_cta_text'] ?? 'Voir toute la boutique',
		'placeholder' => 'Voir toute la boutique',
	) );
	velure_core_field_text( array(
		'label'       => 'Lien du bouton CTA',
		'name'        => 'prod_cta_link',
		'value'       => $s['prod_cta_link'] ?? '/boutique/',
		'placeholder' => '/boutique/',
	) );
	velure_core_field_select( array(
		'label'   => 'Nombre de colonnes',
		'name'    => 'prod_columns',
		'value'   => $s['prod_columns'] ?? '4',
		'choices' => array(
			'2' => '2 colonnes',
			'3' => '3 colonnes',
			'4' => '4 colonnes',
		),
	) );
	velure_core_field_select( array(
		'label'       => 'Mode de selection',
		'name'        => 'prod_mode',
		'value'       => $s['prod_mode'] ?? 'auto',
		'choices'     => array(
			'auto'   => 'Automatique (produits recents)',
			'manual' => 'Manuel (selection manuelle)',
		),
		'description' => 'En mode automatique, les produits sont recuperes depuis WooCommerce.',
	) );
	velure_core_field_number( array(
		'label'       => 'Nombre de produits a afficher',
		'name'        => 'featured_products_count',
		'value'       => $s['featured_products_count'] ?? 8,
		'min'         => 1,
		'max'         => 50,
	) );
	velure_core_field_select( array(
		'label'   => 'Style de fond',
		'name'    => 'prod_bg_style',
		'value'   => $s['prod_bg_style'] ?? 'soft',
		'choices' => array(
			'base'      => 'Base',
			'soft'      => 'Doux',
			'muted'     => 'Mute',
			'dark'      => 'Fonce',
			'secondary' => 'Secondaire',
		),
	) );
	velure_core_field_select( array(
		'label'       => 'Tri des produits',
		'name'        => 'prod_sort',
		'value'       => $s['prod_sort'] ?? 'date',
		'choices'     => array(
			'date'       => 'Date (plus recents)',
			'popularity' => 'Popularite',
			'rating'     => 'Note moyenne',
			'rand'       => 'Aleatoire',
		),
		'description' => 'Utilise uniquement en mode automatique.',
	) );

	echo '</div>';
	echo '</div>';
}

/**
 * Tab: Banner — Split banner with left/right sides.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_banner( $s ) {
	echo '<div class="vc-section vc-section-banner">';

	echo '<h3>Banniere Splitee</h3>';

	velure_core_field_select( array(
		'label'   => 'Disposition',
		'name'    => 'sb_layout',
		'value'   => $s['sb_layout'] ?? '50-50',
		'choices' => array(
			'50-50' => '50 / 50',
			'60-40' => '60 / 40',
			'40-60' => '40 / 60',
		),
	) );

	echo '<hr class="vc-separator" />';

	/* ── Left side ── */
	echo '<h4>Cote gauche</h4>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_image( array(
		'label' => 'Image',
		'name'  => 'sb_left_image',
		'value' => $s['sb_left_image'] ?? 0,
	) );
	velure_core_field_text( array(
		'label'       => 'Eyebrow',
		'name'        => 'sb_left_eyebrow',
		'value'       => $s['sb_left_eyebrow'] ?? 'Collection AW25',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre',
		'name'        => 'sb_left_title',
		'value'       => $s['sb_left_title'] ?? 'La Nouvelle Collection',
	) );
	velure_core_field_textarea( array(
		'label' => 'Description',
		'name'  => 'sb_left_desc',
		'value' => $s['sb_left_desc'] ?? '',
		'rows'  => 3,
	) );
	velure_core_field_text( array(
		'label'       => 'Texte du bouton CTA',
		'name'        => 'sb_left_cta_text',
		'value'       => $s['sb_left_cta_text'] ?? 'Decouvrir',
	) );
	velure_core_field_text( array(
		'label'       => 'Lien du bouton CTA',
		'name'        => 'sb_left_cta_link',
		'value'       => $s['sb_left_cta_link'] ?? '/new-collection/',
	) );
	velure_core_field_select( array(
		'label'   => 'Style du bouton CTA',
		'name'    => 'sb_left_cta_style',
		'value'   => $s['sb_left_cta_style'] ?? 'gold',
		'choices' => array(
			'primary'   => 'Principal',
			'secondary' => 'Secondaire',
			'gold'      => 'Dore',
			'outline'   => 'Contour',
		),
	) );
	velure_core_field_select( array(
		'label'   => 'Style visuel',
		'name'    => 'sb_left_style',
		'value'   => $s['sb_left_style'] ?? 'dark',
		'choices' => array(
			'light' => 'Clair',
			'dark'  => 'Fonce',
		),
	) );

	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Right side ── */
	echo '<h4>Cote droit</h4>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_image( array(
		'label' => 'Image',
		'name'  => 'sb_right_image',
		'value' => $s['sb_right_image'] ?? 0,
	) );
	velure_core_field_text( array(
		'label'       => 'Eyebrow',
		'name'        => 'sb_right_eyebrow',
		'value'       => $s['sb_right_eyebrow'] ?? 'Edition Limitee',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre',
		'name'        => 'sb_right_title',
		'value'       => $s['sb_right_title'] ?? "Accessoires d'Exception",
	) );
	velure_core_field_textarea( array(
		'label' => 'Description',
		'name'  => 'sb_right_desc',
		'value' => $s['sb_right_desc'] ?? '',
		'rows'  => 3,
	) );
	velure_core_field_text( array(
		'label'       => 'Texte du bouton CTA',
		'name'        => 'sb_right_cta_text',
		'value'       => $s['sb_right_cta_text'] ?? 'Explorer',
	) );
	velure_core_field_text( array(
		'label'       => 'Lien du bouton CTA',
		'name'        => 'sb_right_cta_link',
		'value'       => $s['sb_right_cta_link'] ?? '/categorie/accessoires/',
	) );
	velure_core_field_select( array(
		'label'   => 'Style du bouton CTA',
		'name'    => 'sb_right_cta_style',
		'value'   => $s['sb_right_cta_style'] ?? 'outline',
		'choices' => array(
			'primary'   => 'Principal',
			'secondary' => 'Secondaire',
			'gold'      => 'Dore',
			'outline'   => 'Contour',
		),
	) );
	velure_core_field_select( array(
		'label'   => 'Style visuel',
		'name'    => 'sb_right_style',
		'value'   => $s['sb_right_style'] ?? 'light',
		'choices' => array(
			'light' => 'Clair',
			'dark'  => 'Fonce',
		),
	) );

	echo '</div>';
	echo '</div>';
}

/**
 * Tab: Marquee — Brand marquee with brands repeater.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_marquee( $s ) {
	echo '<div class="vc-section vc-section-marquee">';

	echo '<h3>Marquee (Bandeau defilant)</h3>';
	echo '<div class="vc-grid vc-grid-3">';

	velure_core_field_number( array(
		'label'       => 'Vitesse de defilement (s)',
		'name'        => 'marquee_speed',
		'value'       => $s['marquee_speed'] ?? 25,
		'min'         => 5,
		'max'         => 120,
		'step'        => 1,
		'description' => 'Duree en secondes pour un cycle complet.',
	) );
	velure_core_field_select( array(
		'label'   => 'Fond',
		'name'    => 'marquee_bg',
		'value'   => $s['marquee_bg'] ?? 'base',
		'choices' => array(
			'base'      => 'Base',
			'soft'      => 'Doux',
			'muted'     => 'Mute',
			'dark'      => 'Fonce',
			'secondary' => 'Secondaire',
		),
	) );
	velure_core_field_select( array(
		'label'   => 'Direction',
		'name'    => 'marquee_direction',
		'value'   => $s['marquee_direction'] ?? 'left',
		'choices' => array(
			'left'  => 'Gauche vers droite',
			'right' => 'Droite vers gauche',
		),
	) );

	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Brands repeater ── */
	$brands = $s['brand_names'] ?? array();
	if ( ! is_array( $brands ) ) {
		$brands = array();
	}

	velure_core_field_repeater_header( array(
		'label'        => 'Marques',
		'button_label' => 'Ajouter une marque',
		'description'  => 'Entrez le nom de chaque marque a afficher dans le bandeau defilant.',
	) );

	echo '<div class="vc-repeater" id="vc-brand-names-repeater">';

	foreach ( $brands as $i => $brand ) {
		if ( ! is_array( $brand ) ) {
			$brand = array();
		}
		velure_core_field_repeater_row_start( $i, 'Marque ' . ( $i + 1 ) );

		velure_core_field_text( array(
			'label'       => 'Nom de la marque',
			'name'        => 'brand_names[' . $i . '][name]',
			'value'       => $brand['name'] ?? '',
			'placeholder' => 'NOM DE LA MARQUE',
		) );

		velure_core_field_repeater_row_end();
	}

	/* Hidden template row */
	echo '<div class="vc-repeater-template" style="display:none;">';
	velure_core_field_repeater_row_start( '__INDEX__', 'Marque __NUM__' );

	velure_core_field_text( array(
		'label'       => 'Nom de la marque',
		'name'        => 'brand_names[__INDEX__][name]',
		'value'       => '',
		'placeholder' => 'NOM DE LA MARQUE',
	) );

	velure_core_field_repeater_row_end();
	echo '</div><!-- .vc-repeater-template -->';

	echo '</div><!-- .vc-repeater -->';

	echo '</div>';
}

/**
 * Tab: Testimonials — Testimonials section settings.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_testimonials( $s ) {
	echo '<div class="vc-section vc-section-testimonials">';

	echo '<h3>Section Temoignages</h3>';
	echo '<p class="description">Les temoignages sont recuperes depuis le CPT "velure_testimonial". Ajoutez des temoignages depuis le menu "Temoignages" dans l\'admin.</p>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_text( array(
		'label'       => 'Eyebrow',
		'name'        => 'testi_eyebrow',
		'value'       => $s['testi_eyebrow'] ?? 'Avis Clients',
		'placeholder' => 'Avis Clients',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre de la section',
		'name'        => 'section_title_testimonials',
		'value'       => $s['section_title_testimonials'] ?? 'Ce Que Disent Nos Clients',
		'placeholder' => 'Ce Que Disent Nos Clients',
	) );
	velure_core_field_textarea( array(
		'label'       => 'Description',
		'name'        => 'testi_description',
		'value'       => $s['testi_description'] ?? '',
		'rows'        => 3,
	) );
	velure_core_field_number( array(
		'label'       => 'Nombre de temoignages',
		'name'        => 'testimonials_count',
		'value'       => $s['testimonials_count'] ?? 3,
		'min'         => 1,
		'max'         => 20,
	) );
	velure_core_field_select( array(
		'label'   => 'Style de fond',
		'name'    => 'testi_bg_style',
		'value'   => $s['testi_bg_style'] ?? 'base',
		'choices' => array(
			'base'      => 'Base',
			'soft'      => 'Doux',
			'muted'     => 'Mute',
			'dark'      => 'Fonce',
			'secondary' => 'Secondaire',
		),
	) );
	velure_core_field_select( array(
		'label'   => 'Nombre de colonnes',
		'name'    => 'testi_columns',
		'value'   => $s['testi_columns'] ?? '3',
		'choices' => array(
			'1' => '1 colonne',
			'2' => '2 colonnes',
			'3' => '3 colonnes',
		),
	) );

	echo '</div>';
	echo '</div>';
}

/**
 * Tab: Blog — Blog section settings.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_blog( $s ) {
	echo '<div class="vc-section vc-section-blog">';

	echo '<h3>Section Blog (Le Journal)</h3>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_text( array(
		'label'       => 'Eyebrow',
		'name'        => 'blog_eyebrow',
		'value'       => $s['blog_eyebrow'] ?? 'Actualites',
		'placeholder' => 'Actualites',
	) );
	velure_core_field_text( array(
		'label'       => 'Titre de la section',
		'name'        => 'section_title_blog',
		'value'       => $s['section_title_blog'] ?? 'Le Journal',
		'placeholder' => 'Le Journal',
	) );
	velure_core_field_textarea( array(
		'label'       => 'Description',
		'name'        => 'blog_description',
		'value'       => $s['blog_description'] ?? '',
		'rows'        => 3,
	) );
	velure_core_field_text( array(
		'label'       => 'Texte du bouton CTA',
		'name'        => 'blog_cta_text',
		'value'       => $s['blog_cta_text'] ?? 'Voir tous les articles',
		'placeholder' => 'Voir tous les articles',
	) );
	velure_core_field_text( array(
		'label'       => 'Lien du bouton CTA',
		'name'        => 'blog_cta_link',
		'value'       => $s['blog_cta_link'] ?? '/blog/',
		'placeholder' => '/blog/',
	) );
	velure_core_field_number( array(
		'label'       => 'Nombre d\'articles',
		'name'        => 'blog_posts_count',
		'value'       => $s['blog_posts_count'] ?? 3,
		'min'         => 1,
		'max'         => 20,
	) );
	velure_core_field_select( array(
		'label'   => 'Style de fond',
		'name'    => 'blog_bg_style',
		'value'   => $s['blog_bg_style'] ?? 'muted',
		'choices' => array(
			'base'      => 'Base',
			'soft'      => 'Doux',
			'muted'     => 'Mute',
			'dark'      => 'Fonce',
			'secondary' => 'Secondaire',
		),
	) );
	velure_core_field_select( array(
		'label'   => 'Nombre de colonnes',
		'name'    => 'blog_columns',
		'value'   => $s['blog_columns'] ?? '3',
		'choices' => array(
			'2' => '2 colonnes',
			'3' => '3 colonnes',
		),
	) );

	echo '</div>';
	echo '</div>';
}

/**
 * Tab: Instagram — Instagram feed with images repeater.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_instagram( $s ) {
	echo '<div class="vc-section vc-section-instagram">';

	echo '<h3>Section Instagram</h3>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_text( array(
		'label'       => 'Handle Instagram',
		'name'        => 'instagram_handle',
		'value'       => $s['instagram_handle'] ?? '@velure.paris',
		'placeholder' => '@velure.paris',
	) );
	velure_core_field_text( array(
		'label'       => 'URL Instagram',
		'name'        => 'instagram_url',
		'value'       => $s['instagram_url'] ?? 'https://instagram.com/',
		'placeholder' => 'https://instagram.com/',
	) );
	velure_core_field_text( array(
		'label'       => 'Eyebrow',
		'name'        => 'ig_eyebrow',
		'value'       => $s['ig_eyebrow'] ?? 'Suivez-nous',
		'placeholder' => 'Suivez-nous',
	) );
	velure_core_field_select( array(
		'label'   => 'Nombre de colonnes',
		'name'    => 'ig_columns',
		'value'   => $s['ig_columns'] ?? '6',
		'choices' => array(
			'3' => '3 colonnes',
			'4' => '4 colonnes',
			'5' => '5 colonnes',
			'6' => '6 colonnes',
		),
	) );
	velure_core_field_select( array(
		'label'   => 'Espacement entre les images',
		'name'    => 'ig_gap',
		'value'   => $s['ig_gap'] ?? 'small',
		'choices' => array(
			'none'  => 'Aucun',
			'small' => 'Petit',
			'medium'=> 'Moyen',
			'large' => 'Grand',
		),
	) );

	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Instagram images repeater ── */
	$images = $s['instagram_images'] ?? array();
	if ( ! is_array( $images ) ) {
		$images = array();
	}

	velure_core_field_repeater_header( array(
		'label'        => 'Images Instagram',
		'button_label' => 'Ajouter une image',
		'description'  => 'Ajoutez les images a afficher dans la grille Instagram.',
	) );

	echo '<div class="vc-repeater" id="vc-instagram-images-repeater">';

	foreach ( $images as $i => $img ) {
		if ( ! is_array( $img ) ) {
			$img = array();
		}
		velure_core_field_repeater_row_start( $i, 'Image ' . ( $i + 1 ) );

		velure_core_field_image( array(
			'label' => 'Image',
			'name'  => 'instagram_images[' . $i . '][image]',
			'value' => $img['image'] ?? 0,
		) );
		velure_core_field_text( array(
			'label'       => 'Lien',
			'name'        => 'instagram_images[' . $i . '][link]',
			'value'       => $img['link'] ?? '',
			'placeholder' => 'https://instagram.com/p/...',
		) );
		velure_core_field_text( array(
			'label'       => 'Texte alternatif (alt)',
			'name'        => 'instagram_images[' . $i . '][alt]',
			'value'       => $img['alt'] ?? '',
			'placeholder' => 'Description de l\'image',
		) );

		velure_core_field_repeater_row_end();
	}

	/* Hidden template row */
	echo '<div class="vc-repeater-template" style="display:none;">';
	velure_core_field_repeater_row_start( '__INDEX__', 'Image __NUM__' );

	velure_core_field_image( array(
		'label' => 'Image',
		'name'  => 'instagram_images[__INDEX__][image]',
		'value' => 0,
	) );
	velure_core_field_text( array(
		'label'       => 'Lien',
		'name'        => 'instagram_images[__INDEX__][link]',
		'value'       => '',
		'placeholder' => 'https://instagram.com/p/...',
	) );
	velure_core_field_text( array(
		'label'       => 'Texte alternatif (alt)',
		'name'        => 'instagram_images[__INDEX__][alt]',
		'value'       => '',
		'placeholder' => 'Description de l\'image',
	) );

	velure_core_field_repeater_row_end();
	echo '</div><!-- .vc-repeater-template -->';

	echo '</div><!-- .vc-repeater -->';

	echo '</div>';
}

/**
 * Tab: Global — Topbar, footer, padding, animations, custom CSS.
 *
 * @param array $s Full settings array.
 */
function velure_core_tab_global( $s ) {
	echo '<div class="vc-section vc-section-global">';

	/* ── Topbar ── */
	echo '<h3>Barre superieure (Topbar)</h3>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_toggle( array(
		'label' => 'Afficher la topbar',
		'name'  => 'show_topbar',
		'value' => $s['show_topbar'] ?? 1,
	) );
	velure_core_field_text( array(
		'label'       => 'Texte de la topbar',
		'name'        => 'topbar_text',
		'value'       => $s['topbar_text'] ?? '',
		'placeholder' => 'Livraison offerte des 150 EUR d\'achat &bull; Retours gratuits 30 jours',
		'description' => 'Utilisez &bull; pour les separateurs. Le HTML est autorise.',
	) );

	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Footer ── */
	echo '<h3>Pied de page (Footer)</h3>';

	velure_core_field_text( array(
		'label'       => 'Texte de copyright',
		'name'        => 'footer_copyright',
		'value'       => $s['footer_copyright'] ?? '',
		'placeholder' => '&copy; 2026 Velure. Tous droits reserves.',
		'description' => 'Le HTML est autorise.',
	) );

	echo '<hr class="vc-separator" />';

	/* ── Global styles ── */
	echo '<h3>Styles globaux</h3>';
	echo '<div class="vc-grid vc-grid-2">';

	velure_core_field_select( array(
		'label'       => 'Espacement des sections',
		'name'        => 'section_padding',
		'value'       => $s['section_padding'] ?? 'normal',
		'choices'     => array(
			'none'     => 'Aucun',
			'compact'  => 'Compact',
			'normal'   => 'Normal',
			'spacious' => 'Espacieux',
		),
		'description' => 'Definit l\'espacement vertical (padding haut/bas) de chaque section.',
	) );
	velure_core_field_toggle( array(
		'label'       => 'Animations au defilement',
		'name'        => 'scroll_animations',
		'value'       => $s['scroll_animations'] ?? 1,
		'description' => 'Active les animations d\'entree au defilement sur la page d\'accueil.',
	) );

	echo '</div>';

	echo '<hr class="vc-separator" />';

	/* ── Custom CSS ── */
	echo '<h3>CSS personnalise</h3>';

	velure_core_field_textarea( array(
		'label'       => 'CSS personnalise',
		'name'        => 'custom_css',
		'value'       => $s['custom_css'] ?? '',
		'rows'        => 12,
		'description' => 'Ce CSS est injecte dans le <head> de la page d\'accueil uniquement. N\'entrez pas les balises <style>.',
	) );

	echo '</div>';
}

/* ═══════════════════════════════════════════════
   4. MAIN ADMIN PAGE CALLBACK
   ═══════════════════════════════════════════════ */

/**
 * Render the full admin page.
 * Handles form POST, tab navigation, and delegates to tab rendering functions.
 */
function velure_core_render_admin_page() {

	/* ── Determine active tab ── */
	$tab_keys = array( 'sections', 'hero', 'features', 'categories', 'products', 'banner', 'marquee', 'testimonials', 'blog', 'instagram', 'global' );
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'sections';
	if ( ! in_array( $active_tab, $tab_keys, true ) ) {
		$active_tab = 'sections';
	}

	/* ═══════════════════════════════════════════════
	   FORM SAVE HANDLER
	   ═══════════════════════════════════════════════ */
	if ( isset( $_POST['velure_core_save'] ) && check_admin_referer( 'velure_core_save_settings' ) ) {

		$raw     = wp_unslash( $_POST );
		$saved   = get_option( VELURE_CORE_OPTION, array() );
		$defaults = Velure_Core::instance()->default_settings();

		/* ── Simple text / select fields ── */
		$simple_fields = array(
			'hero_height',
			'hero_text_align',
			'hero_text_color',
			'hs_bestseller_label',
			'hs_bestseller_title',
			'hs_bestseller_price',
			'hs_bestseller_cta',
			'hs_bestseller_link',
			'hs_category_label',
			'hs_category_title',
			'hs_category_cta_link',
			'feat_bg_style',
			'feat_padding',
			'cat_eyebrow',
			'section_title_categories',
			'cat_description',
			'cat_cta_text',
			'cat_cta_link',
			'cat_bg_style',
			'prod_eyebrow',
			'section_title_products',
			'prod_description',
			'prod_cta_text',
			'prod_cta_link',
			'prod_columns',
			'prod_mode',
			'prod_bg_style',
			'prod_sort',
			'sb_layout',
			'sb_left_eyebrow',
			'sb_left_title',
			'sb_left_desc',
			'sb_left_cta_text',
			'sb_left_cta_link',
			'sb_left_cta_style',
			'sb_left_style',
			'sb_right_eyebrow',
			'sb_right_title',
			'sb_right_desc',
			'sb_right_cta_text',
			'sb_right_cta_link',
			'sb_right_cta_style',
			'sb_right_style',
			'marquee_direction',
			'marquee_bg',
			'testi_eyebrow',
			'section_title_testimonials',
			'testi_description',
			'testi_bg_style',
			'testi_columns',
			'blog_eyebrow',
			'section_title_blog',
			'blog_description',
			'blog_cta_text',
			'blog_cta_link',
			'blog_bg_style',
			'blog_columns',
			'instagram_handle',
			'instagram_url',
			'ig_eyebrow',
			'ig_columns',
			'ig_gap',
			'topbar_text',
			'footer_copyright',
			'section_padding',
			'custom_css',
		);
		foreach ( $simple_fields as $f ) {
			$saved[ $f ] = isset( $raw[ $f ] ) ? sanitize_text_field( $raw[ $f ] ) : ( $defaults[ $f ] ?? '' );
		}

		/* ── Toggle fields (0 or 1) ── */
		$toggle_fields = array(
			'show_hero',
			'show_features',
			'show_categories',
			'show_products',
			'show_split_banner',
			'show_marquee',
			'show_testimonials',
			'show_blog',
			'show_instagram',
			'hero_autoplay',
			'hero_show_side',
			'feat_bottom_border',
			'scroll_animations',
			'show_topbar',
		);
		foreach ( $toggle_fields as $f ) {
			$saved[ $f ] = ! empty( $raw[ $f ] ) ? 1 : 0;
		}

		/* ── Number fields ── */
		$number_fields = array(
			'hero_autoplay_speed'   => array( 'default' => 6000, 'min' => 1000, 'max' => 20000 ),
			'hero_overlay_opacity'  => array( 'default' => 40,   'min' => 0,    'max' => 100   ),
			'cat_display_count'     => array( 'default' => 10,   'min' => 1,    'max' => 50    ),
			'featured_products_count'=> array( 'default' => 8,    'min' => 1,    'max' => 50    ),
			'marquee_speed'         => array( 'default' => 25,   'min' => 5,    'max' => 120   ),
			'testimonials_count'    => array( 'default' => 3,    'min' => 1,    'max' => 20    ),
			'blog_posts_count'      => array( 'default' => 3,    'min' => 1,    'max' => 20    ),
		);
		foreach ( $number_fields as $f => $cfg ) {
			$val = isset( $raw[ $f ] ) ? intval( $raw[ $f ] ) : $cfg['default'];
			$val = max( $cfg['min'], min( $cfg['max'], $val ) );
			$saved[ $f ] = $val;
		}

		/* ── Image fields (integers) ── */
		$image_fields = array(
			'hs_bestseller_image',
			'hs_category_image',
			'sb_left_image',
			'sb_right_image',
		);
		foreach ( $image_fields as $f ) {
			$saved[ $f ] = isset( $raw[ $f ] ) ? absint( $raw[ $f ] ) : 0;
		}

		/* ── Repeater: hero_slides ── */
		$saved['hero_slides'] = array();
		if ( ! empty( $raw['hero_slides'] ) && is_array( $raw['hero_slides'] ) ) {
			foreach ( $raw['hero_slides'] as $slide ) {
				if ( ! is_array( $slide ) ) {
					continue;
				}
				$saved['hero_slides'][] = array(
					'image'     => absint( $slide['image'] ?? 0 ),
					'eyebrow'   => sanitize_text_field( $slide['eyebrow'] ?? '' ),
					'title'     => sanitize_text_field( $slide['title'] ?? '' ),
					'subtitle'  => sanitize_text_field( $slide['subtitle'] ?? '' ),
					'cta_text'  => sanitize_text_field( $slide['cta_text'] ?? '' ),
					'cta_link'  => esc_url_raw( $slide['cta_link'] ?? '#' ),
					'cta_style' => sanitize_text_field( $slide['cta_style'] ?? 'primary' ),
				);
			}
		}

		/* ── Repeater: trust_features ── */
		$saved['trust_features'] = array();
		if ( ! empty( $raw['trust_features'] ) && is_array( $raw['trust_features'] ) ) {
			foreach ( $raw['trust_features'] as $feat ) {
				if ( ! is_array( $feat ) ) {
					continue;
				}
				$saved['trust_features'][] = array(
					'icon_svg'  => wp_kses( $feat['icon_svg'] ?? '', velure_core_svg_allowed() ),
					'title'     => sanitize_text_field( $feat['title'] ?? '' ),
					'description' => sanitize_text_field( $feat['description'] ?? '' ),
				);
			}
		}

		/* ── Repeater: brand_names ── */
		$saved['brand_names'] = array();
		if ( ! empty( $raw['brand_names'] ) && is_array( $raw['brand_names'] ) ) {
			foreach ( $raw['brand_names'] as $brand ) {
				if ( ! is_array( $brand ) ) {
					continue;
				}
				$name = sanitize_text_field( $brand['name'] ?? '' );
				if ( '' !== $name ) {
					$saved['brand_names'][] = array( 'name' => $name );
				}
			}
		}

		/* ── Repeater: instagram_images ── */
		$saved['instagram_images'] = array();
		if ( ! empty( $raw['instagram_images'] ) && is_array( $raw['instagram_images'] ) ) {
			foreach ( $raw['instagram_images'] as $img ) {
				if ( ! is_array( $img ) ) {
					continue;
				}
				$saved['instagram_images'][] = array(
					'image' => absint( $img['image'] ?? 0 ),
					'link'  => esc_url_raw( $img['link'] ?? '' ),
					'alt'   => sanitize_text_field( $img['alt'] ?? '' ),
				);
			}
		}

		/* ── Section order ── */
		$valid_sections = array( 'hero', 'features', 'categories', 'products', 'split_banner', 'marquee', 'testimonials', 'blog', 'instagram' );
		$saved['section_order'] = array();
		if ( ! empty( $raw['section_order'] ) && is_array( $raw['section_order'] ) ) {
			foreach ( $raw['section_order'] as $sec ) {
				$sec = sanitize_text_field( $sec );
				if ( in_array( $sec, $valid_sections, true ) ) {
					$saved['section_order'][] = $sec;
				}
			}
		}
		/* Ensure all sections are present in the order array */
		foreach ( $valid_sections as $sec ) {
			if ( ! in_array( $sec, $saved['section_order'], true ) ) {
				$saved['section_order'][] = $sec;
			}
		}

		update_option( VELURE_CORE_OPTION, $saved );

		wp_redirect( admin_url( 'admin.php?page=velure-front-page&tab=' . $active_tab . '&saved=1' ) );
		exit;
	}

	/* ═══════════════════════════════════════════════
	   RETRIEVE SETTINGS FOR DISPLAY
	   ═══════════════════════════════════════════════ */
	$s = velure_core_get_settings();

	/* ═══════════════════════════════════════════════
	   RENDER THE ADMIN PAGE
	   ═══════════════════════════════════════════════ */
	?>
	<div class="wrap velure-core-admin-wrap">

		<!-- Welcome banner -->
		<div class="vc-welcome-banner">
			<div>
				<h2>Velure Core v<?php echo esc_html( VELURE_CORE_VERSION ); ?></h2>
				<p>Personnalisez chaque section de votre page d'accueil. Ordre, contenu, styles — tout est configurable.</p>
			</div>
			<a href="https://velure.paris" target="_blank" rel="noopener noreferrer" class="vc-docs-btn">Documentation</a>
		</div>

		<!-- Saved notice -->
		<?php if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><strong>Reglages sauvegardes avec succes.</strong></p>
			</div>
		<?php endif; ?>

		<!-- Tab navigation -->
		<nav class="nav-tab-wrapper velure-core-tabs">
			<?php
			$tab_labels = array(
				'sections'    => 'Sections',
				'hero'        => 'Hero',
				'features'    => 'Confiance',
				'categories'  => 'Categories',
				'products'    => 'Produits',
				'banner'      => 'Banniere',
				'marquee'     => 'Marquee',
				'testimonials'=> 'Temoignages',
				'blog'        => 'Blog',
				'instagram'   => 'Instagram',
				'global'      => 'Global',
			);
			foreach ( $tab_keys as $tk ) {
				$tab_url = 'admin.php?page=velure-front-page&tab=' . $tk;
				$active  = ( $tk === $active_tab ) ? ' nav-tab-active' : '';
				echo '<a href="' . esc_url( admin_url( $tab_url ) ) . '" class="nav-tab' . $active . '">' . esc_html( $tab_labels[ $tk ] ) . '</a>';
			}
			?>
		</nav>

		<!-- Settings form -->
		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=velure-front-page' ) ); ?>" class="vc-settings-form">
			<?php wp_nonce_field( 'velure_core_save_settings' ); ?>

			<!-- Active tab content -->
			<?php
			switch ( $active_tab ) {
				case 'sections':
					velure_core_tab_sections( $s );
					break;
				case 'hero':
					velure_core_tab_hero( $s );
					break;
				case 'features':
					velure_core_tab_features( $s );
					break;
				case 'categories':
					velure_core_tab_categories( $s );
					break;
				case 'products':
					velure_core_tab_products( $s );
					break;
				case 'banner':
					velure_core_tab_banner( $s );
					break;
				case 'marquee':
					velure_core_tab_marquee( $s );
					break;
				case 'testimonials':
					velure_core_tab_testimonials( $s );
					break;
				case 'blog':
					velure_core_tab_blog( $s );
					break;
				case 'instagram':
					velure_core_tab_instagram( $s );
					break;
				case 'global':
					velure_core_tab_global( $s );
					break;
			}
			?>

			<!-- Submit -->
			<div class="vc-form-actions">
				<?php submit_button( 'Sauvegarder les reglages', 'primary large', 'velure_core_save', false ); ?>
			</div>
		</form>

	</div>
	<?php
}