<?php
/**
 * Velure3 — Single Post Template
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>

<?php
$cats = get_the_category();
$cat_name = ! empty( $cats ) ? $cats[0]->name : '';
?>

<div class="velure-page-header">
  <div class="velure-container">
    <?php if ( $cat_name ) : ?>
      <span class="velure-eyebrow"><?php echo esc_html( $cat_name ); ?></span>
    <?php endif; ?>
    <h1 style="font-size:clamp(1.8rem,4vw,2.8rem);"><?php the_title(); ?></h1>
  </div>
</div>

<?php if ( has_post_thumbnail() ) : ?>
  <div style="max-width:var(--v-container-wide);margin:0 auto;">
    <?php the_post_thumbnail( 'large', array( 'style' => 'width:100%;height:auto;display:block;' ) ); ?>
  </div>
<?php endif; ?>

<div class="velure-page-content">
  <?php the_content(); ?>

  <?php
  the_post_navigation( array(
    'prev_text' => '<span class="velure-eyebrow" style="margin-bottom:0.25rem;">Precedent</span><br>%title',
    'next_text' => '<span class="velure-eyebrow" style="margin-bottom:0.25rem;">Suivant</span><br>%title',
  ) );
  ?>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>