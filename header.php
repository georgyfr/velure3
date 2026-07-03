<?php
/**
 * Velure3 — Classic Header
 *
 * @package Velure3
 * @version 1.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="profile" href="https://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="velure-header" id="velure-header">

  <!-- Top Bar -->
  <div class="velure-topbar">
    <div class="velure-container velure-topbar-inner">
      <p class="velure-topbar-text">Livraison gratuite a partir de 75&euro; &bull; Retours sous 30 jours</p>
      <div class="velure-topbar-actions">
        <?php if ( is_user_logged_in() ) : ?>
          <a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : admin_url( 'profile.php' ) ); ?>" class="velure-topbar-link">Mon Compte</a>
        <?php else : ?>
          <a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url( get_permalink() ) ); ?>" class="velure-topbar-link">Connexion</a>
        <?php endif; ?>
        <span class="velure-topbar-sep">|</span>
        <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="velure-topbar-link">Aide</a>
      </div>
    </div>
  </div>

  <!-- Main Navbar -->
  <header class="velure-navbar" id="velure-navbar">
    <div class="velure-container velure-navbar-inner">

      <!-- Mobile Toggle -->
      <button class="velure-menu-toggle" id="velure-menu-toggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>

      <!-- Logo -->
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="velure-logo">
        <?php if ( has_custom_logo() ) : ?>
          <?php the_custom_logo(); ?>
        <?php else : ?>
          <span class="velure-logo-text">VELURE</span>
        <?php endif; ?>
      </a>

      <!-- Desktop Navigation -->
      <nav class="velure-nav" id="velure-nav">
        <?php
        wp_nav_menu( array(
          'theme_location' => 'primary',
          'container'      => false,
          'menu_class'     => 'velure-nav-list',
          'walker'         => new Velure3_Mega_Menu_Walker(),
          'fallback_cb'    => 'velure3_default_primary_menu',
          'depth'          => 3,
          'echo'           => true,
        ) );
        ?>
      </nav>

      <!-- Header Actions -->
      <div class="velure-header-actions">
        <button class="velure-header-btn velure-search-toggle" id="velure-search-toggle" aria-label="Rechercher">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
        <a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>" class="velure-header-btn" aria-label="Mon compte">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </a>
        <a href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>" class="velure-header-btn" aria-label="Favoris">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </a>
        <a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'cart' ) : '#' ); ?>" class="velure-header-btn velure-cart-btn" aria-label="Panier">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          <span class="velure-cart-count" id="velure-cart-count"><?php echo class_exists( 'WooCommerce' ) ? absint( WC()->cart->get_cart_contents_count() ) : '0'; ?></span>
        </a>
      </div>
    </div>
  </header>

  <!-- Search Overlay -->
  <div class="velure-search-overlay" id="velure-search-overlay">
    <div class="velure-search-inner">
      <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="velure-search-form">
        <input type="search" class="velure-search-input" placeholder="Rechercher un produit, une collection..." value="<?php echo esc_attr( get_search_query() ); ?>" name="s" id="velure-search-input" autocomplete="off" />
        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
          <input type="hidden" name="post_type" value="product" />
        <?php endif; ?>
        <button type="submit" class="velure-search-submit" aria-label="Rechercher">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
        <button type="button" class="velure-search-close" id="velure-search-close" aria-label="Fermer">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </form>
    </div>
  </div>

  <!-- Mobile Menu -->
  <div class="velure-mobile-overlay" id="velure-mobile-overlay"></div>
  <nav class="velure-mobile-menu" id="velure-mobile-menu">
    <div class="velure-mobile-menu-header">
      <span class="velure-logo-text">VELURE</span>
      <button class="velure-mobile-close" id="velure-mobile-close" aria-label="Fermer">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <?php
    wp_nav_menu( array(
      'theme_location' => 'primary',
      'container'      => false,
      'menu_class'     => 'velure-mobile-nav-list',
      'fallback_cb'    => 'velure3_default_mobile_menu',
      'depth'          => 1,
      'echo'           => true,
    ) );
    ?>
    <div class="velure-mobile-menu-footer">
      <a href="<?php echo esc_url( class_exists( 'WooCommerce' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>" class="velure-mobile-menu-link">Mon Compte</a>
      <a href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>" class="velure-mobile-menu-link">Mes Favoris</a>
    </div>
  </nav>

</div>