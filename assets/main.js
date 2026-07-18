  // ===== Shared modal focus-trap =====
  // htFocusTrap(modalEl) returns { activate(), deactivate() }. Call activate()
  // right after a modal becomes visible: it remembers whatever had focus,
  // moves focus into the modal, and traps Tab/Shift+Tab inside it. Call
  // deactivate() right after closing: it releases the trap and restores focus
  // to whatever triggered the modal (button, spine, card, etc.). Every modal
  // root (#about-modal, #monkii-modal, #lightbox, #book-modal, #webnovel-modal)
  // needs tabindex="-1" so it's a valid fallback focus target when it has no
  // focusable children yet (e.g. book/webnovel modals before REST content loads).
  const HT_FOCUSABLE = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
  function htFocusTrap(modal) {
    let lastFocused = null;
    function focusable() {
      return Array.from(modal.querySelectorAll(HT_FOCUSABLE))
        .filter(el => el.offsetWidth || el.offsetHeight || el === document.activeElement);
    }
    function onKeydown(e) {
      if (e.key !== 'Tab') return;
      const items = focusable();
      if (!items.length) return;
      const first = items[0];
      const last  = items[items.length - 1];
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault(); last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault(); first.focus();
      }
    }
    let isActive = false;
    return {
      // Safe to call again while already active (e.g. a "More in Series" /
      // "Also By" link navigates the modal to new REST-fetched content
      // without closing it first) — re-focuses the fresh content but keeps
      // the ORIGINAL trigger as the element focus returns to on deactivate().
      activate() {
        if (!isActive) {
          lastFocused = document.activeElement;
          isActive = true;
        }
        modal.addEventListener('keydown', onKeydown);
        // Synchronous, not requestAnimationFrame: rAF can be paused/throttled
        // in backgrounded or non-visible tabs, silently dropping the focus
        // shift. Reading offsetWidth/offsetHeight inside focusable() already
        // forces the layout the class-toggle just triggered, so the browser
        // has accurate, current geometry without needing to wait a frame.
        const items = focusable();
        (items[0] || modal).focus({ preventScroll: true });
      },
      deactivate() {
        modal.removeEventListener('keydown', onKeydown);
        if (lastFocused && typeof lastFocused.focus === 'function') {
          lastFocused.focus({ preventScroll: true });
        }
        lastFocused = null;
        isActive = false;
      }
    };
  }

  // ===== About modal: open from any [data-open-about] OR any <a> whose href
  // resolves to #about (so user-created WP menu items pointing to /#about
  // or #about work without needing the data attribute). Close on Esc / × /
  // backdrop. =====
  (function(){
    const modal = document.getElementById('about-modal');
    if (!modal) return;
    const closeBtn = modal.querySelector('.about-close');
    const trap = htFocusTrap(modal);
    function open() {
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('about-open');
      trap.activate();
    }
    function close() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('about-open');
      trap.deactivate();
    }
    // Delegated click handler — catches both [data-open-about] triggers and
    // any anchor pointing at the #about hash (same-page or cross-page).
    // Cross-page anchors still navigate; the hash-load handler below opens
    // the modal once the home page is loaded.
    document.addEventListener('click', function(e){
      const a = e.target.closest('a, [data-open-about]');
      if (!a) return;
      const isAboutTrigger =
        a.hasAttribute('data-open-about') ||
        (a.tagName === 'A' && (a.getAttribute('href') || '').match(/(^\/|\/)#about$/));
      if (!isAboutTrigger) return;
      // Same-page: open the modal without navigating.
      if (a.tagName !== 'A' || a.pathname === location.pathname || a.getAttribute('href').charAt(0) === '#') {
        e.preventDefault();
        open();
      }
    });
    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('active')) close();
    });
    // Open on direct hash load (#about) or any hashchange to #about.
    if (location.hash === '#about') open();
    window.addEventListener('hashchange', () => { if (location.hash === '#about') open(); });
  })();

  // ===== MONKII modal: open from any [data-open-monkii] OR any <a> whose href
  // resolves to #monkii (same delegated pattern as the About modal). Close on
  // Esc / × / backdrop. =====
  (function(){
    const modal = document.getElementById('monkii-modal');
    if (!modal) return;
    const closeBtn = modal.querySelector('.monkii-close');
    const trap = htFocusTrap(modal);
    function open() {
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('monkii-open');
      trap.activate();
      // Close conflicting modals
      const about = document.getElementById('about-modal');
      if (about && about.classList.contains('active')) {
        about.classList.remove('active');
        about.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('about-open');
      }
    }
    function close() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('monkii-open');
      trap.deactivate();
      if (location.hash === '#monkii') {
        history.replaceState(null, '', location.pathname + location.search);
      }
    }
    document.addEventListener('click', function(e){
      const a = e.target.closest('a, [data-open-monkii]');
      if (!a) return;
      const isMonkiiTrigger =
        a.hasAttribute('data-open-monkii') ||
        (a.tagName === 'A' && (a.getAttribute('href') || '').match(/(^\/|\/)#monkii$/));
      if (!isMonkiiTrigger) return;
      // Same-page: open the modal without navigating.
      if (a.tagName !== 'A' || a.pathname === location.pathname || a.getAttribute('href').charAt(0) === '#') {
        e.preventDefault();
        open();
      }
    });
    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('active')) close();
    });
    // Open on direct hash load (#monkii) or any hashchange to #monkii.
    if (location.hash === '#monkii') open();
    window.addEventListener('hashchange', () => { if (location.hash === '#monkii') open(); });
  })();

  // ===== Hero slider — auto-rotate w/ pause on hover, arrows + dots + progress =====
  (function(){
    const hero = document.getElementById('hero-slider');
    if (!hero) return;
    const slides = hero.querySelectorAll('.hero-content');
    const dots = hero.querySelectorAll('.hero-dot');
    const prevBtn = hero.querySelector('.hero-arrow.prev');
    const nextBtn = hero.querySelector('.hero-arrow.next');
    const progressFill = document.getElementById('hero-progress-fill');
    let index = 0;
    const total = slides.length;
    // Read duration + autoplay from Customizer-localized options if present.
    const OPTS = (typeof HauntedTechOpts !== 'undefined') ? HauntedTechOpts : {};
    const DURATION = (OPTS.sliderDuration && OPTS.sliderDuration > 0) ? Number(OPTS.sliderDuration) : 5000; // ms per slide
    const AUTOPLAY = ('sliderAutoplay' in OPTS) ? Boolean(Number(OPTS.sliderAutoplay)) : true;
    let progressStart = Date.now();
    let paused = false;
    let rafId;

    function go(n) {
      index = (n + total) % total;
      slides.forEach((s, i) => s.classList.toggle('active', i === index));
      dots.forEach((d, i) => {
        d.classList.toggle('active', i === index);
        d.setAttribute('aria-selected', i === index ? 'true' : 'false');
      });
      progressStart = Date.now();
    }
    function tick() {
      if (!paused && AUTOPLAY) {
        const elapsed = Date.now() - progressStart;
        const pct = Math.min(100, (elapsed / DURATION) * 100);
        if (progressFill) progressFill.style.width = pct + '%';
        if (elapsed >= DURATION) go(index + 1);
      } else if (!AUTOPLAY && progressFill) {
        progressFill.style.width = '0%';
      }
      rafId = requestAnimationFrame(tick);
    }
    prevBtn && prevBtn.addEventListener('click', () => go(index - 1));
    nextBtn && nextBtn.addEventListener('click', () => go(index + 1));
    dots.forEach(d => d.addEventListener('click', () => go(parseInt(d.dataset.slide, 10))));
    hero.addEventListener('mouseenter', () => { paused = true; hero.classList.add('paused'); });
    hero.addEventListener('mouseleave', () => {
      paused = false;
      hero.classList.remove('paused');
      progressStart = Date.now() - ((parseFloat(progressFill.style.width)/100) * DURATION || 0);
    });
    // Keyboard arrows when hero in focus
    hero.addEventListener('keydown', e => {
      if (e.key === 'ArrowLeft') go(index - 1);
      if (e.key === 'ArrowRight') go(index + 1);
    });
    tick();
  })();

  // ===== Gallery: tabs + filter chips + pagination + lightbox =====
  (function(){
    // ---------- Tabs (with hash-routing) ----------
    const tabs = document.querySelectorAll('.gallery-tab');
    const panels = document.querySelectorAll('.gallery-panel');
    // Map: short-name in URL hash -> tab data-target
    const hashMap = {
      'gallery-art': 'panel-art',
      'gallery-covers': 'panel-covers',
      'gallery-ai': 'panel-ai'
    };
    function activateTab(targetId, updateHash) {
      tabs.forEach(t => {
        const isActive = t.dataset.target === targetId;
        t.classList.toggle('active', isActive);
        t.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
      panels.forEach(p => p.classList.toggle('active', p.id === targetId));
      if (updateHash) {
        const shortName = Object.keys(hashMap).find(k => hashMap[k] === targetId) || 'gallery';
        history.replaceState(null, '', '#' + shortName);
      }
    }
    tabs.forEach(tab => tab.addEventListener('click', () => activateTab(tab.dataset.target, true)));
    // On load + on hashchange: if URL points to a gallery sub-tab, activate it AND scroll to gallery
    function syncFromHash() {
      const h = location.hash.replace('#', '');
      if (hashMap[h]) {
        activateTab(hashMap[h], false);
        // Smooth-scroll to gallery section after activating the tab
        setTimeout(() => {
          const g = document.getElementById('gallery');
          if (g) g.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 50);
      }
    }
    window.addEventListener('hashchange', syncFromHash);
    syncFromHash();

    // ---------- Filter chips + pagination (combined so a category filter
    // re-paginates its own matches, instead of being constrained to
    // whichever page was showing when the filter was applied) ----------
    document.querySelectorAll('.gallery-panel').forEach(panel => {
      const allItems = Array.from(panel.querySelectorAll('.gallery-item'));
      // Page size = however many items PHP put on page 1 before any filter ran.
      const pageSize = allItems.filter(i => parseInt(i.dataset.page, 10) === 1).length || allItems.length || 1;
      const prevBtn = panel.querySelector('.gallery-arrow.prev');
      const nextBtn = panel.querySelector('.gallery-arrow.next');
      const currentEl = panel.querySelector('.gallery-page-current');
      const totalEl = panel.querySelector('.gallery-page-total');
      let currentPage = 1;

      function showPage(n) {
        const matched = allItems.filter(i => !i.classList.contains('filtered-out'));
        const totalPages = Math.max(1, Math.ceil(matched.length / pageSize));
        currentPage = Math.max(1, Math.min(totalPages, n));
        allItems.forEach(item => {
          if (item.classList.contains('filtered-out')) {
            item.classList.add('page-hidden');
            return;
          }
          const idx = matched.indexOf(item);
          const itemPage = Math.floor(idx / pageSize) + 1;
          item.classList.toggle('page-hidden', itemPage !== currentPage);
        });
        if (currentEl) currentEl.textContent = currentPage;
        if (totalEl) totalEl.textContent = totalPages;
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
      }

      if (prevBtn) prevBtn.addEventListener('click', () => showPage(currentPage - 1));
      if (nextBtn) nextBtn.addEventListener('click', () => showPage(currentPage + 1));

      const chipBar = panel.querySelector('.gallery-chips');
      if (chipBar) {
        const chips = chipBar.querySelectorAll('.gallery-chip');
        chips.forEach(chip => {
          chip.addEventListener('click', () => {
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const filter = chip.dataset.filter;
            allItems.forEach(item => {
              const matches = filter === 'all' || item.dataset.cat === filter;
              item.classList.toggle('filtered-out', !matches);
            });
            showPage(1);
          });
        });
      }

      showPage(1);
    });

    // ---------- Lightbox ----------
    const lightbox = document.getElementById('lightbox');
    const lbImage = document.getElementById('lightbox-image');
    const lbPhoto = document.getElementById('lightbox-image-photo');
    const lbLabel = document.getElementById('lightbox-image-label');
    const lbTag = document.getElementById('lightbox-tag');
    const lbTitle = document.getElementById('lightbox-title');
    const lbDesc = document.getElementById('lightbox-desc');
    const lbBuy = document.getElementById('lightbox-buy');
    const lbClose = lightbox.querySelector('.lightbox-close');
    const lbPrev = lightbox.querySelector('.lightbox-nav.prev');
    const lbNext = lightbox.querySelector('.lightbox-nav.next');
    const lbTrap = htFocusTrap(lightbox);
    let currentItems = [];
    let currentIndex = 0;

    function openLightbox(item) {
      // Build sibling list = visible items in the active panel
      const panel = item.closest('.gallery-panel');
      currentItems = Array.from(panel.querySelectorAll('.gallery-item'))
        .filter(el => !el.classList.contains('page-hidden') && !el.classList.contains('filtered-out'));
      currentIndex = currentItems.indexOf(item);
      renderLightbox();
      lightbox.classList.add('active');
      lightbox.setAttribute('aria-hidden', 'false');
      document.body.classList.add('lightbox-open');
      lbTrap.activate();
    }
    function renderLightbox() {
      const item = currentItems[currentIndex];
      if (!item) return;
      const cls = item.dataset.imageClass || 'v1';
      lbImage.className = 'lightbox-image ' + cls;
      // Match aspect ratio of the source for visual continuity
      const innerImage = item.querySelector('.gallery-image');
      const ratio = innerImage ? getComputedStyle(innerImage).aspectRatio : 'auto';
      lbImage.style.aspectRatio = ratio !== 'auto' ? ratio : '';
      // Real uploaded photo, when one exists — otherwise fall back to the
      // decorative gradient variant + text label (pre-existing behavior for
      // items with no image set).
      if (item.dataset.image) {
        lbPhoto.src = item.dataset.image;
        lbPhoto.alt = item.dataset.title || '';
        lbPhoto.hidden = false;
        lbLabel.hidden = true;
      } else {
        lbPhoto.hidden = true;
        lbPhoto.removeAttribute('src');
        lbLabel.hidden = false;
      }
      lbLabel.textContent = item.dataset.title || '';
      lbTag.textContent = item.dataset.tag || '';
      lbTitle.textContent = item.dataset.title || '';
      lbDesc.textContent = item.dataset.desc || '';
      if (item.dataset.buyUrl) {
        lbBuy.href = item.dataset.buyUrl;
        lbBuy.hidden = false;
      } else {
        lbBuy.hidden = true;
        lbBuy.removeAttribute('href');
      }
      lbPrev.style.visibility = currentIndex > 0 ? 'visible' : 'hidden';
      lbNext.style.visibility = currentIndex < currentItems.length - 1 ? 'visible' : 'hidden';
    }
    function closeLightbox() {
      lightbox.classList.remove('active');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('lightbox-open');
      lbTrap.deactivate();
    }
    document.querySelectorAll('.gallery-item').forEach(item => {
      item.addEventListener('click', e => { e.preventDefault(); openLightbox(item); });
    });
    lbClose.addEventListener('click', closeLightbox);
    lbPrev.addEventListener('click', () => { if (currentIndex > 0) { currentIndex--; renderLightbox(); } });
    lbNext.addEventListener('click', () => { if (currentIndex < currentItems.length - 1) { currentIndex++; renderLightbox(); } });
    lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });
    document.addEventListener('keydown', e => {
      if (!lightbox.classList.contains('active')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowLeft' && currentIndex > 0) { currentIndex--; renderLightbox(); }
      if (e.key === 'ArrowRight' && currentIndex < currentItems.length - 1) { currentIndex++; renderLightbox(); }
    });
  })();

  // ===== v0.8 — Book modal (REST-fetched) + Back-to-top arrow =====
  (function(){
    const modal = document.getElementById('book-modal');
    if (!modal) return;
    const body  = document.getElementById('book-modal-body');
    const title = document.getElementById('book-modal-title');
    const close = modal.querySelector('.book-modal-close');
    const trap  = htFocusTrap(modal);

    // Endpoint base — same-origin /wp-json/haunted-tech/v1/book-modal/<slug>
    const REST_ROOT = (window.wpApiSettings && window.wpApiSettings.root) || (location.origin + '/wp-json/');
    const ENDPOINT  = REST_ROOT + 'haunted-tech/v1/book-modal/';
    const cache = new Map();

    async function openBook(slug, fromHash) {
      if (!slug) return;
      try {
        let data = cache.get(slug);
        if (!data) {
          const resp = await fetch(ENDPOINT + encodeURIComponent(slug));
          if (!resp.ok) throw new Error('Book fetch failed: ' + resp.status);
          data = await resp.json();
          cache.set(slug, data);
        }
        body.innerHTML = data.html;
        if (title) title.textContent = data.title || '';
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('book-modal-open');
        body.scrollTop = 0;
        trap.activate();
        if (!fromHash) history.replaceState(null, '', '#book-' + slug);
        // Close conflicting modals
        const about = document.getElementById('about-modal');
        if (about && about.classList.contains('active')) {
          about.classList.remove('active');
          about.setAttribute('aria-hidden', 'true');
        }
      } catch (e) {
        console.warn('[haunted-tech] book modal load failed', e);
      }
    }
    function shut() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('book-modal-open');
      trap.deactivate();
      if (location.hash.startsWith('#book-')) {
        history.replaceState(null, '', location.pathname + location.search);
      }
    }
    // Delegate clicks for any [data-open-book] (works for dynamically-injected links too)
    document.addEventListener('click', e => {
      const t = e.target.closest('[data-open-book]');
      if (!t) return;
      e.preventDefault();
      openBook(t.dataset.openBook);
    });
    close.addEventListener('click', shut);
    modal.addEventListener('click', e => { if (e.target === modal) shut(); });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('active')) shut();
    });
    // Open on direct hash load (e.g. someone shared codalanguez.com/#book-hexrose)
    const m = location.hash.match(/^#book-([\w-]+)/);
    if (m) openBook(m[1], true);
    window.addEventListener('hashchange', () => {
      const m2 = location.hash.match(/^#book-([\w-]+)/);
      if (m2) openBook(m2[1], true);
    });
  })();

  (function(){
    const btn = document.getElementById('back-to-top');
    if (!btn) return;
    const THRESHOLD = 600;
    let ticking = false;
    function update() {
      const y = window.scrollY || document.documentElement.scrollTop;
      btn.classList.toggle('visible', y > THRESHOLD);
      ticking = false;
    }
    window.addEventListener('scroll', () => {
      if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
    update();
    btn.addEventListener('click', e => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  })();

  // ===== v0.9 — Web novel modal (parity with book modal) =====
  (function(){
    const modal = document.getElementById('webnovel-modal');
    if (!modal) return;
    const body  = document.getElementById('webnovel-modal-body');
    const title = document.getElementById('webnovel-modal-title');
    const close = modal.querySelector('[data-close-webnovel]');
    const trap  = htFocusTrap(modal);
    const REST_ROOT = (window.wpApiSettings && window.wpApiSettings.root) || (location.origin + '/wp-json/');
    const ENDPOINT  = REST_ROOT + 'haunted-tech/v1/webnovel-modal/';
    const cache = new Map();
    async function openWN(slug, fromHash) {
      if (!slug) return;
      try {
        let data = cache.get(slug);
        if (!data) {
          const resp = await fetch(ENDPOINT + encodeURIComponent(slug));
          if (!resp.ok) throw new Error('Web novel fetch failed: ' + resp.status);
          data = await resp.json();
          cache.set(slug, data);
        }
        body.innerHTML = data.html;
        if (title) title.textContent = data.title || '';
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('book-modal-open');
        body.scrollTop = 0;
        trap.activate();
        if (!fromHash) history.replaceState(null, '', '#webnovel-' + slug);
        // Close conflicting modals
        const bookModal = document.getElementById('book-modal');
        if (bookModal && bookModal.classList.contains('active')) {
          bookModal.classList.remove('active');
          bookModal.setAttribute('aria-hidden', 'true');
        }
      } catch (e) {
        console.warn('[haunted-tech] webnovel modal load failed', e);
      }
    }
    function shut() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('book-modal-open');
      trap.deactivate();
      if (location.hash.startsWith('#webnovel-')) {
        history.replaceState(null, '', location.pathname + location.search);
      }
    }
    document.addEventListener('click', e => {
      const t = e.target.closest('[data-open-webnovel]');
      if (!t) return;
      e.preventDefault();
      openWN(t.dataset.openWebnovel);
    });
    close.addEventListener('click', shut);
    modal.addEventListener('click', e => { if (e.target === modal) shut(); });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('active')) shut();
    });
    const m = location.hash.match(/^#webnovel-([\w-]+)/);
    if (m) openWN(m[1], true);
    window.addEventListener('hashchange', () => {
      const m2 = location.hash.match(/^#webnovel-([\w-]+)/);
      if (m2) openWN(m2[1], true);
    });
  })();

  // ===== Header search: toggle popover, focus input on open, close on Esc / outside click =====
  (function(){
    const toggle = document.getElementById('header-search-toggle');
    const panel  = document.getElementById('header-search-panel');
    if (!toggle || !panel) return;
    const input = panel.querySelector('input[type="search"]');
    function open() {
      panel.hidden = false;
      toggle.setAttribute('aria-expanded', 'true');
      if (input) input.focus({ preventScroll: true });
    }
    function close() {
      panel.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
    }
    toggle.addEventListener('click', () => { panel.hidden ? open() : close(); });
    document.addEventListener('click', e => {
      if (panel.hidden) return;
      if (panel.contains(e.target) || toggle.contains(e.target)) return;
      close();
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && !panel.hidden) { close(); toggle.focus(); }
    });
  })();
