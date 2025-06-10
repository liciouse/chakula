<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['author', 'category']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->orderBy('updated_at', 'desc')->paginate(15);
        $categories = Category::where('status', 'active')->get();

        return view('editor.content.index', compact('posts', 'categories'));
    }

    public function show(Post $post)
    {
        return view('editor.content.show', compact('post'));
    }

    public function edit(Post $post)
    {
        $categories = Category::where('status', 'active')->get();
        return view('editor.content.edit', compact('post', 'categories'));
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,pending,published,rejected',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tags' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->only([
            'title', 'content', 'excerpt', 'category_id', 'status', 'tags',
            'meta_title', 'meta_description'
        ]);

        // Generate slug if title changed
        if ($post->title !== $request->title) {
            $data['slug'] = Str::slug($request->title);
            
            // Ensure slug is unique
            $originalSlug = $data['slug'];
            $counter = 1;
            while (Post::where('slug', $data['slug'])->where('id', '!=', $post->id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $image = $request->file('featured_image');
            $imageName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/posts'), $imageName);
            $data['featured_image'] = 'uploads/posts/' . $imageName;
            
            // Delete old image if exists
            if ($post->featured_image && file_exists(public_path($post->featured_image))) {
                unlink(public_path($post->featured_image));
            }
        }

        // Set published_at if publishing
        if ($request->status === 'published' && $post->status !== 'published') {
            $data['published_at'] = now();
        }

        $post->update($data);

        // Log the editor action
        activity()
            ->causedBy(Auth::user())
            ->performedOn($post)
            ->log('Editor updated post: ' . $post->title);

        return redirect()
            ->route('editor.content.index')
            ->with('success', 'Post updated successfully!');
    }

    public function destroy(Post $post)
    {
        // Delete featured image if exists
        if ($post->featured_image && file_exists(public_path($post->featured_image))) {
            unlink(public_path($post->featured_image));
        }

        $post->delete();

        return redirect()
            ->route('editor.content.index')
            ->with('success', 'Post deleted successfully!');
    }

    public function approve(Post $post)
    {
        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        // Log the approval
        activity()
            ->causedBy(Auth::user())
            ->performedOn($post)
            ->log('Editor approved post: ' . $post->title);

        return response()->json([
            'success' => true,
            'message' => 'Post approved and published!'
        ]);
    }

    public function reject(Request $request, Post $post)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $post->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Log the rejection
        activity()
            ->causedBy(Auth::user())
            ->performedOn($post)
            ->log('Editor rejected post: ' . $post->title);

        return response()->json([
            'success' => true,
            'message' => 'Post rejected!'
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'post_ids' => 'required|array',
            'post_ids.*' => 'exists:posts,id',
            'rejection_reason' => 'required_if:action,reject|string|max:500'
        ]);

        $posts = Post::whereIn('id', $request->post_ids)->get();

        foreach ($posts as $post) {
            switch ($request->action) {
                case 'approve':
                    $post->update([
                        'status' => 'published',
                        'published_at' => $post->published_at ?? now(),
                    ]);
                    break;
                
                case 'reject':
                    $post->update([
                        'status' => 'rejected',
                        'rejection_reason' => $request->rejection_reason,
                    ]);
                    break;
                
                case 'delete':
                    if ($post->featured_image && file_exists(public_path($post->featured_image))) {
                        unlink(public_path($post->featured_image));
                    }
                    $post->delete();
                    break;
            }
        }

        $message = count($posts) . ' posts ' . $request->action . 'd successfully!';
        
        return redirect()
            ->route('editor.content.index')
            ->with('success', $message);
    }
}