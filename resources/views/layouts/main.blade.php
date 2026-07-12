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
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bs-body-bg);
            
            padding-top: 90px; 
        }
        
        html[data-bs-theme="light"] body {
            background-color: #f8f9fa;
        }
        
        
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

        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .card-header {
            border-radius: 15px 15px 0 0 !important;
        }

        
        html[data-bs-theme="dark"] {
            --bs-body-bg: #121416;
            --bs-body-color: #e9ecef;
            --bs-secondary-bg: #1f2327;
            --bs-tertiary-bg: #2b3035;
            --bs-card-bg: #1a1d20;
            --bs-border-color: rgba(255, 255, 255, 0.15);
            --bs-border-color-translucent: rgba(255, 255, 255, 0.08);
        }

        html[data-bs-theme="dark"] .text-dark {
            color: var(--bs-body-color) !important;
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

        
        .footer {
            background-color: var(--bs-secondary-bg);
            color: var(--bs-body-color);
            padding: 50px 0 20px;
            margin-top: 60px;
            border-top: 1px solid var(--bs-border-color-translucent);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        
        .footer .text-muted {
            color: var(--bs-secondary-color) !important;
            transition: color 0.3s ease;
        }

        
        .notification-dropdown {
            width: 340px;
            border-radius: 16px !important;
            border: 1px solid var(--bs-border-color-translucent) !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
            overflow: hidden;
        }

        @media (max-width: 991.98px) {
            .notification-dropdown {
                width: 100%;
                border-radius: 0 !important;
                border: none !important;
                box-shadow: none !important;
                background-color: transparent !important;
            }
            .notification-dropdown .bg-body-secondary {
                background-color: transparent !important;
                border-bottom: 1px solid var(--bs-border-color-translucent) !important;
            }
        }
        
        .notification-dropdown .animate-bell {
            display: inline-block;
        }
        
        @keyframes bellRing {
            0%, 100% { transform: rotate(0); }
            20%, 60% { transform: rotate(15deg); }
            40%, 80% { transform: rotate(-15deg); }
        }
        
        .notification-dropdown:hover .animate-bell,
        #notificationDropdown:hover .fa-bell {
            animation: bellRing 0.6s ease-in-out;
        }

        .btn-action-header {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border: none;
            background: transparent;
            border-radius: 50%;
            color: var(--bs-secondary-color) !important;
        }
        .btn-action-header:hover {
            background-color: var(--bs-tertiary-bg);
            color: var(--bs-primary) !important;
        }
        .btn-action-header.text-danger-hover:hover {
            color: #dc3545 !important;
            background-color: rgba(220, 53, 69, 0.1);
        }

        .notification-item {
            position: relative;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
            background-color: transparent;
        }
        
        .notification-item.unread {
            border-left-color: #ff6b35;
            background-color: rgba(255, 107, 53, 0.04) !important;
        }
        
        .notification-item.unread.clickable:hover {
            background-color: rgba(255, 107, 53, 0.08) !important;
        }
        
        .notification-item.clickable:hover {
            background-color: rgba(0, 0, 0, 0.03) !important;
        }
        
        html[data-bs-theme="dark"] .notification-item.clickable:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
        }
        
        .notification-item.clickable {
            cursor: pointer;
        }

        .delete-notification-btn {
            opacity: 0;
            transition: all 0.2s ease;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--bs-secondary-color);
            background: transparent;
            border: none;
        }
        
        .notification-item:hover .delete-notification-btn {
            opacity: 0.7;
        }
        
        .notification-item:hover .delete-notification-btn:hover {
            opacity: 1;
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545 !important;
        }

        @media (hover: none) {
            .delete-notification-btn {
                opacity: 0.7;
            }
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
            <ul class="navbar-nav align-items-lg-center mt-3 mt-lg-0">
                <li class="nav-item me-lg-3 mb-2 mb-lg-0">
                    <button id="themeToggle" class="btn btn-link text-secondary p-1" title="Cambia tema" style="font-size: 1.25rem; text-decoration: none; border: none; background: transparent; padding-left: 0 !important;">
                        <i id="themeToggleIcon" class="fa-solid fa-moon"></i> <span class="d-lg-none ms-1 text-decoration-none" style="font-size: 1rem;">Cambia tema</span>
                    </button>
                </li>
                @auth
                    <li class="nav-item dropdown me-lg-2 mb-3 mb-lg-0 position-relative">
                        <a class="nav-link position-relative text-secondary px-0 px-lg-2 w-100" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1.1rem; transition: color 0.2s;">
                            <i class="fa-solid fa-bell"></i> <span class="d-lg-none ms-1">Notifiche</span>
                            <span id="notificationBadge" class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.65rem; padding: 0.25em 0.5em;">
                                0
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end p-0 mt-2 notification-dropdown" aria-labelledby="notificationDropdown">
                            <div class="px-3 py-2 bg-body-secondary border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold d-flex align-items-center text-body-emphasis">
                                    <i class="fa-solid fa-bell me-2 animate-bell" style="color: #ff6b35;"></i>
                                    Notifiche
                                    <span id="notificationBadgeHeader" class="badge rounded-pill bg-danger ms-2 d-none" style="font-size: 0.65rem;">0</span>
                                </h6>
                                <div class="d-flex align-items-center">
                                    <button id="markAllReadBtn" class="btn-action-header me-1" title="Segna tutte come lette">
                                        <i class="fa-solid fa-check-double"></i>
                                    </button>
                                    <button id="deleteAllNotificationsBtn" class="btn-action-header text-danger-hover" title="Cancella tutte">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="notificationList" class="overflow-auto" style="max-height: 320px;">
                                <div class="p-4 text-center text-muted">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                    <p class="mb-0 small">Caricamento...</p>
                                </div>
                            </div>
                        </div>
                    </li>
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
                        <li class="nav-item d-flex align-items-center ms-lg-2 mt-2 mt-lg-0">
                            <form method="POST" action="{{ route('logout') }}" class="m-0 w-100">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 d-block w-100 w-lg-auto text-start text-lg-center">
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
                        <li class="nav-item d-flex align-items-center ms-lg-2 mt-2 mt-lg-0">
                            <form method="POST" action="{{ route('logout') }}" class="m-0 w-100">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 d-block w-100 text-start text-lg-center" style="max-width: fit-content;">
                                    Esci
                                </button>
                            </form>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Accedi</a>
                    </li>
                    <li class="nav-item mt-2 mt-lg-0 ms-lg-2">
                        <a class="nav-link btn btn-custom btn-primary-custom text-white d-inline-block" href="{{ route('register') }}" style="color: white !important;">Registrati</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@auth
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const badge = document.getElementById('notificationBadge');
            const list = document.getElementById('notificationList');
            const markAllBtn = document.getElementById('markAllReadBtn');

            
            function loadNotifications() {
                fetch('{{ route("notifications.index") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    
                    const badgeHeader = document.getElementById('notificationBadgeHeader');
                    if (data.unread_count > 0) {
                        badge.textContent = data.unread_count;
                        badge.classList.remove('d-none');
                        if (badgeHeader) {
                            badgeHeader.textContent = data.unread_count;
                            badgeHeader.classList.remove('d-none');
                        }
                    } else {
                        badge.classList.add('d-none');
                        if (badgeHeader) {
                            badgeHeader.classList.add('d-none');
                        }
                    }

                    
                    list.innerHTML = '';

                    if (data.notifications.length === 0) {
                        list.innerHTML = `
                            <div class="py-5 text-center text-muted">
                                <div class="mb-3 d-inline-block p-3 rounded-circle bg-body-tertiary">
                                    <i class="fa-solid fa-bell-slash fa-2x text-secondary" style="opacity: 0.6;"></i>
                                </div>
                                <p class="mb-0 fw-semibold text-secondary" style="font-size: 0.9rem;">Non ci sono notifiche</p>
                                <span class="text-muted small" style="font-size: 0.75rem;">Ti avviseremo quando ci saranno novità</span>
                            </div>
                        `;
                        return;
                    }

                    data.notifications.forEach(n => {
                        const isUnread = !n.read_at;
                        const item = document.createElement('div');
                        item.className = `p-3 border-bottom notification-item ${isUnread ? 'unread' : ''}`;

                        item.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start">
                                <h6 class="mb-1 fw-bold text-body-emphasis notification-title" style="font-size: 0.9rem; padding-right: 15px;">${n.title}</h6>
                                <div class="d-flex align-items-center">
                                    ${isUnread ? '<span class="badge bg-primary p-1 rounded-circle me-2" style="width: 8px; height: 8px; display: inline-block;"></span>' : ''}
                                    <button class="btn btn-link btn-sm p-0 text-secondary delete-notification-btn" data-id="${n.id}" title="Cancella notifica">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="mb-1 text-muted small text-justify" style="font-size: 0.8rem; line-height: 1.3;">${n.message}</p>
                            <small class="text-muted" style="font-size: 0.7rem;">${n.created_at}</small>
                        `;

                        
                        const deleteBtn = item.querySelector('.delete-notification-btn');
                        if (deleteBtn) {
                            deleteBtn.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                const id = this.getAttribute('data-id');
                                deleteNotification(id);
                            });
                        }

                        
                        const hasAction = !!n.action_url;
                        const isClickable = hasAction;

                        if (isClickable) {
                            item.classList.add('clickable');
                            item.addEventListener('click', function () {
                                if (isUnread) {
                                    fetch(`/notifications/${n.id}/read`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json'
                                        }
                                    }).then(() => {
                                        if (hasAction) {
                                            window.location.href = n.action_url;
                                        } else {
                                            loadNotifications();
                                        }
                                    }).catch(() => {
                                        if (hasAction) {
                                            window.location.href = n.action_url;
                                        } else {
                                            loadNotifications();
                                        }
                                    });
                                } else if (hasAction) {
                                    window.location.href = n.action_url;
                                }
                            });
                        }

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

            
            function deleteNotification(id) {
                fetch(`/notifications/${id}`, {
                    method: 'DELETE',
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

            
            const deleteAllBtn = document.getElementById('deleteAllNotificationsBtn');
            if (deleteAllBtn) {
                deleteAllBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (confirm('Sei sicuro di voler cancellare tutte le notifiche?')) {
                        fetch('/notifications', {
                            method: 'DELETE',
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
                });
            }

            
            loadNotifications();

            
            setInterval(loadNotifications, 60000);
        });
    </script>
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
