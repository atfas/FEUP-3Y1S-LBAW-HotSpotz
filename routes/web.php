<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardController;
use App\Http\Controllers\ItemController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\HostApplicationController; 
use App\Http\Controllers\MailController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\FavoriteController;

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

// Password reset routes
Route::controller(ForgotPasswordController::class)->group(function () {
    Route::get('/forgot-password', 'showForgotForm')->name('password.request');
    Route::post('/forgot-password', 'sendResetLink')->name('password.send');
    Route::get('/reset-password/{token}', 'showResetForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
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
    Route::get('/admin/users/search', [AdminController::class, 'searchUsers'])->name('admin.search-users');
    
    Route::post('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle-status');
    Route::post('/admin/users/{id}/toggle-admin', [AdminController::class, 'toggleAdminStatus'])->name('admin.users.toggle-admin');
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.destroy');
    
    Route::get('/admin/applications/{id}', [AdminController::class, 'showApplication'])->name('admin.applications.show');
    Route::post('/admin/applications/{id}/approve', [AdminController::class, 'approveApplication'])->name('admin.applications.approve');
    Route::post('/admin/applications/{id}/reject', [AdminController::class, 'rejectApplication'])->name('admin.applications.reject');
});

// Public profile route (no auth required)
Route::get('/user/{id}', [ProfileController::class, 'showPublic'])->name('profile.public');

// Profile routes (authentication required)
Route::middleware('auth')->controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'show')->name('profile.show');
    Route::get('/profile/edit', 'edit')->name('profile.edit');
    // Host reservations now reference the authenticated host; no ID in the URL.
    
    Route::put('/profile', 'update')->name('profile.update');
    Route::get('/profile/picture', 'editPicture')->name('profile.picture.edit');
    Route::put('/profile/picture', 'updatePicture')->name('profile.picture.update');
    Route::delete('/profile/picture', 'deletePicture')->name('profile.picture.delete');
    Route::delete('/profile', 'destroy')->name('profile.destroy');

    Route::get('/profile/host-dashboard', 'hostDashboard')
    ->name('profile.host-dashboard');

    Route::get('/profile/host-statistics', 'hostStatistics')
        ->name('profile.hostStatistics');

    Route::get('/profile/host-reservations', 'hostReservations')->name('profile.hostReservations');

});

// Host application routes (authentication required)
Route::middleware('auth')->controller(HostApplicationController::class)->group(function () {
    Route::get('/host/apply', 'create')->name('host.apply');
    Route::post('/host/apply', 'store')->name('host.apply.store');
});


// Booking routes
Route::middleware('auth')->group(function () {
    Route::get('/booking/{listing}', [BookingController::class, 'show'])->name('booking.show');
});
Route::middleware('auth')->controller(BookingController::class)->group(function () {
    Route::get('/booking/{booking}/pay', 'showPaymentForm')->name('bookings.showPaymentForm');
    Route::post('/bookings', 'store')->name('bookings.store');
    Route::post('/bookings/validate', 'validateBooking')->name('bookings.validate');
    Route::get('/bookings', 'index')->name('bookings.index');
    Route::get('/bookings/{booking}/edit', 'edit')->name('bookings.edit');
    Route::put('/bookings/{booking}', 'update')->name('bookings.update');
    Route::delete('/bookings/{booking}', 'destroy')->name('bookings.destroy');
    Route::post('/bookings/{booking}/host-reject', 'hostReject')->name('bookings.hostReject');
    Route::post('/bookings/{booking}/host-confirm-payment', 'hostConfirmPayment')->name('bookings.hostConfirmPayment');
});

// Payment routes (Stripe)
Route::middleware('auth')->controller(PaymentController::class)->group(function () {
    Route::post('/payments/create-intent', 'createPaymentIntent')->name('payments.create-intent');
    Route::post('/payments/check-email', 'checkUserEmail')->name('payments.check-email');
    Route::post('/payments/confirm', 'confirmPayment')->name('payments.confirm');
    Route::post('/repayment/confirm', 'confirmRepayment')->name('repayment.confirm');
    Route::post('/repayment/confirm-other', 'confirmOtherRepayment')->name('repayment.confirm_other');
    Route::post('/repayment/record-failure', 'recordRepaymentFailure')->name('repayment.record_failure');
});

// Transactions page
Route::middleware('auth')->get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
Route::middleware('auth')->get('/transactions/{transaction}/pay', [TransactionController::class, 'payForm'])->name('transactions.pay');

// Chat routes
Route::middleware('auth')->controller(ChatController::class)->group(function () {
    Route::get('/chat', 'index')->name('chat.index');
    Route::get('/chat/{conversation}', 'show')->name('chat.show');
    Route::post('/chat/create', 'create')->name('chat.create');
    Route::post('/chat/{conversation}/message', 'sendMessage')->name('chat.send-message');
    Route::post('/chat/{conversation}/read', 'markAsRead')->name('chat.mark-read');
});

// Subscription routes
Route::prefix('subscription')->name('subscription.')->group(function () {
    Route::get('/info', fn() => view('pages.subscription.info'))->name('info');
    Route::get('/plans', fn() => view('pages.subscription.plans'))->name('plans');
});

Route::middleware('auth')->prefix('subscription')->name('subscription.')->group(function () {
    Route::post('/cancel', [App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('cancel');
    Route::get('/checkout', [App\Http\Controllers\PaymentController::class, 'subscriptionCheckout'])->name('checkout');
    Route::post('/confirm', [App\Http\Controllers\PaymentController::class, 'confirmSubscription'])->name('confirm');
});

// Review routes
Route::middleware('auth')->group(function () {
    Route::get('/bookings/{booking}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // Host response routes
    Route::post('/reviews/{review}/respond', [ReviewController::class, 'respond'])->name('reviews.respond');
    Route::put('/reviews/{review}/response', [ReviewController::class, 'updateResponse'])->name('reviews.response.update');
    Route::delete('/reviews/{review}/response', [ReviewController::class, 'destroyResponse'])->name('reviews.response.destroy');
});


Route::middleware('auth')->group(function () {

    Route::get('/favorites', [FavoriteController::class, 'index'])
        ->name('favorites.index');

    Route::post('/favorites/toggle/{listing}', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');

});
