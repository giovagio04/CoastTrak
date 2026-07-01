@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold"><i class="fa-solid fa-user text-primary"></i> Area Personale</h2>
</div>

@if(session('status') === 'profile-updated')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Successo!</strong> Il tuo profilo è stato aggiornato.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@elseif(session('status') === 'password-updated')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Successo!</strong> La tua password è stata aggiornata.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Riquadro Sinistro: Informazioni Profilo (col-md-8) -->
    <div class="col-md-8">
        <div class="card shadow-sm border-0 h-100 position-relative">
            <!-- Pennino e Ingranaggio in alto a sinistra -->
            <div class="position-absolute top-0 start-0 m-3 d-flex gap-2">
                <a href="#" class="text-muted hover-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal" title="Modifica Profilo" style="transition: color 0.2s;">
                    <i class="fa-solid fa-pen-to-square fa-lg text-primary"></i>
                </a>
                <a href="#" class="text-muted hover-secondary" data-bs-toggle="modal" data-bs-target="#accountSettingsModal" title="Impostazioni Account" style="transition: color 0.2s;">
                    <i class="fa-solid fa-gear fa-lg text-secondary"></i>
                </a>
            </div>
            
            <div class="card-body p-4">
                <div class="row align-items-center mb-4 mt-3">
                    <!-- Immagine Profilo -->
                    <div class="col-md-4 text-center border-end">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Profile Photo" class="rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 150px; height: 150px;">
                                <i class="fa-solid fa-user text-secondary fa-4x"></i>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Dati Utente -->
                    <div class="col-md-8 ps-md-4">
                        <div class="mb-3">
                            <span class="text-muted small fw-bold text-uppercase">Nome e Cognome</span>
                            <h4 class="fw-bold text-dark mt-1">{{ $user->name }}</h4>
                        </div>
                        
                        <div class="mb-3">
                            <span class="text-muted small fw-bold text-uppercase">Data di Nascita</span>
                            <p class="text-dark mt-1 mb-0">
                                {{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') : 'Non specificata' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Descrizione (Bio) -->
                <div class="mb-2 border-top pt-3">
                    <span class="text-muted small fw-bold text-uppercase">Descrizione (Bio)</span>
                    <p class="text-dark mt-2 text-justify" style="white-space: pre-wrap;">{{ $user->bio ?: 'Nessuna descrizione inserita.' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Riquadro Destro: Credenziale Digitale (col-md-4) -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-light h-100">
            <div class="card-body text-center p-4">
                <div class="display-4 text-warning mb-3">
                    <i class="fa-solid fa-medal"></i>
                </div>
                <h5 class="fw-bold">Credenziale Digitale</h5>
                <p class="text-muted small mb-4">Statistiche dei tuoi cammini completati</p>
                
                <div class="row text-center mb-4">
                    <div class="col-6 border-end">
                        <h2 class="fw-bold text-success mb-0">{{ $user->completedFullTrailsCount() }}</h2>
                        <small class="text-muted fs-6">Cammini<br>Completi</small>
                    </div>
                    <div class="col-6">
                        <h2 class="fw-bold text-primary mb-0">{{ $user->completedSingleStagesCount() }}</h2>
                        <small class="text-muted fs-6">Tappe<br>Singole</small>
                    </div>
                </div>

                @if($user->canCreateOuting())
                    <hr>
                    <p class="small text-success fw-bold"><i class="fa-solid fa-check-circle"></i> Hai completato il cammino! Puoi proporre uscite tra utenti.</p>
                    <a href="{{ route('user.outings.create') }}" class="btn btn-success w-100 mt-2">Proponi un'uscita</a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal di Modifica Profilo -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('patch')
                <input type="hidden" name="email" value="{{ $user->email }}">
                
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="editProfileModalLabel"><i class="fa-solid fa-user-gear text-primary"></i> Modifica Profilo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Immagine Profilo -->
                        <div class="col-md-4 text-center border-end">
                            @if($user->profile_photo_path)
                                <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Profile Photo" class="rounded-circle img-thumbnail mb-3" style="width: 130px; height: 130px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 130px; height: 130px;">
                                    <i class="fa-solid fa-user text-secondary fa-3x"></i>
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
                        
                        <!-- Campi di Testo -->
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold">Nome e Cognome</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label fw-bold">Data di Nascita</label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <label for="bio" class="form-label fw-bold">Descrizione (Bio)</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="4" placeholder="Scrivi qualcosa su di te, sulle tue passioni per il trekking, ecc...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary-custom px-4">Salva Modifiche</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal delle Impostazioni Account (Ingranaggio) -->
<div class="modal fade" id="accountSettingsModal" tabindex="-1" aria-labelledby="accountSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="accountSettingsModalLabel"><i class="fa-solid fa-gear text-secondary"></i> Impostazioni Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 pt-3">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-settings" type="button" role="tab" aria-controls="email-settings" aria-selected="true">Cambia Email</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-settings" type="button" role="tab" aria-controls="password-settings" aria-selected="false">Password</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link text-danger fw-bold" id="delete-tab" data-bs-toggle="tab" data-bs-target="#delete-settings" type="button" role="tab" aria-controls="delete-settings" aria-selected="false">Elimina Profilo</button>
                    </li>
                </ul>
                
                <!-- Tab panes -->
                <div class="tab-content">
                    <!-- Tab 1: Cambio Email -->
                    <div class="tab-pane fade show active" id="email-settings" role="tabpanel" aria-labelledby="email-tab">
                        <form method="post" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')
                            <input type="hidden" name="name" value="{{ $user->name }}">
                            
                            <div class="mb-3">
                                <label for="settings_email" class="form-label fw-bold">Nuovo Indirizzo Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="settings_email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary-custom">Aggiorna Email</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab 2: Cambio Password -->
                    <div class="tab-pane fade" id="password-settings" role="tabpanel" aria-labelledby="password-tab">
                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-bold">Password Attuale</label>
                                <input type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" id="current_password" name="current_password" required>
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label fw-bold">Nuova Password</label>
                                <input type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" id="new_password" name="password" required>
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password_confirmation" class="form-label fw-bold">Conferma Nuova Password</label>
                                <input type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" id="new_password_confirmation" name="password_confirmation" required>
                                @error('password_confirmation', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary-custom">Aggiorna Password</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Tab 3: Elimina Profilo -->
                    <div class="tab-pane fade text-center py-2" id="delete-settings" role="tabpanel" aria-labelledby="delete-tab">
                        <p class="text-muted">Una volta cancellato il tuo account, tutti i tuoi dati associati verranno eliminati permanentemente.</p>
                        <button type="button" class="btn btn-danger px-4 mt-2" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            Elimina Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal di conferma eliminazione account -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                
                <div class="modal-header bg-danger text-white border-bottom-0">
                    <h5 class="modal-title fw-bold" id="deleteAccountModalLabel">Sei sicuro di voler eliminare il profilo?</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted text-justify">Questa azione è irreversibile. Tutti i tuoi dati, le tue partecipazioni e le tue credenziali verranno persi per sempre.</p>
                    <p class="text-muted fw-bold">Inserisci la tua password attuale per confermare l'operazione:</p>
                    
                    <div class="mb-3">
                        <label for="delete_password" class="form-label fw-bold">Password</label>
                        <input type="password" name="password" id="delete_password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" required placeholder="Inserisci la tua password">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#accountSettingsModal">Annulla</button>
                    <button type="submit" class="btn btn-danger px-4">Elimina Definitivamente</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->userDeletion->isNotEmpty())
    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
            myModal.show();
        });
    </script>
    @endsection
@elseif($errors->updatePassword->isNotEmpty())
    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('accountSettingsModal'));
            myModal.show();
            var tabEl = document.querySelector('#password-tab');
            var tab = new bootstrap.Tab(tabEl);
            tab.show();
        });
    </script>
    @endsection
@elseif($errors->has('email'))
    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('accountSettingsModal'));
            myModal.show();
            var tabEl = document.querySelector('#email-tab');
            var tab = new bootstrap.Tab(tabEl);
            tab.show();
        });
    </script>
    @endsection
@elseif($errors->any())
    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var myModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
            myModal.show();
        });
    </script>
    @endsection
@endif

@endsection
