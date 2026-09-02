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
    Route::get('/plannings/{planning}/preview', [PlanningController::class, 'preview'])->name('plannings.preview');
    Route::get('/plannings/{planning}/view', [PlanningController::class, 'view'])->name('plannings.view');
    Route::post('/plannings/{planning}/submit', [PlanningController::class, 'submit'])->name('plannings.submit');
    Route::post('/plannings/{planning}/resubmit', [PlanningController::class, 'submit'])->name('plannings.resubmit');
    Route::post('/plannings/{planning}/approve', [PlanningController::class, 'approve'])->middleware('role:vicerrectorado')->name('plannings.approve');
    Route::post('/plannings/{planning}/reject', [PlanningController::class, 'reject'])->middleware('role:vicerrectorado')->name('plannings.reject');
    Route::get('/plannings/{planning}/versions', [PlanningController::class, 'versions'])->name('plannings.versions');
    Route::get('/plannings/{planning}/versions/{version}/download', [PlanningController::class, 'downloadVersion'])->name('plannings.versions.download');
    Route::put('/plannings/{planning}', [PlanningController::class, 'update'])->name('plannings.update');

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

        // K-005: Rutas de Estructura Académica
        Route::patch('/academic-areas/{academic_area}/toggle-active', [App\Http\Controllers\AcademicAreaController::class, 'toggleActive'])->name('academic-areas.toggleActive');
        Route::resource('academic-areas', App\Http\Controllers\AcademicAreaController::class)->names('academic-areas');

        Route::patch('/courses/{course}/toggle-active', [App\Http\Controllers\CourseController::class, 'toggleActive'])->name('courses.toggleActive');
        Route::resource('courses', App\Http\Controllers\CourseController::class);

        Route::patch('/parallels/{parallel}/toggle-active', [App\Http\Controllers\ParallelController::class, 'toggleActive'])->name('parallels.toggleActive');
        Route::resource('parallels', App\Http\Controllers\ParallelController::class);

        Route::patch('/subjects/{subject}/toggle-active', [SubjectController::class, 'toggleActive'])->name('subjects.toggleActive');

        Route::patch('/teaching-assignments/{teaching_assignment}/toggle-active', [App\Http\Controllers\TeachingAssignmentController::class, 'toggleActive'])->name('teaching-assignments.toggleActive');
        Route::resource('teaching-assignments', App\Http\Controllers\TeachingAssignmentController::class);
    });
});

require __DIR__.'/auth.php';
