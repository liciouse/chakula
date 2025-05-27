<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Food Blog</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        #app-content {
            min-height: calc(100vh - 56px);
            padding: 20px;
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            display: none;
        }
        .nav-link.active {
            font-weight: bold;
            background: rgba(255,255,255,0.1);
            border-left: 3px solid #fff;
        }
        .navbar-nav .nav-item {
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-utensils me-2"></i>Food Blog Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                           href="{{ route('admin.dashboard') }}">
                           <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" 
                           href="{{ route('admin.users.index') }}">
                           <i class="fas fa-users me-1"></i> Manage Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.content*') ? 'active' : '' }}" 
                           href="{{ route('admin.content.index') }}">
                           <i class="fas fa-newspaper me-1"></i> Manage Content
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" 
                           href="{{ route('admin.settings.index') }}">
                           <i class="fas fa-cog me-1"></i> System Settings
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" id="logout-form">
                            @csrf
                            <button type="submit" class="btn btn-link nav-link">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div id="app-content">
        @yield('content')
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr for notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Handle navigation clicks
            $(document).on('click', '.nav-link:not(.disabled)', function(e) {
                if ($(this).attr('href') === '#') return;
                
                e.preventDefault();
                const url = $(this).attr('href');
                const route = $(this).data('route') || $(this).attr('href').split('/').pop();
                
                // Show loading overlay
                $('.loading-overlay').show();
                
                // Update active nav item
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                
                // Update browser history
                history.pushState(null, null, url);
                
                // Load content via AJAX
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        $('#app-content').html(response);
                        document.title = 'Food Blog Admin | ' + route.charAt(0).toUpperCase() + route.slice(1);
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        toastr.error('Error loading page. Please try again.');
                    },
                    complete: function() {
                        $('.loading-overlay').hide();
                    }
                });
            });
            
            // Handle browser back/forward buttons
            window.onpopstate = function() {
                location.reload();
            };
            
            // Handle logout form submission
            $('#logout-form').on('submit', function(e) {
                e.preventDefault();
                $('.loading-overlay').show();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function() {
                        window.location.href = '/login';
                    },
                    error: function() {
                        window.location.href = '/login';
                    }
                });
            });
            
            // Set initial active state based on current route
            function setInitialActiveState() {
                const path = window.location.pathname;
                $('.nav-link').each(function() {
                    const linkPath = $(this).attr('href');
                    if (path.includes(linkPath.split('/admin/')[1])) {
                        $(this).addClass('active');
                    }
                });
            }
            
            setInitialActiveState();
        });
    </script>
</body>
</html>