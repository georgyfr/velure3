<?php
/**
 * Velure3 — Header
 * Topbar + Sticky Navbar + Search Overlay + Mobile Menu
 *
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="profile" href="https://gmpg.org/xfn/11">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ═══════ TOP BAR ═══════ -->
<div class="velure-topbar">
  <div class="velure-container velure-topbar-inner">
    <span class="velure-topbar-text">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:-2px;margin-right:4px;"><rect x="1" y="3" width="15" height="13" rx="2" ry="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      Livraison offerte des 150 EUR d'achat &bull; Retours gratuits 30 jours
    </span>
    <div class="velure-topbar-actions">
      <a href="<?php echo esc_url( home_url( '/mon-compte/' ) ); ?>" class="velure-topbar-link">Mon Compte</a>
      <span class="velure-topbar-sep">|</span>
      <a href="<?php echo esc_url( home_url( '/aide/' ) ); ?>" class="velure-topbar-link">Aide</a>
    </div>
  </div>
</div>

<!-- ═══════ NAVBAR ═══════ -->
<nav class="velure-navbar" id="velure-navbar">
  <div class="velure-container velure-navbar-inner">

    <!-- Logo -->
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="velure-logo" rel="home">
      <?php if ( has_custom_logo() ) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <span class="velure-logo-text">VELURE</span>
      <?php endif; ?>
    </a>

    <!-- Desktop Navigation -->
    <div class="velure-nav">
      <?php
      wp_nav_menu( array(
        'theme_location' => 'primary',
        'container'      => false,
        'menu_class'     => 'velure-nav-list',
        'fallback_cb'    => 'velure3_default_primary_menu',
        'walker'         => new Velure3_Mega_Menu_Walker(),
      ) );
      ?>
    </div>

    <!-- Header Actions -->
    <div class="velure-header-actions">
      <!-- Search -->
      <button class="velure-header-btn" id="velure-search-toggle" aria-label="Rechercher">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </button>

      <!-- Account -->
      <a href="<?php echo esc_url( home_url( '/mon-compte/' ) ); ?>" class="velure-header-btn" aria-label="Mon compte">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </a>

      <!-- Wishlist -->
      <a href="<?php echo esc_url( home_url( '/wishlist/' ) ); ?>" class="velure-header-btn" aria-label="Favoris">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      </a>

      <!-- Cart -->
      <a href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/panier/' ) ); ?>" class="velure-header-btn velure-cart-btn" aria-label="Panier">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <?php if ( class_exists( 'WooCommerce' ) ) : ?>
        <span class="velure-cart-count" id="velure-cart-count" style="<?php echo WC()->cart->get_cart_contents_count() > 0 ? '' : 'display:none;'; ?>">
          <?php echo WC()->cart->get_cart_contents_count(); ?>
        </span>
        <?php endif; ?>
      </a>

      <!-- Mobile Toggle -->
      <button class="velure-menu-toggle" id="velure-menu-toggle" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>

  </div>
</nav>

<!-- ═══════ SEARCH OVERLAY ═══════ -->
<div class="velure-search-overlay" id="velure-search-overlay">
  <button class="velure-search-close" id="velure-search-close" aria-label="Fermer">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
  </button>
  <div class="velure-search-inner">
    <form role="search" method="get" class="velure-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
      <input type="search" class="velure-search-input" id="velure-search-input" placeholder="Rechercher un produit, une collection..." value="<?php echo get_search_query(); ?>" name="s" autocomplete="off">
      <input type="hidden" name="post_type" value="product">
      <button type="submit" class="velure-search-submit" aria-label="Rechercher">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </button>
    </form>
  </div>
</div>

<!-- ═══════ MOBILE OVERLAY ═══════ -->
<div class="velure-mobile-overlay" id="velure-mobile-overlay"></div>

<!-- ═══════ MOBILE MENU ═══════ -->
<div class="velure-mobile-menu" id="velure-mobile-menu">
  <div class="velure-mobile-menu-header">
    <span class="velure-logo-text">VELURE</span>
    <button class="velure-mobile-close" id="velure-mobile-close" aria-label="Fermer le menu">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <?php
  wp_nav_menu( array(
    'theme_location' => 'primary',
    'container'      => false,
    'menu_class'     => 'velure-mobile-nav-list',
    'fallback_cb'    => 'velure3_default_mobile_menu',
    'depth'          => 1,
  ) );
  ?>
  <div class="velure-mobile-menu-footer">
    <a href="<?php echo esc_url( home_url( '/mon-compte/' ) ); ?>" class="velure-mobile-menu-link">Mon Compte</a>
    <a href="<?php echo esc_url( home_url( '/aide/' ) ); ?>" class="velure-mobile-menu-link">Centre d'aide</a>
  </div>
</div>