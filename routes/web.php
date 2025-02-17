<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LandlordController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\DatabaseController;

// Главная страница
Route::get('/', [PropertyController::class, 'index'])->name('home');

// Аутентификация
Route::middleware('guest')->group(function () {
    // Регистрация
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.post');

    // Вход
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Управление жильём
    Route::resource('properties', PropertyController::class);
    
    // Бронирования
    Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('my-bookings', [BookingController::class, 'history'])->name('bookings.history');
    
    // Чат
    Route::get('chat/{user}', [ChatController::class, 'index'])->name('chat.index');
    Route::post('messages', [ChatController::class, 'sendMessage'])->name('messages.send');
    
    // Поддержка
    Route::get('support', [SupportController::class, 'index'])->name('support.index');
    Route::post('support', [SupportController::class, 'store'])->name('support.store');
    Route::get('my-tickets', [SupportController::class, 'myTickets'])->name('support.tickets');
    
    // Тикеты поддержки
    Route::get('/support', [SupportTicketController::class, 'userTickets'])->name('support.index');
    Route::get('/support/create', [SupportTicketController::class, 'create'])->name('support.create');
    Route::post('/support/store', [SupportTicketController::class, 'store'])->name('support.store');
    Route::get('/support/{id}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{id}/message', [SupportTicketController::class, 'sendMessage'])->name('support.message.send');

    // Стать арендодателем и добавить жильё
    Route::get('become-landlord', [PropertyController::class, 'showBecomeLandlordForm'])->name('become-landlord.form')->middleware('auth');
    Route::post('become-landlord', [PropertyController::class, 'storeAsLandlord'])->name('become-landlord.store')->middleware('auth');
});

// Административные маршруты
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    
    // Управление заявками на роль арендодателя
    Route::get('landlord-applications', [AdminController::class, 'landlordApplications'])->name('landlord.applications');
    Route::post('landlord-applications/{application}/approve', [AdminController::class, 'approveLandlordApplication'])->name('landlord.applications.approve');
    Route::post('landlord-applications/{application}/reject', [AdminController::class, 'rejectLandlordApplication'])->name('landlord.applications.reject');

    // Управление базой данных
    Route::get('/database', [DatabaseController::class, 'index'])->name('database.index');
    Route::get('/database/{table}', [DatabaseController::class, 'table'])->name('database.table');
    Route::get('/database/{table}/create', [DatabaseController::class, 'createRow'])->name('database.create');
    Route::post('/database/{table}/store', [DatabaseController::class, 'storeRow'])->name('database.store');
    Route::get('/database/{table}/edit/{id}', [DatabaseController::class, 'editRow'])->name('database.edit');
    Route::put('/database/{table}/update/{id}', [DatabaseController::class, 'updateRow'])->name('database.update');
    Route::delete('/database/{table}/delete/{id}', [DatabaseController::class, 'deleteRow'])->name('database.delete');

    // Управление тикетами поддержки
    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::get('/support/{id}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{id}/message', [SupportTicketController::class, 'sendMessage'])->name('support.message.send');
});
