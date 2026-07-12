@extends('layouts.main')

@section('content')
@php
    $referer = request()->headers->get('referer');
    $backUrl = route('outings.index');
    $backLabel = 'Uscite';

    if ($referer) {
        $refererPath = parse_url($referer, PHP_URL_PATH);
        if ($refererPath === '/dashboard') {
            $backUrl = route('dashboard');
            $backLabel = 'Gestione Uscite';
        } elseif ($refererPath === '/profile') {
            $backUrl = route('profile.show');
            $backLabel = 'Area Personale';
        } elseif (str_starts_with($refererPath, '/profiles/')) {
            $backUrl = $referer;
            $backLabel = 'Profilo Utente';
        } elseif ($refererPath === '/notifications') {
            $backUrl = route('notifications.index');
            $backLabel = 'Notifiche';
        }
    }
@endphp

<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ $backUrl }}">{{ $backLabel }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Dettaglio Uscita</li>
  </ol>
</nav>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">{{ $outing->stage_name }}</h2>
                    @if($outing->type == 'official')
                        <span class="badge bg-primary fs-6 px-3 py-2">Uscita Ufficiale</span>
                    @else
                        <span class="badge bg-success fs-6 px-3 py-2">Uscita tra Utenti</span>
                    @endif
                </div>

                <p class="lead">
                    @if($outing->is_full_trail)
                        Questa uscita copre l'intero percorso del Cammino Kalabria Coast to Coast, da Soverato a Pizzo Calabro, attraversando le Serre Calabre per un totale di 55 km di pura bellezza.
                    @else
                        Percorso personalizzato: da {{ $outing->start_location }} fino a {{ $outing->end_location }}.
                    @endif
                </p>

                <hr class="my-4">

                @php
                    // Accesso completo: qualsiasi utente registrato vede tutti i dettagli
                    $showUserRequest   = auth()->check() ? $outing->participationRequests->where('user_id', auth()->id())->first() : null;
                    $isOutingOrganizer = auth()->check() && $outing->organizer_id === auth()->id();
                    $hasFullAccess     = auth()->check();
                @endphp

                @if($hasFullAccess)
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <h5 class="fw-bold text-muted mb-3">Dettagli Logistici</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fa-regular fa-calendar fa-fw text-primary"></i> <strong>Data:</strong> {{ $outing->date->format('d/m/Y') }}</li>
                            <li class="mb-2"><i class="fa-solid fa-map-pin fa-fw text-danger"></i> <strong>Ritrovo:</strong> {{ $outing->meeting_point }}</li>
                            <li class="mb-2"><i class="fa-solid fa-route fa-fw text-success"></i> <strong>Distanza:</strong> {{ $outing->is_full_trail ? '55.0 km' : 'Personalizzata' }}</li>
                            <li class="mb-2"><i class="fa-solid fa-mountain fa-fw text-warning"></i> <strong>Difficolt&agrave;:</strong> {{ ucfirst($outing->difficulty) }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="fw-bold text-muted mb-3">Partecipazione</h5>
                        <ul class="list-unstyled">
                            @if($outing->status == 'concluded')
                                <li class="mb-2"><i class="fa-solid fa-users fa-fw text-secondary"></i> <strong>Partecipanti Totali:</strong> {{ $outing->participationRequests->where('status', 'accepted')->count() }}</li>
                            @else
                                <li class="mb-2"><i class="fa-solid fa-users fa-fw text-secondary"></i> <strong>Posti Totali:</strong> {{ $outing->max_participants }}</li>
                                <li class="mb-2"><i class="fa-solid fa-user-check fa-fw text-success"></i> <strong>Iscritti Attuali:</strong> {{ $outing->participationRequests->where('status', 'accepted')->count() }}</li>
                            @endif
                            @if($outing->type == 'user' && $outing->organizer)
                                <li class="mt-3">
                                    <i class="fa-solid fa-user-tie fa-fw text-info"></i>
                                    <strong>Organizzatore:</strong> {{ $outing->organizer->name }}
                                    @if(auth()->id() === $outing->organizer_id)
                                        <span class="badge bg-secondary ms-1">Tu</span>
                                    @endif
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
                @if($outing->notes)
                    <div class="alert alert-warning mt-4 border-warning border-start border-4 border-top-0 border-end-0 border-bottom-0 rounded-0">
                        <h6 class="fw-bold"><i class="fa-solid fa-circle-info"></i> Note dell'Organizzatore</h6>
                        <p class="mb-0">{{ $outing->notes }}</p>
                    </div>
                @endif

                @endif
            </div>
        </div>

        @if(auth()->check() && $outing->organizer_id === auth()->id() && $outing->type === 'user')
            @php
                $pendingRequests = $outing->participationRequests->where('status', 'pending');
            @endphp

            @if($pendingRequests->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-users-gear text-primary me-2"></i>Gestione Richieste di Partecipazione</h5>
                        <h6 class="fw-bold text-warning mb-3">Richieste in attesa ({{ $pendingRequests->count() }})</h6>
                        <div class="list-group mb-0">
                            @foreach($pendingRequests as $req)
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center border-0 border-bottom px-0 py-3">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $req->user->name }}</div>
                                        <small class="text-muted">{{ $req->user->email }}</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('participations.approve', $req->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                <i class="fa-solid fa-check me-1"></i> Accetta
                                            </button>
                                        </form>
                                        <form action="{{ route('participations.reject', $req->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="fa-solid fa-xmark me-1"></i> Rifiuta
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 sticky-top" style="top: 2rem;">
            <div class="card-body p-4 text-center">
                <h4 class="fw-bold mb-4">Partecipa all'uscita</h4>
                
                @if(auth()->check())
                    @php
                        $userRequest = $outing->participationRequests->where('user_id', auth()->id())->first();
                        $acceptedCount = $outing->participationRequests->where('status', 'accepted')->count();
                        $isFull = $acceptedCount >= $outing->max_participants;
                    @endphp

                    @if($outing->organizer_id === auth()->id())
                        <div class="alert alert-success">
                            <i class="fa-solid fa-user-tie"></i> Sei l'organizzatore di questa uscita!
                        </div>
                        @if($outing->status === 'published')
                            <a href="{{ route('user.outings.edit', $outing->id) }}" class="btn btn-outline-primary w-100 rounded-pill mt-2">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Modifica Uscita
                            </a>
                        @endif
                        @if($outing->status === 'published')
                            <button type="button" class="btn btn-danger w-100 rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#cancelOutingModal">
                                <i class="fa-solid fa-ban"></i> Annulla Uscita
                            </button>
                        @elseif($outing->status === 'cancelled')
                            <div class="alert alert-warning py-2 small mt-2">
                                <i class="fa-solid fa-circle-info"></i> Questa uscita è stata annullata.
                            </div>
                        @endif
                    @elseif($userRequest)
                        @if($userRequest->status == 'pending')
                            <div class="alert alert-info mb-3">Richiesta inviata. In attesa di approvazione.</div>
                            
                            @if($outing->date >= now() && $outing->status === 'published')
                                <form action="{{ route('participations.destroy', $userRequest->id) }}" method="POST"
                                      onsubmit="return confirm('Sicuro di voler ritirare la tua richiesta di partecipazione?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">
                                        <i class="fa-solid fa-xmark me-1"></i> Ritira Richiesta
                                    </button>
                                </form>
                            @endif
                        @elseif($userRequest->status == 'accepted')
                            <div class="alert alert-success mb-3">Sei iscritto a questa uscita! <i class="fa-solid fa-check"></i></div>
                            
                            @if($outing->date >= now() && $outing->status === 'published')
                                <form action="{{ route('participations.destroy', $userRequest->id) }}" method="POST"
                                      onsubmit="return confirm('Sicuro di voler annullare la tua partecipazione?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">
                                        <i class="fa-solid fa-xmark me-1"></i> Annulla Iscrizione
                                    </button>
                                </form>
                            @endif
                        @elseif($userRequest->status == 'rejected')
                            
                            @if($outing->date >= now() && $outing->status === 'published' && $acceptedCount < $outing->max_participants)
                                
                                <div class="alert alert-warning mb-3">
                                    <i class="fa-solid fa-circle-info me-1"></i> La tua partecipazione precedente era stata revocata. Puoi iscriverti nuovamente.
                                </div>
                                <form action="{{ route('participations.store', $outing->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-custom btn-primary-custom btn-lg w-100">
                                        <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Richiedi Partecipazione
                                    </button>
                                </form>
                            @else
                                
                                <div class="alert alert-danger">
                                    <i class="fa-solid fa-ban me-1"></i> La tua partecipazione a questa uscita è stata revocata.
                                </div>
                            @endif
                        @endif
                    @elseif($isFull)
                        <div class="alert alert-danger">Spiacenti, l'uscita ha raggiunto il limite massimo di partecipanti.</div>
                    @else
                        @if(!auth()->user()->hasBirthDate())
                            
                            <div class="alert alert-warning text-start">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                <strong>Data di nascita mancante</strong><br>
                                <small>Devi inserire la tua data di nascita nel profilo prima di partecipare.</small>
                            </div>
                            <a href="{{ route('profile.show') }}" class="btn btn-warning w-100 rounded-pill">
                                <i class="fa-solid fa-user-pen me-1"></i> Completa il profilo
                            </a>
                        @elseif(auth()->user()->date_of_birth->diffInYears(now()) < 18)
                            
                            <div class="alert alert-danger text-start">
                                <i class="fa-solid fa-ban me-1"></i>
                                <strong>Età non sufficiente</strong><br>
                                <small>Devi avere almeno 18 anni per partecipare a un cammino.</small>
                            </div>
                        @else
                            <!-- Form reale -->
                            <form action="{{ route('participations.store', $outing->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-custom btn-primary-custom btn-lg w-100">Richiedi Partecipazione</button>
                            </form>
                        @endif
                    @endif
                @else
                    <p class="text-muted">Devi essere registrato per partecipare.</p>
                    <div class="d-flex flex-column align-items-center gap-2">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary w-100">Accedi</a>
                        <span class="text-muted" style="font-size: 0.75rem; font-weight: 500;">o</span>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary w-100">Registrati</a>
                    </div>
                @endif
        </div>
    </div>

    
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-users text-success me-2"></i>Partecipanti Iscritti</h5>

            @php
                $acceptedParticipants = $outing->participationRequests->where('status', 'accepted');
            @endphp

            @if($acceptedParticipants->isEmpty())
                <p class="text-muted mb-0 small">Nessun partecipante iscritto al momento.</p>
            @else
                <ul class="list-group list-group-flush mb-0">
                    @foreach($acceptedParticipants as $req)
                        <li class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between border-0 @if(!$loop->last) border-bottom @endif">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-user text-secondary me-2 fs-5"></i>

                                @if(auth()->check())
                                    
                                    <a href="{{ route('profiles.show', $req->user->id) }}" class="text-decoration-none fw-bold text-dark" onmouseover="this.style.color='#0d6efd'" onmouseout="this.style.color='#212529'" style="transition: color 0.2s;">
                                        {{ $req->user->name }}
                                    </a>
                                @else
                                    
                                    <span class="fw-bold" style="filter:blur(5px); user-select:none; pointer-events:none;">Utente Iscritto</span>
                                @endif
                            </div>
                            @if($req->user_id === $outing->organizer_id)
                                <span class="badge bg-info text-white small" style="font-size: 0.7rem;">Organizzatore</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @if(!auth()->check())
                    <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-eye-slash me-1"></i> <a href="{{ route('login') }}" class="text-decoration-none">Accedi</a> o <a href="{{ route('register') }}" class="text-decoration-none">Registrati</a> per vedere i partecipanti.</p>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- Modale Annullamento Organizzatore -->
@if(auth()->check() && $outing->organizer_id === auth()->id() && $outing->status === 'published')
<div class="modal fade" id="cancelOutingModal" tabindex="-1" aria-labelledby="cancelOutingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger" id="cancelOutingModalLabel"><i class="fa-solid fa-ban"></i> Annulla Uscita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('user.outings.cancel', $outing->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="mb-3">Sei sicuro di voler annullare questa uscita? Questa operazione è definitiva e notificherà i partecipanti.</p>
                    <div class="mb-3">
                        <label for="reason" class="form-label fw-bold">Motivazione dell'Annullamento (Sarà mostrata nei dettagli del cammino):</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Scrivi il motivo (es. condizioni meteo sfavorevoli, imprevisto personale)... (Opzionale)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-danger">Conferma ed Annulla Uscita</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
