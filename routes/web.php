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

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.outings.index');
    }

    // Validazione dei filtri
    $request->validate([
        'start_location'  => 'nullable|string|max:100',
        'end_location'    => [
            'nullable', 'string', 'max:100',
            // Partenza e arrivo devono essere diversi (solo se entrambi compilati)
            function ($attribute, $value, $fail) use ($request) {
                if ($value && $request->filled('start_location') &&
                    mb_strtolower(trim($value)) === mb_strtolower(trim($request->start_location))) {
                    $fail('La località di arrivo deve essere diversa da quella di partenza.');
                }
            },
        ],
        'type'            => 'nullable|in:all,official,user',
        'difficulty'      => 'nullable|in:all,facile,medio,difficile',
        'date_from'       => [
            'nullable', 'date', 'after_or_equal:today',
        ],
        'date_to'         => [
            'nullable', 'date', 'after_or_equal:today', 'after_or_equal:date_from',
        ],
        'available_only'  => 'nullable|boolean',
        'status'          => 'nullable|in:all,pending,published,concluded,cancelled,rejected',
        'role'            => 'nullable|in:all,organizer,participant',
    ], [
        'start_location.max'   => 'La località di partenza non può superare i 100 caratteri.',
        'end_location.max'     => 'La località di arrivo non può superare i 100 caratteri.',
        'type.in'              => 'Il tipo di uscita selezionato non è valido.',
        'difficulty.in'        => 'La difficoltà selezionata non è valida.',
        'date_from.date'           => 'La data di inizio inserita non è una data valida.',
        'date_from.after_or_equal' => 'La data di inizio non può essere precedente a quella odierna.',
        'date_to.date'             => 'La data di fine inserita non è una data valida.',
        'date_to.after_or_equal'   => 'La data di fine non può essere precedente a quella odierna (e deve essere uguale o successiva alla data di inizio).',
        'status.in'            => 'Lo stato selezionato non è valido.',
        'role.in'              => 'Il ruolo selezionato non è valido.',
    ]);

    $userId = auth()->id();

    $query = \App\Models\ParticipationRequest::where('user_id', $userId)
        ->with(['outing.organizer', 'outing.guide'])
        ->whereHas('outing');

    // Filtro Tipo
    if ($request->filled('type') && $request->type !== 'all') {
        $query->whereHas('outing', fn($q) => $q->where('type', $request->type));
    }

    // Filtro Difficoltà
    if ($request->filled('difficulty') && $request->difficulty !== 'all') {
        $query->whereHas('outing', fn($q) => $q->where('difficulty', $request->difficulty));
    }

    // Filtro Stato uscita
    if ($request->filled('status') && $request->status !== 'all') {
        $query->whereHas('outing', fn($q) => $q->where('status', $request->status));
    }

    // Filtro Data Da
    if ($request->filled('date_from')) {
        $query->whereHas('outing', fn($q) => $q->whereDate('date', '>=', $request->date_from));
    }

    // Filtro Data A
    if ($request->filled('date_to')) {
        $query->whereHas('outing', fn($q) => $q->whereDate('date', '<=', $request->date_to));
    }

    // Filtro Partenza
    if ($request->filled('start_location')) {
        $start = $request->start_location;
        $query->whereHas('outing', fn($q) => $q->where('start_location', 'LIKE', '%' . $start . '%'));
    }

    // Filtro Arrivo
    if ($request->filled('end_location')) {
        $end = $request->end_location;
        $query->whereHas('outing', fn($q) => $q->where('end_location', 'LIKE', '%' . $end . '%'));
    }

    // Filtro Solo posti disponibili
    if ($request->boolean('available_only')) {
        $query->whereHas('outing', function ($q) {
            $q->whereRaw('(
                SELECT COUNT(*) FROM participation_requests pr
                WHERE pr.outing_id = outings.id AND pr.status = ?
            ) < outings.max_participants', ['accepted']);
        });
    }

    // Filtro Ruolo (organizzatore / partecipante)
    if ($request->filled('role') && $request->role !== 'all') {
        if ($request->role === 'organizer') {
            $query->whereHas('outing', fn($q) => $q->where('organizer_id', $userId));
        } else {
            $query->whereHas('outing', fn($q) => $q->where('organizer_id', '!=', $userId));
        }
    }

    $participations = $query->get()->sortByDesc(fn($p) => $p->outing->date);

    return view('dashboard', compact('participations'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/outings/{outing}/participate', [\App\Http\Controllers\ParticipationController::class, 'store'])->name('participations.store');
    Route::delete('/participations/{participation}', [\App\Http\Controllers\ParticipationController::class, 'destroy'])->name('participations.destroy');
    Route::post('/participations/{participation}/approve', [\App\Http\Controllers\ParticipationController::class, 'approve'])->name('participations.approve');
    Route::post('/participations/{participation}/reject', [\App\Http\Controllers\ParticipationController::class, 'reject'])->name('participations.reject');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profiles/{user}', [ProfileController::class, 'showUser'])->name('profiles.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Uscite create dagli utenti
    Route::get('/user/outings/create', [\App\Http\Controllers\UserOutingController::class, 'create'])->name('user.outings.create');
    Route::post('/user/outings', [\App\Http\Controllers\UserOutingController::class, 'store'])->name('user.outings.store');
    Route::post('/user/outings/{outing}/cancel', [\App\Http\Controllers\UserOutingController::class, 'cancel'])->name('user.outings.cancel');

    // Notifiche
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [\App\Http\Controllers\NotificationController::class, 'destroyAll'])->name('notifications.destroyAll');
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
    Route::post('users/{user}/ban', [\App\Http\Controllers\Admin\UserController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [\App\Http\Controllers\Admin\UserController::class, 'unban'])->name('users.unban');
});

require __DIR__.'/auth.php';
