@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Gestione Uscite</h2>
</div>

{{-- Toast server-side (successo / errore sessione) --}}
@if(session('success') || session('error') || $errors->any())
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    @if(session('success'))
    <div class="toast align-items-center text-bg-success border-0 shadow mb-2 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Chiudi"></button>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="toast align-items-center text-bg-danger border-0 shadow mb-2 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i>
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Chiudi"></button>
        </div>
    </div>
    @endif
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

<div class="row g-4">
    <!-- Le Mie Partecipazioni -->
    <div class="col-lg-8 col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent pt-4 pb-3 border-bottom-0">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-primary"></i> Le mie uscite</h5>

                {{-- ============================================================
                     Barra filtri
                ============================================================ --}}
                @php
                    $activeFilters = array_filter([
                        request('start_location'),
                        request('end_location'),
                        (request('type') && request('type') !== 'all') ? request('type') : null,
                        (request('difficulty') && request('difficulty') !== 'all') ? request('difficulty') : null,
                        (request('status') && request('status') !== 'all') ? request('status') : null,
                        (request('role') && request('role') !== 'all') ? request('role') : null,
                        request('date_from'),
                        request('date_to'),
                        request('available_only') ? '1' : null,
                    ]);
                @endphp

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary position-relative" data-bs-toggle="modal" data-bs-target="#dashFilterModal">
                        <i class="fa-solid fa-sliders me-2"></i>Filtri Avanzati
                        @if(count($activeFilters) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ count($activeFilters) }}
                            </span>
                        @endif
                    </button>

                    <form action="{{ route('dashboard') }}" method="GET" id="dashFilterForm">
                        <input type="hidden" name="start_location" id="hStart"        value="{{ request('start_location') }}">
                        <input type="hidden" name="end_location"   id="hEnd"          value="{{ request('end_location') }}">
                        <input type="hidden" name="type"           id="hType"         value="{{ request('type', 'all') }}">
                        <input type="hidden" name="difficulty"     id="hDiff"         value="{{ request('difficulty', 'all') }}">
                        <input type="hidden" name="status"         id="hStatus"       value="{{ request('status', 'all') }}">
                        <input type="hidden" name="role"           id="hRole"         value="{{ request('role', 'all') }}">
                        <input type="hidden" name="date_from"      id="hDateFrom"     value="{{ request('date_from') }}">
                        <input type="hidden" name="date_to"        id="hDateTo"       value="{{ request('date_to') }}">
                        <input type="hidden" name="available_only" id="hAvailableOnly" value="{{ request('available_only') ? '1' : '' }}">
                    </form>

                    @if(count($activeFilters) > 0)
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-danger" title="Azzera filtri">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body pt-0">
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
                                        <td>
                                            @if($req->outing->status === 'pending')
                                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> PENDING</span>
                                            @elseif($req->outing->status === 'published')
                                                <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> PUBLISHED</span>
                                            @elseif($req->outing->status === 'concluded')
                                                <span class="badge bg-secondary"><i class="fa-solid fa-circle-info me-1"></i> CONCLUDED</span>
                                            @elseif($req->outing->status === 'cancelled')
                                                <span class="badge bg-danger"><i class="fa-solid fa-ban me-1"></i> CANCELLED</span>
                                            @elseif($req->outing->status === 'rejected')
                                                <span class="badge bg-danger"><i class="fa-solid fa-xmark me-1"></i> REJECTED</span>
                                            @else
                                                <span class="badge bg-secondary">{{ strtoupper($req->outing->status) }}</span>
                                            @endif
                                        </td>
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
                                            @if($req->outing->date >= now() && in_array($req->outing->status, ['published']))
                                                @if($req->outing->organizer_id !== auth()->id())
                                                    {{-- Mostro "Annulla" solo se la partecipazione non è già stata revocata (es. a seguito di un ban) --}}
                                                    @if(in_array($req->status, ['accepted', 'pending']))
                                                        <form action="{{ route('participations.destroy', $req->id) }}" method="POST" onsubmit="return confirm('Sicuro di voler annullare la tua partecipazione?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i> Annulla</button>
                                                        </form>
                                                    @else
                                                        {{-- Partecipazione già revocata (es. per ban): nessuna azione disponibile --}}
                                                        <span class="text-muted small">—</span>
                                                    @endif
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
                    <div class="py-4 text-center">
                        <i class="fa-solid fa-compass fa-2x mb-2 text-secondary-emphasis" style="opacity: 0.5;"></i>
                        <p class="mb-2 text-muted small">
                            @if(count($activeFilters) > 0)
                                Nessuna uscita corrisponde ai filtri selezionati.
                            @else
                                Non sei iscritto a nessuna uscita al momento.
                            @endif
                        </p>
                        @if(count($activeFilters) === 0)
                            <a href="{{ route('outings.index') }}" class="btn btn-primary mt-1">Esplora Uscite</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Gestione Organizzazione Uscite -->
    <div class="col-lg-4 col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column justify-content-between p-4">
                <div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-2 me-3" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-route fa-lg"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Organizzazione Uscite</h5>
                    </div>
                    <p class="text-muted small">Vuoi proporre e guidare un nuovo cammino con altri utenti? Puoi creare un'uscita personalizzata qui.</p>
                </div>

                <div class="mt-4">
                    @if(auth()->user()->canCreateOuting())
                        <a href="{{ route('user.outings.create') }}" class="btn btn-success w-100 py-2 fw-semibold shadow-sm">
                            <i class="fa-solid fa-plus-circle me-1"></i> Proponi un'uscita
                        </a>
                        <div class="mt-2 text-success small text-start">
                            <i class="fa-solid fa-circle-check me-1"></i>
                            <span>Hai completato l'intero cammino! Puoi proporre nuove uscite.</span>
                        </div>
                    @else
                        <button class="btn btn-secondary w-100 py-2 fw-semibold shadow-sm" disabled style="cursor: not-allowed; opacity: 0.65;">
                            <i class="fa-solid fa-lock me-1"></i> Proponi un'uscita
                        </button>
                        <div class="mt-2 text-muted small text-start">
                            <i class="fa-solid fa-circle-info text-warning me-1"></i>
                            <span class="fst-italic">Completa un cammino completo prima di poter organizzare un cammino</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ============================================================
     MODAL Filtri Avanzati – Dashboard
============================================================ --}}
<div class="modal fade" id="dashFilterModal" tabindex="-1" aria-labelledby="dashFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">

            <!-- Header -->
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="dashFilterModalLabel">
                    <i class="fa-solid fa-sliders text-primary"></i> Filtri Avanzati
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>

            <!-- Body -->
            <div class="modal-body px-4 py-3">

                <!-- Banner errori client-side -->
                <div id="dashModalErrorBanner" class="d-none mb-3 rounded-3 p-3" style="background: #fff3cd; border-left: 4px solid #f59e0b;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-triangle-exclamation mt-1" style="color: #d97706;"></i>
                        <div>
                            <p class="fw-semibold mb-1 small" style="color: #92400e;">Controlla i dati inseriti</p>
                            <ul id="dashModalErrorList" class="mb-0 small ps-3" style="color: #78350f;"></ul>
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
                        <option value="all"       {{ request('difficulty', 'all') == 'all'   ? 'selected' : '' }}>Tutte le difficoltà</option>
                        <option value="facile"    {{ request('difficulty') == 'facile'        ? 'selected' : '' }}>Facile</option>
                        <option value="medio"     {{ request('difficulty') == 'medio'         ? 'selected' : '' }}>Medio</option>
                        <option value="difficile" {{ request('difficulty') == 'difficile'     ? 'selected' : '' }}>Difficile</option>
                    </select>
                </div>

                <hr class="my-2">

                <!-- Stato Uscita -->
                <div class="mb-4">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-half-stroke text-secondary"></i> Stato Uscita
                    </p>
                    <select id="mStatus" class="form-select form-select-lg">
                        <option value="all"       {{ request('status', 'all') == 'all'       ? 'selected' : '' }}>Tutti gli stati</option>
                        <option value="pending"   {{ request('status') == 'pending'           ? 'selected' : '' }}>In attesa (Pending)</option>
                        <option value="published" {{ request('status') == 'published'         ? 'selected' : '' }}>Pubblicata (Published)</option>
                        <option value="concluded" {{ request('status') == 'concluded'         ? 'selected' : '' }}>Conclusa (Concluded)</option>
                        <option value="cancelled" {{ request('status') == 'cancelled'         ? 'selected' : '' }}>Annullata (Cancelled)</option>
                        <option value="rejected"  {{ request('status') == 'rejected'          ? 'selected' : '' }}>Rifiutata (Rejected)</option>
                    </select>
                </div>

                <hr class="my-2">

                <!-- Ruolo -->
                <div class="mb-4">
                    <p class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-tag text-info"></i> Il mio ruolo
                    </p>
                    <select id="mRole" class="form-select form-select-lg">
                        <option value="all"         {{ request('role', 'all') == 'all'         ? 'selected' : '' }}>Tutti i ruoli</option>
                        <option value="organizer"   {{ request('role') == 'organizer'           ? 'selected' : '' }}>Organizzatore</option>
                        <option value="participant" {{ request('role') == 'participant'         ? 'selected' : '' }}>Partecipante</option>
                    </select>
                </div>

                <hr class="my-2">

                <!-- Periodo -->
                <div class="mb-4">
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

                <!-- Solo posti disponibili -->
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

            </div><!-- /modal-body -->

            <!-- Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="dashBtnReset">
                    Azzera Filtri
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-primary" id="dashBtnApply">Applica Filtri</button>
                </div>
            </div>

        </div>
    </div>
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

    // Elementi del modal
    const mStart        = document.getElementById('mStart');
    const mEnd          = document.getElementById('mEnd');
    const mType         = document.getElementById('mType');
    const mDiff         = document.getElementById('mDiff');
    const mStatus       = document.getElementById('mStatus');
    const mRole         = document.getElementById('mRole');
    const mDateFrom     = document.getElementById('mDateFrom');
    const mDateTo       = document.getElementById('mDateTo');
    const mAvailableOnly = document.getElementById('mAvailableOnly');

    // Campi hidden del form
    const hStart         = document.getElementById('hStart');
    const hEnd           = document.getElementById('hEnd');
    const hType          = document.getElementById('hType');
    const hDiff          = document.getElementById('hDiff');
    const hStatus        = document.getElementById('hStatus');
    const hRole          = document.getElementById('hRole');
    const hDateFrom      = document.getElementById('hDateFrom');
    const hDateTo        = document.getElementById('hDateTo');
    const hAvailableOnly = document.getElementById('hAvailableOnly');

    // Banner errori
    const errorBanner = document.getElementById('dashModalErrorBanner');
    const errorList   = document.getElementById('dashModalErrorList');

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

    [mStart, mEnd, mType, mDiff, mStatus, mRole, mDateFrom, mDateTo, mAvailableOnly].forEach(el => {
        el.addEventListener('input', hideModalErrors);
        el.addEventListener('change', hideModalErrors);
    });

    function validate() {
        const startLoc    = mStart.value.trim();
        const endLoc      = mEnd.value.trim();
        const dateFromStr = mDateFrom.value;
        const dateToStr   = mDateTo.value;
        let errors = [];

        // Costruisce la data odierna (senza orario)
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Controllo 1: partenza e arrivo diversi
        if (startLoc && endLoc && startLoc.toLowerCase() === endLoc.toLowerCase()) {
            errors.push('La località di partenza e quella di arrivo devono essere diverse.');
        }

        // Controllo 2: lunghezza massima località
        if (startLoc.length > 100) {
            errors.push('La località di partenza non può superare i 100 caratteri.');
        }
        if (endLoc.length > 100) {
            errors.push('La località di arrivo non può superare i 100 caratteri.');
        }

        // Controllo 3: Data Da – non precedente ad oggi
        if (dateFromStr) {
            const dateFrom = new Date(dateFromStr);
            if (dateFrom < today) {
                errors.push('La data di inizio non può essere precedente a quella odierna.');
            }
        }

        // Controllo 4: Data A – non precedente ad oggi
        if (dateToStr) {
            const dateTo = new Date(dateToStr);
            if (dateTo < today) {
                errors.push('La data di fine non può essere precedente a quella odierna.');
            }
        }

        // Controllo 5: Data Da ≤ Data A
        if (dateFromStr && dateToStr && new Date(dateFromStr) > new Date(dateToStr)) {
            errors.push('La data di inizio non può essere successiva alla data di fine.');
        }

        return errors;
    }

    // Applica Filtri
    document.getElementById('dashBtnApply').addEventListener('click', function () {
        const errors = validate();
        if (errors.length > 0) {
            showModalErrors(errors);
            return;
        }
        hideModalErrors();
        hStart.value         = mStart.value.trim();
        hEnd.value           = mEnd.value.trim();
        hType.value          = mType.value;
        hDiff.value          = mDiff.value;
        hStatus.value        = mStatus.value;
        hRole.value          = mRole.value;
        hDateFrom.value      = mDateFrom.value;
        hDateTo.value        = mDateTo.value;
        hAvailableOnly.value = mAvailableOnly.checked ? '1' : '';
        document.getElementById('dashFilterForm').submit();
    });

    // Azzera Filtri
    document.getElementById('dashBtnReset').addEventListener('click', function () {
        mStart.value          = '';
        mEnd.value            = '';
        mType.value           = 'all';
        mDiff.value           = 'all';
        mStatus.value         = 'all';
        mRole.value           = 'all';
        mDateFrom.value       = '';
        mDateTo.value         = '';
        mAvailableOnly.checked = false;
        hideModalErrors();
    });

    // Nasconde errori alla chiusura del modal
    document.getElementById('dashFilterModal').addEventListener('hidden.bs.modal', hideModalErrors);

    // Auto-dismiss dei toast server-side dopo 5 secondi
    document.querySelectorAll('.toast').forEach(function (toastEl) {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 5000 });
        bsToast.show();
    });

    // Se ci sono errori server-side sui filtri, riapri automaticamente il modal
    @if($errors->any())
    const dashModal = new bootstrap.Modal(document.getElementById('dashFilterModal'));
    dashModal.show();
    @endif
});
</script>
@endsection
