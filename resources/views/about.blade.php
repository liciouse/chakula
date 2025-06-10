{{-- resources/views/about.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>About Us – Food Blog</title>
</head>
<body>
    <header>
        <a href="{{ route('home') }}">Home</a> | <a href="{{ route('contact') }}">Contact</a>
    </header>

    <main>
        <h1>About Our Food Blog</h1>
        <p>Here at Food Blog, we share the best recipes, cooking tips, and food stories.</p>
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Food Blog.</p>
    </footer>
</body>
</html>
