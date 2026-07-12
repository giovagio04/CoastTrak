@extends('layouts.main')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Area Personale</a></li>
    <li class="breadcrumb-item active" aria-current="page">Proponi Uscita</li>
  </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-success text-white pt-4 pb-3">
                <h4 class="fw-bold mb-0"><i class="fa-solid fa-person-hiking me-2"></i> Proponi un'Uscita tra Utenti</h4>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-info border-start border-4 border-top-0 border-end-0 border-bottom-0 mb-4">
                    <strong>Nota:</strong> Questa è un'uscita auto-organizzata. L'amministratore dovrà revisionarla e approvarla (ha un tempo massimo di 24 ore). Scegli responsabilmente tappa e livello di difficoltà.
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('user.outings.store') }}" method="POST" onsubmit="document.getElementById('submitBtn').disabled = true; document.getElementById('submitBtn').innerHTML = '<i class=\'fa-solid fa-spinner fa-spin\'></i> Invio...'; return true;">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo di Itinerario</label>
                        <div class="d-flex gap-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="route_type" id="route_standard" value="standard" checked onchange="toggleCustomRoute()">
                                <label class="form-check-label" for="route_standard">
                                    🌟 Cammino Completo
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="route_type" id="route_custom" value="custom" onchange="toggleCustomRoute()">
                                <label class="form-check-label" for="route_custom">
                                    📍 Personalizzato
                                </label>
                            </div>
                        </div>

                        <div id="full_route_selectors" class="mt-3">
                            <label for="full_direction" class="form-label small fw-bold">Direzione Cammino</label>
                            <select name="full_direction" id="full_direction" class="form-select" style="max-width: 300px;">
                                <option value="soverato-pizzo">Soverato ➔ Pizzo (Consigliato)</option>
                                <option value="pizzo-soverato">Pizzo ➔ Soverato</option>
                            </select>
                        </div>

                        <div id="custom_route_selectors" class="mt-3" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="start_location" class="form-label small fw-bold">Tappa di Partenza</label>
                                    <select name="start_location" id="start_location" class="form-select">
                                        <option value="">-- Seleziona --</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location }}">{{ $location }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_location" class="form-label small fw-bold">Tappa di Arrivo</label>
                                    <select name="end_location" id="end_location" class="form-select">
                                        <option value="">-- Seleziona --</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location }}">{{ $location }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Data Proposta</label>
                            <input type="date" name="date" class="form-control" min="{{ date('Y-m-d', strtotime('+1 day')) }}" max="{{ date('Y-m-d', strtotime('+3 years')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Posti Massimi (tu incluso)</label>
                            <input type="number" name="max_participants" class="form-control" min="2" max="15" value="5" required>
                            <div class="form-text">Per le uscite auto-organizzate consigliamo gruppi piccoli (max 15).</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Punto e Orario di Ritrovo</label>
                            <input type="text" name="meeting_point" class="form-control" placeholder="es. Bar Centrale Soverato, ore 08:30" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Difficoltà Prevista</label>
                            <select name="difficulty" class="form-select" required>
                                <option value="facile">Facile</option>
                                <option value="medio" selected>Medio</option>
                                <option value="difficile">Difficile</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Note per i partecipanti</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Avvisa gli altri escursionisti se devono portare attrezzatura specifica, acqua extra, pranzo al sacco, ecc."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5">
                        <a href="{{ route('dashboard') }}" class="text-secondary text-decoration-none"><i class="fa-solid fa-arrow-left"></i> Torna indietro</a>
                        <button type="submit" id="submitBtn" class="btn btn-success btn-lg px-5 shadow-sm">Proponi e Invia per Approvazione</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleCustomRoute() {
    const isCustom = document.getElementById('route_custom').checked;
    const customSelectors = document.getElementById('custom_route_selectors');
    const fullSelectors = document.getElementById('full_route_selectors');
    const startStage = document.getElementById('start_location');
    const endStage = document.getElementById('end_location');

    if (isCustom) {
        customSelectors.style.display = 'block';
        fullSelectors.style.display = 'none';
        startStage.required = true;
        endStage.required = true;
    } else {
        customSelectors.style.display = 'none';
        fullSelectors.style.display = 'block';
        startStage.required = false;
        endStage.required = false;
    }
}

function filterEndStages() {
    const startStage = document.getElementById('start_location');
    const endStage = document.getElementById('end_location');
    const selectedStartVal = startStage.value;
    
    Array.from(endStage.options).forEach(option => {
        if (option.value === "") return;
        if (option.value === selectedStartVal) {
            option.disabled = true;
            if (endStage.value === option.value) endStage.value = "";
        } else {
            option.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    toggleCustomRoute();
    document.getElementById('start_location').addEventListener('change', filterEndStages);
});
</script>
@endsection
