# 🚀 QR Payment - Quick Start Guide

## ⚡ 5 Phút Setup

### Bước 1: Cài đặt Thư viện (1 min)
```bash
cd /d/hiii/htdocs/luxurious-fashion-store

# Nếu chưa có composer.json
composer init -n

# Cài thư viện
composer require phpmailer/phpmailer
composer require chillerlan/php-qrcode
```

### Bước 2: Tạo Database (1 min)
```bash
# Copy content của file database/add_qr_payment_table.sql
# Paste vào phpMyAdmin hoặc run qua command:
mysql -u root -p shop_db < database/add_qr_payment_table.sql

# Hoặc tạo thư mục uploads/qr_codes
mkdir -p uploads/qr_codes
chmod 755 uploads/qr_codes
```

### Bước 3: Cấu hình Email (2 min)

**Lấy Gmail App Password:**
1. Vào https://myaccount.google.com/apppasswords
2. Device: Windows Computer
3. App: Mail
4. Copy password

**Cập nhật file:**
- Mở: `backend/cau_hinh/hang_so.php`
- Tìm: `define('SMTP_PASS', '')`
- Thay: `define('SMTP_PASS', 'YOUR_16_DIGIT_PASSWORD')`

### Bước 4: Tích hợp vào Flow (1 min)

**Sửa file:** `frontend/trang/gio_hang/gio_hang.html`

Thêm sau PayPal option:
```html
<!-- QR Code Payment -->
<label class="payment-option">
  <input type="radio" name="payment_method" value="qr">
  <span class="payment-label">📱 Quét mã QR (Chuyển khoản)</span>
</label>
```

**Sửa file:** `frontend/js/trang/gio_hang.js`

Trong hàm `form?.addEventListener('submit')`, thêm trước `btn.disabled = false`:

```javascript
// Lấy phương thức thanh toán
const paymentMethod = form.payment_method?.value || 'cod';

// Thêm vào payload
payload.payment_method = paymentMethod;

// ... sau khi result.success ...
if (result.success) {
  GioHang.luu([]);
  render();

  // ✅ THÊM BLOCK NÀY:
  if (paymentMethod === 'qr') {
    const total = GioHang.tongTien();
    const params = new URLSearchParams({
      order_id: result.data?.order_id || 0,
      customer_email: form.email.value.trim(),
      customer_name: form.ho_ten.value.trim(),
      amount: total,
    });
    window.location.href = `../thanh_toan_qr/thanh_toan_qr.html?${params.toString()}`;
    return;
  }

  // ... rest of code ...
}
```

---

## ✅ Test ngay

### Test 1: Trang QR Payment
```
URL: http://localhost/luxurious-fashion-store/frontend/trang/thanh_toan_qr/thanh_toan_qr.html?order_id=1&customer_email=test@test.com&customer_name=Test&amount=500000

Kỳ vọng:
✓ Hiển thị thông tin đơn hàng
✓ Hiển thị mã QR (hoặc loading)
✓ Hiển thị thông tin ngân hàng
```

### Test 2: Admin Dashboard
```
URL: http://localhost/luxurious-fashion-store/frontend/admin/qr_payment/index.html

Kỳ vọng:
✓ Hiển thị admin dashboard
✓ Stats: Chờ duyệt (0), Đã duyệt (0)
✓ Tab buttons để chuyển tab
✓ Bảng danh sách (nếu có dữ liệu)
```

### Test 3: End-to-end
```
1. Vào trang giỏ hàng: /frontend/trang/gio_hang/gio_hang.html
2. Thêm sản phẩm
3. Điền form + Chọn "📱 Quét mã QR"
4. Click "Đặt hàng"
5. Nên redirect tới trang QR payment
6. Kiểm tra URL có params: ?order_id=...&customer_email=...
```

---

## 📧 Email Config

### Nếu sử dụng Gmail:

**Setup 2FA + App Password:**
1. https://myaccount.google.com/security → Enable 2-Step Verification
2. https://myaccount.google.com/apppasswords → Create app password
3. Copy 16-digit password → Paste vào `hang_so.php`

**Test gửi email:**
```php
<?php
require_once 'backend/helpers/email_helper.php';

try {
    $emailHelper = new EmailHelper();
    echo "Email helper loaded successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```

---

## 🎯 Các file quan trọng

### Backend (Tạo mới):
```
✅ backend/model/qr_payment.php
✅ backend/api/qr_payment_api.php
✅ backend/helpers/qr_helper.php
✅ backend/helpers/email_helper.php
✅ database/add_qr_payment_table.sql
```

### Frontend (Tạo mới):
```
✅ frontend/trang/thanh_toan_qr/thanh_toan_qr.html
✅ frontend/admin/qr_payment/index.html
```

### Sửa:
```
📝 backend/cau_hinh/hang_so.php (thêm SMTP config)
📝 frontend/trang/gio_hang/gio_hang.html (thêm QR option)
📝 frontend/js/trang/gio_hang.js (thêm redirect logic)
```

---

## 🐛 Troubleshooting

| Problem | Fix |
|---------|-----|
| Composer not found | Install from https://getcomposer.org |
| QR not showing | Check uploads/qr_codes folder exists & writable |
| Email not sending | Check SMTP config + Gmail app password |
| Redirect fails | Check URL syntax in gio_hang.js |
| Admin dashboard blank | Check database table created |
| 404 errors | Check folder paths correct |

---

## 📚 Documentation

- **Full Setup:** `QR_PAYMENT_SETUP.md`
- **Integration:** `QR_PAYMENT_INTEGRATION.md`
- **Components:** `QR_PAYMENT_COMPONENTS.md` (this file)

---

## 🎉 Hoàn tất!

Sau khi hoàn tất 4 bước trên, hệ thống QR Payment đã sẵn sàng!

**Next Steps:**
1. Test flow đầu-đến-cuối
2. Customize email templates nếu cần
3. Thêm admin authentication
4. Deploy to production

---

**Cần giúp?** Check logs:
```bash
# Backend errors
tail -f backend/logs/db_errors.log

# Browser console (F12)
# Check Network tab cho API calls
```

Happy coding! 🚀
