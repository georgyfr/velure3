<?php
/**
 * Velure Core — Admin Panel v3.0
 * Elementor-inspired interface with sidebar nav, section previews,
 * pre-built templates, advanced fields, and AJAX save.
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
   2. FIELD HELPERS (redesigned dark theme)
   ═══════════════════════════════════════════════════════════════ */

function velure_core_field_text( $args ) {
        $id          = 'vc-' . sanitize_html_class( $args['name'] );
        $val         = esc_attr( $args['value'] ?? '' );
        $desc        = ! empty( $args['description'] ) ? '<div class="vc-field-hint">' . esc_html( $args['description'] ) . '</div>' : '';
        $placeholder = ! empty( $args['placeholder'] ) ? ' placeholder="' . esc_attr( $args['placeholder'] ) . '"' : '';
        $icon        = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';

        echo '<div class="vc-field">';
        echo '<label for="' . $id . '">' . $icon . esc_html( $args['label'] ) . '</label>';
        echo '<input type="text" id="' . $id . '" name="' . esc_attr( $args['name'] ) . '" value="' . $val . '"' . $placeholder . ' />';
        echo $desc;
        echo '</div>';
}

function velure_core_field_textarea( $args ) {
        $id   = 'vc-' . sanitize_html_class( $args['name'] );
        $val  = esc_textarea( $args['value'] ?? '' );
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
        $id   = 'vc-' . sanitize_html_class( $args['name'] );
        $val  = isset( $args['value'] ) ? $args['value'] : '';
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
        $id   = 'vc-' . sanitize_html_class( $args['name'] );
        $val  = isset( $args['value'] ) ? intval( $args['value'] ) : 0;
        $desc = ! empty( $args['description'] ) ? '<div class="vc-field-hint">' . esc_html( $args['description'] ) . '</div>' : '';
        $min  = isset( $args['min'] ) ? ' min="' . intval( $args['min'] ) . '"' : '';
        $max  = isset( $args['max'] ) ? ' max="' . intval( $args['max'] ) . '"' : '';
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
        $id    = 'vc-' . sanitize_html_class( $args['name'] );
        $val   = isset( $args['value'] ) ? intval( $args['value'] ) : ( $args['default'] ?? 0 );
        $min   = $args['min'] ?? 0;
        $max   = $args['max'] ?? 100;
        $step  = $args['step'] ?? 1;
        $unit  = ! empty( $args['unit'] ) ? $args['unit'] : '';
        $icon  = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';

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
        $id   = 'vc-' . sanitize_html_class( $args['name'] );
        $val  = $args['value'] ?? '#C8A97E';
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
        $name    = esc_attr( $args['name'] );
        $checked = ! empty( $args['value'] ) ? 'checked' : '';
        $icon    = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';

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
        $name    = esc_attr( $args['name'] );
        $val     = intval( $args['value'] ?? 0 );
        $url     = $val ? wp_get_attachment_url( $val ) : '';
        $preview = $url
                ? '<img src="' . esc_url( $url ) . '" class="vc-img-preview" />'
                : '<div class="vc-img-placeholder"><span class="vc-img-placeholder-icon">&#128247;</span>Cliquer ou glisser une image</div>';
        $icon    = ! empty( $args['icon'] ) ? '<span class="vc-label-icon">' . $args['icon'] . '</span>' : '';

        echo '<div class="vc-field vc-image-field">';
        echo '<label>' . $icon . esc_html( $args['label'] ) . '</label>';
        echo '<div class="vc-img-wrap">' . $preview . '</div>';
        echo '<input type="hidden" name="' . $name . '" value="' . $val . '" class="vc-img-id" />';
        echo '<div class="vc-img-actions">';
        echo '<button type="button" class="vc-btn vc-btn-ghost vc-btn-sm vc-img-btn">&#128194; Choisir une image</button>';
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

/* ═══════════════════════════════════════════════════════════════
   3. PRE-BUILT TEMPLATES
   ═══════════════════════════════════════════════════════════════ */

function velure_core_get_templates( $section ) {
        $templates = array(
                'hero' => array(
                        array(
                                'name' => 'Luxe Minimaliste',
                                'desc' => 'Hero plein ecran, texte centre, overlay sombre',
                                'badge' => 'popular',
                                'icon'  => '&#9733;',
                                'data'  => array(
                                        'hero_height' => 'fullscreen',
                                        'hero_text_align' => 'center',
                                        'hero_text_color' => 'light',
                                        'hero_overlay_opacity' => 50,
                                        'hero_autoplay' => 1,
                                        'hero_autoplay_speed' => 7000,
                                        'hero_show_side' => 0,
                                        'hero_slides' => array(
                                                array(
                                                        'image' => 0, 'eyebrow' => 'Collection Automne 2026',
                                                        'title' => "L'Art du Detail",
                                                        'subtitle' => 'Des creations uniques pour un style intemporel.',
                                                        'cta_text' => 'DECOUVRIR', 'cta_link' => '/boutique/', 'cta_style' => 'gold',
                                                ),
                                        ),
                                ),
                        ),
                        array(
                                'name' => 'Boutique Moderne',
                                'desc' => 'Hero standard avec blocs lateraux produits',
                                'badge' => '',
                                'icon'  => '&#128722;',
                                'data'  => array(
                                        'hero_height' => 'standard',
                                        'hero_text_align' => 'left',
                                        'hero_text_color' => 'light',
                                        'hero_overlay_opacity' => 40,
                                        'hero_autoplay' => 1,
                                        'hero_autoplay_speed' => 5000,
                                        'hero_show_side' => 1,
                                        'hero_slides' => array(
                                                array(
                                                        'image' => 0, 'eyebrow' => 'Nouveautes',
                                                        'title' => 'Nouvelle Collection',
                                                        'subtitle' => 'Les pieces incontournables de la saison.',
                                                        'cta_text' => 'VOIR LA COLLECTION', 'cta_link' => '/boutique/', 'cta_style' => 'primary',
                                                ),
                                        ),
                                        'hs_bestseller_label' => 'Best-Seller',
                                        'hs_bestseller_title' => 'Sac Elegance',
                                        'hs_bestseller_price' => '285,00 EUR',
                                        'hs_bestseller_cta' => 'VOIR LE PRODUIT',
                                        'hs_bestseller_link' => '#',
                                        'hs_category_label' => 'Capsule',
                                        'hs_category_title' => 'Les Accessoires Essentiels',
                                        'hs_category_cta_link' => '/categorie/accessoires/',
                                ),
                        ),
                        array(
                                'name' => 'Editorial Bold',
                                'desc' => 'Grand hero avec typographie impactante',
                                'badge' => 'new',
                                'icon'  => '&#9998;',
                                'data'  => array(
                                        'hero_height' => 'large',
                                        'hero_text_align' => 'left',
                                        'hero_text_color' => 'light',
                                        'hero_overlay_opacity' => 60,
                                        'hero_autoplay' => 0,
                                        'hero_show_side' => 0,
                                        'hero_slides' => array(
                                                array(
                                                        'image' => 0, 'eyebrow' => 'EDITION LIMITEE',
                                                        'title' => 'REDEFINIR<br>L\'ELEGANCE',
                                                        'subtitle' => 'Une collection exclusive qui repousse les frontieres du style.',
                                                        'cta_text' => 'RESERVER', 'cta_link' => '/new-collection/', 'cta_style' => 'outline',
                                                ),
                                        ),
                                ),
                        ),
                ),
                'features' => array(
                        array(
                                'name' => 'Confiance Luxe',
                                'desc' => '4 piliers de confiance avec icones',
                                'badge' => 'popular',
                                'icon'  => '&#128142;',
                                'data'  => array(
                                        'feat_bg_style' => 'soft',
                                        'feat_padding' => 'normal',
                                        'feat_bottom_border' => 1,
                                        'trust_features' => array(
                                                array( 'icon_svg' => '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 18H3a2 2 0 01-2-2V8a2 2 0 012-2h3l2-3h6l2 3h3a2 2 0 012 2v8a2 2 0 01-2 2h-2"/><circle cx="12" cy="13" r="3"/></svg>', 'title' => 'Livraison Express', 'description' => 'Sous 24-48h en France metropolitaine' ),
                                                array( 'icon_svg' => '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>', 'title' => 'Qualite Garantie', 'description' => 'Matiere premium et finitions soignees' ),
                                                array( 'icon_svg' => '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16v16H4z"/><path d="M4 10h16M10 4v16"/></svg>', 'title' => 'Retours Gratuits', 'description' => '30 jours pour changer d\'avis' ),
                                                array( 'icon_svg' => '<svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>', 'title' => 'Paiement Securise', 'description' => 'SSL 256-bit et cryptage securise' ),
                                        ),
                                ),
                        ),
                        array(
                                'name' => 'Minimal Clean',
                                'desc' => '3 elements simples, fond sombre',
                                'badge' => '',
                                'icon'  => '&#9711;',
                                'data'  => array(
                                        'feat_bg_style' => 'dark',
                                        'feat_padding' => 'compact',
                                        'feat_bottom_border' => 0,
                                        'trust_features' => array(
                                                array( 'icon_svg' => '', 'title' => 'Livraison Offerte', 'description' => 'Des 150 EUR d\'achat' ),
                                                array( 'icon_svg' => '', 'title' => 'Retours Faciles', 'description' => 'Sous 30 jours' ),
                                                array( 'icon_svg' => '', 'title' => 'Service Client', 'description' => 'Disponible 7j/7' ),
                                        ),
                                ),
                        ),
                ),
                'categories' => array(
                        array(
                                'name' => 'Boutique Complete',
                                'desc' => 'Titre, description et CTA vers la boutique',
                                'badge' => 'popular',
                                'icon'  => '&#127970;',
                                'data'  => array(
                                        'cat_eyebrow' => 'Collections',
                                        'section_title_categories' => 'Explorer par Univers',
                                        'cat_description' => 'Explorez nos univers et trouvez la piece qui vous correspond.',
                                        'cat_cta_text' => 'Toutes les categories',
                                        'cat_cta_link' => '/boutique/',
                                        'cat_bg_style' => 'base',
                                        'cat_display_count' => 10,
                                ),
                        ),
                ),
                'products' => array(
                        array(
                                'name' => 'Selection Vedettes',
                                'desc' => 'Grille 4 colonnes, produits recents',
                                'badge' => 'popular',
                                'icon'  => '&#128722;',
                                'data'  => array(
                                        'prod_eyebrow' => 'Selection', 'section_title_products' => 'Pieces Vedettes',
                                        'prod_description' => 'Nos pieces les plus appreciees, choisies pour vous.',
                                        'prod_cta_text' => 'Voir toute la boutique', 'prod_cta_link' => '/boutique/',
                                        'prod_columns' => '4', 'prod_mode' => 'auto', 'featured_products_count' => 8,
                                        'prod_bg_style' => 'soft', 'prod_sort' => 'date',
                                ),
                        ),
                ),
                'banner' => array(
                        array(
                                'name' => 'Collection Duo',
                                'desc' => 'Banniere 50/50, gauche fonce, droite clair',
                                'badge' => 'popular',
                                'icon'  => '&#128248;',
                                'data'  => array(
                                        'sb_layout' => '50-50',
                                        'sb_left_eyebrow' => 'Collection AW25', 'sb_left_title' => 'La Nouvelle Collection',
                                        'sb_left_desc' => 'Des silhouettes audacieuses et des matieres nobles pour une saison inoubliable.',
                                        'sb_left_cta_text' => 'Decouvrir', 'sb_left_cta_link' => '/new-collection/',
                                        'sb_left_cta_style' => 'gold', 'sb_left_style' => 'dark',
                                        'sb_right_eyebrow' => 'Edition Limitee', 'sb_right_title' => "Accessoires d'Exception",
                                        'sb_right_desc' => 'Sacs, bijoux et ceintures signes par les meilleurs artisans.',
                                        'sb_right_cta_text' => 'Explorer', 'sb_right_cta_link' => '/categorie/accessoires/',
                                        'sb_right_cta_style' => 'outline', 'sb_right_style' => 'light',
                                ),
                        ),
                ),
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
        echo '</div>';
        echo '</div>';
}

/* ═══════════════════════════════════════════════════════════════
   4. SECTION TAB RENDERING FUNCTIONS
   ═══════════════════════════════════════════════════════════════ */

function velure_core_section_preview_card( $title, $badge, $icon, $desc ) {
        echo '<div class="vc-preview-card">';
        echo '<div class="vc-preview-header">';
        echo '<div class="vc-preview-header-left">';
        echo '<h3>' . esc_html( $title ) . '</h3>';
        echo '<span class="vc-preview-badge">' . esc_html( $badge ) . '</span>';
        echo '</div>';
        echo '</div>';
        echo '<div class="vc-preview-body">';
        echo '<div class="vc-preview-visual">';
        echo '<span class="vc-preview-visual-icon">' . $icon . '</span>';
        echo '<span class="vc-preview-visual-label">' . esc_html( $title ) . '</span>';
        echo '<span class="vc-preview-visual-desc">' . esc_html( $desc ) . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
}

/* ── Tab: Sections (Layout Manager) ── */
function velure_core_tab_sections( $s ) {
        echo '<div class="vc-section-panel active" data-panel="sections">';
        velure_core_section_preview_card( 'Gestionnaire de Sections', 'Layout', '&#128202;', 'Activez, desactivez et reordonnez les 9 sections de la page d\'accueil.' );
        velure_core_accordion_start( 'acc-visibility', 'Visibilite des Sections', '&#128065;', true );
        echo '<div class="vc-field-row">';
        $visibility = array(
                'show_hero'         => array( 'Hero (Slider)', '&#127916;' ),
                'show_features'     => array( 'Barre de confiance', '&#128142;' ),
                'show_categories'   => array( 'Categories', '&#127970;' ),
                'show_products'     => array( 'Produits vedettes', '&#128722;' ),
                'show_split_banner' => array( 'Banniere splitee', '&#128248;' ),
                'show_marquee'      => array( 'Marquee (marques)', '&#127926;' ),
                'show_testimonials' => array( 'Temoignages', '&#128172;' ),
                'show_blog'         => array( 'Blog', '&#128240;' ),
                'show_instagram'    => array( 'Instagram', '&#128247;' ),
        );
        foreach ( $visibility as $key => $info ) {
                echo '<div class="vc-field">';
                echo '<label><span class="vc-label-icon">' . $info[1] . '</span> ' . esc_html( $info[0] ) . '</label>';
                echo '<label class="vc-toggle">';
                echo '<input type="checkbox" name="' . $key . '" value="1"' . ( ! empty( $s[ $key ] ) ? ' checked' : '' ) . ' />';
                echo '<span class="vc-toggle-slider"></span>';
                echo '</label>';
                echo '</div>';
        }
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-order', 'Ordre d\'Affichage', '&#128295;', false );
        echo '<p class="vc-field-hint" style="margin-bottom:14px">Glissez-deposez pour reordonner les sections.</p>';
        $section_labels = array(
                'hero'         => array( 'Hero (Slider)', 'Carrousel d\'images en haut de page' ),
                'features'     => array( 'Barre de confiance', 'Livraison, qualite, retours...' ),
                'categories'   => array( 'Categories', 'Grille des univers produits' ),
                'products'     => array( 'Produits vedettes', 'Selection de produits mis en avant' ),
                'split_banner' => array( 'Banniere splitee', 'Double banniere promotionnelle' ),
                'marquee'      => array( 'Marquee', 'Bandeau defilant de marques' ),
                'testimonials' => array( 'Temoignages', 'Avis et notes des clients' ),
                'blog'         => array( 'Blog', 'Derniers articles du journal' ),
                'instagram'    => array( 'Instagram', 'Galerie photo Instagram' ),
        );
        $current_order = $s['section_order'] ?? array_keys( $section_labels );
        echo '<ul class="vc-section-order-list">';
        foreach ( $current_order as $sec ) {
                $info = $section_labels[ $sec ] ?? array( $sec, '' );
                echo '<li>';
                echo '<span class="vc-section-order-handle">&#9776;</span>';
                echo '<span class="vc-section-order-label">' . esc_html( $info[0] ) . '<small>' . esc_html( $info[1] ) . '</small></span>';
                echo '<input type="hidden" name="section_order[]" value="' . esc_attr( $sec ) . '" />';
                echo '</li>';
        }
        echo '</ul>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Hero ── */
function velure_core_tab_hero( $s ) {
        echo '<div class="vc-section-panel" data-panel="hero">';
        velure_core_section_preview_card( 'Section Hero', 'Slider', '&#127916;', 'Carrousel plein ecran avec texte, CTA et blocs lateraux optionnels.' );
        velure_core_render_templates( 'hero', $s );
        velure_core_accordion_start( 'acc-hero-slider', 'Parametres du Slider', '&#9881;', true );
        echo '<div class="vc-field-row">';
        velure_core_field_select( array( 'label' => 'Hauteur du hero', 'name' => 'hero_height', 'value' => $s['hero_height'] ?? 'standard', 'icon' => '&#8597;', 'choices' => array( 'small' => 'Petite (50vh)', 'standard' => 'Standard (70vh)', 'large' => 'Grande (85vh)', 'fullscreen' => 'Plein ecran (100vh)' ) ) );
        velure_core_field_select( array( 'label' => 'Alignement du texte', 'name' => 'hero_text_align', 'value' => $s['hero_text_align'] ?? 'left', 'icon' => '&#8644;', 'choices' => array( 'left' => 'Gauche', 'center' => 'Centre', 'right' => 'Droite' ) ) );
        velure_core_field_select( array( 'label' => 'Couleur du texte', 'name' => 'hero_text_color', 'value' => $s['hero_text_color'] ?? 'light', 'icon' => '&#127912;', 'choices' => array( 'light' => 'Clair (sur fond fonce)', 'dark' => 'Fonce (sur fond clair)' ) ) );
        echo '</div>';
        echo '<div class="vc-field-row">';
        velure_core_field_range( array( 'label' => 'Opacite overlay', 'name' => 'hero_overlay_opacity', 'value' => $s['hero_overlay_opacity'] ?? 40, 'min' => 0, 'max' => 100, 'step' => 5, 'unit' => '%', 'description' => '0 = transparent, 100 = noir total.' ) );
        velure_core_field_range( array( 'label' => 'Vitesse autoplay', 'name' => 'hero_autoplay_speed', 'value' => $s['hero_autoplay_speed'] ?? 6000, 'min' => 2000, 'max' => 20000, 'step' => 500, 'unit' => 'ms' ) );
        echo '</div>';
        velure_core_field_toggle( array( 'label' => 'Lecture automatique', 'name' => 'hero_autoplay', 'value' => $s['hero_autoplay'] ?? 1, 'icon' => '&#9654;' ) );
        velure_core_accordion_end();

        velure_core_accordion_start( 'acc-hero-side', 'Blocs Lateraux', '&#128464;', false );
        velure_core_field_toggle( array( 'label' => 'Afficher les blocs lateraux', 'name' => 'hero_show_side', 'value' => $s['hero_show_side'] ?? 0, 'description' => 'Best-seller + bloc categorie a cote du slider.' ) );
        echo '<div id="vc-hero-side-blocks" style="' . ( ! empty( $s['hero_show_side'] ) ? '' : 'display:none' ) . '">';
        velure_core_accordion_start( 'acc-hero-bs', 'Bloc Best-Seller', '&#11088;', true );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Label', 'name' => 'hs_bestseller_label', 'value' => $s['hs_bestseller_label'] ?? 'Best-Seller' ) );
        velure_core_field_text( array( 'label' => 'Titre produit', 'name' => 'hs_bestseller_title', 'value' => $s['hs_bestseller_title'] ?? 'Sac Elegance' ) );
        velure_core_field_text( array( 'label' => 'Prix', 'name' => 'hs_bestseller_price', 'value' => $s['hs_bestseller_price'] ?? '285,00 EUR' ) );
        velure_core_field_text( array( 'label' => 'Texte CTA', 'name' => 'hs_bestseller_cta', 'value' => $s['hs_bestseller_cta'] ?? 'VOIR LE PRODUIT' ) );
        velure_core_field_text( array( 'label' => 'Lien CTA', 'name' => 'hs_bestseller_link', 'value' => $s['hs_bestseller_link'] ?? '#' ) );
        echo '</div>';
        velure_core_field_image( array( 'label' => 'Image best-seller', 'name' => 'hs_bestseller_image', 'value' => $s['hs_bestseller_image'] ?? 0 ) );
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-hero-cat', 'Bloc Categorie', '&#128193;', false );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Label', 'name' => 'hs_category_label', 'value' => $s['hs_category_label'] ?? 'Capsule' ) );
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'hs_category_title', 'value' => $s['hs_category_title'] ?? 'Les Accessoires Essentiels' ) );
        velure_core_field_text( array( 'label' => 'Lien', 'name' => 'hs_category_cta_link', 'value' => $s['hs_category_cta_link'] ?? '/categorie/accessoires/' ) );
        echo '</div>';
        velure_core_field_image( array( 'label' => 'Image categorie', 'name' => 'hs_category_image', 'value' => $s['hs_category_image'] ?? 0 ) );
        velure_core_accordion_end();
        echo '</div>';
        velure_core_accordion_end();

        /* Slides repeater */
        velure_core_accordion_start( 'acc-hero-slides', 'Slides du Hero', '&#128247;', false );
        $slides = is_array( $s['hero_slides'] ?? null ) ? $s['hero_slides'] : array();
        velure_core_field_repeater_header( array( 'label' => 'Slides', 'button_label' => 'Ajouter un slide', 'repeater_id' => 'vc-hero-slides-repeater' ) );
        echo '<div class="vc-repeater" id="vc-hero-slides-repeater">';
        foreach ( $slides as $i => $slide ) {
                if ( ! is_array( $slide ) ) $slide = array();
                velure_core_field_repeater_row_start( $i, 'Slide ' . ( $i + 1 ) );
                velure_core_field_image( array( 'label' => 'Image du slide', 'name' => 'hero_slides[' . $i . '][image]', 'value' => $slide['image'] ?? 0 ) );
                echo '<div class="vc-field-row">';
                velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'hero_slides[' . $i . '][eyebrow]', 'value' => $slide['eyebrow'] ?? '', 'placeholder' => 'Nouvelle Collection' ) );
                velure_core_field_text( array( 'label' => 'Titre', 'name' => 'hero_slides[' . $i . '][title]', 'value' => $slide['title'] ?? '', 'placeholder' => "L'Elegance Minimaliste" ) );
                echo '</div>';
                velure_core_field_textarea( array( 'label' => 'Sous-titre', 'name' => 'hero_slides[' . $i . '][subtitle]', 'value' => $slide['subtitle'] ?? '', 'rows' => 2, 'placeholder' => 'Nouvelle Collection Automne 2026' ) );
                echo '<div class="vc-field-row">';
                velure_core_field_text( array( 'label' => 'Texte CTA', 'name' => 'hero_slides[' . $i . '][cta_text]', 'value' => $slide['cta_text'] ?? '', 'placeholder' => 'DECOUVRIR' ) );
                velure_core_field_text( array( 'label' => 'Lien CTA', 'name' => 'hero_slides[' . $i . '][cta_link]', 'value' => $slide['cta_link'] ?? '#', 'placeholder' => '/boutique/' ) );
                velure_core_field_select( array( 'label' => 'Style CTA', 'name' => 'hero_slides[' . $i . '][cta_style]', 'value' => $slide['cta_style'] ?? 'primary', 'choices' => array( 'primary' => 'Principal', 'secondary' => 'Secondaire', 'gold' => 'Dore', 'outline' => 'Contour' ) ) );
                echo '</div>';
                velure_core_field_repeater_row_end();
        }
        echo '<div class="vc-repeater-template" id="vc-hero-slides-repeater-template">';
        velure_core_field_repeater_row_start( '__INDEX__', 'Slide __NUM__' );
        velure_core_field_image( array( 'label' => 'Image du slide', 'name' => 'hero_slides[__INDEX__][image]', 'value' => 0 ) );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'hero_slides[__INDEX__][eyebrow]', 'value' => '', 'placeholder' => 'Nouvelle Collection' ) );
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'hero_slides[__INDEX__][title]', 'value' => '', 'placeholder' => "L'Elegance Minimaliste" ) );
        echo '</div>';
        velure_core_field_textarea( array( 'label' => 'Sous-titre', 'name' => 'hero_slides[__INDEX__][subtitle]', 'value' => '', 'rows' => 2, 'placeholder' => 'Nouvelle Collection Automne 2026' ) );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Texte CTA', 'name' => 'hero_slides[__INDEX__][cta_text]', 'value' => '', 'placeholder' => 'DECOUVRIR' ) );
        velure_core_field_text( array( 'label' => 'Lien CTA', 'name' => 'hero_slides[__INDEX__][cta_link]', 'value' => '#', 'placeholder' => '/boutique/' ) );
        velure_core_field_select( array( 'label' => 'Style CTA', 'name' => 'hero_slides[__INDEX__][cta_style]', 'value' => 'primary', 'choices' => array( 'primary' => 'Principal', 'secondary' => 'Secondaire', 'gold' => 'Dore', 'outline' => 'Contour' ) ) );
        echo '</div>';
        velure_core_field_repeater_row_end();
        echo '</div><!-- .vc-repeater-template -->';
        echo '</div><!-- .vc-repeater -->';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Features (Confiance) ── */
function velure_core_tab_features( $s ) {
        echo '<div class="vc-section-panel" data-panel="features">';
        velure_core_section_preview_card( 'Barre de Confiance', 'Trust', '&#128142;', 'Icones, titres et descriptions pour rassurer vos visiteurs.' );
        velure_core_render_templates( 'features', $s );
        velure_core_accordion_start( 'acc-feat-settings', 'Parametres de la Section', '&#9881;', true );
        echo '<div class="vc-field-row-3">';
        velure_core_field_select( array( 'label' => 'Style de fond', 'name' => 'feat_bg_style', 'value' => $s['feat_bg_style'] ?? 'soft', 'choices' => array( 'base' => 'Base', 'soft' => 'Doux', 'muted' => 'Mute', 'dark' => 'Fonce', 'secondary' => 'Secondaire' ) ) );
        velure_core_field_select( array( 'label' => 'Espacement', 'name' => 'feat_padding', 'value' => $s['feat_padding'] ?? 'normal', 'choices' => array( 'compact' => 'Compact', 'normal' => 'Normal', 'spacious' => 'Espacieux' ) ) );
        velure_core_field_toggle( array( 'label' => 'Bordure inferieure', 'name' => 'feat_bottom_border', 'value' => $s['feat_bottom_border'] ?? 0 ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-feat-items', 'Elements de Confiance', '&#128142;', true );
        $features = is_array( $s['trust_features'] ?? null ) ? $s['trust_features'] : array();
        velure_core_field_repeater_header( array( 'label' => 'Elements', 'button_label' => 'Ajouter un element', 'repeater_id' => 'vc-trust-features-repeater' ) );
        echo '<div class="vc-repeater" id="vc-trust-features-repeater">';
        foreach ( $features as $i => $feat ) {
                if ( ! is_array( $feat ) ) $feat = array();
                velure_core_field_repeater_row_start( $i, 'Element ' . ( $i + 1 ) );
                velure_core_field_textarea( array( 'label' => 'Icone SVG', 'name' => 'trust_features[' . $i . '][icon_svg]', 'value' => $feat['icon_svg'] ?? '', 'rows' => 3, 'description' => 'Collez le code SVG de l\'icone.' ) );
                echo '<div class="vc-field-row">';
                velure_core_field_text( array( 'label' => 'Titre', 'name' => 'trust_features[' . $i . '][title]', 'value' => $feat['title'] ?? '', 'placeholder' => 'Livraison Express' ) );
                velure_core_field_text( array( 'label' => 'Description', 'name' => 'trust_features[' . $i . '][description]', 'value' => $feat['description'] ?? '', 'placeholder' => 'Sous 24-48h en France' ) );
                echo '</div>';
                velure_core_field_repeater_row_end();
        }
        echo '<div class="vc-repeater-template" id="vc-trust-features-repeater-template">';
        velure_core_field_repeater_row_start( '__INDEX__', 'Element __NUM__' );
        velure_core_field_textarea( array( 'label' => 'Icone SVG', 'name' => 'trust_features[__INDEX__][icon_svg]', 'value' => '', 'rows' => 3, 'description' => 'Collez le code SVG.' ) );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'trust_features[__INDEX__][title]', 'value' => '', 'placeholder' => 'Livraison Express' ) );
        velure_core_field_text( array( 'label' => 'Description', 'name' => 'trust_features[__INDEX__][description]', 'value' => '', 'placeholder' => 'Sous 24-48h en France' ) );
        echo '</div>';
        velure_core_field_repeater_row_end();
        echo '</div>';
        echo '</div>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Categories ── */
function velure_core_tab_categories( $s ) {
        echo '<div class="vc-section-panel" data-panel="categories">';
        velure_core_section_preview_card( 'Section Categories', 'Grille', '&#127970;', 'Grille des univers produits avec titre et CTA.' );
        velure_core_render_templates( 'categories', $s );
        velure_core_accordion_start( 'acc-cat-content', 'Contenu de la Section', '&#128221;', true );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'cat_eyebrow', 'value' => $s['cat_eyebrow'] ?? 'Collections', 'placeholder' => 'Collections' ) );
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'section_title_categories', 'value' => $s['section_title_categories'] ?? 'Explorer par Univers', 'placeholder' => 'Explorer par Univers' ) );
        echo '</div>';
        velure_core_field_textarea( array( 'label' => 'Description', 'name' => 'cat_description', 'value' => $s['cat_description'] ?? '', 'rows' => 3, 'placeholder' => 'Explorez nos univers et trouvez la piece qui vous correspond.' ) );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Texte CTA', 'name' => 'cat_cta_text', 'value' => $s['cat_cta_text'] ?? 'Toutes les categories', 'placeholder' => 'Toutes les categories' ) );
        velure_core_field_text( array( 'label' => 'Lien CTA', 'name' => 'cat_cta_link', 'value' => $s['cat_cta_link'] ?? '/boutique/', 'placeholder' => '/boutique/' ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-cat-style', 'Style et Affichage', '&#127912;', false );
        echo '<div class="vc-field-row">';
        velure_core_field_select( array( 'label' => 'Style de fond', 'name' => 'cat_bg_style', 'value' => $s['cat_bg_style'] ?? 'base', 'choices' => array( 'base' => 'Base', 'soft' => 'Doux', 'muted' => 'Mute', 'dark' => 'Fonce', 'secondary' => 'Secondaire' ) ) );
        velure_core_field_number( array( 'label' => 'Nb. categories', 'name' => 'cat_display_count', 'value' => $s['cat_display_count'] ?? 10, 'min' => 1, 'max' => 50 ) );
        echo '</div>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Products ── */
function velure_core_tab_products( $s ) {
        echo '<div class="vc-section-panel" data-panel="products">';
        velure_core_section_preview_card( 'Produits Vedettes', 'WooCommerce', '&#128722;', 'Selection automatique ou manuelle de produits mis en avant.' );
        velure_core_render_templates( 'products', $s );
        velure_core_accordion_start( 'acc-prod-content', 'Contenu de la Section', '&#128221;', true );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'prod_eyebrow', 'value' => $s['prod_eyebrow'] ?? 'Selection' ) );
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'section_title_products', 'value' => $s['section_title_products'] ?? 'Pieces Vedettes' ) );
        echo '</div>';
        velure_core_field_textarea( array( 'label' => 'Description', 'name' => 'prod_description', 'value' => $s['prod_description'] ?? '', 'rows' => 3, 'placeholder' => 'Nos pieces les plus appreciees.' ) );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Texte CTA', 'name' => 'prod_cta_text', 'value' => $s['prod_cta_text'] ?? 'Voir toute la boutique' ) );
        velure_core_field_text( array( 'label' => 'Lien CTA', 'name' => 'prod_cta_link', 'value' => $s['prod_cta_link'] ?? '/boutique/' ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-prod-display', 'Affichage et Tri', '&#9881;', true );
        echo '<div class="vc-field-row-3">';
        velure_core_field_select( array( 'label' => 'Colonnes', 'name' => 'prod_columns', 'value' => $s['prod_columns'] ?? '4', 'choices' => array( '2' => '2 colonnes', '3' => '3 colonnes', '4' => '4 colonnes' ) ) );
        velure_core_field_select( array( 'label' => 'Mode', 'name' => 'prod_mode', 'value' => $s['prod_mode'] ?? 'auto', 'choices' => array( 'auto' => 'Automatique', 'manual' => 'Manuel' ), 'description' => 'Auto = WooCommerce recents.' ) );
        velure_core_field_number( array( 'label' => 'Nb. produits', 'name' => 'featured_products_count', 'value' => $s['featured_products_count'] ?? 8, 'min' => 1, 'max' => 50 ) );
        echo '</div>';
        echo '<div class="vc-field-row">';
        velure_core_field_select( array( 'label' => 'Style de fond', 'name' => 'prod_bg_style', 'value' => $s['prod_bg_style'] ?? 'soft', 'choices' => array( 'base' => 'Base', 'soft' => 'Doux', 'muted' => 'Mute', 'dark' => 'Fonce', 'secondary' => 'Secondaire' ) ) );
        velure_core_field_select( array( 'label' => 'Tri', 'name' => 'prod_sort', 'value' => $s['prod_sort'] ?? 'date', 'choices' => array( 'date' => 'Date', 'popularity' => 'Popularite', 'rating' => 'Note', 'rand' => 'Aleatoire' ), 'description' => 'Mode automatique uniquement.' ) );
        echo '</div>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Banner ── */
function velure_core_tab_banner( $s ) {
        echo '<div class="vc-section-panel" data-panel="banner">';
        velure_core_section_preview_card( 'Banniere Splitee', 'Promo', '&#128248;', 'Double banniere avec image, texte et CTA de chaque cote.' );
        velure_core_render_templates( 'banner', $s );
        velure_core_accordion_start( 'acc-banner-layout', 'Disposition Generale', '&#9881;', true );
        velure_core_field_select( array( 'label' => 'Layout', 'name' => 'sb_layout', 'value' => $s['sb_layout'] ?? '50-50', 'choices' => array( '50-50' => '50 / 50', '60-40' => '60 / 40', '40-60' => '40 / 60' ) ) );
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-banner-left', 'Cote Gauche', '&#9664;', true );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'sb_left_eyebrow', 'value' => $s['sb_left_eyebrow'] ?? 'Collection AW25' ) );
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'sb_left_title', 'value' => $s['sb_left_title'] ?? 'La Nouvelle Collection' ) );
        echo '</div>';
        velure_core_field_textarea( array( 'label' => 'Description', 'name' => 'sb_left_desc', 'value' => $s['sb_left_desc'] ?? '', 'rows' => 3 ) );
        echo '<div class="vc-field-row-3">';
        velure_core_field_text( array( 'label' => 'Texte CTA', 'name' => 'sb_left_cta_text', 'value' => $s['sb_left_cta_text'] ?? 'Decouvrir' ) );
        velure_core_field_text( array( 'label' => 'Lien CTA', 'name' => 'sb_left_cta_link', 'value' => $s['sb_left_cta_link'] ?? '/new-collection/' ) );
        velure_core_field_select( array( 'label' => 'Style CTA', 'name' => 'sb_left_cta_style', 'value' => $s['sb_left_cta_style'] ?? 'gold', 'choices' => array( 'primary' => 'Principal', 'secondary' => 'Secondaire', 'gold' => 'Dore', 'outline' => 'Contour' ) ) );
        echo '</div>';
        echo '<div class="vc-field-row">';
        velure_core_field_select( array( 'label' => 'Style visuel', 'name' => 'sb_left_style', 'value' => $s['sb_left_style'] ?? 'dark', 'choices' => array( 'light' => 'Clair', 'dark' => 'Fonce' ) ) );
        velure_core_field_image( array( 'label' => 'Image', 'name' => 'sb_left_image', 'value' => $s['sb_left_image'] ?? 0 ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-banner-right', 'Cote Droit', '&#9654;', false );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'sb_right_eyebrow', 'value' => $s['sb_right_eyebrow'] ?? 'Edition Limitee' ) );
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'sb_right_title', 'value' => $s['sb_right_title'] ?? "Accessoires d'Exception" ) );
        echo '</div>';
        velure_core_field_textarea( array( 'label' => 'Description', 'name' => 'sb_right_desc', 'value' => $s['sb_right_desc'] ?? '', 'rows' => 3 ) );
        echo '<div class="vc-field-row-3">';
        velure_core_field_text( array( 'label' => 'Texte CTA', 'name' => 'sb_right_cta_text', 'value' => $s['sb_right_cta_text'] ?? 'Explorer' ) );
        velure_core_field_text( array( 'label' => 'Lien CTA', 'name' => 'sb_right_cta_link', 'value' => $s['sb_right_cta_link'] ?? '/categorie/accessoires/' ) );
        velure_core_field_select( array( 'label' => 'Style CTA', 'name' => 'sb_right_cta_style', 'value' => $s['sb_right_cta_style'] ?? 'outline', 'choices' => array( 'primary' => 'Principal', 'secondary' => 'Secondaire', 'gold' => 'Dore', 'outline' => 'Contour' ) ) );
        echo '</div>';
        echo '<div class="vc-field-row">';
        velure_core_field_select( array( 'label' => 'Style visuel', 'name' => 'sb_right_style', 'value' => $s['sb_right_style'] ?? 'light', 'choices' => array( 'light' => 'Clair', 'dark' => 'Fonce' ) ) );
        velure_core_field_image( array( 'label' => 'Image', 'name' => 'sb_right_image', 'value' => $s['sb_right_image'] ?? 0 ) );
        echo '</div>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Marquee ── */
function velure_core_tab_marquee( $s ) {
        echo '<div class="vc-section-panel" data-panel="marquee">';
        velure_core_section_preview_card( 'Bandeau Marquee', 'Defilant', '&#127926;', 'Bandeau defilant affichant les noms des marques.' );
        velure_core_accordion_start( 'acc-marq-settings', 'Parametres du Marquee', '&#9881;', true );
        echo '<div class="vc-field-row-3">';
        velure_core_field_range( array( 'label' => 'Vitesse', 'name' => 'marquee_speed', 'value' => $s['marquee_speed'] ?? 25, 'min' => 5, 'max' => 120, 'unit' => 's', 'description' => 'Duree pour un cycle complet.' ) );
        velure_core_field_select( array( 'label' => 'Fond', 'name' => 'marquee_bg', 'value' => $s['marquee_bg'] ?? 'base', 'choices' => array( 'base' => 'Base', 'soft' => 'Doux', 'muted' => 'Mute', 'dark' => 'Fonce', 'secondary' => 'Secondaire' ) ) );
        velure_core_field_select( array( 'label' => 'Direction', 'name' => 'marquee_direction', 'value' => $s['marquee_direction'] ?? 'left', 'choices' => array( 'left' => 'Gauche > Droite', 'right' => 'Droite > Gauche' ) ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-marq-brands', 'Marques', '&#127926;', true );
        $brands = is_array( $s['brand_names'] ?? null ) ? $s['brand_names'] : array();
        velure_core_field_repeater_header( array( 'label' => 'Marques', 'button_label' => 'Ajouter une marque', 'repeater_id' => 'vc-brand-names-repeater' ) );
        echo '<div class="vc-repeater" id="vc-brand-names-repeater">';
        foreach ( $brands as $i => $brand ) {
                if ( ! is_array( $brand ) ) $brand = array();
                velure_core_field_repeater_row_start( $i, 'Marque ' . ( $i + 1 ) );
                velure_core_field_text( array( 'label' => 'Nom de la marque', 'name' => 'brand_names[' . $i . '][name]', 'value' => $brand['name'] ?? '', 'placeholder' => 'NOM DE LA MARQUE' ) );
                velure_core_field_repeater_row_end();
        }
        echo '<div class="vc-repeater-template" id="vc-brand-names-repeater-template">';
        velure_core_field_repeater_row_start( '__INDEX__', 'Marque __NUM__' );
        velure_core_field_text( array( 'label' => 'Nom de la marque', 'name' => 'brand_names[__INDEX__][name]', 'value' => '', 'placeholder' => 'NOM DE LA MARQUE' ) );
        velure_core_field_repeater_row_end();
        echo '</div>';
        echo '</div>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Testimonials ── */
function velure_core_tab_testimonials( $s ) {
        echo '<div class="vc-section-panel" data-panel="testimonials">';
        velure_core_section_preview_card( 'Temoignages', 'Avis', '&#128172;', 'Avis clients recuperes depuis le CPT Temoignages.' );
        echo '<div class="vc-field-hint" style="margin-bottom:16px;padding:12px 16px;background:var(--vc-bg-input);border-radius:var(--vc-radius-sm);border-left:3px solid var(--vc-accent)">&#128161; Les temoignages sont recuperes depuis le CPT <strong>"Temoignages"</strong>. Ajoutez des temoignages depuis le menu admin correspondant.</div>';
        velure_core_accordion_start( 'acc-testi-content', 'Contenu', '&#128221;', true );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'testi_eyebrow', 'value' => $s['testi_eyebrow'] ?? 'Avis Clients' ) );
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'section_title_testimonials', 'value' => $s['section_title_testimonials'] ?? 'Ce Que Disent Nos Clients' ) );
        velure_core_field_textarea( array( 'label' => 'Description', 'name' => 'testi_description', 'value' => $s['testi_description'] ?? '', 'rows' => 2 ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-testi-display', 'Affichage', '&#9881;', true );
        echo '<div class="vc-field-row-3">';
        velure_core_field_number( array( 'label' => 'Nb. temoignages', 'name' => 'testimonials_count', 'value' => $s['testimonials_count'] ?? 3, 'min' => 1, 'max' => 20 ) );
        velure_core_field_select( array( 'label' => 'Colonnes', 'name' => 'testi_columns', 'value' => $s['testi_columns'] ?? '3', 'choices' => array( '1' => '1 colonne', '2' => '2 colonnes', '3' => '3 colonnes' ) ) );
        velure_core_field_select( array( 'label' => 'Style de fond', 'name' => 'testi_bg_style', 'value' => $s['testi_bg_style'] ?? 'base', 'choices' => array( 'base' => 'Base', 'soft' => 'Doux', 'muted' => 'Mute', 'dark' => 'Fonce', 'secondary' => 'Secondaire' ) ) );
        echo '</div>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Blog ── */
function velure_core_tab_blog( $s ) {
        echo '<div class="vc-section-panel" data-panel="blog">';
        velure_core_section_preview_card( 'Section Blog', 'Journal', '&#128240;', 'Derniers articles du blog avec grille responsive.' );
        velure_core_accordion_start( 'acc-blog-content', 'Contenu', '&#128221;', true );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'blog_eyebrow', 'value' => $s['blog_eyebrow'] ?? 'Actualites' ) );
        velure_core_field_text( array( 'label' => 'Titre', 'name' => 'section_title_blog', 'value' => $s['section_title_blog'] ?? 'Le Journal' ) );
        velure_core_field_textarea( array( 'label' => 'Description', 'name' => 'blog_description', 'value' => $s['blog_description'] ?? '', 'rows' => 2 ) );
        echo '</div>';
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Texte CTA', 'name' => 'blog_cta_text', 'value' => $s['blog_cta_text'] ?? 'Voir tous les articles' ) );
        velure_core_field_text( array( 'label' => 'Lien CTA', 'name' => 'blog_cta_link', 'value' => $s['blog_cta_link'] ?? '/blog/' ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-blog-display', 'Affichage', '&#9881;', true );
        echo '<div class="vc-field-row-3">';
        velure_core_field_number( array( 'label' => 'Nb. articles', 'name' => 'blog_posts_count', 'value' => $s['blog_posts_count'] ?? 3, 'min' => 1, 'max' => 20 ) );
        velure_core_field_select( array( 'label' => 'Colonnes', 'name' => 'blog_columns', 'value' => $s['blog_columns'] ?? '3', 'choices' => array( '2' => '2 colonnes', '3' => '3 colonnes' ) ) );
        velure_core_field_select( array( 'label' => 'Style de fond', 'name' => 'blog_bg_style', 'value' => $s['blog_bg_style'] ?? 'muted', 'choices' => array( 'base' => 'Base', 'soft' => 'Doux', 'muted' => 'Mute', 'dark' => 'Fonce', 'secondary' => 'Secondaire' ) ) );
        echo '</div>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Instagram ── */
function velure_core_tab_instagram( $s ) {
        echo '<div class="vc-section-panel" data-panel="instagram">';
        velure_core_section_preview_card( 'Section Instagram', 'Feed', '&#128247;', 'Galerie d\'images avec lien vers Instagram.' );
        velure_core_accordion_start( 'acc-ig-content', 'Configuration Instagram', '&#128221;', true );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Handle', 'name' => 'instagram_handle', 'value' => $s['instagram_handle'] ?? '@velure.paris' ) );
        velure_core_field_text( array( 'label' => 'URL', 'name' => 'instagram_url', 'value' => $s['instagram_url'] ?? 'https://instagram.com/' ) );
        velure_core_field_text( array( 'label' => 'Eyebrow', 'name' => 'ig_eyebrow', 'value' => $s['ig_eyebrow'] ?? 'Suivez-nous' ) );
        echo '</div>';
        echo '<div class="vc-field-row">';
        velure_core_field_select( array( 'label' => 'Colonnes', 'name' => 'ig_columns', 'value' => $s['ig_columns'] ?? '6', 'choices' => array( '3' => '3', '4' => '4', '5' => '5', '6' => '6' ) ) );
        velure_core_field_select( array( 'label' => 'Espacement', 'name' => 'ig_gap', 'value' => $s['ig_gap'] ?? 'small', 'choices' => array( 'none' => 'Aucun', 'small' => 'Petit', 'medium' => 'Moyen', 'large' => 'Grand' ) ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-ig-images', 'Images Instagram', '&#128247;', true );
        $images = is_array( $s['instagram_images'] ?? null ) ? $s['instagram_images'] : array();
        velure_core_field_repeater_header( array( 'label' => 'Images', 'button_label' => 'Ajouter une image', 'repeater_id' => 'vc-instagram-images-repeater' ) );
        echo '<div class="vc-repeater" id="vc-instagram-images-repeater">';
        foreach ( $images as $i => $img ) {
                if ( ! is_array( $img ) ) $img = array();
                velure_core_field_repeater_row_start( $i, 'Image ' . ( $i + 1 ) );
                velure_core_field_image( array( 'label' => 'Image', 'name' => 'instagram_images[' . $i . '][image]', 'value' => $img['image'] ?? 0 ) );
                echo '<div class="vc-field-row">';
                velure_core_field_text( array( 'label' => 'Lien', 'name' => 'instagram_images[' . $i . '][link]', 'value' => $img['link'] ?? '', 'placeholder' => 'https://instagram.com/p/...' ) );
                velure_core_field_text( array( 'label' => 'Texte alt', 'name' => 'instagram_images[' . $i . '][alt]', 'value' => $img['alt'] ?? '', 'placeholder' => 'Description de l\'image' ) );
                echo '</div>';
                velure_core_field_repeater_row_end();
        }
        echo '<div class="vc-repeater-template" id="vc-instagram-images-repeater-template">';
        velure_core_field_repeater_row_start( '__INDEX__', 'Image __NUM__' );
        velure_core_field_image( array( 'label' => 'Image', 'name' => 'instagram_images[__INDEX__][image]', 'value' => 0 ) );
        echo '<div class="vc-field-row">';
        velure_core_field_text( array( 'label' => 'Lien', 'name' => 'instagram_images[__INDEX__][link]', 'value' => '', 'placeholder' => 'https://instagram.com/p/...' ) );
        velure_core_field_text( array( 'label' => 'Texte alt', 'name' => 'instagram_images[__INDEX__][alt]', 'value' => '', 'placeholder' => 'Description' ) );
        echo '</div>';
        velure_core_field_repeater_row_end();
        echo '</div>';
        echo '</div>';
        velure_core_accordion_end();
        echo '</div>';
}

/* ── Tab: Global ── */
function velure_core_tab_global( $s ) {
        echo '<div class="vc-section-panel" data-panel="global">';
        velure_core_section_preview_card( 'Parametres Globaux', 'General', '&#127760;', 'Topbar, footer, espacement, animations et CSS personnalise.' );
        velure_core_accordion_start( 'acc-global-topbar', 'Barre Superieure (Topbar)', '&#128240;', true );
        velure_core_field_toggle( array( 'label' => 'Afficher la topbar', 'name' => 'show_topbar', 'value' => $s['show_topbar'] ?? 1 ) );
        velure_core_field_text( array( 'label' => 'Texte de la topbar', 'name' => 'topbar_text', 'value' => $s['topbar_text'] ?? '', 'placeholder' => 'Livraison offerte des 150 EUR d\'achat', 'description' => 'Utilisez &bull; pour les separateurs.' ) );
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-global-footer', 'Pied de Page (Footer)', '&#128230;', false );
        velure_core_field_text( array( 'label' => 'Texte de copyright', 'name' => 'footer_copyright', 'value' => $s['footer_copyright'] ?? '', 'placeholder' => '&copy; 2026 Velure. Tous droits reserves.' ) );
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-global-styles', 'Styles Globaux', '&#127912;', false );
        echo '<div class="vc-field-row">';
        velure_core_field_select( array( 'label' => 'Espacement sections', 'name' => 'section_padding', 'value' => $s['section_padding'] ?? 'normal', 'choices' => array( 'none' => 'Aucun', 'compact' => 'Compact', 'normal' => 'Normal', 'spacious' => 'Espacieux' ), 'description' => 'Espacement vertical de chaque section.' ) );
        velure_core_field_toggle( array( 'label' => 'Animations au defilement', 'name' => 'scroll_animations', 'value' => $s['scroll_animations'] ?? 1, 'description' => 'Animations d\'entree au scroll.' ) );
        echo '</div>';
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-global-css', 'CSS Personnalise', '&#128396;', false );
        velure_core_field_textarea( array( 'label' => 'CSS personnalise', 'name' => 'custom_css', 'value' => $s['custom_css'] ?? '', 'rows' => 12, 'description' => 'Injecte dans le <head> de la page d\'accueil. Pas de balises <style>.' ) );
        velure_core_accordion_end();
        velure_core_accordion_start( 'acc-global-io', 'Import / Export', '&#128190;', false );
        echo '<p class="vc-field-hint" style="margin-bottom:14px">Exportez vos reglages ou importez une configuration.</p>';
        echo '<div style="display:flex;gap:10px;flex-wrap:wrap">';
        echo '<button type="button" class="vc-btn vc-btn-ghost" id="vc-export-btn">&#128228; Exporter</button>';
        echo '<button type="button" class="vc-btn vc-btn-ghost" id="vc-import-btn">&#128229; Importer</button>';
        echo '<button type="button" class="vc-btn vc-btn-danger" id="vc-reset-btn">&#9888; Reinitialiser</button>';
        echo '</div>';
        echo '<div id="vc-export-output" style="display:none;margin-top:14px">';
        echo '<textarea id="vc-export-text" rows="6" style="width:100%;background:var(--vc-bg-input);border:1px solid var(--vc-border);color:var(--vc-text-bright);border-radius:var(--vc-radius-sm);font-family:monospace;font-size:11px" readonly></textarea>';
        echo '<button type="button" class="vc-btn vc-btn-primary vc-btn-sm" style="margin-top:8px" id="vc-copy-export">&#128203; Copier</button>';
        echo '</div>';
        echo '<div id="vc-import-input" style="display:none;margin-top:14px">';
        echo '<textarea id="vc-import-text" rows="6" style="width:100%;background:var(--vc-bg-input);border:1px solid var(--vc-border);color:var(--vc-text-bright);border-radius:var(--vc-radius-sm);font-family:monospace;font-size:11px" placeholder="Collez ici le JSON exporte..."></textarea>';
        echo '<button type="button" class="vc-btn vc-btn-primary vc-btn-sm" style="margin-top:8px" id="vc-do-import">&#10003; Appliquer</button>';
        echo '</div>';
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

        $simple = array('hero_height','hero_text_align','hero_text_color','hs_bestseller_label','hs_bestseller_title','hs_bestseller_price','hs_bestseller_cta','hs_bestseller_link','hs_category_label','hs_category_title','hs_category_cta_link','feat_bg_style','feat_padding','cat_eyebrow','section_title_categories','cat_description','cat_cta_text','cat_cta_link','cat_bg_style','prod_eyebrow','section_title_products','prod_description','prod_cta_text','prod_cta_link','prod_columns','prod_mode','prod_bg_style','prod_sort','sb_layout','sb_left_eyebrow','sb_left_title','sb_left_desc','sb_left_cta_text','sb_left_cta_link','sb_left_cta_style','sb_left_style','sb_right_eyebrow','sb_right_title','sb_right_desc','sb_right_cta_text','sb_right_cta_link','sb_right_cta_style','sb_right_style','marquee_direction','marquee_bg','testi_eyebrow','section_title_testimonials','testi_description','testi_bg_style','testi_columns','blog_eyebrow','section_title_blog','blog_description','blog_cta_text','blog_cta_link','blog_bg_style','blog_columns','instagram_handle','instagram_url','ig_eyebrow','ig_columns','ig_gap','topbar_text','footer_copyright','section_padding','custom_css');
        foreach ( $simple as $f ) { $saved[$f] = isset($raw[$f]) ? sanitize_text_field($raw[$f]) : ($default[$f] ?? ''); }
        $toggles = array('show_hero','show_features','show_categories','show_products','show_split_banner','show_marquee','show_testimonials','show_blog','show_instagram','hero_autoplay','hero_show_side','feat_bottom_border','scroll_animations','show_topbar');
        foreach ( $toggles as $f ) { $saved[$f] = !empty($raw[$f]) ? 1 : 0; }
        $nums = array('hero_autoplay_speed'=>array(6000,1000,20000),'hero_overlay_opacity'=>array(40,0,100),'cat_display_count'=>array(10,1,50),'featured_products_count'=>array(8,1,50),'marquee_speed'=>array(25,5,120),'testimonials_count'=>array(3,1,20),'blog_posts_count'=>array(3,1,20));
        foreach ( $nums as $f => $c ) { $v = isset($raw[$f]) ? intval($raw[$f]) : $c[0]; $saved[$f] = max($c[1],min($c[2],$v)); }
        foreach ( array('hs_bestseller_image','hs_category_image','sb_left_image','sb_right_image') as $f ) { $saved[$f] = isset($raw[$f]) ? absint($raw[$f]) : 0; }

        $saved['hero_slides'] = array();
        if ( !empty($raw['hero_slides']) && is_array($raw['hero_slides']) ) { foreach ($raw['hero_slides'] as $sl) { if (!is_array($sl)) continue; $saved['hero_slides'][] = array('image'=>absint($sl['image']??0),'eyebrow'=>sanitize_text_field($sl['eyebrow']??''),'title'=>sanitize_text_field($sl['title']??''),'subtitle'=>sanitize_text_field($sl['subtitle']??''),'cta_text'=>sanitize_text_field($sl['cta_text']??''),'cta_link'=>esc_url_raw($sl['cta_link']??'#'),'cta_style'=>sanitize_text_field($sl['cta_style']??'primary')); } }
        $saved['trust_features'] = array();
        if ( !empty($raw['trust_features']) && is_array($raw['trust_features']) ) { foreach ($raw['trust_features'] as $ft) { if (!is_array($ft)) continue; $saved['trust_features'][] = array('icon_svg'=>wp_kses($ft['icon_svg']??'',velure_core_svg_allowed()),'title'=>sanitize_text_field($ft['title']??''),'description'=>sanitize_text_field($ft['description']??'')); } }
        $saved['brand_names'] = array();
        if ( !empty($raw['brand_names']) && is_array($raw['brand_names']) ) { foreach ($raw['brand_names'] as $br) { if (!is_array($br)) continue; $n=sanitize_text_field($br['name']??''); if (''!==$n) $saved['brand_names'][] = array('name'=>$n); } }
        $saved['instagram_images'] = array();
        if ( !empty($raw['instagram_images']) && is_array($raw['instagram_images']) ) { foreach ($raw['instagram_images'] as $im) { if (!is_array($im)) continue; $saved['instagram_images'][] = array('image'=>absint($im['image']??0),'link'=>esc_url_raw($im['link']??''),'alt'=>sanitize_text_field($im['alt']??'')); } }

        $valid = array('hero','features','categories','products','split_banner','marquee','testimonials','blog','instagram');
        $saved['section_order'] = array();
        if ( !empty($raw['section_order']) && is_array($raw['section_order']) ) { foreach ($raw['section_order'] as $sec) { $sec=sanitize_text_field($sec); if (in_array($sec,$valid,true)) $saved['section_order'][]=$sec; } }
        foreach ($valid as $sec) { if (!in_array($sec,$saved['section_order'],true)) $saved['section_order'][]=$sec; }

        update_option( VELURE_CORE_OPTION, $saved );
        update_option( 'velure_core_last_saved', current_time('mysql') );
        wp_send_json_success( array( 'message' => 'Reglages publies avec succes.' ) );
}

/* ═══════════════════════════════════════════════════════════════
   6. MAIN ADMIN PAGE (Elementor-like layout)
   ═══════════════════════════════════════════════════════════════ */
function velure_core_render_admin_page() {
        $s = velure_core_get_settings();
        $nav_items = array(
                'sections'    => array('Sections','&#128202;','Gestion du layout'),
                'hero'        => array('Hero','&#127916;','Slider et CTA'),
                'features'    => array('Confiance','&#128142;','Barre de confiance'),
                'categories'  => array('Categories','&#127970;','Univers produits'),
                'products'    => array('Produits','&#128722;','Pieces vedettes'),
                'banner'      => array('Banniere','&#128248;','Banniere splitee'),
                'marquee'     => array('Marquee','&#127926;','Bandeau defilant'),
                'testimonials'=> array('Temoignages','&#128172;','Avis clients'),
                'blog'        => array('Blog','&#128240;','Le Journal'),
                'instagram'   => array('Instagram','&#128247;','Galerie photos'),
                'global'      => array('Global','&#127760;','Reglages generaux'),
        );
        $all_templates = array();
        foreach ( array('hero','features','categories','products','banner') as $ts ) { $all_templates[$ts] = velure_core_get_templates($ts); }
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
                <script type="application/json" id="vc-templates-data"><?php echo esc_json_encode($all_templates); ?></script>
        </div>
        <?php
}
