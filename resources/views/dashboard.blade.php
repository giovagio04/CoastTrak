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
    <div class="col-md-12">
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
                                        <td>
                                            <a href="{{ route('outings.show', $req->outing->id) }}">{{ $req->outing->stage_name }}</a>
                                            @if($req->outing->organizer_id === auth()->id())
                                                <span class="badge bg-info text-dark ms-1 small"><i class="fa-solid fa-user-tie"></i> Organizzatore</span>
                                            @endif
                                        </td>
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
                                                @if($req->outing->organizer_id !== auth()->id())
                                                    <form action="{{ route('participations.destroy', $req->id) }}" method="POST" onsubmit="return confirm('Sicuro di voler annullare la tua partecipazione?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i> Annulla</button>
                                                    </form>
                                                @else
                                                    <div class="d-flex flex-column align-items-center">
                                                        <span class="text-muted small mb-1"><i class="fa-solid fa-user-tie"></i> Organizzatore</span>
                                                        @if($req->outing->status === 'published')
                                                            <a href="{{ route('outings.show', $req->outing->id) }}" class="btn btn-xs btn-outline-danger py-0 px-2" style="font-size: 0.75rem;"><i class="fa-solid fa-ban"></i> Annulla</a>
                                                        @endif
                                                    </div>
                                                @endif
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
</div>
@endsection
