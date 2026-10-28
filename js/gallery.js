/**
 * DARK MATTER — Elite Fitness
 * Gallery Filter & Interactive Lightbox Viewer
 */

document.addEventListener('DOMContentLoaded', () => {
  initGalleryFilter();
  initGalleryLightbox();
});

let currentGalleryItems = [];
let activeLightboxIndex = 0;

function initGalleryFilter() {
  const tabs = document.querySelectorAll('.gallery-filter .filter-tab');
  const items = document.querySelectorAll('.gallery-item');
  const emptyState = document.getElementById('galleryEmptyState');

  if (!tabs.length || !items.length) return;

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const filterVal = tab.getAttribute('data-filter');
      let visibleCount = 0;

      items.forEach(item => {
        const itemCategory = item.getAttribute('data-category');
        if (filterVal === 'all' || itemCategory === filterVal) {
          item.style.display = 'block';
          visibleCount++;
        } else {
          item.style.display = 'none';
        }
      });

      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
      }

      // Refresh lightbox list with currently visible items
      updateVisibleGalleryItems();
    });
  });

  updateVisibleGalleryItems();
}

function updateVisibleGalleryItems() {
  const allItems = Array.from(document.querySelectorAll('.gallery-item'));
  currentGalleryItems = allItems.filter(item => item.style.display !== 'none');
}

function initGalleryLightbox() {
  const lightbox = document.getElementById('galleryLightbox');
  if (!lightbox) return;

  const lightboxImg = document.getElementById('lightboxImg');
  const lightboxTitle = document.getElementById('lightboxTitle');
  const lightboxTag = document.getElementById('lightboxTag');
  const closeBtn = document.getElementById('lightboxClose');
  const prevBtn = document.getElementById('lightboxPrev');
  const nextBtn = document.getElementById('lightboxNext');

  const openLightbox = (index) => {
    if (!currentGalleryItems.length) return;
    activeLightboxIndex = (index + currentGalleryItems.length) % currentGalleryItems.length;

    const currentItem = currentGalleryItems[activeLightboxIndex];
    const imgEl = currentItem.querySelector('img');
    const titleEl = currentItem.querySelector('.gallery-caption');
    const tagEl = currentItem.querySelector('.gallery-tag');

    if (imgEl && lightboxImg) {
      lightboxImg.src = imgEl.src;
      lightboxImg.alt = imgEl.alt || 'Dark Matter Facility';
    }
    if (titleEl && lightboxTitle) {
      lightboxTitle.textContent = titleEl.textContent;
    }
    if (tagEl && lightboxTag) {
      lightboxTag.textContent = tagEl.textContent;
    }

    lightbox.classList.add('active');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeLightbox = () => {
    lightbox.classList.remove('active');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  const showNext = () => openLightbox(activeLightboxIndex + 1);
  const showPrev = () => openLightbox(activeLightboxIndex - 1);

  // Bind clicks on gallery items
  document.addEventListener('click', (e) => {
    const item = e.target.closest('.gallery-item');
    if (item) {
      updateVisibleGalleryItems();
      const idx = currentGalleryItems.indexOf(item);
      if (idx !== -1) {
        openLightbox(idx);
      }
    }
  });

  if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
  if (nextBtn) nextBtn.addEventListener('click', showNext);
  if (prevBtn) prevBtn.addEventListener('click', showPrev);

  // Close when clicking backdrop outside content
  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      closeLightbox();
    }
  });

  // Keyboard navigation
  window.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('active')) return;

    if (e.key === 'Escape') {
      closeLightbox();
    } else if (e.key === 'ArrowRight') {
      showNext();
    } else if (e.key === 'ArrowLeft') {
      showPrev();
    }
  });
}
