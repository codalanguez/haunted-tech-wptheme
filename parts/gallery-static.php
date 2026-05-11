<?php
/**
 * Gallery section — currently static markup carried over from the mockup.
 *
 * TODO: convert to a `gallery_item` CPT with fields
 *   service_tab (art|covers|ai), tag, title, description, image, ratio
 * then query by tab and render dynamically.
 *
 * @package HauntedTech
 */
?>
<section class="gallery block-gallery" id="gallery">
  <div class="section-header">
    <h2 class="section-title">Gallery</h2>
    <div class="section-meta">Recent Work &mdash; Filter by Service</div>
  </div>

  <div class="gallery-tabs" role="tablist">
    <button class="gallery-tab active" data-target="panel-art" role="tab" aria-selected="true">Art Commissions</button>
    <button class="gallery-tab" data-target="panel-covers" role="tab" aria-selected="false">Book Covers</button>
    <button class="gallery-tab" data-target="panel-ai" role="tab" aria-selected="false">AI Generation</button>
  </div>

  <!-- Art Commissions panel — has filter chips -->
  <div class="gallery-panel active" id="panel-art" role="tabpanel" data-page="1" data-pages="2">
    <div class="gallery-chips" role="toolbar" aria-label="Filter commissions">
      <button class="gallery-chip active" data-filter="all">All</button>
      <button class="gallery-chip" data-filter="portrait">Portrait</button>
      <button class="gallery-chip" data-filter="bust">Bust</button>
      <button class="gallery-chip" data-filter="couple">Couple</button>
      <button class="gallery-chip" data-filter="scene">Scene</button>
      <button class="gallery-chip" data-filter="ritual">Ritual</button>
    </div>
    <div class="masonry">
      <!-- Placeholder items — replace with CPT loop -->
      <a href="#" class="gallery-item" data-page="1" data-cat="portrait" data-tag="Portrait" data-title="Sample Portrait" data-desc="Replace this gallery with real entries from a `gallery_item` CPT." data-image-class="v1">
        <div class="gallery-image v1" style="--ratio: 3/4;"><span class="gallery-image-label">Sample 1</span></div>
        <div class="gallery-meta">
          <div class="gallery-tag">Portrait</div>
          <div class="gallery-title">Sample Portrait</div>
          <div class="gallery-caption">Placeholder &mdash; wire up `gallery_item` CPT to populate.</div>
        </div>
      </a>
      <a href="#" class="gallery-item" data-page="1" data-cat="scene" data-tag="Scene" data-title="Sample Scene" data-desc="Demo gallery item." data-image-class="v3">
        <div class="gallery-image v3" style="--ratio: 16/10;"><span class="gallery-image-label">Sample 2</span></div>
        <div class="gallery-meta">
          <div class="gallery-tag">Scene</div>
          <div class="gallery-title">Sample Scene</div>
          <div class="gallery-caption">Demo gallery item.</div>
        </div>
      </a>
      <a href="#" class="gallery-item" data-page="1" data-cat="bust" data-tag="Bust" data-title="Sample Bust" data-desc="Demo gallery item." data-image-class="v2">
        <div class="gallery-image v2" style="--ratio: 1/1;"><span class="gallery-image-label">Sample 3</span></div>
        <div class="gallery-meta">
          <div class="gallery-tag">Bust</div>
          <div class="gallery-title">Sample Bust</div>
          <div class="gallery-caption">Demo gallery item.</div>
        </div>
      </a>
    </div>
    <div class="gallery-footer">
      <button class="gallery-arrow prev" aria-label="Previous page" disabled>&larr;</button>
      <div class="gallery-page-indicator">Page <span>1</span> / 1</div>
      <button class="gallery-arrow next" aria-label="Next page" disabled>&rarr;</button>
      <a href="#" class="gallery-view-all">All Commissions</a>
    </div>
  </div>

  <div class="gallery-panel" id="panel-covers" role="tabpanel" data-page="1" data-pages="1">
    <div class="masonry">
      <a href="#" class="gallery-item" data-page="1" data-tag="Sample" data-title="Cover Placeholder" data-desc="Replace with CPT entries." data-image-class="v4">
        <div class="gallery-image v4" style="--ratio: 2/3;"><span class="gallery-image-label">Cover Sample</span></div>
        <div class="gallery-meta"><div class="gallery-tag">Sample</div><div class="gallery-title">Cover Sample</div><div class="gallery-caption">Placeholder.</div></div>
      </a>
    </div>
    <div class="gallery-footer">
      <a href="#" class="gallery-view-all">All Covers</a>
    </div>
  </div>

  <div class="gallery-panel" id="panel-ai" role="tabpanel" data-page="1" data-pages="1">
    <div class="masonry">
      <a href="#" class="gallery-item" data-page="1" data-tag="Sample" data-title="AI Sample" data-desc="Replace with CPT entries." data-image-class="v8">
        <div class="gallery-image v8" style="--ratio: 16/10;"><span class="gallery-image-label">AI Sample</span></div>
        <div class="gallery-meta"><div class="gallery-tag">Sample</div><div class="gallery-title">AI Sample</div><div class="gallery-caption">Placeholder.</div></div>
      </a>
    </div>
    <div class="gallery-footer">
      <a href="#" class="gallery-view-all">All AI Pieces</a>
    </div>
  </div>
</section>
