@extends('layouts.author')

@section('title', 'Manage Comments')
@section('page-title', 'Comments')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Comments</h1>
            <p class="text-gray-600">Manage comments on your articles</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="toggleBulkActions()" 
                    id="bulkToggle"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-check-square mr-2"></i>
                Bulk Actions
            </button>
            <button onclick="markAllAsRead()" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-check-double mr-2"></i>
                Mark All Read
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <form method="GET" action="{{ route('author.comments.index') }}">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search comments..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('article'))
                            <input type="hidden" name="article" value="{{ request('article') }}">
                        @endif
                    </div>
                </form>
            </div>

            <!-- Filters -->
            <div class="flex items-center space-x-4">
                <select id="statusFilter" 
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="spam" {{ request('status') === 'spam' ? 'selected' : '' }}>Spam</option>
                </select>

                <select id="articleFilter" 
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Articles</option>
                    @foreach(auth()->user()->posts()->withCount('comments')->get() as $post)
                        <option value="{{ $post->id }}" {{ request('article') == $post->id ? 'selected' : '' }}>
                            {{ Str::limit($post->title, 30) }} ({{ $post->comments_count }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Comments Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-comments text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Comments</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $comments->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Approved</h3>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $comments->where('status', 'approved')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Pending</h3>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $comments->where('status', 'pending')->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Spam</h3>
                    <p class="text-2xl font-bold text-gray-900">
                        {{ $comments->where('status', 'spam')->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" 
         class="bg-blue-50 border border-blue-200 rounded-lg p-4 hidden">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           id="selectAll" 
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Select All</span>
                </label>
                <span id="selectedCount" class="text-sm text-gray-600">0 selected</span>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="bulkAction('approve')" 
                        class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                    <i class="fas fa-check mr-1"></i>Approve
                </button>
                <button onclick="bulkAction('pending')" 
                        class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    <i class="fas fa-clock mr-1"></i>Pending
                </button>
                <button onclick="bulkAction('spam')" 
                        class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                    <i class="fas fa-ban mr-1"></i>Spam
                </button>
                <button onclick="bulkAction('delete')" 
                        class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">
                    <i class="fas fa-trash mr-1"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Comments List -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            @if($comments->count() > 0)
                <div class="space-y-4">
                    @foreach($comments as $comment)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-4">
                                <!-- Checkbox -->
                                <div class="bulk-checkbox hidden">
                                    <input type="checkbox" 
                                           class="comment-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                           value="{{ $comment->id }}">
                                </div>

                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                </div>

                                <!-- Comment Content -->
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="font-medium text-gray-900">{{ $comment->author_name ?? 'Anonymous' }}</h4>
                                            <span class="text-sm text-gray-500">{{ $comment->author_email }}</span>
                                            <span class="px-2 py-1 text-xs rounded-full 
                                                {{ $comment->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                   ($comment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                    'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($comment->status) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                            <div class="relative">
                                                <button onclick="toggleDropdown({{ $comment->id }})" 
                                                        class="p-1 text-gray-400 hover:text-gray-600">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div id="dropdown-{{ $comment->id }}" 
                                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border hidden z-10">
                                                    <div class="py-2">
                                                        @if($comment->status !== 'approved')
                                                            <button onclick="updateCommentStatus({{ $comment->id }}, 'approved')"
                                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                <i class="fas fa-check mr-2 text-green-600"></i>Approve
                                                            </button>
                                                        @endif
                                                        @if($comment->status !== 'pending')
                                                            <button onclick="updateCommentStatus({{ $comment->id }}, 'pending')"
                                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                <i class="fas fa-clock mr-2 text-yellow-600"></i>Mark Pending
                                                            </button>
                                                        @endif
                                                        @if($comment->status !== 'spam')
                                                            <button onclick="updateCommentStatus({{ $comment->id }}, 'spam')"
                                                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                <i class="fas fa-ban mr-2 text-red-600"></i>Mark as Spam
                                                            </button>
                                                        @endif
                                                        <div class="border-t border-gray-100 my-1"></div>
                                                        <button onclick="replyToComment({{ $comment->id }})"
                                                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                            <i class="fas fa-reply mr-2 text-blue-600"></i>Reply
                                                        </button>
                                                        <button onclick="deleteComment({{ $comment->id }})"
                                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                            <i class="fas fa-trash mr-2"></i>Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Comment Text -->
                                    <div class="mb-3">
                                        <p class="text-gray-700">{{ $comment->content }}</p>
                                    </div>

                                    <!-- Article Reference -->
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-newspaper mr-2"></i>
                                        <span>On: </span>
                                        <a href="{{ route('posts.show', $comment->post->slug) }}" 
                                           class="ml-1 text-blue-600 hover:text-blue-800">
                                            {{ Str::limit($comment->post->title, 50) }}
                                        </a>
                                    </div>

                                    <!-- Reply Form -->
                                    <div id="reply-form-{{ $comment->id }}" class="mt-4 hidden">
                                        <form onsubmit="submitReply(event, {{ $comment->id }})">
                                            <div class="bg-gray-50 rounded-lg p-4">
                                                <textarea name="reply_content" 
                                                          placeholder="Write your reply..."
                                                          class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                          rows="3"></textarea>
                                                <div class="flex justify-end space-x-2 mt-3">
                                                    <button type="button" 
                                                            onclick="cancelReply({{ $comment->id }})"
                                                            class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                                        Cancel
                                                    </button>
                                                    <button type="submit"
                                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                                        Reply
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $comments->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-comments text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No comments found</h3>
                    <p class="text-gray-500">
                        @if(request('search') || request('status') || request('article'))
                            Try adjusting your filters to see more comments.
                        @else
                            Comments on your articles will appear here.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
let bulkActionsVisible = false;

function toggleBulkActions() {
    const bar = document.getElementById('bulkActionsBar');
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    const toggle = document.getElementById('bulkToggle');
    
    bulkActionsVisible = !bulkActionsVisible;
    
    if (bulkActionsVisible) {
        bar.classList.remove('hidden');
        checkboxes.forEach(cb => cb.classList.remove('hidden'));
        toggle.classList.add('bg-blue-600', 'text-white');
        toggle.classList.remove('bg-gray-100', 'text-gray-700');
    } else {
        bar.classList.add('hidden');
        checkboxes.forEach(cb => cb.classList.add('hidden'));
        toggle.classList.remove('bg-blue-600', 'text-white');
        toggle.classList.add('bg-gray-100', 'text-gray-700');
        document.getElementById('selectAll').checked = false;
        updateSelectedCount();
    }
}

function updateSelectedCount() {
    const checked = document.querySelectorAll('.comment-checkbox:checked');
    document.getElementById('selectedCount').textContent = `${checked.length} selected`;
}

// Select all functionality
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.comment-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateSelectedCount();
    });

    // Individual checkbox change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('comment-checkbox')) {
            updateSelectedCount();
        }
    });

    // Filter changes
    document.getElementById('statusFilter').addEventListener('change', function() {
        updateFilters();
    });

    document.getElementById('articleFilter').addEventListener('change', function() {
        updateFilters();
    });
});

function updateFilters() {
    const status = document.getElementById('statusFilter').value;
    const article = document.getElementById('articleFilter').value;
    const search = document.querySelector('input[name="search"]').value;
    
    let url = new URL(window.location.href);
    url.searchParams.delete('status');
    url.searchParams.delete('article');
    url.searchParams.delete('search');
    
    if (status) url.searchParams.set('status', status);
    if (article) url.searchParams.set('article', article);
    if (search) url.searchParams.set('search', search);
    
    window.location.href = url.toString();
}

function toggleDropdown(commentId) {
    document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
        if (dropdown.id !== `dropdown-${commentId}`) {
            dropdown.classList.add('hidden');
        }
    });
    
    const dropdown = document.getElementById(`dropdown-${commentId}`);
    dropdown.classList.toggle('hidden');
}

function updateCommentStatus(commentId, status) {
    fetch(`/author/comments/${commentId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function bulkAction(action) {
    const selected = Array.from(document.querySelectorAll('.comment-checkbox:checked')).map(cb => cb.value);
    
    if (selected.length === 0) {
        alert('Please select comments first');
        return;
    }

    if (action === 'delete' && !confirm('Are you sure you want to delete selected comments?')) {
        return;
    }

    fetch('/author/comments/bulk-action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: action,
            comment_ids: selected
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function replyToComment(commentId) {
    document.getElementById(`reply-form-${commentId}`).classList.remove('hidden');
    document.getElementById(`dropdown-${commentId}`).classList.add('hidden');
}

function cancelReply(commentId) {
    document.getElementById(`reply-form-${commentId}`).classList.add('hidden');
}

function submitReply(event, commentId) {
    event.preventDefault();
    const form = event.target;
    const content = form.reply_content.value;

    fetch(`/author/comments/${commentId}/reply`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ content: content })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;

    fetch(`/author/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markAllAsRead() {
    fetch('/author/comments/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});
</script>
@endsection