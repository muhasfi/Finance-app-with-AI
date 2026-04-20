<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RecurringPlanController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance App — API Routes
| Base URL: /api
| Auth: Bearer Token (Laravel Sanctum)
|--------------------------------------------------------------------------
*/

// ── Public routes (tidak butuh token) ────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});

// ── 2FA verification (pakai temp_token) ──────────────────────────────────
// Route::middleware('auth:sanctum')->post('auth/2fa/verify', [AuthController::class, 'verifyTwoFactor']);

// ── Protected routes (butuh Bearer token) ────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('logout',          [AuthController::class, 'logout']);
        Route::post('logout-all',      [AuthController::class, 'logoutAll']);
        Route::post('email/resend',    [AuthController::class, 'resendVerification']);
        Route::get('email/status',     [AuthController::class, 'emailVerificationStatus']);
        Route::get('me',               [AuthController::class, 'me']);
        Route::put('me',               [AuthController::class, 'updateProfile']);
        Route::put('me/password',      [AuthController::class, 'changePassword']);
        Route::get('tokens',           [AuthController::class, 'tokens']);
        Route::delete('tokens/{id}',   [AuthController::class, 'revokeToken']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/',      [DashboardController::class, 'index']);
        Route::get('charts', [DashboardController::class, 'charts']);
    });

    // Transaksi
    Route::prefix('transactions')->group(function () {
        Route::get('/',         [TransactionController::class, 'index']);
        Route::post('/',        [TransactionController::class, 'store']);
        Route::get('summary',   [TransactionController::class, 'summary']);
        Route::post('transfer', [TransactionController::class, 'transfer']);
        Route::get('{id}',      [TransactionController::class, 'show']);
        Route::put('{id}',      [TransactionController::class, 'update']);
        Route::delete('{id}',   [TransactionController::class, 'destroy']);
    });

    // Rekening
    Route::prefix('accounts')->group(function () {
        Route::get('/',              [AccountController::class, 'index']);
        Route::post('/',             [AccountController::class, 'store']);
        Route::get('total-balance',  [AccountController::class, 'totalBalance']);
        Route::get('{id}',           [AccountController::class, 'show']);
        Route::put('{id}',           [AccountController::class, 'update']);
        Route::delete('{id}',        [AccountController::class, 'destroy']);
    });

    // Kategori
    Route::prefix('categories')->group(function () {
        Route::get('/',         [CategoryController::class, 'index']);
        Route::get('flat',      [CategoryController::class, 'flat']);
        Route::post('/',        [CategoryController::class, 'store']);
        Route::put('{id}',      [CategoryController::class, 'update']);
        Route::delete('{id}',   [CategoryController::class, 'destroy']);
    });

    // Budget
    Route::prefix('budgets')->group(function () {
        Route::get('/',               [BudgetController::class, 'index']);
        Route::post('/',              [BudgetController::class, 'store']);
        Route::post('copy',           [BudgetController::class, 'copyFromLastMonth']);
        Route::put('{id}',            [BudgetController::class, 'update']);
        Route::delete('{id}',         [BudgetController::class, 'destroy']);
    });

    // Transaksi berulang
    Route::prefix('recurring')->group(function () {
        Route::get('/',              [RecurringPlanController::class, 'index']);
        Route::post('/',             [RecurringPlanController::class, 'store']);
        Route::get('{id}',           [RecurringPlanController::class, 'show']);
        Route::put('{id}',           [RecurringPlanController::class, 'update']);
        Route::delete('{id}',        [RecurringPlanController::class, 'destroy']);
        Route::patch('{id}/toggle',  [RecurringPlanController::class, 'toggle']);
    });

    // Laporan
    Route::prefix('reports')->group(function () {
        Route::get('monthly',    [ReportController::class, 'monthly']);
        Route::get('trend',      [ReportController::class, 'trend']);
        Route::get('range',      [ReportController::class, 'range']);
        Route::get('comparison', [ReportController::class, 'comparison']);
        Route::get('filterMeta', [ReportController::class, 'filterMeta']);
        Route::get('exportCsv', [ReportController::class, 'exportCsv']);
        Route::get('exportPdf', [ReportController::class, 'exportPdf']);
    });

    // Notifikasi
    Route::prefix('notifications')->group(function () {
        Route::get('/',              [NotificationController::class, 'index']);
        Route::get('unread-count',   [NotificationController::class, 'unreadCount']);
        Route::post('read-all',      [NotificationController::class, 'markAllAsRead']);
        Route::delete('all',         [NotificationController::class, 'destroyAll']);
        Route::post('{id}/read',     [NotificationController::class, 'markAsRead']);
        Route::delete('{id}',        [NotificationController::class, 'destroy']);
    });

    // AI (hanya aktif jika GEMINI_API_KEY diset)
    Route::prefix('ai')->middleware('throttle:ai-chat')->group(function () {
        Route::post('chat',              [AiController::class, 'chat']);
        Route::post('chat/reset',        [AiController::class, 'resetChat']);
        Route::get('insights',           [AiController::class, 'insights']);
        Route::post('insights/generate', [AiController::class, 'generateInsight']);
    });
    Route::prefix('import')->group(function () {
        Route::post('upload', [ImportController::class, 'upload']);
    });

    Route::get('/receipt/{path}', [TransactionController::class, 'img'])->name('receipt');
});
