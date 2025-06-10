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
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
        #logout-form button {
            background: none;
            border: none;
            color: rgba(255,255,255,.55);
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: color .15s ease-in-out;
        }
        #logout-form button:hover {
            color: rgba(255,255,255,.75);
        }
    </style>
</head>
<body>
    @yield('navbar')
    
    <div class="loading-overlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div id="app-content">
        @yield('content')
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastr for notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // Handle navigation clicks (excluding logout)
            $(document).on('click', '.nav-link:not(.disabled):not([data-logout="true"])', function(e) {
                // Skip if it's a form button or has no href
                if ($(this).is('button') || $(this).attr('href') === '#' || !$(this).attr('href')) {
                    return;
                }
                
                e.preventDefault();
                const url = $(this).attr('href');
                const route = url.split('/').pop() || 'dashboard';
                
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
                        
                        // Reinitialize tooltips for new content
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    },
                    error: function(xhr) {
                        console.error('Navigation Error:', xhr);
                        if (xhr.status === 419) {
                            toastr.error('Session expired. Please refresh the page.');
                        } else {
                            console.log("Page Loading Error: ", xhr)
                            toastr.error('Error loading page. Please try again.');
                        }
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
                    success: function(response) {
                        toastr.success('Logged out successfully');
                        setTimeout(function() {
                            window.location.href = '/login';
                        }, 1000);
                    },
                    error: function(xhr) {
                        console.error('Logout Error:', xhr);
                        // Even on error, redirect to login (logout might have succeeded)
                        toastr.info('Redirecting to login...');
                        setTimeout(function() {
                            window.location.href = '/login';
                        }, 1000);
                    },
                    complete: function() {
                        $('.loading-overlay').hide();
                    }
                });
            });
            
            // Set initial active state based on current route
            function setInitialActiveState() {
                const path = window.location.pathname;
                $('.nav-link').each(function() {
                    const linkPath = $(this).attr('href');
                    if (linkPath && linkPath !== '#' && path === linkPath) {
                        $('.nav-link').removeClass('active');
                        $(this).addClass('active');
                    }
                });
            }
            
            // Configure Toastr options
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            setInitialActiveState();
            
            // Handle any flash messages from Laravel
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
            
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif
            
            @if(session('warning'))
                toastr.warning('{{ session('warning') }}');
            @endif
            
            @if(session('info'))
                toastr.info('{{ session('info') }}');
            @endif
        });
        
        // Global function to show loading
        function showLoading() {
            $('.loading-overlay').show();
        }
        
        // Global function to hide loading
        function hideLoading() {
            $('.loading-overlay').hide();
        }
        
        // Global AJAX error handler
        $(document).ajaxError(function(event, xhr, settings) {
            if (xhr.status === 419) {
                toastr.error('CSRF token mismatch. Please refresh the page.');
            } else if (xhr.status === 401) {
                toastr.error('Unauthorized. Please login again.');
                setTimeout(function() {
                    window.location.href = '/login';
                }, 2000);
            } else if (xhr.status === 403) {
                toastr.error('Access denied.');
            } else if (xhr.status === 500) {
                toastr.error('Server error. Please try again later.');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>