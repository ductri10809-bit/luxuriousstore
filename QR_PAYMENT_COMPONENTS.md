# 📦 QR Payment System - Tóm tắt thành phần

## 🎯 Mục đích

Hệ thống QR Payment cho phép khách hàng thanh toán bằng cách quét mã QR hoặc chuyển khoản thủ công. Admin nhận email thông báo và duyệt thanh toán trực tiếp từ dashboard.

---

## 📁 Cấu trúc Files

### Backend

#### 1. **Model Layer** (`backend/model/`)
```
qr_payment.php
├── Quản lý database table qr_payments
├── CRUD operations cho QR payment
├── Hàm: tao(), layTheoOrder(), xacNhanThanhToan(), etc.
└── Liên kết: orders table
```

#### 2. **API Layer** (`backend/api/`)
```
qr_payment_api.php
├── REST API endpoints:
│   ├── action=create → Tạo QR mới
│   ├── action=get → Lấy chi tiết QR
│   ├── action=approve → Admin duyệt
│   ├── action=reject → Admin từ chối
│   └── action=list-pending → Danh sách chờ duyệt
└── JSON responses
```

#### 3. **Helper Layer** (`backend/helpers/`)
```
qr_helper.php
├── Tạo QR code từ dữ liệu
├── Lưu ảnh QR vào file
├── Xác minh QR code data
└── Trả về base64 hoặc URL

email_helper.php
├── PHPMailer configuration
├── Gửi email admin (khi quét QR)
├── Gửi email khách (khi duyệt)
├── Email templates (HTML)
└── Xử lý lỗi email
```

#### 4. **Config** (`backend/cau_hinh/`)
```
hang_so.php (sửa)
├── ADMIN_EMAIL = 'ductri10809@gmail.com'
├── SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS
└── APP_NAME, SMTP_FROM_EMAIL, etc.
```

#### 5. **Database** (`database/`)
```
add_qr_payment_table.sql
├── Table qr_payments
├── Columns: id, order_id, qr_code_data, status, etc.
├── Foreign keys: order_id → orders
└── Indexes: idx_order_id, idx_status, idx_created_at
```

### Frontend

#### 1. **Trang Thanh toán QR** (`frontend/trang/thanh_toan_qr/`)
```
thanh_toan_qr.html
├── Layout:
│   ├── Header (tiêu đề)
│   ├── Thông tin đơn hàng
│   ├── Mã QR hiển thị
│   ├── Hướng dẫn thanh toán
│   └── Thông tin ngân hàng
├── Features:
│   ├── Tạo QR code via API
│   ├── Hiển thị thông tin chuyển khoản
│   ├── Download QR code
│   └── Responsive design
└── JavaScript: Xử lý tạo QR, format tiền, etc.
```

#### 2. **Admin Dashboard** (`frontend/admin/qr_payment/`)
```
index.html
├── Layout:
│   ├── Header (tiêu đề)
│   ├── Stats (chờ duyệt, đã duyệt)
│   ├── Tab buttons (chuyển tab)
│   ├── Table danh sách
│   ├── Modal chi tiết
│   └── Action buttons
├── Features:
│   ├── Xem danh sách chờ duyệt
│   ├── Xem danh sách đã duyệt
│   ├── Modal chi tiết thanh toán
│   ├── Nút "Duyệt" và "Từ chối"
│   ├── Auto-refresh stats
│   └── Responsive design
└── JavaScript: Fetch API, DOM manipulation, etc.
```

#### 3. **Tích hợp Giỏ hàng** (`frontend/trang/gio_hang/`)
```
gio_hang.html (sửa)
├── Thêm option "📱 Quét mã QR" vào payment methods
├── Grouped với "Chuyển khoản ngân hàng"
└── Radio button integration

gio_hang.js (sửa)
├── Thêm logic redirect khi chọn QR
├── Gửi payment_method tới backend
├── Redirect với params: order_id, customer_email, amount
└── Query string parsing
```

#### 4. **Payment Handler** (`frontend/js/trang/`)
```
payment.js (sửa)
├── Hiển thị/ẩn bank options khi chọn bank transfer
├── Xử lý QR option
└── Radio button event listeners
```

---

## 🔄 Data Flow

### Scenario 1: Khách hàng tạo QR Payment

```
1. Customer selects "📱 Quét mã QR"
   ↓
2. Form submit → Backend creates order
   ↓
3. Frontend redirects to thanh_toan_qr.html with params:
   - order_id=123
   - customer_email=customer@example.com
   - customer_name=Khách hàng
   - amount=500000
   ↓
4. thanh_toan_qr.html:
   - Parses URL params
   - Calls API: /backend/api/qr_payment_api.php?action=create
   - POST: order_id, customer_email
   ↓
5. Backend:
   - Creates QR data (JSON)
   - Generates QR image (PNG)
   - Saves to qr_payments table
   - Returns: qr_image (base64), qr_image_url
   ↓
6. Frontend:
   - Displays QR code image
   - Shows bank info
   - User can scan or manual transfer
```

### Scenario 2: Admin Duyệt Thanh toán

```
1. (Optional) Customer scans QR → Webhook/notification to admin

2. Admin navigates to /frontend/admin/qr_payment/index.html
   ↓
3. Page loads:
   - Calls API: /backend/api/qr_payment_api.php?action=list-pending
   - Fetches pending QR payments
   - Displays in table
   ↓
4. Admin clicks "Xem chi tiết" or "Duyệt":
   - Opens modal with payment details
   - Shows: order ID, customer, amount, timestamp
   ↓
5. Admin clicks "Duyệt thanh toán":
   - Calls API: /backend/api/qr_payment_api.php?action=approve&id=456
   ↓
6. Backend:
   - Updates qr_payments: status = "confirmed"
   - Updates orders: status = "da_thanh_toan"
   - Sends email to customer (via EmailHelper)
   - Marks customer as notified
   ↓
7. Frontend:
   - Shows success message
   - Refreshes list
   - QR payment moves from "Chờ duyệt" to "Đã duyệt"
   ↓
8. Customer receives email:
   - Confirmation: "Thanh toán thành công"
   - Order details + amount
   - Thank you message
```

---

## 💾 Database Schema

### Table: `qr_payments`

```sql
CREATE TABLE qr_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    qr_code_data LONGTEXT NOT NULL,    -- JSON: {admin_email, customer_email, order_id, amount}
    qr_image_path VARCHAR(255),        -- /uploads/qr_codes/qr_123.png
    admin_email VARCHAR(255),          -- ductri10809@gmail.com
    customer_email VARCHAR(255),       -- customer@example.com
    bank_account VARCHAR(50),          -- Optional: which bank used
    transaction_status VARCHAR(50),    -- pending, confirmed, rejected
    admin_confirmed_at TIMESTAMP NULL, -- When admin approved
    customer_notified_at TIMESTAMP NULL, -- When customer was notified
    amount DECIMAL(15, 2),             -- Payment amount
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_status (transaction_status),
    INDEX idx_created_at (created_at)
);
```

---

## 🔐 Security Considerations

1. **API Validation:**
   - Verify order_id exists
   - Match customer_email with order
   - Validate amount

2. **Admin Authentication:**
   - Dashboard should require admin login
   - Add middleware check before approve/reject

3. **Email Security:**
   - Use OAuth 2.0 instead of app passwords (future)
   - Log all approval actions
   - Implement rate limiting on API

4. **QR Code:**
   - Add signature/hash to QR data
   - Verify data integrity
   - TTL for QR codes (optional)

---

## 🚀 Performance Optimization

1. **Caching:**
   - Cache pending count (Redis)
   - Cache admin stats

2. **Database:**
   - Add indexes on foreign keys
   - Archive old payments

3. **Email:**
   - Queue email sending (async job)
   - Batch email send

4. **Frontend:**
   - Lazy load modal content
   - Virtual scrolling for large lists

---

## 🧪 Testing Checklist

- [ ] Create order → Select QR option → Redirect works
- [ ] QR code generates correctly
- [ ] Admin dashboard loads data
- [ ] Approve button sends email
- [ ] Customer receives email
- [ ] Email format is correct (both templates)
- [ ] SMS/SMTP errors handled gracefully
- [ ] Mobile responsiveness works
- [ ] Bank info displays correctly
- [ ] Download QR button works

---

## 📊 API Endpoints Reference

```
POST /backend/api/qr_payment_api.php?action=create
  Payload: order_id, customer_email
  Response: { success, data: { qr_payment_id, qr_image, qr_image_url } }

GET /backend/api/qr_payment_api.php?action=get&id=456
  Response: { success, data: { ...qr_payment_data } }

GET /backend/api/qr_payment_api.php?action=approve&id=456
  Response: { success, message: "..." }

GET /backend/api/qr_payment_api.php?action=reject&id=456
  Response: { success, message: "..." }

GET /backend/api/qr_payment_api.php?action=list-pending&page=1&limit=20
  Response: { success, data: { list, total, page, limit } }
```

---

## 📞 Support & Debugging

**Enable Debug Mode:**
```php
// In hang_so.php
define('DEBUG_MODE', true);
define('LOG_PATH', __DIR__ . '/../../logs/');
```

**Check Logs:**
```bash
tail -f backend/logs/db_errors.log
tail -f backend/logs/email_errors.log
```

**Test Email:**
```php
$email = new EmailHelper();
$email->guiThongBaoAdminQR($qrData, $orderData, $paymentData);
```

---

**Version:** 1.0.0  
**Created:** 2024  
**Last Updated:** 2024-06-03
