@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fa-solid fa-user-gear text-primary"></i> Area Personale</h2>
</div>

@if(session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Successo!</strong> Il tuo profilo è stato aggiornato.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <h5 class="fw-bold"><i class="fa-solid fa-id-card text-success"></i> Informazioni Profilo</h5>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('patch')

                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            @if(auth()->user()->profile_photo_path)
                                <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}" alt="Profile Photo" class="rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px;">
                                    <i class="fa-solid fa-user text-secondary fa-4x"></i>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label for="profile_photo" class="form-label fw-bold">Immagine Profilo</label>
                                <input class="form-control form-control-sm" id="profile_photo" name="profile_photo" type="file" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Nome e Cognome</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label fw-bold">Data di Nascita</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', auth()->user()->date_of_birth) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bio" class="form-label fw-bold">Descrizione (Bio)</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Scrivi qualcosa su di te, sulle tue passioni per il trekking, ecc...">{{ old('bio', auth()->user()->bio) }}</textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary-custom px-4">Salva Modifiche</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
                <h5 class="fw-bold"><i class="fa-solid fa-key text-warning"></i> Sicurezza</h5>
            </div>
            <div class="card-body text-center p-4">
                <p class="text-muted">Desideri cambiare la tua password o gestire altre impostazioni di sicurezza?</p>
                <button class="btn btn-outline-secondary w-100" disabled>Cambia Password (Presto disponibile)</button>
            </div>
        </div>
    </div>
</div>
@endsection
