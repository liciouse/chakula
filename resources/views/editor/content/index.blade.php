@extends('layouts.editor')

@section('title', 'Manage Content')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Content Management</h1>
                <div class="btn-group">
                    <a href="{{ route('editor.content.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Post
                    </a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                        <i class="fas fa-tasks"></i> Bulk Actions
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('editor.content.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select name="category" id="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" name="search" id="search" class="form-control" placeholder="Search posts..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Posts Table -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Posts ({{ $posts->total() }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($posts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Post</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Views</th>
                                        <th>Date</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($posts as $post)
                                    <tr data-post-id="{{ $post->id }}">
                                        <td>
                                            <input type="checkbox" name="selected_posts[]" value="{{ $post->id }}" class="form-check-input post-checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                @if($post->featured_image)
                                                    <img src="{{ asset($post->featured_image) }}" alt="{{ $post->title }}" class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                                @endif
                                                <div>
                                                    <h6 class="mb-1">{{ Str::limit($post->title, 50) }}</h6>
                                                    <small class="text-muted">{{ Str::limit(strip_tags($post->excerpt), 80) }}</small>
                                                    @if($post->tags)
                                                        <div class="mt-1">
                                                            @foreach(is_array($post->tags) ? $post->tags : explode(',', $post->tags) as $tag)
                                                                <span class="badge bg-light text-dark me-1">{{ trim($tag) }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    @if($post->author->avatar)
                                                        <img src="{{ asset($post->author->avatar) }}" alt="{{ $post->author->name }}" class="rounded-circle" width="32" height="32">
                                                    @else
                                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px;">
                                                            {{ substr($post->author->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $post->author->name }}</div>
                                                    <small class="text-muted">{{ $post->author->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $post->category->name ?? 'No Category' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $post->status_badge }}">{{ ucfirst($post->status) }}</span>
                                            @if($post->is_featured)
                                                <span class="badge bg-warning ms-1">Featured</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                <i class="fas fa-eye"></i> {{ number_format($post->view_count) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-muted">
                                                <small>{{ $post->created_at->format('M j, Y') }}</small>
                                                @if($post->published_at)
                                                    <br><small class="text-success">Published: {{ $post->published_at->format('M j, Y') }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('editor.content.show', $post) }}" class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('editor.content.edit', $post) }}" class="btn btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($post->status === 'pending')
                                                    <button type="button" class="btn btn-outline-success" onclick="quickApprove({{ $post->id }})" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" onclick="quickReject({{ $post->id }})" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-outline-danger" onclick="deletePost({{ $post->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <div class="text-muted">
                                Showing {{ $posts->firstItem() }} to {{ $posts->lastItem() }} of {{ $posts->total() }} results
                            </div>
                            {{ $posts->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No posts found</h5>
                            <p class="text-muted">No posts match your current filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionModalLabel">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkActionForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Selected Posts: <span id="selected-count">0</span></label>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bulk_action" class="form-label">Action</label>
                        <select class="form-select" id="bulk_action" name="action" required>
                            <option value="">Select Action</option>
                            <option value="approve">Approve Posts</option>
                            <option value="reject">Reject Posts</option>
                            <option value="publish">Publish Posts</option>
                            <option value="draft">Move to Draft</option>
                            <option value="delete">Delete Posts</option>
                            <option value="feature">Mark as Featured</option>
                            <option value="unfeature">Remove Featured</option>
                        </select>
                    </div>
                    
                    <div id="rejection-reason" class="mb-3 d-none">
                        <label for="reason" class="form-label">Rejection Reason</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="Enter reason for rejection..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action will be applied to all selected posts.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Approve Modal -->
<div class="modal fade" id="quickApproveModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this post?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmApprove">Approve</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Reject Modal -->
<div class="modal fade" id="quickRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reject_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this post? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let selectedPosts = [];
    let currentPostId = null;
    
    // Select All functionality
    $('#selectAll').on('change', function() {
        let isChecked = $(this).is(':checked');
        $('.post-checkbox').prop('checked', isChecked);
        updateSelectedPosts();
    });
    
    // Individual checkbox functionality
    $('.post-checkbox').on('change', function() {
        updateSelectedPosts();
        
        // Update select all checkbox
        let totalCheckboxes = $('.post-checkbox').length;
        let checkedCheckboxes = $('.post-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
    
    // Update selected posts array
    function updateSelectedPosts() {
        selectedPosts = [];
        $('.post-checkbox:checked').each(function() {
            selectedPosts.push($(this).val());
        });
        $('#selected-count').text(selectedPosts.length);
    }
    
    // Bulk action form handling
    $('#bulk_action').on('change', function() {
        if ($(this).val() === 'reject') {
            $('#rejection-reason').removeClass('d-none');
            $('#reason').attr('required', true);
        } else {
            $('#rejection-reason').addClass('d-none');
            $('#reason').attr('required', false);
        }
    });
    
    // Bulk action form submission
    $('#bulkActionForm').on('submit', function(e) {
        e.preventDefault();
        
        if (selectedPosts.length === 0) {
            showToast('warning', 'Please select at least one post');
            return;
        }
        
        let action = $('#bulk_action').val();
        let reason = $('#reason').val();
        
        if (action === 'delete' && !confirm('Are you sure you want to delete the selected posts? This action cannot be undone.')) {
            return;
        }
        
        $.ajax({
            url: '{{ route("editor.content.bulk-action") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                posts: selectedPosts,
                action: action,
                reason: reason
            },
            success: function(response) {
                showToast('success', response.message);
                location.reload();
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'An error occurred');
            }
        });
    });
    
    // Quick approve functionality
    window.quickApprove = function(postId) {
        currentPostId = postId;
        $('#quickApproveModal').modal('show');
    };
    
    $('#confirmApprove').on('click', function() {
        $.ajax({
            url: '{{ route("editor.content.approve", ":id") }}'.replace(':id', currentPostId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast('success', 'Post approved successfully');
                location.reload();
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'An error occurred');
            }
        });
    });
    
    // Quick reject functionality
    window.quickReject = function(postId) {
        currentPostId = postId;
        $('#quickRejectModal').modal('show');
    };
    
    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("editor.content.reject", ":id") }}'.replace(':id', currentPostId),
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reason: $('#reject_reason').val()
            },
            success: function(response) {
                showToast('success', 'Post rejected successfully');
                location.reload();
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'An error occurred');
            }
        });
    });
    
    // Delete functionality
    window.deletePost = function(postId) {
        currentPostId = postId;
        $('#deleteModal').modal('show');
    };
    
    $('#confirmDelete').on('click', function() {
        $.ajax({
            url: '{{ route("editor.content.destroy", ":id") }}'.replace(':id', currentPostId),
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                showToast('success', 'Post deleted successfully');
                location.reload();
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'An error occurred');
            }
        });
    });
    
    // Auto-refresh every 5 minutes to check for new posts
    setInterval(function() {
        let currentUrl = window.location.href;
        $.get(currentUrl, function(data) {
            let newCount = $(data).find('.card-header h5').text().match(/\d+/);
            let currentCount = $('.card-header h5').text().match(/\d+/);
            
            if (newCount && currentCount && newCount[0] !== currentCount[0]) {
                showToast('info', 'New posts available. <a href="#" onclick="location.reload()">Refresh</a>');
            }
        });
    }, 300000); // 5 minutes
});

// Toast notification function
function showToast(type, message) {
    let bgClass = type === 'success' ? 'bg-success' : 
                  type === 'error' ? 'bg-danger' : 
                  type === 'warning' ? 'bg-warning' : 'bg-info';
    
    let toast = `
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    if (!$('.toast-container').length) {
        $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
    }
    
    $('.toast-container').append(toast);
    $('.toast:last').toast('show');
}
</script>
@endpush