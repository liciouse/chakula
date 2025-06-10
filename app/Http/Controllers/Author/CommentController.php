<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of comments on author's posts
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Comment::whereHas('post', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['post:id,title,slug', 'user:id,name']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('content', 'like', '%' . $request->search . '%')
                  ->orWhere('author_name', 'like', '%' . $request->search . '%')
                  ->orWhere('author_email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('article')) {
            $query->where('post_id', $request->article);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $comments = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get author's posts for filter dropdown
        $posts = $user->posts()->select('id', 'title')->get();

        // Get comment statistics
        $stats = [
            'total' => Comment::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            'approved' => Comment::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'approved')->count(),
            'pending' => Comment::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'pending')->count(),
            'spam' => Comment::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', 'spam')->count(),
        ];

        return view('author.comments.index', compact('comments', 'posts', 'stats'));
    }

    /**
     * Show the specified comment
     */
    public function show(Comment $comment)
    {
        $this->authorize('view', $comment);
        
        $comment->load(['post:id,title,slug', 'replies', 'user:id,name']);
        
        return view('author.comments.show', compact('comment'));
    }

    /**
     * Update comment status
     */
    public function updateStatus(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'status' => 'required|in:approved,pending,spam'
        ]);

        $comment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Comment status updated successfully!'
        ]);
    }

    /**
     * Reply to a comment
     */
    public function reply(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        $reply = Comment::create([
            'post_id' => $comment->post_id,
            'parent_id' => $comment->id,
            'content' => $validated['content'],
            'author_name' => Auth::user()->name,
            'author_email' => Auth::user()->email,
            'user_id' => Auth::id(),
            'status' => 'approved'
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply->load('user:id,name'),
            'message' => 'Reply posted successfully!'
        ]);
    }

    /**
     * Update comment content
     */
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
        ]);

        $comment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully!'
        ]);
    }

    /**
     * Remove the specified comment
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        
        // Delete all replies first
        $comment->replies()->delete();
        
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully!'
        ]);
    }

    /**
     * Bulk actions for comments
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,pending,spam,delete',
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id'
        ]);

        $comments = Comment::whereIn('id', $validated['comment_ids'])
                          ->whereHas('post', function($q) {
                              $q->where('user_id', Auth::id());
                          });

        switch ($validated['action']) {
            case 'approve':
                $comments->update(['status' => 'approved']);
                break;
            case 'pending':
                $comments->update(['status' => 'pending']);
                break;
            case 'spam':
                $comments->update(['status' => 'spam']);
                break;
            case 'delete':
                // Delete replies first
                $commentIds = $comments->pluck('id');
                Comment::whereIn('parent_id', $commentIds)->delete();
                $comments->delete();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk action completed successfully!'
        ]);
    }

    /**
     * Mark comments as read
     */
    public function markAsRead(Request $request)
    {
        $validated = $request->validate([
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id'
        ]);

        Comment::whereIn('id', $validated['comment_ids'])
               ->whereHas('post', function($q) {
                   $q->where('user_id', Auth::id());
               })
               ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Comments marked as read!'
        ]);
    }

    /**
     * Mark all comments as read
     */
    public function markAllAsRead()
    {
        Comment::whereHas('post', function($q) {
            $q->where('user_id', Auth::id());
        })->whereNull('read_at')
          ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All comments marked as read!'
        ]);
    }

    /**
     * Get comment statistics for dashboard
     */
    public function statistics()
    {
        $user = Auth::user();
        
        $stats = [
            'today' => Comment::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereDate('created_at', today())->count(),
            
            'this_week' => Comment::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('created_at', '>=', now()->startOfWeek())->count(),
            
            'this_month' => Comment::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereMonth('created_at', now()->month)->count(),
            
            'unread' => Comment::whereHas('post', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->whereNull('read_at')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export comments to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $query = Comment::whereHas('post', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['post:id,title']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('article')) {
            $query->where('post_id', $request->article);
        }

        $comments = $query->orderBy('created_at', 'desc')->get();

        $filename = 'comments_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($comments) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Article', 'Author Name', 'Author Email', 
                'Content', 'Status', 'Created At', 'IP Address'
            ]);

            // CSV data
            foreach ($comments as $comment) {
                fputcsv($file, [
                    $comment->id,
                    $comment->post->title,
                    $comment->author_name,
                    $comment->author_email,
                    strip_tags($comment->content),
                    ucfirst($comment->status),
                    $comment->created_at->format('Y-m-d H:i:s'),
                    $comment->ip_address ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}