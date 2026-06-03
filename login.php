<?php
require_once __DIR__ . '/config.php';
startSession();

// Đã đăng nhập rồi thì về trang chủ
if (isLoggedIn()) redirect(SITE_URL . '/index.php');

$error  = '';
$mode   = $_GET['mode'] ?? 'login';
$redirect = $_GET['redirect'] ?? SITE_URL . '/index.php';

/* ══ XỬ LÝ POST ══ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* ── ĐĂNG NHẬP ── */
    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password']  ?? '';
        if (!$email || !$pass) {
            $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
        } elseif (!isValidEmailPhp($email)) {
            $error = 'Email không đúng định dạng (vd: ten@gmail.com).';
        } else {
            try {
                $stmt = db()->prepare("SELECT * FROM users WHERE email = :e LIMIT 1");
                $stmt->execute([':e' => $email]);
                $user = $stmt->fetch();
                if ($user && password_verify($pass, $user['mat_khau'])) {
                    $_SESSION['user_id']    = $user['id'];
                    $_SESSION['user_name']  = $user['ho_ten'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role']  = $user['vai_tro'];
                    redirect($redirect);
                } else {
                    $error = 'Email hoặc mật khẩu không đúng.';
                }
            } catch (Exception $e) {
                $error = 'Lỗi kết nối database. Kiểm tra lại config.php';
            }
        }
    }

    /* ── ĐĂNG KÝ ── */
    if ($action === 'register') {
        $name  = trim($_POST['ho_ten']   ?? '');
        $email = trim($_POST['email']    ?? '');
        $pass  = $_POST['password']      ?? '';
        $pass2 = $_POST['password2']     ?? '';
        if (!$name || !$email || !$pass) {
            $error = 'Vui lòng điền đầy đủ thông tin.';
        } elseif (!isValidEmailPhp($email)) {
            $error = 'Email không đúng định dạng (vd: ten@gmail.com).';
        } elseif (strlen($pass) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
        } elseif ($pass !== $pass2) {
            $error = 'Mật khẩu nhập lại không khớp.';
        } else {
            try {
                $chk = db()->prepare("SELECT id FROM users WHERE email=:e");
                $chk->execute([':e' => $email]);
                if ($chk->fetch()) {
                    $error = 'Email này đã được đăng ký.';
                } else {
                    $hash = password_hash($pass, PASSWORD_BCRYPT);
                    $ins  = db()->prepare("INSERT INTO users (ho_ten,email,mat_khau) VALUES (:n,:e,:p)");
                    $ins->execute([':n'=>$name, ':e'=>$email, ':p'=>$hash]);
                    $_SESSION['user_id']    = db()->lastInsertId();
                    $_SESSION['user_name']  = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role']  = 'khach_hang';
                    redirect(SITE_URL . '/index.php');
                }
            } catch (Exception $e) {
                $error = 'Lỗi kết nối database. Kiểm tra lại config.php';
            }
        }
        $mode = 'register';
    }
}
$currentPage = '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $mode==='register' ? 'Đăng ký' : 'Đăng nhập' ?> — FROMSHOPWHERE</title>
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

<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-logo" style="text-align:center;margin-bottom:16px">
      <img src="images/logo.png" alt="FROMSHOPWHERE" style="height:64px;width:auto">
    </div>

    <!-- Tabs -->
    <div style="display:flex;border-bottom:2px solid var(--border);margin-bottom:24px">
      <a href="login.php" style="flex:1;text-align:center;padding:10px;font-weight:700;font-size:14px;text-decoration:none;
         border-bottom:<?= $mode==='login' ? '2px solid var(--green-600,#0A8A4C)' : 'none' ?>;margin-bottom:-2px;
         color:<?= $mode==='login' ? 'var(--green-600,#0A8A4C)' : 'var(--text-muted,#888)' ?>">
        Đăng nhập
      </a>
      <a href="login.php?mode=register" style="flex:1;text-align:center;padding:10px;font-weight:700;font-size:14px;text-decoration:none;
         border-bottom:<?= $mode==='register' ? '2px solid var(--green-600,#0A8A4C)' : 'none' ?>;margin-bottom:-2px;
         color:<?= $mode==='register' ? 'var(--green-600,#0A8A4C)' : 'var(--text-muted,#888)' ?>">
        Đăng ký
      </a>
    </div>

    <?php if ($error): ?>
      <div class="form-error" style="background:#FEE2E2;color:#991B1B;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px">
        ⚠ <?= e($error) ?>
      </div>
    <?php endif; ?>

    <?php if ($mode === 'login'): ?>
    <!-- ĐĂNG NHẬP -->
    <form method="POST" autocomplete="on">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" id="loginEmail" required
               placeholder="ten@gmail.com" autocomplete="email"
               pattern="[^\s@]+@[^\s@]+\.[^\s@]{2,}"
               title="Email phải có dạng: ten@gmail.com"
               value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <input class="form-input" type="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn-submit">Đăng nhập →</button>
      <p style="text-align:center;margin-top:14px;font-size:12px;color:var(--text-muted)">
        Demo admin: <b>admin@fromshopwhere.com</b> / <b>admin123</b>
      </p>
    </form>

    <?php else: ?>
    <!-- ĐĂNG KÝ -->
    <form method="POST" autocomplete="on">
      <input type="hidden" name="action" value="register">
      <div class="form-group">
        <label class="form-label">Họ và tên</label>
        <input class="form-input" type="text" name="ho_ten" required
               placeholder="Nguyễn Văn A"
               value="<?= e($_POST['ho_ten'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" id="registerEmail" required
               placeholder="ten@gmail.com" autocomplete="email"
               pattern="[^\s@]+@[^\s@]+\.[^\s@]{2,}"
               title="Email phải có dạng: ten@gmail.com"
               value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Mật khẩu</label>
        <input class="form-input" type="password" name="password" required placeholder="Tối thiểu 6 ký tự">
      </div>
      <div class="form-group">
        <label class="form-label">Nhập lại mật khẩu</label>
        <input class="form-input" type="password" name="password2" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn-submit">Tạo tài khoản →</button>
    </form>
    <?php endif; ?>

  </div>
</div>

<footer>
  <div class="footer-inner">
    <div class="footer-bottom">
      <p>© 2025 FROMSHOPWHERE. Bảo lưu mọi quyền.</p>
      <div class="pay-icons">
        <div class="pay-badge">VISA</div><div class="pay-badge">MC</div>
        <div class="pay-badge">MOMO</div><div class="pay-badge">ZALO</div>
      </div>
    </div>
  </div>
</footer>
<script src="shared.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  restoreTheme(); updateCartBadge(); syncCartPanel();
  bindEmailInput(document.getElementById('loginEmail'));
  bindEmailInput(document.getElementById('registerEmail'));
});
</script>
</body>
</html>
