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
                        <span>{{ Auth::user()->name }}</span>
                        <i class="fa-solid fa-door-open"></i>
                    @endauth

                    @guest
                        <div class="dropdown">
                        <button type="button" class="login-btn btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                            Login
                        </button>
                        <form class="dropdown-menu p-4 dropdown-menu-end" style="width: 300px;">
                            <div class="mb-3">
                            <label for="exampleDropdownFormEmail2" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="exampleDropdownFormEmail2" placeholder="email@example.com">
                            </div>
                            <div class="mb-3">
                            <label for="exampleDropdownFormPassword2" class="form-label">Password</label>
                            <input type="password" class="form-control" id="exampleDropdownFormPassword2" placeholder="Password">
                            </div>
                            <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="dropdownCheck2">
                                <label class="form-check-label" for="dropdownCheck2">
                                Remember me
                                </label>
                            </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Sign in</button>
                        </form>
                        </div>
                    @endguest
                    </a>
                </div>
                </div>
            </div>
            </nav>
        
                @auth
                   <a class="button" href="{{ url('/logout') }}"> Logout </a> <span>{{ Auth::user()->name }}</span>
                @endauth

            <section id="content">
                @yield('content')
            </section>

            <nav class="navbar navbar-expand-lg bg-body-tertiary">
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