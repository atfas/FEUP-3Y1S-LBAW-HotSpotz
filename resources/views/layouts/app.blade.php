<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/icon.png') }}">

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
            <nav class="navbar navbar-expand-lg navbar-custom" style="position: sticky; top: 0; z-index: 1000;">
                <div class="container-fluid px-4">
                    <a class="navbar-brand" href="{{ route('home') }}">
                        <img src="assets/hotSpotzLogo.png" alt="HotSpotz Logo" width="180px" class="d-inline-block align-text-top">
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                        <div class="navbar-nav ms-auto align-items-center">
                            @guest
                                <a class="btn btn-outline-danger btn-sm ms-2" href="#">
                                    Become a Host
                                </a>
                            @endguest

                            @auth
                                @if(!Auth::user()->is_host)
                                    <a class="btn btn-outline-danger btn-sm ms-2" href="#">
                                        Become a Host
                                    </a>
                                @endif
                            @endauth

                            <a class="nav-link" href="{{ route('home') }}">
                                Home
                            </a>
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-heart"></i><span class="nav-text">Favourites</span>
                            </a>
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-message"></i> <span class="nav-text">Messages</span>
                            </a>
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-bell"></i><span class="nav-text">Notifications</span>
                            </a>
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-credit-card"></i><span class="nav-text">Payments</span>
                            </a>

                            @auth

                                @if(Auth::user()->is_admin)
                                    <a class="nav-link" href="{{ route('admin.index') }}">
                                        <i class="fas fa-cog icon"></i><span class="nav-text">Admin Dashboard</span>
                                    </a>
                                @endif
                                
                                <a href="{{ route('profile.show') }}" class="nav-link fw-bold">
                                    {{ Auth::user()->full_name }}
                                </a>

                                <a class="btn btn-outline-danger btn-sm ms-2" href="{{ route('logout') }}">
                                    <i class="fas fa-sign-out-alt icon"></i> 
                                </a>
                            @endauth

                            @guest
                                <a class="btn btn-primary ms-2" href="{{ route('login') }}">
                                    Login
                                </a>
                            @endguest
                        </div>
                    </div>
                </div>
            </nav>

            <section id="content">
                @yield('content')
            </section>

            <nav class="navbar navbar-expand-lg footer-custom">
                <div class="container-fluid px-4">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-info-circle"></i> About Us
                            </a>
                        </li>
                        <li class="nav-item" data-bs-toggle="modal" data-bs-target="#contact-us-modal">
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-envelope"></i> Contact
                            </a>
                        </li>
                    </ul>
                    <span class="navbar-text">
                        © 2025 HotSpotz. All rights reserved.
                    </span>
                </div>
            </nav>
      

            <!-- Contact us modal-->
            <div class="modal fade" id="contact-us-modal" tabindex="-1" aria-labelledby="contact-us-modalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header d-flex justify-content-center position-relative">
                            <h1 class="modal-title fs-5" id="contact-us-modalLabel">Contact Us</h1>
                            <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                         <div class="modal-body d-flex justify-content-center align-items-center">
                            <span>
                                Afonso Saraiva - <a href="mailto:up202304461@up.pt">up202304461</a><br>
                                Afonso Saraiva - <a href="mailto:up202304461@up.pt">up202304461</a><br>
                                Afonso Saraiva - <a href="mailto:up202304461@up.pt">up202304461</a><br>
                                Afonso Saraiva - <a href="mailto:up202304461@up.pt">up202304461</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>


        </main>
    </body>
</html>