<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the profile settings page
     */
    public function index()
    {
        $user = Auth::user();
        return view('author.profile', compact('user'));
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'bio' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($validated['current_password'], Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'notifications' => 'array',
            'notifications.*' => 'string',
            'default_visibility' => 'in:published,draft',
            'auto_save_drafts' => 'boolean',
            'enable_comments' => 'boolean',
            'email_notifications' => 'boolean',
            'theme' => 'in:light,dark,auto',
            'language' => 'string|max:10',
            'timezone' => 'string|max:50',
        ]);

        // Handle notification settings
        $notifications = $request->input('notifications', []);
        
        $user->update([
            'notification_settings' => $notifications,
            'default_visibility' => $validated['default_visibility'] ?? 'draft',
            'auto_save_drafts' => $request->boolean('auto_save_drafts'),
            'enable_comments' => $request->boolean('enable_comments'),
            'email_notifications' => $request->boolean('email_notifications'),
            'theme' => $validated['theme'] ?? 'light',
            'language' => $validated['language'] ?? 'en',
            'timezone' => $validated['timezone'] ?? 'UTC',
        ]);

        return redirect()->back()->with('success', 'Preferences updated successfully!');
    }

    /**
     * Update social media links
     */
    public function updateSocial(Request $request)
    {
        $validated = $request->validate([
            'social' => 'array',
            'social.twitter' => 'nullable|url|max:255',
            'social.facebook' => 'nullable|url|max:255',
            'social.instagram' => 'nullable|url|max:255',
            'social.linkedin' => 'nullable|url|max:255',
            'social.youtube' => 'nullable|url|max:255',
            'social.github' => 'nullable|url|max:255',
            'social.website' => 'nullable|url|max:255',
        ]);

        Auth::user()->update([
            'social_links' => $validated['social'] ?? []
        ]);

        return redirect()->back()->with('success', 'Social links updated successfully!');
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        
        $user->update(['avatar' => $path]);

        return response()->json([
            'success' => true,
            'avatar_url' => Storage::url($path),
            'message' => 'Avatar updated successfully!'
        ]);
    }

    /**
     * Remove user avatar
     */
    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Avatar removed successfully!'
        ]);
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request)
    {
        $validated = $request->validate([
            'two_factor_enabled' => 'boolean',
            'login_notifications' => 'boolean',
            'session_timeout' => 'integer|min:5|max:720', // 5 minutes to 12 hours
        ]);

        $user = Auth::user();
        
        $user->update([
            'two_factor_enabled' => $request->boolean('two_factor_enabled'),
            'login_notifications' => $request->boolean('login_notifications'),
            'session_timeout' => $validated['session_timeout'] ?? 120, // Default 2 hours
        ]);

        return redirect()->back()->with('success', 'Security settings updated successfully!');
    }

    /**
     * Download user data (GDPR compliance)
     */
    public function downloadData()
    {
        $user = Auth::user();
        
        // Collect user data
        $userData = [
            'profile' => $user->only([
                'name', 'email', 'bio', 'website', 'location', 
                'phone', 'date_of_birth', 'created_at', 'updated_at'
            ]),
            'posts' => $user->posts()->with('category')->get()->toArray(),
            'comments' => $user->comments()->get()->toArray(),
            'social_links' => $user->social_links,
            'preferences' => [
                'notification_settings' => $user->notification_settings,
                'default_visibility' => $user->default_visibility,
                'auto_save_drafts' => $user->auto_save_drafts,
                'enable_comments' => $user->enable_comments,
                'theme' => $user->theme,
                'language' => $user->language,
                'timezone' => $user->timezone,
            ]
        ];

        $filename = 'user_data_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
        
        return response()->json($userData)
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Deactivate user account
     */
    public function deactivateAccount(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'reason' => 'nullable|string|max:500'
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Password is incorrect']);
        }

        $user = Auth::user();
        
        // Soft delete or deactivate
        $user->update([
            'status' => 'inactive',
            'deactivated_at' => now(),
            'deactivation_reason' => $request->reason
        ]);

        // Log out the user
        Auth::logout();

        return redirect()->route('home')->with('message', 'Your account has been deactivated.');
    }

    /**
     * Get profile statistics
     */
    public function statistics()
    {
        $user = Auth::user();
        
        $stats = [
            'posts_count' => $user->posts()->count(),
            'published_posts' => $user->posts()->where('status', 'published')->count(),
            'draft_posts' => $user->posts()->where('status', 'draft')->count(),
            'total_views' => $user->posts()->sum('views'),
            'total_comments' => $user->posts()->withCount('comments')->get()->sum('comments_count'),
            'total_likes' => $user->posts()->withCount('likes')->get()->sum('likes_count'),
            'member_since' => $user->created_at->format('M Y'),
            'last_post' => $user->posts()->latest()->first()?->created_at?->diffForHumans(),
        ];

        return response()->json($stats);
    }

    /**
     * Update email notification preferences
     */
    public function updateEmailPreferences(Request $request)
    {
        $validated = $request->validate([
            'email_preferences' => 'array',
            'email_preferences.*' => 'boolean'
        ]);

        $user = Auth::user();
        $preferences = $request->input('email_preferences', []);

        $user->update([
            'email_preferences' => [
                'new_comments' => $preferences['new_comments'] ?? false,
                'new_followers' => $preferences['new_followers'] ?? false,
                'post_published' => $preferences['post_published'] ?? false,
                'weekly_digest' => $preferences['weekly_digest'] ?? false,
                'marketing' => $preferences['marketing'] ?? false,
            ]
        ]);

        return redirect()->back()->with('success', 'Email preferences updated successfully!');
    }

    /**
     * Export user profile data
     */
    public function exportProfile()
    {
        $user = Auth::user();
        
        $data = [
            'Personal Information' => [
                'Name' => $user->name,
                'Email' => $user->email,
                'Bio' => $user->bio,
                'Website' => $user->website,
                'Location' => $user->location,
                'Phone' => $user->phone,
                'Date of Birth' => $user->date_of_birth,
                'Member Since' => $user->created_at->format('F j, Y'),
            ],
            'Statistics' => [
                'Total Posts' => $user->posts()->count(),
                'Published Posts' => $user->posts()->where('status', 'published')->count(),
                'Draft Posts' => $user->posts()->where('status', 'draft')->count(),
                'Total Views' => $user->posts()->sum('views'),
                'Total Comments' => $user->posts()->withCount('comments')->get()->sum('comments_count'),
            ]
        ];

        $filename = 'profile_' . $user->id . '_' . now()->format('Y-m-d') . '.json';
        
        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }
}