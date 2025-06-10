<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect based on user role
            $user = Auth::user();
            
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                    Log::info('admin');
                case 'editor':
                    return redirect()->route('editor.dashboard');
                    Log::info('editor');
                case 'author':
                    return redirect()->route('author.dashboard');
                    Log::info('author');
                default:
                    return redirect()->route('user.dashboard');
                    Log::info('user');
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}