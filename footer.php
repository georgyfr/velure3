<?php
/**
 * Velure3 — Classic Footer
 *
 * @package Velure3
 * @version 1.0.0
 */
?>

  <!-- NEWSLETTER BAND -->
  <div class="velure-newsletter-band">
    <div class="velure-container">
      <div class="velure-newsletter-inner">
        <div class="velure-newsletter-text">
          <h3 class="velure-newsletter-title">Rejoignez l'univers Velure</h3>
          <p class="velure-newsletter-desc">Inscrivez-vous pour recevoir nos nouveautes, conseils styling et offres exclusives.</p>
        </div>
        <form class="velure-newsletter-form" action="#" method="post">
          <input type="email" class="velure-newsletter-input" placeholder="Votre adresse email" required />
          <button type="submit" class="velure-newsletter-btn">S'inscrire</button>
        </form>
      </div>
    </div>
  </div>

  <!-- MAIN FOOTER -->
  <footer class="velure-footer">
    <div class="velure-footer-main">
      <div class="velure-container">
        <div class="velure-footer-grid">

          <!-- Brand Column -->
          <div class="velure-footer-col velure-footer-brand">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="velure-footer-logo">
              <?php if ( has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
              <?php else : ?>
                <span class="velure-logo-text">VELURE</span>
              <?php endif; ?>
            </a>
            <p class="velure-footer-about">L'elegance au quotidien. Des pieces intemporelles pensees pour sublimer chaque moment de votre vie.</p>
            <div class="velure-footer-social">
              <?php
              wp_nav_menu( array(
                'theme_location' => 'social',
                'container'      => false,
                'menu_class'     => 'velure-footer-social',
                'fallback_cb'    => 'velure3_default_social_menu',
                'depth'          => 1,
                'echo'           => true,
              ) );
              ?>
            </div>
          </div>

          <!-- Boutique Column -->
          <div class="velure-footer-col">
            <h4 class="velure-footer-heading">Boutique</h4>
            <?php
            wp_nav_menu( array(
              'theme_location' => 'footer_boutique',
              'container'      => false,
              'menu_class'     => 'velure-footer-links',
              'fallback_cb'    => 'velure3_default_footer_boutique_menu',
              'depth'          => 1,
              'echo'           => true,
            ) );
            ?>
          </div>

          <!-- Info Column -->
          <div class="velure-footer-col">
            <h4 class="velure-footer-heading">Informations</h4>
            <?php
            wp_nav_menu( array(
              'theme_location' => 'footer_info',
              'container'      => false,
              'menu_class'     => 'velure-footer-links',
              'fallback_cb'    => 'velure3_default_footer_info_menu',
              'depth'          => 1,
              'echo'           => true,
            ) );
            ?>
          </div>

          <!-- Help Column -->
          <div class="velure-footer-col">
            <h4 class="velure-footer-heading">Aide</h4>
            <?php
            wp_nav_menu( array(
              'theme_location' => 'footer_help',
              'container'      => false,
              'menu_class'     => 'velure-footer-links',
              'fallback_cb'    => 'velure3_default_footer_help_menu',
              'depth'          => 1,
              'echo'           => true,
            ) );
            ?>
          </div>

        </div>
      </div>
    </div>

    <!-- Footer Bottom -->
    <div class="velure-footer-bottom">
      <div class="velure-container velure-footer-bottom-inner">
        <p class="velure-copyright">&copy; <?php echo date( 'Y' ); ?> Velure. Tous droits reserves.</p>
        <div class="velure-payments">
          <span class="velure-payment-label">Paiements :</span>
          <span class="velure-payment-icon">Visa</span>
          <span class="velure-payment-icon">Mastercard</span>
          <span class="velure-payment-icon">PayPal</span>
          <span class="velure-payment-icon">Apple Pay</span>
        </div>
      </div>
    </div>
  </footer>

<?php wp_footer(); ?>
</body>
</html>