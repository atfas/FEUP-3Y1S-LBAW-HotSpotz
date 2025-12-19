<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardController;
use App\Http\Controllers\ItemController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;


// Home
Route::redirect('/', '/home');

Route::controller(HomeController::class)->group(function () {
    Route::get('/home', 'index')->name('home');
}); 

// Listing show (only match numeric ids)
Route::get('/listing/{id}', [ListingController::class, 'show'])->whereNumber('id')->name('listing.show');
Route::get('/listing/search', [SearchController::class, 'index'])->name('search');

// Listing creation (auth)
Route::middleware('auth')->group(function () {
    Route::get('/listing/add', [ListingController::class, 'create'])->name('listing.create');
    Route::post('/listing/add', [ListingController::class, 'store'])->name('listing.store');
    Route::get('/listing/{id}/edit', [ListingController::class, 'edit'])->whereNumber('id')->name('listing.edit');
    Route::get('/listing/{id}/reservations', [ListingController::class, 'reservations'])->whereNumber('id')->name('listing.reservations');
    Route::put('/listing/{id}', [ListingController::class, 'update'])->whereNumber('id')->name('listing.update');
    Route::delete('/listing/{id}/delete', [ListingController::class, 'destroy'])->whereNumber('id')->name('listing.destroy');
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
Route::middleware(['auth'])->group(function () {

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/search-users', [AdminController::class, 'searchUsers'])->name('admin.search-users');
    
    Route::post('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle-status');
    Route::post('/admin/users/{id}/toggle-admin', [AdminController::class, 'toggleAdminStatus'])->name('admin.users.toggle-admin');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.destroy');
});

// Profile routes (authentication required)
Route::middleware('auth')->controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'show')->name('profile.show');
    Route::get('/profile/edit', 'edit')->name('profile.edit');
    // Host reservations now reference the authenticated host; no ID in the URL.
    Route::get('/profile/host-reservations', 'hostReservations')->name('profile.hostReservations');
    Route::put('/profile', 'update')->name('profile.update');
    Route::get('/profile/picture', 'editPicture')->name('profile.picture.edit');
    Route::put('/profile/picture', 'updatePicture')->name('profile.picture.update');
    Route::delete('/profile/picture', 'deletePicture')->name('profile.picture.delete');
});


// Booking routes
Route::get('/booking/{listing}', [BookingController::class, 'show'])->name('booking.show');
Route::middleware('auth')->controller(BookingController::class)->group(function () {
    Route::post('/bookings', 'store')->name('bookings.store');
    Route::post('/bookings/validate', 'validateBooking')->name('bookings.validate');
    Route::get('/bookings', 'index')->name('bookings.index');
    Route::get('/bookings/{booking}/edit', 'edit')->name('bookings.edit');
    Route::put('/bookings/{booking}', 'update')->name('bookings.update');
    Route::delete('/bookings/{booking}', 'destroy')->name('bookings.destroy');
    Route::post('/bookings/{booking}/host-reject', 'hostReject')->name('bookings.hostReject');
});
