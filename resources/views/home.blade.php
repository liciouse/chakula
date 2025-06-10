{{-- resources/views/home.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Food Blog Home</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"> {{-- if you’re using Laravel Mix / Tailwind / Bootstrap --}}
</head>
<body>
    <header>
        <nav>
            <a href="{{ route('home') }}">Home</a> |
            <a href="{{ route('about') }}">About</a> |
            <a href="{{ route('contact') }}">Contact</a> |
            @guest
                <a href="{{ route('login') }}">Login</a> |
                <a href="{{ route('register') }}">Register</a>
            @else
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout ({{ Auth::user()->name }})
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endguest
        </nav>
    </header>

    <main>
        <h1>Welcome to the Food Blog!</h1>
        <p>This is the homepage. Browse our latest recipes and articles.</p>
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Food Blog. All rights reserved.</p>
    </footer>
</body>
</html>
