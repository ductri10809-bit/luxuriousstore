# 🚀 DEPLOYMENT GUIDE - QR PAYMENT SYSTEM

## Bước 1: Chuẩn Bị Cơ Sở Dữ Liệu

```bash
# Chạy 2 migration files
mysql -u root shop_db < database/add_qr_payment_table.sql
mysql -u root shop_db < database/add_payment_method.sql

# Hoặc trong phpMyAdmin: 
# - Import add_qr_payment_table.sql
# - Import add_payment_method.sql
```

**Verify**: 
```sql
SHOW COLUMNS FROM orders; -- Kiểm tra có payment_method column
SHOW TABLES LIKE 'qr_payments'; -- Kiểm tra bảng tồn tại
DESC qr_payments; -- Xem chi tiết schema
```

---

## Bước 2: Cài Đặt Composer Dependencies

```bash
cd d:\hiii\htdocs\luxurious-fashion-store

# Cài PHPMailer
composer require phpmailer/phpmailer

# Cài QR Code Library
composer require chillerlan/php-qrcode

# Verify
ls vendor/
```

---

## Bước 3: Cấu Hình Email (CRITICAL)

**File**: `backend/cau_hinh/hang_so.php`

```php
// Line ~10 (kiểm tra & cập nhật)
define('ADMIN_EMAIL', 'ductri10809@gmail.com');

// SMTP Configuration (tìm và cập nhật)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'ductri10809@gmail.com');
define('SMTP_PASS', 'YOUR_APP_PASSWORD'); // ← THAY ĐỔI ĐÂY
define('SMTP_FROM_EMAIL', 'ductri10809@gmail.com');
define('SMTP_FROM_NAME', 'Luxurious Fashion Store');
```

**⚠️ CÁCH LẤY APP PASSWORD:**
1. Vào https://myaccount.google.com/
2. Security → 2-Step Verification (phải enable)
3. App passwords → Select app (Mail) → Device (Windows/Mac)
4. Copy password → Paste vào `SMTP_PASS`

---

## Bước 4: Kiểm Tra Directory Permissions

```bash
# Linux/Mac
chmod 755 uploads/qr_codes/
chmod 755 backend/logs/

# Windows (sử dụng Properties → Security)
# - uploads/qr_codes/ → Full Control
# - backend/logs/ → Modify
```

---

## Bước 5: Test Tất Cả Endpoints

### Test 1: Tạo Order với Payment Method

```bash
# Via curl hoặc Postman
POST http://localhost/luxurious-fashion-store/backend/api/dat_hang.php

Body (JSON):
{
  "ho_ten": "Nguyễn Văn A",
  "sdt": "0123456789",
  "email": "customer@example.com",
  "dia_chi": "123 Main St",
  "payment_method": "qr_code",
  "items": [
    {
      "product_id": 1,
      "gia": 1000000,
      "so_luong": 2
    }
  ]
}

# Expected Response:
{
  "success": true,
  "data": {
    "order_id": 1,
    "tong_tien": 2000000
  }
}
```

### Test 2: Auto-Create QR Record

```bash
# Via curl
GET http://localhost/luxurious-fashion-store/backend/api/qr_payment_api.php?action=auto-create&order_id=1&customer_email=customer@example.com

# Expected Response:
{
  "success": true,
  "data": {
    "qr_payment_id": 1,
    "qr_image": "data:image/png;base64,...",
    "qr_image_url": "/luxurious-fashion-store/uploads/qr_codes/..."
  }
}
```

### Test 3: Check QR Payment Created

```sql
SELECT * FROM qr_payments WHERE order_id = 1;

-- Kiểm tra:
-- id: 1
-- order_id: 1
-- admin_email: ductri10809@gmail.com
-- customer_email: customer@example.com
-- transaction_status: pending
```

---

## Bước 6: Test Frontend Flow

### Test Payment Selection & Order Creation

1. **Vào giỏ hàng page**: `frontend/trang/gio_hang/gio_hang.html`
2. **Thêm sản phẩm** (nếu chưa)
3. **Chọn payment method**: "Mã QR Chuyển khoản"
4. **Điền form**: Họ tên, SĐT, email, địa chỉ
5. **Click "Đặt hàng"**
6. **Kiểm tra**:
   - Redirect đến `thanh_toan_qr.html` ✓
   - QR code hiển thị ✓
   - Thông tin ngân hàng hiển thị ✓
   - localStorage có qr_payment_data ✓

### Test QR Payment Page

- URL: `http://localhost/luxurious-fashion-store/frontend/trang/thanh_toan_qr/thanh_toan_qr.html`
- QR code visible ✓
- Bank info showing ✓
- Can download QR ✓

### Test Admin Dashboard

- URL: `http://localhost/luxurious-fashion-store/frontend/admin/qr_payment/index.html`
- Click tab "Chờ duyệt" (Pending)
- See order ✓
- Click "Xem chi tiết"
- Modal shows ✓
- Click "Duyệt" (Approve)
- Email sent to customer ✓
- Status changes to "Đã duyệt" ✓

---

## Bước 7: Email Testing

### Test Admin Email

1. Open admin dashboard
2. Click order → Click "Duyệt"
3. Check email: `ductri10809@gmail.com`
4. Email content:
   - Order details ✓
   - Amount ✓
   - Customer info ✓
   - Approval link ✓

### Test Customer Email

1. Click approval link từ admin email
2. Check customer email
3. Email content:
   - "Thanh toán thành công" ✓
   - Order details ✓
   - Amount confirmed ✓

---

## 🔍 Troubleshooting

### Problem: QR không hiển thị

```
→ Check browser console (F12)
→ Look for API error messages
→ Verify order_id & customer_email passed
→ Check uploads/qr_codes/ exists & writable
```

### Problem: Email không gửi

```
→ Check SMTP_PASS đúng (app password, không password gmail)
→ Check 2FA enabled in Gmail
→ Test: Check backend/logs/error_log.txt
→ Verify ADMIN_EMAIL constant
```

### Problem: Payment method không lưu

```
→ Check database migration ran
→ Verify payment_method column exists: DESC orders;
→ Check form has name="payment_method"
→ Check gio_hang.js captures value correctly
```

### Problem: Duplicate QR Records

```
→ This is prevented by auto-create endpoint
→ Check line 265-272 in qr_payment_api.php
→ If issue persists, check qr_payments table for duplicates
```

---

## ✅ Final Verification Checklist

- [ ] Both migrations imported
- [ ] Composer dependencies installed
- [ ] SMTP_PASS configured (app password, not gmail password)
- [ ] uploads/qr_codes/ is writable
- [ ] Test order creation works
- [ ] QR code generates
- [ ] Admin dashboard shows pending payments
- [ ] Admin approval sends email
- [ ] Customer receives confirmation email

---

## 📞 Quick Reference

| Component | File | Status |
|-----------|------|--------|
| Database Schema | add_qr_payment_table.sql + add_payment_method.sql | ✓ Ready |
| Backend Model | backend/model/don_hang.php | ✓ Updated |
| Backend Controller | backend/controller/don_hang_controller.php | ✓ Updated |
| Backend API | backend/api/qr_payment_api.php | ✓ Ready |
| Email Helper | backend/helpers/email_helper.php | ✓ Ready |
| QR Helper | backend/helpers/qr_helper.php | ✓ Ready |
| Frontend Form | frontend/trang/gio_hang/gio_hang.html | ✓ Updated |
| Frontend JS | frontend/js/trang/gio_hang.js | ✓ Updated |
| QR Page | frontend/trang/thanh_toan_qr/thanh_toan_qr.html | ✓ Ready |
| Admin Dashboard | frontend/admin/qr_payment/index.html | ✓ Updated |

---

## 🎉 Production Ready!

Tất cả các bước hoàn tất. System sẵn sàng deploy! 🚀
