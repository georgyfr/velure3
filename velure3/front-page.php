<?php
/**
 * Velure3 — Dynamic Front Page
 * All content is queried from the database (ACF + WooCommerce + Posts + CPT).
 * Falls back to default content when ACF/WooCommerce are not configured.
 *
 * Template Name: Accueil Dynamique
 * @package Velure3
 * @version 1.3.0
 */

// ── Data ──
$hero_slides      = velure3_get_hero_slides();
$hero_static      = velure3_get_hero_static_blocks();
$features         = velure3_get_features();
$categories       = velure3_get_product_categories( 10 );
$products_count   = (int) velure3_opt( 'featured_products_count', 8 );
$featured_products = velure3_get_featured_products( $products_count );
$split_banner     = velure3_get_split_banner();
$testimonials_count = (int) velure3_opt( 'testimonials_count', 3 );
$testimonials     = velure3_get_testimonials( $testimonials_count );
$brands           = velure3_get_brands();
$blog_count       = (int) velure3_opt( 'blog_posts_count', 3 );
$blog_posts       = velure3_get_blog_posts( $blog_count );
$instagram_items  = velure3_get_instagram_items();
$instagram_handle = velure3_opt( 'instagram_handle', '@velure.paris' );

$section_titles = array(
	'categories'   => velure3_opt( 'section_title_categories', 'Explorer par Univers' ),
	'products'     => velure3_opt( 'section_title_products', 'Pièces Vedettes' ),
	'testimonials' => velure3_opt( 'section_title_testimonials', 'Ce Que Disent Nos Clients' ),
	'blog'         => velure3_opt( 'section_title_blog', 'Le Journal' ),
);

$star_svg = velure3_star_svg();

get_header();
?>


<!-- ═══════════════════════════════════════════════════
     1. HERO SECTION — BANNIÈRE ASYMÉTRIQUE
     ═══════════════════════════════════════════════════ -->
<div class="hero-section">

  <!-- Colonne A : Slider Émotionnel -->
  <div class="hero-slider">
    <div class="hero-slides-track">
      <?php foreach ( $hero_slides as $i => $slide ) :
        $active = ( $i === 0 ) ? ' hero-slide--active' : '';
      ?>
      <div class="hero-slide<?php echo $active; ?>" data-slide="<?php echo $i; ?>">
        <img src="<?php echo esc_url( $slide['image'] ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $slide['title'] ) ); ?>" class="hero-slide-img" />
        <div class="hero-slide-overlay"></div>
        <div class="hero-slide-content">
          <span class="hero-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></span>
          <h1 class="hero-slide-title"><?php echo wp_kses_post( $slide['title'] ); ?></h1>
          <p class="hero-slide-subtitle"><?php echo esc_html( $slide['subtitle'] ); ?></p>
          <a href="<?php echo esc_url( $slide['cta_link'] ); ?>" class="velure-btn velure-btn-primary"><?php echo esc_html( $slide['cta_text'] ); ?></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Slider Controls -->
    <div class="hero-slider-controls">
      <?php foreach ( $hero_slides as $i => $slide ) :
        $active = ( $i === 0 ) ? ' hero-slider-dot--active' : '';
      ?>
      <button class="hero-slider-dot<?php echo $active; ?>" data-goto="<?php echo $i; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
      <?php endforeach; ?>
    </div>

    <div class="hero-slider-progress">
      <div class="hero-slider-progress-bar"></div>
    </div>
  </div>

  <!-- Colonne B : Blocs Statiques -->
  <div class="hero-static-blocks">

    <!-- Bloc B1 : Best-Seller -->
    <section class="hero-block hero-block--bestseller">
      <div class="hero-block-visual">
        <img src="<?php echo esc_url( $hero_static['bestseller']['image'] ); ?>" alt="<?php echo esc_attr( $hero_static['bestseller']['title'] ); ?>" class="hero-block-img" />
      </div>
      <div class="hero-block-content">
        <span class="hero-block-label"><?php echo esc_html( $hero_static['bestseller']['label'] ); ?></span>
        <h3 class="hero-block-title"><?php echo esc_html( $hero_static['bestseller']['title'] ); ?></h3>
        <p class="hero-block-price"><?php echo esc_html( $hero_static['bestseller']['price'] ); ?></p>
        <a href="<?php echo esc_url( $hero_static['bestseller']['cta_link'] ); ?>" class="velure-btn velure-btn-outline velure-btn-sm"><?php echo esc_html( $hero_static['bestseller']['cta_text'] ); ?></a>
      </div>
    </section>

    <!-- Bloc B2 : Catégorie -->
    <section class="hero-block hero-block--category">
      <div class="hero-block-visual">
        <img src="<?php echo esc_url( $hero_static['category']['image'] ); ?>" alt="<?php echo esc_attr( $hero_static['category']['title'] ); ?>" class="hero-block-img" />
      </div>
      <div class="hero-block-content">
        <span class="hero-block-label"><?php echo esc_html( $hero_static['category']['label'] ); ?></span>
        <h3 class="hero-block-title"><?php echo esc_html( $hero_static['category']['title'] ); ?></h3>
        <a href="<?php echo esc_url( $hero_static['category']['cta_link'] ); ?>" class="hero-block-link">TOUT PARCOURIR &rarr;</a>
      </div>
    </section>

  </div>
</div>


<!-- ═══════════════════════════════════════════════════
     2. TRUST / FEATURES BAR
     ═══════════════════════════════════════════════════ -->
<div class="velure-section">
  <div class="velure-container">
    <div class="velure-features">
      <?php foreach ( $features as $feature ) : ?>
      <div class="velure-feature">
        <div class="velure-feature-icon">
          <?php echo $feature['icon']; // SVG is already escaped by ACF or hardcoded ?>
        </div>
        <div class="velure-feature-text">
          <strong><?php echo esc_html( $feature['title'] ); ?></strong>
          <span><?php echo esc_html( $feature['desc'] ); ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════
     3. CATEGORIES CAROUSEL
     ═══════════════════════════════════════════════════ -->
<?php if ( ! empty( $categories ) ) : ?>
<div class="velure-section">
  <div class="velure-container">
    <h2 class="velure-section-heading"><?php echo esc_html( $section_titles['categories'] ); ?></h2>
    <div class="velure-carousel" id="velure-category-carousel">
      <div class="velure-carousel-track">
        <?php foreach ( $categories as $cat ) : ?>
        <a href="<?php echo esc_url( $cat['link'] ); ?>" class="velure-category-card">
          <img src="<?php echo esc_url( $cat['image'] ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>" />
          <div class="velure-category-card-overlay">
            <span class="velure-category-card-label">Collection</span>
            <h3 class="velure-category-card-title"><?php echo esc_html( $cat['name'] ); ?></h3>
            <span class="velure-category-card-count"><?php echo esc_html( $cat['count'] ); ?> pièces</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <button class="velure-carousel-arrow velure-carousel-arrow--prev" aria-label="Précédent">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <button class="velure-carousel-arrow velure-carousel-arrow--next" aria-label="Suivant">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════
     4. FEATURED PRODUCTS
     ═══════════════════════════════════════════════════ -->
<?php if ( ! empty( $featured_products ) ) : ?>
<div class="velure-section velure-section-soft">
  <div class="velure-container">
    <h2 class="velure-section-heading"><?php echo esc_html( $section_titles['products'] ); ?></h2>
    <div class="velure-grid velure-grid-4">
      <?php foreach ( $featured_products as $prod ) : ?>
      <div class="velure-product-card">
        <div class="velure-product-card-image">
          <?php if ( $prod['image'] ) : ?>
          <img src="<?php echo esc_url( $prod['image'] ); ?>" alt="<?php echo esc_attr( $prod['name'] ); ?>" loading="lazy" />
          <?php endif; ?>
          <?php echo $prod['badge']; ?>
          <div class="velure-product-card-actions">
            <button class="velure-product-card-action" aria-label="Ajouter aux favoris">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </button>
            <button class="velure-product-card-action" aria-label="Aperçu rapide">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            <a href="<?php echo esc_url( $prod['link'] ); ?>" class="velure-product-card-action" aria-label="Voir le produit">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </a>
          </div>
        </div>
        <div class="velure-product-card-info">
          <h3><?php echo esc_html( $prod['name'] ); ?></h3>
          <div class="velure-product-card-price"><?php echo $prod['price_html']; // Already sanitized by WC ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════
     5. SPLIT BANNER
     ═══════════════════════════════════════════════════ -->
<div class="velure-section">
  <div class="velure-container">
    <div class="velure-grid velure-grid-2">
      <?php
      // Render one side of the split banner
      function velure3_render_banner_side( $side, $index ) {
        $btn_class = 'velure-btn velure-btn-' . ( $side['cta_style'] ?: 'primary' );
        $extra_style = ( $side['cta_style'] === 'outline' ) ? 'border-color:#fff;color:#fff;' : '';
      ?>
      <div style="position:relative;overflow:hidden;min-height:320px;display:flex;align-items:flex-end;">
        <img src="<?php echo esc_url( $side['image'] ); ?>" alt="<?php echo esc_attr( $side['title'] ); ?>" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;" />
        <div style="position:relative;z-index:1;padding:40px;width:100%;background:linear-gradient(to top,rgba(0,0,0,0.6) 0%,transparent 100%);">
          <span class="velure-eyebrow" style="color:rgba(255,255,255,0.8);"><?php echo esc_html( $side['eyebrow'] ); ?></span>
          <h2 style="color:#fff;font-size:2rem;margin:8px 0 12px;font-weight:300;letter-spacing:-0.01em;"><?php echo esc_html( $side['title'] ); ?></h2>
          <p style="color:rgba(255,255,255,0.75);margin-bottom:20px;font-size:0.95rem;line-height:1.6;"><?php echo esc_html( $side['desc'] ); ?></p>
          <a href="<?php echo esc_url( $side['cta_link'] ); ?>" class="<?php echo esc_attr( $btn_class ); ?>" <?php if ( $extra_style ) echo 'style="' . esc_attr( $extra_style ) . '"'; ?>><?php echo esc_html( $side['cta_text'] ); ?></a>
        </div>
      </div>
      <?php
      }

      velure3_render_banner_side( $split_banner['left'], 0 );
      velure3_render_banner_side( $split_banner['right'], 1 );
      ?>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════════════
     6. TESTIMONIALS
     ═══════════════════════════════════════════════════ -->
<?php if ( ! empty( $testimonials ) ) : ?>
<div class="velure-section velure-section-soft">
  <div class="velure-container">
    <h2 class="velure-section-heading"><?php echo esc_html( $section_titles['testimonials'] ); ?></h2>
    <div class="velure-grid velure-grid-3">
      <?php foreach ( $testimonials as $t ) : ?>
      <div class="velure-testimonial">
        <div class="velure-testimonial-stars">
          <?php for ( $s = 0; $s < $t['stars']; $s++ ) echo $star_svg; ?>
        </div>
        <p class="velure-testimonial-text">&laquo; <?php echo esc_html( $t['text'] ); ?> &raquo;</p>
        <p class="velure-testimonial-author"><?php echo esc_html( $t['author'] ); ?></p>
        <?php if ( $t['role'] ) : ?>
        <p class="velure-testimonial-role"><?php echo esc_html( $t['role'] ); ?></p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════
     7. BRAND MARQUEE
     ═══════════════════════════════════════════════════ -->
<?php if ( ! empty( $brands ) ) : ?>
<div class="velure-section velure-section-dark">
  <div class="velure-marquee">
    <div class="velure-marquee-track">
      <?php foreach ( $brands as $brand ) : ?>
      <span class="velure-marquee-item"><?php echo esc_html( $brand ); ?></span>
      <?php endforeach; ?>
      <!-- Duplicate for infinite scroll -->
      <?php foreach ( $brands as $brand ) : ?>
      <span class="velure-marquee-item"><?php echo esc_html( $brand ); ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════
     8. BLOG / JOURNAL
     ═══════════════════════════════════════════════════ -->
<?php if ( ! empty( $blog_posts ) ) : ?>
<div class="velure-section">
  <div class="velure-container">
    <h2 class="velure-section-heading"><?php echo esc_html( $section_titles['blog'] ); ?></h2>
    <div class="velure-grid velure-grid-3">
      <?php foreach ( $blog_posts as $post ) : ?>
      <article class="velure-blog-card">
        <div class="velure-blog-card-image">
          <?php if ( $post['image'] ) : ?>
          <img src="<?php echo esc_url( $post['image'] ); ?>" alt="<?php echo esc_attr( $post['title'] ); ?>" loading="lazy" />
          <?php else : ?>
          <div style="background:var(--v-color-soft);width:100%;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;">
            <span style="color:#999;font-size:0.85rem;">Pas d'image</span>
          </div>
          <?php endif; ?>
        </div>
        <div class="velure-blog-card-meta">
          <time datetime="<?php echo esc_attr( $post['date'] ); ?>"><?php echo esc_html( $post['date'] ); ?></time>
          <?php if ( $post['category'] ) : ?>
          <span style="margin:0 6px;opacity:0.4;">&bull;</span>
          <span><?php echo esc_html( $post['category'] ); ?></span>
          <?php endif; ?>
        </div>
        <h3 class="velure-blog-card-title"><?php echo esc_html( $post['title'] ); ?></h3>
        <p class="velure-blog-card-excerpt"><?php echo esc_html( $post['excerpt'] ); ?></p>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════
     9. INSTAGRAM FEED
     ═══════════════════════════════════════════════════ -->
<?php if ( ! empty( $instagram_items ) ) : ?>
<div class="velure-section velure-section-soft">
  <div class="velure-container">
    <h2 class="velure-section-heading"><?php echo esc_html( $instagram_handle ); ?></h2>
    <div class="velure-instagram-grid">
      <?php foreach ( $instagram_items as $item ) : ?>
      <a href="<?php echo esc_url( $item['link'] ); ?>" class="velure-instagram-item" target="_blank" rel="noopener">
        <img src="<?php echo esc_url( $item['image'] ); ?>" alt="Instagram - Velure" loading="lazy" />
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>


<?php
get_footer();