<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardController;
use App\Http\Controllers\ItemController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;


// Home
Route::redirect('/', '/home');

Route::controller(HomeController::class)->group(function () {
    Route::get('/home', 'index')->name('home');
}); 

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
// Route::middleware(['auth'])->group(function () {
Route::group([], function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/search-users', [AdminController::class, 'searchUsers'])->name('admin.search-users');
    Route::get('/statistics', [AdminController::class, 'getStatistics'])->name('statistics');
    
    Route::post('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle-status');
    Route::post('/admin/users/{id}/toggle-admin', [AdminController::class, 'toggleAdminStatus'])->name('admin.users.toggle-admin');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.destroy');
});

// Profile routes (authentication required)
Route::middleware('auth')->controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'show')->name('profile.show');
    Route::get('/profile/edit', 'edit')->name('profile.edit');
    Route::put('/profile', 'update')->name('profile.update');
    Route::get('/profile/picture', 'editPicture')->name('profile.picture.edit');
    Route::put('/profile/picture', 'updatePicture')->name('profile.picture.update');
    Route::delete('/profile/picture', 'deletePicture')->name('profile.picture.delete');
});


// Booking routes
Route::get('/booking/{listing}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/bookings', [BookingController::class, 'store'])->middleware('auth')->name('bookings.store');