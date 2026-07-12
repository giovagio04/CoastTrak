@extends('layouts.main')

@section('content')
<nav aria-label="breadcrumb" class="mb-4">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.outings.index') }}">Gestione Uscite</a></li>
    <li class="breadcrumb-item active" aria-current="page">Modifica Uscita</li>
  </ol>
</nav>

<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent border-bottom-0 pt-4">
        <h4 class="fw-bold mb-0">Modifica Uscita #{{ $outing->id }}</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.outings.update', $outing->id) }}" method="POST" onsubmit="document.getElementById('submitBtn').disabled = true; document.getElementById('submitBtn').innerHTML = '<i class=\'fa-solid fa-spinner fa-spin\'></i> Salvataggio...'; return true;">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Stato dell'uscita</label>
                    <select name="status" class="form-select border-primary" required>
                        <option value="pending" {{ $outing->status == 'pending' ? 'selected' : '' }}>In attesa di approvazione</option>
                        <option value="published" {{ $outing->status == 'published' ? 'selected' : '' }}>Pubblicata (Visibile)</option>
                        <option value="concluded" {{ $outing->status == 'concluded' ? 'selected' : '' }}>Conclusa</option>
                        <option value="cancelled" {{ $outing->status == 'cancelled' ? 'selected' : '' }}>Annullata</option>
                    </select>
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tipo di Itinerario</label>
                    <div class="d-flex gap-4 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="route_type" id="route_standard" value="standard" {{ $outing->is_full_trail ? 'checked' : '' }} onchange="toggleCustomRoute()">
                            <label class="form-check-label" for="route_standard">
                                🌟 Cammino Completo
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="route_type" id="route_custom" value="custom" {{ !$outing->is_full_trail ? 'checked' : '' }} onchange="toggleCustomRoute()">
                            <label class="form-check-label" for="route_custom">
                                📍 Personalizzato
                            </label>
                        </div>
                    </div>

                    <div id="full_route_selectors" class="mt-3" style="display: {{ $outing->is_full_trail ? 'block' : 'none' }};">
                        <label for="full_direction" class="form-label small fw-bold">Direzione Cammino</label>
                        <select name="full_direction" id="full_direction" class="form-select" style="max-width: 300px;">
                            <option value="soverato-pizzo" {{ $outing->start_location == 'Soverato' ? 'selected' : '' }}>Soverato ➔ Pizzo (Consigliato)</option>
                            <option value="pizzo-soverato" {{ $outing->start_location == 'Pizzo' ? 'selected' : '' }}>Pizzo ➔ Soverato</option>
                        </select>
                    </div>

                    <div id="custom_route_selectors" class="mt-3" style="display: {{ !$outing->is_full_trail ? 'block' : 'none' }};">
                        <div class="row">
                            <div class="col-6">
                                <label for="start_location" class="form-label small fw-bold">Tappa di Partenza</label>
                                <select name="start_location" id="start_location" class="form-select">
                                    <option value="">-- Seleziona --</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}" {{ $outing->start_location == $location ? 'selected' : '' }}>{{ $location }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="end_location" class="form-label small fw-bold">Tappa di Arrivo</label>
                                <select name="end_location" id="end_location" class="form-select">
                                    <option value="">-- Seleziona --</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}" {{ $outing->end_location == $location ? 'selected' : '' }}>{{ $location }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Data Uscita</label>
                    <input type="date" name="date" class="form-control" value="{{ $outing->date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Posti Max</label>
                    <input type="number" name="max_participants" class="form-control" min="1" max="50" value="{{ $outing->max_participants }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">Punto di Ritrovo</label>
                    <input type="text" name="meeting_point" class="form-control" value="{{ $outing->meeting_point }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Difficoltà</label>
                    <select name="difficulty" class="form-select" required>
                        <option value="facile" {{ $outing->difficulty == 'facile' ? 'selected' : '' }}>Facile</option>
                        <option value="medio" {{ $outing->difficulty == 'medio' ? 'selected' : '' }}>Medio</option>
                        <option value="difficile" {{ $outing->difficulty == 'difficile' ? 'selected' : '' }}>Difficile</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Note aggiuntive</label>
                <textarea name="notes" class="form-control" rows="3">{{ $outing->notes }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.outings.index') }}" class="btn btn-light border">Annulla</a>
                <button type="submit" id="submitBtn" class="btn btn-primary">Salva Modifiche</button>
            </div>
        </form>
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
    filterEndStages(); // Call once on load for edit view
});
</script>
@endsection
