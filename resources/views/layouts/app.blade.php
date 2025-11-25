<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Styles -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @stack('styles')

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>

        <script src="https://kit.fontawesome.com/b3a434d82b.js" crossorigin="anonymous"></script>
        @stack('scripts')
    </head>
    <style>
        body {
            /* Wine color gradient */
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); /* Soft fallback */
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); /* Soft blue/grey */
            background: linear-gradient(to right, #722F37 0%, #990000 100%); /* Wine/Deep Red */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        #content {
            flex: 1;
        }
        .card {
            border: none;
            border-radius: 1rem;
        }
        .btn-primary {
            background-color: #722F37; /* Wine color */
            border-color: #722F37;
            color: white;
        }
        .btn-primary:hover {
            background-color: #59232b; /* Darker wine */
            border-color: #59232b;
        }
        .text-wine {
            color: #722F37 !important;
        }
    </style>
    <body>
        <main>
            <nav class="navbar navbar-expand-lg bg-body-tertiary">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                        <img src="/docs/5.3/assets/brand/bootstrap-logo.svg" alt="Logo" width="30" height="24" class="d-inline-block align-text-top">
                    </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="#">Home</a>
                    <a class="nav-link" href="#"><i class="fa-regular fa-bell"></i></a>
                    <a class="nav-link" href="#">Pricing</a>
                    <a class="nav-link" href="#">Features</a>
                    <a class="nav-link" href="#">Pricing</a>
                    <a class="nav-link" href="#">
                    @auth
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('profile.show') }}" class="fw-bold text-wine text-decoration-none">
                                {{ Auth::user()->full_name }}
                            </a>
                            <a class="btn btn-outline-danger btn-sm" href="{{ route('logout') }}">
                                <i class="fa-solid fa-right-from-bracket"></i> Logout
                            </a>
                        </div>
                    @endauth

                    @guest
                        <a class="btn btn-primary" href="{{ route('login') }}">Login</a>
                    @endguest
                    </a>
                </div>
                </div>
            </div>
            </nav>

            <section id="content">
                @yield('content')
            </section>

            <nav class="navbar navbar-expand-lg bg-body-tertiary mt-auto">
            <div class="container-fluid">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    <a class="nav-link" href="#">About Us</a>
                    </li>
                    <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                    </li>
                </ul>
                <span class="navbar-text">
                    © 2025 HotSpotz. All rights reserved.
                </span>
                </div>
            </div>
            </nav>
        </main>
    </body>
    
</html>