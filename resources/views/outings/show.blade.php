@extends('layouts.main')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('outings.index') }}">Uscite</a></li>
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

                <div class="row g-4">
                    <div class="col-md-6">
                        <h5 class="fw-bold text-muted mb-3">Dettagli Logistici</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fa-regular fa-calendar fa-fw text-primary"></i> <strong>Data:</strong> {{ $outing->date->format('d/m/Y') }}</li>
                            <li class="mb-2"><i class="fa-solid fa-map-pin fa-fw text-danger"></i> <strong>Ritrovo:</strong> {{ $outing->meeting_point }}</li>
                            <li class="mb-2"><i class="fa-solid fa-route fa-fw text-success"></i> <strong>Distanza:</strong> {{ $outing->is_full_trail ? '55.0 km' : 'Personalizzata' }}</li>
                            <li class="mb-2"><i class="fa-solid fa-mountain fa-fw text-warning"></i> <strong>Difficoltà:</strong> {{ ucfirst($outing->difficulty) }}</li>
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
                            
                            @if($outing->type == 'official' && $outing->guide)
                                <li class="mt-3"><i class="fa-solid fa-compass fa-fw text-info"></i> <strong>Guida Assegnata:</strong> {{ $outing->guide->name }}</li>
                            @elseif($outing->type == 'user' && $outing->organizer)
                                <li class="mt-3">
                                    <i class="fa-solid fa-user-tie fa-fw text-info"></i> 
                                    <strong>Organizzatore:</strong> {{ $outing->organizer->name }}
                                    @if(auth()->check() && auth()->id() === $outing->organizer_id)
                                        <span class="badge bg-secondary ms-1">Tu</span>
                                    @endif
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if($outing->notes)
                    <div class="alert alert-warning mt-4 border-warning border-start border-4 border-top-0 border-end-0 border-bottom-0 rounded-0 bg-light">
                        <h6 class="fw-bold"><i class="fa-solid fa-circle-info"></i> Note dell'Organizzatore</h6>
                        <p class="mb-0">{{ $outing->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
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
                            <div class="alert alert-info">Richiesta inviata. In attesa di approvazione.</div>
                        @elseif($userRequest->status == 'accepted')
                            <div class="alert alert-success">Sei iscritto a questa uscita! <i class="fa-solid fa-check"></i></div>
                        @endif
                    @elseif($isFull)
                        <div class="alert alert-danger">Spiacenti, l'uscita ha raggiunto il limite massimo di partecipanti.</div>
                    @else
                        <!-- Form reale -->
                        <form action="{{ route('participations.store', $outing->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-custom btn-primary-custom btn-lg w-100">Richiedi Partecipazione</button>
                        </form>
                    @endif
                @else
                    <p class="text-muted">Devi essere registrato per partecipare.</p>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 mb-2">Accedi</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100">Registrati</a>
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
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Scrivi il motivo (es. condizioni meteo sfavorevoli, imprevisto personale)..." required></textarea>
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
