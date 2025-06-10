<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Editor Dashboard') - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <style>
        :root {
            --editor-primary: #198754;
            --editor-secondary: #6c757d;
            --editor-sidebar: #212529;
        }
        
        .sidebar {
            min-height: 100vh;
            background-color: var(--editor-sidebar);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--editor-primary);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        .navbar-brand {
            font-weight: 600;
            color: var(--editor-primary) !important;
        }
        
        .card {
            border: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            font-size: 0.75em;
        }
        
        .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, var(--editor-primary) 0%, #20c997 100%);
            color: white;
        }
        
        .stats-card .stats-icon {
            font-size: 2rem;
            opacity: 0.7;
        }
        
        .activity-item {
            border-left: 3px solid var(--editor-primary);
            padding-left: 15px;
            margin-bottom: 15px;
        }
        
        .select2-container--default .select2-selection--single {
            height: 38px;
            line-height: 36px;
        }
        
        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar">
                <div class="d-flex flex-column">
                    <!-- Brand -->
                    <div class="p-3 border-bottom border-secondary">
                        <h5 class="text-white mb-0">
                            <i class="fas fa-utensils text-success me-2"></i>
                            Food Blog
                        </h5>
                        <small class="text-muted">Editor Panel</small>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="nav flex-column pt-3">
                        <a class="nav-link {{ request()->routeIs('editor.dashboard') ? 'active' : '' }}" href="{{ route('editor.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('editor.content.*') ? 'active' : '' }}" href="{{ route('editor.content.index') }}">
                            <i class="fas fa-file-alt"></i>
                            Content Management
                        </a>
                        <a class="nav-link {{ request()->routeIs('editor.categories.*') ? 'active' : '' }}" href="{{ route('editor.categories.index') }}">
                            <i class="fas fa-tags"></i>
                            Categories
                        </a>
                        <a class="nav-link {{ request()->routeIs('editor.users.*') ? 'active' : '' }}" href="{{ route('editor.users.index') }}">
                            <i class="fas fa-users"></i>
                            Authors
                        </a>
                        <a class="nav-link {{ request()->routeIs('editor.comments.*') ? 'active' : '' }}" href="{{ route('editor.comments.index') }}">
                            <i class="fas fa-comments"></i>
                            Comments
                        </a>
                        
                        <hr class="text-secondary my-3">
                        
                        <a class="nav-link" href="{{ route('home') }}" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            View Site
                        </a>
                        <a class="nav-link" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user-cog"></i>
                            Profile
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
                    <div class="container-fluid">
                        <div class="navbar-nav ms-auto">
                            <!-- Notifications -->
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-bell"></i>
                                    <span class="badge bg-danger badge-sm">3</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><h6 class="dropdown-header">Notifications</h6></li>
                                    <li><a class="dropdown-item" href="#">New post pending review</a></li>
                                    <li><a class="dropdown-item" href="#">Comment awaiting approval</a></li>
                                    <li><a class="dropdown-item" href="#">New author registered</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-center" href="#">View all notifications</a></li>
                                </ul>
                            </div>
                            
                            <!-- User Menu -->
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    @if(Auth::user()->avatar)
                                        <img src="{{ asset(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="rounded-circle me-2" width="32" height="32">
                                    @else
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 32px; height: 32px;">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                    {{ Auth::user()->name }}
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                                    <li><a class="dropdown-item" href="{{ route('profile.settings') }}">Settings</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="dropdown-item">Logout</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Page Content -->
                <div class="px-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // CSRF Token Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5'
        });
        
        // Global Functions
        function showToast(type, message) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
            
            Toast.fire({
                icon: type,
                title: message
            });
        }
        
        function confirmDelete(url, title = 'Delete Item') {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
        
        // Auto-hide alerts
        $('.alert').delay(5000).fadeOut();
    </script>
    
    @stack('scripts')
</body>
</html>