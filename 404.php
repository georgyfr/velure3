<?php
/**
 * Velure3 — 404 Page
 * @package Velure3
 * @version 1.0.0
 */

get_header();
?>

<div class="velure-404">
  <h1>404</h1>
  <p>La page que vous recherchez n'existe pas ou a ete deplacee.</p>
  <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="velure-btn velure-btn-primary">Retour a l'accueil</a>
</div>

<?php get_footer(); ?>