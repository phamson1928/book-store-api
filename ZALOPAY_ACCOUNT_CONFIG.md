# Cấu hình thông tin tài khoản ZaloPay

## Cách thay đổi thông tin tài khoản trong ZaloPay Sandbox

### 1. Cấu hình trong file .env

Thêm các biến môi trường sau vào file `.env`:

```env
# ZaloPay Configuration (Sandbox)
ZALOPAY_APP_ID=2553
ZALOPAY_KEY1=PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL
ZALOPAY_KEY2=kLtgPl8HHhfvMuDHPwKfgfsY4Ydm9eIz
ZALOPAY_CALLBACK_URL=http://localhost:8000/api/zalopay/callback
ZALOPAY_REDIRECT_URL=http://localhost:3000/payment/success
ZALOPAY_ENDPOINT=https://sb-openapi.zalopay.vn/v2

# Thông tin merchant (tùy chọn)
ZALOPAY_MERCHANT_ID=your_merchant_id
ZALOPAY_MERCHANT_NAME=Your Store Name
```

### 2. Cấu hình trong ZaloPay Sandbox Dashboard

1. **Truy cập ZaloPay Sandbox**: https://sandbox.zalopay.vn/
2. **Đăng nhập** với tài khoản sandbox của bạn
3. **Vào phần Settings/Profile** để cập nhật thông tin:
    - Tên cửa hàng
    - Số điện thoại
    - Email
    - Địa chỉ

### 3. Cấu hình thông tin ngân hàng

Trong ZaloPay Sandbox, bạn có thể:

1. **Vào phần "Tài khoản ngân hàng"**
2. **Thêm tài khoản ngân hàng test** với thông tin:
    - Số tài khoản: `1234567890` (số test)
    - Tên ngân hàng: `Ngân hàng Test`
    - Tên chủ tài khoản: `Test Account`

### 4. Test với thông tin tài khoản tùy chỉnh

#### Cách 1: Sử dụng ZaloPay Sandbox App

1. Tải app ZaloPay Sandbox từ: https://sandbox.zalopay.vn/
2. Đăng nhập với tài khoản sandbox
3. Cập nhật thông tin cá nhân trong app
4. Thêm tài khoản ngân hàng test

#### Cách 2: Sử dụng Web Sandbox

1. Truy cập: https://sandbox.zalopay.vn/
2. Đăng nhập
3. Vào "Quản lý tài khoản"
4. Cập nhật thông tin cá nhân và ngân hàng

### 5. Test cards cho ZaloPay Sandbox

```
Thành công: 4111111111111111
Thất bại: 4000000000000002
Hủy: 4000000000000003
```

### 6. Cấu hình redirect URL

Trong file `.env`, cập nhật:

```env
# URL redirect sau khi thanh toán thành công
ZALOPAY_REDIRECT_URL=http://localhost:3000/payment/success

# URL callback để nhận kết quả thanh toán
ZALOPAY_CALLBACK_URL=http://localhost:8000/api/zalopay/callback
```

### 7. Test flow hoàn chỉnh

1. **Tạo đơn hàng** với `payment_method: "zalopay"`
2. **Gọi API tạo thanh toán**:
    ```bash
    POST /api/zalopay/create-payment
    {
      "order_id": 1,
      "amount": 100000
    }
    ```
3. **Redirect user** đến `order_url` từ response
4. **Thanh toán** trên ZaloPay với thông tin tài khoản đã cấu hình
5. **Kiểm tra kết quả** qua callback hoặc API check status

### 8. Debug thông tin tài khoản

Để kiểm tra thông tin tài khoản hiện tại:

```bash
# Kiểm tra logs
tail -f storage/logs/laravel.log | grep ZaloPay

# Test với curl
curl -X POST http://localhost:8000/api/zalopay/create-payment \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"order_id": 1, "amount": 100000}'
```

### 9. Lưu ý quan trọng

-   **Sandbox chỉ dùng để test**: Không sử dụng thông tin thật
-   **Thông tin tài khoản**: Có thể tùy chỉnh trong ZaloPay Sandbox dashboard
-   **Callback URL**: Phải là URL public có thể truy cập từ internet
-   **Redirect URL**: URL để redirect user sau khi thanh toán

### 10. Troubleshooting

**Vấn đề**: Thông tin tài khoản không thay đổi
**Giải pháp**:

1. Đăng xuất và đăng nhập lại ZaloPay Sandbox
2. Clear cache browser
3. Kiểm tra cấu hình trong ZaloPay dashboard

**Vấn đề**: Không nhận được callback
**Giải pháp**:

1. Sử dụng ngrok để expose local server
2. Cập nhật ZALOPAY_CALLBACK_URL với ngrok URL
3. Test callback với Postman

### 11. Production Setup

Khi chuyển sang production:

1. Đăng ký tài khoản ZaloPay Merchant thật
2. Lấy thông tin production từ ZaloPay
3. Cập nhật tất cả biến môi trường
4. Test kỹ trước khi go-live
