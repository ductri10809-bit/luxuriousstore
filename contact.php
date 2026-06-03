<?php
require_once __DIR__ . '/config.php';
startSession();
$currentPage = 'contact';
$_user = currentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Liên hệ — FROMSHOPWHERE</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<?php
/* ── inline nav ── */
if (!defined('SITE_URL')) require_once __DIR__ . '/config.php';
startSession();
$_user        = currentUser();
$_currentPage = $currentPage ?? '';
?>
<!-- ── TOAST ── -->
<div class="toast" id="toast"></div>

<!-- ── CART OVERLAY ── -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCartOnBackdrop(event)">
  <div class="cart-panel">
    <div class="cart-header">
      <h3>Giỏ hàng</h3>
      <button class="close-btn" onclick="toggleCart()">✕</button>
    </div>
    <div class="cart-items" id="cartItems">
      <div style="text-align:center;padding:48px 0">
        <div style="font-size:40px;margin-bottom:12px">🛒</div>
        <p style="color:var(--text-muted);font-size:14px">Giỏ hàng trống</p>
      </div>
    </div>
    <div class="cart-footer">
      <div class="cart-total">
        <span class="ct-label">Tổng cộng</span>
        <span class="ct-value" id="cartTotal">0đ</span>
      </div>
      <button class="btn-checkout" onclick="window.location.href='<?= SITE_URL ?>/checkout.php'">Tiến hành thanh toán →</button>
    </div>
  </div>
</div>

<!-- ══ NAV ══ -->
<nav>
  <div class="nav-inner">
    <a class="logo" href="<?= SITE_URL ?>/index.php">
      <img src="<?= SITE_URL ?>/images/logo.png" alt="FROMSHOPWHERE"
           style="height:44px;width:auto;object-fit:contain;filter:drop-shadow(0 0 6px rgba(0,0,0,.3))">
    </a>

    <ul class="nav-links">
      <li><a href="<?= SITE_URL ?>/index.php"    <?= $_currentPage==='home'     ?'class="active"':'' ?>>Trang chủ</a></li>
      <li><a href="<?= SITE_URL ?>/products.php" <?= $_currentPage==='products' ?'class="active"':'' ?>>Sản phẩm</a></li>
      <li><a href="<?= SITE_URL ?>/blog.php"     <?= $_currentPage==='blog'     ?'class="active"':'' ?>>Blog</a></li>
      <li><a href="<?= SITE_URL ?>/contact.php"  <?= $_currentPage==='contact'  ?'class="active"':'' ?>>Liên hệ</a></li>
    </ul>

    <div class="nav-right">
      <div class="search-wrap">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
        </svg>
        <input class="search-box" type="search" placeholder="Tìm phần mềm..."
               onkeydown="if(event.key==='Enter')window.location.href='<?= SITE_URL ?>/products.php?q='+encodeURIComponent(this.value)">
      </div>

      <button class="theme-toggle" onclick="toggleTheme()" title="Chuyển sáng/tối" aria-label="Theme">
        <div class="theme-knob" id="themeKnob">☀️</div>
      </button>

      <div class="cart-btn" onclick="toggleCart()">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
        <span class="cart-badge" id="cartCount">0</span>
      </div>

      <?php if ($_user): ?>
        <div style="position:relative">
          <button class="btn-login"
                  onclick="document.getElementById('userMenu').classList.toggle('open')"
                  style="cursor:pointer;display:flex;align-items:center;gap:6px">
            <span style="font-size:16px">👤</span>
            <?= e($_user['ho_ten']) ?> <span style="font-size:10px;opacity:.7">▾</span>
          </button>
          <div id="userMenu" class="user-dropdown">
            <?php if (isAdmin()): ?>
            <a href="<?= SITE_URL ?>/admin/">⚙️ Quản trị Admin</a>
            <?php endif; ?>
            <a href="<?= SITE_URL ?>/profile.php">👤 Tài khoản</a>
            <a href="<?= SITE_URL ?>/logout.php">🚪 Đăng xuất</a>
          </div>
        </div>
      <?php else: ?>
        <a class="btn-login" href="<?= SITE_URL ?>/login.php">Đăng nhập</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<style>
.user-dropdown{position:absolute;top:calc(100% + 8px);right:0;background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:6px;min-width:170px;box-shadow:0 8px 32px rgba(0,0,0,.2);z-index:300;display:none;flex-direction:column;gap:2px}
.user-dropdown.open{display:flex}
.user-dropdown a{padding:9px 13px;border-radius:8px;text-decoration:none;color:var(--text);font-size:13px;font-weight:500;transition:background .12s}
.user-dropdown a:hover{background:var(--bg-alt);color:var(--green-600,#0A8A4C)}
</style>
<script>
document.addEventListener('click', e => {
  const m = document.getElementById('userMenu');
  if (m && !m.parentElement.contains(e.target)) m.classList.remove('open');
});
</script>

<!-- ══════════════════════════════════════ -->
<!--  PAGE HEADER                          -->
<!-- ══════════════════════════════════════ -->
<div class="page-header">
  <div class="page-header-inner">
    <h1>Liên hệ</h1>
    <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7</p>
  </div>
</div>

<!-- ══════════════════════════════════════ -->
<!--  CONTACT                              -->
<!-- ══════════════════════════════════════ -->
<div class="section">
  <div class="contact-grid">

    <!-- Form -->
    <div class="checkout-box">
      <h3>Gửi tin nhắn cho chúng tôi</h3>
      <div class="form-group">
        <label class="form-label">Họ và tên</label>
        <input class="form-input" type="text" placeholder="Nguyễn Văn A" id="contactName">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" placeholder="ten@gmail.com" id="contactEmail" required
               pattern="[^\s@]+@[^\s@]+\.[^\s@]{2,}"
               title="Email phải có dạng: ten@gmail.com">
      </div>
      <div class="form-group">
        <label class="form-label">Chủ đề</label>
        <select class="form-input" style="cursor:pointer" id="contactSubject">
          <option>Hỗ trợ kỹ thuật</option>
          <option>Tư vấn sản phẩm</option>
          <option>Khiếu nại đơn hàng</option>
          <option>Hợp tác kinh doanh</option>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:20px">
        <label class="form-label">Nội dung</label>
        <textarea class="form-input" rows="5" placeholder="Nội dung tin nhắn của bạn..." style="resize:vertical" id="contactMsg"></textarea>
      </div>
      <button class="btn-submit" onclick="sendContact()">Gửi tin nhắn</button>
    </div>

    <!-- Info -->
    <div>
      <div class="contact-info-card">
        <div class="ci-icon" style="background:#E1F5EE">📧</div>
        <div>
          <div class="ci-label">Email hỗ trợ</div>
          <div class="ci-val">support@fromshopwhere.com</div>
          <div class="ci-note">Phản hồi trong 2 giờ</div>
        </div>
      </div>
      <div class="contact-info-card">
        <div class="ci-icon" style="background:#E6F1FB">📱</div>
        <div>
          <div class="ci-label">Hotline miễn phí</div>
          <div class="ci-val">1900 1234</div>
          <div class="ci-note">Thứ 2–7, 8:00–22:00</div>
        </div>
      </div>
      <div class="contact-info-card">
        <div class="ci-icon" style="background:#FAEEDA">💬</div>
        <div>
          <div class="ci-label">Zalo OA</div>
          <div class="ci-val">FROMSHOPWHERE Official</div>
          <div class="ci-note">Chat trực tiếp 24/7</div>
        </div>
      </div>
      <div class="contact-cta">
        <h4>⚡ Hỗ trợ nhanh nhất</h4>
        <p>Nhắn tin qua Zalo hoặc Facebook Messenger để được hỗ trợ nhanh nhất trong giờ hành chính. Đội ngũ kỹ thuật của chúng tôi luôn sẵn sàng.</p>
      </div>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════ -->
<!--  FOOTER                               -->
<!-- ══════════════════════════════════════ -->
<footer>
  <div class="footer-inner">
    <div class="footer-grid">
      <div class="footer-brand">
        <div style="margin-bottom:12px">
          <img src="images/logo.png" alt="FROMSHOPWHERE" style="height:50px;width:auto;object-fit:contain;filter:brightness(1.1) drop-shadow(0 0 4px rgba(0,0,0,.4))">
        </div>
        <p>Nền tảng mua bán phần mềm bản quyền uy tín hàng đầu Việt Nam.</p>
        <div class="social-links">
          <a class="social-link" href="#">f</a>
          <a class="social-link" href="#">in</a>
          <a class="social-link" href="#">yt</a>
          <a class="social-link" href="#">tk</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Sản phẩm</h4>
        <ul>
          <li><a href="products.php">Thiết kế đồ hoạ</a></li>
          <li><a href="products.php">Văn phòng</a></li>
          <li><a href="products.php">Chỉnh sửa video</a></li>
          <li><a href="products.php">Bảo mật</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Hỗ trợ</h4>
        <ul>
          <li><a href="blog.php">Hướng dẫn cài đặt</a></li>
          <li><a href="contact.php">Liên hệ hỗ trợ</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Công ty</h4>
        <ul>
          <li><a href="#">Giới thiệu</a></li>
          <li><a href="blog.php">Blog</a></li>
          <li><a href="#">Điều khoản dịch vụ</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 FROMSHOPWHERE. Bảo lưu mọi quyền.</p>
      <div class="pay-icons">
        <div class="pay-badge">VISA</div>
        <div class="pay-badge">MC</div>
        <div class="pay-badge">MOMO</div>
        <div class="pay-badge">ZALO</div>
        <div class="pay-badge">ATM</div>
      </div>
    </div>
  </div>
</footer>

<script src="shared.js"></script>
<script>
  function sendContact() {
    const name = document.getElementById('contactName').value.trim();
    const email = document.getElementById('contactEmail').value.trim();
    const msg = document.getElementById('contactMsg').value.trim();
    if (!name || !email || !msg) {
      showToast('⚠ Vui lòng điền đầy đủ thông tin!');
      return;
    }
    if (!isValidEmail(email)) {
      showToast('⚠ Email không đúng định dạng (vd: ten@gmail.com)');
      document.getElementById('contactEmail')?.reportValidity();
      return;
    }
    showToast('✓ Đã gửi tin nhắn thành công!');
    document.getElementById('contactName').value = '';
    document.getElementById('contactEmail').value = '';
    document.getElementById('contactMsg').value = '';
  }

  document.addEventListener('DOMContentLoaded', () => {
    restoreTheme();
    updateCartBadge();
    updateLoginBtn();
    syncCartPanel();
    bindEmailInput(document.getElementById('contactEmail'));
  });
</script>
</body>
</html>
