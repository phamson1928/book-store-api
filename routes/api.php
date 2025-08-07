<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;

//Đăng nhập, đăng ký
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//Show tất cả sách, tác giả, thể loại, đơn hàng
Route::get('books', [BookController::class,'index']);
Route::get('authors', [AuthorController::class,'index']);
Route::get('categories', [CategoryController::class,'index']);
Route::get('orders', [OrderController::class,'index']);

//Show riêng sách, tác giả, thể loại, đơn hàng
Route::get('books/{id}', [BookController::class,'show']);
Route::get('authors/{id}', [AuthorController::class,'show']);
Route::get('categories/{id}', [CategoryController::class,'show']);
Route::get('orders/{id}', [OrderController::class,'show']);

//Phân quyền đăng nhập, đăng ký, quản lý tài khoản
Route::middleware('auth:sanctum')->group(function () {
    //Quản lý tài khoản
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    //Admin quản lý sách, tác giả, thể loại, đơn hàng
    Route::middleware('checkAdmin')->group(function () {
        //Lưu sách, tác giả, thể loại, đơn hàng
        Route::post('books',BookController::class,'store');
        Route::post('authors',AuthorController::class,'store');
        Route::post('categories',CategoryController::class,'store');
        Route::post('orders',OrderController::class,'store');
        //Sửa sách, tác giả, thể loại, đơn hàng
        Route::put('books/{id}',BookController::class,'update');
        Route::put('authors/{id}',AuthorController::class,'update');
        Route::put('categories/{id}',CategoryController::class,'update');
        Route::put('orders/{id}',OrderController::class,'update');
        //Xóa sách, tác giả, thể loại, đơn hàng
        Route::delete('books/{id}',BookController::class,'destroy');
        Route::delete('authors/{id}',AuthorController::class,'destroy');
        Route::delete('categories/{id}',CategoryController::class,'destroy');
        Route::delete('orders/{id}',OrderController::class,'destroy');
    });
});
