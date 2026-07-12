<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CoastTrack - Cammino Kalabria Coast to Coast</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        :root {
            --primary-color: #004c8c;
            --primary-hover: #003366;
            --accent-color: #ff6b35;
            --accent-hover: #e55a2b;
            --success-color: #2e8b57;
            
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-heading: #004c8c;
            --text-muted: #6c757d;
            --navbar-bg: rgba(255, 255, 255, 0.95);
            --navbar-border: rgba(0, 0, 0, 0.08);
            --shadow-color: rgba(0, 0, 0, 0.05);
            
            --split-bg: linear-gradient(135deg, #004c8c 0%, #2e8b57 100%);
            --icon-1: #2e8b57;
            --icon-2: #ff6b35;
            --icon-3: #004c8c;
        }

        html[data-bs-theme="dark"] {
            --primary-color: #3b82f6;
            --primary-hover: #60a5fa;
            --accent-color: #ff8552;
            --accent-hover: #ff9e75;
            --success-color: #4ade80;
            
            --bg-color: #121416;
            --card-bg: #1a1d20;
            --text-heading: #60a5fa;
            --text-muted: #adb5bd;
            --navbar-bg: rgba(33, 37, 41, 0.95);
            --navbar-border: rgba(255, 255, 255, 0.08);
            --shadow-color: rgba(0, 0, 0, 0.3);
            
            --split-bg: linear-gradient(135deg, #1a1d20 0%, #121416 100%);
            --icon-1: #4ade80;
            --icon-2: #ff8552;
            --icon-3: #60a5fa;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--bs-body-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        
        .navbar {
            background-color: var(--navbar-bg) !important;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease, background-color 0.3s ease, border-color 0.3s ease;
            padding: 15px 0;
            border-bottom: 1px solid var(--navbar-border);
        }
        .navbar-brand { 
            font-weight: 800; 
            color: var(--bs-body-color) !important; 
            font-size: 1.5rem;
            transition: color 0.3s ease;
        }
        .nav-link {
            font-weight: 600;
            color: var(--bs-body-color) !important;
            transition: color 0.3s ease, opacity 0.3s ease;
            margin-left: 15px;
            opacity: 0.85;
        }
        .nav-link:hover {
            color: var(--accent-color) !important;
            opacity: 1;
        }

        
        .hero {
            position: relative;
            height: 100vh;
            min-height: 600px;
            display: flex;
            align-items: center;
            color: white;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset('img/hero_real.jpg') }}');
            background-size: cover;
            background-position: center;
            z-index: 1;
            transform: scale(1.05);
            animation: breathe 20s infinite alternate ease-in-out;
        }
        @keyframes breathe {
            0% { transform: scale(1.0); }
            100% { transform: scale(1.1); }
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 76, 140, 0.7) 0%, rgba(46, 139, 87, 0.4) 100%);
            z-index: 2;
        }
        .hero-content {
            position: relative;
            z-index: 3;
            text-align: center;
            margin-top: 50px;
        }
        .hero-title {
            font-size: 5rem;
            font-weight: 800;
            text-shadow: 2px 2px 15px rgba(0,0,0,0.5);
            margin-bottom: 20px;
            letter-spacing: -1px;
            line-height: 1.1;
        }
        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            text-shadow: 1px 1px 10px rgba(0,0,0,0.5);
            margin-bottom: 40px;
        }
        
        
        .btn-custom {
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-primary-custom {
            background-color: var(--accent-color);
            color: white;
            border: none;
            box-shadow: 0 10px 20px rgba(255, 107, 53, 0.2);
        }
        .btn-primary-custom:hover {
            background-color: var(--accent-hover);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(255, 107, 53, 0.3);
        }
        .btn-outline-custom {
            background-color: rgba(255,255,255,0.1);
            color: white;
            border: 2px solid white;
            backdrop-filter: blur(5px);
        }
        .btn-outline-custom:hover {
            background-color: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        
        .feature-section {
            padding: 120px 0;
            background-color: var(--bg-color);
            transition: background-color 0.3s ease;
        }
        .feature-section .text-muted {
            color: var(--text-muted) !important;
            transition: color 0.3s ease;
        }
        .feature-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: var(--card-bg);
            box-shadow: 0 15px 35px var(--shadow-color);
            transition: all 0.4s ease, background-color 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 45px var(--shadow-color);
        }
        .feature-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }
        .feature-icon.icon-1 { color: var(--icon-1); }
        .feature-icon.icon-2 { color: var(--icon-2); }
        .feature-icon.icon-3 { color: var(--icon-3); }
        
        
        .split-section {
            display: flex;
            align-items: stretch;
            background: var(--split-bg);
            color: white;
            padding: 0;
            overflow: hidden;
            transition: background 0.3s ease;
        }
        .split-image {
            background-image: url('{{ asset('img/hikers_real.jpg') }}');
            background-size: cover;
            background-position: center;
            min-height: 600px;
        }
        .split-content {
            padding: 100px 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .footer {
            background-color: var(--navbar-bg);
            color: var(--bs-body-color);
            padding: 60px 0 30px;
            border-top: 1px solid var(--navbar-border);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        .footer .text-muted {
            color: var(--text-muted) !important;
            transition: color 0.3s ease;
        }
        
        
        .fade-up {
            animation: fadeUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(40px);
        }
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        
        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        
        @media (max-width: 768px) {
            .hero-title { font-size: 3rem; }
            .split-content { padding: 50px 30px; }
            .split-image { min-height: 300px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <i class="fa-solid fa-person-hiking" style="color: var(--success-color);"></i> CoastTrack
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
            </ul>
            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item me-lg-3">
                    <button id="themeToggle" class="btn btn-link nav-link d-flex align-items-center w-100 text-start p-2 p-lg-1" title="Cambia tema" style="border: none; background: transparent;">
                        <i id="themeToggleIcon" class="fa-solid fa-moon" style="font-size: 1.25rem;"></i>
                        <span class="d-lg-none ms-3">Cambia tema</span>
                    </button>
                </li>
                @auth
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="{{ route('admin.outings.index') }}">Gestione Uscite</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="{{ route('admin.users.index') }}">Gestione Utenti</a>
                        </li>
                        <li class="nav-item d-flex align-items-center ms-lg-2 mt-2 mt-lg-0 mb-2 mb-lg-0">
                            <form method="POST" action="{{ route('logout') }}" class="m-0 w-100">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 py-2 py-lg-1 d-block w-100 w-lg-auto text-start text-lg-center">
                                    Esci
                                </button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link p-2 p-lg-2" href="{{ route('dashboard') }}">Gestione Uscite</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link p-2 p-lg-2 text-primary" href="{{ route('profile.show') }}"><i class="fa-solid fa-user"></i> Area Personale</a>
                        </li>
                        <li class="nav-item d-flex align-items-center ms-lg-2 mt-2 mt-lg-0 mb-2 mb-lg-0">
                            <form method="POST" action="{{ route('logout') }}" class="m-0 w-100">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 py-2 py-lg-1 d-block w-100 w-lg-auto text-start text-lg-center">
                                    Esci
                                </button>
                            </form>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link p-2 p-lg-2" href="{{ route('login') }}">Accedi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link p-2 p-lg-2" href="{{ route('register') }}">Registrati</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="hero-title fade-up">Dal Mar Ionio<br>al Mar Tirreno</h1>
                <p class="hero-subtitle fade-up delay-1">Unisciti al Cammino Kalabria Coast to Coast. Scopri la natura incontaminata, i borghi antichi e i sapori autentici del Mediterraneo.</p>
                <div class="d-flex justify-content-center flex-wrap gap-3 fade-up delay-2">
                    <a href="{{ route('outings.index') }}" class="btn btn-custom btn-primary-custom">Trova un'escursione</a>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-custom btn-outline-custom">Unisciti alla Community</a>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</section>

<section class="feature-section">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <h2 class="display-5 fw-bold" style="color: var(--text-heading);">Vivi la Calabria a passo lento</h2>
            <p class="lead text-muted mt-3">Un'app pensata per connettere amanti della natura e del trekking sul cammino più bello del sud Italia.</p>
        </div>
        
        <div class="row g-4 mt-4">
            <div class="col-md-4 fade-up delay-1">
                <div class="card feature-card p-4 text-center">
                    <div class="card-body">
                        <i class="fa-solid fa-route feature-icon icon-1"></i>
                        <h4 class="fw-bold mt-3">3 Tappe Mozzafiato</h4>
                        <p class="text-muted mt-3">Da Soverato a Pizzo Calabro, 55 km di pura bellezza attraversando le montagne rigogliose delle Serre calabre.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 fade-up delay-2">
                <div class="card feature-card p-4 text-center">
                    <div class="card-body">
                        <i class="fa-solid fa-compass feature-icon icon-2"></i>
                        <h4 class="fw-bold mt-3">Uscite Ufficiali</h4>
                        <p class="text-muted mt-3">Partecipa a escursioni sicure organizzate dall'amministrazione e guidate da esperti qualificati del territorio.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 fade-up delay-3">
                <div class="card feature-card p-4 text-center">
                    <div class="card-body">
                        <i class="fa-solid fa-users-viewfinder feature-icon icon-3"></i>
                        <h4 class="fw-bold mt-3">Community Attiva</h4>
                        <p class="text-muted mt-3">Completa le tappe, ottieni la Credenziale Digitale e proponi le tue uscite auto-organizzate agli altri esploratori.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container-fluid p-0">
    <div class="row g-0 split-section">
        <div class="col-lg-6 split-content">
            <h2 class="display-4 fw-bold mb-4">La tua prossima avventura inizia qui</h2>
            <p class="lead mb-4" style="color: rgba(255,255,255,0.9);">CoastTrack non è solo un'app, è la porta d'accesso a un'esperienza trasformativa. Che tu sia un escursionista esperto o un principiante, troverai l'uscita giusta per te.</p>
            <ul class="list-unstyled fs-5 mb-5" style="color: rgba(255,255,255,0.9);">
                <li class="mb-3"><i class="fa-solid fa-check text-success me-3"></i> Gestione semplice delle prenotazioni</li>
                <li class="mb-3"><i class="fa-solid fa-check text-success me-3"></i> Dashboard personale con storico tappe</li>
                <li class="mb-3"><i class="fa-solid fa-check text-success me-3"></i> Accesso alle proposte "Tra Utenti"</li>
            </ul>
            <div>
                <a href="{{ route('outings.index') }}" class="btn btn-custom btn-primary-custom px-5">Inizia a Esplorare</a>
            </div>
        </div>
        <div class="col-lg-6 split-image"></div>
    </div>
</section>

<footer class="footer">
    <div class="container text-center">
        <h3 class="fw-bold mb-3"><i class="fa-solid fa-person-hiking" style="color: var(--success-color);"></i> CoastTrack</h3>
        <p class="text-muted mb-1">&copy; {{ date('Y') }} Progetto Universitario di Programmazione Web.</p>
        <p class="text-muted small">Ispirato al vero Cammino Kalabria Coast to Coast.</p>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('themeToggle');
        const toggleIcon = document.getElementById('themeToggleIcon');

        if (!toggleBtn || !toggleIcon) return;

        const updateIcon = (theme) => {
            if (theme === 'dark') {
                toggleIcon.classList.remove('fa-moon');
                toggleIcon.classList.add('fa-sun');
                toggleBtn.classList.remove('text-secondary');
                toggleBtn.classList.add('text-warning');
            } else {
                toggleIcon.classList.remove('fa-sun');
                toggleIcon.classList.add('fa-moon');
                toggleBtn.classList.remove('text-warning');
                toggleBtn.classList.add('text-secondary');
            }
        };

        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateIcon(currentTheme);

        toggleBtn.addEventListener('click', () => {
            const theme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            updateIcon(theme);
        });
    });
</script>
</body>
</html>
