/**
 * Velure3 Theme JavaScript
 * @version 1.0.0
 */

(function() {
  'use strict';

  /* ── Sticky Header Scroll Effect ── */
  const navbar = document.getElementById('velure-navbar');
  if (navbar) {
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      if (currentScroll > 50) {
        navbar.classList.add('velure-scrolled');
      } else {
        navbar.classList.remove('velure-scrolled');
      }
      lastScroll = currentScroll;
    }, { passive: true });
  }

  /* ── Search Overlay ── */
  const searchToggle = document.getElementById('velure-search-toggle');
  const searchOverlay = document.getElementById('velure-search-overlay');
  const searchClose = document.getElementById('velure-search-close');
  const searchInput = document.getElementById('velure-search-input');

  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', () => {
      searchOverlay.classList.add('velure-active');
      document.body.style.overflow = 'hidden';
      setTimeout(() => searchInput && searchInput.focus(), 100);
    });

    const closeSearch = () => {
      searchOverlay.classList.remove('velure-active');
      document.body.style.overflow = '';
    };

    if (searchClose) searchClose.addEventListener('click', closeSearch);
    searchOverlay.addEventListener('click', (e) => {
      if (e.target === searchOverlay) closeSearch();
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && searchOverlay.classList.contains('velure-active')) {
        closeSearch();
      }
    });
  }

  /* ── Mobile Menu ── */
  const menuToggle = document.getElementById('velure-menu-toggle');
  const mobileMenu = document.getElementById('velure-mobile-menu');
  const mobileOverlay = document.getElementById('velure-mobile-overlay');
  const mobileClose = document.getElementById('velure-mobile-close');

  if (menuToggle && mobileMenu) {
    const openMobile = () => {
      mobileMenu.classList.add('velure-active');
      mobileOverlay.classList.add('velure-active');
      document.body.style.overflow = 'hidden';
    };

    const closeMobile = () => {
      mobileMenu.classList.remove('velure-active');
      mobileOverlay.classList.remove('velure-active');
      document.body.style.overflow = '';
    };

    menuToggle.addEventListener('click', openMobile);
    if (mobileClose) mobileClose.addEventListener('click', closeMobile);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobile);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mobileMenu.classList.contains('velure-active')) {
        closeMobile();
      }
    });
  }

  /* ── Cart Count (WooCommerce) ── */
  function updateCartCount() {
    const cartCount = document.getElementById('velure-cart-count');
    if (!cartCount) return;
    if (typeof velure3_ajax !== 'undefined') {
      fetch(velure3_ajax.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=velure_get_cart_count&nonce=' + velure3_ajax.nonce
      })
      .then(r => r.json())
      .then(data => {
        if (data.count !== undefined) {
          cartCount.textContent = data.count;
          cartCount.style.display = data.count > 0 ? 'flex' : 'none';
        }
      })
      .catch(() => {});
    }
  }

  /* Listen for WooCommerce cart events */
  document.body.addEventListener('added_to_cart', updateCartCount);
  document.body.addEventListener('removed_from_cart', updateCartCount);
  document.body.addEventListener('wc_fragments_refreshed', updateCartCount);

  /* ── Animate on Scroll ── */
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('velure-visible');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll('.velure-animate').forEach(el => {
    observer.observe(el);
  });

  /* ── Smooth scroll for anchor links ── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  /* ══════════════════════════════════════
     HERO SLIDER — Asymmetrical Banner
     Vanilla JS, zero dependency, ~60 lines
     ══════════════════════════════════════ */
  (function initHeroSlider() {
    var slider = document.querySelector('.hero-slider');
    if (!slider) return;

    var slides     = slider.querySelectorAll('.hero-slide');
    var dots       = slider.querySelectorAll('.hero-slider-dot');
    var progressBar = slider.querySelector('.hero-slider-progress-bar');
    if (slides.length < 2) return;

    var current    = 0;
    var INTERVAL   = 5000;          // 5 s par slide
    var timer      = null;
    var animFrame  = null;

    /* Aller à un index précis */
    function goTo(idx) {
      if (idx === current) return;

      // Désactiver l'ancien
      slides[current].classList.remove('hero-slide--active');
      if (dots[current]) dots[current].classList.remove('hero-slider-dot--active');

      current = (idx + slides.length) % slides.length;

      // Activer le nouveau
      slides[current].classList.add('hero-slide--active');
      if (dots[current]) dots[current].classList.add('hero-slider-dot--active');

      // Relancer la barre de progression
      resetProgress();
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    /* Barre de progression animée */
    function resetProgress() {
      if (!progressBar) return;

      // Annuler les anciens
      cancelAnimationFrame(animFrame);
      clearTimeout(timer);

      // Reset visuel immédiat
      progressBar.classList.remove('hero-slider-progress-bar--running');
      progressBar.style.width = '0%';

      // Forcer le reflow pour que le navigateur prenne en compte le reset
      void progressBar.offsetWidth;

      // Lancer l'animation CSS
      requestAnimationFrame(function() {
        progressBar.classList.add('hero-slider-progress-bar--running');
      });

      // Programmer le passage au slide suivant
      timer = setTimeout(next, INTERVAL);
    }

    /* Clic sur les dots */
    dots.forEach(function(dot) {
      dot.addEventListener('click', function() {
        var idx = parseInt(this.getAttribute('data-goto'), 10);
        if (!isNaN(idx)) goTo(idx);
      });
    });

    /* Pause au survol, reprise à la sortie */
    slider.addEventListener('mouseenter', function() {
      clearTimeout(timer);
      cancelAnimationFrame(animFrame);
      if (progressBar) {
        var computed = getComputedStyle(progressBar).width;
        progressBar.classList.remove('hero-slider-progress-bar--running');
        progressBar.style.width = computed;
      }
    });

    slider.addEventListener('mouseleave', function() {
      // Reprendre le timer (5s à nouveau)
      timer = setTimeout(next, INTERVAL);
    });

    /* Support tactile : swipe basique */
    var touchStartX = 0;
    var touchEndX   = 0;

    slider.addEventListener('touchstart', function(e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    slider.addEventListener('touchend', function(e) {
      touchEndX = e.changedTouches[0].screenX;
      var diff = touchStartX - touchEndX;
      if (Math.abs(diff) > 50) {
        if (diff > 0) next();
        else prev();
      }
    }, { passive: true });

    /* Initialiser la barre et le timer au chargement */
    resetProgress();
  })();

})();