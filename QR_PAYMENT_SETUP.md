# 🎉 Hệ thống QR Code Thanh toán - Luxurious Fashion Store

## 📋 Tổng quan

Hệ thống QR code thanh toán cho phép khách hàng quét mã QR để thanh toán chuyển khoản ngân hàng. Admin sẽ nhận được email thông báo và có thể duyệt thanh toán trực tiếp từ dashboard.

### Quy trình:
1. Khách hàng chọn phương thức thanh toán "Chuyển khoản ngân hàng"
2. Hệ thống hiển thị mã QR + thông tin chuyển khoản
3. Khách hàng quét QR bằng app ngân hàng
4. Admin nhận email thông báo với nút duyệt
5. Sau khi duyệt, khách hàng nhận email xác nhận thanh toán thành công

---

## 🚀 Cài đặt

### Bước 1: Cài đặt thư viện PHP cần thiết

```bash
# Vào thư mục project root
cd /luxurious-fashion-store

# Cài đặt PHPMailer (để gửi email)
composer require phpmailer/phpmailer

# Cài đặt PHP QRCode library
composer require chillerlan/php-qrcode
```

### Bước 2: Tạo các thư mục cần thiết

```bash
# Thư mục lưu ảnh QR code
mkdir -p uploads/qr_codes
chmod 755 uploads/qr_codes
```

### Bước 3: Tạo database table

Chạy SQL migration file:
```sql
-- File: database/add_qr_payment_table.sql
-- Import vào database shop_db
```

Hoặc chạy qua MySQL:
```bash
mysql -u root -p shop_db < database/add_qr_payment_table.sql
```

### Bước 4: Cấu hình Email (Gmail)

1. **Bật 2-Factor Authentication trên Gmail:**
   - Truy cập: https://myaccount.google.com/security
   - Bật "2-Step Verification"

2. **Tạo App Password:**
   - Truy cập: https://myaccount.google.com/apppasswords
   - Device: Windows Computer
   - App: Mail
   - Copy password

3. **Cập nhật cấu hình:**
   - Mở file: `backend/cau_hinh/hang_so.php`
   - Tìm phần SMTP config
   - Thay thế:
   ```php
   define('SMTP_PASS', 'YOUR_APP_PASSWORD_HERE'); // Paste app password
   ```

### Bước 5: Kiểm tra các file đã tạo

```
✓ backend/model/qr_payment.php
✓ backend/api/qr_payment_api.php
✓ backend/helpers/qr_helper.php
✓ backend/helpers/email_helper.php
✓ frontend/trang/thanh_toan_qr/thanh_toan_qr.html
✓ frontend/admin/qr_payment/index.html
✓ database/add_qr_payment_table.sql
```

---

## 📱 Cách sử dụng

### Cho Khách hàng:

1. **Chọn thanh toán chuyển khoản:**
   - Tại trang giỏ hàng → Chọn "Chuyển khoản ngân hàng"
   - Redirect đến: `/frontend/trang/thanh_toan_qr/thanh_toan_qr.html`

2. **Quét mã QR:**
   - Mở app ngân hàng (Techcombank, VPBank, etc.)
   - Chọn "Quét QR"
   - Quét mã hiển thị trên màn hình
   - Kiểm tra thông tin → Xác nhận thanh toán

3. **Nhận email xác nhận:**
   - Sau khi admin duyệt (trong vòng 10 phút)
   - Khách sẽ nhận email xác nhận thanh toán thành công

### Cho Admin:

1. **Truy cập Dashboard:**
   - URL: `/frontend/admin/qr_payment/index.html`
   - Hoặc click menu "Quản lý QR Payment" (nếu có)

2. **Duyệt thanh toán:**
   - Xem danh sách "Chờ duyệt"
   - Click "Xem chi tiết" → Review thông tin
   - Click "Duyệt thanh toán"
   - Khách hàng sẽ tự động nhận email

3. **Lịch sử duyệt:**
   - Tab "Đã duyệt" → Xem tất cả thanh toán đã xác nhận

---

## 🔧 Cấu hình ngân hàng

Chỉnh sửa file `frontend/trang/thanh_toan_qr/thanh_toan_qr.html`:

Tìm phần "Thông tin chuyển khoản" và cập nhật:
```html
<div class="bank-item">
    <strong>🏦 Ngân hàng Techcombank</strong>
    <span><strong>Chủ tài khoản:</strong> YOUR_NAME</span>
    <span><strong>Số tài khoản:</strong> YOUR_ACCOUNT_NUMBER</span>
</div>
```

---

## 📧 Email Templates

Email được gửi bằng PHPMailer. Hai loại email:

### 1. Email cho Admin (khi khách quét QR):
- Tiêu đề: "[Store Name] Thông báo thanh toán mới - Đơn #123"
- Nội dung: Chi tiết thanh toán + Nút "Xác nhận"
- Gửi tới: `ductri10809@gmail.com`

### 2. Email cho Khách (khi admin duyệt):
- Tiêu đề: "[Store Name] Thanh toán thành công - Đơn #123"
- Nội dung: Xác nhận thanh toán + Chi tiết đơn hàng
- Gửi tới: Email của khách hàng

Để tùy chỉnh template, chỉnh sửa các hàm:
- `taoEmailAdminQR()` - Template email admin
- `taoEmailKhachThanhToanThanhCong()` - Template email khách

Trong file: `backend/helpers/email_helper.php`

---

## 🔐 Bảo mật

1. **Kiểm tra quyền Admin:**
   - Dashboard chỉ nên truy cập khi đăng nhập admin
   - Thêm middleware authentication nếu chưa có

2. **Bảo vệ API:**
   - Thêm CSRF token check
   - Validate email admin trước khi duyệt
   - Log tất cả hành động duyệt

3. **Mã hóa dữ liệu:**
   - QR code data được JSON encode
   - Nên thêm hash signature để xác minh

---

## 📊 Database Schema

### Table `qr_payments`:
```sql
- id (Primary Key)
- order_id (FK → orders)
- qr_code_data (JSON data)
- qr_image_path (URL ảnh QR)
- admin_email (Email admin)
- customer_email (Email khách)
- bank_account (Ngân hàng sử dụng)
- transaction_status (pending, confirmed, rejected)
- admin_confirmed_at (Thời gian duyệt)
- customer_notified_at (Thời gian gửi email)
- amount (Số tiền)
- created_at, updated_at
```

---

## 🆘 Troubleshooting

### Email không gửi được:
1. Kiểm tra Gmail app password có chính xác không
2. Kiểm tra GMail có enable SMTP không
3. Check file logs: `backend/logs/db_errors.log`

### QR code không hiển thị:
1. Kiểm tra thư viện `chillerlan/php-qrcode` đã cài đặt
2. Kiểm tra thư mục `uploads/qr_codes/` có write permission
3. Kiểm tra browser console có error không

### Admin dashboard trống:
1. Kiểm tra table `qr_payments` đã tạo trong database
2. Kiểm tra có dữ liệu trong table: `SELECT * FROM qr_payments;`

### Email template không đẹp:
- Email HTML phụ thuộc vào email client
- Test trên Gmail, Outlook, Yahoo để xác minh

---

## 🚀 Tối ưu hóa

### Để cải thiện performance:

1. **Thêm caching:**
   ```php
   // Tính số lượng pending
   $count = redis->get('qr_pending_count');
   if (!$count) {
       $count = $qrModel->demSoCho();
       redis->setex('qr_pending_count', 60, $count);
   }
   ```

2. **Batch email send:**
   - Sử dụng job queue (Laravel Queue, Bull, etc.)
   - Gửi email async thay vì sync

3. **Add pagination:**
   - Dashboard đã có pagination trong API
   - Thêm "Load more" button khi cần

---

## 📞 Support

Nếu có vấn đề, kiểm tra:
1. Logs trong `backend/logs/`
2. Browser DevTools (F12)
3. MySQL error logs

---

## 📄 License

Tạo bởi: Admin Panel - Luxurious Fashion Store  
Ngày: 2024  
Version: 1.0.0
