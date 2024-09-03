<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AddressController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::get('me', [AuthController::class, 'me'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::get('users/email/{email}', [UserController::class, 'getByEmail']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
});

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{id}', [CategoryController::class, 'show']);


Route::get('products', [ProductController::class, 'index']);
Route::get('products/{slug}', [ProductController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('carts', [CartController::class, 'index']);
    Route::get('carts/{id}', [CartController::class, 'show']);
    Route::get('carts/user/{userId}', [CartController::class, 'getByUserId']);
    Route::post('carts', [CartController::class, 'store']);
    Route::put('carts/{id}', [CartController::class, 'update']);
    Route::delete('carts/{id}', [CartController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('addresses', [AddressController::class, 'index']);
    Route::get('addresses/{id}', [AddressController::class, 'show']);
    Route::get('addresses/user/{userId}', [AddressController::class, 'getByUserId']);
    Route::post('addresses', [AddressController::class, 'store']);
    Route::put('addresses/{id}', [AddressController::class, 'update']);
    Route::delete('addresses/{id}', [AddressController::class, 'destroy']);
});
