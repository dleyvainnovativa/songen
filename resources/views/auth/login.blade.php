{{--
    Vista: auth/login.blade.php
    Ruta:  GET /login

    Login con Firebase Authentication (email/password).
    El flujo completo vive en App.auth.login():
        Firebase sign-in → ID token → POST /auth/session → sesión Laravel → redirect.

    Esta vista usa su propio layout mínimo (no main.blade) porque no debe
    mostrar top-bar ni navegación: es la puerta de entrada.
--}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base" content="{{ url('/api/v1') }}">

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

    <title>Iniciar sesión · Songen</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    @vite(['resources/css/theme.css', 'resources/js/app.js'])
</head>

<body class="login-body">

    <div id="toast-container"></div>

    <div class="login-wrap">
        <div class="login-card">
            <div class="login-brand">
                <img src="{{ asset('img/logo.png') }}" alt="Songen" class="login-logo">
            </div>
            <p class="login-sub">Accede con tu cuenta de personal médico</p>

            <form id="login-form" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control"
                            placeholder="medico@clinica.mx" autocomplete="email" required>
                    </div>
                    <div class="invalid-feedback" id="err-email"></div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control"
                            placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" class="input-group-text btn-toggle-pw" id="toggle-pw" tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback" id="err-password"></div>
                </div>

                <button type="submit" class="btn-next w-100 justify-content-center" id="btn-login">
                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>
                    Entrar
                </button>
            </form>
        </div>

        <p class="login-foot">
            ¿Problemas para acceder? Contacta al administrador de la clínica.
        </p>
    </div>

    <script type="module">
        const form = document.getElementById('login-form');
        const btn = document.getElementById('btn-login');

        // Mostrar/ocultar contraseña
        document.getElementById('toggle-pw').addEventListener('click', () => {
            const pw = document.getElementById('password');
            const icon = document.querySelector('#toggle-pw i');
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            icon.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });

        // Mapeo de códigos de error de Firebase → mensaje en español
        function mensajeFirebase(code) {
            const map = {
                'auth/invalid-email': 'El correo no tiene un formato válido.',
                'auth/user-disabled': 'Esta cuenta está deshabilitada.',
                'auth/user-not-found': 'No existe una cuenta con ese correo.',
                'auth/wrong-password': 'Correo o contraseña incorrectos.',
                'auth/invalid-credential': 'Correo o contraseña incorrectos.',
                'auth/too-many-requests': 'Demasiados intentos. Espera un momento e intenta de nuevo.',
                'auth/network-request-failed': 'Sin conexión. Revisa tu red.',
            };
            return map[code] ?? 'No se pudo iniciar sesión. Intenta de nuevo.';
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            App.clearErrors(form);

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                App.showErrors({
                    ...(email ? {} : {
                        email: ['Ingresa tu correo.']
                    }),
                    ...(password ? {} : {
                        password: ['Ingresa tu contraseña.']
                    }),
                });
                return;
            }

            App.loading(btn, true, 'Entrando…');
            try {
                const res = await App.auth.login(email, password);
                App.toast('success', '¡Bienvenido!', 'Redirigiendo…');
                setTimeout(() => window.location.href = res.redirect, 800);
            } catch (err) {
                App.loading(btn, false);
                // Error de Firebase (tiene .code) vs error del servidor (ApiError con .status)
                if (err.code) {
                    App.toast('error', 'No se pudo entrar', mensajeFirebase(err.code));
                } else if (err instanceof App.ApiError) {
                    // 403 = sin médico vinculado, 401 = token inválido
                    App.toast('error', 'Acceso denegado', err.message);
                } else {
                    App.toast('error', 'Error', 'Algo salió mal. Intenta de nuevo.');
                }
            }
        });
    </script>
</body>

</html>