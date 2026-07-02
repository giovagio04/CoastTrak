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
    <script>
        (function () {
            const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bs-body-bg);
            /* Aggiungiamo un padding-top per evitare che la navbar fissa copra il contenuto nelle pagine interne */
            padding-top: 90px; 
        }
        
        html[data-bs-theme="light"] body {
            background-color: #f8f9fa;
        }
        
        /* Navbar styling coerente con la Home */
        .navbar {
            background-color: var(--bs-body-bg) !important;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            padding: 15px 0;
            border-bottom: 1px solid var(--bs-border-color-translucent);
        }
        
        html[data-bs-theme="light"] .navbar {
            background-color: rgba(255, 255, 255, 0.98) !important;
        }
        
        html[data-bs-theme="dark"] .navbar {
            background-color: rgba(33, 37, 41, 0.98) !important;
        }

        .navbar-brand { 
            font-weight: 800; 
            color: var(--bs-body-color) !important; 
            font-size: 1.5rem;
        }
        
        html[data-bs-theme="light"] .navbar-brand {
            color: #1a1a1a !important;
        }

        .nav-link {
            font-weight: 600;
            color: var(--bs-secondary-color) !important;
            transition: color 0.3s;
            margin-left: 15px;
        }
        
        html[data-bs-theme="light"] .nav-link {
            color: #333 !important;
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

        /* Dark mode overrides for cards and sections */
        html[data-bs-theme="dark"] {
            --bs-body-bg: #121416;
            --bs-body-color: #e9ecef;
            --bs-secondary-bg: #1f2327;
            --bs-tertiary-bg: #2b3035;
            --bs-card-bg: #1a1d20;
            --bs-border-color: rgba(255, 255, 255, 0.15);
            --bs-border-color-translucent: rgba(255, 255, 255, 0.08);
        }

        html[data-bs-theme="dark"] .card {
            background-color: var(--bs-card-bg);
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        }

        html[data-bs-theme="dark"] .modal-content {
            background-color: #1a1d20;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        html[data-bs-theme="dark"] .list-group-item {
            background-color: transparent;
            color: var(--bs-body-color);
            border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-bs-theme="dark"] .table {
            --bs-table-color: #e9ecef;
            --bs-table-bg: transparent;
            --bs-table-border-color: rgba(255, 255, 255, 0.08);
        }

        html[data-bs-theme="dark"] .table-light {
            --bs-table-color: #f8f9fa;
            --bs-table-bg: #212529;
            --bs-table-border-color: rgba(255, 255, 255, 0.1);
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
                @if(!auth()->check() || auth()->user()->role !== 'admin')
                    <li class="nav-item">
                        <a class="nav-link text-success-emphasis" href="{{ route('outings.index') }}">
                            <i class="fa-solid fa-compass" style="color: #2e8b57;"></i> Esplora cammini
                        </a>
                    </li>
                @endif
            </ul>
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3">
                    <button id="themeToggle" class="btn btn-link text-secondary p-1" title="Cambia tema" style="font-size: 1.25rem; text-decoration: none; border: none; background: transparent;">
                        <i id="themeToggleIcon" class="fa-solid fa-moon"></i>
                    </button>
                </li>
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
                        <li class="nav-item dropdown me-2 position-relative d-flex align-items-center">
                            <a class="nav-link position-relative text-secondary px-2" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1.1rem; transition: color 0.2s;">
                                <i class="fa-solid fa-bell"></i>
                                <span id="notificationBadge" class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                                    0
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0 mt-2" aria-labelledby="notificationDropdown" style="width: 320px; border-radius: 12px; overflow: hidden; right: 0; left: auto;">
                                <div class="p-3 bg-body-secondary border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-bell text-primary me-2"></i>Notifiche</h6>
                                    <button id="markAllReadBtn" class="btn btn-link btn-sm text-decoration-none p-0 text-primary fw-semibold" style="font-size: 0.8rem;">Segna come lette</button>
                                </div>
                                <div id="notificationList" class="overflow-auto" style="max-height: 300px;">
                                    <div class="p-4 text-center text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                        <p class="mb-0 small">Caricamento...</p>
                                    </div>
                                </div>
                            </div>
                        </li>
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

@auth
    @if(auth()->user()->role !== 'admin')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const badge = document.getElementById('notificationBadge');
                const list = document.getElementById('notificationList');
                const markAllBtn = document.getElementById('markAllReadBtn');

                // Funzione per caricare le notifiche
                function loadNotifications() {
                    fetch('{{ route("notifications.index") }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Aggiorna Badge
                        if (data.unread_count > 0) {
                            badge.textContent = data.unread_count;
                            badge.classList.remove('d-none');
                        } else {
                            badge.classList.add('d-none');
                        }

                        // Svuota lista
                        list.innerHTML = '';

                        if (data.notifications.length === 0) {
                            list.innerHTML = `
                                <div class="p-4 text-center text-muted">
                                    <i class="fa-solid fa-bell-slash fa-2x mb-2 text-secondary-emphasis" style="opacity: 0.5;"></i>
                                    <p class="mb-0 small">Non ci sono notifiche</p>
                                </div>
                            `;
                            return;
                        }

                        data.notifications.forEach(n => {
                            const isUnread = !n.read_at;
                            const item = document.createElement('div');
                            item.className = `p-3 border-bottom notification-item ${isUnread ? 'bg-light-blue' : ''}`;
                            item.style.transition = 'background-color 0.2s';
                            
                            if (isUnread) {
                                item.style.backgroundColor = 'rgba(13, 110, 253, 0.05)';
                            }

                            item.innerHTML = `
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1 fw-bold text-dark" style="font-size: 0.9rem;">${n.title}</h6>
                                    ${isUnread ? '<span class="badge bg-primary p-1 rounded-circle" style="width: 8px; height: 8px; display: inline-block;"></span>' : ''}
                                </div>
                                <p class="mb-1 text-muted small text-justify" style="font-size: 0.8rem; line-height: 1.3;">${n.message}</p>
                                <small class="text-muted" style="font-size: 0.7rem;">${n.created_at}</small>
                            `;

                            list.appendChild(item);
                        });
                    })
                    .catch(err => {
                        console.error('Errore nel recupero delle notifiche:', err);
                        list.innerHTML = `
                            <div class="p-4 text-center text-danger">
                                <i class="fa-solid fa-triangle-exclamation fa-2x mb-2"></i>
                                <p class="mb-0 small">Errore nel caricamento delle notifiche</p>
                            </div>
                        `;
                    });
                }

                // Segna come letta
                function markAsRead(id) {
                    fetch(`/notifications/${id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadNotifications();
                        }
                    });
                }

                // Segna tutte come lette
                if (markAllBtn) {
                    markAllBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        fetch('{{ route("notifications.markAllAsRead") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                loadNotifications();
                            }
                        });
                    });
                }

                // Esegui
                loadNotifications();

                // Aggiorna ogni 60 secondi
                setInterval(loadNotifications, 60000);
            });
        </script>
    @endif
@endauth

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

@yield('scripts')
</body>
</html>
