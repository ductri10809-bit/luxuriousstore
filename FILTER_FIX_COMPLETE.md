# Filter Logic Fix - Complete Documentation

## Problem Statement

The filter system had two critical issues:

1. **Frontend Issue**: Form event listeners weren't being attached because the bo_loc component loads asynchronously, but listeners were being attached before it finished loading.

2. **Backend Issue**: When multiple filters (SALE + TREND) were selected, they used AND logic instead of OR, resulting in no products being shown (since products can't be both sale AND trend simultaneously in most cases).

## Root Cause Analysis

### Frontend
- `san_pham.js` was calling `attachFormListeners()` during DOMContentLoaded
- The bo_loc component loads via `taiComponent()` AFTER DOMContentLoaded
- By the time form elements were needed, listeners weren't attached
- Result: Form submission had no effect

### Backend
- Original code in `san_pham.php` model:
```php
if (!empty($filters['is_trend'])) {
    $sql .= ' AND p.is_trend = 1';  // First filter
}
if (!empty($filters['is_sale'])) {
    $sql .= ' AND p.is_sale = 1';   // Second filter - also AND
}
```
- This created: `WHERE is_trend = 1 AND is_sale = 1`
- Products can't be both, so no results
- Result: No products shown when both filters selected

## Solutions Implemented

### Frontend Fix
```javascript
// Listen for component to be loaded
document.addEventListener('componentLoaded', (e) => {
  if (e.detail.selector === '#bo-loc') {
    // Now form elements exist, attach listeners
    attachFormListeners();
  }
});
```

### Backend Fix
```php
if (!empty($filters['is_trend']) || !empty($filters['is_sale'])) {
    // Build OR conditions
    $conditions = [];
    if (!empty($filters['is_trend'])) {
        $conditions[] = 'p.is_trend = 1';
    }
    if (!empty($filters['is_sale'])) {
        $conditions[] = 'p.is_sale = 1';
    }
    // Join with OR
    $sql .= ' WHERE (' . implode(' OR ', $conditions) . ')';
}
```

## Expected Behavior

### Single Filter
- **SALE checkbox only**: Shows products where `is_sale = 1`
- **TREND checkbox only**: Shows products where `is_trend = 1`

### Multiple Filters
- **SALE + TREND**: Shows products where `(is_sale = 1 OR is_trend = 1)`
  - Includes both sale products AND trend products

### Combined with Other Filters
- **Category + SALE**: Shows sale products in selected category
- **Category + SALE + TREND**: Shows (sale OR trend) products in category

## Testing Checklist

- [ ] Open browser dev tools (F12) and check Console
- [ ] Navigate to Products page
- [ ] Check "SALE" checkbox, click "Áp dụng"
  - [ ] Console shows: "Filter Query: ?is_sale=1"
  - [ ] Only sale products display
  - [ ] Page shows correct product count
- [ ] Check "TREND" checkbox only, click "Áp dụng"
  - [ ] Console shows: "Filter Query: ?is_trend=1"
  - [ ] Only trend products display
- [ ] Check BOTH "SALE" and "TREND", click "Áp dụng"
  - [ ] Console shows: "Filter Query: ?is_sale=1&is_trend=1"
  - [ ] Products with BOTH badges appear
  - [ ] Count ≥ max(sale_count, trend_count)
- [ ] Click "Xóa bộ lọc"
  - [ ] All filters cleared
  - [ ] All products display
- [ ] Combine with Category filter
  - [ ] Category + SALE works
  - [ ] Category + SALE + TREND works

## API Test Endpoints

### Test Filter Logic
```
GET /backend/api/test_filters_api.php
```
Returns:
- Total products count
- Sale products count
- Trend products count
- Sale OR Trend products count
- Verification of OR logic working

### Debug Filter Functionality
```
GET /backend/api/debug_filters.php
```
Returns sample data and filter verification

## Console Messages

When using filters, you'll see these debug messages:

```
Filter Query: ?is_sale=1              // Single filter
Form submitted with query: ?is_sale=1
componentLoaded bo_loc                // Component loaded
```

## Technical Details

### Files Modified
1. `/frontend/js/trang/san_pham.js` - Event listener timing fixed
2. `/backend/model/san_pham.php` - OR logic implemented
3. `/backend/api/test_filters_api.php` - New test endpoint
4. `/backend/api/debug_filters.php` - New debug endpoint

### Database Structure (No Changes)
- `is_sale` - tinyint(1) - Product is on sale
- `is_trend` - tinyint(1) - Product is trending
- `sale_price` - decimal(15,0) - Discounted price (nullable)

### JavaScript Logic Flow
1. User checks filter checkboxes
2. User clicks "Áp dụng" button
3. Form submit event fires
4. buildQuery() creates: `?is_sale=1` or `?is_trend=1` or `?is_sale=1&is_trend=1`
5. taiSanPham() calls API with query string
6. API passes filters to controller
7. Model builds SQL with OR logic for sale/trend
8. Products display filtered correctly

## Known Limitations

- Filters are currently read-only OR (not user-configurable)
- Can't toggle between AND/OR behavior
- Color filters with sale/trend filters may be complex

## Future Improvements

- Add "All of these" / "Any of these" toggle for filter behavior
- Cache filter results
- Add filter persistence to URL
- Add filter counts (e.g., "Sale (5)" to show how many)
