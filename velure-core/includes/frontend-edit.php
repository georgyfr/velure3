<?php
/**
 * Velure Core — Frontend Edit Buttons
 * Injects contextual "Modifier" buttons on the frontend for admin users.
 * Each button links to the Visual Builder for that specific section.
 *
 * @package VelureCore
 * @since 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Section registry: maps section slugs to labels.
 * Used by the edit button helper and by the Visual Builder routing.
 */
function velure_core_get_editable_sections() {
	return array(
		'hero'        => array( 'label' => 'Hero',        'icon' => '&#127916;' ),
		'features'    => array( 'label' => 'Confiance',    'icon' => '&#128142;' ),
		'categories'  => array( 'label' => 'Categories',   'icon' => '&#127970;' ),
		'products'    => array( 'label' => 'Produits',     'icon' => '&#128722;' ),
		'split_banner'=> array( 'label' => 'Banniere',     'icon' => '&#128248;' ),
		'marquee'     => array( 'label' => 'Marquee',      'icon' => '&#127926;' ),
		'testimonials'=> array( 'label' => 'Temoignages',  'icon' => '&#128172;' ),
		'blog'        => array( 'label' => 'Blog',         'icon' => '&#128240;' ),
		'instagram'   => array( 'label' => 'Instagram',    'icon' => '&#128247;' ),
	);
}

/**
 * Render a "Modifier" button for a specific section on the frontend.
 *
 * This function:
 *  - Checks if the current user can edit theme options
 *  - Builds the admin URL with the section slug
 *  - Outputs an absolutely positioned button with hover behavior
 *
 * @param string $section_slug  The section identifier (hero, features, etc.)
 */
function velure_core_edit_button( $section_slug ) {

	/* Only show for users who can edit theme options */
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	/* Validate slug against registry */
	$sections = velure_core_get_editable_sections();
	if ( ! isset( $sections[ $section_slug ] ) ) {
		return;
	}

	$section = $sections[ $section_slug ];
	$edit_url = admin_url( 'admin.php?page=velure-front-page&section=' . urlencode( $section_slug ) );

	?>
	<div class="vc-fe-edit-wrap" data-vc-section="<?php echo esc_attr( $section_slug ); ?>">
		<a href="<?php echo esc_url( $edit_url ); ?>"
		   class="vc-fe-edit-btn"
		   target="_blank"
		   rel="noopener noreferrer"
		   title="<?php printf( esc_attr__( 'Modifier la section %s', 'velure-core' ), esc_attr( $section['label'] ) ); ?>">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
			<span><?php echo esc_html( $section['label'] ); ?></span>
		</a>
	</div>
	<?php
}

/**
 * Enqueue frontend edit button assets (CSS only — JS not needed for Step 1).
 * Called conditionally: only for admin-capable users, only on the front-end.
 */
function velure_core_frontend_edit_assets() {

	/* Never load in admin */
	if ( is_admin() ) {
		return;
	}

	/* Only for users who can edit */
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	wp_enqueue_style(
		'velure-core-frontend-edit',
		VELURE_CORE_URI . 'assets/css/frontend-edit.css',
		array(),
		VELURE_CORE_VERSION
	);
}