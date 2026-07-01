@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left"></i> Torna alla gestione utenti
        </a>
        <h2 class="fw-bold mb-0">Profilo Utente: {{ $user->name }}</h2>
    </div>
</div>

<div class="row g-4">
    <!-- Colonna Sinistra: Info Profilo & Statistiche -->
    <div class="col-lg-4">
        <!-- Scheda Profilo -->
        <div class="card shadow-sm border-0 mb-4 text-center p-4">
            <div class="card-body">
                @if($user->profile_photo_path)
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Foto Profilo" class="rounded-circle img-thumbnail mb-3 mx-auto d-block" style="width: 130px; height: 130px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 130px; height: 130px;">
                        <i class="fa-solid fa-user text-secondary fa-4x"></i>
                    </div>
                @endif

                <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                <div class="mb-3">
                    @if($user->role === 'admin')
                        <span class="badge bg-danger">Amministratore</span>
                    @else
                        <span class="badge bg-primary">Esploratore</span>
                    @endif
                </div>

                <hr class="my-3">

                <div class="text-start">
                    <p class="mb-2">
                        <i class="fa-regular fa-envelope text-muted me-2"></i>
                        <a href="mailto:{{ $user->email }}" class="text-decoration-none text-dark">{{ $user->email }}</a>
                    </p>
                    @if($user->date_of_birth)
                        <p class="mb-2">
                            <i class="fa-regular fa-calendar-days text-muted me-2"></i>
                            <strong>Data Nascita:</strong> {{ \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') }}
                        </p>
                    @endif
                    <p class="mb-2">
                        <i class="fa-solid fa-clock text-muted me-2"></i>
                        <strong>Registrato il:</strong> {{ $user->created_at->format('d/m/Y') }}
                    </p>
                </div>

                @if($user->bio)
                    <hr class="my-3">
                    <div class="text-start">
                        <h6 class="fw-bold mb-1"><i class="fa-solid fa-quote-left text-muted me-1"></i> Biografia</h6>
                        <p class="text-muted small mb-0">{{ $user->bio }}</p>
                    </div>
                @endif

                <hr class="my-3">

                <!-- Statistiche Credenziali integrate -->
                <div class="bg-light rounded p-3 text-center mb-2">
                    <div class="text-warning mb-2">
                        <i class="fa-solid fa-medal fa-lg"></i> <strong class="text-dark small">Credenziali Conseguite</strong>
                    </div>
                    <div class="row text-center g-0">
                        <div class="col-6 border-end">
                            <h4 class="fw-bold text-success mb-0">{{ $user->completedFullTrailsCount() }}</h4>
                            <small class="text-muted" style="font-size: 0.75rem;">Cammini Completi</small>
                        </div>
                        <div class="col-6">
                            <h4 class="fw-bold text-primary mb-0">{{ $user->completedSingleStagesCount() }}</h4>
                            <small class="text-muted" style="font-size: 0.75rem;">Tappe Singole</small>
                        </div>
                    </div>
                    
                    @if($user->canCreateOuting())
                        <hr class="my-2 text-muted opacity-25">
                        <p class="small text-success fw-bold mb-0" style="font-size: 0.8rem;">
                            <i class="fa-solid fa-circle-check"></i> Può creare uscite personali
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Colonna Destra: Liste Dettagliate -->
    <div class="col-lg-8">
        <!-- Tabs Navigazione -->
        <ul class="nav nav-pills mb-3 bg-white p-2 rounded shadow-sm gap-2" id="userTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active border-0" id="credentials-tab" data-bs-toggle="tab" data-bs-target="#credentials" type="button" role="tab">
                    <i class="fa-solid fa-award me-1"></i> Credenziali Digitali
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link border-0" id="outings-tab" data-bs-toggle="tab" data-bs-target="#outings" type="button" role="tab">
                    <i class="fa-solid fa-route me-1"></i> Uscite Organizzate ({{ $user->outings->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link border-0" id="participations-tab" data-bs-toggle="tab" data-bs-target="#participations" type="button" role="tab">
                    <i class="fa-solid fa-users-rectangle me-1"></i> Iscrizioni ({{ $user->participationRequests->count() }})
                </button>
            </li>
        </ul>

        <!-- Contenuto Tabs -->
        <div class="tab-content" id="userTabContent">
            <!-- Tab Credenziali -->
            <div class="tab-pane fade show active" id="credentials" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-medal text-warning"></i> Credenziali Conquistate</h5>
                        
                        @if($user->digitalCredentials->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
                                L'utente non ha ancora conseguito alcuna credenziale digitale.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tipo / Tappa</th>
                                            <th>Uscita Correlata</th>
                                            <th>Data Completamento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->digitalCredentials as $cred)
                                            <tr>
                                                <td>
                                                    @if($cred->stage)
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">Tappa Singola</span>
                                                        <div class="fw-bold mt-1">{{ $cred->stage->name }}</div>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Cammino Completo</span>
                                                        <div class="fw-bold mt-1 text-success">Intero Percorso Kalabria Coast to Coast</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.outings.show', $cred->outing_id) }}" class="text-decoration-none fw-bold">
                                                        <i class="fa-solid fa-up-right-from-square small"></i> Cammino #{{ $cred->outing_id }}
                                                    </a>
                                                    <div class="text-muted small">{{ $cred->outing->stage_name }}</div>
                                                </td>
                                                <td>{{ $cred->completed_at ? $cred->completed_at->format('d/m/Y H:i') : $cred->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Uscite Organizzate -->
            <div class="tab-pane fade" id="outings" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-compass text-success"></i> Uscite Proposte come Organizzatore</h5>
                        
                        @if($user->outings->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="fa-regular fa-calendar-times fa-2x mb-2 d-block"></i>
                                L'utente non ha mai organizzato o proposto alcuna uscita.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Tappa / Percorso</th>
                                            <th>Data Uscita</th>
                                            <th>Difficoltà</th>
                                            <th>Stato</th>
                                            <th>Iscritti</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->outings as $outing)
                                            <tr onclick="window.location='{{ route('admin.outings.show', $outing->id) }}'" style="cursor: pointer;">
                                                <td class="fw-bold">#{{ $outing->id }}</td>
                                                <td>{{ $outing->stage_name }}</td>
                                                <td>{{ $outing->date->format('d/m/Y') }}</td>
                                                <td>{{ ucfirst($outing->difficulty) }}</td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ strtoupper($outing->status) }}</span>
                                                </td>
                                                <td>
                                                    {{ $outing->participationRequests->where('status', 'accepted')->count() }} / {{ $outing->max_participants }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Iscrizioni (Richieste di Partecipazione) -->
            <div class="tab-pane fade" id="participations" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3"><i class="fa-solid fa-users text-primary"></i> Storico Richieste di Iscrizione</h5>
                        
                        @if($user->participationRequests->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="fa-regular fa-clipboard fa-2x mb-2 d-block"></i>
                                L'utente non ha mai richiesto l'iscrizione a nessuna uscita.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Uscita</th>
                                            <th>Percorso</th>
                                            <th>Data Uscita</th>
                                            <th>Data Richiesta</th>
                                            <th>Stato Iscrizione</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->participationRequests as $req)
                                            <tr onclick="window.location='{{ route('admin.outings.show', $req->outing->id) }}'" style="cursor: pointer;">
                                                <td class="fw-bold">#{{ $req->outing->id }}</td>
                                                <td>{{ $req->outing->stage_name }}</td>
                                                <td>{{ $req->outing->date->format('d/m/Y') }}</td>
                                                <td>{{ $req->created_at->format('d/m/Y') }}</td>
                                                <td>
                                                    @if($req->status === 'accepted')
                                                        <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i> Approvata</span>
                                                    @elseif($req->status === 'pending')
                                                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> In attesa</span>
                                                    @else
                                                        <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> Rifiutata / Annullata</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
