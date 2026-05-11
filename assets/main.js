  // ===== About modal: open from any [data-open-about], close on Esc / × / backdrop =====
  (function(){
    const modal = document.getElementById('about-modal');
    if (!modal) return;
    const closeBtn = modal.querySelector('.about-close');
    function open() {
      modal.classList.add('active');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('about-open');
    }
    function close() {
      modal.classList.remove('active');
      modal.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('about-open');
    }
    document.querySelectorAll('[data-open-about]').forEach(trigger => {
      trigger.addEventListener('click', e => { e.preventDefault(); open(); });
    });
    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('active')) close();
    });
    // Open on direct hash load (#about)
    if (location.hash === '#about') open();
    window.addEventListener('hashchange', () => { if (location.hash === '#about') open(); });
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
    const DURATION = 5000; // ms per slide
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
      if (!paused) {
        const elapsed = Date.now() - progressStart;
        const pct = Math.min(100, (elapsed / DURATION) * 100);
        if (progressFill) progressFill.style.width = pct + '%';
        if (elapsed >= DURATION) go(index + 1);
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

    // ---------- Filter chips (currently only Art Commissions panel) ----------
    document.querySelectorAll('.gallery-chips').forEach(chipBar => {
      const panel = chipBar.closest('.gallery-panel');
      const chips = chipBar.querySelectorAll('.gallery-chip');
      chips.forEach(chip => {
        chip.addEventListener('click', () => {
          chips.forEach(c => c.classList.remove('active'));
          chip.classList.add('active');
          const filter = chip.dataset.filter;
          panel.querySelectorAll('.gallery-item').forEach(item => {
            const matches = filter === 'all' || item.dataset.cat === filter;
            item.classList.toggle('filtered-out', !matches);
          });
        });
      });
    });

    // ---------- Pagination ----------
    document.querySelectorAll('.gallery-panel').forEach(panel => {
      const totalPages = parseInt(panel.dataset.pages, 10) || 1;
      if (totalPages < 2) return;
      const prevBtn = panel.querySelector('.gallery-arrow.prev');
      const nextBtn = panel.querySelector('.gallery-arrow.next');
      const indicator = panel.querySelector('.gallery-page-indicator span');
      let currentPage = parseInt(panel.dataset.page, 10) || 1;
      function showPage(n) {
        currentPage = Math.max(1, Math.min(totalPages, n));
        panel.dataset.page = currentPage;
        panel.querySelectorAll('.gallery-item').forEach(item => {
          const itemPage = parseInt(item.dataset.page, 10);
          item.classList.toggle('page-hidden', itemPage !== currentPage);
        });
        if (indicator) indicator.textContent = currentPage;
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
      }
      if (prevBtn) prevBtn.addEventListener('click', () => showPage(currentPage - 1));
      if (nextBtn) nextBtn.addEventListener('click', () => showPage(currentPage + 1));
    });

    // ---------- Lightbox ----------
    const lightbox = document.getElementById('lightbox');
    const lbImage = document.getElementById('lightbox-image');
    const lbLabel = document.getElementById('lightbox-image-label');
    const lbTag = document.getElementById('lightbox-tag');
    const lbTitle = document.getElementById('lightbox-title');
    const lbDesc = document.getElementById('lightbox-desc');
    const lbClose = lightbox.querySelector('.lightbox-close');
    const lbPrev = lightbox.querySelector('.lightbox-nav.prev');
    const lbNext = lightbox.querySelector('.lightbox-nav.next');
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
      lbLabel.textContent = item.dataset.title || '';
      lbTag.textContent = item.dataset.tag || '';
      lbTitle.textContent = item.dataset.title || '';
      lbDesc.textContent = item.dataset.desc || '';
      lbPrev.style.visibility = currentIndex > 0 ? 'visible' : 'hidden';
      lbNext.style.visibility = currentIndex < currentItems.length - 1 ? 'visible' : 'hidden';
    }
    function closeLightbox() {
      lightbox.classList.remove('active');
      lightbox.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('lightbox-open');
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
