@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Gestione Uscite</h2>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-4">
    <!-- Le Mie Partecipazioni -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <h5 class="fw-bold"><i class="fa-solid fa-list-check text-primary"></i> Le mie uscite</h5>
            </div>
            <div class="card-body">
                @if($participations->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Tappa</th>
                                    <th>Stato Uscita</th>
                                    <th>Stato Richiesta</th>
                                    <th>Azione</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($participations as $req)
                                    <tr>
                                        <td>{{ $req->outing->date->format('d/m/Y') }}</td>
                                        <td><a href="{{ route('outings.show', $req->outing->id) }}">{{ $req->outing->stage_name }}</a></td>
                                        <td><span class="badge bg-secondary">{{ strtoupper($req->outing->status) }}</span></td>
                                        <td>
                                            @if($req->status == 'accepted')
                                                <span class="badge bg-success">Accettata</span>
                                            @elseif($req->status == 'pending')
                                                <span class="badge bg-warning text-dark">In attesa</span>
                                            @else
                                                <span class="badge bg-danger">Annullata</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($req->outing->date >= now() && in_array($req->outing->status, ['published', 'open']))
                                                <form action="{{ route('participations.destroy', $req->id) }}" method="POST" onsubmit="return confirm('Sicuro di voler annullare la tua partecipazione?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i> Annulla</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">Non sei iscritto a nessuna uscita al momento.</p>
                    <a href="{{ route('outings.index') }}" class="btn btn-primary mt-3">Esplora Uscite</a>
                @endif
            </div>
        </div>
    </div>

    <!-- La mia Credenziale Digitale -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-light h-100">
            <div class="card-body text-center p-4">
                <div class="display-4 text-warning mb-3">
                    <i class="fa-solid fa-medal"></i>
                </div>
                <h5 class="fw-bold">Credenziale Digitale</h5>
                <p class="text-muted small mb-4">Statistiche dei tuoi cammini completati</p>
                
                <div class="row text-center mb-3">
                    <div class="col-6 border-end">
                        <h2 class="fw-bold text-success mb-0">{{ auth()->user()->completedFullTrailsCount() }}</h2>
                        <small class="text-muted">Cammini<br>Completi</small>
                    </div>
                    <div class="col-6">
                        <h2 class="fw-bold text-primary mb-0">{{ auth()->user()->completedSingleStagesCount() }}</h2>
                        <small class="text-muted">Tappe<br>Singole</small>
                    </div>
                </div>

                @if(auth()->user()->canCreateOuting())
                    <hr>
                    <p class="small text-success fw-bold"><i class="fa-solid fa-check-circle"></i> Hai completato il cammino! Puoi proporre uscite tra utenti.</p>
                    <a href="{{ route('user.outings.create') }}" class="btn btn-success w-100 mt-2">Proponi un'uscita</a>
                @else
                    <hr>
                    <p class="small text-muted"><i class="fa-solid fa-lock"></i> Partecipa a un'uscita "Cammino Completo" per sbloccare la proposta di uscite.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
