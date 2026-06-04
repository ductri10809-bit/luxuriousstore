# ✅ QR Payment - Installation Checklist

## 📦 Files Được Tạo

### Backend (6 files)
- [x] `backend/model/qr_payment.php` - Model database
- [x] `backend/api/qr_payment_api.php` - REST API
- [x] `backend/helpers/qr_helper.php` - QR code generator
- [x] `backend/helpers/email_helper.php` - Email sender
- [x] `backend/cau_hinh/hang_so.php` - Config (UPDATE: SMTP settings)
- [x] `database/add_qr_payment_table.sql` - Database migration

### Frontend (2 main files)
- [x] `frontend/trang/thanh_toan_qr/thanh_toan_qr.html` - QR payment page
- [x] `frontend/admin/qr_payment/index.html` - Admin dashboard

### Documentation (5 files)
- [x] `QUICK_START.md` - 5-minute setup
- [x] `QR_PAYMENT_SETUP.md` - Full setup guide
- [x] `QR_PAYMENT_INTEGRATION.md` - Integration guide
- [x] `QR_PAYMENT_COMPONENTS.md` - Components overview
- [x] `QR_PAYMENT_README.md` - Main README

---

## 🔧 Setup Steps (In Order)

### Step 1: Install Dependencies
```bash
cd /d/hiii/htdocs/luxurious-fashion-store

# Check if composer.json exists
# If not, create it first
composer init -n

# Install required packages
composer require phpmailer/phpmailer
composer require chillerlan/php-qrcode

# Verify installation
composer list
```
- [ ] PHPMailer installed
- [ ] PHP QRCode installed
- [ ] vendor/ folder created

### Step 2: Create Database Table
```bash
# Option 1: Via MySQL CLI
mysql -u root -p shop_db < database/add_qr_payment_table.sql

# Option 2: Via phpMyAdmin
# 1. Import add_qr_payment_table.sql
# 2. Select database "shop_db"
# 3. Click Import

# Verify table created
SELECT * FROM qr_payments LIMIT 1;
```
- [ ] qr_payments table created
- [ ] Columns match schema
- [ ] Foreign key to orders working

### Step 3: Create Upload Directory
```bash
# Linux/Mac/WSL
mkdir -p uploads/qr_codes
chmod 755 uploads/qr_codes

# Windows (PowerShell)
New-Item -Path "uploads/qr_codes" -ItemType Directory -Force
```
- [ ] Directory exists
- [ ] Directory is writable
- [ ] Path: `/d/hiii/htdocs/luxurious-fashion-store/uploads/qr_codes/`

### Step 4: Configure Email (Gmail)

**A. Enable Gmail 2FA:**
1. Go to https://myaccount.google.com/security
2. Click "2-Step Verification"
3. Follow prompts to enable

**B. Create App Password:**
1. Go to https://myaccount.google.com/apppasswords
2. Device: Windows Computer
3. App: Mail
4. Copy 16-digit password

**C. Update Config:**
- File: `backend/cau_hinh/hang_so.php`
- Find: `define('SMTP_PASS', '')`
- Replace with: `define('SMTP_PASS', 'YOUR_16_DIGIT_PASSWORD')`

```php
// Example:
define('SMTP_PASS', 'abcd efgh ijkl mnop');
```

- [ ] Gmail 2FA enabled
- [ ] App password created
- [ ] hang_so.php updated
- [ ] SMTP config correct

### Step 5: Update Payment Flow

**File A:** `frontend/trang/gio_hang/gio_hang.html`

Add after PayPal option (around line 173):
```html
<!-- QR Code Payment (NEW) -->
<label class="payment-option">
  <input type="radio" name="payment_method" value="qr">
  <span class="payment-label">📱 Quét mã QR (Chuyển khoản)</span>
</label>
```

**File B:** `frontend/js/trang/gio_hang.js`

In form submit handler, update to include payment_method:

```javascript
// Around line 95, add:
const paymentMethod = form.payment_method?.value || 'cod';
payload.payment_method = paymentMethod;

// Around line 116, add redirect logic:
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
```

- [ ] QR option added to HTML
- [ ] payment_method included in payload
- [ ] Redirect logic added
- [ ] URL params correct

### Step 6: Test Implementation

**Test 1: Direct QR Page**
```
URL: http://localhost/luxurious-fashion-store/frontend/trang/thanh_toan_qr/thanh_toan_qr.html?order_id=1&customer_email=test@example.com&customer_name=Test&amount=500000

Check:
- [ ] Page loads without errors
- [ ] Order info displays
- [ ] QR code appears (or loading spinner)
- [ ] Bank info displays
- [ ] Download button works
```

**Test 2: Admin Dashboard**
```
URL: http://localhost/luxurious-fashion-store/frontend/admin/qr_payment/index.html

Check:
- [ ] Page loads
- [ ] Stats show (0 pending, 0 approved)
- [ ] Tab buttons work
- [ ] "No data" message shows (if no payments)
- [ ] Responsive on mobile
```

**Test 3: Full Flow**
```
1. Go to: /frontend/trang/gio_hang/gio_hang.html
   [ ] Page loads

2. Add product to cart
   [ ] Product in cart

3. Scroll to payment section
   [ ] QR option visible in payment methods

4. Fill form:
   [ ] Name
   [ ] Phone
   [ ] Email
   [ ] Address

5. Select "📱 Quét mã QR"
   [ ] Option selectable

6. Click "Đặt hàng"
   [ ] Loading shows
   [ ] Redirect to QR payment page
   [ ] URL has order_id parameter
   [ ] QR code displays

7. Check database:
   mysql> SELECT * FROM qr_payments ORDER BY id DESC LIMIT 1;
   [ ] New row created
   [ ] All fields populated
   [ ] Status = "pending"

8. Admin Dashboard:
   [ ] Go to /frontend/admin/qr_payment/index.html
   [ ] QR payment appears in list
   [ ] Click "Xem chi tiết"
   [ ] Modal shows payment details
   [ ] Can click "Duyệt thanh toán"
```

---

## 🔍 Verification Checklist

### Files Check
```bash
# Verify all files exist
ls -la backend/model/qr_payment.php
ls -la backend/api/qr_payment_api.php
ls -la backend/helpers/qr_helper.php
ls -la backend/helpers/email_helper.php
ls -la frontend/trang/thanh_toan_qr/thanh_toan_qr.html
ls -la frontend/admin/qr_payment/index.html
ls -la uploads/qr_codes/
```

### Database Check
```sql
-- Check table structure
DESCRIBE qr_payments;

-- Should have these columns:
-- id, order_id, qr_code_data, qr_image_path, admin_email, 
-- customer_email, bank_account, transaction_status, 
-- admin_confirmed_at, customer_notified_at, amount, created_at, updated_at

-- Check constraints
SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = 'qr_payments';
```

### Config Check
```php
// backend/cau_hinh/hang_so.php should have:
define('ADMIN_EMAIL', 'ductri10809@gmail.com');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'ductri10809@gmail.com');
define('SMTP_PASS', '...'); // Not empty!
define('SMTP_FROM_EMAIL', 'ductri10809@gmail.com');
```

### Frontend Check
```html
<!-- gio_hang.html should have:
<label class="payment-option">
  <input type="radio" name="payment_method" value="qr">
  <span class="payment-label">📱 Quét mã QR (Chuyển khoản)</span>
</label>
-->
```

---

## 📋 Pre-Flight Checklist (Before Production)

### Security
- [ ] Admin dashboard requires authentication
- [ ] API endpoints validate input
- [ ] Email contains no sensitive info
- [ ] Passwords not logged
- [ ] CORS properly configured

### Performance
- [ ] Database indexes created
- [ ] QR generation doesn't timeout
- [ ] Email queue implemented (optional)
- [ ] Caching enabled (optional)

### UX/UI
- [ ] QR page mobile responsive
- [ ] Admin dashboard mobile responsive
- [ ] Email templates tested
- [ ] Error messages user-friendly
- [ ] Loading states show

### Documentation
- [ ] README complete
- [ ] Setup guide provided
- [ ] Integration guide provided
- [ ] API docs provided
- [ ] Troubleshooting guide provided

### Deployment
- [ ] All files uploaded to server
- [ ] Database migrated
- [ ] Composer dependencies installed
- [ ] Email credentials configured
- [ ] File permissions correct (755 for dirs)
- [ ] Error logging enabled

---

## 🐛 Common Issues & Fixes

| Issue | Cause | Fix |
|-------|-------|-----|
| `Class 'PHPMailer' not found` | Composer not installed | Run `composer install` |
| `Class 'chillerlan\QRCode' not found` | QRCode lib not installed | Run `composer require chillerlan/php-qrcode` |
| QR code not showing | QRCode generation failed | Check error logs, verify library version |
| Email not sending | SMTP config wrong | Verify credentials, check Gmail settings |
| 404 on QR page | File not in right directory | Check path: `/frontend/trang/thanh_toan_qr/` |
| Database error | Table not created | Run SQL migration file |
| Admin dashboard blank | No data or API error | Check database, browser console |
| Redirect not working | JavaScript error | Check browser console, verify gio_hang.js |

---

## 📞 Support Resources

1. **Check Logs:**
   ```bash
   tail -f backend/logs/db_errors.log
   tail -f backend/logs/email_errors.log
   ```

2. **Browser Console:**
   - Press F12
   - Check Console tab for errors
   - Check Network tab for API calls

3. **Test API Endpoint:**
   ```bash
   curl -X GET "http://localhost/luxurious-fashion-store/backend/api/qr_payment_api.php?action=list-pending"
   ```

4. **Verify Database:**
   ```bash
   mysql -u root -p shop_db
   SELECT COUNT(*) FROM qr_payments;
   ```

---

## 🎯 Success Criteria

When complete, you should be able to:

- ✅ Visit QR payment page and see QR code
- ✅ Place order with QR option and redirect works
- ✅ Access admin dashboard and see pending payments
- ✅ Approve payment and see success message
- ✅ Customer receives email confirmation
- ✅ Admin receives email when customer quets QR

---

## 📝 Notes

- Estimated setup time: 15-30 minutes
- Most common issue: Gmail app password
- All files use UTF-8 encoding
- PHP 7.4+ required
- MySQL 5.7+ required
- CORS must be configured if frontend/backend on different domains

---

**Setup Date:** ________________  
**Completed By:** ________________  
**Verified By:** ________________  

---

**🎉 Installation Complete!**

Once all checkboxes are ticked, your QR Payment system is ready to go!
