<?php
/**
 * Velure3 — Archive Template
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<div class="velure-page-header">
  <div class="velure-container">
    <h1>
      <?php
      if ( is_category() ) {
        single_cat_title();
      } elseif ( is_tag() ) {
        single_tag_title();
      } elseif ( is_tax() ) {
        single_term_title();
      } elseif ( is_post_type_archive() ) {
        post_type_archive_title();
      } elseif ( is_author() ) {
        echo get_the_author();
      } elseif ( is_year() ) {
        echo get_the_date( 'Y' );
      } elseif ( is_month() ) {
        echo get_the_date( 'F Y' );
      } else {
        _e( 'Archives', 'velure3' );
      }
      ?>
    </h1>
    <?php if ( is_category() || is_tag() || is_tax() ) {
      $desc = term_description();
      if ( $desc ) echo '<p style="color:#888;margin-top:0.5rem;max-width:600px;margin-left:auto;margin-right:auto;">' . wp_kses_post( $desc ) . '</p>';
    } ?>
  </div>
</div>

<div class="velure-container" style="padding-bottom:5rem;">
  <?php if ( have_posts() ) : ?>
    <div class="velure-grid velure-grid-3">
      <?php while ( have_posts() ) : the_post(); ?>
        <article <?php post_class( 'velure-blog-card velure-animate' ); ?>>
          <?php if ( has_post_thumbnail() ) : ?>
            <div class="velure-blog-card-img">
              <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail( 'medium_large' ); ?>
              </a>
            </div>
          <?php endif; ?>
          <?php
          $cats = get_the_category();
          if ( ! empty( $cats ) ) :
          ?>
            <div class="velure-blog-card-meta"><?php echo esc_html( $cats[0]->name ); ?></div>
          <?php endif; ?>
          <h2 class="velure-blog-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <p class="velure-blog-card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
        </article>
      <?php endwhile; ?>
    </div>

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
    <p style="text-align:center;padding:4rem 0;color:#888;">Aucun contenu pour le moment.</p>
  <?php endif; ?>
</div>

<?php get_footer(); ?>