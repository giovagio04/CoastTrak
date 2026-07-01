@extends('layouts.main')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h1 class="display-5 fw-bold">Uscite in Programma</h1>
        <p class="lead text-muted">Scopri le escursioni sul Cammino Kalabria Coast to Coast e unisciti a noi.</p>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-4">
    @forelse($outings as $outing)
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    @if($outing->type == 'official')
                        <span class="badge bg-primary mb-2">Ufficiale</span>
                    @else
                        <span class="badge bg-success mb-2">Tra Utenti</span>
                    @endif
                    <h5 class="card-title fw-bold mb-0">{{ $outing->stage_name }}</h5>
                    <small class="text-muted">
                        @if($outing->is_full_trail)
                            55 km &bull; Intero percorso
                        @else
                            {{ $outing->start_location }} &rarr; {{ $outing->end_location }}
                        @endif
                    </small>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <i class="fa-regular fa-calendar text-primary me-2"></i> 
                        <strong>{{ $outing->date->format('d/m/Y') }}</strong>
                    </p>
                    <p class="card-text mb-1">
                        <i class="fa-solid fa-map-pin text-danger me-2"></i> {{ $outing->meeting_point }}
                    </p>
                    <p class="card-text mb-3">
                        <i class="fa-solid fa-users text-secondary me-2"></i> Posti max: {{ $outing->max_participants }}
                    </p>
                    
                    @if($outing->guide)
                        <p class="small text-muted mb-0"><i class="fa-solid fa-compass me-1"></i> Guida: {{ $outing->guide->name }}</p>
                    @endif
                </div>
                <div class="card-footer bg-white border-top-0 pb-4">
                    <a href="{{ route('outings.show', $outing->id) }}" class="btn btn-custom btn-outline-custom w-100">Dettagli e Partecipa</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                Al momento non ci sono uscite in programma.
            </div>
        </div>
    @endforelse
</div>
@endsection
