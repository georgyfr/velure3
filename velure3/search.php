<?php
/**
 * Velure3 — Search Results Template
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<div class="velure-page-header">
  <div class="velure-container">
    <h1>Resultats pour : "<?php echo esc_html( get_search_query() ); ?>"</h1>
  </div>
</div>

<div class="velure-container" style="padding-bottom:5rem;">
  <?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
      <article <?php post_class( 'velure-post-card velure-animate' ); ?>>
        <?php if ( has_post_thumbnail() ) : ?>
          <div class="velure-post-card-img">
            <a href="<?php the_permalink(); ?>">
              <?php the_post_thumbnail( 'medium_large' ); ?>
            </a>
          </div>
        <?php endif; ?>
        <div class="velure-post-card-content">
          <?php
          $cats = get_the_category();
          if ( ! empty( $cats ) ) :
          ?>
            <span class="velure-post-card-meta"><?php echo esc_html( $cats[0]->name ); ?> &bull; <?php echo get_the_date( 'd M Y' ); ?></span>
          <?php endif; ?>
          <h2 class="velure-post-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p class="velure-post-card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 25 ); ?></p>
        </div>
      </article>
    <?php endwhile; ?>

    <div style="text-align:center;padding:2rem 0;">
      <?php
      the_posts_pagination( array(
        'mid_size'  => 2,
        'prev_text' => '&larr; Precedent',
        'next_text' => 'Suivant &rarr;',
      ) );
      ?>
    </div>

  <?php else : ?>
    <div class="velure-404">
      <p>Aucun resultat pour "<?php echo esc_html( get_search_query() ); ?>".</p>
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="velure-btn velure-btn-primary">Retour a l'accueil</a>
    </div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>