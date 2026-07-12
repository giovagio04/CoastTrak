@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Gestione Uscite</h2>
    <a href="{{ route('admin.outings.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Crea Uscita Ufficiale</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form action="{{ route('admin.outings.index') }}" method="GET" class="mb-4">
    <div class="row g-2">
        <div class="col-md-12 text-end">
            <button type="button" class="btn btn-outline-secondary shadow-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="fa-solid fa-filter"></i> Filtri Avanzati
            </button>
        </div>
    </div>

    
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-sliders"></i> Filtri Avanzati</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-layer-group text-primary"></i> Tipo Uscita</label>
                        <select name="type" class="form-select">
                            <option value="">Tutti i tipi</option>
                            <option value="official" {{ request('type') === 'official' ? 'selected' : '' }}>Ufficiale</option>
                            <option value="user" {{ request('type') === 'user' ? 'selected' : '' }}>Utente</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fa-solid fa-traffic-light text-warning"></i> Stato</label>
                        <select name="status" class="form-select">
                            <option value="">Tutti gli stati</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>In attesa</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Pubblicata</option>
                            <option value="concluded" {{ request('status') === 'concluded' ? 'selected' : '' }}>Conclusa</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annullata</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rifiutata</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-auto" id="btnResetAdminFilters">Azzera Filtri</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn btn-primary">Applica Filtri</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tappa</th>
                        <th>Creato il</th>
                        <th>Data Uscita</th>
                        <th>Tipo</th>
                        <th>Stato</th>
                        <th>Posti</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($outings as $outing)
                    <tr onclick="window.location='{{ route('admin.outings.show', $outing->id) }}'" style="cursor: pointer;" class="hover-shadow-sm">
                        <td class="fw-bold text-muted">#{{ $outing->id }}</td>
                        <td>{{ $outing->stage_name }}</td>
                        <td>{{ $outing->created_at->format('d/m/Y') }}</td>
                        <td>{{ $outing->date->format('d/m/Y') }}</td>
                        <td>
                            @if($outing->type == 'official')
                                <span class="badge bg-primary">Ufficiale</span>
                            @else
                                <span class="badge bg-success">Utente</span>
                            @endif
                        </td>
                        <td>
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
                        </td>
                        <td>{{ $outing->participationRequests->where('status','accepted')->count() }} / {{ $outing->max_participants }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Nessuna uscita trovata con i filtri selezionati.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($outings->hasPages())
            <div class="mt-4">
                {{ $outings->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('btnResetAdminFilters').addEventListener('click', function () {
        
        var modalEl = document.getElementById('filterModal');
        modalEl.querySelector('[name="type"]').value   = '';
        modalEl.querySelector('[name="status"]').value = '';
    });
});
</script>
@endsection
