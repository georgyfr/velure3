<?php
/**
 * Velure3 — Archive
 * @package Velure3
 * @version 1.0.0
 */

get_header();
?>

<div class="velure-page-header">
  <div class="velure-container">
    <h1><?php the_archive_title(); ?></h1>
    <?php the_archive_description( '<p style="color:#888;max-width:600px;margin:0.5rem auto 0;">', '</p>' ); ?>
  </div>
</div>

<div class="velure-container" style="padding-bottom:5rem;">
  <?php if ( have_posts() ) : ?>
    <div class="velure-grid velure-grid-3">
      <?php while ( have_posts() ) : the_post(); ?>
        <a href="<?php the_permalink(); ?>" class="velure-blog-card">
          <div class="velure-blog-card-img">
            <?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium_large' ); endif; ?>
          </div>
          <span class="velure-blog-card-meta"><?php echo get_the_date( 'd M Y' ); ?></span>
          <h3 class="velure-blog-card-title"><?php the_title(); ?></h3>
          <p class="velure-blog-card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
        </a>
      <?php endwhile; ?>
    </div>

    <div style="text-align:center;padding:2rem 0;">
      <?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '&larr; Precedent', 'next_text' => 'Suivant &rarr;' ) ); ?>
    </div>
  <?php else : ?>
    <p style="text-align:center;padding:4rem 0;color:#888;">Aucun resultat.</p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>