<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CoastTrack') }}</title>

    
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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

        
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            position: relative;
            background-color: var(--bs-body-bg);
            overflow: hidden;
        }

        
        .auth-wrapper::before {
            content: '';
            position: absolute;
            top: -150px;
            right: -150px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(46, 139, 87, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-wrapper::after {
            content: '';
            position: absolute;
            bottom: -150px;
            left: -150px;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 107, 53, 0.10) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-card {
            width: 100%;
            max-width: 460px;
            position: relative;
            z-index: 1;
        }

        
        .auth-brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .auth-brand a {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--bs-body-color);
            transition: opacity 0.2s;
        }

        .auth-brand a:hover {
            opacity: 0.8;
        }

        .auth-brand .brand-icon {
            color: #2e8b57;
            font-size: 1.8rem;
        }

        
        .auth-box {
            background-color: var(--bs-body-bg);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.10);
            border: 1px solid var(--bs-border-color-translucent);
        }

        html[data-bs-theme="dark"] .auth-box {
            background-color: var(--bs-card-bg);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.40);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        
        .auth-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 6px;
            color: var(--bs-body-color);
        }

        .auth-subtitle {
            font-size: 0.9rem;
            color: var(--bs-secondary-color);
            margin-bottom: 28px;
        }

        
        .auth-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--bs-body-color);
            margin-bottom: 6px;
        }

        .auth-input {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 0.95rem;
            font-family: 'Outfit', sans-serif;
            border: 1px solid var(--bs-border-color);
            background-color: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
            transition: all 0.25s ease;
        }

        html[data-bs-theme="light"] .auth-input {
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .auth-input:focus {
            border-color: #2e8b57;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.15);
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }

        html[data-bs-theme="light"] .auth-input:focus {
            background-color: #fff;
        }

        .auth-input.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.10);
        }

        
        .btn-auth-primary {
            background-color: #ff6b35;
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 32px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.25);
        }

        .btn-auth-primary:hover {
            background-color: #e55a2b;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.35);
        }

        .btn-auth-primary:active {
            transform: translateY(0);
        }

        
        .auth-link {
            color: #ff6b35;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s;
        }

        .auth-link:hover {
            color: #e55a2b;
            text-decoration: underline;
        }

        .auth-link-secondary {
            color: var(--bs-secondary-color);
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .auth-link-secondary:hover {
            color: var(--bs-body-color);
            text-decoration: underline;
        }

        
        .auth-divider {
            border-color: var(--bs-border-color-translucent);
            margin: 24px 0;
        }

        
        .form-check-input:checked {
            background-color: #2e8b57;
            border-color: #2e8b57;
        }

        .form-check-input:focus {
            border-color: #2e8b57;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.15);
        }

        .form-check-label {
            font-size: 0.875rem;
            color: var(--bs-secondary-color);
        }

        
        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bs-secondary-bg);
            border: 1px solid var(--bs-border-color-translucent);
            border-radius: 50%;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--bs-secondary-color);
            transition: all 0.2s ease;
            z-index: 100;
        }

        .theme-toggle:hover {
            background: var(--bs-tertiary-bg);
            color: var(--bs-body-color);
        }

        
        .auth-alert {
            border-radius: 12px;
            font-size: 0.875rem;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        .invalid-feedback {
            font-size: 0.8rem;
            font-weight: 500;
        }
    </style>
</head>
<body>

<button class="theme-toggle" id="themeToggle" title="Cambia tema">
    <i id="themeIcon" class="fa-solid fa-moon"></i>
</button>

<div class="auth-wrapper">
    <div class="auth-card">

        
        <div class="auth-brand">
            <a href="{{ url('/') }}">
                <i class="fa-solid fa-person-hiking brand-icon"></i>
                CoastTrack
            </a>
        </div>

        
        <div class="auth-box">
            {{ $slot }}
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');

        const updateIcon = (theme) => {
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                btn.style.color = '#ffc107';
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                btn.style.color = '';
            }
        };

        const current = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateIcon(current);

        btn.addEventListener('click', () => {
            const theme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            updateIcon(theme);
        });
    });
</script>

</body>
</html>
