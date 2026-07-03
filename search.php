<?php
/**
 * Velure3 — Search Results
 * @package Velure3
 * @version 1.0.0
 */

get_header();
?>

<div class="velure-page-header">
  <div class="velure-container">
    <h1>Resultats pour : <?php echo esc_html( get_search_query() ); ?></h1>
  </div>
</div>

<div class="velure-container" style="padding-bottom:5rem;">
  <?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
      <article <?php post_class( 'velure-post-card' ); ?>>
        <div class="velure-post-card-content">
          <h2 class="velure-post-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p class="velure-post-card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 25 ); ?></p>
        </div>
      </article>
    <?php endwhile; ?>
    <div style="text-align:center;padding:2rem 0;">
      <?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '&larr; Precedent', 'next_text' => 'Suivant &rarr;' ) ); ?>
    </div>
  <?php else : ?>
    <div class="velure-404">
      <h1 style="font-size:3rem;">Aucun resultat</h1>
      <p>Nous n'avons trouve aucun contenu correspondant a votre recherche.</p>
      <?php get_search_form(); ?>
    </div>
  <?php endif; ?>
</div>

<?php get_footer(); ?>