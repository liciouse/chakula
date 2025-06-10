@extends('layouts.editor')

@section('title', 'Editor Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Editor Dashboard</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-success" onclick="refreshStats()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <i class="fas fa-clock fs-1 mb-2"></i>
                            <h5 class="card-title">Pending Articles</h5>
                            <p class="card-text display-6">{{ $stats['pending_articles'] }}</p>
                            <a href="{{ route('editor.content.index', ['status' => 'pending']) }}" class="btn btn-dark btn-sm">Review</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fs-1 mb-2"></i>
                            <h5 class="card-title">Published</h5>
                            <p class="card-text display-6">{{ $stats['published_articles'] }}</p>
                            <a href="{{ route('editor.content.index', ['status' => 'published']) }}" class="btn btn-light btn-sm">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-edit fs-1 mb-2"></i>
                            <h5 class="card-title">Drafts</h5>
                            <p class="card-text display-6">{{ $stats['draft_articles'] }}</p>
                            <a href="{{ route('editor.content.index', ['status' => 'draft']) }}" class="btn btn-light btn-sm">View</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-tags fs-1 mb-2"></i>
                            <h5 class="card-title">Categories</h5>
                            <p class="card-text display-6">{{ $stats['total_categories'] }}</p>
                            <a href="{{ route('editor.categories.index') }}" class="btn btn-light btn-sm">Manage</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-comments fs-1 mb-2"></i>
                            <h5 class="card-title">Pending Comments</h5>
                            <p class="card-text display-6">{{ $stats['pending_comments'] }}</p>
                            <a href="{{ route('editor.comments.index', ['status' => 'pending']) }}" class="btn btn-light btn-sm">Review</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fs-1 mb-2"></i>
                            <h5 class="card-title">Authors</h5>
                            <p class="card-text display-6">{{ $stats['total_authors'] }}</p>
                            <a href="{{ route('editor.authors.index') }}" class="btn btn-light btn-sm">View</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Posts -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Recent Posts Requiring Attention</h5>
                        </div>
                        <div class="card-body">
                            @if($recent_posts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Status</th>
                                                <th>Updated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recent_posts as $post)
                                            <tr>
                                                <td>
                                                    <strong>{{ Str::limit($post->title, 40) }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $post->category->name ?? 'No Category' }}</small>
                                                </td>
                                                <td>{{ $post->author->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $post->status === 'pending' ? 'warning' : ($post->status === 'published' ? 'success' : 'secondary') }}">
                                                        {{ ucfirst($post->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $post->updated_at->diffForHumans() }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('editor.content.show', $post) }}" class="btn btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('editor.content.edit', $post) }}" class="btn btn-outline-secondary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @if($post->status === 'pending')
                                                        <button type="button" class="btn btn-outline-success" onclick="quickApprove({{ $post->id }})">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('editor.content.index') }}" class="btn btn-success">View All Posts</a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">No posts requiring attention at the moment.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Recent Comments -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Pending Comments</h5>
                        </div>
                        <div class="card-body">
                            @if($recent_comments->count() > 0)
                                @foreach($recent_comments as $comment)
                                <div class="border-bottom pb-2 mb-2">
                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                    <p class="mb-1">{{ Str::limit($comment->content, 80) }}</p>
                                    <small>
                                        On: <a href="{{ route('editor.content.show', $comment->post) }}">{{ Str::limit($comment->post->title, 30) }}</a>
                                    </small>
                                    <div class="mt-1">
                                        <button class="btn btn-sm btn-outline-success" onclick="approveComment({{ $comment->id }})">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="rejectComment({{ $comment->id }})">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                                <div class="text-center">
                                    <a href="{{ route('editor.comments.index') }}" class="btn btn-info btn-sm">View All Comments</a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-comments fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">No pending comments.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function refreshStats() {
    fetch('{{ route("api.editor.stats") }}')
        .then(response => response.json())
        .then(data => {
            // Update the stats cards with new data
            location.reload(); // Simple approach - reload the page
        })
        .catch(error => {
            console.error('Error refreshing stats:', error);
            alert('Error refreshing stats');
        });
}

function quickApprove(postId) {
    if (confirm('Are you sure you want to approve this post?')) {
        fetch(`/api/editor/posts/${postId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error approving post');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error approving post');
        });
    }
}

function approveComment(commentId) {
    if (confirm('Approve this comment?')) {
        fetch(`/api/editor/comments/${commentId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error approving comment');
            }
        });
    }
}

function rejectComment(commentId) {
    if (confirm('Reject this comment?')) {
        fetch(`/api/editor/comments/${commentId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error rejecting comment');
            }
        });
    }
}
</script>
@endpush