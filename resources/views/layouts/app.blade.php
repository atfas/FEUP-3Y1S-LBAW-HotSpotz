<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @auth
        <meta name="user-id" content="{{ Auth::id() }}">
        @endauth

        <title>@yield('title', config('app.name', 'Laravel'))</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/icon.png') }}">

        <!-- Styles -->
        @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @stack('styles')

        <style>
            /* Global pagination styling to match admin panel */
            .pagination { margin: 0; }
            .page-link { color: var(--color-wine, #6f2a6d); border-color: #dee2e6; padding: 0.5rem 0.75rem; transition: all 0.2s; }
            .page-link:hover { background-color: var(--color-light-purple, #d8bfd8); border-color: var(--color-dark-purple, #5a1f4a); color: white; transform: translateY(-1px); }
            .page-item.active .page-link { background-color: var(--color-wine, #6f2a6d); border-color: var(--color-wine, #6f2a6d); color: white; }
            .page-item.disabled .page-link { color: #6c757d; background-color: #fff; border-color: #dee2e6; }
        </style>

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
                        <img src="{{ asset('assets/hotSpotzLogo.png') }}" alt="HotSpotz Logo" width="180px" class="d-inline-block align-text-top">
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                        <div class="navbar-nav ms-auto align-items-center">
                            @auth
                                @if(!Auth::user()->application_status === 'Approved')
                                    <a class="btn btn-outline-danger btn-sm ms-2" href="#">
                                        Become a Host
                                    </a>
                                @endif

                                @if(Auth::user()->application_status === 'Approved')
                                    <a class="btn btn-outline-danger btn-sm ms-2" href="{{ route('listing.create') }}">
                                        Add Listing
                                    </a>
                                @endif
                                
                                @if(Auth::user()->application_status === 'Pending')
                                    <a class="btn btn-outline-warning btn-sm ms-2" href="#">
                                        Pending Application
                                    </a>
                                @endif
                            @endauth
                                
                            <a class="nav-link" href="{{ route('home') }}">
                                Home
                            </a>

                            @auth
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-heart"></i><span class="nav-text">Favourites</span>
                            </a>
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-message"></i> <span class="nav-text">Messages</span>
                            </a>
                            
                            <!-- Notifications dropdown -->
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-bell"></i>
                                    <span class="nav-text">Notifications</span>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notification-count" style="display: none;">
                                        0
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                                    <li class="dropdown-header d-flex justify-content-between align-items-center">
                                        <span>Notifications</span>
                                        <a href="{{ route('notifications.index') }}" class="text-primary text-decoration-none small">View all</a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <div id="notifications-dropdown-content">
                                        <li class="text-center py-3">
                                            <span class="text-muted small">Loading...</span>
                                        </li>
                                    </div>
                                    <li><hr class="dropdown-divider"></li>
                                    <li class="text-center py-2">
                                        <button class="btn btn-sm btn-link text-decoration-none" id="mark-all-read-btn" style="display: none;">
                                            Mark all as read
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            
                            <a class="nav-link" href="#">
                                <i class="fa-solid fa-credit-card"></i><span class="nav-text">Payments</span>
                            </a>
                            @endauth

                            @auth

                                @if(Auth::user()->is_admin)
                                    <a class="nav-link" href="{{ route('admin.index') }}">
                                        <i class="fas fa-cog icon"></i><span class="nav-text">Admin Dashboard</span>
                                    </a>
                                @endif
                                
                                <a href="{{ route('profile.show') }}" class="nav-link fw-bold">
                                    {{ explode(' ', Auth::user()->full_name)[0] }}
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
                            <span class="text-center">
                                Afonso Saraiva - <a href="mailto:up202304461@up.pt">up202304461</a><br>
                                Inês Francisco - <a href="mailto:up202304912@up.pt">up202304912</a><br>
                                Pedro Peixoto - <a href="mailto:up202304912@up.pt">up202304912</a><br>
                                Vasco Alves - <a href="mailto:up202304912@up.pt">up202304912</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>


        </main>
    </body>
</html>