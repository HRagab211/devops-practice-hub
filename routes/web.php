<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskReportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('tasks', TaskController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/reports', [TaskReportController::class, 'index'])->name('reports.index');
    Route::post('/reports', [TaskReportController::class, 'store'])->middleware('throttle:6,1')->name('reports.store');
    Route::get('/reports/{report}/download', [TaskReportController::class, 'download'])->name('reports.download');
    Route::get('/lab', LabController::class)->name('lab');
});
