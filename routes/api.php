<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    BookController,
    AuthorController,
    CategoryController,
    OrderController,
    DashboardController,
    PingController,
    UserController,
    CartController,
    OrderChangeRequestController,
    NotificationController,
    DiscountController
};

/*
|--------------------------------------------------------------------------
| Public Routes (Không cần đăng nhập)
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Danh sách public
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);

Route::get('/authors/{id}', [AuthorController::class, 'show']);
Route::get('/authors', [AuthorController::class, 'index']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Cần đăng nhập)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Quản lý tài khoản
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/tokens', [AuthController::class, 'tokens']);

    // Ping cập nhật trạng thái online
    Route::post('/ping', [PingController::class, 'ping']);

    // Quản lý giỏ hàng
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::delete('/cart', [CartController::class, 'clear']);

    //Quản lý đơn hàng
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/orders', [OrderController::class, 'showByUser']);

    //Gửi yêu cầu thay đổi đơn hàng
    Route::post('/orders/{id}/change-requests', [OrderChangeRequestController::class, 'store']);
    Route::get('/orders/{id}/change-requests', [OrderChangeRequestController::class, 'show']);
    
    // Thông báo
    Route::get('/notifications/user', [NotificationController::class, 'showForUser']);
    Route::put('/notifications/user/markAllAsRead', [NotificationController::class, 'markAllAsRead']);

    // Lấy % giảm giá cửa hàng
    Route::get('/discounts/active', [DiscountController::class, 'getActive']);
    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Cần quyền admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware('checkAdmin')->group(function () {
        //CRUD Người dùng
        Route::get('/user-index', [Usercontroller::class, 'index']);
        Route::delete('/user-delete/{id}', [Usercontroller::class, 'destroy']);

        // CRUD Sách
        Route::post('/books', [BookController::class, 'store']);
        Route::put('/books/{id}', [BookController::class, 'update']);
        Route::delete('/books/{id}', [BookController::class, 'destroy']);

        // CRUD Tác giả
        Route::post('/authors', [AuthorController::class, 'store']);
        Route::put('/authors/{id}', [AuthorController::class, 'update']);
        Route::delete('/authors/{id}', [AuthorController::class, 'destroy']);

        // CRUD Thể loại
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // CRUD Đơn hàng
        Route::put('/orders/{id}', [OrderController::class, 'update']);
        Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

        // CRUD Thông báo
        Route::get('/notifications/admin', [NotificationController::class, 'showForAdmin']);
        Route::post('/notifications/admin', [NotificationController::class, 'storeByAdmin']);
        Route::put('/notifications/admin/{id}', [NotificationController::class, 'updateByAdmin']);
        Route::delete('/notifications/admin/{id}', [NotificationController::class, 'destroyByAdmin']);

        // CRUD Giảm giá cửa hàng
        Route::get('/discounts', [DiscountController::class, 'index']);
        Route::post('/discounts', [DiscountController::class, 'store']);
        Route::put('/discounts/{id}', [DiscountController::class, 'update']);
        Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);

        // Thống kê Dashboard
        Route::get('/dashboard-stats', [DashboardController::class, 'stats']);
        Route::get('/categories-stats', [CategoryController::class, 'stats']);
        Route::get('/authors-stats', [AuthorController::class, 'stats']);
        Route::get('/orders-stats', [OrderController::class, 'stats']);
        Route::get('/users-stats', [UserController::class, 'stats']);

        // Thông tin yêu cầu thay đổi
        Route::get('/order-change-requests', [OrderChangeRequestController::class, 'index']);
        Route::put('/order-change-requests/{id}', [OrderChangeRequestController::class, 'updateAdminResponse']);
        Route::delete('/order-change-requests/{id}', [OrderChangeRequestController::class, 'destroy']);
        Route::delete('/order-change-requests/delete-all', [OrderChangeRequestController::class, 'destroyAll']);
    });
});
