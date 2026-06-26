<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF + base API para window.App --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base" content="{{ url('/api/v1') }}">

    {{-- Config pública Firebase (consumida por resources/js/firebase.js) --}}
    @php($fb = config('firebase.client'))
    <meta name="firebase-api-key" content="{{ $fb['api_key'] }}">
    <meta name="firebase-auth-domain" content="{{ $fb['auth_domain'] }}">
    <meta name="firebase-project-id" content="{{ $fb['project_id'] }}">
    <meta name="firebase-storage-bucket" content="{{ $fb['storage_bucket'] }}">
    <meta name="firebase-sender-id" content="{{ $fb['sender_id'] }}">
    <meta name="firebase-app-id" content="{{ $fb['app_id'] }}">
    <link rel="icon" type="image/png" href="{{asset('img/icon/favicon-96x96.png')}}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{asset('img/icon/favicon.svg')}}" />
    <link rel="shortcut icon" href="{{asset('img/icon/favicon.ico')}}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('img/icon/apple-touch-icon.png')}}" />
    <meta name="apple-mobile-web-app-title" content="Tagliare" />
    <link rel="manifest" href="{{asset('img/icon/site.webmanifest')}}" />
    <title>@yield('title', 'Fisio Clínica')</title>

    {{-- Fuentes: DM Sans (UI) · DM Serif Display (marca) · JetBrains Mono (datos) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 + Font Awesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    {{-- Tema central + assets compilados por Vite --}}
    @vite(['resources/css/theme.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body>

    {{-- ── Top bar ─────────────────────────────────────────────────────────── --}}
    <header class="top-bar">
        <!-- <div class="top-bar-div"> -->
        @auth
        @php($u = auth()->user())
        @php($ini = mb_strtoupper(mb_substr($u->nombre,0,1).mb_substr($u->primer_apellido,0,1)))

        {{-- Hamburguesa (solo móvil) --}}
        <button class="nav-toggle" id="nav-toggle" aria-label="Abrir menú" aria-controls="offcanvas-nav" aria-expanded="false">
            <i class="fa-solid fa-bars"></i>
        </button>
        @endauth

        <div class="top-bar-left">
            <a href="{{ url('/') }}" class="brand">
                <img src="{{ asset('img/logo.png') }}" alt="Fisio Clínica" class="brand-logo">
            </a>
        </div>

        @auth
        {{-- Navegación primaria (escritorio) --}}
        <nav class="top-nav" aria-label="Navegación principal">
            <a href="{{ route('dashboard') }}"
                class="top-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Inicio</span>
            </a>
            <a href="{{ route('pacientes.index') }}"
                class="top-nav-link {{ request()->routeIs('pacientes.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i>
                <span>Pacientes</span>
            </a>
            @if($u->esAdmin())
            <a href="{{ route('auditoria.index') }}"
                class="top-nav-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-list"></i>
                <span>Auditoría</span>
            </a>
            @endif
        </nav>

        {{-- Menú de usuario --}}
        <div class="top-bar-right">
            <div class="user-menu" id="user-menu">
                <button class="user-trigger" id="user-trigger" aria-haspopup="true" aria-expanded="false">
                    <span class="user-avatar">{{ $ini }}</span>
                    <span class="user-meta d-none d-md-flex">
                        <span class="user-name">{{ $u->nombre }} {{ $u->primer_apellido }}</span>
                        <span class="user-role">{{ $u->esAdmin() ? 'Administrador' : 'Médico' }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down user-caret"></i>
                </button>

                <div class="user-dropdown" id="user-dropdown" role="menu">
                    <div class="user-dropdown-head">
                        <span class="user-avatar user-avatar-lg">{{ $ini }}</span>
                        <div>
                            <div class="user-name">{{ $u->nombre_completo }}</div>
                            <div class="user-email">{{ $u->email ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="user-dropdown-body">
                        <span class="role-chip {{ $u->esAdmin() ? 'role-chip-admin' : '' }}">
                            <i class="fa-solid {{ $u->esAdmin() ? 'fa-shield-halved' : 'fa-user-doctor' }}"></i>
                            {{ $u->esAdmin() ? 'Administrador' : 'Médico' }}
                        </span>
                    </div>
                    <button class="user-dropdown-item" data-logout role="menuitem">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        Cerrar sesión
                    </button>
                </div>
            </div>
        </div>
        @endauth
        <!-- </div> -->
    </header>

    @auth
    {{-- ── Offcanvas (navegación móvil) ──────────────────────────────────── --}}
    <div class="offcanvas-backdrop" id="offcanvas-backdrop"></div>
    <aside class="offcanvas-nav" id="offcanvas-nav" aria-hidden="true">
        <div class="offcanvas-head">
            <span class="user-avatar user-avatar-lg">{{ $ini }}</span>
            <div class="flex-grow-1">
                <div class="user-name">{{ $u->nombre_completo }}</div>
                <span class="role-chip {{ $u->esAdmin() ? 'role-chip-admin' : '' }}">
                    <i class="fa-solid {{ $u->esAdmin() ? 'fa-shield-halved' : 'fa-user-doctor' }}"></i>
                    {{ $u->esAdmin() ? 'Administrador' : 'Médico' }}
                </span>
            </div>
            <button class="offcanvas-close" id="offcanvas-close" aria-label="Cerrar menú">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="offcanvas-links" aria-label="Navegación móvil">
            <a href="{{ route('dashboard') }}"
                class="offcanvas-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Inicio
            </a>
            <a href="{{ route('pacientes.index') }}"
                class="offcanvas-link {{ request()->routeIs('pacientes.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> Pacientes
            </a>
            @if($u->esAdmin())
            <a href="{{ route('auditoria.index') }}"
                class="offcanvas-link {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-list"></i> Auditoría
            </a>
            @endif
        </nav>

        <button class="offcanvas-logout" data-logout>
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
        </button>
    </aside>
    @endauth

    {{-- Slot para barras pegajosas bajo el top-bar (p. ej. wizard progress) --}}
    @yield('header')

    {{-- ── Contenido ───────────────────────────────────────────────────────── --}}
    <main class="page-body">
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    {{-- Contenedor global de toasts (App.toast lo reutiliza) --}}
    <div id="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>