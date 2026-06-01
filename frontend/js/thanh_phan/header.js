/**
 * Minimal header script (Céline-like responsive behavior)
 * - idempotent init
 * - hamburger open/close with overlay and body scroll lock
 * - accessible aria attributes
 */
(() => {
  if (window.__cf_header_v2) return; // guard
  window.__cf_header_v2 = true;

  function safeGet(id) { return document.getElementById(id); }

  function init() {
    const hamburger = safeGet('hamburger-btn');
    const nav = safeGet('site-nav');
    const cartBadge = safeGet('cart-count');
    const wishBadge = safeGet('wishlist-count');
    const authEl = safeGet('auth-actions');

    // update badges if available
    try {
      if (cartBadge && window.GioHang) cartBadge.textContent = GioHang.dem();
      if (wishBadge && window.YeuThich) wishBadge.textContent = YeuThich.dem();
    } catch (e) { /* ignore */ }

    // update auth area (show user name + logout) if Chung is available
    async function updateAuthActions() {
      if (!authEl) return;
      try {
        if (window.Chung && typeof Chung.ensureUserChecked === 'function') {
          const user = await Chung.ensureUserChecked();
          if (user) {
            // build simple, safe DOM elements (avoid innerHTML with user content)
            authEl.innerHTML = '';
            const nameSpan = document.createElement('span');
            nameSpan.className = 'user-name';
            nameSpan.textContent = user.ho_ten || user.email || 'Tài khoản';

            const logoutLink = document.createElement('a');
            logoutLink.href = '#';
            logoutLink.id = 'logout-btn';
            logoutLink.textContent = 'Đăng xuất';
            logoutLink.addEventListener('click', (ev) => {
              ev.preventDefault();
              if (typeof Chung.logout === 'function') Chung.logout();
              else window.location.href = '../../trang/dang_nhap/dang_nhap.html';
            });

            authEl.appendChild(nameSpan);
            authEl.appendChild(logoutLink);
            return;
          }
        }
      } catch (err) {
        console.error('updateAuthActions failed', err);
      }
      // fallback: show login link
      if (authEl) authEl.innerHTML = '<a href="../../trang/dang_nhap/dang_nhap.html">Đăng nhập</a>';
    }

    // run auth update but don't block initialization
    updateAuthActions();

    if (!hamburger || !nav) return;

    // prepare overlay
    let overlay = null;
    function createOverlay() {
      if (overlay) return overlay;
      overlay = document.createElement('div');
      overlay.id = 'nav-overlay';
      overlay.className = 'nav-overlay open';
      overlay.addEventListener('click', () => closeMenu());
      document.body.appendChild(overlay);
      return overlay;
    }
    function removeOverlay() { if (overlay) { overlay.remove(); overlay = null; } }

    function openMenu() {
      nav.classList.add('active');
      hamburger.classList.add('active');
      nav.setAttribute('aria-hidden', 'false');
      hamburger.setAttribute('aria-expanded', 'true');
      document.body.classList.add('nav-open');
      createOverlay();
    }

    function closeMenu() {
      nav.classList.remove('active');
      hamburger.classList.remove('active');
      nav.setAttribute('aria-hidden', 'true');
      hamburger.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('nav-open');
      removeOverlay();
    }

    function toggleMenu() { if (nav.classList.contains('active')) closeMenu(); else openMenu(); }

    hamburger.addEventListener('click', (e) => { e.stopPropagation(); toggleMenu(); });

    // close when clicking outside
    document.addEventListener('click', (e) => {
      if (!nav.contains(e.target) && !hamburger.contains(e.target) && nav.classList.contains('active')) {
        closeMenu();
      }
    });

    // ESC key
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });

    // close on resize to desktop
    let t = null;
    window.addEventListener('resize', () => { clearTimeout(t); t = setTimeout(() => { if (window.innerWidth > 900) closeMenu(); }, 150); });
  }

  // If header is injected dynamically, listen for event
  document.addEventListener('componentLoaded', (e) => { if (e.detail && e.detail.selector === '#header') init(); });
  // Otherwise init immediately
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
