<x-guest-layout>

    
    <h2 class="auth-title">Crea un account</h2>
    <p class="auth-subtitle">Unisciti alla comunità CoastTrack e inizia a camminare.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        
        <div class="mb-3">
            <label for="name" class="form-label auth-label">Nome</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control auth-input @error('name') is-invalid @enderror"
                placeholder="Mario Rossi"
                required
                autofocus
                autocomplete="name"
            >
            @error('name')
                <div class="invalid-feedback">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        
        <div class="mb-3">
            <label for="email" class="form-label auth-label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control auth-input @error('email') is-invalid @enderror"
                placeholder="tuaemail@esempio.it"
                pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}"
                title="Inserisci un indirizzo email valido (es: nome@dominio.it)"
                required
                autocomplete="username"
            >
            @error('email')
                <div class="invalid-feedback">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        
        <div class="mb-3">
            <label for="date_of_birth" class="form-label auth-label">Data di Nascita</label>
            <input
                id="date_of_birth"
                type="date"
                name="date_of_birth"
                value="{{ old('date_of_birth') }}"
                class="form-control auth-input @error('date_of_birth') is-invalid @enderror"
                min="{{ now()->subYears(100)->format('Y-m-d') }}"
                max="{{ now()->subYears(18)->format('Y-m-d') }}"
                required
            >
            @error('date_of_birth')
                <div class="invalid-feedback">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
            <small class="text-muted d-block mt-1">Devi avere almeno 18 anni per iscriverti.</small>
        </div>

        
        <div class="mb-3">
            <label for="password" class="form-label auth-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control auth-input @error('password') is-invalid @enderror"
                placeholder="Minimo 8 caratteri"
                required
                autocomplete="new-password"
            >
            @error('password')
                <div class="invalid-feedback">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        
        <div class="mb-4">
            <label for="password_confirmation" class="form-label auth-label">Conferma Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="form-control auth-input @error('password_confirmation') is-invalid @enderror"
                placeholder="Ripeti la password"
                required
                autocomplete="new-password"
            >
            @error('password_confirmation')
                <div class="invalid-feedback">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        
        <div class="d-grid">
            <button type="submit" class="btn btn-auth-primary">
                <i class="fa-solid fa-user-plus me-2"></i>Crea Account
            </button>
        </div>

        
        <hr class="auth-divider">
        <p class="text-center mb-0" style="font-size: 0.875rem; color: var(--bs-secondary-color);">
            Hai già un account?
            <a href="{{ route('login') }}" class="auth-link ms-1">Accedi</a>
        </p>

    </form>

</x-guest-layout>
