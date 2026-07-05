<x-guest-layout>

    <h2 class="auth-title">Reimposta Password</h2>
    <p class="auth-subtitle">Scegli una nuova password sicura per il tuo account.</p>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label auth-label">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                class="form-control auth-input @error('email') is-invalid @enderror"
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

        {{-- Nuova Password --}}
        <div class="mb-3">
            <label for="password" class="form-label auth-label">Nuova Password</label>
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

        {{-- Conferma Password --}}
        <div class="mb-4">
            <label for="password_confirmation" class="form-label auth-label">Conferma Nuova Password</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="form-control auth-input @error('password_confirmation') is-invalid @enderror"
                placeholder="Ripeti la nuova password"
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
                <i class="fa-solid fa-key me-2"></i>Reimposta Password
            </button>
        </div>
    </form>

</x-guest-layout>
