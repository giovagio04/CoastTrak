<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OutingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\OutingController as AdminOutingController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/outings', [OutingController::class, 'index'])->name('outings.index');
Route::get('/outings/{outing}', [OutingController::class, 'show'])->name('outings.show');

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    
    
    Route::middleware('throttle:20,1')->group(function () {
        Route::post('/outings/{outing}/participate', [\App\Http\Controllers\ParticipationController::class, 'store'])->name('participations.store');
        Route::delete('/participations/{participation}', [\App\Http\Controllers\ParticipationController::class, 'destroy'])->name('participations.destroy');
        Route::post('/participations/{participation}/approve', [\App\Http\Controllers\ParticipationController::class, 'approve'])->name('participations.approve');
        Route::post('/participations/{participation}/reject', [\App\Http\Controllers\ParticipationController::class, 'reject'])->name('participations.reject');
    });

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profiles/{user}', [ProfileController::class, 'showUser'])->name('profiles.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
    Route::get('/user/outings/create', [\App\Http\Controllers\UserOutingController::class, 'create'])->name('user.outings.create');
    Route::post('/user/outings', [\App\Http\Controllers\UserOutingController::class, 'store'])->name('user.outings.store');
    Route::get('/user/outings/{outing}/edit', [\App\Http\Controllers\UserOutingController::class, 'edit'])->name('user.outings.edit');
    Route::put('/user/outings/{outing}', [\App\Http\Controllers\UserOutingController::class, 'update'])->name('user.outings.update');
    Route::post('/user/outings/{outing}/cancel', [\App\Http\Controllers\UserOutingController::class, 'cancel'])->name('user.outings.cancel');

    
    Route::middleware('throttle:30,1')->group(function () {
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
        Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
        Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::delete('/notifications', [\App\Http\Controllers\NotificationController::class, 'destroyAll'])->name('notifications.destroyAll');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('outings/{outing}/approve', [\App\Http\Controllers\Admin\OutingController::class, 'approve'])->name('outings.approve');
    Route::post('outings/{outing}/reject', [\App\Http\Controllers\Admin\OutingController::class, 'reject'])->name('outings.reject');
    Route::post('outings/{outing}/cancel', [\App\Http\Controllers\Admin\OutingController::class, 'cancel'])->name('outings.cancel');
    Route::resource('outings', AdminOutingController::class);

    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::post('users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('users.unban');
});

require __DIR__.'/auth.php';

