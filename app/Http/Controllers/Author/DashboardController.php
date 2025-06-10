<?php
namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Comment; // ADD THIS IMPORT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get recent posts
        $posts = $user->posts()->latest()->limit(5)->get();
        
        // Calculate all the statistics your view expects
        $publishedCount = $user->posts()->where('status', 'published')->count();
        $pendingCount = $user->posts()->where('status', 'pending')->count();
        $draftCount = $user->posts()->where('status', 'draft')->count();
        
        // Calculate comments count (comments on user's posts)
        $commentsCount = Comment::whereHas('post', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        
        // Additional stats (these might be needed by your view)
        $totalPosts = $user->posts()->count();
        $totalViews = $user->posts()->sum('views') ?? 0;
        $totalLikes = $user->posts()->withCount('likes')->get()->sum('likes_count') ?? 0;
        
        return view('author.dashboard', compact(
            'posts',
            'publishedCount',
            'pendingCount', 
            'commentsCount'
        ));
    }
}