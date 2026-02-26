<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        .hd-navbar-wrap {
            padding: 14px 0;
            background:
                radial-gradient(560px 220px at 8% 0%, rgba(124, 58, 237, 0.16), transparent 65%),
                radial-gradient(560px 220px at 92% 0%, rgba(34, 197, 94, 0.14), transparent 65%),
                #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .hd-navbar {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
            padding: 8px 12px;
        }

        .hd-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: #1e293b;
        }

        .hd-brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            color: #fff;
            background: linear-gradient(145deg, #7c3aed 0%, #22c55e 100%);
        }

        .hd-link {
            color: #334155;
            border-radius: 10px;
            padding: 8px 12px !important;
            font-weight: 600;
            transition: all .2s ease;
        }

        .hd-link:hover {
            color: #7c3aed;
            background: #eef2ff;
        }

        .hd-link-active {
            color: #7c3aed !important;
            background: #ede9fe;
        }

        .hd-auth-btn {
            border-radius: 10px;
            padding: 8px 12px !important;
            font-weight: 600;
        }

        .hd-auth-primary {
            background: #7c3aed;
            color: #fff !important;
        }

        .hd-auth-secondary {
            background: #334155;
            color: #fff !important;
        }

        .hd-user-toggle {
            border-radius: 10px;
            background: #f1f5f9;
            padding: 8px 12px !important;
            color: #1e293b !important;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="hd-navbar-wrap">
            <div class="container">
                <nav class="navbar navbar-expand-md hd-navbar">
                    <a class="navbar-brand hd-brand" href="{{ url('/') }}">
                        <span class="hd-brand-mark">H</span>
                        <span>{{ config('app.name', 'HelpDesk') }}</span>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- Left Side Of Navbar -->
                        <ul class="navbar-nav me-auto gap-md-1">
                            @auth
                                <li class="nav-item">
                                    <a class="nav-link hd-link {{ request()->routeIs('dashboard') ? 'hd-link-active' : '' }}" href="{{ route('dashboard') }}">
                                        Tickets
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link hd-link {{ request()->is('tags*') ? 'hd-link-active' : '' }}" href="{{ Route::has('tags.index') ? route('tags.index') : '#' }}">
                                        Tags
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link hd-link {{ request()->is('users*') ? 'hd-link-active' : '' }}" href="{{ Route::has('users.index') ? route('users.index') : '#' }}">
                                        Users
                                    </a>
                                </li>
                            @endauth
                        </ul>

                        <!-- Right Side Of Navbar -->
                        <ul class="navbar-nav ms-auto align-items-md-center gap-md-1">
                            <!-- Authentication Links -->
                            @guest
                                @if (Route::has('login'))
                                    <li class="nav-item">
                                        <a class="nav-link hd-auth-btn" href="{{ route('login') }}">{{ __('Login') }}</a>
                                    </li>
                                @endif

                                @if (Route::has('register'))
                                    <li class="nav-item">
                                        <a class="nav-link hd-auth-btn hd-auth-primary" href="{{ route('register') }}">{{ __('Register') }}</a>
                                    </li>
                                @endif
                            @else
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link dropdown-toggle hd-user-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ Auth::user()->name }}
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit();">
                                            {{ __('Logout') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </nav>
            </div>
        </div>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
