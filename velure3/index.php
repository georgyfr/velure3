<?php
/**
 * Velure3 — Index / Blog Template
 *
 * @package Velure3
 */

get_header();
?>

<div class="velure-section">
  <div class="velure-container">
    <h2 class="velure-section-heading">Le Journal</h2>

    <?php if ( have_posts() ) : ?>
    <div class="velure-grid velure-grid-3">
      <?php while ( have_posts() ) : the_post(); ?>
      <article <?php post_class( 'velure-blog-card' ); ?>>
        <div class="velure-blog-card-image">
          <?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>">
              <?php the_post_thumbnail( 'medium_large' ); ?>
            </a>
          <?php else : ?>
            <div style="background:var(--v-color-soft);width:100%;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;">
              <span style="color:#999;font-size:0.85rem;">Pas d'image</span>
            </div>
          <?php endif; ?>
        </div>
        <div class="velure-blog-card-meta">
          <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
          <?php
          $cats = get_the_category();
          if ( ! empty( $cats ) ) :
          ?>
            <span style="margin:0 6px;opacity:0.4;">&bull;</span>
            <span><?php echo esc_html( $cats[0]->name ); ?></span>
          <?php endif; ?>
        </div>
        <h3 class="velure-blog-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="velure-blog-card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '...' ) ); ?></p>
      </article>
      <?php endwhile; ?>
    </div>

    <div style="text-align:center;margin-top:3rem;">
      <?php
      the_posts_pagination( array(
        'mid_size'  => 2,
        'prev_text' => '&larr; Pr&eacute;c&eacute;dent',
        'next_text' => 'Suivant &rarr;',
      ) );
      ?>
    </div>

    <?php else : ?>
    <p style="text-align:center;color:#888;padding:4rem 0;">Aucun article pour le moment.</p>
    <?php endif; ?>
  </div>
</div>

<?php get_footer(); ?>