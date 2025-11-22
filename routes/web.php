<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardController;
use App\Http\Controllers\ItemController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\AdminController;


// Home
Route::redirect('/', '/login');

// Cards (authentication required)
Route::middleware('auth')->controller(CardController::class)->group(function () {
    Route::get('/cards', 'index')->name('cards.index');
    Route::get('/cards/{card}', 'show')->name('cards.show');
});

// API (authentication required)
Route::middleware('auth')->controller(CardController::class)->group(function () {
    Route::post('/api/cards', 'store');              // create card
    Route::delete('/api/cards/{card}', 'destroy');   // delete card
});

Route::middleware('auth')->controller(ItemController::class)->group(function () {
    Route::post('/api/cards/{card}/items', 'store'); // add item to card
    Route::patch('/api/items/{item}', 'update');     // update item
    Route::delete('/api/items/{item}', 'destroy');   // delete item
});


// Authentication
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate');
});

Route::controller(LogoutController::class)->group(function () {
    Route::get('/logout', 'logout')->name('logout');
});

Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
});

// Admin routes (authentication required)
// Route::get('/admin', [AdminController::class, 'index'])->name('admin');
//Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
// Admin routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/search-users', [AdminController::class, 'searchUsers'])->name('searchUsers');
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('toggleUserStatus');
    Route::post('/users/{id}/toggle-admin', [AdminController::class, 'toggleAdminStatus'])->name('toggleAdminStatus');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('deleteUser');
    Route::get('/statistics', [AdminController::class, 'getStatistics'])->name('statistics');
});