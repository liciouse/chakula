<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        try {
            $posts = Post::with(['user', 'categories'])
                        ->latest()
                        ->paginate(10, ['*'], 'posts_page');
            
            $comments = Comment::with(['user', 'post'])
                            ->latest()
                            ->paginate(10, ['*'], 'comments_page');
            
            $view = request()->ajax() 
                ? 'admin.content.content' 
                : 'admin.content.index';
            
            return view($view, compact('posts', 'comments'));
            
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to load content');
        }
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.content.create', compact('categories'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $this->validatePostRequest($request);
            
            $post = Post::create([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']),
                'content' => $validated['content'],
                'excerpt' => $validated['excerpt'],
                'user_id' => auth()->id(),
                'featured_image' => $this->storeFeaturedImage($request),
                'status' => $validated['status'],
            ]);
            
            $post->categories()->sync($validated['categories']);
            
            return response()->json([
                'success' => true,
                'redirect' => route('admin.content.index')
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to create post');
        }
    }

    public function edit(Post $post)
    {
        $categories = Category::all();
        $selectedCategories = $post->categories->pluck('id')->toArray();
        
        return view('admin.content.edit', compact('post', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Post $post)
    {
        try {
            $validated = $this->validatePostRequest($request, $post->id);
            
            $post->update([
                'title' => $validated['title'],
                'slug' => Str::slug($validated['title']),
                'content' => $validated['content'],
                'excerpt' => $validated['excerpt'],
                'status' => $validated['status'],
                'featured_image' => $request->hasFile('featured_image') 
                    ? $this->storeFeaturedImage($request) 
                    : $post->featured_image,
            ]);
            
            $post->categories()->sync($validated['categories']);
            
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to update post');
        }
    }

    public function destroy(Post $post)
    {
        try {
            // Delete associated image if exists
            if ($post->featured_image) {
                Storage::delete($post->featured_image);
            }
            
            $post->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to delete post');
        }
    }

    public function deleteComment(Comment $comment)
    {
        try {
            $comment->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to delete comment');
        }
    }

    protected function validatePostRequest(Request $request, $postId = null)
    {
        return $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts')->ignore($postId)
            ],
            'content' => 'required|string',
            'excerpt' => 'required|string|max:300',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,archived',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);
    }

    protected function storeFeaturedImage(Request $request)
    {
        if ($request->hasFile('featured_image')) {
            return $request->file('featured_image')->store('posts', 'public');
        }
        return null;
    }

    protected function handleError(\Exception $e, $message = 'An error occurred')
    {
        Log::error($e->getMessage());
        
        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 500);
        }
        
        return back()->with('error', $message);
    }
}
