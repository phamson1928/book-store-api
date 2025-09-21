# ZaloPay Test Examples

## Cách test ZaloPay integration

### 1. Tạo đơn hàng với ZaloPay

```bash
# Tạo đơn hàng
curl -X POST http://localhost:8000/api/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "payment_method": "zalopay",
    "phone": "0123456789",
    "address": "123 Test Street"
  }'
```

### 2. Tạo thanh toán ZaloPay

```bash
# Tạo thanh toán
curl -X POST http://localhost:8000/api/zalopay/create-payment \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "order_id": 1,
    "amount": 100000
  }'
```

Response sẽ trả về `order_url` để redirect user đến ZaloPay.

### 3. Kiểm tra trạng thái thanh toán

```bash
# Kiểm tra trạng thái
curl -X POST http://localhost:8000/api/zalopay/check-status \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "app_trans_id": "250920_1_abc123"
  }'
```

### 4. Lấy danh sách thanh toán

```bash
# Lấy danh sách thanh toán
curl -X GET http://localhost:8000/api/zalopay/payments \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 5. Test với Postman

Import collection sau vào Postman:

```json
{
    "info": {
        "name": "ZaloPay API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Create Payment",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Authorization",
                        "value": "Bearer {{token}}"
                    },
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n  \"order_id\": 1,\n  \"amount\": 100000\n}"
                },
                "url": {
                    "raw": "{{base_url}}/api/zalopay/create-payment",
                    "host": ["{{base_url}}"],
                    "path": ["api", "zalopay", "create-payment"]
                }
            }
        },
        {
            "name": "Check Status",
            "request": {
                "method": "POST",
                "header": [
                    {
                        "key": "Authorization",
                        "value": "Bearer {{token}}"
                    },
                    {
                        "key": "Content-Type",
                        "value": "application/json"
                    }
                ],
                "body": {
                    "mode": "raw",
                    "raw": "{\n  \"app_trans_id\": \"{{app_trans_id}}\"\n}"
                },
                "url": {
                    "raw": "{{base_url}}/api/zalopay/check-status",
                    "host": ["{{base_url}}"],
                    "path": ["api", "zalopay", "check-status"]
                }
            }
        }
    ],
    "variable": [
        {
            "key": "base_url",
            "value": "http://localhost:8000"
        },
        {
            "key": "token",
            "value": "YOUR_TOKEN_HERE"
        },
        {
            "key": "app_trans_id",
            "value": "YOUR_APP_TRANS_ID"
        }
    ]
}
```

### 6. Test Webhook Callback

Để test webhook callback, bạn có thể sử dụng ngrok để expose local server:

```bash
# Cài đặt ngrok
npm install -g ngrok

# Expose local server
ngrok http 8000
```

Sau đó cập nhật `ZALOPAY_CALLBACK_URL` trong `.env`:

```env
ZALOPAY_CALLBACK_URL=https://your-ngrok-url.ngrok.io/api/zalopay/callback
```

### 7. Test với ZaloPay Sandbox

1. Truy cập: https://sandbox.zalopay.vn/
2. Đăng nhập với tài khoản test
3. Sử dụng test cards:
    - **Thành công**: 4111111111111111
    - **Thất bại**: 4000000000000002
    - **Hủy**: 4000000000000003

### 8. Kiểm tra Database

```sql
-- Xem tất cả payments
SELECT * FROM payments;

-- Xem payments theo trạng thái
SELECT * FROM payments WHERE status = 'success';

-- Xem payments của user cụ thể
SELECT p.*, o.user_id
FROM payments p
JOIN orders o ON p.order_id = o.id
WHERE o.user_id = 1;
```

### 9. Debug Logs

Kiểm tra logs trong `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log | grep ZaloPay
```

### 10. Common Issues

**Issue**: "Chữ ký không hợp lệ"
**Solution**: Kiểm tra Key1, Key2 và cách tạo signature

**Issue**: "App ID không tồn tại"  
**Solution**: Kiểm tra ZALOPAY_APP_ID = 2553 (sandbox)

**Issue**: "Callback không được gọi"
**Solution**: Đảm bảo callback URL có thể truy cập từ internet

**Issue**: "Amount phải là số nguyên"
**Solution**: Chuyển đổi amount thành integer: `(int) $amount`
