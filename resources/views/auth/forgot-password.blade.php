<x-guest-layout>

    <h2 class="auth-title">Password dimenticata?</h2>
    <p class="auth-subtitle">
        Inserisci il tuo indirizzo email e ti invieremo un link per reimpostare la tua password.
    </p>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="alert alert-success auth-alert" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-4">
            <label for="email" class="form-label auth-label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control auth-input @error('email') is-invalid @enderror"
                placeholder="tuaemail@esempio.it"
                required
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-auth-primary">
                <i class="fa-solid fa-paper-plane me-2"></i>Invia link di reset
            </button>
        </div>

        <hr class="auth-divider">
        <p class="text-center mb-0" style="font-size: 0.875rem; color: var(--bs-secondary-color);">
            Ricordi la password?
            <a href="{{ route('login') }}" class="auth-link ms-1">Torna al login</a>
        </p>
    </form>

</x-guest-layout>
