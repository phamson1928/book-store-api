# ZaloPay Integration Guide

## Tổng quan

Dự án đã được tích hợp ZaloPay để xử lý thanh toán online. Tích hợp này sử dụng ZaloPay Sandbox environment để test.

## Cấu hình môi trường

Thêm các biến môi trường sau vào file `.env`:

```env
# ZaloPay Configuration (Sandbox)
ZALOPAY_APP_ID=2553
ZALOPAY_KEY1=PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL
ZALOPAY_KEY2=kLtgPl8HHhfvMuDHPwKfgfsY4Ydm9eIz
ZALOPAY_CALLBACK_URL=http://localhost:8000/api/zalopay/callback
ZALOPAY_REDIRECT_URL=http://localhost:3000/payment/success
ZALOPAY_ENDPOINT=https://sb-openapi.zalopay.vn/v2
```

### Giải thích các thông số:

-   `ZALOPAY_APP_ID`: App ID của ZaloPay (Sandbox: 2553)
-   `ZALOPAY_KEY1`: Key1 để tạo chữ ký (Sandbox: PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL)
-   `ZALOPAY_KEY2`: Key2 để xác thực callback (Sandbox: kLtgPl8HHhfvMuDHPwKfgfsY4Ydm9eIz)
-   `ZALOPAY_CALLBACK_URL`: URL webhook để ZaloPay gửi kết quả thanh toán
-   `ZALOPAY_REDIRECT_URL`: URL redirect sau khi thanh toán thành công
-   `ZALOPAY_ENDPOINT`: Endpoint API của ZaloPay (Sandbox)

## Cấu trúc Database

### Bảng `payments`

```sql
CREATE TABLE payments (
    id BIGINT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    payment_method VARCHAR(255) DEFAULT 'zalopay',
    zalopay_trans_id VARCHAR(255) NULL,
    app_trans_id VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(255) DEFAULT 'pending',
    zalopay_response JSON NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);
```

## API Endpoints

### 1. Tạo thanh toán ZaloPay

**POST** `/api/zalopay/create-payment`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**

```json
{
    "order_id": 1,
    "amount": 100000
}
```

**Response:**

```json
{
    "message": "Tạo đơn hàng thanh toán thành công",
    "payment": {
        "id": 1,
        "order_id": 1,
        "app_trans_id": "250920_1_abc123",
        "amount": 100000,
        "status": "pending"
    },
    "order_url": "https://sb-openapi.zalopay.vn/v2/pay/...",
    "app_trans_id": "250920_1_abc123"
}
```

### 2. Kiểm tra trạng thái thanh toán

**POST** `/api/zalopay/check-status`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**

```json
{
    "app_trans_id": "250920_1_abc123"
}
```

**Response:**

```json
{
    "payment": {
        "id": 1,
        "status": "success",
        "paid_at": "2025-09-20T10:30:00Z"
    },
    "status": 1,
    "message": "Thanh toán thành công"
}
```

### 3. Lấy danh sách thanh toán của user

**GET** `/api/zalopay/payments`

**Headers:**

```
Authorization: Bearer {token}
```

**Response:**

```json
[
    {
        "id": 1,
        "order_id": 1,
        "amount": 100000,
        "status": "success",
        "created_at": "2025-09-20T10:00:00Z"
    }
]
```

### 4. Lấy chi tiết thanh toán

**GET** `/api/zalopay/payments/{id}`

**Headers:**

```
Authorization: Bearer {token}
```

### 5. Hoàn tiền

**POST** `/api/zalopay/refund`

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**

```json
{
    "payment_id": 1,
    "amount": 100000,
    "description": "Hoàn tiền đơn hàng"
}
```

### 6. Lấy thông tin thanh toán của đơn hàng

**GET** `/api/orders/{id}/payment`

**Headers:**

```
Authorization: Bearer {token}
```

## Webhook Callback

ZaloPay sẽ gửi kết quả thanh toán đến endpoint:

**POST** `/api/zalopay/callback`

Endpoint này không cần authentication và sẽ tự động cập nhật trạng thái thanh toán.

## Flow thanh toán

1. **Tạo đơn hàng**: User tạo đơn hàng với `payment_method: "zalopay"`
2. **Tạo thanh toán**: Gọi API `/api/zalopay/create-payment` để tạo thanh toán ZaloPay
3. **Redirect**: Redirect user đến `order_url` để thanh toán
4. **Callback**: ZaloPay gửi kết quả về `/api/zalopay/callback`
5. **Kiểm tra**: Có thể gọi `/api/zalopay/check-status` để kiểm tra trạng thái

## Trạng thái thanh toán

-   `pending`: Đang chờ thanh toán
-   `success`: Thanh toán thành công
-   `failed`: Thanh toán thất bại
-   `cancelled`: Thanh toán bị hủy

## Test với ZaloPay Sandbox

### Thông tin test:

-   **App ID**: 2553
-   **Key1**: PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL
-   **Key2**: kLtgPl8HHhfvMuDHPwKfgfsY4Ydm9eIz
-   **Endpoint**: https://sb-openapi.zalopay.vn/v2

### Test cards:

-   **Thành công**: 4111111111111111
-   **Thất bại**: 4000000000000002
-   **Hủy**: 4000000000000003

## Lưu ý quan trọng

1. **Sandbox chỉ dùng để test**: Không sử dụng thông tin sandbox cho production
2. **Callback URL**: Phải là URL public có thể truy cập từ internet
3. **Amount**: ZaloPay yêu cầu amount là số nguyên (VND)
4. **Security**: Luôn verify callback signature trước khi xử lý
5. **Logging**: Tất cả giao dịch đều được log để debug

## Troubleshooting

### Lỗi thường gặp:

1. **"Chữ ký không hợp lệ"**: Kiểm tra Key1, Key2 và cách tạo signature
2. **"App ID không tồn tại"**: Kiểm tra ZALOPAY_APP_ID
3. **"Callback không được gọi"**: Kiểm tra ZALOPAY_CALLBACK_URL có thể truy cập từ internet
4. **"Amount phải là số nguyên"**: Chuyển đổi amount thành integer trước khi gửi

### Debug:

-   Kiểm tra log trong `storage/logs/laravel.log`
-   Sử dụng Postman để test API endpoints
-   Kiểm tra database table `payments` để xem trạng thái

## Production Setup

Khi chuyển sang production:

1. Đăng ký tài khoản ZaloPay Merchant
2. Lấy thông tin production từ ZaloPay
3. Cập nhật các biến môi trường
4. Thay đổi endpoint từ sandbox sang production
5. Test kỹ trước khi go-live
