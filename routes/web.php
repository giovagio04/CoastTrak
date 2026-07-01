<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OutingController;
use App\Http\Controllers\Admin\OutingController as AdminOutingController;
use App\Http\Controllers\Admin\GuideController as AdminGuideController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Rotte Pubbliche
Route::get('/outings', [OutingController::class, 'index'])->name('outings.index');
Route::get('/outings/{outing}', [OutingController::class, 'show'])->name('outings.show');

Route::get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.outings.index');
    }
    $participations = auth()->user()->participationRequests()->with(['outing'])->get();
    return view('dashboard', compact('participations'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/outings/{outing}/participate', [\App\Http\Controllers\ParticipationController::class, 'store'])->name('participations.store');
    Route::delete('/participations/{participation}', [\App\Http\Controllers\ParticipationController::class, 'destroy'])->name('participations.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Uscite create dagli utenti
    Route::get('/user/outings/create', [\App\Http\Controllers\UserOutingController::class, 'create'])->name('user.outings.create');
    Route::post('/user/outings', [\App\Http\Controllers\UserOutingController::class, 'store'])->name('user.outings.store');
});

// Rotte Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('outings/{outing}/approve', [\App\Http\Controllers\Admin\OutingController::class, 'approve'])->name('outings.approve');
    Route::post('outings/{outing}/reject', [\App\Http\Controllers\Admin\OutingController::class, 'reject'])->name('outings.reject');
    Route::post('outings/{outing}/cancel', [\App\Http\Controllers\Admin\OutingController::class, 'cancel'])->name('outings.cancel');
    Route::resource('outings', AdminOutingController::class);
    Route::resource('guides', AdminGuideController::class);
    Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
});

require __DIR__.'/auth.php';
