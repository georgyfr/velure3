<?php
/**
 * Velure3 — Front Page (100% Dynamic)
 * Every section: visibility toggle, ACF-driven text, configurable layout.
 *
 * @package Velure3
 * @version 1.0.0
 */

get_header();

/* ── Section Visibility ── */
$visibility = velure3_get_section_visibility();

/* ── Hero ── */
$hero          = velure3_get_hero_data();
$height_class  = 'v-hero-' . $hero['height'];
$svg_allowed   = velure3_svg_allowed();

/* ── Features ── */
$features = velure3_get_features();

/* ── Categories ── */
$categories      = velure3_get_product_categories();
$cat_eyebrow     = velure3_opt( 'cat_eyebrow', 'Collections' );
$cat_title       = velure3_opt( 'section_title_categories', 'Explorer par Univers' );
$cat_desc        = velure3_opt( 'cat_description', 'Explorez nos univers et trouvez la piece qui vous correspond.' );
$cat_cta_text    = velure3_opt( 'cat_cta_text', 'Toutes les categories' );
$cat_cta_link    = velure3_opt( 'cat_cta_link', '/boutique/' );
$cat_bg          = velure3_bg_class( velure3_opt( 'cat_bg_style', 'base' ) );

/* ── Products ── */
$products        = velure3_get_featured_products();
$prod_eyebrow    = velure3_opt( 'prod_eyebrow', 'Selection' );
$prod_title      = velure3_opt( 'section_title_products', 'Pieces Vedettes' );
$prod_desc       = velure3_opt( 'prod_description', 'Nos pieces les plus appreciees, choisies pour vous.' );
$prod_cta_text   = velure3_opt( 'prod_cta_text', 'Voir toute la boutique' );
$prod_cta_link   = velure3_opt( 'prod_cta_link', '/boutique/' );
$prod_columns    = velure3_opt( 'prod_columns', '4' );
$prod_bg         = velure3_bg_class( velure3_opt( 'prod_bg_style', 'soft' ) );

/* ── Split Banner ── */
$split_banner    = velure3_get_split_banner();

/* ── Marquee ── */
$brands          = velure3_get_brands();
$marquee_speed   = absint( velure3_opt( 'marquee_speed', 25 ) );

/* ── Testimonials ── */
$testi_count     = absint( velure3_opt( 'testimonials_count', 3 ) );
$testimonials    = velure3_get_testimonials( $testi_count );
$testi_eyebrow   = velure3_opt( 'testi_eyebrow', 'Avis Clients' );
$testi_title     = velure3_opt( 'section_title_testimonials', 'Ce Que Disent Nos Clients' );
$testi_desc      = velure3_opt( 'testi_description', '' );
$testi_bg        = velure3_bg_class( velure3_opt( 'testi_bg_style', 'base' ) );

/* ── Blog ── */
$blog_count      = absint( velure3_opt( 'blog_posts_count', 3 ) );
$blog_posts      = velure3_get_blog_posts( $blog_count );
$blog_eyebrow    = velure3_opt( 'blog_eyebrow', 'Actualites' );
$blog_title      = velure3_opt( 'section_title_blog', 'Le Journal' );
$blog_desc       = velure3_opt( 'blog_description', '' );
$blog_cta_text   = velure3_opt( 'blog_cta_text', 'Voir tous les articles' );
$blog_cta_link   = velure3_opt( 'blog_cta_link', '/blog/' );
$blog_bg         = velure3_bg_class( velure3_opt( 'blog_bg_style', 'muted' ) );

/* ── Instagram ── */
$ig_items        = velure3_get_instagram_items();
$ig_handle       = velure3_opt( 'instagram_handle', '@velure.paris' );
$ig_url          = velure3_opt( 'instagram_url', 'https://instagram.com/' );
$ig_eyebrow      = velure3_opt( 'ig_eyebrow', 'Suivez-nous' );

?>


<!-- ═══════════════════════════════════════════════
     1. HERO SLIDER
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['hero'] ) : ?>
<section class="v-hero-slider <?php echo esc_attr( $height_class ); ?>" aria-label="Slider principal">
  <div class="v-hero-main">
    <?php $i = 0; foreach ( $hero['slides'] as $slide ) : ?>
      <div class="v-hero-slide<?php echo 0 === $i ? ' v-hero-slide--active' : ''; ?>">
        <div class="v-hero-slide-bg" style="background-image:url('<?php echo esc_url( $slide['image'] ); ?>');"></div>
        <div class="v-hero-slide-overlay"></div>
        <div class="v-hero-slide-content">
          <span class="velure-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></span>
          <h2><?php echo wp_kses_post( $slide['title'] ); ?></h2>
          <?php if ( ! empty( $slide['subtitle'] ) ) : ?>
            <p class="v-hero-subtitle"><?php echo esc_html( $slide['subtitle'] ); ?></p>
          <?php endif; ?>
          <?php if ( ! empty( $slide['cta_text'] ) ) :
            $btn_cls = 'velure-btn velure-btn-' . esc_attr( $slide['cta_style'] );
            if ( 'outline-light' === $slide['cta_style'] ) $btn_cls = 'velure-btn velure-btn-outline-light';
          ?>
            <a href="<?php echo esc_url( $slide['cta_link'] ); ?>" class="<?php echo $btn_cls; ?>"><?php echo esc_html( $slide['cta_text'] ); ?></a>
          <?php endif; ?>
        </div>
      </div>
    <?php $i++; endforeach; ?>

    <?php if ( count( $hero['slides'] ) > 1 ) : ?>
      <div class="v-hero-dots">
        <?php $i = 0; foreach ( $hero['slides'] as $slide ) : ?>
          <button class="v-hero-dot<?php echo 0 === $i ? ' v-hero-dot--active' : ''; ?>" data-goto="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
        <?php $i++; endforeach; ?>
      </div>
      <div class="v-hero-progress">
        <div class="v-hero-progress-bar"></div>
      </div>
    <?php endif; ?>
  </div>

  <?php if ( $hero['show_side'] && ! empty( $hero['side_blocks'] ) ) : ?>
    <div class="v-hero-side">
      <?php $bs = $hero['side_blocks']['bestseller']; ?>
      <a href="<?php echo esc_url( $bs['cta_link'] ); ?>" class="v-hero-side-card v-hero-side-card--bs">
        <div class="v-hero-side-card-img" style="background-image:url('<?php echo esc_url( $bs['image'] ); ?>');"></div>
        <div class="v-hero-side-card-body">
          <span class="velure-eyebrow" style="margin-bottom:0.5rem;"><?php echo esc_html( $bs['label'] ); ?></span>
          <h4 class="v-hero-side-card-title"><?php echo esc_html( $bs['title'] ); ?></h4>
          <span class="v-hero-side-card-price"><?php echo esc_html( $bs['price'] ); ?></span>
          <?php if ( ! empty( $bs['cta_text'] ) ) : ?>
            <span class="v-hero-side-card-cta"><?php echo esc_html( $bs['cta_text'] ); ?> &rarr;</span>
          <?php endif; ?>
        </div>
      </a>
      <?php $cat = $hero['side_blocks']['category']; ?>
      <a href="<?php echo esc_url( $cat['cta_link'] ); ?>" class="v-hero-side-card v-hero-side-card--cat">
        <div class="v-hero-side-card-img" style="background-image:url('<?php echo esc_url( $cat['image'] ); ?>');"></div>
        <div class="v-hero-side-card-body">
          <span class="velure-eyebrow" style="margin-bottom:0.5rem;"><?php echo esc_html( $cat['label'] ); ?></span>
          <h4 class="v-hero-side-card-title"><?php echo esc_html( $cat['title'] ); ?></h4>
        </div>
      </a>
    </div>
  <?php endif; ?>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     2. FEATURES / TRUST BAR
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['features'] && ! empty( $features ) ) : ?>
<section class="velure-section velure-features velure-animate" aria-label="Engagements">
  <div class="velure-container">
    <div class="velure-features-grid">
      <?php foreach ( $features as $feat ) : ?>
        <div class="velure-feature-item">
          <div class="velure-feature-icon"><?php echo wp_kses( $feat['icon'], $svg_allowed ); ?></div>
          <span class="velure-feature-title"><?php echo esc_html( $feat['title'] ); ?></span>
          <?php if ( ! empty( $feat['desc'] ) ) : ?>
            <span class="velure-feature-desc"><?php echo esc_html( $feat['desc'] ); ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     3. CATEGORY CAROUSEL
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['categories'] && ! empty( $categories ) ) : ?>
<section class="velure-section velure-categories velure-animate <?php echo esc_attr( $cat_bg ); ?>" aria-label="Categories">
  <div class="velure-container">
    <div class="velure-section-heading">
      <?php if ( ! empty( $cat_eyebrow ) ) : ?><span class="velure-eyebrow"><?php echo esc_html( $cat_eyebrow ); ?></span><?php endif; ?>
      <h2><?php echo esc_html( $cat_title ); ?></h2>
      <?php if ( ! empty( $cat_desc ) ) : ?><p><?php echo esc_html( $cat_desc ); ?></p><?php endif; ?>
    </div>
  </div>

  <div class="velure-container velure-carousel">
    <button class="velure-carousel-arrow velure-carousel-arrow--prev" aria-label="Precedent">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="velure-carousel-track">
      <?php foreach ( $categories as $cat ) : ?>
        <a href="<?php echo esc_url( $cat['link'] ); ?>" class="velure-category-card">
          <div class="velure-category-card-img">
            <img src="<?php echo esc_url( $cat['image'] ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>" loading="lazy" />
          </div>
          <span class="velure-category-card-name"><?php echo esc_html( $cat['name'] ); ?></span>
          <?php if ( $cat['count'] > 0 ) : ?>
            <span class="velure-category-card-count"><?php echo $cat['count']; ?> articles</span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
    <button class="velure-carousel-arrow velure-carousel-arrow--next" aria-label="Suivant">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
  </div>

  <?php if ( ! empty( $cat_cta_text ) ) : ?>
    <div class="velure-container" style="text-align:center;margin-top:2rem;">
      <a href="<?php echo esc_url( $cat_cta_link ); ?>" class="velure-btn velure-btn-outline"><?php echo esc_html( $cat_cta_text ); ?></a>
    </div>
  <?php endif; ?>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     4. FEATURED PRODUCTS
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['products'] && ! empty( $products ) ) : ?>
<section class="velure-section velure-animate <?php echo esc_attr( $prod_bg ); ?>" aria-label="Produits vedettes">
  <div class="velure-container">
    <div class="velure-section-heading">
      <?php if ( ! empty( $prod_eyebrow ) ) : ?><span class="velure-eyebrow"><?php echo esc_html( $prod_eyebrow ); ?></span><?php endif; ?>
      <h2><?php echo esc_html( $prod_title ); ?></h2>
      <?php if ( ! empty( $prod_desc ) ) : ?><p><?php echo esc_html( $prod_desc ); ?></p><?php endif; ?>
    </div>

    <div class="velure-grid velure-grid-<?php echo esc_attr( $prod_columns ); ?>">
      <?php foreach ( $products as $product ) : ?>
        <a href="<?php echo esc_url( $product['link'] ); ?>" class="velure-product-card">
          <div class="velure-product-card-img">
            <?php if ( ! empty( $product['image'] ) ) : ?>
              <img src="<?php echo esc_url( $product['image'] ); ?>" alt="<?php echo esc_attr( $product['name'] ); ?>" loading="lazy" />
            <?php endif; ?>
            <?php echo $product['badge']; ?>
          </div>
          <span class="velure-product-card-name"><?php echo esc_html( $product['name'] ); ?></span>
          <span class="velure-product-card-price"><?php echo $product['price_html']; ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ( ! empty( $prod_cta_text ) ) : ?>
      <div style="text-align:center;margin-top:3rem;">
        <a href="<?php echo esc_url( $prod_cta_link ); ?>" class="velure-btn velure-btn-outline"><?php echo esc_html( $prod_cta_text ); ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     5. SPLIT BANNER
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['split_banner'] ) : ?>
<section class="velure-split-banner velure-animate" aria-label="Banniere">
  <?php foreach ( array( 'left', 'right' ) as $side ) :
    $data      = $split_banner[ $side ];
    $side_cls  = 'velure-split-side--' . esc_attr( $data['style'] );
    $btn_class = 'velure-btn velure-btn-' . esc_attr( $data['cta_style'] );
  ?>
    <div class="velure-split-side <?php echo $side_cls; ?>">
      <div class="velure-split-side-bg" style="background-image:url('<?php echo esc_url( $data['image'] ); ?>');"></div>
      <div class="velure-split-side-content">
        <span class="velure-eyebrow"><?php echo esc_html( $data['eyebrow'] ); ?></span>
        <h2><?php echo esc_html( $data['title'] ); ?></h2>
        <p><?php echo esc_html( $data['desc'] ); ?></p>
        <?php if ( ! empty( $data['cta_text'] ) ) : ?>
          <a href="<?php echo esc_url( $data['cta_link'] ); ?>" class="<?php echo $btn_class; ?>"><?php echo esc_html( $data['cta_text'] ); ?></a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     6. BRAND MARQUEE
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['marquee'] && ! empty( $brands ) ) : ?>
<section class="velure-marquee" aria-label="Marques" data-speed="<?php echo esc_attr( $marquee_speed ); ?>">
  <div class="velure-marquee-track" style="animation-duration:<?php echo esc_attr( $marquee_speed ); ?>s;">
    <?php $all_brands = array_merge( $brands, $brands ); ?>
    <?php foreach ( $all_brands as $brand ) : ?>
      <span class="velure-marquee-item"><?php echo esc_html( $brand ); ?></span>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     7. TESTIMONIALS
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['testimonials'] && ! empty( $testimonials ) ) : ?>
<section class="velure-section velure-animate <?php echo esc_attr( $testi_bg ); ?>" aria-label="Temoignages">
  <div class="velure-container">
    <div class="velure-section-heading">
      <?php if ( ! empty( $testi_eyebrow ) ) : ?><span class="velure-eyebrow"><?php echo esc_html( $testi_eyebrow ); ?></span><?php endif; ?>
      <h2><?php echo esc_html( $testi_title ); ?></h2>
      <?php if ( ! empty( $testi_desc ) ) : ?><p><?php echo esc_html( $testi_desc ); ?></p><?php endif; ?>
    </div>

    <div class="velure-testimonials-grid">
      <?php foreach ( $testimonials as $t ) : ?>
        <div class="velure-testimonial-card">
          <div class="velure-testimonial-stars">
            <?php for ( $s = 0; $s < $t['stars']; $s++ ) echo velure3_star_svg(); ?>
          </div>
          <p class="velure-testimonial-text"><?php echo esc_html( $t['text'] ); ?></p>
          <span class="velure-testimonial-author"><?php echo esc_html( $t['author'] ); ?></span>
          <?php if ( ! empty( $t['role'] ) ) : ?>
            <span class="velure-testimonial-role"><?php echo esc_html( $t['role'] ); ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     8. BLOG
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['blog'] && ! empty( $blog_posts ) ) : ?>
<section class="velure-section velure-animate <?php echo esc_attr( $blog_bg ); ?>" aria-label="Blog">
  <div class="velure-container">
    <div class="velure-section-heading">
      <?php if ( ! empty( $blog_eyebrow ) ) : ?><span class="velure-eyebrow"><?php echo esc_html( $blog_eyebrow ); ?></span><?php endif; ?>
      <h2><?php echo esc_html( $blog_title ); ?></h2>
      <?php if ( ! empty( $blog_desc ) ) : ?><p><?php echo esc_html( $blog_desc ); ?></p><?php endif; ?>
    </div>

    <div class="velure-grid velure-grid-3">
      <?php foreach ( $blog_posts as $post ) : ?>
        <a href="<?php echo esc_url( $post['link'] ); ?>" class="velure-blog-card">
          <div class="velure-blog-card-img">
            <?php if ( ! empty( $post['image'] ) ) : ?>
              <img src="<?php echo esc_url( $post['image'] ); ?>" alt="<?php echo esc_attr( $post['title'] ); ?>" loading="lazy" />
            <?php endif; ?>
          </div>
          <?php if ( ! empty( $post['category'] ) ) : ?>
            <span class="velure-blog-card-meta"><?php echo esc_html( $post['category'] ); ?> &bull; <?php echo esc_html( $post['date'] ); ?></span>
          <?php endif; ?>
          <h3 class="velure-blog-card-title"><?php echo esc_html( $post['title'] ); ?></h3>
          <p class="velure-blog-card-excerpt"><?php echo esc_html( $post['excerpt'] ); ?></p>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ( ! empty( $blog_cta_text ) ) : ?>
      <div style="text-align:center;margin-top:3rem;">
        <a href="<?php echo esc_url( $blog_cta_link ); ?>" class="velure-btn velure-btn-outline"><?php echo esc_html( $blog_cta_text ); ?></a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════
     9. INSTAGRAM FEED
     ═══════════════════════════════════════════════ -->
<?php if ( $visibility['instagram'] && ! empty( $ig_items ) ) : ?>
<section class="velure-section velure-animate" aria-label="Instagram" style="padding-bottom:0;">
  <div class="velure-container" style="text-align:center;margin-bottom:2rem;">
    <?php if ( ! empty( $ig_eyebrow ) ) : ?><span class="velure-eyebrow"><?php echo esc_html( $ig_eyebrow ); ?></span><?php endif; ?>
    <h2 style="font-family:var(--v-font-serif);font-size:1.5rem;margin-top:0.5rem;">
      <a href="<?php echo esc_url( $ig_url ); ?>" style="color:var(--v-color-contrast);"><?php echo esc_html( $ig_handle ); ?></a>
    </h2>
  </div>
  <div class="velure-instagram-grid">
    <?php foreach ( $ig_items as $ig ) : ?>
      <a href="<?php echo esc_url( $ig['link'] ); ?>" class="velure-instagram-item" target="_blank" rel="noopener">
        <img src="<?php echo esc_url( $ig['image'] ); ?>" alt="Instagram" loading="lazy" />
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>