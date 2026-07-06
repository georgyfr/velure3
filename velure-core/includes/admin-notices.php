<?php
/**
 * Velure Core — Admin Notices
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Theme Compatibility Notice ── */
add_action( 'admin_notices', 'velure_core_theme_notice' );
function velure_core_theme_notice() {
	$screen = get_current_screen();
	if ( $screen && 'toplevel_page_velure-front-page' === $screen->id ) return;

	$theme = wp_get_theme();
	if ( 'Velure3' === $theme->name || 'velure3' === $theme->template ) return;
	?>
	<div class="notice notice-info">
		<p>
			<strong>Velure Core :</strong>
			<?php
			printf(
				esc_html__( 'Ce plugin est optimise pour le theme Velure3. Le theme actif est "%s". Certaines fonctionnalites peuvent ne pas fonctionner correctement.', 'velure-core' ),
				esc_html( $theme->name )
			);
			?>
		</p>
	</div>
	<?php
}