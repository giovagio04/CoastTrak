@extends('layouts.main')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h1 class="display-5 fw-bold">Uscite in Programma</h1>
        <p class="lead text-muted">Scopri le escursioni sul Cammino Kalabria Coast to Coast e unisciti a noi.</p>
    </div>
</div>

<!-- Toast container per errori server-side -->
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

<!-- Barra filtri + form nascosto per submit -->
<div class="d-flex align-items-center gap-2 mb-4">

    <!-- Indicatori filtri attivi -->
    @php
        $activeFilters = array_filter([
            request('start_location'), request('end_location'),
            (request('type') && request('type') !== 'all') ? request('type') : null,
            (request('difficulty') && request('difficulty') !== 'all') ? request('difficulty') : null,
            request('date_from'), request('date_to'),
        ]);
    @endphp

    <button type="button" class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#filterModal">
        <i class="fa-solid fa-sliders me-2"></i>Filtri Avanzati
        @if(count($activeFilters) > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ count($activeFilters) }}
            </span>
        @endif
    </button>

    <form action="{{ route('outings.index') }}" method="GET" id="filterForm">
        <!-- Campi nascosti che ricevono i valori dal modal -->
        <input type="hidden" name="start_location" id="hStart" value="{{ request('start_location') }}">
        <input type="hidden" name="end_location"   id="hEnd"   value="{{ request('end_location') }}">
        <input type="hidden" name="type"            id="hType"  value="{{ request('type', 'all') }}">
        <input type="hidden" name="difficulty"      id="hDiff"  value="{{ request('difficulty', 'all') }}">
        <input type="hidden" name="date_from"       id="hDateFrom" value="{{ request('date_from') }}">
        <input type="hidden" name="date_to"         id="hDateTo"   value="{{ request('date_to') }}">
    </form>

    @if(count($activeFilters) > 0)
        <a href="{{ route('outings.index') }}" class="btn btn-outline-danger" title="Azzera filtri">
            <i class="fa-solid fa-rotate-left"></i>
        </a>
    @endif
</div>

<!-- ============================================================
     MODAL Filtri Avanzati
============================================================ -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- Header -->
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="filterModalLabel">
                    <i class="fa-solid fa-sliders text-primary"></i> Filtri Avanzati
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>

            <!-- Body -->
            <div class="modal-body px-4 py-3">

                <!-- Banner errori client-side (nascosto di default) -->
                <div id="modalErrorBanner" class="d-none mb-3 rounded-3 p-3" style="background: #fff3cd; border-left: 4px solid #f59e0b;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation mt-1" style="color: #d97706;"></i>
                        <div>
                            <p class="fw-semibold mb-1 small" style="color: #92400e;">Controlla i dati inseriti</p>
                            <ul id="modalErrorList" class="mb-0 small ps-3" style="color: #78350f;"></ul>
                        </div>
                    </div>
                </div>

                <!-- Partenza -->
                <div class="mb-4">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-flag text-warning"></i> Partenza
                    </p>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fa-solid fa-flag text-muted"></i>
                        </span>
                        <input type="text" id="mStart" class="form-control border-start-0"
                               placeholder="es. Soverato" value="{{ request('start_location') }}">
                    </div>
                </div>

                <hr class="my-2">

                <!-- Arrivo -->
                <div class="mb-4">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-location-dot text-danger"></i> Arrivo
                    </p>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="fa-solid fa-location-dot text-muted"></i>
                        </span>
                        <input type="text" id="mEnd" class="form-control border-start-0"
                               placeholder="es. Pizzo" value="{{ request('end_location') }}">
                    </div>
                </div>

                <hr class="my-2">

                <!-- Tipo Uscita -->
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

                <!-- Difficoltà -->
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

                <!-- Intervallo date -->
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

            </div><!-- /modal-body -->

            <!-- Footer -->
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
                    @elseif($outing->organizer)
                        <p class="small text-muted mb-0">
                            <i class="fa-solid fa-user me-1"></i> 
                            Organizzatore: {{ $outing->organizer->name }}
                            @if(auth()->check() && auth()->id() === $outing->organizer_id)
                                <span class="badge bg-secondary ms-1">Tu</span>
                            @endif
                        </p>
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

    // Auto-dismiss dei toast server-side dopo 5 secondi
    document.querySelectorAll('.toast').forEach(function (toastEl) {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 5000 });
        bsToast.show();
    });

    // Elementi del modal
    const mStart    = document.getElementById('mStart');
    const mEnd      = document.getElementById('mEnd');
    const mType     = document.getElementById('mType');
    const mDiff     = document.getElementById('mDiff');
    const mDateFrom = document.getElementById('mDateFrom');
    const mDateTo   = document.getElementById('mDateTo');

    // Campi hidden del form
    const hStart    = document.getElementById('hStart');
    const hEnd      = document.getElementById('hEnd');
    const hType     = document.getElementById('hType');
    const hDiff     = document.getElementById('hDiff');
    const hDateFrom = document.getElementById('hDateFrom');
    const hDateTo   = document.getElementById('hDateTo');

    // Banner errori nel modal
    const errorBanner = document.getElementById('modalErrorBanner');
    const errorList   = document.getElementById('modalErrorList');

    function showModalErrors(errors) {
        errorList.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
        errorBanner.classList.remove('d-none');
        // Shake animation
        errorBanner.style.animation = 'none';
        errorBanner.offsetHeight; // reflow
        errorBanner.style.animation = 'filterShake 0.35s ease';
        errorBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideModalErrors() {
        errorBanner.classList.add('d-none');
        errorList.innerHTML = '';
    }

    // Nasconde il banner ogni volta che l'utente modifica un campo
    [mStart, mEnd, mType, mDiff, mDateFrom, mDateTo].forEach(el => {
        el.addEventListener('input', hideModalErrors);
        el.addEventListener('change', hideModalErrors);
    });

    // Funzione di validazione
    function validate() {
        const startLoc   = mStart.value.trim();
        const endLoc     = mEnd.value.trim();
        const dateFromStr = mDateFrom.value;
        const dateToStr   = mDateTo.value;
        let errors = [];

        // Controllo 1: partenza ed arrivo diversi
        if (startLoc && endLoc && startLoc.toLowerCase() === endLoc.toLowerCase()) {
            errors.push("La località di partenza e di arrivo devono essere diverse.");
        }

        // Controllo 2: data d'inizio >= oggi
        if (dateFromStr) {
            const dateFromObj = new Date(dateFromStr);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (dateFromObj < today) {
                errors.push("La data di inizio non può essere precedente a quella odierna.");
            } else if (dateFromObj.getFullYear() > 2100) {
                errors.push("La data di inizio inserita non è valida.");
            }
        }

        // Controllo 3: data fine valida
        if (dateToStr) {
            const year = new Date(dateToStr).getFullYear();
            if (year < 2000 || year > 2100) {
                errors.push("La data di fine inserita non è valida.");
            }
        }

        // Controllo 4: date da <= data a
        if (dateFromStr && dateToStr && new Date(dateFromStr) > new Date(dateToStr)) {
            errors.push("La data di inizio non può essere successiva alla data di fine.");
        }

        return errors;
    }

    // "Applica Filtri": valida e, se ok, trasferisce i valori e fa submit
    document.getElementById('btnApplyFilters').addEventListener('click', function () {
        const errors = validate();
        if (errors.length > 0) {
            showModalErrors(errors);
            return;
        }
        hideModalErrors();
        hStart.value    = mStart.value.trim();
        hEnd.value      = mEnd.value.trim();
        hType.value     = mType.value;
        hDiff.value     = mDiff.value;
        hDateFrom.value = mDateFrom.value;
        hDateTo.value   = mDateTo.value;
        document.getElementById('filterForm').submit();
    });

    // "Azzera Filtri": svuota i campi del modal senza chiuderlo o ricaricare la pagina
    document.getElementById('btnResetModal').addEventListener('click', function () {
        mStart.value    = '';
        mEnd.value      = '';
        mType.value     = 'all';
        mDiff.value     = 'all';
        mDateFrom.value = '';
        mDateTo.value   = '';
        hideModalErrors();
    });

    // Nasconde errori alla chiusura del modal
    document.getElementById('filterModal').addEventListener('hidden.bs.modal', hideModalErrors);
});
</script>
@endsection

