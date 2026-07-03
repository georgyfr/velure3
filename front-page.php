<?php
/**
 * Velure3 — Front Page
 * Built from scratch as a classic PHP template.
 * No FSE. No block templates. Pure PHP.
 *
 * Sections:
 *  1. Hero Slider
 *  2. Features / Trust Bar
 *  3. Category Carousel
 *  4. Featured Products
 *  5. Split Banner
 *  6. Brand Marquee
 *  7. Testimonials
 *  8. Blog
 *  9. Instagram Feed
 *
 * @package Velure3
 * @version 1.0.0
 */

get_header();

/* ── Data ── */
$hero_slides    = velure3_get_hero_slides();
$features       = velure3_get_features();
$categories     = velure3_get_product_categories();
$product_count  = absint( velure3_opt( 'featured_products_count', 8 ) );
$products       = velure3_get_featured_products( $product_count );
$split_banner   = velure3_get_split_banner();
$brands         = velure3_get_brands();
$testi_count    = absint( velure3_opt( 'testimonials_count', 3 ) );
$testimonials   = velure3_get_testimonials( $testi_count );
$blog_count     = absint( velure3_opt( 'blog_posts_count', 3 ) );
$blog_posts     = velure3_get_blog_posts( $blog_count );
$ig_items       = velure3_get_instagram_items();
$ig_handle      = velure3_opt( 'instagram_handle', '@velure.paris' );

$cat_title      = velure3_opt( 'section_title_categories', 'Explorer par Univers' );
$prod_title     = velure3_opt( 'section_title_products', 'Pieces Vedettes' );
$testi_title    = velure3_opt( 'section_title_testimonials', 'Ce Que Disent Nos Clients' );
$blog_title     = velure3_opt( 'section_title_blog', 'Le Journal' );

?>

<!-- ═══════════════════════════════════════════════
     1. HERO SLIDER
     ═══════════════════════════════════════════════ -->
<section class="v-hero-slider" aria-label="Slider principal">
  <?php $i = 0; foreach ( $hero_slides as $slide ) : ?>
    <div class="v-hero-slide<?php echo 0 === $i ? ' v-hero-slide--active' : ''; ?>">
      <div class="v-hero-slide-bg" style="background-image:url('<?php echo esc_url( $slide['image'] ); ?>');"></div>
      <div class="v-hero-slide-overlay"></div>
      <div class="v-hero-slide-content">
        <span class="velure-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></span>
        <h2><?php echo wp_kses_post( $slide['title'] ); ?></h2>
        <?php if ( ! empty( $slide['subtitle'] ) ) : ?>
          <p class="v-hero-subtitle"><?php echo esc_html( $slide['subtitle'] ); ?></p>
        <?php endif; ?>
        <?php if ( ! empty( $slide['cta_text'] ) ) : ?>
          <a href="<?php echo esc_url( $slide['cta_link'] ); ?>" class="velure-btn velure-btn-primary"><?php echo esc_html( $slide['cta_text'] ); ?></a>
        <?php endif; ?>
      </div>
    </div>
  <?php $i++; endforeach; ?>

  <?php if ( count( $hero_slides ) > 1 ) : ?>
    <div class="v-hero-dots">
      <?php $i = 0; foreach ( $hero_slides as $slide ) : ?>
        <button class="v-hero-dot<?php echo 0 === $i ? ' v-hero-dot--active' : ''; ?>" data-goto="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
      <?php $i++; endforeach; ?>
    </div>
    <div class="v-hero-progress">
      <div class="v-hero-progress-bar"></div>
    </div>
  <?php endif; ?>
</section>


<!-- ═══════════════════════════════════════════════
     2. FEATURES / TRUST BAR
     ═══════════════════════════════════════════════ -->
<section class="velure-section velure-features velure-animate" aria-label="Engagements">
  <div class="velure-container">
    <div class="velure-features-grid">
      <?php foreach ( $features as $feat ) : ?>
        <div class="velure-feature-item">
          <div class="velure-feature-icon"><?php echo wp_kses( $feat['icon'], array( 'svg' => array( 'width','height','viewBox','fill','stroke','stroke-width','stroke-linecap','stroke-linejoin' ), 'rect' => array( 'x','y','width','height','rx','ry' ), 'polygon' => array( 'points' ), 'circle' => array( 'cx','cy','r' ), 'path' => array( 'd' ), 'polyline' => array( 'points' ) ) ); ?></div>
          <span class="velure-feature-title"><?php echo esc_html( $feat['title'] ); ?></span>
          <?php if ( ! empty( $feat['desc'] ) ) : ?>
            <span class="velure-feature-desc"><?php echo esc_html( $feat['desc'] ); ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════
     3. CATEGORY CAROUSEL
     ═══════════════════════════════════════════════ -->
<section class="velure-section velure-categories velure-animate" aria-label="Categories">
  <div class="velure-container">
    <div class="velure-section-heading">
      <span class="velure-eyebrow">Collections</span>
      <h2><?php echo esc_html( $cat_title ); ?></h2>
      <p>Explorez nos univers et trouvez la piece qui vous correspond.</p>
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
</section>


<!-- ═══════════════════════════════════════════════
     4. FEATURED PRODUCTS
     ═══════════════════════════════════════════════ -->
<section class="velure-section velure-section-soft velure-animate" aria-label="Produits vedettes">
  <div class="velure-container">
    <div class="velure-section-heading">
      <span class="velure-eyebrow">Selection</span>
      <h2><?php echo esc_html( $prod_title ); ?></h2>
      <p>Nos pieces les plus appréciees, choisies pour vous.</p>
    </div>

    <div class="velure-grid velure-grid-4">
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

    <div style="text-align:center;margin-top:3rem;">
      <a href="<?php echo esc_url( home_url( '/boutique/' ) ); ?>" class="velure-btn velure-btn-outline">Voir toute la boutique</a>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════
     5. SPLIT BANNER
     ═══════════════════════════════════════════════ -->
<section class="velure-split-banner velure-animate" aria-label="Banniere">
  <?php foreach ( array( 'left', 'right' ) as $side ) :
    $data = $split_banner[ $side ];
    $dark = ( 'left' === $side ) ? 'velure-split-side--dark' : 'velure-split-side--light';
    $btn_class = 'velure-btn velure-btn-' . esc_attr( $data['cta_style'] );
  ?>
    <div class="velure-split-side <?php echo $dark; ?>">
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


<!-- ═══════════════════════════════════════════════
     6. BRAND MARQUEE
     ═══════════════════════════════════════════════ -->
<section class="velure-marquee" aria-label="Marques">
  <div class="velure-marquee-track">
    <?php
    $all_brands = array_merge( $brands, $brands ); // duplicate for seamless loop
    foreach ( $all_brands as $brand ) :
    ?>
      <span class="velure-marquee-item"><?php echo esc_html( $brand ); ?></span>
    <?php endforeach; ?>
  </div>
</section>


<!-- ═══════════════════════════════════════════════
     7. TESTIMONIALS
     ═══════════════════════════════════════════════ -->
<section class="velure-section velure-animate" aria-label="Temoignages">
  <div class="velure-container">
    <div class="velure-section-heading">
      <span class="velure-eyebrow">Avis Clients</span>
      <h2><?php echo esc_html( $testi_title ); ?></h2>
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


<!-- ═══════════════════════════════════════════════
     8. BLOG
     ═══════════════════════════════════════════════ -->
<section class="velure-section velure-section-muted velure-animate" aria-label="Blog">
  <div class="velure-container">
    <div class="velure-section-heading">
      <span class="velure-eyebrow">Actualites</span>
      <h2><?php echo esc_html( $blog_title ); ?></h2>
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
  </div>
</section>


<!-- ═══════════════════════════════════════════════
     9. INSTAGRAM FEED
     ═══════════════════════════════════════════════ -->
<section class="velure-section velure-animate" aria-label="Instagram" style="padding-bottom:0;">
  <div class="velure-container" style="text-align:center;margin-bottom:2rem;">
    <span class="velure-eyebrow">@velure</span>
    <h2 style="font-family:var(--v-font-serif);font-size:1.5rem;margin-top:0.5rem;">
      <a href="https://instagram.com/" style="color:var(--v-color-contrast);"><?php echo esc_html( $ig_handle ); ?></a>
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

<?php get_footer(); ?>