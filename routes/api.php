<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Public;
use App\Http\Controllers\Api\V1\Admin;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ─── Public Routes ───────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    // Event & static data
    Route::get('/public/event', [Public\EventController::class, 'show']);
    Route::get('/public/ticket-products', [Public\EventController::class, 'ticketProducts']);
    Route::get('/public/faqs', [Public\EventController::class, 'faqs']);
    Route::get('/public/provinces', [Public\EventController::class, 'provinces']);
    Route::get('/public/provinces/{province}/cities', [Public\EventController::class, 'cities']);

    // Orders (public)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/orders', [Public\OrderController::class, 'store']);
        Route::post('/orders/search', [Public\OrderController::class, 'search']);
        Route::get('/orders/{orderCode}', [Public\OrderController::class, 'show']);
        Route::get('/public/e-tickets/{eTicket}/download', [Public\OrderController::class, 'downloadETicket']);
        Route::post('/orders/{orderCode}/payment-confirmations', [Public\PaymentController::class, 'store']);
    });

    // ─── Admin Auth ────────────────────────────────────────────────
    Route::prefix('admin/auth')->middleware('throttle:10,1')->group(function () {
        Route::post('/login', [Admin\AuthController::class, 'login']);
    });

    // ─── Admin Protected ────────────────────────────────────────────
    Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {

        // Auth
        Route::post('/auth/logout', [Admin\AuthController::class, 'logout']);
        Route::get('/auth/me', [Admin\AuthController::class, 'me']);

        // Dashboard
        Route::get('/dashboard', [Admin\DashboardController::class, 'index']);

        // Orders
        Route::get('/orders', [Admin\OrderController::class, 'index']);
        Route::get('/orders/{order}', [Admin\OrderController::class, 'show']);
        Route::patch('/orders/{order}', [Admin\OrderController::class, 'update']);
        Route::post('/orders/{order}/cancel', [Admin\OrderController::class, 'cancel']);
        Route::post('orders/{order}/extend-expiry', [Admin\OrderController::class, 'extendExpiry']);
        Route::post('orders/{order}/resend-ticket', [Admin\OrderController::class, 'resendTicket']);
        Route::post('orders/{order}/e-tickets', [Admin\OrderController::class, 'uploadETickets']);
        Route::delete('orders/e-tickets/{eTicket}', [Admin\OrderController::class, 'deleteETicket']);
        Route::get('orders/e-tickets/{eTicket}/download', [Admin\OrderController::class, 'downloadETicket']);

        // Payments
        Route::get('/payments', [Admin\PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [Admin\PaymentController::class, 'show']);
        Route::post('/payments/{payment}/approve', [Admin\PaymentController::class, 'approve']);
        Route::post('/payments/{payment}/reject', [Admin\PaymentController::class, 'reject']);
        Route::get('/payments/{payment}/proof', [Admin\PaymentController::class, 'getProof']);

        // Check-in
        Route::post('/check-in', [Admin\CheckinController::class, 'checkin']);
        Route::get('/check-in/verify', [Admin\CheckinController::class, 'verify']);

        // Ticket Products
        Route::apiResource('/ticket-products', Admin\TicketProductController::class);

        // Bank Accounts
        Route::apiResource('/bank-accounts', Admin\BankAccountController::class)->except(['show']);

        // Events
        Route::get('/events', [Admin\EventController::class, 'index']);
        Route::get('/events/{event}', [Admin\EventController::class, 'show']);
        Route::patch('/events/{event}', [Admin\EventController::class, 'update']);

        // FAQs
        Route::apiResource('/faqs', Admin\FaqController::class)->except(['show']);

        // Users
        Route::apiResource('/users', Admin\UserController::class)->except(['show']);

        // Settings
        Route::get('/settings', [Admin\SettingController::class, 'index']);
        Route::patch('/settings', [Admin\SettingController::class, 'update']);
        Route::post('/settings/test-smtp', [Admin\SettingController::class, 'testSmtp']);
        Route::post('/settings/test-wa', [Admin\SettingController::class, 'testWa']);

        // Reports
        Route::get('/reports/sales', [Admin\ReportController::class, 'sales']);
        Route::get('/audit-logs', [Admin\ReportController::class, 'auditLogs']);
    });
});
