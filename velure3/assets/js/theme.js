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

  /* ── Inline Dynamic Search ── */
  (function() {
    var searchWrap = document.getElementById('velure-inline-search');
    if (!searchWrap) return;

    var trigger = document.getElementById('velure-search-trigger');
    var input = document.getElementById('velure-search-input-inline');
    var clearBtn = document.getElementById('velure-search-clear-inline');
    var resultsBox = document.getElementById('velure-search-results');
    var form = searchWrap.querySelector('.velure-search-form-inline');
    var isOpen = false;
    var debounceTimer = null;
    var abortController = null;

    function open() {
      searchWrap.classList.add('velure-search-open');
      isOpen = true;
      setTimeout(function() { input.focus(); }, 100);
    }

    function close() {
      searchWrap.classList.remove('velure-search-open');
      isOpen = false;
      input.value = '';
      if (clearBtn) clearBtn.style.display = 'none';
      resultsBox.style.display = 'none';
      resultsBox.innerHTML = '';
    }

    if (trigger) {
      trigger.addEventListener('click', function(e) {
        e.stopPropagation();
        if (isOpen) { close(); } else { open(); }
      });
    }

    input.addEventListener('input', function() {
      var val = this.value.trim();
      if (clearBtn) clearBtn.style.display = val.length > 0 ? 'flex' : 'none';
      clearTimeout(debounceTimer);
      var minChars = (typeof velure3_ajax !== 'undefined' && velure3_ajax.search) ? velure3_ajax.search.min_chars : 2;
      var delay = (typeof velure3_ajax !== 'undefined' && velure3_ajax.search) ? velure3_ajax.search.delay : 300;

      if (val.length < minChars) {
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
        return;
      }

      debounceTimer = setTimeout(function() { fetchResults(val); }, delay);
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', function() {
        input.value = '';
        this.style.display = 'none';
        input.focus();
        resultsBox.style.display = 'none';
        resultsBox.innerHTML = '';
      });
    }

    function fetchResults(query) {
      if (typeof velure3_ajax === 'undefined') return;

      // Cancel previous request
      if (abortController) abortController.abort();
      abortController = new (window.AbortController || window.XMLHttpRequest)();

      // Show loading
      resultsBox.style.display = 'block';
      resultsBox.innerHTML = '<div class="velure-search-loading"></div>';

      var url = velure3_ajax.ajax_url +
        '?action=velure_live_search' +
        '&q=' + encodeURIComponent(query) +
        '&nonce=' + velure3_ajax.nonce;

      fetch(url, {
        method: 'GET',
        signal: abortController.signal,
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        renderResults(data, query);
      })
      .catch(function(err) {
        if (err.name !== 'AbortError') {
          resultsBox.innerHTML = '<div class="velure-search-no-results">Une erreur est survenue.</div>';
        }
      });
    }

    function renderResults(data, query) {
      var html = '';
      var hasProducts = data.results && data.results.length > 0;
      var hasCats = data.categories && data.categories.length > 0;

      if (!hasProducts && !hasCats) {
        html = '<div class="velure-search-no-results">Aucun resultat pour &laquo; ' + escHtml(query) + ' &raquo;</div>';
        resultsBox.innerHTML = html;
        resultsBox.style.display = 'block';
        return;
      }

      if (hasCats) {
        html += '<div class="velure-search-results-group-title">Categories</div>';
        data.categories.forEach(function(cat) {
          html += '<a href="' + escAttr(cat.url) + '" class="velure-search-result-cat">';
          html += '<span class="velure-search-result-cat-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg></span>';
          html += '<span>' + escHtml(cat.title) + '</span>';
          html += '</a>';
        });
      }

      if (hasProducts) {
        html += '<div class="velure-search-results-group-title">Produits</div>';
        data.results.forEach(function(item) {
          html += '<a href="' + escAttr(item.url) + '" class="velure-search-result-item">';
          html += '<div class="velure-search-result-img">';
          if (item.image) {
            html += '<img src="' + escAttr(item.image) + '" alt="" loading="lazy">';
          }
          html += '</div>';
          html += '<div class="velure-search-result-info">';
          html += '<div class="velure-search-result-title">' + escHtml(item.title) + '</div>';
          if (item.price) {
            html += '<div class="velure-search-result-price">' + item.price + '</div>';
          }
          html += '</div>';
          html += '</a>';
        });
      }

      // View all link
      var searchUrl = (typeof velure3_ajax !== 'undefined')
        ? location.origin + '/?s=' + encodeURIComponent(query) + '&post_type=product'
        : '#';
      html += '<a href="' + searchUrl + '" class="velure-search-view-all">Voir tous les resultats</a>';

      resultsBox.innerHTML = html;
      resultsBox.style.display = 'block';
    }

    function escHtml(str) {
      var div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    }
    function escAttr(str) {
      return str.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Close on click outside
    document.addEventListener('click', function(e) {
      if (!searchWrap.contains(e.target)) close();
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && isOpen) close();
    });

    // Close on form submit (go to search page)
    if (form) {
      form.addEventListener('submit', function() {
        close();
      });
    }
  })();

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