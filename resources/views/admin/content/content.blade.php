@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="row">
        <!-- Posts Section -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span>Blog Posts ({{ $posts->total() }})</span>
                    <a href="{{ route('admin.content.create') }}" class="btn btn-sm btn-light">Add New</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($posts as $post)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-1">{{ $post->title }}</h6>
                                    <small class="text-muted">
                                        By {{ $post->user->name }} | 
                                        {{ $post->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="btn-group">
                                    <a href="{{ route('admin.content.edit', $post->id) }}" 
                                       class="btn btn-sm btn-outline-primary">Edit</a>
                                    <button class="btn btn-sm btn-outline-danger delete-post" 
                                            data-id="{{ $post->id }}">Delete</button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            No posts found
                        </div>
                        @endforelse
                    </div>
                </div>
                @if($posts->hasPages())
                <div class="card-footer">
                    {{ $posts->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- Comments Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    Recent Comments ({{ $comments->total() }})
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($comments as $comment)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="mb-1">{{ Str::limit($comment->content, 60) }}</p>
                                    <small class="text-muted">
                                        On: <a href="{{ route('posts.show', $comment->post_id) }}">
                                            {{ $comment->post->title }}
                                        </a><br>
                                        By: {{ $comment->user->name }} | 
                                        {{ $comment->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-danger delete-comment" 
                                            data-id="{{ $comment->id }}">Delete</button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-4">
                            No comments found
                        </div>
                        @endforelse
                    </div>
                </div>
                @if($comments->hasPages())
                <div class="card-footer">
                    {{ $comments->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle post deletion
    $('.delete-post').click(function() {
        if(confirm('Are you sure you want to delete this post?')) {
            const postId = $(this).data('id');
            $.ajax({
                url: "{{ route('admin.content.destroy', '') }}/" + postId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    window.location.reload();
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        }
    });

    // Handle comment deletion
    $('.delete-comment').click(function() {
        if(confirm('Are you sure you want to delete this comment?')) {
            const commentId = $(this).data('id');
            $.ajax({
                url: "{{ route('admin.content.comments.destroy', '') }}/" + commentId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    window.location.reload();
                },
                error: function(xhr) {
                    alert('Error: ' + xhr.responseJSON.message);
                }
            });
        }
    });
});
</script>
@endpush
@endsection