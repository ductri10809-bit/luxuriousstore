# 🎉 QR Payment System - Hệ thống Thanh toán QR Code

> **Tạo bởi:** Copilot  
> **Ngày:** 2024-06-03  
> **Version:** 1.0.0

---

## 📋 Tổng Quan

### Mục đích chính:
Hệ thống QR Payment cho phép khách hàng thanh toán chuyển khoản ngân hàng bằng cách quét mã QR hoặc nhập thông tin thủ công. Admin nhận email thông báo và duyệt thanh toán trực tiếp từ dashboard.

### Quy trình:
```
Khách chọn QR Payment
    ↓
Nhập thông tin + Click "Đặt hàng"
    ↓
Redirect tới trang QR (hiển thị mã + thông tin ngân hàng)
    ↓
Khách quét QR hoặc chuyển khoản thủ công
    ↓
Admin nhận email → Xem dashboard → Duyệt thanh toán
    ↓
Khách nhận email xác nhận thành công
```

---

## 🚀 Cài đặt Nhanh (5 phút)

Xem file: **`QUICK_START.md`**

```bash
# 1. Cài thư viện
composer require phpmailer/phpmailer chillerlan/php-qrcode

# 2. Tạo database
mysql -u root -p shop_db < database/add_qr_payment_table.sql

# 3. Cấu hình email
# Sửa: backend/cau_hinh/hang_so.php
define('SMTP_PASS', 'YOUR_APP_PASSWORD');

# 4. Tích hợp vào flow thanh toán
# Sửa: frontend/trang/gio_hang/gio_hang.html
# Sửa: frontend/js/trang/gio_hang.js

# 5. Test!
# Vào: http://localhost/luxurious-fashion-store/frontend/trang/gio_hang/gio_hang.html
```

---

## 📁 Cấu trúc Files

### Backend:
```
backend/
├── model/
│   └── qr_payment.php           ✅ Model QR payments
├── api/
│   └── qr_payment_api.php        ✅ REST API endpoints
├── helpers/
│   ├── qr_helper.php             ✅ Tạo QR code
│   └── email_helper.php          ✅ Gửi email
├── cau_hinh/
│   └── hang_so.php               📝 SMTP config (sửa)
└── logs/
    └── (auto-create)

database/
└── add_qr_payment_table.sql      ✅ Migration
```

### Frontend:
```
frontend/
├── trang/
│   ├── thanh_toan_qr/
│   │   └── thanh_toan_qr.html    ✅ QR payment page
│   └── gio_hang/
│       ├── gio_hang.html          📝 Thêm QR option
│       └── (JS files)
├── admin/
│   └── qr_payment/
│       └── index.html             ✅ Admin dashboard
└── js/
    └── trang/
        ├── gio_hang.js            📝 Thêm redirect logic
        └── payment.js
```

### Docs:
```
├── QUICK_START.md                ⚡ Setup nhanh 5 phút
├── QR_PAYMENT_SETUP.md           📖 Setup chi tiết
├── QR_PAYMENT_INTEGRATION.md     🔗 Tích hợp guide
├── QR_PAYMENT_COMPONENTS.md      📦 Chi tiết thành phần
└── README.md                      📄 File này
```

---

## 🎯 Features

### Cho Khách hàng:
- ✅ Chọn phương thức "Quét mã QR"
- ✅ Xem mã QR + thông tin chuyển khoản
- ✅ Quét QR từ app ngân hàng
- ✅ Hoặc chuyển khoản thủ công
- ✅ Nhận email xác nhận thanh toán
- ✅ Responsive trên mobile + desktop

### Cho Admin:
- ✅ Dashboard xem danh sách thanh toán chờ duyệt
- ✅ Xem chi tiết từng thanh toán
- ✅ Duyệt hoặc từ chối thanh toán
- ✅ Tự động gửi email cho khách
- ✅ Lịch sử thanh toán đã duyệt
- ✅ Stats: số chờ duyệt, đã duyệt

---

## 🔧 Cấu hình

### SMTP Email:
```php
// File: backend/cau_hinh/hang_so.php

define('ADMIN_EMAIL', 'ductri10809@gmail.com');
define('APP_NAME', 'Luxurious Fashion Store');

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'ductri10809@gmail.com');
define('SMTP_PASS', ''); // ← Paste app password here
define('SMTP_FROM_EMAIL', 'ductri10809@gmail.com');
define('SMTP_FROM_NAME', 'Luxurious Fashion Store');
```

### Ngân hàng:
```html
<!-- File: frontend/trang/thanh_toan_qr/thanh_toan_qr.html -->
<!-- Sửa phần "Thông tin chuyển khoản" -->

<div class="bank-item">
  <strong>🏦 Ngân hàng Techcombank</strong>
  <span><strong>Chủ tài khoản:</strong> DUC TRI</span>
  <span><strong>Số tài khoản:</strong> 0108210816</span>
</div>
```

---

## 📱 URLs Chính

```
Trang QR Payment:
http://localhost/luxurious-fashion-store/frontend/trang/thanh_toan_qr/thanh_toan_qr.html

Admin Dashboard:
http://localhost/luxurious-fashion-store/frontend/admin/qr_payment/index.html

Giỏ hàng (với QR option):
http://localhost/luxurious-fashion-store/frontend/trang/gio_hang/gio_hang.html
```

---

## 💾 Database

### Table: `qr_payments`
```sql
- id (Primary Key)
- order_id (FK → orders)
- qr_code_data (JSON data: {admin_email, customer_email, order_id, amount})
- qr_image_path (URL: /uploads/qr_codes/qr_123.png)
- admin_email (Email admin)
- customer_email (Email khách)
- bank_account (Ngân hàng được sử dụng)
- transaction_status (pending, confirmed, rejected)
- admin_confirmed_at (Timestamp khi admin duyệt)
- customer_notified_at (Timestamp khi gửi email)
- amount (Số tiền)
- created_at, updated_at
```

---

## 🔄 API Endpoints

```
POST /backend/api/qr_payment_api.php?action=create
  → Tạo QR code mới

GET /backend/api/qr_payment_api.php?action=get&id=456
  → Lấy chi tiết QR payment

GET /backend/api/qr_payment_api.php?action=approve&id=456
  → Admin duyệt thanh toán

GET /backend/api/qr_payment_api.php?action=reject&id=456
  → Admin từ chối thanh toán

GET /backend/api/qr_payment_api.php?action=list-pending
  → Danh sách chờ duyệt
```

---

## 📧 Email Templates

### 1. Email cho Admin (khi khách quét QR):
```
Subject: [Store Name] Thông báo thanh toán mới - Đơn #123
Body:
- Thông báo thanh toán
- Chi tiết đơn hàng: mã, khách, email, tiền
- Nút "Xác nhận và duyệt thanh toán"
```

### 2. Email cho Khách (khi admin duyệt):
```
Subject: [Store Name] Thanh toán thành công - Đơn #123
Body:
- Xác nhận thanh toán thành công
- Chi tiết đơn hàng
- Ngân hàng sử dụng
- Lời cảm ơn
```

---

## ✅ Checklist Cài đặt

- [ ] Cài đặt Composer + PHP libraries
- [ ] Tạo table database
- [ ] Tạo thư mục uploads/qr_codes
- [ ] Cấu hình SMTP email
- [ ] Lấy Gmail app password
- [ ] Thêm QR option vào gio_hang.html
- [ ] Cập nhật gio_hang.js (redirect logic)
- [ ] Test: tạo đơn hàng → QR page
- [ ] Test: admin dashboard xem QR
- [ ] Test: duyệt thanh toán → email gửi
- [ ] Customize email templates (nếu cần)
- [ ] Deploy to production

---

## 🧪 Testing

### Test 1: QR Code Generation
```
GET http://localhost/luxurious-fashion-store/frontend/trang/thanh_toan_qr/thanh_toan_qr.html?order_id=1&customer_email=test@test.com&customer_name=Test&amount=500000

Expected:
✓ QR code hiển thị
✓ Thông tin đơn hàng đúng
✓ Thông tin ngân hàng đúng
```

### Test 2: Admin Dashboard
```
GET http://localhost/luxurious-fashion-store/frontend/admin/qr_payment/index.html

Expected:
✓ Dashboard load
✓ Stats hiển thị
✓ Tab buttons work
✓ Table list QR payments (nếu có data)
```

### Test 3: Approval Flow
```
1. Admin xem QR pending
2. Click "Xem chi tiết"
3. Click "Duyệt thanh toán"
4. Check email customer nhận được

Expected:
✓ API approve thành công
✓ Email gửi đến customer
✓ QR status → confirmed
✓ Order status → da_thanh_toan
```

---

## 🆘 Troubleshooting

| Problem | Solution |
|---------|----------|
| QR code không hiển thị | Check `uploads/qr_codes` folder exists + writable. Check PHP library cài đặt. |
| Email không gửi | Check SMTP config + app password. Check Gmail SMTP enabled. |
| Admin dashboard trống | Check `qr_payments` table created. Check browser console. |
| Redirect không hoạt động | Check URL syntax. Check query string. |
| API error 500 | Check PHP logs. Check database connection. |

---

## 📚 Documentation Files

| File | Nội dung |
|------|---------|
| **QUICK_START.md** | Setup nhanh 5 phút |
| **QR_PAYMENT_SETUP.md** | Hướng dẫn cài đặt chi tiết |
| **QR_PAYMENT_INTEGRATION.md** | Cách tích hợp vào flow thanh toán |
| **QR_PAYMENT_COMPONENTS.md** | Tóm tắt chi tiết thành phần |
| **README.md** | File này |

---

## 🔐 Bảo mật

1. **API Validation:**
   - Verify order_id tồn tại
   - Match customer_email với order
   - Validate amount

2. **Authentication:**
   - Admin dashboard nên require đăng nhập
   - Thêm middleware check

3. **Email Security:**
   - Không expose admin email công khai
   - Log tất cả approval actions
   - Implement rate limiting

4. **QR Code:**
   - Optional: add hash signature
   - Optional: add TTL (time to live)

---

## 🚀 Future Enhancements

- [ ] Support multiple admin emails
- [ ] Webhook notifications from banks
- [ ] SMS notifications
- [ ] Payment analytics + reports
- [ ] Automatic payment verification (API integration)
- [ ] Multi-language support
- [ ] OAuth 2.0 for email instead of app passwords
- [ ] Payment history export (CSV, PDF)

---

## 📞 Support

Nếu có vấn đề:
1. Check logs: `backend/logs/`
2. Check browser console (F12)
3. Check network tab (API calls)
4. Review error messages

---

## 📄 License

Tạo cho: Luxurious Fashion Store  
Copilot AI Assistant  
2024

---

## 🎉 Chúc mừng!

Bạn đã có một hệ thống QR Payment hoàn chỉnh!

**Bước tiếp theo:**
1. Setup theo QUICK_START.md
2. Test end-to-end
3. Customize theo nhu cầu
4. Deploy to production

Happy coding! 🚀
