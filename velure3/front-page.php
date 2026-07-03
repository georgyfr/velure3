<?php
/**
 * Velure3 — Front Page (Page d'accueil)
 * 9 sections dynamiques pilotées par ACF avec fallbacks
 *
 * @package Velure3
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$visibility = velure3_get_section_visibility();

get_header();

/* ═══════════════════════════════════════════════════
   1. HERO SLIDER
   ═══════════════════════════════════════════════════ */
if ( $visibility['hero'] ) :
  $hero  = velure3_get_hero_data();
  $h_cls = 'v-hero-slider';
  if ( 'tall' === $hero['height'] )    $h_cls .= ' v-hero-tall';
  if ( 'compact' === $hero['height'] ) $h_cls .= ' v-hero-compact';
?>
<div class="<?php echo esc_attr( $h_cls ); ?>">
  <div class="v-hero-main">
    <?php foreach ( $hero['slides'] as $i => $slide ) : ?>
    <div class="v-hero-slide<?php echo 0 === $i ? ' v-hero-slide--active' : ''; ?>">
      <div class="v-hero-slide-bg" style="background-image:url('<?php echo esc_url( $slide['image'] ); ?>');"></div>
      <div class="v-hero-slide-overlay"></div>
      <div class="v-hero-slide-content">
        <?php if ( $slide['eyebrow'] ) : ?>
          <span class="velure-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></span>
        <?php endif; ?>
        <h2><?php echo wp_kses_post( $slide['title'] ); ?></h2>
        <?php if ( $slide['subtitle'] ) : ?>
          <p class="v-hero-subtitle"><?php echo esc_html( $slide['subtitle'] ); ?></p>
        <?php endif; ?>
        <?php if ( $slide['cta_text'] ) : ?>
          <a href="<?php echo esc_url( $slide['cta_link'] ); ?>" class="velure-btn velure-btn-<?php echo esc_attr( $slide['cta_style'] ); ?>">
            <?php echo esc_html( $slide['cta_text'] ); ?>
          </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if ( count( $hero['slides'] ) > 1 ) : ?>
    <div class="v-hero-dots">
      <?php foreach ( $hero['slides'] as $i => $_s ) : ?>
      <button class="v-hero-dot<?php echo 0 === $i ? ' v-hero-dot--active' : ''; ?>" data-goto="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
      <?php endforeach; ?>
    </div>
    <div class="v-hero-progress"><div class="v-hero-progress-bar"></div></div>
    <?php endif; ?>
  </div>

  <?php if ( $hero['show_side'] && ! empty( $hero['side_blocks'] ) ) : ?>
  <div class="v-hero-side">
    <?php
    $bs = $hero['side_blocks']['bestseller'] ?? null;
    if ( $bs ) :
    ?>
    <a href="<?php echo esc_url( $bs['cta_link'] ); ?>" class="v-hero-side-card">
      <div class="v-hero-side-card-img" style="background-image:url('<?php echo esc_url( $bs['image'] ); ?>');"></div>
      <div class="v-hero-side-card-body">
        <span class="velure-eyebrow" style="margin-bottom:0.25rem;font-size:0.6rem;"><?php echo esc_html( $bs['label'] ); ?></span>
        <div class="v-hero-side-card-title"><?php echo esc_html( $bs['title'] ); ?></div>
        <?php if ( ! empty( $bs['price'] ) ) : ?>
        <span class="v-hero-side-card-price"><?php echo esc_html( $bs['price'] ); ?></span>
        <?php endif; ?>
        <span class="v-hero-side-card-cta"><?php echo esc_html( $bs['cta_text'] ?? 'VOIR' ); ?></span>
      </div>
    </a>
    <?php endif; ?>

    <?php
    $cat = $hero['side_blocks']['category'] ?? null;
    if ( $cat ) :
    ?>
    <a href="<?php echo esc_url( $cat['cta_link'] ); ?>" class="v-hero-side-card">
      <div class="v-hero-side-card-img" style="background-image:url('<?php echo esc_url( $cat['image'] ); ?>');"></div>
      <div class="v-hero-side-card-body">
        <span class="velure-eyebrow" style="margin-bottom:0.25rem;font-size:0.6rem;"><?php echo esc_html( $cat['label'] ); ?></span>
        <div class="v-hero-side-card-title"><?php echo esc_html( $cat['title'] ); ?></div>
        <span class="v-hero-side-card-cta">DECOUVRIR</span>
      </div>
    </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>


<?php
/* ═══════════════════════════════════════════════════
   2. FEATURES / TRUST BAR
   ═══════════════════════════════════════════════════ */
if ( $visibility['features'] ) :
  $features = velure3_get_features();
?>
<div class="velure-features velure-section-soft">
  <div class="velure-container">
    <div class="velure-features-grid">
      <?php foreach ( $features as $feat ) : ?>
      <div class="velure-feature-item">
        <div class="velure-feature-icon">
          <?php echo wp_kses( $feat['icon'], velure3_svg_allowed() ); ?>
        </div>
        <span class="velure-feature-title"><?php echo esc_html( $feat['title'] ); ?></span>
        <?php if ( $feat['desc'] ) : ?>
        <span class="velure-feature-desc"><?php echo esc_html( $feat['desc'] ); ?></span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>


<?php
/* ═══════════════════════════════════════════════════
   3. CATEGORIES CAROUSEL
   ═══════════════════════════════════════════════════ */
if ( $visibility['categories'] ) :
  $categories = velure3_get_product_categories();
  $cat_bg     = velure3_bg_class( velure3_opt( 'cat_bg_style', 'base' ) );
  $cat_eyebrow = velure3_opt( 'cat_eyebrow', 'Collections' );
  $cat_title   = velure3_opt( 'section_title_categories', 'Explorer par Univers' );
  $cat_desc    = velure3_opt( 'cat_description', 'Explorez nos univers et trouvez la piece qui vous correspond.' );
  $cat_cta_txt = velure3_opt( 'cat_cta_text', 'Toutes les categories' );
  $cat_cta_url = velure3_opt( 'cat_cta_link', '/boutique/' );
?>
<section class="velure-section <?php echo esc_attr( $cat_bg ); ?>">
  <div class="velure-container">
    <div class="velure-section-heading velure-animate">
      <?php if ( $cat_eyebrow ) : ?>
        <span class="velure-eyebrow"><?php echo esc_html( $cat_eyebrow ); ?></span>
      <?php endif; ?>
      <h2><?php echo esc_html( $cat_title ); ?></h2>
      <?php if ( $cat_desc ) : ?>
        <p><?php echo esc_html( $cat_desc ); ?></p>
      <?php endif; ?>
    </div>

    <div class="velure-carousel velure-animate">
      <button class="velure-carousel-arrow velure-carousel-arrow--prev" aria-label="Precedent">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <div class="velure-carousel-track">
        <?php foreach ( $categories as $cat ) : ?>
        <a href="<?php echo esc_url( $cat['link'] ); ?>" class="velure-category-card">
          <div class="velure-category-card-img">
            <img src="<?php echo esc_url( $cat['image'] ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>" loading="lazy">
          </div>
          <div class="velure-category-card-name"><?php echo esc_html( $cat['name'] ); ?></div>
          <?php if ( $cat['count'] > 0 ) : ?>
          <div class="velure-category-card-count"><?php echo $cat['count']; ?> articles</div>
          <?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>
      <button class="velure-carousel-arrow velure-carousel-arrow--next" aria-label="Suivant">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>

    <?php if ( $cat_cta_txt ) : ?>
    <div style="text-align:center;margin-top:2.5rem;" class="velure-animate">
      <a href="<?php echo esc_url( $cat_cta_url ); ?>" class="velure-btn velure-btn-outline"><?php echo esc_html( $cat_cta_txt ); ?></a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>


<?php
/* ═══════════════════════════════════════════════════
   4. FEATURED PRODUCTS
   ═══════════════════════════════════════════════════ */
if ( $visibility['products'] ) :
  $products     = velure3_get_featured_products();
  $prod_cols    = velure3_opt( 'prod_columns', '4' );
  $prod_eyebrow = velure3_opt( 'prod_eyebrow', 'Selection' );
  $prod_title   = velure3_opt( 'section_title_products', 'Pieces Vedettes' );
  $prod_desc    = velure3_opt( 'prod_description', 'Nos pieces les plus appreciees, choisies pour vous.' );
  $prod_cta_txt = velure3_opt( 'prod_cta_text', 'Voir toute la boutique' );
  $prod_cta_url = velure3_opt( 'prod_cta_link', '/boutique/' );
  $prod_bg      = velure3_bg_class( velure3_opt( 'prod_bg_style', 'soft' ) );
  $grid_cls     = 'velure-grid-' . $prod_cols;
?>
<section class="velure-section <?php echo esc_attr( $prod_bg ); ?>">
  <div class="velure-container">
    <div class="velure-section-heading velure-animate">
      <?php if ( $prod_eyebrow ) : ?>
        <span class="velure-eyebrow"><?php echo esc_html( $prod_eyebrow ); ?></span>
      <?php endif; ?>
      <h2><?php echo esc_html( $prod_title ); ?></h2>
      <?php if ( $prod_desc ) : ?>
        <p><?php echo esc_html( $prod_desc ); ?></p>
      <?php endif; ?>
    </div>

    <div class="velure-grid <?php echo esc_attr( $grid_cls ); ?> velure-animate">
      <?php foreach ( $products as $prod ) : ?>
      <a href="<?php echo esc_url( $prod['link'] ); ?>" class="velure-product-card">
        <div class="velure-product-card-img">
          <?php if ( ! empty( $prod['image'] ) ) : ?>
          <img src="<?php echo esc_url( $prod['image'] ); ?>" alt="<?php echo esc_attr( $prod['name'] ); ?>" loading="lazy">
          <?php endif; ?>
          <?php echo ! empty( $prod['badge'] ) ? $prod['badge'] : ''; ?>
        </div>
        <div class="velure-product-card-name"><?php echo esc_html( $prod['name'] ); ?></div>
        <div class="velure-product-card-price">
          <?php echo isset( $prod['price_html'] ) ? $prod['price_html'] : esc_html( $prod['price'] ?? '' ); ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ( $prod_cta_txt ) : ?>
    <div style="text-align:center;margin-top:2.5rem;" class="velure-animate">
      <a href="<?php echo esc_url( $prod_cta_url ); ?>" class="velure-btn velure-btn-outline"><?php echo esc_html( $prod_cta_txt ); ?></a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>


<?php
/* ═══════════════════════════════════════════════════
   5. SPLIT BANNER
   ═══════════════════════════════════════════════════ */
if ( $visibility['split_banner'] ) :
  $banner = velure3_get_split_banner();
?>
<section class="velure-split-banner velure-animate">
  <?php foreach ( array( 'left', 'right' ) as $side ) :
    $b     = $banner[ $side ];
    $style = $b['style'] === 'dark' ? 'velure-split-side--dark' : 'velure-split-side--light';
  ?>
  <div class="velure-split-side <?php echo esc_attr( $style ); ?>">
    <div class="velure-split-side-bg" style="background-image:url('<?php echo esc_url( $b['image'] ); ?>');"></div>
    <div class="velure-split-side-content">
      <?php if ( $b['eyebrow'] ) : ?>
        <span class="velure-eyebrow"><?php echo esc_html( $b['eyebrow'] ); ?></span>
      <?php endif; ?>
      <h2><?php echo esc_html( $b['title'] ); ?></h2>
      <?php if ( $b['desc'] ) : ?>
        <p><?php echo esc_html( $b['desc'] ); ?></p>
      <?php endif; ?>
      <?php if ( $b['cta_text'] ) : ?>
        <a href="<?php echo esc_url( $b['cta_link'] ); ?>" class="velure-btn velure-btn-<?php echo esc_attr( $b['cta_style'] ); ?>">
          <?php echo esc_html( $b['cta_text'] ); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>


<?php
/* ═══════════════════════════════════════════════════
   6. BRAND MARQUEE
   ═══════════════════════════════════════════════════ */
if ( $visibility['marquee'] ) :
  $brands = velure3_get_brands();
  $speed  = absint( velure3_opt( 'marquee_speed', 25 ) );
  $marquee_items = '';
  foreach ( $brands as $name ) {
    $marquee_items .= '<span class="velure-marquee-item">' . esc_html( $name ) . '</span>';
  }
  /* Double for seamless loop */
  $marquee_html = $marquee_items . $marquee_items;
?>
<div class="velure-marquee">
  <div class="velure-marquee-track" style="animation-duration:<?php echo $speed; ?>s;">
    <?php echo $marquee_html; ?>
  </div>
</div>
<?php endif; ?>


<?php
/* ═══════════════════════════════════════════════════
   7. TESTIMONIALS
   ═══════════════════════════════════════════════════ */
if ( $visibility['testimonials'] ) :
  $testi_count = absint( velure3_opt( 'testimonials_count', 3 ) );
  $testimonials = velure3_get_testimonials( $testi_count );
  $testi_eyebrow = velure3_opt( 'testi_eyebrow', 'Avis Clients' );
  $testi_title   = velure3_opt( 'section_title_testimonials', 'Ce Que Disent Nos Clients' );
  $testi_bg      = velure3_bg_class( velure3_opt( 'testi_bg_style', 'base' ) );
?>
<section class="velure-section <?php echo esc_attr( $testi_bg ); ?>">
  <div class="velure-container">
    <div class="velure-section-heading velure-animate">
      <?php if ( $testi_eyebrow ) : ?>
        <span class="velure-eyebrow"><?php echo esc_html( $testi_eyebrow ); ?></span>
      <?php endif; ?>
      <h2><?php echo esc_html( $testi_title ); ?></h2>
    </div>

    <div class="velure-testimonials-grid velure-animate">
      <?php foreach ( $testimonials as $t ) : ?>
      <div class="velure-testimonial-card">
        <div class="velure-testimonial-stars">
          <?php for ( $s = 0; $s < $t['stars']; $s++ ) echo velure3_star_svg(); ?>
        </div>
        <p class="velure-testimonial-text">"<?php echo esc_html( $t['text'] ); ?>"</p>
        <div class="velure-testimonial-author"><?php echo esc_html( $t['author'] ); ?></div>
        <?php if ( $t['role'] ) : ?>
        <div class="velure-testimonial-role"><?php echo esc_html( $t['role'] ); ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<?php
/* ═══════════════════════════════════════════════════
   8. BLOG
   ═══════════════════════════════════════════════════ */
if ( $visibility['blog'] ) :
  $blog_count   = absint( velure3_opt( 'blog_posts_count', 3 ) );
  $blog_posts   = velure3_get_blog_posts( $blog_count );
  $blog_eyebrow = velure3_opt( 'blog_eyebrow', 'Actualites' );
  $blog_title   = velure3_opt( 'section_title_blog', 'Le Journal' );
  $blog_cta_txt = velure3_opt( 'blog_cta_text', 'Voir tous les articles' );
  $blog_cta_url = velure3_opt( 'blog_cta_link', '/blog/' );
  $blog_bg      = velure3_bg_class( velure3_opt( 'blog_bg_style', 'muted' ) );
?>
<section class="velure-section <?php echo esc_attr( $blog_bg ); ?>">
  <div class="velure-container">
    <div class="velure-section-heading velure-animate">
      <?php if ( $blog_eyebrow ) : ?>
        <span class="velure-eyebrow"><?php echo esc_html( $blog_eyebrow ); ?></span>
      <?php endif; ?>
      <h2><?php echo esc_html( $blog_title ); ?></h2>
    </div>

    <div class="velure-grid velure-grid-3 velure-animate">
      <?php foreach ( $blog_posts as $post ) : ?>
      <a href="<?php echo esc_url( $post['link'] ); ?>" class="velure-blog-card">
        <?php if ( ! empty( $post['image'] ) ) : ?>
        <div class="velure-blog-card-img">
          <img src="<?php echo esc_url( $post['image'] ); ?>" alt="<?php echo esc_attr( $post['title'] ); ?>" loading="lazy">
        </div>
        <?php endif; ?>
        <?php if ( $post['category'] ) : ?>
        <div class="velure-blog-card-meta"><?php echo esc_html( $post['category'] ); ?></div>
        <?php endif; ?>
        <h3 class="velure-blog-card-title"><?php echo esc_html( $post['title'] ); ?></h3>
        <?php if ( $post['excerpt'] ) : ?>
        <p class="velure-blog-card-excerpt"><?php echo esc_html( $post['excerpt'] ); ?></p>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>

    <?php if ( $blog_cta_txt ) : ?>
    <div style="text-align:center;margin-top:2.5rem;" class="velure-animate">
      <a href="<?php echo esc_url( $blog_cta_url ); ?>" class="velure-btn velure-btn-outline"><?php echo esc_html( $blog_cta_txt ); ?></a>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>


<?php
/* ═══════════════════════════════════════════════════
   9. INSTAGRAM FEED
   ═══════════════════════════════════════════════════ */
if ( $visibility['instagram'] ) :
  $ig_items  = velure3_get_instagram_items();
  $ig_handle = velure3_opt( 'instagram_handle', '@velure.paris' );
  $ig_eyebrow = velure3_opt( 'ig_eyebrow', 'Suivez-nous' );
?>
<section class="velure-section">
  <div class="velure-container">
    <div class="velure-section-heading velure-animate">
      <?php if ( $ig_eyebrow ) : ?>
        <span class="velure-eyebrow"><?php echo esc_html( $ig_eyebrow ); ?></span>
      <?php endif; ?>
      <h2><?php echo esc_html( $ig_handle ); ?></h2>
    </div>
  </div>

  <div class="velure-instagram-grid velure-animate">
    <?php foreach ( $ig_items as $item ) : ?>
    <a href="<?php echo esc_url( $item['link'] ); ?>" class="velure-instagram-item" target="_blank" rel="noopener">
      <?php if ( ! empty( $item['image'] ) ) : ?>
      <img src="<?php echo esc_url( $item['image'] ); ?>" alt="Instagram" loading="lazy">
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>