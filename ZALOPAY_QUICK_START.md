# ZaloPay Integration - Quick Start Guide

## ✅ Đã hoàn thành tích hợp ZaloPay Sandbox

### 🚀 Cách sử dụng ngay:

#### 1. Tạo đơn hàng với ZaloPay

```bash
POST /api/orders
{
  "payment_method": "zalopay",
  "phone": "0123456789",
  "address": "123 Test Street"
}
```

#### 2. Tạo thanh toán ZaloPay

```bash
POST /api/zalopay/create-payment
{
  "order_id": 1,
  "amount": 100000
}
```

#### 3. Thanh toán

-   Nhận `order_url` từ response
-   Mở URL trong browser
-   Quét QR code bằng app ZaloPay
-   Hoặc sử dụng test card: `4111111111111111`

### 📱 Test với app ZaloPay thật:

1. **Tải app ZaloPay** từ App Store/Google Play
2. **Đăng nhập** với tài khoản ZaloPay thật
3. **Quét QR code** từ trang thanh toán
4. **Thanh toán** với số tiền nhỏ (ví dụ: 1,000 VND)

### 🔧 Cấu hình mặc định (đã sẵn sàng):

```env
# Không cần thêm gì vào .env, đã có giá trị mặc định
ZALOPAY_APP_ID=2553
ZALOPAY_KEY1=PcY4iZIKFCIdgZvA6ueMcMHHUbRLYjPL
ZALOPAY_KEY2=kLtgPl8HHhfvMuDHPwKfgfsY4Ydm9eIz
ZALOPAY_CALLBACK_URL=http://localhost:8000/api/zalopay/callback
ZALOPAY_REDIRECT_URL=http://localhost:3000/payment/success
ZALOPAY_ENDPOINT=https://sb-openapi.zalopay.vn/v2
```

### 🎯 API Endpoints đã sẵn sàng:

-   `POST /api/zalopay/create-payment` - Tạo thanh toán
-   `POST /api/zalopay/check-status` - Kiểm tra trạng thái
-   `GET /api/zalopay/payments` - Lấy danh sách thanh toán
-   `GET /api/zalopay/payments/{id}` - Chi tiết thanh toán
-   `POST /api/zalopay/refund` - Hoàn tiền
-   `POST /api/zalopay/callback` - Webhook (tự động)

### 📊 Database đã sẵn sàng:

-   Bảng `payments` đã được tạo
-   Model `Payment` đã được tích hợp
-   Relationship với `Order` đã sẵn sàng

### 🔍 Test cards:

```
✅ Thành công: 4111111111111111
❌ Thất bại: 4000000000000002
🚫 Hủy: 4000000000000003
```

### 🐛 Debug:

```bash
# Xem logs
tail -f storage/logs/laravel.log | grep ZaloPay

# Kiểm tra database
SELECT * FROM payments;
SELECT * FROM orders WHERE payment_method = 'zalopay';
```

### 🌐 Ngrok (nếu cần callback public):

```bash
# Cài đặt ngrok
npm install -g ngrok

# Expose local server
ngrok http 8000

# Cập nhật callback URL
ZALOPAY_CALLBACK_URL=https://your-ngrok-url.ngrok.io/api/zalopay/callback
```

### 🎉 Kết quả test:

```
✅ Order created successfully!
App Trans ID: 250920_1_LpRWmf
Order URL: https://qcgateway.zalopay.vn/openinapp?order=...
```

### 📋 Checklist hoàn thành:

-   ✅ ZaloPay Service class
-   ✅ ZaloPay Controller
-   ✅ API Routes
-   ✅ Database Migration
-   ✅ Payment Model
-   ✅ Order Integration
-   ✅ Webhook Handler
-   ✅ Sandbox Configuration
-   ✅ Test với ZaloPay API

### 🚀 Sẵn sàng sử dụng!

Bạn có thể bắt đầu test ngay với frontend hoặc Postman. Tất cả đã được cấu hình và test thành công!
