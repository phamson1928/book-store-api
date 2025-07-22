# API Authentication Documentation

## Tổng quan

API này sử dụng Laravel Sanctum để xác thực người dùng thông qua Bearer tokens.

## Base URL

```
http://localhost:8000/api
```

## Endpoints

### 1. Đăng ký (Register)

**POST** `/api/register`

**Body:**

```json
{
    "name": "Nguyễn Văn A",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response:**

```json
{
    "status": "success",
    "message": "Đăng ký thành công",
    "user": {
        "id": 1,
        "name": "Nguyễn Văn A",
        "email": "user@example.com",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "1|abc123...",
    "token_type": "Bearer"
}
```

### 2. Đăng nhập (Login)

**POST** `/api/login`

**Body:**

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response:**

```json
{
    "status": "success",
    "message": "Đăng nhập thành công",
    "user": {
        "id": 1,
        "name": "Nguyễn Văn A",
        "email": "user@example.com"
    },
    "token": "2|def456...",
    "token_type": "Bearer"
}
```

### 3. Lấy thông tin user (Get User Info)

**GET** `/api/user`

**Headers:**

```
Authorization: Bearer {token}
```

**Response:**

```json
{
    "status": "success",
    "user": {
        "id": 1,
        "name": "Nguyễn Văn A",
        "email": "user@example.com"
    }
}
```

### 4. Đăng xuất (Logout)

**POST** `/api/logout`

**Headers:**

```
Authorization: Bearer {token}
```

**Response:**

```json
{
    "status": "success",
    "message": "Đăng xuất thành công"
}
```

### 5. Xem tất cả tokens

**GET** `/api/tokens`

**Headers:**

```
Authorization: Bearer {token}
```

### 6. Đăng xuất tất cả thiết bị

**POST** `/api/logout-all`

**Headers:**

```
Authorization: Bearer {token}
```

### 7. Test Authentication

**GET** `/api/test-auth`

**Headers:**

```
Authorization: Bearer {token}
```

### 8. Test API

**GET** `/api/test`

**Response:**

```json
{
    "status": "success",
    "message": "API đang hoạt động bình thường!",
    "timestamp": "2024-01-01T00:00:00.000000Z"
}
```

## Cách sử dụng

### 1. Đăng ký tài khoản mới

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 2. Đăng nhập

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### 3. Sử dụng token để truy cập protected routes

```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer {your_token_here}"
```

## Lưu ý

-   Tất cả protected routes cần có header `Authorization: Bearer {token}`
-   Token sẽ hết hạn khi user đăng xuất hoặc xóa token
-   Một user có thể có nhiều tokens (đăng nhập từ nhiều thiết bị)
-   Sử dụng `/api/logout-all` để đăng xuất tất cả thiết bị

## Error Responses

### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

### Authentication Error (401)

```json
{
    "message": "Unauthenticated."
}
```

### Not Found Error (404)

```json
{
    "message": "Not Found."
}
```
