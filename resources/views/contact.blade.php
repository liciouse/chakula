{{-- resources/views/contact.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contact Us – Food Blog</title>
</head>
<body>
    <header>
        <a href="{{ route('home') }}">Home</a> | <a href="{{ route('about') }}">About</a>
    </header>

    <main>
        <h1>Contact Us</h1>

        @if(session('success'))
            <div style="background: #e0ffe0; padding: 10px; margin-bottom: 1rem;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('contact.send') }}" method="POST">
            @csrf
            <label>
                Your Name:<br>
                <input type="text" name="name" value="{{ old('name') }}">
                @error('name') <div style="color:red;">{{ $message }}</div> @enderror
            </label><br><br>

            <label>
                Your Email:<br>
                <input type="email" name="email" value="{{ old('email') }}">
                @error('email') <div style="color:red;">{{ $message }}</div> @enderror
            </label><br><br>

            <label>
                Message:<br>
                <textarea name="message">{{ old('message') }}</textarea>
                @error('message') <div style="color:red;">{{ $message }}</div> @enderror
            </label><br><br>

            <button type="submit">Send Message</button>
        </form>
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Food Blog.</p>
    </footer>
</body>
</html>
