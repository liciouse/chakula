<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Author Panel') - Food Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-600 text-white">
            <div class="p-6">
                <h1 class="text-xl font-bold">Author Panel</h1>
            </div>
            
            <nav class="mt-6">
                <a href="{{ route('author.dashboard') }}" 
                   class="flex items-center px-6 py-3 text-white hover:bg-blue-700 {{ request()->routeIs('author.dashboard') ? 'bg-blue-700 border-r-4 border-white' : '' }}">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('author.articles.index') }}" 
                   class="flex items-center px-6 py-3 text-white hover:bg-blue-700 {{ request()->routeIs('author.articles.*') ? 'bg-blue-700 border-r-4 border-white' : '' }}">
                    <i class="fas fa-newspaper mr-3"></i>
                    Articles
                </a>
                
                <a href="{{ route('author.comments.index') }}" 
                   class="flex items-center px-6 py-3 text-white hover:bg-blue-700 {{ request()->routeIs('author.comments.*') ? 'bg-blue-700 border-r-4 border-white' : '' }}">
                    <i class="fas fa-comments mr-3"></i>
                    Comments
                </a>
                
                <a href="{{ route('author.profile') }}" 
                   class="flex items-center px-6 py-3 text-white hover:bg-blue-700 {{ request()->routeIs('author.profile') ? 'bg-blue-700 border-r-4 border-white' : '' }}">
                    <i class="fas fa-user mr-3"></i>
                    Profile
                </a>
                
                <a href="{{ route('author.profile') }}" 
                   class="flex items-center px-6 py-3 text-white hover:bg-blue-700">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a>
            </nav>

            <!-- User Info at Bottom -->
            <div class="absolute bottom-0 w-64 p-6 border-t border-blue-500">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-800 rounded-full flex items-center justify-center">
                        <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-blue-200">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-blue-200 hover:text-white">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-500">{{ now()->format('M d, Y') }}</span>
                            <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                <i class="fas fa-bell text-gray-600"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>