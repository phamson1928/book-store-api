<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::apiResource('books',BookController::class)->middleware('checkAdmin');
    Route::apiResource('authors',AuthorController::class)->middleware('checkAdmin');
});

Route::get('/trending-books', [BookController::class, 'trending']);
Route::get('list-books-of-author/{id}', [AuthorController::class, 'getBooksByAuthor']);