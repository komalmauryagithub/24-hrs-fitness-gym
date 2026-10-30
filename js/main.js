/**
 * DARK MATTER — Elite Fitness
 * Main Application Script: Navigation, Scroll Interactions, Counters, Toast & Modals
 */

document.addEventListener('DOMContentLoaded', () => {
  initNavbar();
  initMobileMenu();
  initScrollReveals();
  initStatCounters();
  initModals();
  initTrainerFilter();
  highlightActiveNavLink();
  initSecretAdminTriggers();
});

/**
 * Navbar Sticky State on Scroll
 */
function initNavbar() {
  const header = document.querySelector('.site-header');
  if (!header) return;

  const onScroll = () => {
    if (window.scrollY > 30) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
}

/**
 * Mobile Navigation Drawer Toggle & Accessibility
 */
function initMobileMenu() {
  const toggleBtn = document.querySelector('.menu-toggle');
  const drawer = document.querySelector('.mobile-drawer');
  const overlay = document.querySelector('.mobile-drawer-overlay');
  if (!toggleBtn || !drawer || !overlay) return;

  const openDrawer = () => {
    toggleBtn.setAttribute('aria-expanded', 'true');
    drawer.classList.add('active');
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  const closeDrawer = () => {
    toggleBtn.setAttribute('aria-expanded', 'false');
    drawer.classList.remove('active');
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  };

  toggleBtn.addEventListener('click', () => {
    const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
    if (isExpanded) {
      closeDrawer();
    } else {
      openDrawer();
    }
  });

  overlay.addEventListener('click', closeDrawer);

  // Close when clicking a nav link inside drawer
  const drawerLinks = drawer.querySelectorAll('.nav-link, .btn-dm');
  drawerLinks.forEach(link => {
    link.addEventListener('click', closeDrawer);
  });

  // Close on Escape key
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && drawer.classList.contains('active')) {
      closeDrawer();
    }
  });
}

/**
 * Active Navigation Link Highlighting based on pathname
 */
function highlightActiveNavLink() {
  const currentPath = window.location.pathname.toLowerCase();
  const navLinks = document.querySelectorAll('.nav-link');

  navLinks.forEach(link => {
    const href = link.getAttribute('href');
    if (!href) return;
    
    // Check if link matches current page or index
    const isHome = (currentPath.endsWith('/') || currentPath.endsWith('index.html')) && (href === 'index.html' || href === './' || href === '/');
    const isMatch = !isHome && href !== 'index.html' && href !== './' && currentPath.includes(href.replace('.html', ''));

    if (isHome || isMatch) {
      link.classList.add('active');
    } else if (link.classList.contains('active') && !isHome) {
      link.classList.remove('active');
    }
  });
}

/**
 * Scroll Reveal Animations via Intersection Observer
 */
function initScrollReveals() {
  // If user prefers reduced motion, reveal everything immediately
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const revealElements = document.querySelectorAll('.reveal-init');

  if (prefersReduced) {
    revealElements.forEach(el => el.classList.add('revealed'));
    return;
  }

  if (!('IntersectionObserver' in window)) {
    revealElements.forEach(el => el.classList.add('revealed'));
    return;
  }

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        obs.unobserve(entry.target);
      }
    });
  }, {
    threshold: 0.12,
    rootMargin: '0px 0px -40px 0px'
  });

  revealElements.forEach(el => observer.observe(el));
}

/**
 * Animated Number Counters for Stats
 */
function initStatCounters() {
  const counters = document.querySelectorAll('.counter-val');
  if (!counters.length) return;

  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const animateCounter = (el) => {
    if (el.dataset.animated === 'true') return;
    el.dataset.animated = 'true';

    const target = parseInt(el.getAttribute('data-target'), 10) || 0;
    if (prefersReduced) {
      el.textContent = target.toLocaleString();
      return;
    }

    const duration = 1400; // ms
    const startTime = performance.now();

    const update = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      // Ease out cubic
      const ease = 1 - Math.pow(1 - progress, 3);
      const current = Math.floor(ease * target);
      el.textContent = current.toLocaleString();

      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        el.textContent = target.toLocaleString();
      }
    };

    requestAnimationFrame(update);
  };

  if (!('IntersectionObserver' in window)) {
    counters.forEach(c => animateCounter(c));
    return;
  }

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting || entry.intersectionRatio > 0) {
        animateCounter(entry.target);
        obs.unobserve(entry.target);
      }
    });
  }, { 
    threshold: 0.05,
    rootMargin: '50px 0px 0px 0px'
  });

  counters.forEach(c => {
    // Check if element is already within viewport
    const rect = c.getBoundingClientRect();
    if (rect.top < window.innerHeight && rect.bottom >= 0) {
      animateCounter(c);
    } else {
      observer.observe(c);
    }
  });
}

/**
 * Accessible Modal Dialog Handler
 */
function initModals() {
  // Trigger open by [data-open-modal]
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-open-modal]');
    if (trigger) {
      e.preventDefault();
      const modalId = trigger.getAttribute('data-open-modal');
      openModal(modalId, trigger);
    }

    const closeBtn = e.target.closest('[data-close-modal]');
    if (closeBtn) {
      e.preventDefault();
      const modal = closeBtn.closest('.dm-modal-backdrop');
      if (modal) {
        closeModal(modal.id);
      }
    }
  });

  // Close when clicking directly on the backdrop outside the dialog
  document.querySelectorAll('.dm-modal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) {
        closeModal(backdrop.id);
      }
    });
  });

  // Escape key closes open modal
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const activeModal = document.querySelector('.dm-modal-backdrop.active');
      if (activeModal) {
        closeModal(activeModal.id);
      }
    }
  });
}

let lastFocusedElement = null;

function openModal(modalId, triggerElement = null) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  lastFocusedElement = triggerElement || document.activeElement;
  modal.classList.add('active');
  modal.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';

  // Focus the first focusable element inside the modal
  const focusable = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
  if (focusable.length) {
    focusable[0].focus();
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  modal.classList.remove('active');
  modal.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';

  if (lastFocusedElement) {
    lastFocusedElement.focus();
    lastFocusedElement = null;
  }
}

// Expose openModal/closeModal globally
window.openModal = openModal;
window.closeModal = closeModal;

/**
 * Toast Notification System
 */
function showToast(title, message, duration = 4000) {
  let container = document.querySelector('.dm-toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'dm-toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'dm-toast';
  toast.setAttribute('role', 'status');
  toast.innerHTML = `
    <div class="toast-icon">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>
    </div>
    <div class="toast-message">
      <strong>${title}</strong>
      <span>${message}</span>
    </div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.transition = 'all 0.35s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(60px)';
    setTimeout(() => toast.remove(), 350);
  }, duration);
}

window.showToast = showToast;



/**
 * Trainers Filter by Specialty Tab
 */
function initTrainerFilter() {
  const filterTabs = document.querySelectorAll('.trainers-filter .filter-tab');
  const trainerCols = document.querySelectorAll('.trainer-col');
  const emptyState = document.getElementById('trainersEmptyState');

  if (!filterTabs.length || !trainerCols.length) return;

  filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      filterTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const filterVal = tab.getAttribute('data-filter');
      let visibleCount = 0;

      trainerCols.forEach(col => {
        const cat = col.getAttribute('data-category');
        if (filterVal === 'all' || cat === filterVal) {
          col.style.display = 'block';
          visibleCount++;
        } else {
          col.style.display = 'none';
        }
      });

      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
      }
    });
  });
}

/**
 * Secret Admin Portal Access Triggers:
 * 1. Triple-Tap on Brand Logo: Tap 3 times quickly to redirect to admin login.
 * 2. Long-Press on Footer Copyright: Press & hold for 1.5 seconds to redirect to admin login.
 */
function initSecretAdminTriggers() {
  // 1. Triple Tap on Logo (Desktop Header Logo + Mobile Drawer Logo)
  const logos = document.querySelectorAll('.brand-logo, .brand-logo img, .mobile-drawer img, .footer-brand img');
  let tapCount = 0;
  let tapTimer = null;

  logos.forEach(logo => {
    logo.style.cursor = 'pointer';

    logo.addEventListener('click', (e) => {
      tapCount++;
      clearTimeout(tapTimer);

      if (tapCount === 1) {
        // Start 800ms window for 3 taps; don't preventDefault so normal click still works
        tapTimer = setTimeout(() => {
          tapCount = 0;
        }, 800);
      } else if (tapCount === 2) {
        // 2nd tap detected, prevent navigation so 3rd tap can register
        e.preventDefault();
        tapTimer = setTimeout(() => {
          tapCount = 0;
        }, 800);
      } else if (tapCount >= 3) {
        // 3rd TAP DETECTED!
        e.preventDefault();
        e.stopPropagation();
        clearTimeout(tapTimer);
        tapCount = 0;

        if (window.showToast) {
          window.showToast('ADMIN ACCESS', 'Secret gesture recognized. Opening Admin Portal...');
        }
        setTimeout(() => {
          window.location.href = 'admin/login.php';
        }, 350);
        return false;
      }
    });
  });

  // 2. Long Press on Footer Copyright Text (1.5 seconds hold)
  const copyrightEl = document.getElementById('footerCopyright') || document.querySelector('.footer-bottom div:first-child');
  if (copyrightEl) {
    let pressTimer = null;

    const startPress = () => {
      copyrightEl.style.transition = 'color 0.3s ease, text-shadow 0.3s ease';
      copyrightEl.style.color = 'var(--dm-lime, #00E5FF)';
      copyrightEl.style.textShadow = '0 0 10px rgba(0, 229, 255, 0.7)';

      pressTimer = setTimeout(() => {
        if (window.showToast) {
          window.showToast('STAFF ACCESS', 'Secret gesture recognized. Opening Admin Portal...');
        }
        setTimeout(() => {
          window.location.href = 'admin/login.php';
        }, 350);
      }, 1500);
    };

    const cancelPress = () => {
      if (pressTimer) {
        clearTimeout(pressTimer);
        pressTimer = null;
      }
      copyrightEl.style.color = '';
      copyrightEl.style.textShadow = '';
    };

    copyrightEl.addEventListener('mousedown', startPress);
    copyrightEl.addEventListener('mouseup', cancelPress);
    copyrightEl.addEventListener('mouseleave', cancelPress);

    copyrightEl.addEventListener('touchstart', startPress, { passive: true });
    copyrightEl.addEventListener('touchend', cancelPress);
    copyrightEl.addEventListener('touchcancel', cancelPress);
  }
}

