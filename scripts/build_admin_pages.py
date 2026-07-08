#!/usr/bin/env python3
"""
Build admin-pages.php, admin.css, admin.js for Velure Core v3.1.0
WYSIWYG Hero Editor with Elementor-like controls.
"""
import os

BASE = '/home/z/my-project/velure-core'

# Read existing admin-pages.php to get all non-hero sections
with open(os.path.join(BASE, 'includes/admin-pages.php'), 'r') as f:
    existing = f.read()

# ═══════════════════════════════════════════════════════
# 1. ADMIN-PAGES.PHP — Complete rewrite with WYSIWYG hero
# ═══════════════════════════════════════════════════════
admin_pages = r"""<?php
/**
 * Velure Core — Admin Panel v3.1
 * Elementor-inspired WYSIWYG interface for Hero section.
 * Full typography, button styling, spacing, background & responsive controls.
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ═══════════════════════════════════════════════════════════════
   1. SETTINGS RETRIEVAL
   ═══════════════════════════════════════════════════════════════ */

function velure_core_get_settings() {
	$saved   = get_option( VELURE_CORE_OPTION, array() );
	$default = Velure_Core::instance()->default_settings();
	return wp_parse_args( $saved, $default );
}

/* ═══════════════════════════════════════════════════════════════
   2. FIELD HELPERS
   ═══════════════════════════════════════════════════════════════ */

function velure_core_field_text( $args ) {
	$id = 'vc-' . sanitize_html_class( $args['name'] );
	$val = esc_attr( $args['value'] ?? '' );
	$desc = ! empty( $args['description'] ) ? '<div class="vc-field-hint">' . esc_html( $args['description'] ) . '</div>' : '';
	$placeholder = ! empty( $args['placeholder'] ) ? ' placeholder="' . esc_attr( $args['placeholder'] ) . '"' : '';
	$icon = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';
	echo '<div class="vc-field">';
	echo '<label for="' . $id . '">' . $icon . esc_html( $args['label'] ) . '</label>';
	echo '<input type="text" id="' . $id . '" name="' . esc_attr( $args['name'] ) . '" value="' . $val . '"' . $placeholder . ' />';
	echo $desc;
	echo '</div>';
}

function velure_core_field_textarea( $args ) {
	$id = 'vc-' . sanitize_html_class( $args['name'] );
	$val = esc_textarea( $args['value'] ?? '' );
	$desc = ! empty( $args['description'] ) ? '<div class="vc-field-hint">' . esc_html( $args['description'] ) . '</div>' : '';
	$rows = ! empty( $args['rows'] ) ? intval( $args['rows'] ) : 4;
	$icon = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';
	echo '<div class="vc-field">';
	echo '<label for="' . $id . '">' . $icon . esc_html( $args['label'] ) . '</label>';
	echo '<textarea id="' . $id . '" name="' . esc_attr( $args['name'] ) . '" rows="' . $rows . '">' . $val . '</textarea>';
	echo $desc;
	echo '</div>';
}

function velure_core_field_select( $args ) {
	$id = 'vc-' . sanitize_html_class( $args['name'] );
	$val = isset( $args['value'] ) ? $args['value'] : '';
	$desc = ! empty( $args['description'] ) ? '<div class="vc-field-hint">' . esc_html( $args['description'] ) . '</div>' : '';
	$choices = $args['choices'] ?? array();
	$icon = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';
	echo '<div class="vc-field">';
	echo '<label for="' . $id . '">' . $icon . esc_html( $args['label'] ) . '</label>';
	echo '<select id="' . $id . '" name="' . esc_attr( $args['name'] ) . '">';
	foreach ( $choices as $ck => $cv ) {
		$selected = ( string ) $val === ( string ) $ck ? ' selected' : '';
		echo '<option value="' . esc_attr( $ck ) . '"' . $selected . '>' . esc_html( $cv ) . '</option>';
	}
	echo '</select>';
	echo $desc;
	echo '</div>';
}

function velure_core_field_number( $args ) {
	$id = 'vc-' . sanitize_html_class( $args['name'] );
	$val = isset( $args['value'] ) ? intval( $args['value'] ) : 0;
	$desc = ! empty( $args['description'] ) ? '<div class="vc-field-hint">' . esc_html( $args['description'] ) . '</div>' : '';
	$min = isset( $args['min'] ) ? ' min="' . intval( $args['min'] ) . '"' : '';
	$max = isset( $args['max'] ) ? ' max="' . intval( $args['max'] ) . '"' : '';
	$step = isset( $args['step'] ) ? ' step="' . esc_attr( $args['step'] ) . '"' : '';
	$unit = ! empty( $args['unit'] ) ? $args['unit'] : '';
	$icon = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';
	echo '<div class="vc-field">';
	echo '<label for="' . $id . '">' . $icon . esc_html( $args['label'] ) . '</label>';
	if ( $unit ) {
		echo '<div class="vc-field-number-group">';
		echo '<input type="number" id="' . $id . '" name="' . esc_attr( $args['name'] ) . '" value="' . $val . '"' . $min . $max . $step . ' />';
		echo '<span class="vc-field-number-unit">' . esc_html( $unit ) . '</span>';
		echo '</div>';
	} else {
		echo '<input type="number" id="' . $id . '" name="' . esc_attr( $args['name'] ) . '" value="' . $val . '"' . $min . $max . $step . ' />';
	}
	echo $desc;
	echo '</div>';
}

function velure_core_field_range( $args ) {
	$id = 'vc-' . sanitize_html_class( $args['name'] );
	$val = isset( $args['value'] ) ? intval( $args['value'] ) : ( $args['default'] ?? 0 );
	$min = $args['min'] ?? 0;
	$max = $args['max'] ?? 100;
	$step = $args['step'] ?? 1;
	$unit = ! empty( $args['unit'] ) ? $args['unit'] : '';
	$icon = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';
	echo '<div class="vc-field">';
	echo '<label>' . $icon . esc_html( $args['label'] ) . '</label>';
	echo '<div class="vc-range-wrap">';
	echo '<input type="range" name="' . esc_attr( $args['name'] ) . '" value="' . $val . '" min="' . $min . '" max="' . $max . '" step="' . $step . '" data-range="' . esc_attr( $args['name'] ) . '" />';
	echo '<span class="vc-range-value">' . $val . ( $unit ? ' ' . esc_html( $unit ) : '' ) . '</span>';
	echo '</div>';
	if ( ! empty( $args['description'] ) ) {
		echo '<div class="vc-field-hint">' . esc_html( $args['description'] ) . '</div>';
	}
	echo '</div>';
}

function velure_core_field_color( $args ) {
	$val = $args['value'] ?? '#C8A97E';
	if ( empty( $val ) ) $val = '#C8A97E';
	$icon = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';
	echo '<div class="vc-field">';
	echo '<label>' . $icon . esc_html( $args['label'] ) . '</label>';
	echo '<div class="vc-color-wrap">';
	echo '<div class="vc-color-swatch" style="background:' . esc_attr( $val ) . '">';
	echo '<input type="color" name="' . esc_attr( $args['name'] ) . '" value="' . esc_attr( $val ) . '" data-color="' . esc_attr( $args['name'] ) . '" />';
	echo '</div>';
	echo '<input type="text" name="' . esc_attr( $args['name'] ) . '_hex" value="' . esc_attr( $val ) . '" class="vc-color-text" data-color-text="' . esc_attr( $args['name'] ) . '" />';
	echo '</div>';
	echo '</div>';
}

function velure_core_field_toggle( $args ) {
	$name = esc_attr( $args['name'] );
	$checked = ! empty( $args['value'] ) ? 'checked' : '';
	$icon = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';
	echo '<div class="vc-field vc-toggle-field">';
	echo '<label>' . $icon . esc_html( $args['label'] ) . '</label>';
	echo '<label class="vc-toggle">';
	echo '<input type="checkbox" name="' . $name . '" value="1" ' . $checked . ' />';
	echo '<span class="vc-toggle-slider"></span>';
	echo '</label>';
	if ( ! empty( $args['description'] ) ) {
		echo '<div class="vc-field-hint" style="grid-column:1/-1">' . esc_html( $args['description'] ) . '</div>';
	}
	echo '</div>';
}

function velure_core_field_image( $args ) {
	$name = esc_attr( $args['name'] );
	$val = intval( $args['value'] ?? 0 );
	$url = $val ? wp_get_attachment_url( $val ) : '';
	$preview = $url
		? '<img src="' . esc_url( $url ) . '" class="vc-img-preview" />'
		: '<div class="vc-img-placeholder"><span class="vc-img-placeholder-icon">&#128247;</span>Cliquer ou glisser une image</div>';
	$icon = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';
	echo '<div class="vc-field vc-image-field">';
	echo '<label>' . $icon . esc_html( $args['label'] ) . '</label>';
	echo '<div class="vc-img-wrap">' . $preview . '</div>';
	echo '<input type="hidden" name="' . $name . '" value="' . $val . '" class="vc-img-id" />';
	echo '<div class="vc-img-actions">';
	echo '<button type="button" class="vc-btn vc-btn-ghost vc-btn-sm vc-img-btn">&#128194; Choisir</button>';
	echo '<button type="button" class="vc-btn vc-btn-danger vc-btn-sm vc-img-remove" style="' . ( $val ? '' : 'display:none;' ) . '">&#128465; Supprimer</button>';
	echo '</div>';
	echo '</div>';
}

/* ── Repeater helpers ── */
function velure_core_field_repeater_header( $args ) {
	$button_label = $args['button_label'] ?? 'Ajouter un element';
	echo '<div class="vc-repeater-header">';
	echo '<h4>' . esc_html( $args['label'] ) . '</h4>';
	echo '<button type="button" class="vc-btn vc-btn-ghost vc-btn-sm vc-repeater-add" data-repeater="' . esc_attr( $args['repeater_id'] ?? '' ) . '">+ ' . esc_html( $button_label ) . '</button>';
	echo '</div>';
}

function velure_core_field_repeater_row_start( $index, $title = '' ) {
	echo '<div class="vc-repeater-row open" data-index="' . intval( $index ) . '">';
	echo '<div class="vc-repeater-row-header">';
	echo '<span class="vc-repeater-handle" title="Glisser pour reordonner">&#9776;</span>';
	echo '<span class="vc-repeater-title">' . esc_html( $title ) . '</span>';
	echo '<div class="vc-repeater-row-actions">';
	echo '<button type="button" class="vc-repeater-toggle-btn" title="Plier/deplier">&#9660;</button>';
	echo '<button type="button" class="vc-repeater-remove" title="Supprimer">&#10005;</button>';
	echo '</div>';
	echo '</div>';
	echo '<div class="vc-repeater-fields">';
}

function velure_core_field_repeater_row_end() {
	echo '</div><!-- .vc-repeater-fields -->';
	echo '</div><!-- .vc-repeater-row -->';
}

/* ── Accordion helper ── */
function velure_core_accordion_start( $id, $title, $icon = '', $open = false ) {
	echo '<div class="vc-accordion-item' . ( $open ? ' open' : '' ) . '" id="' . esc_attr( $id ) . '">';
	echo '<div class="vc-accordion-header">';
	echo '<span class="vc-accordion-icon">&#9654;</span>';
	echo '<span class="vc-accordion-title">' . ( $icon ? '<span style="margin-right:6px">' . $icon . '</span>' : '' ) . esc_html( $title ) . '</span>';
	echo '</div>';
	echo '<div class="vc-accordion-body">';
}

function velure_core_accordion_end() {
	echo '</div><!-- .vc-accordion-body -->';
	echo '</div><!-- .vc-accordion-item -->';
}

/* ── WYSIWYG Typography Group Helper ── */
function velure_core_field_typo_group( $args ) {
	$p = $args['prefix'];
	$s = $args['settings'];
	$label = $args['label'] ?? '';
	$icon = $args['icon'] ?? '&#128196;';
	$controls = $args['controls'] ?? array('font_family','font_size','font_weight','color','letter_spacing','text_transform','line_height','margin_bottom');

	echo '<div class="vc-typo-group">';
	echo '<div class="vc-typo-group-header">';
	echo '<span class="vc-typo-group-icon">' . $icon . '</span>';
	echo '<span class="vc-typo-group-label">' . esc_html( $label ) . '</span>';
	echo '</div>';
	echo '<div class="vc-typo-group-body">';
	echo '<div class="vc-field-row-3">';

	if ( in_array( 'font_family', $controls ) ) {
		velure_core_field_select( array(
			'label' => 'Police', 'name' => $p . '_font_family',
			'value' => $s[ $p . '_font_family' ] ?? '',
			'choices' => array(
				'Inter' => 'Inter', 'Playfair Display' => 'Playfair Display',
				'Cormorant Garamond' => 'Cormorant Garamond',
				'Georgia' => 'Georgia', 'system-ui' => 'Systeme',
			),
		) );
	}
	if ( in_array( 'font_size', $controls ) ) {
		velure_core_field_number( array(
			'label' => 'Taille', 'name' => $p . '_font_size',
			'value' => $s[ $p . '_font_size' ] ?? '', 'min' => 8, 'max' => 120, 'unit' => 'px',
		) );
	}
	if ( in_array( 'font_weight', $controls ) ) {
		velure_core_field_select( array(
			'label' => 'Graisse', 'name' => $p . '_font_weight',
			'value' => $s[ $p . '_font_weight' ] ?? '',
			'choices' => array( '300'=>'Light','400'=>'Regular','500'=>'Medium','600'=>'Semi-Bold','700'=>'Bold','800'=>'Extra-Bold' ),
		) );
	}
	echo '</div>';

	echo '<div class="vc-field-row-3">';
	if ( in_array( 'color', $controls ) ) {
		velure_core_field_color( array(
			'label' => 'Couleur', 'name' => $p . '_color',
			'value' => $s[ $p . '_color' ] ?? '',
		) );
	}
	if ( in_array( 'letter_spacing', $controls ) ) {
		velure_core_field_number( array(
			'label' => 'Espacement', 'name' => $p . '_letter_spacing',
			'value' => $s[ $p . '_letter_spacing' ] ?? '', 'min' => -5, 'max' => 20, 'step' => 0.5, 'unit' => 'px',
		) );
	}
	if ( in_array( 'text_transform', $controls ) ) {
		velure_core_field_select( array(
			'label' => 'Casse', 'name' => $p . '_text_transform',
			'value' => $s[ $p . '_text_transform' ] ?? '',
			'choices' => array( 'none'=>'Aucune','uppercase'=>'MAJUSCULES','lowercase'=>'minuscules','capitalize'=>'Capitale' ),
		) );
	}
	echo '</div>';

	$extra = array();
	if ( in_array( 'line_height', $controls ) ) {
		$extra[] = $p . '_line_height';
	}
	if ( in_array( 'margin_bottom', $controls ) ) {
		$extra[] = $p . '_margin_bottom';
	}
	if ( ! empty( $extra ) ) {
		echo '<div class="vc-field-row-' . count( $extra ) . '">';
		if ( in_array( 'line_height', $controls ) ) {
			velure_core_field_number( array(
				'label' => 'Hauteur de ligne', 'name' => $p . '_line_height',
				'value' => $s[ $p . '_line_height' ] ?? '', 'min' => 0.8, 'max' => 3, 'step' => 0.1,
			) );
		}
		if ( in_array( 'margin_bottom', $controls ) ) {
			velure_core_field_number( array(
				'label' => 'Marge bas', 'name' => $p . '_margin_bottom',
				'value' => $s[ $p . '_margin_bottom' ] ?? '', 'min' => 0, 'max' => 80, 'unit' => 'px',
			) );
		}
		echo '</div>';
	}

	echo '</div></div>';
}

/* ═══════════════════════════════════════════════════════════════
   3. PRE-BUILT TEMPLATES (unchanged)
   ═══════════════════════════════════════════════════════════════ */

function velure_core_get_templates( $section ) {
	$templates = array(
		'hero' => array(
			array( 'name'=>'Luxe Minimaliste','desc'=>'Hero plein ecran, texte centre, overlay sombre','badge'=>'popular','icon'=>'&#9733;','data'=>array('hero_height'=>'fullscreen','hero_text_align'=>'center','hero_text_color'=>'light','hero_overlay_opacity'=>50,'hero_autoplay'=>1,'hero_autoplay_speed'=>7000,'hero_show_side'=>0,'hero_slides'=>array(array('image'=>0,'eyebrow'=>'Collection Automne 2026','title'=>"L'Art du Detail",'subtitle'=>'Des creations uniques pour un style intemporel.','cta_text'=>'DECOUVRIR','cta_link'=>'/boutique/','cta_style'=>'gold'))) ),
			array( 'name'=>'Boutique Moderne','desc'=>'Hero standard avec blocs lateraux','badge'=>'','icon'=>'&#128722;','data'=>array('hero_height'=>'standard','hero_text_align'=>'left','hero_text_color'=>'light','hero_overlay_opacity'=>40,'hero_autoplay'=>1,'hero_autoplay_speed'=>5000,'hero_show_side'=>1,'hero_slides'=>array(array('image'=>0,'eyebrow'=>'Nouveautes','title'=>'Nouvelle Collection','subtitle'=>'Les pieces incontournables.','cta_text'=>'VOIR LA COLLECTION','cta_link'=>'/boutique/','cta_style'=>'primary')),'hs_bestseller_label'=>'Best-Seller','hs_bestseller_title'=>'Sac Elegance','hs_bestseller_price'=>'285,00 EUR','hs_bestseller_cta'=>'VOIR LE PRODUIT','hs_bestseller_link'=>'#','hs_category_label'=>'Capsule','hs_category_title'=>'Les Accessoires Essentiels','hs_category_cta_link'=>'/categorie/accessoires/') ),
			array( 'name'=>'Editorial Bold','desc'=>'Grand hero typographie impactante','badge'=>'new','icon'=>'&#9998;','data'=>array('hero_height'=>'large','hero_text_align'=>'left','hero_text_color'=>'light','hero_overlay_opacity'=>60,'hero_autoplay'=>0,'hero_show_side'=>0,'hero_slides'=>array(array('image'=>0,'eyebrow'=>'EDITION LIMITEE','title'=>'REDEFINIR<br>L\'ELEGANCE','subtitle'=>'Une collection exclusive.','cta_text'=>'RESERVER','cta_link'=>'/new-collection/','cta_style'=>'outline')),'hero_title_font_size'=>72,'hero_title_font_weight'=>800,'hero_eyebrow_letter_spacing'=>4) ),
		),
		'features' => array(
			array( 'name'=>'Confiance Luxe','desc'=>'4 piliers de confiance avec icones','badge'=>'popular','icon'=>'&#128142;','data'=>array('feat_bg_style'=>'soft','feat_padding'=>'normal','feat_bottom_border'=>1,'trust_features'=>array(array('icon_svg'=>'<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 18H3a2 2 0 01-2-2V8a2 2 0 012-2h3l2-3h6l2 3h3a2 2 0 012 2v8a2 2 0 01-2 2h-2"/><circle cx="12" cy="13" r="3"/></svg>','title'=>'Livraison Express','description'=>'Sous 24-48h en France metropolitaine'),array('icon_svg'=>'<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>','title'=>'Qualite Garantie','description'=>'Matiere premium et finitions soignees'),array('icon_svg'=>'<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16v16H4z"/><path d="M4 10h16M10 4v16"/></svg>','title'=>'Retours Gratuits','description'=>'30 jours pour changer d\'avis'),array('icon_svg'=>'<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>','title'=>'Paiement Securise','description'=>'SSL 256-bit et cryptage securise')) ) ),
			array( 'name'=>'Minimal Clean','desc'=>'3 elements simples, fond sombre','badge'=>'','icon'=>'&#9711;','data'=>array('feat_bg_style'=>'dark','feat_padding'=>'compact','feat_bottom_border'=>0,'trust_features'=>array(array('icon_svg'=>'','title'=>'Livraison Offerte','description'=>'Des 150 EUR d\'achat'),array('icon_svg'=>'','title'=>'Retours Faciles','description'=>'Sous 30 jours'),array('icon_svg'=>'','title'=>'Service Client','description'=>'Disponible 7j/7')) ) ),
		),
		'categories' => array( array( 'name'=>'Boutique Complete','desc'=>'Titre, description et CTA','badge'=>'popular','icon'=>'&#127970;','data'=>array('cat_eyebrow'=>'Collections','section_title_categories'=>'Explorer par Univers','cat_description'=>'Explorez nos univers.','cat_cta_text'=>'Toutes les categories','cat_cta_link'=>'/boutique/','cat_bg_style'=>'base','cat_display_count'=>10) ) ),
		'products' => array( array( 'name'=>'Selection Vedettes','desc'=>'Grille 4 colonnes','badge'=>'popular','icon'=>'&#128722;','data'=>array('prod_eyebrow'=>'Selection','section_title_products'=>'Pieces Vedettes','prod_description'=>'Nos pieces les plus appreciees.','prod_cta_text'=>'Voir toute la boutique','prod_cta_link'=>'/boutique/','prod_columns'=>'4','prod_mode'=>'auto','featured_products_count'=>8,'prod_bg_style'=>'soft','prod_sort'=>'date') ) ),
		'banner' => array( array( 'name'=>'Collection Duo','desc'=>'Banniere 50/50','badge'=>'popular','icon'=>'&#128248;','data'=>array('sb_layout'=>'50-50','sb_left_eyebrow'=>'Collection AW25','sb_left_title'=>'La Nouvelle Collection','sb_left_desc'=>'Des silhouettes audacieuses.','sb_left_cta_text'=>'Decouvrir','sb_left_cta_link'=>'/new-collection/','sb_left_cta_style'=>'gold','sb_left_style'=>'dark','sb_right_eyebrow'=>'Edition Limitee','sb_right_title'=>"Accessoires d'Exception",'sb_right_desc'=>'Sacs, bijoux et ceintures.','sb_right_cta_text'=>'Explorer','sb_right_cta_link'=>'/categorie/accessoires/','sb_right_cta_style'=>'outline','sb_right_style'=>'light') ) ),
	);
	return $templates[ $section ] ?? array();
}

function velure_core_render_templates( $section, $s ) {
	$templates = velure_core_get_templates( $section );
	if ( empty( $templates ) ) return;
	echo '<div class="vc-templates-bar">';
	echo '<div class="vc-templates-bar-title">&#9998; Modeles preconstruits</div>';
	echo '<div class="vc-templates-grid">';
	foreach ( $templates as $t ) {
		$badge_html = '';
		if ( ! empty( $t['badge'] ) ) {
			$badge_html = '<span class="vc-template-card-badge ' . esc_attr( $t['badge'] ) . '">' . ( 'popular' === $t['badge'] ? 'Populaire' : 'Nouveau' ) . '</span>';
		}
		echo '<div class="vc-template-card" data-template-section="' . esc_attr( $section ) . '" data-template="' . esc_attr( $t['name'] ) . '">';
		echo $badge_html;
		echo '<div class="vc-template-card-thumb">' . ( $t['icon'] ?? '' ) . '</div>';
		echo '<div class="vc-template-card-name">' . esc_html( $t['name'] ) . '</div>';
		echo '<div class="vc-template-card-desc">' . esc_html( $t['desc'] ) . '</div>';
		echo '</div>';
	}
	echo '</div></div>';
}

function velure_core_section_preview_card( $title, $badge, $icon, $desc ) {
	echo '<div class="vc-preview-card">';
	echo '<div class="vc-preview-header"><div class="vc-preview-header-left"><h3>' . esc_html( $title ) . '</h3><span class="vc-preview-badge">' . esc_html( $badge ) . '</span></div></div>';
	echo '<div class="vc-preview-body"><div class="vc-preview-visual"><span class="vc-preview-visual-icon">' . $icon . '</span><span class="vc-preview-visual-label">' . esc_html( $title ) . '</span><span class="vc-preview-visual-desc">' . esc_html( $desc ) . '</span></div></div>';
	echo '</div>';
}

/* ═══════════════════════════════════════════════════════════════
   4. SECTION TAB RENDERING
   ═══════════════════════════════════════════════════════════════ */

/* ── Tab: Sections ── */
function velure_core_tab_sections( $s ) {
	echo '<div class="vc-section-panel active" data-panel="sections">';
	velure_core_section_preview_card( 'Gestionnaire de Sections', 'Layout', '&#128202;', 'Activez, desactivez et reordonnez les 9 sections.' );
	velure_core_accordion_start( 'acc-visibility', 'Visibilite', '&#128065;', true );
	echo '<div class="vc-field-row">';
	$visibility = array('show_hero'=>'Hero (Slider) &#127916;','show_features'=>'Barre de confiance &#128142;','show_categories'=>'Categories &#127970;','show_products'=>'Produits vedettes &#128722;','show_split_banner'=>'Banniere splitee &#128248;','show_marquee'=>'Marquee &#127926;','show_testimonials'=>'Temoignages &#128172;','show_blog'=>'Blog &#128240;','show_instagram'=>'Instagram &#128247;');
	foreach ( $visibility as $key => $info ) {
		echo '<div class="vc-field"><label>' . $info . '</label><label class="vc-toggle"><input type="checkbox" name="' . $key . '" value="1"' . ( ! empty( $s[ $key ] ) ? ' checked' : '' ) . ' /><span class="vc-toggle-slider"></span></label></div>';
	}
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-order', 'Ordre d\'Affichage', '&#128295;', false );
	echo '<p class="vc-field-hint" style="margin-bottom:14px">Glissez-deposez pour reordonner.</p>';
	$section_labels = array('hero'=>'Hero (Slider)','features'=>'Barre de confiance','categories'=>'Categories','products'=>'Produits vedettes','split_banner'=>'Banniere splitee','marquee'=>'Marquee','testimonials'=>'Temoignages','blog'=>'Blog','instagram'=>'Instagram');
	$current_order = $s['section_order'] ?? array_keys( $section_labels );
	echo '<ul class="vc-section-order-list">';
	foreach ( $current_order as $sec ) {
		$info = $section_labels[ $sec ] ?? $sec;
		echo '<li><span class="vc-section-order-handle">&#9776;</span><span class="vc-section-order-label">' . esc_html( $info ) . '</span><input type="hidden" name="section_order[]" value="' . esc_attr( $sec ) . '" /></li>';
	}
	echo '</ul>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Hero (WYSIWYG) ── */
function velure_core_tab_hero( $s ) {
	echo '<div class="vc-section-panel" data-panel="hero">';
	velure_core_section_preview_card( 'Section Hero', 'WYSIWYG', '&#127916;', 'Contenu, typographie, bouton, espacement, image de fond et responsive.' );
	velure_core_render_templates( 'hero', $s );

	/* ── Accordion: Contenu (Slides + Side blocks) ── */
	velure_core_accordion_start( 'acc-hero-content', 'Contenu (Slides & Blocs)', '&#128221;', true );
	velure_core_accordion_start( 'acc-hero-slider', 'Parametres du Slider', '&#9881;', true );
	echo '<div class="vc-field-row-3">';
	velure_core_field_select( array( 'label'=>'Hauteur','name'=>'hero_height','value'=>$s['hero_height']??'standard','icon'=>'&#8597;','choices'=>array('small'=>'Petite (50vh)','standard'=>'Standard (70vh)','large'=>'Grande (85vh)','fullscreen'=>'Plein ecran (100vh)') ) );
	velure_core_field_select( array( 'label'=>'Alignement','name'=>'hero_text_align','value'=>$s['hero_text_align']??'left','icon'=>'&#8644;','choices'=>array('left'=>'Gauche','center'=>'Centre','right'=>'Droite') ) );
	velure_core_field_select( array( 'label'=>'Couleur texte','name'=>'hero_text_color','value'=>$s['hero_text_color']??'light','icon'=>'&#127912;','choices'=>array('light'=>'Clair','dark'=>'Fonce') ) );
	echo '</div>';
	echo '<div class="vc-field-row-3">';
	velure_core_field_range( array( 'label'=>'Opacite overlay','name'=>'hero_overlay_opacity','value'=>$s['hero_overlay_opacity']??40,'min'=>0,'max'=>100,'step'=>5,'unit'=>'%','description'=>'0=transparent, 100=noir total.' ) );
	velure_core_field_range( array( 'label'=>'Vitesse autoplay','name'=>'hero_autoplay_speed','value'=>$s['hero_autoplay_speed']??6000,'min'=>2000,'max'=>20000,'step'=>500,'unit'=>'ms' ) );
	velure_core_field_toggle( array( 'label'=>'Lecture auto','name'=>'hero_autoplay','value'=>$s['hero_autoplay']??1,'icon'=>'&#9654;' ) );
	echo '</div>';
	velure_core_accordion_end();

	velure_core_accordion_start( 'acc-hero-side', 'Blocs Lateraux', '&#128464;', false );
	velure_core_field_toggle( array( 'label'=>'Afficher les blocs lateraux','name'=>'hero_show_side','value'=>$s['hero_show_side']??0,'description'=>'Best-seller + bloc categorie.' ) );
	echo '<div id="vc-hero-side-blocks" style="' . ( ! empty( $s['hero_show_side'] ) ? '' : 'display:none' ) . '">';
	velure_core_accordion_start( 'acc-hero-bs', 'Best-Seller', '&#11088;', true );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Label','name'=>'hs_bestseller_label','value'=>$s['hs_bestseller_label']??'Best-Seller' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'hs_bestseller_title','value'=>$s['hs_bestseller_title']??'Sac Elegance' ) );
	velure_core_field_text( array( 'label'=>'Prix','name'=>'hs_bestseller_price','value'=>$s['hs_bestseller_price']??'285,00 EUR' ) );
	echo '</div>';
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Texte CTA','name'=>'hs_bestseller_cta','value'=>$s['hs_bestseller_cta']??'VOIR LE PRODUIT' ) );
	velure_core_field_text( array( 'label'=>'Lien CTA','name'=>'hs_bestseller_link','value'=>$s['hs_bestseller_link']??'#' ) );
	velure_core_field_image( array( 'label'=>'Image','name'=>'hs_bestseller_image','value'=>$s['hs_bestseller_image']??0 ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-hero-cat', 'Categorie', '&#128193;', false );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Label','name'=>'hs_category_label','value'=>$s['hs_category_label']??'Capsule' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'hs_category_title','value'=>$s['hs_category_title']??'Les Accessoires Essentiels' ) );
	velure_core_field_text( array( 'label'=>'Lien','name'=>'hs_category_cta_link','value'=>$s['hs_category_cta_link']??'/categorie/accessoires/' ) );
	echo '</div>';
	velure_core_field_image( array( 'label'=>'Image','name'=>'hs_category_image','value'=>$s['hs_category_image']??0 ) );
	velure_core_accordion_end();
	echo '</div>';
	velure_core_accordion_end();

	/* Slides repeater */
	velure_core_accordion_start( 'acc-hero-slides', 'Slides', '&#128247;', false );
	$slides = is_array( $s['hero_slides'] ?? null ) ? $s['hero_slides'] : array();
	velure_core_field_repeater_header( array( 'label'=>'Slides','button_label'=>'Ajouter un slide','repeater_id'=>'vc-hero-slides-repeater' ) );
	echo '<div class="vc-repeater" id="vc-hero-slides-repeater">';
	foreach ( $slides as $i => $slide ) {
		if ( ! is_array( $slide ) ) $slide = array();
		velure_core_field_repeater_row_start( $i, 'Slide ' . ( $i + 1 ) );
		velure_core_field_image( array( 'label'=>'Image','name'=>'hero_slides[' . $i . '][image]','value'=>$slide['image']??0 ) );
		echo '<div class="vc-field-row">';
		velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'hero_slides[' . $i . '][eyebrow]','value'=>$slide['eyebrow']??'','placeholder'=>'Nouvelle Collection' ) );
		velure_core_field_text( array( 'label'=>'Titre','name'=>'hero_slides[' . $i . '][title]','value'=>$slide['title']??'' ) );
		echo '</div>';
		velure_core_field_textarea( array( 'label'=>'Sous-titre','name'=>'hero_slides[' . $i . '][subtitle]','value'=>$slide['subtitle']??'','rows'=>2 ) );
		echo '<div class="vc-field-row-3">';
		velure_core_field_text( array( 'label'=>'Texte CTA','name'=>'hero_slides[' . $i . '][cta_text]','value'=>$slide['cta_text']??'' ) );
		velure_core_field_text( array( 'label'=>'Lien CTA','name'=>'hero_slides[' . $i . '][cta_link]','value'=>$slide['cta_link']??'#' ) );
		velure_core_field_select( array( 'label'=>'Style CTA','name'=>'hero_slides[' . $i . '][cta_style]','value'=>$slide['cta_style']??'primary','choices'=>array('primary'=>'Principal','secondary'=>'Secondaire','gold'=>'Dore','outline'=>'Contour') ) );
		echo '</div>';
		velure_core_field_repeater_row_end();
	}
	echo '<div class="vc-repeater-template" id="vc-hero-slides-repeater-template">';
	velure_core_field_repeater_row_start( '__INDEX__', 'Slide __NUM__' );
	velure_core_field_image( array( 'label'=>'Image','name'=>'hero_slides[__INDEX__][image]','value'=>0 ) );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'hero_slides[__INDEX__][eyebrow]','value'=>'' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'hero_slides[__INDEX__][title]','value'=>'' ) );
	echo '</div>';
	velure_core_field_textarea( array( 'label'=>'Sous-titre','name'=>'hero_slides[__INDEX__][subtitle]','value'=>'','rows'=>2 ) );
	echo '<div class="vc-field-row-3">';
	velure_core_field_text( array( 'label'=>'Texte CTA','name'=>'hero_slides[__INDEX__][cta_text]','value'=>'' ) );
	velure_core_field_text( array( 'label'=>'Lien CTA','name'=>'hero_slides[__INDEX__][cta_link]','value'=>'#' ) );
	velure_core_field_select( array( 'label'=>'Style CTA','name'=>'hero_slides[__INDEX__][cta_style]','value'=>'primary','choices'=>array('primary'=>'Principal','secondary'=>'Secondaire','gold'=>'Dore','outline'=>'Contour') ) );
	echo '</div>';
	velure_core_field_repeater_row_end();
	echo '</div></div>';
	velure_core_accordion_end();
	velure_core_accordion_end();

	/* ── Accordion: Typography ── */
	velure_core_accordion_start( 'acc-hero-typo', 'Typographie', '&#128196;', false );
	velure_core_field_typo_group( array( 'prefix'=>'hero_eyebrow','label'=>'Eyebrow (ligne au-dessus du titre)','icon'=>'&#128196;','settings'=>$s,'controls'=>array('font_family','font_size','font_weight','color','letter_spacing','text_transform','margin_bottom') ) );
	velure_core_field_typo_group( array( 'prefix'=>'hero_title','label'=>'Titre Principal','icon'=>'&#127912;','settings'=>$s,'controls'=>array('font_family','font_size','font_weight','color','letter_spacing','line_height','margin_bottom') ) );
	velure_core_field_typo_group( array( 'prefix'=>'hero_subtitle','label'=>'Sous-titre','icon'=>'&#128221;','settings'=>$s,'controls'=>array('font_family','font_size','font_weight','color','line_height','margin_bottom') ) );
	velure_core_accordion_end();

	/* ── Accordion: CTA Button Styling ── */
	velure_core_accordion_start( 'acc-hero-cta-style', 'Bouton CTA', '&#128279;', false );
	echo '<div class="vc-field-row-3">';
	velure_core_field_number( array( 'label'=>'Taille police','name'=>'hero_cta_font_size','value'=>$s['hero_cta_font_size']??13,'min'=>10,'max'=>24,'unit'=>'px' ) );
	velure_core_field_select( array( 'label'=>'Graisse','name'=>'hero_cta_font_weight','value'=>$s['hero_cta_font_weight']??600,'choices'=>array('400'=>'Regular','500'=>'Medium','600'=>'Semi-Bold','700'=>'Bold') ) );
	velure_core_field_select( array( 'label'=>'Casse','name'=>'hero_cta_text_transform','value'=>$s['hero_cta_text_transform']??'uppercase','choices'=>array('none'=>'Aucune','uppercase'=>'MAJUSCULES','capitalize'=>'Capitale') ) );
	echo '</div>';
	echo '<div class="vc-field-row-3">';
	velure_core_field_number( array( 'label'=>'Espacement lettres','name'=>'hero_cta_letter_spacing','value'=>$s['hero_cta_letter_spacing']??1.5,'min'=>-2,'max'=>10,'step'=>0.5,'unit'=>'px' ) );
	velure_core_field_number( array( 'label'=>'Padding horizontal','name'=>'hero_cta_padding_x','value'=>$s['hero_cta_padding_x']??32,'min'=>0,'max'=>80,'unit'=>'px' ) );
	velure_core_field_number( array( 'label'=>'Padding vertical','name'=>'hero_cta_padding_y','value'=>$s['hero_cta_padding_y']??15,'min'=>0,'max'=>40,'unit'=>'px' ) );
	echo '</div>';
	echo '<div class="vc-field-row-3">';
	velure_core_field_number( array( 'label'=>'Border radius','name'=>'hero_cta_border_radius','value'=>$s['hero_cta_border_radius']??0,'min'=>0,'max'=>50,'unit'=>'px' ) );
	velure_core_field_number( array( 'label'=>'Border width','name'=>'hero_cta_border_width','value'=>$s['hero_cta_border_width']??0,'min'=>0,'max'=>5,'unit'=>'px' ) );
	velure_core_field_color( array( 'label'=>'Border couleur','name'=>'hero_cta_border_color','value'=>$s['hero_cta_border_color']??'#1A1A1A' ) );
	echo '</div>';
	echo '<div class="vc-typo-divider">Couleurs du bouton</div>';
	echo '<div class="vc-field-row-3">';
	velure_core_field_color( array( 'label'=>'Fond','name'=>'hero_cta_bg_color','value'=>$s['hero_cta_bg_color']??'#1A1A1A' ) );
	velure_core_field_color( array( 'label'=>'Texte','name'=>'hero_cta_text_color','value'=>$s['hero_cta_text_color']??'#FFFFFF' ) );
	echo '</div>';
	echo '<div class="vc-field-row-3">';
	velure_core_field_color( array( 'label'=>'Fond (hover)','name'=>'hero_cta_hover_bg_color','value'=>$s['hero_cta_hover_bg_color']??'#C8A97E' ) );
	velure_core_field_color( array( 'label'=>'Texte (hover)','name'=>'hero_cta_hover_text_color','value'=>$s['hero_cta_hover_text_color']??'#1A1A1A' ) );
	echo '</div>';
	velure_core_accordion_end();

	/* ── Accordion: Background Image ── */
	velure_core_accordion_start( 'acc-hero-bg', 'Image de fond', '&#127912;', false );
	echo '<div class="vc-field-row-2">';
	velure_core_field_select( array( 'label'=>'Position du fond','name'=>'hero_bg_position','value'=>$s['hero_bg_position']??'center','choices'=>array('center'=>'Centre','top'=>'Haut','bottom'=>'Bas','left'=>'Gauche','right'=>'Droite','top left'=>'Haut Gauche','top right'=>'Haut Droite','bottom left'=>'Bas Gauche','bottom right'=>'Bas Droite') ) );
	velure_core_field_select( array( 'label'=>'Taille du fond','name'=>'hero_bg_size','value'=>$s['hero_bg_size']??'cover','choices'=>array('cover'=>'Couvrir (cover)','contain'=>'Contenir (contain)','auto'=>'Auto','100% 100%'=>'Etirer') ) );
	echo '</div>';
	velure_core_accordion_end();

	/* ── Accordion: Spacing & Layout ── */
	velure_core_accordion_start( 'acc-hero-spacing', 'Espacement & Layout', '&#128208;', false );
	echo '<div class="vc-field-row-2">';
	velure_core_field_number( array( 'label'=>'Largeur max contenu','name'=>'hero_content_max_width','value'=>$s['hero_content_max_width']??580,'min'=>200,'max'=>1200,'unit'=>'px','description'=>'Largeur max du bloc de texte.' ) );
	velure_core_field_range( array( 'label'=>'Padding vertical','name'=>'hero_padding_v','value'=>$s['hero_padding_v']??80,'min'=>0,'max'=>200,'step'=>5,'unit'=>'px','description'=>'Espacement au-dessus et en-dessous du contenu.' ) );
	echo '</div>';
	velure_core_accordion_end();

	/* ── Accordion: Side Blocks Styling ── */
	velure_core_accordion_start( 'acc-hero-side-style', 'Blocs Lateraux (Style)', '&#128464;', false );
	echo '<div class="vc-field-row-3">';
	velure_core_field_number( array( 'label'=>'Largeur colonne','name'=>'hero_side_width','value'=>$s['hero_side_width']??320,'min'=>200,'max'=>500,'unit'=>'px' ) );
	velure_core_field_number( array( 'label'=>'Espacement cartes','name'=>'hero_side_gap','value'=>$s['hero_side_gap']??12,'min'=>0,'max'=>30,'unit'=>'px' ) );
	velure_core_field_number( array( 'label'=>'Border radius','name'=>'hero_side_card_radius','value'=>$s['hero_side_card_radius']??8,'min'=>0,'max'=>30,'unit'=>'px' ) );
	echo '</div>';
	echo '<div class="vc-field-row">';
	velure_core_field_number( array( 'label'=>'Hauteur image carte','name'=>'hero_side_card_img_height','value'=>$s['hero_side_card_img_height']??160,'min'=>80,'max'=>400,'unit'=>'px' ) );
	echo '</div>';
	velure_core_accordion_end();

	/* ── Accordion: Responsive (Mobile) ── */
	velure_core_accordion_start( 'acc-hero-responsive', 'Responsive (Mobile)', '&#128241;', false );
	echo '<div class="vc-responsive-notice vc-field-hint" style="margin-bottom:14px">&#128241; Ces valeurs s\'appliquent sur ecrans de moins de 768px.</div>';
	echo '<div class="vc-field-row-3">';
	velure_core_field_number( array( 'label'=>'Titre (mobile)','name'=>'hero_title_size_mobile','value'=>$s['hero_title_size_mobile']??36,'min'=>20,'max'=>72,'unit'=>'px' ) );
	velure_core_field_number( array( 'label'=>'Sous-titre (mobile)','name'=>'hero_subtitle_size_mobile','value'=>$s['hero_subtitle_size_mobile']??15,'min'=>10,'max'=>30,'unit'=>'px' ) );
	velure_core_field_number( array( 'label'=>'CTA (mobile)','name'=>'hero_cta_size_mobile','value'=>$s['hero_cta_size_mobile']??12,'min'=>8,'max'=>20,'unit'=>'px' ) );
	echo '</div>';
	echo '<div class="vc-field-row-2">';
	velure_core_field_number( array( 'label'=>'Eyebrow (mobile)','name'=>'hero_eyebrow_size_mobile','value'=>$s['hero_eyebrow_size_mobile']??11,'min'=>8,'max'=>20,'unit'=>'px' ) );
	velure_core_field_number( array( 'label'=>'Padding vertical (mobile)','name'=>'hero_padding_v_mobile','value'=>$s['hero_padding_v_mobile']??50,'min'=>0,'max'=>150,'unit'=>'px' ) );
	echo '</div>';
	velure_core_field_toggle( array( 'label'=>'Masquer les blocs lateraux sur mobile','name'=>'hero_hide_side_mobile','value'=>$s['hero_hide_side_mobile']??0,'description'=>'Les blocs lateraux ne s\'afficheront pas sur mobile.' ) );
	velure_core_accordion_end();

	echo '</div>';
}

/* ── Tab: Features ── */
function velure_core_tab_features( $s ) {
	echo '<div class="vc-section-panel" data-panel="features">';
	velure_core_section_preview_card( 'Barre de Confiance', 'Trust', '&#128142;', 'Icones, titres et descriptions.' );
	velure_core_render_templates( 'features', $s );
	velure_core_accordion_start( 'acc-feat-settings', 'Parametres', '&#9881;', true );
	echo '<div class="vc-field-row-3">';
	velure_core_field_select( array( 'label'=>'Fond','name'=>'feat_bg_style','value'=>$s['feat_bg_style']??'soft','choices'=>array('base'=>'Base','soft'=>'Doux','muted'=>'Mute','dark'=>'Fonce','secondary'=>'Secondaire') ) );
	velure_core_field_select( array( 'label'=>'Espacement','name'=>'feat_padding','value'=>$s['feat_padding']??'normal','choices'=>array('compact'=>'Compact','normal'=>'Normal','spacious'=>'Espacieux') ) );
	velure_core_field_toggle( array( 'label'=>'Bordure inferieure','name'=>'feat_bottom_border','value'=>$s['feat_bottom_border']??0 ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-feat-items', 'Elements', '&#128142;', true );
	$features = is_array( $s['trust_features'] ?? null ) ? $s['trust_features'] : array();
	velure_core_field_repeater_header( array( 'label'=>'Elements','button_label'=>'Ajouter un element','repeater_id'=>'vc-trust-features-repeater' ) );
	echo '<div class="vc-repeater" id="vc-trust-features-repeater">';
	foreach ( $features as $i => $feat ) {
		if ( ! is_array( $feat ) ) $feat = array();
		velure_core_field_repeater_row_start( $i, 'Element ' . ( $i + 1 ) );
		velure_core_field_textarea( array( 'label'=>'Icone SVG','name'=>'trust_features[' . $i . '][icon_svg]','value'=>$feat['icon_svg']??'','rows'=>3 ) );
		echo '<div class="vc-field-row">';
		velure_core_field_text( array( 'label'=>'Titre','name'=>'trust_features[' . $i . '][title]','value'=>$feat['title']??'' ) );
		velure_core_field_text( array( 'label'=>'Description','name'=>'trust_features[' . $i . '][description]','value'=>$feat['description']??'' ) );
		echo '</div>';
		velure_core_field_repeater_row_end();
	}
	echo '<div class="vc-repeater-template" id="vc-trust-features-repeater-template">';
	velure_core_field_repeater_row_start( '__INDEX__', 'Element __NUM__' );
	velure_core_field_textarea( array( 'label'=>'Icone SVG','name'=>'trust_features[__INDEX__][icon_svg]','value'=>'','rows'=>3 ) );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Titre','name'=>'trust_features[__INDEX__][title]','value'=>'' ) );
	velure_core_field_text( array( 'label'=>'Description','name'=>'trust_features[__INDEX__][description]','value'=>'' ) );
	echo '</div>';
	velure_core_field_repeater_row_end();
	echo '</div></div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Categories ── */
function velure_core_tab_categories( $s ) {
	echo '<div class="vc-section-panel" data-panel="categories">';
	velure_core_section_preview_card( 'Categories', 'Grille', '&#127970;', 'Grille des univers produits.' );
	velure_core_render_templates( 'categories', $s );
	velure_core_accordion_start( 'acc-cat-content', 'Contenu', '&#128221;', true );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'cat_eyebrow','value'=>$s['cat_eyebrow']??'Collections' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'section_title_categories','value'=>$s['section_title_categories']??'Explorer par Univers' ) );
	echo '</div>';
	velure_core_field_textarea( array( 'label'=>'Description','name'=>'cat_description','value'=>$s['cat_description']??'','rows'=>3 ) );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Texte CTA','name'=>'cat_cta_text','value'=>$s['cat_cta_text']??'Toutes les categories' ) );
	velure_core_field_text( array( 'label'=>'Lien CTA','name'=>'cat_cta_link','value'=>$s['cat_cta_link']??'/boutique/' ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-cat-style', 'Style', '&#127912;', false );
	echo '<div class="vc-field-row">';
	velure_core_field_select( array( 'label'=>'Fond','name'=>'cat_bg_style','value'=>$s['cat_bg_style']??'base','choices'=>array('base'=>'Base','soft'=>'Doux','muted'=>'Mute','dark'=>'Fonce','secondary'=>'Secondaire') ) );
	velure_core_field_number( array( 'label'=>'Nb. categories','name'=>'cat_display_count','value'=>$s['cat_display_count']??10,'min'=>1,'max'=>50 ) );
	echo '</div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Products ── */
function velure_core_tab_products( $s ) {
	echo '<div class="vc-section-panel" data-panel="products">';
	velure_core_section_preview_card( 'Produits Vedettes', 'WooCommerce', '&#128722;', 'Selection de produits.' );
	velure_core_render_templates( 'products', $s );
	velure_core_accordion_start( 'acc-prod-content', 'Contenu', '&#128221;', true );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'prod_eyebrow','value'=>$s['prod_eyebrow']??'Selection' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'section_title_products','value'=>$s['section_title_products']??'Pieces Vedettes' ) );
	echo '</div>';
	velure_core_field_textarea( array( 'label'=>'Description','name'=>'prod_description','value'=>$s['prod_description']??'','rows'=>3 ) );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Texte CTA','name'=>'prod_cta_text','value'=>$s['prod_cta_text']??'Voir toute la boutique' ) );
	velure_core_field_text( array( 'label'=>'Lien CTA','name'=>'prod_cta_link','value'=>$s['prod_cta_link']??'/boutique/' ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-prod-display', 'Affichage', '&#9881;', true );
	echo '<div class="vc-field-row-3">';
	velure_core_field_select( array( 'label'=>'Colonnes','name'=>'prod_columns','value'=>$s['prod_columns']??'4','choices'=>array('2'=>'2','3'=>'3','4'=>'4') ) );
	velure_core_field_select( array( 'label'=>'Mode','name'=>'prod_mode','value'=>$s['prod_mode']??'auto','choices'=>array('auto'=>'Auto','manual'=>'Manuel') ) );
	velure_core_field_number( array( 'label'=>'Nb. produits','name'=>'featured_products_count','value'=>$s['featured_products_count']??8,'min'=>1,'max'=>50 ) );
	echo '</div>';
	echo '<div class="vc-field-row">';
	velure_core_field_select( array( 'label'=>'Fond','name'=>'prod_bg_style','value'=>$s['prod_bg_style']??'soft','choices'=>array('base'=>'Base','soft'=>'Doux','muted'=>'Mute','dark'=>'Fonce','secondary'=>'Secondaire') ) );
	velure_core_field_select( array( 'label'=>'Tri','name'=>'prod_sort','value'=>$s['prod_sort']??'date','choices'=>array('date'=>'Date','popularity'=>'Popularite','rating'=>'Note','rand'=>'Aleatoire') ) );
	echo '</div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Banner ── */
function velure_core_tab_banner( $s ) {
	echo '<div class="vc-section-panel" data-panel="banner">';
	velure_core_section_preview_card( 'Banniere Splitee', 'Promo', '&#128248;', 'Double banniere.' );
	velure_core_render_templates( 'banner', $s );
	velure_core_accordion_start( 'acc-banner-layout', 'Layout', '&#9881;', true );
	velure_core_field_select( array( 'label'=>'Layout','name'=>'sb_layout','value'=>$s['sb_layout']??'50-50','choices'=>array('50-50'=>'50/50','60-40'=>'60/40','40-60'=>'40/60') ) );
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-banner-left', 'Cote Gauche', '&#9664;', true );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'sb_left_eyebrow','value'=>$s['sb_left_eyebrow']??'Collection AW25' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'sb_left_title','value'=>$s['sb_left_title']??'La Nouvelle Collection' ) );
	echo '</div>';
	velure_core_field_textarea( array( 'label'=>'Description','name'=>'sb_left_desc','value'=>$s['sb_left_desc']??'','rows'=>3 ) );
	echo '<div class="vc-field-row-3">';
	velure_core_field_text( array( 'label'=>'Texte CTA','name'=>'sb_left_cta_text','value'=>$s['sb_left_cta_text']??'Decouvrir' ) );
	velure_core_field_text( array( 'label'=>'Lien CTA','name'=>'sb_left_cta_link','value'=>$s['sb_left_cta_link']??'/new-collection/' ) );
	velure_core_field_select( array( 'label'=>'Style CTA','name'=>'sb_left_cta_style','value'=>$s['sb_left_cta_style']??'gold','choices'=>array('primary'=>'Principal','secondary'=>'Secondaire','gold'=>'Dore','outline'=>'Contour') ) );
	echo '</div>';
	echo '<div class="vc-field-row">';
	velure_core_field_select( array( 'label'=>'Style visuel','name'=>'sb_left_style','value'=>$s['sb_left_style']??'dark','choices'=>array('light'=>'Clair','dark'=>'Fonce') ) );
	velure_core_field_image( array( 'label'=>'Image','name'=>'sb_left_image','value'=>$s['sb_left_image']??0 ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-banner-right', 'Cote Droit', '&#9654;', false );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'sb_right_eyebrow','value'=>$s['sb_right_eyebrow']??'Edition Limitee' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'sb_right_title','value'=>$s['sb_right_title']??"Accessoires d'Exception" ) );
	echo '</div>';
	velure_core_field_textarea( array( 'label'=>'Description','name'=>'sb_right_desc','value'=>$s['sb_right_desc']??'','rows'=>3 ) );
	echo '<div class="vc-field-row-3">';
	velure_core_field_text( array( 'label'=>'Texte CTA','name'=>'sb_right_cta_text','value'=>$s['sb_right_cta_text']??'Explorer' ) );
	velure_core_field_text( array( 'label'=>'Lien CTA','name'=>'sb_right_cta_link','value'=>$s['sb_right_cta_link']??'/categorie/accessoires/' ) );
	velure_core_field_select( array( 'label'=>'Style CTA','name'=>'sb_right_cta_style','value'=>$s['sb_right_cta_style']??'outline','choices'=>array('primary'=>'Principal','secondary'=>'Secondaire','gold'=>'Dore','outline'=>'Contour') ) );
	echo '</div>';
	echo '<div class="vc-field-row">';
	velure_core_field_select( array( 'label'=>'Style visuel','name'=>'sb_right_style','value'=>$s['sb_right_style']??'light','choices'=>array('light'=>'Clair','dark'=>'Fonce') ) );
	velure_core_field_image( array( 'label'=>'Image','name'=>'sb_right_image','value'=>$s['sb_right_image']??0 ) );
	echo '</div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Marquee ── */
function velure_core_tab_marquee( $s ) {
	echo '<div class="vc-section-panel" data-panel="marquee">';
	velure_core_section_preview_card( 'Bandeau Marquee', 'Defilant', '&#127926;', 'Bandeau de marques.' );
	velure_core_accordion_start( 'acc-marq-settings', 'Parametres', '&#9881;', true );
	echo '<div class="vc-field-row-3">';
	velure_core_field_range( array( 'label'=>'Vitesse','name'=>'marquee_speed','value'=>$s['marquee_speed']??25,'min'=>5,'max'=>120,'unit'=>'s' ) );
	velure_core_field_select( array( 'label'=>'Fond','name'=>'marquee_bg','value'=>$s['marquee_bg']??'base','choices'=>array('base'=>'Base','soft'=>'Doux','muted'=>'Mute','dark'=>'Fonce','secondary'=>'Secondaire') ) );
	velure_core_field_select( array( 'label'=>'Direction','name'=>'marquee_direction','value'=>$s['marquee_direction']??'left','choices'=>array('left'=>'Gauche > Droite','right'=>'Droite > Gauche') ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-marq-brands', 'Marques', '&#127926;', true );
	$brands = is_array( $s['brand_names'] ?? null ) ? $s['brand_names'] : array();
	velure_core_field_repeater_header( array( 'label'=>'Marques','button_label'=>'Ajouter','repeater_id'=>'vc-brand-names-repeater' ) );
	echo '<div class="vc-repeater" id="vc-brand-names-repeater">';
	foreach ( $brands as $i => $brand ) {
		if ( ! is_array( $brand ) ) $brand = array();
		velure_core_field_repeater_row_start( $i, 'Marque ' . ( $i + 1 ) );
		velure_core_field_text( array( 'label'=>'Nom','name'=>'brand_names[' . $i . '][name]','value'=>$brand['name']??'' ) );
		velure_core_field_repeater_row_end();
	}
	echo '<div class="vc-repeater-template" id="vc-brand-names-repeater-template">';
	velure_core_field_repeater_row_start( '__INDEX__', 'Marque __NUM__' );
	velure_core_field_text( array( 'label'=>'Nom','name'=>'brand_names[__INDEX__][name]','value'=>'' ) );
	velure_core_field_repeater_row_end();
	echo '</div></div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Testimonials ── */
function velure_core_tab_testimonials( $s ) {
	echo '<div class="vc-section-panel" data-panel="testimonials">';
	velure_core_section_preview_card( 'Temoignages', 'Avis', '&#128172;', 'Avis clients (CPT).' );
	echo '<div class="vc-field-hint" style="margin-bottom:16px;padding:12px 16px;background:var(--vc-bg-input);border-radius:var(--vc-radius-sm);border-left:3px solid var(--vc-accent)">&#128161; Les temoignages proviennent du CPT "Temoignages".</div>';
	velure_core_accordion_start( 'acc-testi-content', 'Contenu', '&#128221;', true );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'testi_eyebrow','value'=>$s['testi_eyebrow']??'Avis Clients' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'section_title_testimonials','value'=>$s['section_title_testimonials']??'Ce Que Disent Nos Clients' ) );
	velure_core_field_textarea( array( 'label'=>'Description','name'=>'testi_description','value'=>$s['testi_description']??'','rows'=>2 ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-testi-display', 'Affichage', '&#9881;', true );
	echo '<div class="vc-field-row-3">';
	velure_core_field_number( array( 'label'=>'Nb.','name'=>'testimonials_count','value'=>$s['testimonials_count']??3,'min'=>1,'max'=>20 ) );
	velure_core_field_select( array( 'label'=>'Colonnes','name'=>'testi_columns','value'=>$s['testi_columns']??'3','choices'=>array('1'=>'1','2'=>'2','3'=>'3') ) );
	velure_core_field_select( array( 'label'=>'Fond','name'=>'testi_bg_style','value'=>$s['testi_bg_style']??'base','choices'=>array('base'=>'Base','soft'=>'Doux','muted'=>'Mute','dark'=>'Fonce','secondary'=>'Secondaire') ) );
	echo '</div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Blog ── */
function velure_core_tab_blog( $s ) {
	echo '<div class="vc-section-panel" data-panel="blog">';
	velure_core_section_preview_card( 'Blog', 'Journal', '&#128240;', 'Derniers articles.' );
	velure_core_accordion_start( 'acc-blog-content', 'Contenu', '&#128221;', true );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'blog_eyebrow','value'=>$s['blog_eyebrow']??'Actualites' ) );
	velure_core_field_text( array( 'label'=>'Titre','name'=>'section_title_blog','value'=>$s['section_title_blog']??'Le Journal' ) );
	velure_core_field_textarea( array( 'label'=>'Description','name'=>'blog_description','value'=>$s['blog_description']??'','rows'=>2 ) );
	echo '</div>';
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Texte CTA','name'=>'blog_cta_text','value'=>$s['blog_cta_text']??'Voir tous les articles' ) );
	velure_core_field_text( array( 'label'=>'Lien CTA','name'=>'blog_cta_link','value'=>$s['blog_cta_link']??'/blog/' ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-blog-display', 'Affichage', '&#9881;', true );
	echo '<div class="vc-field-row-3">';
	velure_core_field_number( array( 'label'=>'Nb. articles','name'=>'blog_posts_count','value'=>$s['blog_posts_count']??3,'min'=>1,'max'=>20 ) );
	velure_core_field_select( array( 'label'=>'Colonnes','name'=>'blog_columns','value'=>$s['blog_columns']??'3','choices'=>array('2'=>'2','3'=>'3') ) );
	velure_core_field_select( array( 'label'=>'Fond','name'=>'blog_bg_style','value'=>$s['blog_bg_style']??'muted','choices'=>array('base'=>'Base','soft'=>'Doux','muted'=>'Mute','dark'=>'Fonce','secondary'=>'Secondaire') ) );
	echo '</div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Instagram ── */
function velure_core_tab_instagram( $s ) {
	echo '<div class="vc-section-panel" data-panel="instagram">';
	velure_core_section_preview_card( 'Instagram', 'Feed', '&#128247;', 'Galerie photos.' );
	velure_core_accordion_start( 'acc-ig-content', 'Configuration', '&#128221;', true );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Handle','name'=>'instagram_handle','value'=>$s['instagram_handle']??'@velure.paris' ) );
	velure_core_field_text( array( 'label'=>'URL','name'=>'instagram_url','value'=>$s['instagram_url']??'https://instagram.com/' ) );
	velure_core_field_text( array( 'label'=>'Eyebrow','name'=>'ig_eyebrow','value'=>$s['ig_eyebrow']??'Suivez-nous' ) );
	echo '</div>';
	echo '<div class="vc-field-row">';
	velure_core_field_select( array( 'label'=>'Colonnes','name'=>'ig_columns','value'=>$s['ig_columns']??'6','choices'=>array('3'=>'3','4'=>'4','5'=>'5','6'=>'6') ) );
	velure_core_field_select( array( 'label'=>'Espacement','name'=>'ig_gap','value'=>$s['ig_gap']??'small','choices'=>array('none'=>'Aucun','small'=>'Petit','medium'=>'Moyen','large'=>'Grand') ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-ig-images', 'Images', '&#128247;', true );
	$images = is_array( $s['instagram_images'] ?? null ) ? $s['instagram_images'] : array();
	velure_core_field_repeater_header( array( 'label'=>'Images','button_label'=>'Ajouter','repeater_id'=>'vc-instagram-images-repeater' ) );
	echo '<div class="vc-repeater" id="vc-instagram-images-repeater">';
	foreach ( $images as $i => $img ) {
		if ( ! is_array( $img ) ) $img = array();
		velure_core_field_repeater_row_start( $i, 'Image ' . ( $i + 1 ) );
		velure_core_field_image( array( 'label'=>'Image','name'=>'instagram_images[' . $i . '][image]','value'=>$img['image']??0 ) );
		echo '<div class="vc-field-row">';
		velure_core_field_text( array( 'label'=>'Lien','name'=>'instagram_images[' . $i . '][link]','value'=>$img['link']??'' ) );
		velure_core_field_text( array( 'label'=>'Alt','name'=>'instagram_images[' . $i . '][alt]','value'=>$img['alt']??'' ) );
		echo '</div>';
		velure_core_field_repeater_row_end();
	}
	echo '<div class="vc-repeater-template" id="vc-instagram-images-repeater-template">';
	velure_core_field_repeater_row_start( '__INDEX__', 'Image __NUM__' );
	velure_core_field_image( array( 'label'=>'Image','name'=>'instagram_images[__INDEX__][image]','value'=>0 ) );
	echo '<div class="vc-field-row">';
	velure_core_field_text( array( 'label'=>'Lien','name'=>'instagram_images[__INDEX__][link]','value'=>'' ) );
	velure_core_field_text( array( 'label'=>'Alt','name'=>'instagram_images[__INDEX__][alt]','value'=>'' ) );
	echo '</div>';
	velure_core_field_repeater_row_end();
	echo '</div></div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ── Tab: Global ── */
function velure_core_tab_global( $s ) {
	echo '<div class="vc-section-panel" data-panel="global">';
	velure_core_section_preview_card( 'Parametres Globaux', 'General', '&#127760;', 'Topbar, footer, CSS.' );
	velure_core_accordion_start( 'acc-global-topbar', 'Topbar', '&#128240;', true );
	velure_core_field_toggle( array( 'label'=>'Afficher la topbar','name'=>'show_topbar','value'=>$s['show_topbar']??1 ) );
	velure_core_field_text( array( 'label'=>'Texte','name'=>'topbar_text','value'=>$s['topbar_text']??'' ) );
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-global-footer', 'Footer', '&#128230;', false );
	velure_core_field_text( array( 'label'=>'Copyright','name'=>'footer_copyright','value'=>$s['footer_copyright']??'' ) );
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-global-styles', 'Styles', '&#127912;', false );
	echo '<div class="vc-field-row">';
	velure_core_field_select( array( 'label'=>'Espacement sections','name'=>'section_padding','value'=>$s['section_padding']??'normal','choices'=>array('none'=>'Aucun','compact'=>'Compact','normal'=>'Normal','spacious'=>'Espacieux') ) );
	velure_core_field_toggle( array( 'label'=>'Animations scroll','name'=>'scroll_animations','value'=>$s['scroll_animations']??1 ) );
	echo '</div>';
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-global-css', 'CSS Personnalise', '&#128396;', false );
	velure_core_field_textarea( array( 'label'=>'CSS','name'=>'custom_css','value'=>$s['custom_css']??'','rows'=>12 ) );
	velure_core_accordion_end();
	velure_core_accordion_start( 'acc-global-io', 'Import / Export', '&#128190;', false );
	echo '<p class="vc-field-hint" style="margin-bottom:14px">Exportez ou importez vos reglages.</p>';
	echo '<div style="display:flex;gap:10px;flex-wrap:wrap">';
	echo '<button type="button" class="vc-btn vc-btn-ghost" id="vc-export-btn">&#128228; Exporter</button>';
	echo '<button type="button" class="vc-btn vc-btn-ghost" id="vc-import-btn">&#128229; Importer</button>';
	echo '<button type="button" class="vc-btn vc-btn-danger" id="vc-reset-btn">&#9888; Reinitialiser</button>';
	echo '</div>';
	echo '<div id="vc-export-output" style="display:none;margin-top:14px"><textarea id="vc-export-text" rows="6" style="width:100%;background:var(--vc-bg-input);border:1px solid var(--vc-border);color:var(--vc-text-bright);border-radius:var(--vc-radius-sm);font-family:monospace;font-size:11px" readonly></textarea><button type="button" class="vc-btn vc-btn-primary vc-btn-sm" style="margin-top:8px" id="vc-copy-export">&#128203; Copier</button></div>';
	echo '<div id="vc-import-input" style="display:none;margin-top:14px"><textarea id="vc-import-text" rows="6" style="width:100%;background:var(--vc-bg-input);border:1px solid var(--vc-border);color:var(--vc-text-bright);border-radius:var(--vc-radius-sm);font-family:monospace;font-size:11px" placeholder="Collez le JSON ici..."></textarea><button type="button" class="vc-btn vc-btn-primary vc-btn-sm" style="margin-top:8px" id="vc-do-import">&#10003; Appliquer</button></div>';
	velure_core_accordion_end();
	echo '</div>';
}

/* ═══════════════════════════════════════════════════════════════
   5. AJAX SAVE HANDLER
   ═══════════════════════════════════════════════════════════════ */
add_action( 'wp_ajax_velure_core_save', 'velure_core_ajax_save' );
function velure_core_ajax_save() {
	check_ajax_referer( 'velure_core_save_settings' );
	if ( ! current_user_can( 'edit_theme_options' ) ) wp_send_json_error( array( 'message' => 'Permission insuffisante.' ) );

	$raw = wp_unslash( $_POST );
	$saved = get_option( VELURE_CORE_OPTION, array() );
	$default = Velure_Core::instance()->default_settings();

	/* Simple text fields */
	$simple = array(
		'hero_height','hero_text_align','hero_text_color',
		'hs_bestseller_label','hs_bestseller_title','hs_bestseller_price','hs_bestseller_cta','hs_bestseller_link',
		'hs_category_label','hs_category_title','hs_category_cta_link',
		'feat_bg_style','feat_padding',
		'cat_eyebrow','section_title_categories','cat_description','cat_cta_text','cat_cta_link','cat_bg_style',
		'prod_eyebrow','section_title_products','prod_description','prod_cta_text','prod_cta_link','prod_columns','prod_mode','prod_bg_style','prod_sort',
		'sb_layout','sb_left_eyebrow','sb_left_title','sb_left_desc','sb_left_cta_text','sb_left_cta_link','sb_left_cta_style','sb_left_style',
		'sb_right_eyebrow','sb_right_title','sb_right_desc','sb_right_cta_text','sb_right_cta_link','sb_right_cta_style','sb_right_style',
		'marquee_direction','marquee_bg',
		'testi_eyebrow','section_title_testimonials','testi_description','testi_bg_style','testi_columns',
		'blog_eyebrow','section_title_blog','blog_description','blog_cta_text','blog_cta_link','blog_bg_style','blog_columns',
		'instagram_handle','instagram_url','ig_eyebrow','ig_columns','ig_gap',
		'topbar_text','footer_copyright','section_padding','custom_css',
		/* Hero WYSIWYG typography */
		'hero_eyebrow_font_family','hero_eyebrow_color','hero_eyebrow_text_transform',
		'hero_title_font_family','hero_title_color',
		'hero_subtitle_font_family','hero_subtitle_color',
		'hero_cta_text_transform',
		'hero_cta_bg_color','hero_cta_text_color','hero_cta_hover_bg_color','hero_cta_hover_text_color',
		'hero_cta_border_color',
		'hero_bg_position','hero_bg_size',
		'hero_side_width','hero_side_gap',
	);
	foreach ( $simple as $f ) { $saved[$f] = isset($raw[$f]) ? sanitize_text_field($raw[$f]) : ($default[$f] ?? ''); }

	/* Toggle fields */
	$toggles = array(
		'show_hero','show_features','show_categories','show_products','show_split_banner','show_marquee','show_testimonials','show_blog','show_instagram',
		'hero_autoplay','hero_show_side','feat_bottom_border','scroll_animations','show_topbar',
		'hero_hide_side_mobile',
	);
	foreach ( $toggles as $f ) { $saved[$f] = !empty($raw[$f]) ? 1 : 0; }

	/* Numeric fields */
	$nums = array(
		'hero_autoplay_speed'=>array(6000,1000,20000),
		'hero_overlay_opacity'=>array(40,0,100),
		'cat_display_count'=>array(10,1,50),
		'featured_products_count'=>array(8,1,50),
		'marquee_speed'=>array(25,5,120),
		'testimonials_count'=>array(3,1,20),
		'blog_posts_count'=>array(3,1,20),
		/* Hero WYSIWYG */
		'hero_eyebrow_font_size'=>array(12,8,60),
		'hero_eyebrow_font_weight'=>array(500,100,900),
		'hero_eyebrow_letter_spacing'=>array(2,-5,20),
		'hero_eyebrow_margin_bottom'=>array(12,0,80),
		'hero_title_font_size'=>array(56,10,120),
		'hero_title_font_weight'=>array(700,100,900),
		'hero_title_letter_spacing'=>array(-0.5,-5,20),
		'hero_title_line_height'=>array(1.1,0.8,3),
		'hero_title_margin_bottom'=>array(16,0,80),
		'hero_subtitle_font_size'=>array(18,8,60),
		'hero_subtitle_font_weight'=>array(400,100,900),
		'hero_subtitle_line_height'=>array(1.6,0.8,3),
		'hero_subtitle_margin_bottom'=>array(28,0,80),
		'hero_cta_font_size'=>array(13,8,30),
		'hero_cta_font_weight'=>array(600,100,900),
		'hero_cta_letter_spacing'=>array(1.5,-5,20),
		'hero_cta_padding_x'=>array(32,0,100),
		'hero_cta_padding_y'=>array(15,0,60),
		'hero_cta_border_radius'=>array(0,0,50),
		'hero_cta_border_width'=>array(0,0,10),
		'hero_content_max_width'=>array(580,200,1200),
		'hero_padding_v'=>array(80,0,200),
		'hero_side_card_radius'=>array(8,0,30),
		'hero_side_card_img_height'=>array(160,80,400),
		'hero_title_size_mobile'=>array(36,20,72),
		'hero_subtitle_size_mobile'=>array(15,10,30),
		'hero_cta_size_mobile'=>array(12,8,20),
		'hero_eyebrow_size_mobile'=>array(11,8,20),
		'hero_padding_v_mobile'=>array(50,0,150),
	);
	foreach ( $nums as $f => $c ) {
		$v = isset($raw[$f]) ? floatval($raw[$f]) : $c[0];
		$saved[$f] = max($c[1],min($c[2],$v));
	}

	/* Image IDs */
	foreach ( array('hs_bestseller_image','hs_category_image','sb_left_image','sb_right_image') as $f ) {
		$saved[$f] = isset($raw[$f]) ? absint($raw[$f]) : 0;
	}

	/* Repeater arrays */
	$saved['hero_slides'] = array();
	if ( !empty($raw['hero_slides']) && is_array($raw['hero_slides']) ) {
		foreach ($raw['hero_slides'] as $sl) {
			if (!is_array($sl)) continue;
			$saved['hero_slides'][] = array(
				'image'=>absint($sl['image']??0),
				'eyebrow'=>sanitize_text_field($sl['eyebrow']??''),
				'title'=>wp_kses_post($sl['title']??''),
				'subtitle'=>sanitize_text_field($sl['subtitle']??''),
				'cta_text'=>sanitize_text_field($sl['cta_text']??''),
				'cta_link'=>esc_url_raw($sl['cta_link']??'#'),
				'cta_style'=>sanitize_text_field($sl['cta_style']??'primary'),
			);
		}
	}
	$saved['trust_features'] = array();
	if ( !empty($raw['trust_features']) && is_array($raw['trust_features']) ) {
		foreach ($raw['trust_features'] as $ft) {
			if (!is_array($ft)) continue;
			$saved['trust_features'][] = array(
				'icon_svg'=>wp_kses($ft['icon_svg']??'',velure_core_svg_allowed()),
				'title'=>sanitize_text_field($ft['title']??''),
				'description'=>sanitize_text_field($ft['description']??''),
			);
		}
	}
	$saved['brand_names'] = array();
	if ( !empty($raw['brand_names']) && is_array($raw['brand_names']) ) {
		foreach ($raw['brand_names'] as $br) {
			if (!is_array($br)) continue;
			$n=sanitize_text_field($br['name']??'');
			if (''!==$n) $saved['brand_names'][] = array('name'=>$n);
		}
	}
	$saved['instagram_images'] = array();
	if ( !empty($raw['instagram_images']) && is_array($raw['instagram_images']) ) {
		foreach ($raw['instagram_images'] as $im) {
			if (!is_array($im)) continue;
			$saved['instagram_images'][] = array(
				'image'=>absint($im['image']??0),
				'link'=>esc_url_raw($im['link']??''),
				'alt'=>sanitize_text_field($im['alt']??''),
			);
		}
	}

	/* Section order */
	$valid = array('hero','features','categories','products','split_banner','marquee','testimonials','blog','instagram');
	$saved['section_order'] = array();
	if ( !empty($raw['section_order']) && is_array($raw['section_order']) ) {
		foreach ($raw['section_order'] as $sec) {
			$sec=sanitize_text_field($sec);
			if (in_array($sec,$valid,true)) $saved['section_order'][]=$sec;
		}
	}
	foreach ($valid as $sec) { if (!in_array($sec,$saved['section_order'],true)) $saved['section_order'][]=$sec; }

	update_option( VELURE_CORE_OPTION, $saved );
	update_option( 'velure_core_last_saved', current_time('mysql') );
	wp_send_json_success( array( 'message' => 'Reglages publies avec succes.' ) );
}

/* ═══════════════════════════════════════════════════════════════
   6. MAIN ADMIN PAGE
   ═══════════════════════════════════════════════════════════════ */
function velure_core_render_admin_page() {
	$s = velure_core_get_settings();
	$nav_items = array(
		'sections'=>array('Sections','&#128202;','Layout'),
		'hero'=>array('Hero','&#127916;','WYSIWYG'),
		'features'=>array('Confiance','&#128142;','Trust bar'),
		'categories'=>array('Categories','&#127970;','Univers'),
		'products'=>array('Produits','&#128722;','Vedettes'),
		'banner'=>array('Banniere','&#128248;','Split'),
		'marquee'=>array('Marquee','&#127926;','Defilant'),
		'testimonials'=>array('Temoignages','&#128172;','Avis'),
		'blog'=>array('Blog','&#128240;','Journal'),
		'instagram'=>array('Instagram','&#128247;','Feed'),
		'global'=>array('Global','&#127760;','Reglages'),
	);
	$all_templates = array();
	foreach ( array('hero','features','categories','products','banner') as $ts ) {
		$all_templates[$ts] = velure_core_get_templates($ts);
	}
	?>
	<div class="wrap velure-core-admin-wrap">
		<div class="vc-app">
			<aside class="vc-sidebar">
				<div class="vc-sidebar-brand">
					<div class="vc-sidebar-brand-icon">V</div>
					<div class="vc-sidebar-brand-text"><strong>Velure Core</strong><span>v<?php echo esc_html(VELURE_CORE_VERSION); ?></span></div>
				</div>
				<nav class="vc-sidebar-nav">
					<div class="vc-nav-section-label">Page d'accueil</div>
					<?php $pi=0; foreach ($nav_items as $key => $item) : if ($pi===5) echo '<div class="vc-nav-section-label">Contenu</div>'; ?>
					<div class="vc-nav-item<?php echo $pi===0?' active':''; ?>" data-nav="<?php echo esc_attr($key); ?>">
						<span class="vc-nav-item-icon"><?php echo $item[1]; ?></span>
						<span class="vc-nav-item-label"><?php echo esc_html($item[0]); ?></span>
					</div>
					<?php $pi++; endforeach; ?>
				</nav>
				<div class="vc-sidebar-footer">
					<a href="https://velure.paris" target="_blank" rel="noopener noreferrer"><span style="margin-right:6px">&#128196;</span> Documentation</a>
				</div>
			</aside>
			<div class="vc-main">
				<div class="vc-header">
					<div>
						<div class="vc-header-title" id="vc-header-title">Gestionnaire de Sections</div>
						<div class="vc-header-breadcrumb">Velure Accueil <span>/</span> <span id="vc-breadcrumb-current">Sections</span></div>
					</div>
					<div class="vc-header-actions">
						<span class="vc-unsaved-dot" id="vc-unsaved-dot"></span>
						<button type="button" class="vc-btn vc-btn-ghost" onclick="window.open('<?php echo esc_url(home_url('/')); ?>','_blank')">&#128065; Apercu</button>
					</div>
				</div>
				<div class="vc-panel">
					<?php if (isset($_GET['saved'])&&'1'===$_GET['saved']): ?>
					<div class="vc-toast vc-toast-success show" id="vc-saved-toast">&#10003; Reglages sauvegardes.</div>
					<?php endif; ?>
					<form method="post" action="" class="vc-settings-form" id="vc-settings-form">
						<?php wp_nonce_field('velure_core_save_settings'); ?>
						<input type="hidden" name="action" value="velure_core_save" />
						<?php
						velure_core_tab_sections($s);
						velure_core_tab_hero($s);
						velure_core_tab_features($s);
						velure_core_tab_categories($s);
						velure_core_tab_products($s);
						velure_core_tab_banner($s);
						velure_core_tab_marquee($s);
						velure_core_tab_testimonials($s);
						velure_core_tab_blog($s);
						velure_core_tab_instagram($s);
						velure_core_tab_global($s);
						?>
					</form>
				</div>
				<div class="vc-footer">
					<div class="vc-footer-status"><span class="vc-footer-status-dot"></span><span>Derniere sauvegarde : <?php echo esc_html(get_option('velure_core_last_saved','--')); ?></span></div>
					<div class="vc-footer-actions">
						<button type="button" class="vc-btn vc-btn-ghost" id="vc-discard-btn">Annuler</button>
						<button type="button" class="vc-btn vc-btn-publish" id="vc-publish-btn">&#10003; Publier</button>
					</div>
				</div>
			</div>
		</div>
		<div class="vc-toast" id="vc-toast"></div>
		<script type="application/json" id="vc-templates-data"><?php echo wp_json_encode( $all_templates, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ); ?></script>
	</div>
	<?php
}
"""

with open(os.path.join(BASE, 'includes/admin-pages.php'), 'w') as f:
    f.write(admin_pages)
print("  admin-pages.php written")

print("admin-pages.php done!")