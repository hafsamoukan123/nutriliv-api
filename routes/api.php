<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\LivreurController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\NotificationController;

// ✅ Routes عامة — لا تحتاج token
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ✅ تصفح المنتجات — عام أيضاً
Route::get('/products',              [ProductController::class, 'index']);
Route::get('/products/{id}',         [ProductController::class, 'show']);
Route::get('/categories',            [ProductController::class, 'categories']);

// ✅ Routes تحتاج token (مستخدم مسجل)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout',  [AuthController::class, 'logout']);
    Route::get('/me',       [AuthController::class, 'me']);
    Route::put('/profile',  [AuthController::class, 'updateProfile']);

    // الإشعارات — لكل المستخدمين
    Route::get('/notifications',           [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // ✅ Client فقط
    Route::middleware('role:client')->group(function () {
        Route::get('/cart',              [CartController::class, 'index']);
        Route::post('/cart',             [CartController::class, 'addItem']);
        Route::put('/cart/{id}',         [CartController::class, 'updateItem']);
        Route::delete('/cart/{id}',      [CartController::class, 'removeItem']);
        Route::post('/orders',           [OrderController::class, 'store']);
        Route::get('/orders/my',         [OrderController::class, 'myOrders']);
        Route::get('/orders/{id}/track', [OrderController::class, 'track']);
    });

    // ✅ Vendeur فقط
    Route::middleware('role:vendeur')->group(function () {
        Route::get('/vendeur/products',        [ProductController::class, 'myProducts']);
        Route::post('/vendeur/products',       [ProductController::class, 'store']);
        Route::put('/vendeur/products/{id}',   [ProductController::class, 'update']);
        Route::delete('/vendeur/products/{id}',[ProductController::class, 'destroy']);
        Route::get('/vendeur/orders',          [OrderController::class, 'vendeurOrders']);
        Route::put('/vendeur/orders/{id}',     [OrderController::class, 'updateStatus']);
        Route::get('/vendeur/dashboard',       [OrderController::class, 'dashboard']);
        Route::get('/vendeur/transactions',    [OrderController::class, 'transactions']);
        Route::post('/vendeur/transfer',       [OrderController::class, 'requestTransfer']);
    });

    // ✅ Livreur فقط
    Route::middleware('role:livreur')->group(function () {
        Route::get('/livreur/orders/available', [LivreurController::class, 'availableOrders']);
        Route::post('/livreur/orders/{id}/accept', [LivreurController::class, 'acceptOrder']);
        Route::put('/livreur/orders/{id}/status',  [LivreurController::class, 'updateStatus']);
        Route::put('/livreur/location',            [LivreurController::class, 'updateLocation']);
    });

    // ✅ Admin فقط
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users',                  [AdminController::class, 'users']);
        Route::put('/admin/users/{id}',             [AdminController::class, 'updateUser']);
        Route::get('/admin/orders',                 [AdminController::class, 'orders']);
        Route::get('/admin/analytics',              [AdminController::class, 'analytics']);
        Route::get('/admin/transactions',           [AdminController::class, 'transactions']);
        Route::put('/admin/transactions/{id}',      [AdminController::class, 'processTransfer']);
    });
});