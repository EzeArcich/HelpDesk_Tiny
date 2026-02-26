@php
    $initialMode = old('_auth_mode');
    if (! $initialMode) {
        $initialMode = request('auth');
    }
    if (! in_array($initialMode, ['login', 'register'], true)) {
        $initialMode = ($errors->has('name') || $errors->has('password_confirmation')) ? 'register' : 'login';
    }
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'HelpDesk') }}</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        :root {
            --surface: #f8fafc;
            --ink: #111827;
            --ink-soft: #64748b;
            --primary: #7c3aed;
            --secondary: #334155;
            --success: #22c55e;
            --warning: #f97316;
            --card: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            font-family: "SFMono-Regular", Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            background:
                radial-gradient(900px 420px at 10% -8%, rgba(124, 58, 237, 0.16), transparent 60%),
                radial-gradient(800px 380px at 95% -10%, rgba(34, 197, 94, 0.16), transparent 60%),
                var(--surface);
        }

        .page-shell {
            width: 100%;
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr;
        }

        .topbar {
            width: 100%;
            padding: 24px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .brand {
            text-decoration: none;
            color: var(--secondary);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: clamp(24px, 2.6vw, 34px);
            font-weight: 500;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 30px;
            background: linear-gradient(150deg, #8b5cf6 0%, #22c55e 100%);
        }

        .top-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .action-btn {
            border: 1px solid #d8deeb;
            border-radius: 12px;
            padding: 9px 14px;
            background: #fff;
            color: #334155;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .action-btn.primary {
            border: none;
            background: var(--primary);
            color: #fff;
        }

        .layout {
            width: 100%;
            padding: 10px 40px 30px;
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 20px;
            align-items: stretch;
        }

        .hero-panel,
        .auth-panel {
            border-radius: 22px;
            border: 1px solid #e6eaf2;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
            background: var(--card);
        }

        .hero-panel {
            position: relative;
            padding: 36px 34px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            animation: rise .6s ease both;
        }

        .hero-ambient {
            position: absolute;
            inset: 0;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
            background:
                radial-gradient(300px 160px at 12% 12%, rgba(124, 58, 237, 0.16), transparent 70%),
                radial-gradient(280px 140px at 90% 18%, rgba(34, 197, 94, 0.14), transparent 72%),
                radial-gradient(240px 120px at 60% 90%, rgba(249, 115, 22, 0.13), transparent 70%);
        }

        .hero-ambient::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(to right, rgba(100, 116, 139, 0.07) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(100, 116, 139, 0.06) 1px, transparent 1px);
            background-size: 44px 44px;
            mask-image: radial-gradient(circle at center, rgba(0, 0, 0, 0.45), transparent 75%);
        }

        .hero-chip {
            position: absolute;
            border: 1px solid rgba(124, 58, 237, 0.16);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.78);
            color: #475569;
            font-size: 11px;
            letter-spacing: .02em;
            padding: 6px 10px;
            animation: floatY var(--dur, 9s) ease-in-out infinite;
            animation-delay: var(--delay, 0s);
            backdrop-filter: blur(2px);
        }

        .hero-chip b {
            color: #334155;
        }

        .chip-a { top: 18%; left: 8%; --dur: 8s; --delay: -2s; }
        .chip-b { top: 16%; right: 10%; --dur: 10s; --delay: -1s; }
        .chip-c { top: 58%; left: 12%; --dur: 9s; --delay: -3s; }
        .chip-d { top: 62%; right: 14%; --dur: 11s; --delay: -4s; }
        .chip-e { bottom: 14%; left: 38%; --dur: 12s; --delay: -2.5s; }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border-radius: 999px;
            border: 1px solid #ceb9fb;
            color: var(--primary);
            background: #f4efff;
            font-size: 12px;
            padding: 9px 14px;
        }

        .hero-title {
            margin: 18px 0 12px;
            font-size: clamp(36px, 5.6vw, 80px);
            line-height: 1.04;
            letter-spacing: -0.03em;
            color: #1f2a44;
        }

        .hero-gradient {
            background: linear-gradient(92deg, #7c3aed 0%, #3b82f6 45%, #22c55e 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero-copy {
            margin: 0;
            max-width: 860px;
            color: #54657f;
            font-size: clamp(16px, 2vw, 22px);
            line-height: 1.55;
        }

        .hero-metrics {
            margin-top: 24px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .metric {
            border-radius: 14px;
            border: 1px solid #ebeff7;
            background: #fff;
            padding: 14px;
        }

        .metric-label {
            margin: 0 0 8px;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: .05em;
        }

        .metric-value {
            margin: 0;
            color: #1e293b;
            font-size: clamp(24px, 2.5vw, 34px);
            line-height: 1;
        }

        .metric-note {
            margin: 7px 0 0;
            color: #64748b;
            font-size: 12px;
        }

        .auth-panel {
            padding: 20px;
            background:
                linear-gradient(150deg, rgba(124, 58, 237, 0.06) 0%, rgba(34, 197, 94, 0.07) 100%),
                #fff;
            animation: rise .8s ease both;
        }

        .tabs {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 4px;
            margin-bottom: 14px;
        }

        .tab-btn {
            border: none;
            border-radius: 9px;
            padding: 10px 12px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            font-size: 14px;
        }

        .tab-btn.active {
            background: #eef2ff;
            color: #4c1d95;
        }

        .auth-shell {
            position: relative;
            min-height: 440px;
        }

        .auth-form {
            position: absolute;
            inset: 0;
            border: 1px solid #e6ebf3;
            border-radius: 14px;
            background: #fff;
            padding: 18px;
            transition: opacity .35s ease, transform .35s ease, visibility .35s ease;
            visibility: hidden;
            opacity: 0;
            transform: translateX(18px) scale(.98);
            pointer-events: none;
        }

        .auth-shell.mode-login .form-login,
        .auth-shell.mode-register .form-register {
            visibility: visible;
            opacity: 1;
            transform: translateX(0) scale(1);
            pointer-events: auto;
        }

        .auth-title {
            margin: 0 0 6px;
            font-size: 22px;
            color: #0f172a;
        }

        .auth-sub {
            margin: 0 0 14px;
            color: #64748b;
            font-size: 13px;
        }

        .field {
            margin-bottom: 12px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            color: #334155;
        }

        .field input {
            width: 100%;
            border: 1px solid #d9e0eb;
            border-radius: 10px;
            height: 40px;
            padding: 0 12px;
            font-size: 14px;
            color: #0f172a;
            background: #fff;
        }

        .field input:focus {
            outline: none;
            border-color: #a78bfa;
            box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.2);
        }

        .err {
            color: #b91c1c;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }

        .form-bottom {
            margin-top: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .hint-link {
            color: #475569;
            text-decoration: none;
            font-size: 12px;
        }

        .submit-btn {
            border: none;
            border-radius: 10px;
            height: 40px;
            padding: 0 16px;
            color: #fff;
            background: var(--primary);
            font-size: 14px;
            cursor: pointer;
        }

        .check-row {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b;
            font-size: 12px;
            margin-top: -2px;
        }

        .global-errors {
            margin-bottom: 10px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fef2f2;
            color: #991b1b;
            padding: 9px 10px;
            font-size: 12px;
        }

        @keyframes rise {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatY {
            0% {
                transform: translateY(0) translateX(0);
                opacity: .8;
            }
            50% {
                transform: translateY(-12px) translateX(5px);
                opacity: 1;
            }
            100% {
                transform: translateY(0) translateX(0);
                opacity: .8;
            }
        }

        @media (max-width: 1024px) {
            .topbar {
                padding: 18px 16px;
            }

            .layout {
                padding: 6px 16px 20px;
                grid-template-columns: 1fr;
            }

            .hero-metrics {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .hero-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="{{ url('/') }}">
                <span class="brand-mark">H</span>
                <span>HelpDesk</span>
            </a>
            <div class="top-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="action-btn">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="action-btn primary" type="submit">Salir</button>
                    </form>
                @else
                    <button class="action-btn" type="button" data-switch="login">Login</button>
                    <button class="action-btn primary" type="button" data-switch="register">Register</button>
                @endauth
            </div>
        </header>

        <main class="layout">
            <section class="hero-panel">
                <div class="hero-ambient" aria-hidden="true">
                    <span class="hero-chip chip-a">ticket <b>#4821</b> nuevo</span>
                    <span class="hero-chip chip-b">SLA <b>92%</b> hoy</span>
                    <span class="hero-chip chip-c">IA sugirio <b>3</b> respuestas</span>
                    <span class="hero-chip chip-d">cola critica <b>14</b></span>
                    <span class="hero-chip chip-e">CSAT <b>4.8</b></span>
                </div>

                <div class="hero-content">
                    <p class="pill">Plataforma moderna para soporte y tickets</p>
                    <h1 class="hero-title">
                        Revoluciona tu
                        <span class="hero-gradient">soporte al cliente</span>
                    </h1>
                    <p class="hero-copy">
                        Centraliza conversaciones, acelera respuestas con automatizaciones y controla
                        SLA desde una interfaz clara y fresca. Todo en un solo lugar, pensado para equipos
                        que quieren escalar sin perder calidad.
                    </p>
                </div>

                <div class="hero-metrics hero-content">
                    <article class="metric">
                        <p class="metric-label">Tickets hoy</p>
                        <p class="metric-value">128</p>
                        <p class="metric-note">+8 ultima hora</p>
                    </article>
                    <article class="metric">
                        <p class="metric-label">Primer respuesta</p>
                        <p class="metric-value">2.4m</p>
                        <p class="metric-note">SLA en verde</p>
                    </article>
                    <article class="metric">
                        <p class="metric-label">CSAT</p>
                        <p class="metric-value">4.8</p>
                        <p class="metric-note">341 opiniones</p>
                    </article>
                </div>
            </section>

            @guest
                <aside class="auth-panel">
                    <div class="tabs">
                        <button type="button" class="tab-btn" data-switch="login">Login</button>
                        <button type="button" class="tab-btn" data-switch="register">Register</button>
                    </div>

                    <div class="auth-shell" id="authShell" data-initial-mode="{{ $initialMode }}">
                        <form method="POST" action="{{ route('login') }}" class="auth-form form-login" novalidate>
                            @csrf
                            <input type="hidden" name="_auth_mode" value="login">
                            <h2 class="auth-title">Bienvenido de nuevo</h2>
                            <p class="auth-sub">Inicia sesion para gestionar tu mesa de ayuda.</p>

                            @if ($errors->has('email') && $initialMode === 'login')
                                <div class="global-errors">{{ $errors->first('email') }}</div>
                            @endif

                            <div class="field">
                                <label for="login-email">Email</label>
                                <input id="login-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                                @if ($errors->has('email') && $initialMode === 'login')
                                    <span class="err">{{ $errors->first('email') }}</span>
                                @endif
                            </div>

                            <div class="field">
                                <label for="login-password">Password</label>
                                <input id="login-password" name="password" type="password" required autocomplete="current-password">
                                @error('password')
                                    <span class="err">{{ $message }}</span>
                                @enderror
                            </div>

                            <label class="check-row">
                                <input type="checkbox" name="remember">
                                Recordarme
                            </label>

                            <div class="form-bottom">
                                @if (Route::has('password.request'))
                                    <a class="hint-link" href="{{ route('password.request') }}">Olvide mi password</a>
                                @endif
                                <button class="submit-btn" type="submit">Entrar</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('register') }}" class="auth-form form-register" novalidate>
                            @csrf
                            <input type="hidden" name="_auth_mode" value="register">
                            <h2 class="auth-title">Crea tu cuenta</h2>
                            <p class="auth-sub">Activa tu espacio de soporte en menos de un minuto.</p>

                            <div class="field">
                                <label for="register-name">Nombre</label>
                                <input id="register-name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
                                @error('name')
                                    <span class="err">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="register-email">Email</label>
                                <input id="register-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                                @if ($errors->has('email') && $initialMode === 'register')
                                    <span class="err">{{ $errors->first('email') }}</span>
                                @endif
                            </div>

                            <div class="field">
                                <label for="register-password">Password</label>
                                <input id="register-password" name="password" type="password" required autocomplete="new-password">
                                @error('password')
                                    <span class="err">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="register-password-confirmation">Confirmar password</label>
                                <input id="register-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                                @error('password_confirmation')
                                    <span class="err">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-bottom">
                                <span class="hint-link">Al crear cuenta aceptas terminos y privacidad.</span>
                                <button class="submit-btn" type="submit">Crear cuenta</button>
                            </div>
                        </form>
                    </div>
                </aside>
            @else
                <aside class="auth-panel d-flex flex-column justify-content-center align-items-start p-4">
                    <h2 class="auth-title mb-2">Sesion iniciada</h2>
                    <p class="auth-sub mb-3">Ya puedes continuar al panel para gestionar tickets.</p>
                    <a href="{{ route('dashboard') }}" class="action-btn primary">Ir al dashboard</a>
                </aside>
            @endguest
        </main>
    </div>

    @guest
        <script>
            (() => {
                const shell = document.getElementById('authShell');
                if (!shell) return;

                let mode = shell.dataset.initialMode === 'register' ? 'register' : 'login';
                const tabs = Array.from(document.querySelectorAll('[data-switch]'));

                const paint = () => {
                    shell.classList.toggle('mode-register', mode === 'register');
                    shell.classList.toggle('mode-login', mode === 'login');

                    tabs.forEach((tab) => {
                        const active = tab.dataset.switch === mode;
                        tab.classList.toggle('active', active);
                    });
                };

                tabs.forEach((tab) => {
                    tab.addEventListener('click', () => {
                        mode = tab.dataset.switch === 'register' ? 'register' : 'login';
                        paint();
                    });
                });

                paint();
            })();
        </script>
    @endguest
</body>
</html>
