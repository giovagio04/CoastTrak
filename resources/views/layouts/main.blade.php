<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoastTrack - Cammino Kalabria Coast to Coast</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
            /* Aggiungiamo un padding-top per evitare che la navbar fissa copra il contenuto nelle pagine interne */
            padding-top: 90px; 
        }
        
        /* Navbar styling coerente con la Home */
        .navbar {
            background-color: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            padding: 15px 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .navbar-brand { 
            font-weight: 800; 
            color: #1a1a1a !important; 
            font-size: 1.5rem;
        }
        .nav-link {
            font-weight: 600;
            color: #333 !important;
            transition: color 0.3s;
            margin-left: 15px;
        }
        .nav-link:hover {
            color: #ff6b35 !important;
        }

        /* Stili bottoni presi dalla Home */
        .btn-custom {
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }
        .btn-primary-custom {
            background-color: #ff6b35;
            color: white;
            border: none;
            box-shadow: 0 4px 10px rgba(255, 107, 53, 0.2);
        }
        .btn-primary-custom:hover {
            background-color: #e55a2b;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 107, 53, 0.3);
        }
        .btn-outline-custom {
            color: #004c8c;
            border: 2px solid #004c8c;
            background: transparent;
        }
        .btn-outline-custom:hover {
            background-color: #004c8c;
            color: white;
            transform: translateY(-2px);
        }

        /* Cards coerenti */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
        }

        /* Footer coerente con la Home */
        .footer {
            background-color: #1a1a1a;
            color: #f8f9fa;
            padding: 50px 0 20px;
            margin-top: 60px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fa-solid fa-person-hiking" style="color: #2e8b57;"></i> CoastTrack
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link text-success-emphasis" href="{{ route('outings.index') }}">
                        <i class="fa-solid fa-compass" style="color: #2e8b57;"></i> Esplora cammini
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="{{ route('admin.outings.index') }}">
                                Gestione Uscite
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="{{ route('admin.users.index') }}">
                                Gestione Utenti
                            </a>
                        </li>
                        <li class="nav-item d-flex align-items-center ms-2">
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                    Esci
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Gestione Uscite</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-primary" href="{{ route('profile.show') }}"><i class="fa-solid fa-user"></i> Area Personale</a>
                        </li>
                        <li class="nav-item d-flex align-items-center ms-2">
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                    Esci
                                </button>
                            </form>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Accedi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-custom btn-primary-custom ms-2 text-white" href="{{ route('register') }}" style="color: white !important;">Registrati</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
    @yield('content')
</main>

<footer class="footer">
    <div class="container text-center">
        <h4 class="fw-bold mb-3"><i class="fa-solid fa-person-hiking" style="color: #2e8b57;"></i> CoastTrack</h4>
        <p class="text-muted mb-1">&copy; {{ date('Y') }} Progetto Universitario di Programmazione Web.</p>
        <p class="text-muted small">Ispirato al vero Cammino Kalabria Coast to Coast.</p>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
