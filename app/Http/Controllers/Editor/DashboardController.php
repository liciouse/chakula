<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pending_articles' => Post::where('status', 'pending')->count(),
            'published_articles' => Post::where('status', 'published')->count(),
            'draft_articles' => Post::where('status', 'draft')->count(),
            'total_categories' => Category::where('status', 'active')->count(),
            'pending_comments' => Comment::where('status', 'pending')->count(),
            'total_authors' => User::where('role', 'author')->count(),
        ];

        // Recent activity
        $recent_posts = Post::with(['author', 'category'])
            ->whereIn('status', ['pending', 'published'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $recent_comments = Comment::with(['post', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('editor.dashboard', compact('stats', 'recent_posts', 'recent_comments'));
    }

    public function getStats()
    {
        return response()->json([
            'pending_articles' => Post::where('status', 'pending')->count(),
            'published_articles' => Post::where('status', 'published')->count(),
            'draft_articles' => Post::where('status', 'draft')->count(),
            'total_categories' => Category::where('status', 'active')->count(),
            'pending_comments' => Comment::where('status', 'pending')->count(),
            'total_authors' => User::where('role', 'author')->count(),
        ]);
    }
}