# Sale Price Display Fix - Complete Implementation

## Issues Addressed

1. **Old SALE/Trend APIs** - Removed dead `sale.php` and `xu_huong.php` endpoints
2. **Missing Price Display** - Products weren't showing discounted prices
3. **Badge Format** - Badge showed "SALE" instead of discount percentage (e.g., "-45%")
4. **Price Layout** - No visual separation between original and sale price
5. **Code Cleanup** - Removed redundant `gia_goc` field from formatter

## Changes Made

### 1. Backend - Removed Old APIs
- ❌ Deleted: `/backend/api/sale.php` - Old hardcoded sale endpoint
- ❌ Deleted: `/backend/api/xu_huong.php` - Old hardcoded trend endpoint
- ℹ️ Note: Filter system now handles both via `is_sale` and `is_trend` parameters in `/backend/api/san_pham.php`

### 2. Frontend - Product Card Display
**File**: `/frontend/js/thanh_phan/the_san_pham.js`

#### Badge Display (Lines 14-26)
- **Old**: `<span class="sale-badge">🔥 SALE</span>` (no context)
- **New**: `<span class="sale-badge">🔥 -45%</span>` (shows discount percentage)
- Calculates discount as: `((originalPrice - salePrice) / originalPrice) * 100`

#### Price Display (Lines 28-35)
- **Old**: Single price line `<p class="product-card__price">999,999đ</p>`
- **New**: Dual price display:
  ```html
  <div class="product-card__price-container">
    <span class="product-card__price-original">999,999đ</span>  <!-- strikethrough -->
    <span class="product-card__price">599,999đ</span>          <!-- discounted -->
  </div>
  ```

### 3. Frontend - Styling
**File**: `/frontend/css/thanh_phan/the_san_pham.css` (Lines 148-172)

Added `.product-card__price-container` with:
- Flex layout for side-by-side display
- Gap: 0.75rem
- Original price: strikethrough + gray color
- Sale price: bold + primary color

### 4. Backend - Data Formatting
**File**: `/backend/helpers/dinh_dang_san_pham.php`

Cleaned up mapper:
- ❌ Removed: `'gia_goc'` field (was redundant)
- ❌ Removed: `'giam_gia'` field (use `is_sale` instead)
- ✅ Keep: `'gia'` - original price from `price` column
- ✅ Keep: `'sale_price'` - discounted price from `sale_price` column
- ✅ Keep: `'is_sale'` - flag indicating if product is on sale

### 5. Testing
**File**: `/backend/api/test_sale_display.php`

Created test endpoint that verifies:
- All products are returned
- Sale filter works correctly
- Discount percentage calculation works
- Both `gia` and `sale_price` fields are present

## Database Schema (No Changes Needed)

The product table should have:
- `gia` or `price` - original price (int)
- `sale_price` - discounted price (int, nullable)
- `is_sale` - flag (tinyint, 0 or 1)
- `is_trend` - flag (tinyint, 0 or 1)

**Note**: If `original_price` column exists in database, it can remain unused or be dropped. The system uses `price` column for original price and `sale_price` for discounted price.

## How It Works

### Display Logic
1. Product card checks `is_sale` and `sale_price` fields
2. If both are set:
   - Calculate discount: `((gia - sale_price) / gia) * 100`
   - Show badge with percentage: `🔥 -45%`
   - Show price container with both prices:
     - Original (strikethrough): gray text
     - Sale price: larger, bold, main color
3. If sale_price is null, just show regular price (no discount)

### Filter Integration
- Filters work via `is_sale` checkbox in `/frontend/thanh_phan/bo_loc/bo_loc.html`
- API query: `GET /backend/api/san_pham.php?is_sale=1`
- Products tagged with `is_sale=1` and `sale_price` set display with badges and dual prices

## Testing Checklist

- [ ] Navigate to products page
- [ ] Check filter "SALE" checkbox
- [ ] Verify products with sale_price display:
  - ✅ Badge shows discount percentage (not just "SALE")
  - ✅ Original price is crossed out in gray
  - ✅ Sale price is bold and prominent
- [ ] Check filter "TREND" checkbox
- [ ] Verify products display ✨ TREND badge
- [ ] Check products with both sale_price AND is_trend=1
  - ✅ Both badges display (stacked)
  - ✅ Prices display correctly
- [ ] Uncheck filters
- [ ] Verify all products display (regular price only)
- [ ] Browser console - no errors

## Example Output

For a product:
- Original price: 1,000,000đ
- Sale price: 600,000đ
- Discount: 40%

**Display**:
```
┌─────────────────────────────┐
│ 🔥 -40%                    │
│                             │
│ [Product Image]             │
│                             │
│ ~~1,000,000đ~~  600,000đ   │
└─────────────────────────────┘
```

## API Response Example

```json
{
  "product_id": 1,
  "product_name": "Luxury Handbag",
  "gia": 1000000,
  "sale_price": 600000,
  "is_sale": 1,
  "is_trend": 0
}
```

## Status

✅ **Implementation Complete**
- All old code removed
- New price display fully integrated
- Badge percentage calculation implemented
- CSS styling applied
- Test endpoint created and ready

Ready for verification and testing!
