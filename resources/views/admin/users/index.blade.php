@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Gestione Utenti</h2>
</div>

<form action="{{ route('admin.users.index') }}" method="GET" class="mb-4">
    <div class="row g-2">
        <div class="col-md-9">
            <div class="input-group shadow-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" class="form-control border-start-0" name="search" value="{{ request('search') }}" placeholder="Cerca per nome o email...">
                <button type="submit" class="btn btn-primary">Cerca</button>
            </div>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-outline-secondary w-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fa-solid fa-filter"></i> Filtri Avanzati
            </button>
        </div>
    </div>

    
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-sliders"></i> Filtri Avanzati</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="fa-solid fa-medal text-warning"></i> Min. Cammini</label>
                            <input type="number" class="form-control" name="min_trails" value="{{ request('min_trails') }}" min="0" placeholder="Es. 1">
                            <small class="text-muted d-block mt-1">Cammini interi completati.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><i class="fa-solid fa-route text-primary"></i> Min. Tappe</label>
                            <input type="number" class="form-control" name="min_stages" value="{{ request('min_stages') }}" min="0" placeholder="Es. 5">
                            <small class="text-muted d-block mt-1">Tappe parziali completate.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold"><i class="fa-solid fa-user-shield text-primary"></i> Stato Account</label>
                            <select class="form-select" name="status">
                                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Tutti</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Attivi</option>
                                <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Bannati</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold"><i class="fa-solid fa-calendar-plus text-success"></i> Data Registrazione</label>
                            <div class="input-group">
                                <span class="input-group-text">Da</span>
                                <input type="date" class="form-control" name="registered_from" value="{{ request('registered_from') }}">
                                <span class="input-group-text">A</span>
                                <input type="date" class="form-control" name="registered_to" value="{{ request('registered_to') }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold"><i class="fa-solid fa-cake-candles text-danger"></i> Data di Nascita</label>
                            <div class="input-group">
                                <span class="input-group-text">Da</span>
                                <input type="date" class="form-control" name="birth_from" value="{{ request('birth_from') }}">
                                <span class="input-group-text">A</span>
                                <input type="date" class="form-control" name="birth_to" value="{{ request('birth_to') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light me-auto">Azzera Filtri</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary">Applica Filtri</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Data Registrazione</th>
                        <th>Cammini Completati</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="fw-bold">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="text-decoration-none">{{ $user->name }}</a>
                                @if($user->is_banned)
                                    <span class="badge bg-danger ms-1 small"><i class="fa-solid fa-user-slash"></i> Bannato</span>
                                @endif
                            </td>
                            <td><a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a></td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-success rounded-pill" style="min-width: 24px;" title="Cammini interi completati">
                                    {{ $user->completedFullTrailsCount() }}
                                </span>
                                <span class="badge bg-primary rounded-pill ms-1" style="min-width: 24px;" title="Tappe parziali completate">
                                    {{ $user->completedSingleStagesCount() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Nessun utente trovato.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-4">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
