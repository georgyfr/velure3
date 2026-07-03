/**
 * Velure3 Theme JavaScript
 * @version 1.0.0
 */

(function() {
  'use strict';

  /* ── Sticky Header ── */
  var navbar = document.getElementById('velure-navbar');
  if (navbar) {
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 50) {
        navbar.classList.add('velure-scrolled');
      } else {
        navbar.classList.remove('velure-scrolled');
      }
    }, { passive: true });
  }

  /* ── Search Overlay ── */
  var searchToggle = document.getElementById('velure-search-toggle');
  var searchOverlay = document.getElementById('velure-search-overlay');
  var searchClose = document.getElementById('velure-search-close');
  var searchInput = document.getElementById('velure-search-input');

  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', function() {
      searchOverlay.classList.add('velure-active');
      document.body.classList.add('velure-search-open');
      setTimeout(function() { if (searchInput) searchInput.focus(); }, 100);
    });
    var closeSearch = function() {
      searchOverlay.classList.remove('velure-active');
      document.body.classList.remove('velure-search-open');
    };
    if (searchClose) searchClose.addEventListener('click', closeSearch);
    searchOverlay.addEventListener('click', function(e) {
      if (e.target === searchOverlay) closeSearch();
    });
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && searchOverlay.classList.contains('velure-active')) closeSearch();
    });
  }

  /* ── Mobile Menu ── */
  var menuToggle = document.getElementById('velure-menu-toggle');
  var mobileMenu = document.getElementById('velure-mobile-menu');
  var mobileOverlay = document.getElementById('velure-mobile-overlay');
  var mobileClose = document.getElementById('velure-mobile-close');

  if (menuToggle && mobileMenu) {
    var openMobile = function() {
      mobileMenu.classList.add('velure-active');
      mobileOverlay.classList.add('velure-active');
      document.body.classList.add('velure-menu-open');
    };
    var closeMobile = function() {
      mobileMenu.classList.remove('velure-active');
      mobileOverlay.classList.remove('velure-active');
      document.body.classList.remove('velure-menu-open');
    };
    menuToggle.addEventListener('click', openMobile);
    if (mobileClose) mobileClose.addEventListener('click', closeMobile);
    if (mobileOverlay) mobileOverlay.addEventListener('click', closeMobile);
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && mobileMenu.classList.contains('velure-active')) closeMobile();
    });
  }

  /* ── Cart Count (WooCommerce) ── */
  function updateCartCount() {
    var cartCount = document.getElementById('velure-cart-count');
    if (!cartCount) return;
    if (typeof velure3_ajax !== 'undefined') {
      fetch(velure3_ajax.ajax_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=velure_get_cart_count&nonce=' + velure3_ajax.nonce
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data.count !== undefined) {
          cartCount.textContent = data.count;
          cartCount.style.display = data.count > 0 ? 'flex' : 'none';
        }
      })
      .catch(function() {});
    }
  }
  document.body.addEventListener('added_to_cart', updateCartCount);
  document.body.addEventListener('removed_from_cart', updateCartCount);
  document.body.addEventListener('wc_fragments_refreshed', updateCartCount);

  /* ── Animate on Scroll ── */
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('velure-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

  document.querySelectorAll('.velure-animate').forEach(function(el) {
    observer.observe(el);
  });

  /* ── Hero Slider ── */
  (function() {
    var slider = document.querySelector('.v-hero-slider');
    if (!slider) return;

    var main = slider.querySelector('.v-hero-main');
    if (!main) return;

    var slides = main.querySelectorAll('.v-hero-slide');
    var dots = main.querySelectorAll('.v-hero-dot');
    var progressBar = main.querySelector('.v-hero-progress-bar');
    if (slides.length < 2) {
      if (slides[0]) slides[0].classList.add('v-hero-slide--active');
      if (dots[0]) dots[0].classList.add('v-hero-dot--active');
      return;
    }

    var current = 0;
    var INTERVAL = 5000;
    var timer = null;

    function goTo(idx) {
      if (idx === current) return;
      slides[current].classList.remove('v-hero-slide--active');
      if (dots[current]) dots[current].classList.remove('v-hero-dot--active');
      current = (idx + slides.length) % slides.length;
      slides[current].classList.add('v-hero-slide--active');
      if (dots[current]) dots[current].classList.add('v-hero-dot--active');
      resetProgress();
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function resetProgress() {
      if (!progressBar) { timer = setTimeout(next, INTERVAL); return; }
      clearTimeout(timer);
      progressBar.classList.remove('v-hero-progress-bar--running');
      progressBar.style.width = '0%';
      void progressBar.offsetWidth;
      requestAnimationFrame(function() {
        progressBar.classList.add('v-hero-progress-bar--running');
      });
      timer = setTimeout(next, INTERVAL);
    }

    dots.forEach(function(dot) {
      dot.addEventListener('click', function() {
        var idx = parseInt(this.getAttribute('data-goto'), 10);
        if (!isNaN(idx)) goTo(idx);
      });
    });

    slider.addEventListener('mouseenter', function() {
      clearTimeout(timer);
      if (progressBar) {
        var w = getComputedStyle(progressBar).width;
        progressBar.classList.remove('v-hero-progress-bar--running');
        progressBar.style.width = w;
      }
    });
    slider.addEventListener('mouseleave', function() {
      timer = setTimeout(next, INTERVAL);
    });

    var touchStartX = 0;
    slider.addEventListener('touchstart', function(e) { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
    slider.addEventListener('touchend', function(e) {
      var diff = touchStartX - e.changedTouches[0].screenX;
      if (Math.abs(diff) > 50) { diff > 0 ? next() : prev(); }
    }, { passive: true });

    resetProgress();
  })();

  /* ── Category Carousel ── */
  document.querySelectorAll('.velure-carousel').forEach(function(carousel) {
    var track = carousel.querySelector('.velure-carousel-track');
    var prevBtn = carousel.querySelector('.velure-carousel-arrow--prev');
    var nextBtn = carousel.querySelector('.velure-carousel-arrow--next');
    if (!track) return;

    var cardWidth = 0;
    function measureCard() {
      var first = track.querySelector(':scope > *');
      cardWidth = first ? first.offsetWidth + parseFloat(getComputedStyle(track).gap) : 0;
    }
    if (prevBtn) prevBtn.addEventListener('click', function() { measureCard(); track.scrollBy({ left: -cardWidth, behavior: 'smooth' }); });
    if (nextBtn) nextBtn.addEventListener('click', function() { measureCard(); track.scrollBy({ left: cardWidth, behavior: 'smooth' }); });
    window.addEventListener('resize', measureCard, { passive: true });
    measureCard();
  });

  /* ── Smooth scroll for anchor links ── */
  document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
    anchor.addEventListener('click', function(e) {
      var target = document.querySelector(this.getAttribute('href'));
      if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });

})();