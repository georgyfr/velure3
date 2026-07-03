<?php
/**
 * Velure3 — Single Post
 * @package Velure3
 * @version 1.0.0
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>

<article <?php post_class(); ?>>
  <div class="velure-page-header">
    <div class="velure-container">
      <?php
      $cats = get_the_category();
      if ( ! empty( $cats ) ) :
      ?>
        <span class="velure-eyebrow"><?php echo esc_html( $cats[0]->name ); ?></span>
      <?php endif; ?>
      <h1><?php the_title(); ?></h1>
      <p style="color:#888;font-size:0.85rem;margin-top:0.5rem;"><?php echo get_the_date( 'd F Y' ); ?></p>
    </div>
  </div>

  <?php if ( has_post_thumbnail() ) : ?>
    <div class="velure-container" style="margin-bottom:2rem;">
      <?php the_post_thumbnail( 'large' ); ?>
    </div>
  <?php endif; ?>

  <div class="velure-page-content">
    <?php the_content(); ?>
  </div>
</article>

<?php endwhile; ?>

<?php get_footer(); ?>