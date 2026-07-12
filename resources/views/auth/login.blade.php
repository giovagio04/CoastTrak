<x-guest-layout>

    
    <h2 class="auth-title">Bentornato!</h2>
    <p class="auth-subtitle">Accedi al tuo account CoastTrack per continuare.</p>

    
    @if (session('status'))
        <div class="alert alert-success auth-alert" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
        </div>
    @endif

    
    @if ($errors->any() && !$errors->has('email') && !$errors->has('password'))
        <div class="alert alert-danger auth-alert" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        
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
                autofocus
                autocomplete="username"
            >
            @error('email')
                <div class="invalid-feedback">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        
        <div class="mb-3">
            <label for="password" class="form-label auth-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control auth-input @error('password') is-invalid @enderror"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            >
            @error('password')
                <div class="invalid-feedback">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        
        <div class="mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                <label class="form-check-label" for="remember_me">
                    Ricordami su questo dispositivo
                </label>
            </div>
        </div>

        
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-auth-primary w-100">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Accedi
            </button>
        </div>

        
        <hr class="auth-divider">
        <p class="text-center mb-0" style="font-size: 0.875rem; color: var(--bs-secondary-color);">
            Non hai un account?
            <a href="{{ route('register') }}" class="auth-link ms-1">Registrati ora</a>
        </p>

    </form>

</x-guest-layout>
