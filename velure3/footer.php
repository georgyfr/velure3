<?php
/**
 * Velure3 — Footer
 * Newsletter Band + 4-Column Footer + Bottom Bar
 *
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>

<!-- ═══════ NEWSLETTER BAND ═══════ -->
<div class="velure-newsletter-band">
  <div class="velure-container">
    <div class="velure-newsletter-inner">
      <div>
        <h3 class="velure-newsletter-title">Restez informe</h3>
        <p class="velure-newsletter-desc">Inscrivez-vous pour recevoir nos nouveautes et offres exclusives.</p>
      </div>
      <form class="velure-newsletter-form" action="#" method="post">
        <input type="email" class="velure-newsletter-input" placeholder="Votre adresse email" required aria-label="Email">
        <button type="submit" class="velure-newsletter-btn">S'inscrire</button>
      </form>
    </div>
  </div>
</div>

<!-- ═══════ FOOTER ═══════ -->
<footer class="velure-footer">
  <div class="velure-container">
    <div class="velure-footer-main">
      <div class="velure-footer-grid">

        <!-- Brand Column -->
        <div class="velure-footer-brand">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="velure-footer-logo">
            <span class="velure-logo-text">VELURE</span>
          </a>
          <p class="velure-footer-about">L'expression d'un style intemporel. Des vetements et accessoires conqus avec soin pour sublimer chaque moment de votre vie.</p>
          <?php wp_nav_menu( array(
            'theme_location' => 'social',
            'container'      => false,
            'fallback_cb'    => 'velure3_default_social_menu',
            'depth'          => 1,
          ) ); ?>
        </div>

        <!-- Boutique Column -->
        <div>
          <h4 class="velure-footer-heading">Boutique</h4>
          <?php wp_nav_menu( array(
            'theme_location' => 'footer_boutique',
            'container'      => false,
            'menu_class'     => 'velure-footer-links',
            'fallback_cb'    => 'velure3_default_footer_boutique_menu',
            'depth'          => 1,
          ) ); ?>
        </div>

        <!-- Informations Column -->
        <div>
          <h4 class="velure-footer-heading">Informations</h4>
          <?php wp_nav_menu( array(
            'theme_location' => 'footer_info',
            'container'      => false,
            'menu_class'     => 'velure-footer-links',
            'fallback_cb'    => 'velure3_default_footer_info_menu',
            'depth'          => 1,
          ) ); ?>
        </div>

        <!-- Aide Column -->
        <div>
          <h4 class="velure-footer-heading">Aide</h4>
          <?php wp_nav_menu( array(
            'theme_location' => 'footer_help',
            'container'      => false,
            'menu_class'     => 'velure-footer-links',
            'fallback_cb'    => 'velure3_default_footer_help_menu',
            'depth'          => 1,
          ) ); ?>
        </div>

      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="velure-footer-bottom">
      <div class="velure-footer-bottom-inner">
        <span class="velure-copyright">&copy; <?php echo date( 'Y' ); ?> Velure. Tous droits reserves.</span>
        <div class="velure-payments">
          <span class="velure-payment-label">Paiements securises :</span>
          <span class="velure-payment-icon">VISA</span>
          <span class="velure-payment-icon">MC</span>
          <span class="velure-payment-icon">AMEX</span>
          <span class="velure-payment-icon">PAYPAL</span>
        </div>
      </div>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>