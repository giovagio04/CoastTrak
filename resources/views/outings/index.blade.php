@extends('layouts.main')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h1 class="display-5 fw-bold">Uscite in Programma</h1>
        <p class="lead text-muted">Scopri le escursioni sul Cammino Kalabria Coast to Coast e unisciti a noi.</p>
    </div>
</div>

@if ($errors->any())
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    @foreach ($errors->all() as $error)
    <div class="toast align-items-center text-bg-danger border-0 shadow mb-2 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ $error }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Chiudi"></button>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="d-flex align-items-center gap-2 mb-4">

    
    @php
        $activeFilters = array_filter([
            request('start_location'), request('end_location'),
            (request('type') && request('type') !== 'all') ? request('type') : null,
            (request('difficulty') && request('difficulty') !== 'all') ? request('difficulty') : null,
            request('date_from'), request('date_to'),
            request('available_only') ? '1' : null,
        ]);
    @endphp

    @auth
        
        <button type="button" class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#filterModal">
            <i class="fa-solid fa-sliders me-2"></i>Filtri Avanzati
            @if(count($activeFilters) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ count($activeFilters) }}
                </span>
            @endif
        </button>
    @else
        
        <span
            data-bs-toggle="tooltip"
            data-bs-placement="bottom"
            title="Accedi o registrati per usare i filtri avanzati"
        >
            <button type="button" class="btn btn-outline-secondary" disabled style="pointer-events: none;">
                <i class="fa-solid fa-lock me-2 text-muted"></i>Filtri Avanzati
            </button>
        </span>
        <a href="{{ route('login') }}" class="btn btn-outline-primary">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Accedi per filtrare
        </a>
    @endauth

    <form action="{{ route('outings.index') }}" method="GET" id="filterForm">
        
        <input type="hidden" name="route_type_filter" id="hRouteTypeFilter" value="{{ request('route_type_filter', 'all') }}">
        <input type="hidden" name="full_direction" id="hFullDirection" value="{{ request('full_direction', 'soverato-pizzo') }}">
        <input type="hidden" name="start_location" id="hStart" value="{{ request('start_location') }}">
        <input type="hidden" name="end_location"   id="hEnd"   value="{{ request('end_location') }}">
        <input type="hidden" name="type"            id="hType"  value="{{ request('type', 'all') }}">
        <input type="hidden" name="difficulty"      id="hDiff"  value="{{ request('difficulty', 'all') }}">
        <input type="hidden" name="date_from"       id="hDateFrom" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to"         id="hDateTo"   value="{{ request('date_to') }}">
        <input type="hidden" name="available_only"  id="hAvailableOnly" value="{{ request('available_only') ? '1' : '' }}">
    </form>

    @if(count($activeFilters) > 0)
        <a href="{{ route('outings.index') }}" class="btn btn-outline-danger" title="Azzera filtri">
            <i class="fa-solid fa-rotate-left"></i>
        </a>
    @endif
</div>

<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="filterModalLabel">
                    <i class="fa-solid fa-sliders text-primary"></i> Filtri Avanzati
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>

            
            <div class="modal-body px-4 py-3">

                
                <div id="modalErrorBanner" class="d-none mb-3 rounded-3 p-3" style="background: #fff3cd; border-left: 4px solid #f59e0b;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation mt-1" style="color: #d97706;"></i>
                        <div>
                            <p class="fw-semibold mb-1 small" style="color: #92400e;">Controlla i dati inseriti</p>
                            <ul id="modalErrorList" class="mb-0 small ps-3" style="color: #78350f;"></ul>
                        </div>
                    </div>
                </div>

                
                <div class="mb-4">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-route text-info"></i> Tipo di Percorso
                    </p>
                    <select id="mRouteTypeFilter" class="form-select form-select-lg">
                        <option value="all" {{ request('route_type_filter', 'all') === 'all' ? 'selected' : '' }}>Tutti i percorsi</option>
                        <option value="full" {{ request('route_type_filter') === 'full' ? 'selected' : '' }}>Cammino Completo</option>
                        <option value="custom" {{ request('route_type_filter') === 'custom' ? 'selected' : '' }}>Cammino Personalizzato</option>
                    </select>
                </div>

                
                <div class="mb-4" id="sectionFullDirection" style="display: none;">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-arrow-right-arrow-left text-primary"></i> Direzione
                    </p>
                    <select id="mFullDirection" class="form-select form-select-lg">
                        <option value="soverato-pizzo" {{ request('full_direction', 'soverato-pizzo') === 'soverato-pizzo' ? 'selected' : '' }}>Soverato a Pizzo (consigliato)</option>
                        <option value="pizzo-soverato" {{ request('full_direction') === 'pizzo-soverato' ? 'selected' : '' }}>Pizzo a Soverato</option>
                    </select>
                </div>

                
                <div id="sectionCustomRoute" style="display: none;">
                    
                    <div class="mb-4">
                        <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-flag text-warning"></i> Partenza
                        </p>
                        <select id="mStart" class="form-select form-select-lg">
                            <option value="">Qualsiasi</option>
                            <option value="Soverato" {{ request('start_location') === 'Soverato' ? 'selected' : '' }}>Soverato</option>
                            <option value="Petrizzi" {{ request('start_location') === 'Petrizzi' ? 'selected' : '' }}>Petrizzi</option>
                            <option value="Monterosso Calabro" {{ request('start_location') === 'Monterosso Calabro' ? 'selected' : '' }}>Monterosso Calabro</option>
                            <option value="Pizzo" {{ request('start_location') === 'Pizzo' ? 'selected' : '' }}>Pizzo</option>
                        </select>
                    </div>

                    <hr class="my-2">

                    
                    <div class="mb-4">
                        <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-location-dot text-danger"></i> Arrivo
                        </p>
                        <select id="mEnd" class="form-select form-select-lg">
                            <option value="">Qualsiasi</option>
                            <option value="Soverato" {{ request('end_location') === 'Soverato' ? 'selected' : '' }}>Soverato</option>
                            <option value="Petrizzi" {{ request('end_location') === 'Petrizzi' ? 'selected' : '' }}>Petrizzi</option>
                            <option value="Monterosso Calabro" {{ request('end_location') === 'Monterosso Calabro' ? 'selected' : '' }}>Monterosso Calabro</option>
                            <option value="Pizzo" {{ request('end_location') === 'Pizzo' ? 'selected' : '' }}>Pizzo</option>
                        </select>
                    </div>
                </div>

                <hr class="my-2">

                
                <div class="mb-4">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-layer-group text-primary"></i> Tipo Uscita
                    </p>
                    <select id="mType" class="form-select form-select-lg">
                        <option value="all"      {{ request('type', 'all') == 'all'      ? 'selected' : '' }}>Tutti i tipi</option>
                        <option value="official" {{ request('type') == 'official'        ? 'selected' : '' }}>Ufficiale</option>
                        <option value="user"     {{ request('type') == 'user'            ? 'selected' : '' }}>Tra Utenti</option>
                    </select>
                </div>

                <hr class="my-2">

                
                <div class="mb-4">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-mountain-sun text-warning"></i> Difficoltà
                    </p>
                    <select id="mDiff" class="form-select form-select-lg">
                        <option value="all"       {{ request('difficulty', 'all') == 'all'      ? 'selected' : '' }}>Tutte le difficoltà</option>
                        <option value="facile"    {{ request('difficulty') == 'facile'           ? 'selected' : '' }}>Facile</option>
                        <option value="medio"     {{ request('difficulty') == 'medio'            ? 'selected' : '' }}>Medio</option>
                        <option value="difficile" {{ request('difficulty') == 'difficile'        ? 'selected' : '' }}>Difficile</option>
                    </select>
                </div>

                <hr class="my-2">

                
                <div class="mb-2">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-success"></i> Periodo
                    </p>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small text-muted">Data Da</label>
                            <input type="date" id="mDateFrom" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Data A</label>
                            <input type="date" id="mDateTo" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>

                <hr class="my-2">

                
                <div class="mb-2">
                    <div class="form-check form-switch d-flex align-items-center gap-2 py-2">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="mAvailableOnly" style="width:2.5em; height:1.25em; cursor:pointer;"
                               {{ request('available_only') ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold d-flex align-items-center gap-2 mb-0" for="mAvailableOnly">
                            <i class="fa-solid fa-user-check text-success"></i> Solo posti disponibili
                        </label>
                    </div>
                    <p class="text-muted small mb-0 ms-1">Mostra solo le uscite che non hanno ancora raggiunto il numero massimo di partecipanti.</p>
                </div>

            </div>

            
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="btnResetModal">
                    Azzera Filtri
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-primary" id="btnApplyFilters">Applica Filtri</button>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-4">

    @forelse($outings as $outing)
        <div class="col">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @if($outing->type == 'official')
                            <span class="badge bg-primary">Ufficiale</span>
                        @else
                            <span class="badge bg-success">Tra Utenti</span>
                        @endif
                        
                        @if($outing->difficulty == 'facile')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Facile</span>
                        @elseif($outing->difficulty == 'medio')
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Medio</span>
                        @elseif($outing->difficulty == 'difficile')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Difficile</span>
                        @endif
                    </div>
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
                    @php $cardFullAccess = auth()->check(); @endphp
                    @if($cardFullAccess)
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
                        @if($outing->organizer)
                            <p class="small text-muted mb-0">
                                <i class="fa-solid fa-user me-1"></i>
                                Organizzatore: {{ $outing->organizer->name }}
                                @if(auth()->id() === $outing->organizer_id)
                                    <span class="badge bg-secondary ms-1">Tu</span>
                                @endif
                            </p>
                        @endif
                    @else
                        
                        <div class="d-flex flex-column align-items-center justify-content-center py-3 text-center" style="min-height: 90px;">
                            <i class="fa-solid fa-lock text-muted mb-2" style="font-size: 1.4rem; opacity: 0.5;"></i>
                            <p class="text-muted small mb-2">Accedi/Registrati per vedere data, ritrovo e disponibilità.</p>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-right-to-bracket me-1"></i> Accedi
                                </a>
                                <span class="text-muted" style="font-size: 0.75rem; font-weight: 500;">o</span>
                                <a href="{{ route('register') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-user-plus me-1"></i> Registrati
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-transparent border-top-0 pb-4">
                    <a href="{{ route('outings.show', $outing->id) }}" class="btn btn-custom btn-outline-custom w-100">Dettagli e Partecipa</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info text-center py-4">
                <i class="fa-solid fa-compass fa-2x mb-2 text-secondary-emphasis" style="opacity: 0.5;"></i>
                <p class="mb-0 small">Nessuna uscita in programma corrisponde ai filtri selezionati.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection

@section('styles')
<style>
@keyframes filterShake {
    0%   { transform: translateX(0); }
    20%  { transform: translateX(-6px); }
    40%  { transform: translateX(6px); }
    60%  { transform: translateX(-4px); }
    80%  { transform: translateX(4px); }
    100% { transform: translateX(0); }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    
    document.querySelectorAll('.toast').forEach(function (toastEl) {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 5000 });
        bsToast.show();
    });

    
    const mRouteTypeFilter = document.getElementById('mRouteTypeFilter');
    const sectionFullDirection = document.getElementById('sectionFullDirection');
    const mFullDirection = document.getElementById('mFullDirection');
    const sectionCustomRoute = document.getElementById('sectionCustomRoute');
    const mStart    = document.getElementById('mStart');
    const mEnd      = document.getElementById('mEnd');
    const mType     = document.getElementById('mType');
    const mDiff     = document.getElementById('mDiff');
    const mDateFrom = document.getElementById('mDateFrom');
    const mDateTo   = document.getElementById('mDateTo');
    const mAvailableOnly = document.getElementById('mAvailableOnly');

    
    const hRouteTypeFilter = document.getElementById('hRouteTypeFilter');
    const hFullDirection = document.getElementById('hFullDirection');
    const hStart    = document.getElementById('hStart');
    const hEnd      = document.getElementById('hEnd');

    function toggleRouteSections() {
        if (mRouteTypeFilter.value === 'full') {
            sectionFullDirection.style.display = 'block';
            sectionCustomRoute.style.display = 'none';
        } else if (mRouteTypeFilter.value === 'custom') {
            sectionFullDirection.style.display = 'none';
            sectionCustomRoute.style.display = 'block';
        } else {
            sectionFullDirection.style.display = 'none';
            sectionCustomRoute.style.display = 'none';
        }
    }
    
    function disableSelectedLocation() {
        const startVal = mStart.value;
        Array.from(mEnd.options).forEach(opt => {
            if (opt.value && opt.value === startVal) {
                opt.disabled = true;
            } else {
                opt.disabled = false;
            }
        });
    }

    mRouteTypeFilter.addEventListener('change', toggleRouteSections);
    mStart.addEventListener('change', disableSelectedLocation);
    
    
    toggleRouteSections();
    disableSelectedLocation();
    const hType     = document.getElementById('hType');
    const hDiff     = document.getElementById('hDiff');
    const hDateFrom = document.getElementById('hDateFrom');
    const hDateTo   = document.getElementById('hDateTo');
    const hAvailableOnly = document.getElementById('hAvailableOnly');

    
    const errorBanner = document.getElementById('modalErrorBanner');
    const errorList   = document.getElementById('modalErrorList');

    function showModalErrors(errors) {
        errorList.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
        errorBanner.classList.remove('d-none');
        
        errorBanner.style.animation = 'none';
        errorBanner.offsetHeight; 
        errorBanner.style.animation = 'filterShake 0.35s ease';
        errorBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideModalErrors() {
        errorBanner.classList.add('d-none');
        errorList.innerHTML = '';
    }

    
    [mRouteTypeFilter, mFullDirection, mStart, mEnd, mType, mDiff, mDateFrom, mDateTo, mAvailableOnly].forEach(el => {
        if (el) {
            el.addEventListener('input', hideModalErrors);
            el.addEventListener('change', hideModalErrors);
        }
    });

    
    function validate() {
        const startLoc   = mStart.value.trim();
        const endLoc     = mEnd.value.trim();
        const dateFromStr = mDateFrom.value;
        const dateToStr   = mDateTo.value;
        let errors = [];

        
        if (mRouteTypeFilter.value === 'custom' && startLoc && endLoc && startLoc.toLowerCase() === endLoc.toLowerCase()) {
            errors.push("La località di partenza e di arrivo devono essere diverse.");
        }

        
        if (dateFromStr) {
            const dateFromObj = new Date(dateFromStr);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (dateFromObj < today) {
                errors.push("La data di inizio non può essere precedente a quella odierna.");
            }
        }

        
        if (dateToStr) {
            const dateToObj = new Date(dateToStr);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (dateToObj < today) {
                errors.push("La data di fine non può essere precedente a quella odierna.");
            }
        }

        
        if (dateFromStr && dateToStr && new Date(dateFromStr) > new Date(dateToStr)) {
            errors.push("La data di inizio non può essere successiva alla data di fine.");
        }

        return errors;
    }

    
    document.getElementById('btnApplyFilters').addEventListener('click', function () {
        const errors = validate();
        if (errors.length > 0) {
            showModalErrors(errors);
            return;
        }
        hideModalErrors();
        
        let finalRouteType = mRouteTypeFilter.value;
        let finalStart = mStart.value.trim();
        let finalEnd = mEnd.value.trim();
        let finalFullDirection = mFullDirection.value;

        
        if (finalRouteType === 'custom') {
            if (finalStart === 'Soverato' && finalEnd === 'Pizzo') {
                finalRouteType = 'full';
                finalFullDirection = 'soverato-pizzo';
            } else if (finalStart === 'Pizzo' && finalEnd === 'Soverato') {
                finalRouteType = 'full';
                finalFullDirection = 'pizzo-soverato';
            }
        }

        hRouteTypeFilter.value = finalRouteType;
        hFullDirection.value = finalFullDirection;
        hStart.value    = finalStart;
        hEnd.value      = finalEnd;
        hType.value     = mType.value;
        hDiff.value     = mDiff.value;
        hDateFrom.value = mDateFrom.value;
        hDateTo.value   = mDateTo.value;
        hAvailableOnly.value = mAvailableOnly.checked ? '1' : '';
        document.getElementById('filterForm').submit();
    });

    
    document.getElementById('btnResetModal').addEventListener('click', function () {
        mRouteTypeFilter.value = 'all';
        mFullDirection.value = 'soverato-pizzo';
        mStart.value    = '';
        mEnd.value      = '';
        mType.value     = 'all';
        toggleRouteSections();
        disableSelectedLocation();
        mDiff.value     = 'all';
        mDateFrom.value = '';
        mDateTo.value   = '';
        mAvailableOnly.checked = false;
        hideModalErrors();
    });

    
    document.getElementById('filterModal').addEventListener('hidden.bs.modal', hideModalErrors);
});
</script>
@endsection

