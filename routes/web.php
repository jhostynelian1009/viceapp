<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'active', 'verified'])->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    // Perfil y Notificaciones propias
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Rutas Generales de Planificaciones (Protección fina en Controller / Policy)
    Route::resource('plannings', PlanningController::class)->except(['create', 'show', 'edit', 'update']);
    Route::get('/plannings/{planning}/download', [PlanningController::class, 'download'])->name('plannings.download');
    Route::get('/plannings/{planning}/view', [PlanningController::class, 'view'])->name('plannings.view');
    Route::patch('/plannings/{planning}/status', [PlanningController::class, 'updateStatus'])->name('plannings.updateStatus');

    // Rutas de Comentarios (Protegidas por Policy)
    Route::post('/plannings/{planning}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Google Drive
    Route::get('/google-drive/connect', [GoogleDriveController::class, 'connect'])->name('google.connect');
    Route::get('/google-drive/callback', [GoogleDriveController::class, 'callback'])->name('google.callback');
    Route::get('/google-drive/picker', [GoogleDriveController::class, 'picker'])->name('google.picker');

    // Rutas exclusivas para Revisión Académica (Solo Vicerrectorado)
    Route::get('/plannings/review', [PlanningController::class, 'review'])
        ->middleware('role:vicerrectorado')
        ->name('plannings.review');

    // Rutas administrativas (Secretaría y Vicerrectorado)
    Route::middleware('role:secretaria|vicerrectorado')->group(function () {
        Route::patch('/teachers/{teacher}/toggle-active', [TeacherController::class, 'toggleActive'])->name('teachers.toggleActive');
        Route::resource('teachers', TeacherController::class);
        Route::resource('reports', ReportController::class)->only(['index']);
        Route::get('/reports/download/{type}', [ReportController::class, 'download'])->name('reports.download');
        Route::resource('subjects', SubjectController::class);
    });
});

require __DIR__.'/auth.php';
