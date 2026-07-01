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
                                <input class="form-control form-control-sm @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" type="file" accept="image/*">
                                @error('profile_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Nome e Cognome</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label fw-bold">Data di Nascita</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', auth()->user()->date_of_birth) }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="bio" class="form-label fw-bold">Descrizione (Bio)</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="4" placeholder="Scrivi qualcosa su di te, sulle tue passioni per il trekking, ecc...">{{ old('bio', auth()->user()->bio) }}</textarea>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary-custom px-4">Salva Modifiche</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- La mia Credenziale Digitale -->
        <div class="card shadow-sm border-0 bg-light mb-4">
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

        <!-- Sicurezza -->
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
