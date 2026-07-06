<?php
/**
 * Velure Core — Admin Notices
 *
 * @package VelureCore
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── ACF Required Notice ── */
add_action( 'admin_notices', 'velure_core_acf_notice' );
function velure_core_acf_notice() {
	if ( function_exists( 'acf_add_options_page' ) ) return;
	?>
	<div class="notice notice-warning">
		<p>
			<strong>Velure Core :</strong>
			<?php esc_html_e( 'Le plugin Advanced Custom Fields (ACF) n\'est pas actif. La page d\'accueil utilisera le contenu par defaut.', 'velure-core' ); ?>
			<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=term' ) ); ?>">
				<?php esc_html_e( 'Installer ACF', 'velure-core' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/* ── Theme Compatibility Notice ── */
add_action( 'admin_notices', 'velure_core_theme_notice' );
function velure_core_theme_notice() {
	$theme = wp_get_theme();
	if ( 'Velure3' === $theme->name || 'velure3' === $theme->template ) return;
	?>
	<div class="notice notice-info">
		<p>
			<strong>Velure Core :</strong>
			<?php
			printf(
				/* translators: %s: theme name */
				esc_html__( 'Ce plugin est optimise pour le theme Velure3. Le theme actif est "%s". Certaines fonctionnalites peuvent ne pas fonctionner correctement.', 'velure-core' ),
				esc_html( $theme->name )
			);
			?>
		</p>
	</div>
	<?php
}

/* ── Welcome Banner on plugin page ── */
add_action( 'admin_notices', 'velure_core_welcome_banner' );
function velure_core_welcome_banner() {
	$screen = get_current_screen();
	if ( ! $screen || 'appearance_page_velure-front-page' !== $screen->id ) return;
	?>
	<div style="background:linear-gradient(135deg,#1a1a1a 0%,#2d2d2d 100%);color:#fff;padding:1.5rem 2rem;border-radius:8px;margin:10px 0 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
		<div>
			<h2 style="margin:0 0 0.25rem;font-family:system-ui,sans-serif;font-size:1.25rem;color:#C8A97E;">Velure Core v<?php echo esc_html( VELURE_CORE_VERSION ); ?></h2>
			<p style="margin:0;color:#aaa;font-size:0.9rem;"><?php esc_html_e( 'Personnalisez chaque section de votre page d\'accueil. Ordre, contenu, styles — tout est configurable.', 'velure-core' ); ?></p>
		</div>
		<a href="https://velure.paris" target="_blank" rel="noopener" style="background:#C8A97E;color:#1a1a1a;padding:0.5rem 1.25rem;border-radius:4px;text-decoration:none;font-weight:600;font-size:0.85rem;white-space:nowrap;"><?php esc_html_e( 'Documentation', 'velure-core' ); ?></a>
	</div>
	<?php
}