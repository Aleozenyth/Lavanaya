<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Staff
    Route::middleware('role:staff')->group(function () {
        Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/create', [SubmissionController::class, 'create'])->name('submissions.create');
        Route::post('/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    });

    // Detail & download bisa diakses staff (pemilik) + semua approver/finance (lihat)
    Route::middleware('role:staff,spv,manager,direktur,finance')->group(function () {
        Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');
    });

    // SPV, Manager, Direktur
    Route::middleware('role:spv,manager,direktur')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::post('/approvals/{submission}', [ApprovalController::class, 'act'])->name('approvals.act');
    });

    // Finance
    Route::middleware('role:finance')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments/{submission}', [PaymentController::class, 'process'])->name('payments.process');
    });
});
