<?php
/**
 * Velure3 — Page Template
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<div class="velure-page-header">
  <div class="velure-container">
    <?php the_title( '<h1>', '</h1>' ); ?>
  </div>
</div>

<div class="velure-page-content">
  <?php
  while ( have_posts() ) :
    the_post();
    the_content();
  endwhile;
  ?>
</div>

<?php get_footer(); ?>