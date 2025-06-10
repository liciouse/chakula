<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the author's articles
     */
    public function index(Request $request)
{
    $user = Auth::user();
    $query = $user->posts()->withCount(['comments', 'likes']);

    // Apply filters
    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    // Apply sorting
    $sortBy = $request->get('sort', 'created_at');
    $sortOrder = $request->get('order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    $articles = $query->paginate(10)->appends($request->query());
    $categories = Category::all();

    return view('author.articles.index', compact('articles', 'categories'));
}


    /**
     * Show the form for creating a new article
     */
    public function create()
    {
        $categories = Category::all();
        return view('author.articles.create', compact('categories'));
    }

    /**
     * Store a newly created article
     */
    public function store(Request $request)
{
    // Updated validation to match your form data
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'excerpt' => 'nullable|string|max:500',
        'category_id' => 'required|exists:categories,id',
        'featured_image' => 'nullable|image|max:2048',
        'tags' => 'nullable|string',
        'status' => 'required|in:draft,pending,published',
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string|max:255',
        'meta_keywords' => 'nullable|string|max:255',
        'published_at' => 'nullable|date',
    ]);

    // Handle the action button to determine final status
    $finalStatus = $validated['status'] ?? 'draft';
    if ($request->has('action')) {
        switch ($request->input('action')) {
            case 'save':
                $finalStatus = 'published';
                break;
            case 'preview':
                $finalStatus = 'pending';
                break;
            case 'draft':
                $finalStatus = 'draft';
                break;
        }
    }

    // Create the post
    $post = new Post();
    $post->title = $validated['title'];
    $post->slug = $request->input('slug', Str::slug($validated['title']));
    $post->content = $validated['content'];
    $post->excerpt = $validated['excerpt'] ?? null;
    $post->category_id = $validated['category_id'];
    $post->status = $finalStatus;
    $post->meta_title = $validated['meta_title'] ?? null;
    $post->meta_description = $validated['meta_description'] ?? null;
    $post->meta_keywords = $validated['meta_keywords'] ?? null;
    $post->user_id = Auth::id();

    // Handle featured image upload
    if ($request->hasFile('featured_image')) {
        $path = $request->file('featured_image')->store('posts', 'public');
        $post->featured_image = $path;
    }

    // Set published_at if publishing
    if ($finalStatus === 'published') {
        $post->published_at = $validated['published_at'] ?? now();
    }

    // Save the post
    $post->save();

    // Handle tags - store as JSON for now
    if (!empty($validated['tags'])) {
        $tagsArray = array_map('trim', explode(',', $validated['tags']));
        $post->tags = json_encode($tagsArray);
        $post->save();
    }

    return redirect()->route('author.articles.index')
                    ->with('success', 'Article created successfully!');
}

    /**
     * Display the specified article
     */
    public function show(Post $post)
    {
        $this->authorize('view', $post);
        
        $post->load(['category', 'comments' => function($query) {
            $query->where('status', 'approved')->latest();
        }]);

        return view('author.articles.show', compact('post'));
    }

    /**
     * Show the form for editing the specified article
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);
        $categories = Category::all();
        return view('author.articles.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified article
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'featured_image' => 'nullable|image|max:2048',
            'tags' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('posts', 'public');
        }

        // Update slug if title changed
        if ($post->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Set published_at if publishing for first time
        if ($validated['status'] === 'published' && !$post->published_at && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        // Handle tags
        if (isset($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
            $post->syncTags($tags);
        }

        return redirect()->route('author.articles.index')
                        ->with('success', 'Article updated successfully!');
    }

    /**
     * Remove the specified article
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        // Delete featured image
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Duplicate an article
     */
    public function duplicate(Post $post)
    {
        $this->authorize('view', $post);

        $newPost = $post->replicate();
        $newPost->title = $post->title . ' (Copy)';
        $newPost->slug = Str::slug($newPost->title);
        $newPost->status = 'draft';
        $newPost->published_at = null;
        $newPost->views = 0;
        $newPost->save();

        return redirect()->route('author.articles.edit', $newPost)
                        ->with('success', 'Article duplicated successfully!');
    }

    /**
     * Toggle article status
     */
    public function toggleStatus(Post $post)
    {
        $this->authorize('update', $post);

        $newStatus = $post->status === 'published' ? 'draft' : 'published';
        
        $post->update([
            'status' => $newStatus,
            'published_at' => $newStatus === 'published' && !$post->published_at ? now() : $post->published_at
        ]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Article status updated successfully!'
        ]);
    }

    /**
     * Bulk actions for articles
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,draft,delete',
            'article_ids' => 'required|array',
            'article_ids.*' => 'exists:posts,id'
        ]);

        $posts = Post::whereIn('id', $validated['article_ids'])
                    ->where('user_id', Auth::id());

        switch ($validated['action']) {
            case 'publish':
                $posts->update([
                    'status' => 'published',
                    'published_at' => now()
                ]);
                break;
            case 'draft':
                $posts->update(['status' => 'draft']);
                break;
            case 'delete':
                // Delete associated images
                $postsToDelete = $posts->get();
                foreach ($postsToDelete as $post) {
                    if ($post->featured_image) {
                        Storage::disk('public')->delete($post->featured_image);
                    }
                }
                $posts->delete();
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Auto-save draft
     */
    public function autoSave(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:posts,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validated['id']) {
            $post = Post::findOrFail($validated['id']);
            $this->authorize('update', $post);
            $post->update($validated);
        } else {
            $post = Post::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'excerpt' => $validated['excerpt'],
                'category_id' => $validated['category_id'],
                'user_id' => Auth::id(),
                'slug' => Str::slug($validated['title']),
                'status' => 'draft'
            ]);
        }

        return response()->json([
            'success' => true,
            'post_id' => $post->id,
            'saved_at' => $post->updated_at->format('g:i A')
        ]);
    }
}