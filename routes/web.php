<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LandlordController;

// Главная страница
Route::get('/', [PropertyController::class, 'index'])->name('home');

// Аутентификация
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register']);
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Управление жильём
Route::resource('properties', PropertyController::class);

// Бронирования
Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show')->middleware('auth');

// Чат
Route::get('chat/{user}', [ChatController::class, 'index'])->name('chat.index')->middleware('auth');
Route::post('messages', [ChatController::class, 'sendMessage'])->name('messages.send')->middleware('auth');

// Поддержка
Route::get('support', [SupportController::class, 'index'])->name('support.index')->middleware('auth');
Route::post('support', [SupportController::class, 'store'])->name('support.store')->middleware('auth');

// История бронирований пользователя
Route::get('my-bookings', [BookingController::class, 'history'])->name('bookings.history')->middleware('auth');

// Мои тикеты в поддержку
Route::get('my-tickets', [SupportController::class, 'myTickets'])->name('support.tickets')->middleware('auth');

// Заявка на роль арендодателя
Route::get('apply-landlord', [LandlordController::class, 'showApplyForm'])->name('landlord.apply')->middleware('auth');
Route::post('apply-landlord', [LandlordController::class, 'apply'])->name('landlord.apply.submit');

// Становление арендодателем и добавление жилья
Route::get('become-landlord', [PropertyController::class, 'showBecomeLandlordForm'])->name('become-landlord.form')->middleware('auth');
Route::post('become-landlord', [PropertyController::class, 'storeAsLandlord'])->name('become-landlord.store')->middleware('auth');

// Административные маршруты
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/landlord-applications', [AdminController::class, 'landlordApplications'])->name('landlord.applications');
    Route::post('/landlord-applications/{application}/approve', [AdminController::class, 'approveLandlordApplication'])->name('landlord.applications.approve');
    Route::post('/landlord-applications/{application}/reject', [AdminController::class, 'rejectLandlordApplication'])->name('landlord.applications.reject');
});