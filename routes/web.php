<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Web\Admin\CategoryAdminController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\MaintenanceAdminController;
use App\Http\Controllers\Web\Admin\ProductAdminController;
use App\Http\Controllers\Web\Admin\TrackerAdminController;
use App\Http\Controllers\Web\Admin\UserAdminController;
use App\Http\Controllers\Web\AuthPageController;
use App\Http\Controllers\Web\CatalogController;
use Illuminate\Support\Facades\Route;

Route::middleware('track.public')->group(function (): void {
    Route::get('/', [CatalogController::class, 'home'])->name('home');
    Route::get('/products', [CatalogController::class, 'products'])->name('products.index');
    Route::get('/products/{id}', [CatalogController::class, 'show'])->name('products.show');
    Route::get('/categories/{id}', [CatalogController::class, 'byCategory'])->name('categories.show');
});

Route::get('/login', [AuthPageController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthPageController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthPageController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('api')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('api.auth');
    Route::post('/auth/signup', [AuthController::class, 'signup']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::middleware(['api.auth', 'role:SUPERADMIN,ADMIN'])->group(function (): void {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete']);
        Route::post('/assets/upload', [AssetController::class, 'upload']);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::middleware(['api.auth', 'role:SUPERADMIN,ADMIN'])->group(function (): void {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

    Route::middleware(['api.auth', 'role:SUPERADMIN'])->group(function (): void {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
});

Route::prefix('admin')
    ->middleware(['auth', 'role:SUPERADMIN,ADMIN'])
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/products', [ProductAdminController::class, 'index'])->name('products.index');
        Route::get('/products/new', [ProductAdminController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductAdminController::class, 'store'])->name('products.store');
        Route::get('/products/{id}/edit', [ProductAdminController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}', [ProductAdminController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [ProductAdminController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/bulk-delete', [ProductAdminController::class, 'bulkDestroy'])->name('products.bulk-delete');

        Route::get('/lucide-icons/{name}.svg', [CategoryAdminController::class, 'iconSvg'])
            ->where('name', '[A-Za-z0-9\\-]+')
            ->name('lucide-icons.svg');

        Route::get('/categories', [CategoryAdminController::class, 'index'])->name('categories.index');
        Route::get('/categories/new', [CategoryAdminController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryAdminController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}/edit', [CategoryAdminController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [CategoryAdminController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [CategoryAdminController::class, 'destroy'])->name('categories.destroy');

        Route::prefix('/tracker')->name('tracker.')->group(function (): void {
            Route::get('/summary', [TrackerAdminController::class, 'summary'])->name('summary');
            Route::get('/visits', [TrackerAdminController::class, 'visits'])->name('visits');
            Route::get('/users', [TrackerAdminController::class, 'guests'])->name('users');
        });
    });

Route::prefix('admin')
    ->middleware(['auth', 'role:SUPERADMIN'])
    ->name('admin.')
    ->group(function (): void {
        Route::post('/maintenance/enable', [MaintenanceAdminController::class, 'enable'])->name('maintenance.enable');
        Route::post('/maintenance/disable', [MaintenanceAdminController::class, 'disable'])->name('maintenance.disable');

        Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
        Route::post('/users', [UserAdminController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserAdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserAdminController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserAdminController::class, 'destroy'])->name('users.destroy');
    });
