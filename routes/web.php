<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AiInsightController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecurringPlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

Route::get('/', fn() => redirect()->route('dashboard'));

// ── Authenticated user ────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('transactions', TransactionController::class);
    Route::resource('categories', CategoryController::class)->except('show');   
    Route::get('/receipt/{path}', [TransactionController::class, 'img'])
    ->where('path', '.*');

    Route::resource('accounts', AccountController::class)->except('show');
    
    Route::get('/transfer',  [TransferController::class, 'create'])->name('transfer.create');
    Route::post('/transfer', [TransferController::class, 'store'])->name('transfer.store');
    
    
    // Profil
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password',   [ProfileController::class, 'updatePassword'])->name('password.update');
    
    Route::get('/ai/chat',          [ChatbotController::class, 'index'])->name('ai.chat');
    Route::post('/ai/chat/message', [ChatbotController::class, 'message'])
    ->middleware('throttle:ai-chat')
    ->name('ai.chat.message');
    Route::post('/ai/chat/reset',   [ChatbotController::class, 'reset'])->name('ai.chat.reset');
    
    Route::resource('recurring', RecurringPlanController::class)->except('show');
    Route::patch('recurring/{recurring}/toggle', [RecurringPlanController::class, 'toggle'])
    ->name('recurring.toggle');
    
// Budget per kategori
    Route::resource('budgets', \App\Http\Controllers\BudgetController::class)->except('show');
    Route::post('budgets/copy', [\App\Http\Controllers\BudgetController::class, 'copyFromLastMonth'])->name('budgets.copy');
        
    // Import CSV
    Route::get('/import',          [ImportController::class, 'create'])->name('import.create');
    Route::post('/import/upload',  [ImportController::class, 'upload'])->name('import.upload');
    Route::post('/import/confirm', [ImportController::class, 'confirm'])->name('import.confirm');

    // Laporan & Export
    Route::get('/reports',        [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Insight keuangan bulanan
    Route::get('/ai/insights',         [AiInsightController::class, 'index'])->name('ai.insights');
    Route::post('/ai/insights/generate',[AiInsightController::class, 'generate'])->name('ai.insights.generate');
    Route::get('/ai/insights/status',  [AiInsightController::class, 'status'])->name('ai.insights.status');
    Route::get('/ai/tips',             [AiInsightController::class, 'tips'])->name('ai.tips');
    Route::get('/ai/anomalies',        [AiInsightController::class, 'anomalies'])->name('ai.anomalies');
});

// ── Admin ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', \App\Http\Controllers\Admin\DashboardController::class)
            ->name('dashboard');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
            ->only(['index', 'show', 'destroy']);

        Route::patch('users/{user}/suspend',  [\App\Http\Controllers\Admin\UserController::class, 'suspend'])
            ->name('users.suspend');

        Route::patch('users/{user}/activate', [\App\Http\Controllers\Admin\UserController::class, 'activate'])
            ->name('users.activate');

        Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class)
            ->except('show');

        Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])
            ->name('audit-logs.index');

    });


require __DIR__.'/auth.php';
