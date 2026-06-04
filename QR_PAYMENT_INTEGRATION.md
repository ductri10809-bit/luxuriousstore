# 🔗 Hướng dẫn tích hợp QR Payment vào Flow Thanh toán

## 📌 Tóm tắt

QR Payment đã được thiết kế để tích hợp vào flow thanh toán hiện tại. Bạn chỉ cần chỉnh sửa một số file để kích hoạt tính năng này.

---

## 🔧 Bước 1: Cập nhật payment.js

**File:** `frontend/js/trang/payment.js`

Thêm logic hiển thị QR payment option:

```javascript
/**
 * Payment methods handler
 */
document.addEventListener('DOMContentLoaded', () => {
  const paymentOptions = document.querySelectorAll('.payment-option input[name="payment_method"]');
  const bankOptions = document.querySelector('.bank-options');
  const qrOption = document.querySelector('input[name="payment_method"][value="qr"]'); // Thêm dòng này
  const bankRadios = document.querySelectorAll('input[name="bank_name"]');

  paymentOptions.forEach(option => {
    option.addEventListener('change', () => {
      const isBank = option.value === 'bank_transfer';
      const isQR = option.value === 'qr'; // Thêm dòng này
      
      if (bankOptions) {
        bankOptions.hidden = !isBank;
      }

      // Uncheck bank radios if not bank transfer
      if (!isBank) {
        bankRadios.forEach(radio => radio.checked = false);
      } else if (bankRadios.length > 0 && !Array.from(bankRadios).some(r => r.checked)) {
        bankRadios[0].checked = true;
      }
    });
  });

  // Bank card selection
  const bankItems = document.querySelectorAll('.bank-item');
  bankItems.forEach(item => {
    item.addEventListener('click', () => {
      const radio = item.querySelector('input[type="radio"]');
      radio.checked = true;
    });
  });

  // Auto-check first bank if bank transfer is selected on load
  const selectedPaymentMethod = Array.from(paymentOptions).find(o => o.checked)?.value;
  if (selectedPaymentMethod === 'bank_transfer' && bankRadios.length > 0 && !Array.from(bankRadios).some(r => r.checked)) {
    bankRadios[0].checked = true;
  }
});
```

---

## 🔧 Bước 2: Cập nhật gio_hang.html

**File:** `frontend/trang/gio_hang/gio_hang.html`

Thêm option QR Payment vào payment methods (sau PayPal):

```html
<!-- QR Code Payment (NEW) -->
<label class="payment-option">
  <input type="radio" name="payment_method" value="qr">
  <span class="payment-label">
    📱 Quét mã QR (Chuyển khoản)
  </span>
</label>
```

Full context:
```html
<div class="form-group payment-methods">
  <label>Phương thức thanh toán</label>
  <div class="payment-options">
    <!-- COD -->
    <label class="payment-option">
      <input type="radio" name="payment_method" value="cod" checked>
      <span class="payment-label">Thanh toán khi nhận hàng (COD)</span>
    </label>

    <!-- Bank Transfer -->
    <label class="payment-option payment-group">
      <input type="radio" name="payment_method" value="bank_transfer">
      <span class="payment-label">Chuyển khoản ngân hàng</span>
      <!-- ... existing bank options ... -->
    </label>

    <!-- QR Code Payment (NEW) -->
    <label class="payment-option">
      <input type="radio" name="payment_method" value="qr">
      <span class="payment-label">
        📱 Quét mã QR (Chuyển khoản)
      </span>
    </label>

    <!-- PayPal -->
    <label class="payment-option">
      <input type="radio" name="payment_method" value="paypal">
      <span class="payment-label">PayPal</span>
    </label>
  </div>
</div>
```

---

## 🔧 Bước 3: Cập nhật gio_hang.js

**File:** `frontend/js/trang/gio_hang.js`

Tìm phần `form?.addEventListener('submit')` và thêm logic redirect tới QR payment:

```javascript
form?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  // Check user login status first
  await Chung.ensureUserChecked();
  if (!Chung.currentUser) {
    const ok = await Chung.showLoginPrompt(
      'Bạn đang chưa đăng nhập. Đăng nhập để lưu đơn, theo dõi trạng thái và nhận ưu đãi.',
      window.location.href
    );
    if (!ok) return;
  }

  const items = GioHang.lay();
  if (!items.length) return;

  // Lấy phương thức thanh toán
  const paymentMethod = form.payment_method?.value || 'cod';

  const payload = {
    ho_ten: form.ho_ten.value.trim(),
    sdt: form.sdt.value.trim(),
    email: form.email.value.trim(),
    dia_chi: form.dia_chi.value.trim() + (form.ghi_chu?.value ? `\nGhi chú: ${form.ghi_chu.value}` : ''),
    items: items.map(i => ({
      product_id: i.id,
      id: i.id,
      gia: i.gia,
      so_luong: i.so_luong,
    })),
    payment_method: paymentMethod, // ✅ Thêm dòng này
  };

  try {
    const btn = document.getElementById('btn-dat-hang');
    btn.disabled = true;
    btn.textContent = 'Đang xử lý...';
    
    const result = await Chung.goiApi('dat_hang.php', {
      method: 'POST',
      body: JSON.stringify(payload),
    });

    if (result.success) {
      GioHang.luu([]);
      render();

      // ✅ THÊM: Redirect tới QR payment nếu phương thức là QR
      if (paymentMethod === 'qr') {
        const total = GioHang.tongTien();
        const params = new URLSearchParams({
          order_id: result.data?.order_id || 0,
          customer_email: form.email.value.trim(),
          customer_name: form.ho_ten.value.trim(),
          amount: total,
        });
        
        // Redirect tới trang QR payment
        window.location.href = `../thanh_toan_qr/thanh_toan_qr.html?${params.toString()}`;
        return;
      }

      // Nếu không phải QR, hiển thị popup thông báo như bình thường
      const modal = document.getElementById('order-success');
      const msg = document.getElementById('order-success-msg');
      if (msg) {
        msg.textContent = `Đơn hàng #${result.data?.order_id || ''} đã được ghi nhận. Chúng tôi sẽ liên hệ sớm.`;
      }
      modal.hidden = false;
      form.reset();
    } else {
      Chung.toast(result.message || 'Đặt hàng thất bại');
    }
    
    btn.disabled = false;
    btn.textContent = 'Đặt hàng';
  } catch (err) {
    Chung.toast('Không thể đặt hàng. Vui lòng thử lại.');
    console.error(err);
  }
});
```

---

## 🔧 Bước 4: Cập nhật Backend API (Optional)

**File:** `backend/api/dat_hang.php` (hoặc controller thanh toán)

Thêm trường `payment_method` vào database nếu chưa có:

```php
// Trong hàm tạo đơn hàng
$paymentMethod = $data['payment_method'] ?? 'cod';

// Lưu vào database
$stmt = $this->db->prepare(
  'INSERT INTO orders (user_id, customer_name, customer_email, total_amount, 
   order_status, address, phone, payment_method) 
   VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
  $data['user_id'] ?? null,
  $data['customer_name'],
  $data['customer_email'],
  $data['tong_tien'],
  'cho_xu_ly',
  $data['dia_chi'],
  $data['phone'],
  $paymentMethod, // ✅ Thêm dòng này
]);
```

Thêm column vào table orders nếu chưa có:
```sql
ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'cod';
```

---

## 📊 Flow khi người dùng chọn QR Payment

```
1. Khách chọn "📱 Quét mã QR"
   ↓
2. Điền form + Click "Đặt hàng"
   ↓
3. Backend tạo đơn hàng
   ↓
4. Frontend kiểm tra payment_method === 'qr'
   ↓
5. Redirect tới: /frontend/trang/thanh_toan_qr/thanh_toan_qr.html?order_id=123&customer_email=...&amount=500000
   ↓
6. Trang QR payment:
   - Tạo mã QR
   - Hiển thị thông tin chuyển khoản
   - Khách quét QR hoặc chuyển khoản thủ công
   ↓
7. Admin nhận email → Duyệt thanh toán
   ↓
8. Khách nhận email xác nhận thành công
```

---

## 🎯 Checklist tích hợp

- [ ] Cài đặt thư viện (composer require...)
- [ ] Chạy migration SQL
- [ ] Cập nhật `hang_so.php` với SMTP config
- [ ] Thêm option "QR Payment" vào `gio_hang.html`
- [ ] Cập nhật `payment.js` (nếu cần)
- [ ] Cập nhật `gio_hang.js` để redirect tới QR payment
- [ ] Cập nhật `dat_hang.php` backend
- [ ] Test: Đặt hàng → Chọn QR → Redirect tới trang QR
- [ ] Test: Admin dashboard xem QR payment chờ duyệt
- [ ] Test: Admin duyệt → Khách nhận email

---

## ✅ Xác minh hoạt động

1. **Trang QR Payment hiển thị:**
   - Navigate tới: `http://localhost/luxurious-fashion-store/frontend/trang/thanh_toan_qr/thanh_toan_qr.html?order_id=1&customer_email=test@test.com&customer_name=Test&amount=500000`
   - Nên thấy mã QR + thông tin chuyển khoản

2. **Admin Dashboard:**
   - Navigate tới: `http://localhost/luxurious-fashion-store/frontend/admin/qr_payment/index.html`
   - Nên thấy danh sách QR payment chờ duyệt (nếu có dữ liệu)

3. **Email Gmail:**
   - Cập nhật app password
   - Test gửi email từ helper class
   - Kiểm tra Gmail inbox

---

## 🆘 Troubleshooting tích hợp

| Problem | Solution |
|---------|----------|
| Redirect không hoạt động | Kiểm tra path URL chính xác, xem browser console error |
| QR code không hiển thị | Kiểm tra CORS, PHP library cài đặt, thư mục uploads/qr_codes |
| Email không gửi | Kiểm tra SMTP config, app password, Gmail 2FA |
| Admin dashboard trống | Kiểm trace table qr_payments có data không, refresh browser |
| Duyệt thanh toán lỗi | Check API endpoint, kiểm log backend |

---

## 📚 Files được tạo/sửa

### Tạo mới:
- ✅ `backend/model/qr_payment.php`
- ✅ `backend/api/qr_payment_api.php`
- ✅ `backend/helpers/qr_helper.php`
- ✅ `backend/helpers/email_helper.php`
- ✅ `frontend/trang/thanh_toan_qr/thanh_toan_qr.html`
- ✅ `frontend/admin/qr_payment/index.html`
- ✅ `database/add_qr_payment_table.sql`

### Sửa/Thêm:
- 📝 `backend/cau_hinh/hang_so.php` - Thêm SMTP config
- 📝 `frontend/trang/gio_hang/gio_hang.html` - Thêm QR option
- 📝 `frontend/js/trang/gio_hang.js` - Thêm redirect logic

---

## 🚀 Đã xong!

Sau khi cập nhật tất cả file trên, hệ thống QR Payment sẽ hoàn toàn tích hợp vào flow thanh toán hiện tại của bạn.

**Bắt đầu test ngay!** 🎉
