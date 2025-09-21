# ZaloPay Sandbox - Hướng dẫn đơn giản

## Cấu hình mặc định (không cần đăng ký)

### 1. Thông tin ZaloPay Sandbox mặc định

```env
# ZaloPay Sandbox mặc định - không cần đăng ký
ZALOPAY_APP_ID=2553
ZALOPAY_KEY1=PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL
ZALOPAY_KEY2=kLtgPl8HHhfvMuDHPwKfgfsY4Ydm9eIz
ZALOPAY_CALLBACK_URL=http://localhost:8000/api/zalopay/callback
ZALOPAY_REDIRECT_URL=http://localhost:3000/payment/success
ZALOPAY_ENDPOINT=https://sb-openapi.zalopay.vn/v2
```

### 2. Cách test thanh toán

#### Bước 1: Tạo đơn hàng

```bash
POST /api/orders
{
  "payment_method": "zalopay",
  "phone": "0123456789",
  "address": "123 Test Street"
}
```

#### Bước 2: Tạo thanh toán ZaloPay

```bash
POST /api/zalopay/create-payment
{
  "order_id": 1,
  "amount": 100000
}
```

#### Bước 3: Thanh toán

-   Nhận được `order_url` từ response
-   Mở URL trong browser
-   Quét QR code bằng app ZaloPay thật
-   Hoặc sử dụng test cards

### 3. Test cards

```
Thành công: 4111111111111111
Thất bại: 4000000000000002
Hủy: 4000000000000003
```

### 4. Kiểm tra trạng thái thanh toán

```bash
POST /api/zalopay/check-status
{
  "app_trans_id": "250920_1_abc123"
}
```

### 5. Flow hoàn chỉnh

1. **User tạo đơn hàng** với `payment_method: "zalopay"`
2. **Frontend gọi API** `/api/zalopay/create-payment`
3. **Nhận order_url** từ response
4. **Redirect user** đến order_url
5. **User thanh toán** trên ZaloPay
6. **ZaloPay gửi callback** về `/api/zalopay/callback`
7. **Hệ thống cập nhật** trạng thái thanh toán

### 6. Test với Postman

```json
{
    "name": "ZaloPay Test",
    "request": {
        "method": "POST",
        "header": [
            {
                "key": "Authorization",
                "value": "Bearer YOUR_TOKEN"
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
            "raw": "http://localhost:8000/api/zalopay/create-payment",
            "protocol": "http",
            "host": ["localhost"],
            "port": "8000",
            "path": ["api", "zalopay", "create-payment"]
        }
    }
}
```

### 7. Debug

#### Kiểm tra logs

```bash
tail -f storage/logs/laravel.log | grep ZaloPay
```

#### Kiểm tra database

```sql
SELECT * FROM payments;
SELECT * FROM orders WHERE payment_method = 'zalopay';
```

### 8. Lưu ý quan trọng

-   **Sandbox mặc định**: Không cần đăng ký tài khoản
-   **Test cards**: Sử dụng số thẻ test ở trên
-   **Callback URL**: Phải là URL public (dùng ngrok nếu cần)
-   **Amount**: Phải là số nguyên (VND)

### 9. Troubleshooting

**Lỗi**: "Chữ ký không hợp lệ"
**Giải pháp**: Kiểm tra Key1, Key2 trong config

**Lỗi**: "App ID không tồn tại"
**Giải pháp**: Sử dụng APP_ID = 2553

**Lỗi**: "Callback không được gọi"
**Giải pháp**: Sử dụng ngrok để expose local server

### 10. Ngrok setup (nếu cần)

```bash
# Cài đặt ngrok
npm install -g ngrok

# Expose local server
ngrok http 8000

# Cập nhật callback URL
ZALOPAY_CALLBACK_URL=https://your-ngrok-url.ngrok.io/api/zalopay/callback
```

### 11. Production

Khi chuyển sang production:

1. Đăng ký tài khoản ZaloPay Merchant
2. Lấy thông tin production
3. Cập nhật tất cả biến môi trường
4. Test kỹ trước khi go-live
