# Cart Management Flow - Giải quyết vấn đề giỏ hàng

## 🎯 Vấn đề đã được giải quyết

**Trước đây**: Khi tạo đơn hàng, giỏ hàng bị xóa ngay lập tức, dẫn đến:

-   User quay lại trang giỏ hàng → trống
-   Thanh toán thất bại → mất sản phẩm
-   UX không tốt

**Bây giờ**: Giỏ hàng chỉ bị xóa khi thanh toán thành công

## 🔄 Flow mới

### 1. COD (Cash on Delivery)

```
Tạo đơn hàng → Xóa giỏ hàng ngay lập tức
```

-   Vì COD đã xác nhận đơn hàng
-   User không cần thanh toán online

### 2. ZaloPay

```
Tạo đơn hàng → Giữ giỏ hàng → Thanh toán thành công → Xóa giỏ hàng
```

## 📱 User Experience

### Khi user tạo đơn hàng ZaloPay:

1. **Tạo đơn hàng** → Giỏ hàng vẫn còn sản phẩm
2. **Redirect đến ZaloPay** → User có thể quay lại
3. **Thanh toán thành công** → Giỏ hàng tự động xóa
4. **Thanh toán thất bại** → Giỏ hàng vẫn còn sản phẩm

### Nếu user muốn hủy đơn hàng:

```bash
POST /api/orders/{id}/cancel
```

-   Đơn hàng chuyển thành "Đã hủy"
-   Sản phẩm được thêm lại vào giỏ hàng
-   User nhận thông báo

## 🛠 API Endpoints

### 1. Tạo đơn hàng

```bash
POST /api/orders
{
  "payment_method": "zalopay", // hoặc "cod"
  "phone": "0123456789",
  "address": "123 Test Street"
}
```

**Response**:

```json
{
  "message": "Đặt hàng thành công!",
  "order": {...},
  "payment_method": "zalopay",
  "clear_cart": false  // false cho ZaloPay, true cho COD
}
```

### 2. Hủy đơn hàng

```bash
POST /api/orders/{id}/cancel
```

**Response**:

```json
{
  "message": "Đơn hàng đã được hủy thành công. Sản phẩm đã được thêm lại vào giỏ hàng.",
  "order": {...}
}
```

### 3. Kiểm tra trạng thái thanh toán

```bash
POST /api/zalopay/check-status
{
  "app_trans_id": "250920_1_abc123"
}
```

## 🔧 Logic xử lý

### OrderController.store()

```php
// Chỉ xóa giỏ hàng nếu là COD
if ($paymentMethod === 'cod') {
    $cart->items()->delete();
}
```

### ZaloPayController.callback()

```php
// Xóa giỏ hàng khi thanh toán thành công
if ($status == 1) { // Thành công
    $cart = Cart::where('user_id', $payment->order->user_id)->first();
    if ($cart) {
        $cart->items()->delete();
    }
}
```

### OrderController.cancelOrder()

```php
// Restore giỏ hàng khi hủy đơn hàng
foreach ($order->orderItems as $orderItem) {
    $cart->items()->create([
        'book_id' => $orderItem->book_id,
        'quantity' => $orderItem->quantity
    ]);
}
```

## 📊 Trạng thái đơn hàng

| Trạng thái               | Giỏ hàng     | Có thể hủy |
| ------------------------ | ------------ | ---------- |
| Chờ xác nhận (COD)       | Đã xóa       | ❌         |
| Chờ thanh toán (ZaloPay) | Còn sản phẩm | ✅         |
| Đã thanh toán            | Đã xóa       | ❌         |
| Thanh toán thất bại      | Còn sản phẩm | ✅         |
| Đã hủy                   | Đã restore   | ❌         |

## 🎯 Frontend Integration

### 1. Sau khi tạo đơn hàng

```javascript
// Kiểm tra response
if (response.data.clear_cart) {
    // COD - xóa giỏ hàng ngay
    clearCart();
} else {
    // ZaloPay - giữ giỏ hàng
    // Redirect đến ZaloPay
    window.location.href = response.data.order_url;
}
```

### 2. Khi user quay lại

```javascript
// Kiểm tra giỏ hàng
if (cartItems.length > 0) {
    // Hiển thị sản phẩm
    showCartItems();
} else {
    // Hiển thị thông báo
    showEmptyCart();
}
```

### 3. Nút hủy đơn hàng

```javascript
// Chỉ hiển thị cho đơn hàng chưa thanh toán
if (order.payment_status !== "Đã thanh toán") {
    showCancelButton();
}
```

## 🐛 Debug

### Kiểm tra giỏ hàng

```sql
SELECT * FROM cart_items WHERE cart_id IN (
    SELECT id FROM carts WHERE user_id = 1
);
```

### Kiểm tra đơn hàng

```sql
SELECT id, payment_method, payment_status, state
FROM orders
WHERE user_id = 1
ORDER BY created_at DESC;
```

### Logs

```bash
tail -f storage/logs/laravel.log | grep -E "(Cart|Order|Payment)"
```

## ✅ Lợi ích

1. **UX tốt hơn**: User không mất sản phẩm khi quay lại
2. **Linh hoạt**: Có thể hủy đơn hàng chưa thanh toán
3. **An toàn**: Chỉ xóa giỏ hàng khi thực sự thành công
4. **Recovery**: Có thể restore giỏ hàng khi cần

## 🚀 Sẵn sàng sử dụng!

Flow mới đã được implement và test. User experience sẽ tốt hơn nhiều!
