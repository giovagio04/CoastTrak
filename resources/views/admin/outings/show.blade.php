@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.outings.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block"><i class="fa-solid fa-arrow-left"></i> Torna alla lista</a>
        <h2 class="fw-bold mb-0">Gestione Cammino #{{ $outing->id }}</h2>
    </div>
    
    <div class="d-flex gap-2">
        @if($outing->type == 'user' && $outing->status == 'pending')
            <form action="{{ route('admin.outings.approve', $outing->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Approva</button>
            </form>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="fa-solid fa-xmark"></i> Rifiuta
            </button>
        @endif

        @if($outing->status == 'published')
            <a href="{{ route('admin.outings.edit', $outing->id) }}" class="btn btn-primary"><i class="fa-solid fa-pen"></i> Modifica</a>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="fa-solid fa-ban"></i> Annulla
            </button>
        @endif
        
        @if(in_array($outing->status, ['cancelled', 'rejected']))
            <form action="{{ route('admin.outings.destroy', $outing->id) }}" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa uscita in modo permanente?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Elimina</button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent fw-bold">
                <i class="fa-solid fa-circle-info text-primary"></i> Informazioni Cammino
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Stato</span>
                        @if($outing->status === 'pending')
                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> PENDING</span>
                        @elseif($outing->status === 'published')
                            <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> PUBLISHED</span>
                        @elseif($outing->status === 'concluded')
                            <span class="badge bg-secondary"><i class="fa-solid fa-circle-info me-1"></i> CONCLUDED</span>
                        @elseif($outing->status === 'cancelled')
                            <span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> CANCELLED</span>
                        @elseif($outing->status === 'rejected')
                            <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> REJECTED</span>
                        @else
                            <span class="badge bg-secondary">{{ strtoupper($outing->status) }}</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Tipo</span>
                        @if($outing->type == 'official')
                            <span class="badge bg-primary">Ufficiale</span>
                        @else
                            <span class="badge bg-success">Utente</span>
                        @endif
                    </li>
                    @if($outing->type == 'user' && $outing->organizer)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Organizzatore</span>
                        <a href="{{ route('admin.users.show', $outing->organizer->id) }}" class="fw-bold text-decoration-none">{{ $outing->organizer->name }}</a>
                    </li>
                    @endif
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Tappa</span>
                        <span class="fw-bold">{{ $outing->stage_name }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Data Uscita</span>
                        <span class="fw-bold">{{ $outing->date->format('d/m/Y') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Creato il</span>
                        <span>{{ $outing->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Posti Occupati</span>
                        <span>{{ $outing->participationRequests->where('status','accepted')->count() }} / {{ $outing->max_participants }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Difficoltà</span>
                        <span>{{ ucfirst($outing->difficulty) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent fw-bold">
                <i class="fa-solid fa-users text-success"></i> Utenti Iscritti
            </div>
            <div class="card-body p-0">
                @php
                    $partecipanti = $outing->participationRequests->where('status', 'accepted');
                @endphp
                
                @if($partecipanti->isEmpty())
                    <div class="p-4 text-center text-muted">
                        Nessun utente iscritto al momento.
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($partecipanti as $req)
                            <li class="list-group-item d-flex align-items-center p-3">
                                <div class="bg-body-secondary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-user text-secondary"></i>
                                </div>
                                <div>
                                    <div class="fw-bold">
                                        <a href="{{ route('admin.users.show', $req->user->id) }}" class="text-decoration-none">{{ $req->user->name }}</a>
                                    </div>
                                    <div class="text-muted small">{{ $req->user->email }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@if($outing->notes)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent fw-bold">
        <i class="fa-solid fa-note-sticky text-warning"></i> Note aggiuntive
    </div>
    <div class="card-body">
        {!! nl2br(e($outing->notes)) !!}
    </div>
</div>
@endif

@if($outing->type == 'user' && $outing->status == 'pending')
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-xmark"></i> Rifiuta Cammino</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.outings.reject', $outing->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Sei sicuro di voler rifiutare questa uscita? Puoi inserire una nota (opzionale) che verrà aggiunta ai dettagli.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivazione (Facoltativa)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Scrivi il motivo del rifiuto..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla operazione</button>
                    <button type="submit" class="btn btn-danger">Conferma Rifiuto</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if($outing->status == 'published')
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-warning"><i class="fa-solid fa-ban"></i> Annulla Cammino</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.outings.cancel', $outing->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Sei sicuro di voler annullare questa uscita pubblicata? Puoi inserire una nota (opzionale) che verrà aggiunta ai dettagli.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivazione (Facoltativa)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Scrivi il motivo dell'annullamento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-warning">Conferma Annullamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
