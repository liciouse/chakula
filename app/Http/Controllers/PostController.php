<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of posts (public blog index)
     */
    public function index()
    {
        $posts = Post::where('published', true)
                    ->with(['user', 'category'])
                    ->withCount(['comments', 'likes'])
                    ->latest('published_at')
                    ->paginate(12);

        return view('posts.index', compact('posts'));
    }

    /**
     * Display the specified post (public post view)
     */
    public function show(Post $post)
    {
        // Only show published posts to public
        if (!$post->published) {
            abort(404);
        }

        // Increment view count
        $post->increment('views');

        // Load relationships
        $post->load(['user', 'category', 'tags']);
        
        // Load comments with pagination
        $comments = $post->comments()
                        ->where('approved', true)
                        ->with('user')
                        ->latest()
                        ->paginate(10);

        // Get related posts
        $relatedPosts = Post::where('published', true)
                           ->where('id', '!=', $post->id)
                           ->where('category_id', $post->category_id)
                           ->withCount(['comments', 'likes'])
                           ->latest()
                           ->take(3)
                           ->get();

        return view('posts.show', compact('post', 'comments', 'relatedPosts'));
    }

    /**
     * Display posts by category
     */
    public function category(Category $category)
    {
        $posts = Post::where('published', true)
                    ->where('category_id', $category->id)
                    ->with(['user', 'category'])
                    ->withCount(['comments', 'likes'])
                    ->latest('published_at')
                    ->paginate(12);

        return view('posts.category', compact('posts', 'category'));
    }

    /**
     * Display posts by tag
     */
    public function tag(Tag $tag)
    {
        $posts = $tag->posts()
                    ->where('published', true)
                    ->with(['user', 'category'])
                    ->withCount(['comments', 'likes'])
                    ->latest('published_at')
                    ->paginate(12);

        return view('posts.tag', compact('posts', 'tag'));
    }

    /**
     * Store a comment on a post
     */
    public function storeComment(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|min:10|max:1000',
        ]);

        if (!$post->published) {
            abort(404);
        }

        $comment = new Comment();
        $comment->content = $request->content;
        $comment->post_id = $post->id;
        $comment->user_id = Auth::id();
        $comment->approved = false; // Require approval
        $comment->save();

        return back()->with('success', 'Comment submitted and awaiting approval.');
    }

    /**
     * Toggle like on a post
     */
    public function toggleLike(Post $post)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        if (!$post->published) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $like = $post->likes()->where('user_id', Auth::id())->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            $post->likes()->create(['user_id' => Auth::id()]);
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $post->likes()->count()
        ]);
    }

    /**
     * Display author's public profile
     */
    public function authorProfile(User $user)
    {
        // Get author's published posts
        $posts = $user->posts()
                     ->where('published', true)
                     ->withCount(['comments', 'likes'])
                     ->latest('published_at')
                     ->take(6)
                     ->get();

        $totalPosts = $user->posts()->where('published', true)->count();

        return view('authors.profile', compact('user', 'posts', 'totalPosts'));
    }

    /**
     * Display author's posts
     */
    public function authorPosts(User $user)
    {
        $posts = $user->posts()
                     ->where('published', true)
                     ->with(['category'])
                     ->withCount(['comments', 'likes'])
                     ->latest('published_at')
                     ->paginate(12);

        return view('authors.posts', compact('user', 'posts'));
    }

    /**
     * Search posts
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $posts = Post::where('published', true)
                    ->where(function($q) use ($query) {
                        $q->where('title', 'LIKE', "%{$query}%")
                          ->orWhere('content', 'LIKE', "%{$query}%")
                          ->orWhere('excerpt', 'LIKE', "%{$query}%");
                    })
                    ->with(['user', 'category'])
                    ->withCount(['comments', 'likes'])
                    ->latest('published_at')
                    ->paginate(12);

        return view('posts.search', compact('posts', 'query'));
    }

    /**
     * Display archive page
     */
    public function archive()
    {
        $archives = Post::where('published', true)
                       ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
                       ->groupBy('year', 'month')
                       ->orderBy('year', 'desc')
                       ->orderBy('month', 'desc')
                       ->get();

        return view('posts.archive', compact('archives'));
    }

    /**
     * Display posts by year
     */
    public function yearArchive($year)
    {
        $posts = Post::where('published', true)
                    ->whereYear('published_at', $year)
                    ->with(['user', 'category'])
                    ->withCount(['comments', 'likes'])
                    ->latest('published_at')
                    ->paginate(12);

        return view('posts.year-archive', compact('posts', 'year'));
    }

    /**
     * Generate RSS Feed
     */
    public function feed()
    {
        $posts = Post::where('published', true)
                    ->with(['user', 'category'])
                    ->latest('published_at')
                    ->take(20)
                    ->get();

        return response()->view('posts.feed', compact('posts'))
                        ->header('Content-Type', 'application/rss+xml');
    }

    /**
     * Generate sitemap
     */
    public function sitemap()
    {
        $posts = Post::where('published', true)
                    ->latest('published_at')
                    ->get();

        return response()->view('posts.sitemap', compact('posts'))
                        ->header('Content-Type', 'application/xml');
    }
}