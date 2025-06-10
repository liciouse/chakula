@extends('layouts.author')

@section('title', 'My Articles')
@section('page-title', 'Articles')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">My Articles</h1>
            <p class="text-gray-600">Manage your articles and track their status</p>
        </div>
        <a href="{{ route('author.articles.create') }}" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            New Article
        </a>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <!-- Search -->
            <div class="flex-1 max-w-md">
                <form method="GET" action="{{ route('author.articles.index') }}" id="filterForm">
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Search articles..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="hidden" name="status" id="statusInput" value="{{ request('status') }}">
                    </div>
                </form>
            </div>

            <!-- Status Filter -->
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Filter by status:</label>
                <select id="statusFilter" 
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6">
                @php
                    $statuses = ['' => 'All Articles', 'published' => 'Published', 'pending' => 'Pending', 'draft' => 'Drafts'];
                @endphp
                @foreach($statuses as $key => $label)
                    <a href="{{ route('author.articles.index', ['status' => $key ?: null]) }}"
                       class="py-4 px-1 border-b-2 font-medium text-sm {{ request('status') === $key || (!request('status') && $key === '') ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $label }}
                        <span class="ml-2 bg-gray-100 text-gray-600 py-1 px-2 rounded-full text-xs">
                            {{ $key ? auth()->user()->posts()->where('status', $key)->count() : $articles->total() }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Articles List -->
        <div class="p-6">
            @if($articles->count() > 0)
                <div class="space-y-4">
                    @foreach($articles as $article)
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <a href="{{ route('author.articles.show', $article) }}" 
                                               class="hover:text-blue-600">
                                                {{ $article->title }}
                                            </a>
                                        </h3>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            {{ $article->status === 'published' ? 'bg-green-100 text-green-800' : 
                                               ($article->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($article->status) }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-3">
                                        {{ Str::limit($article->excerpt ?? $article->content, 150) }}
                                    </p>
                                    
                                    <div class="flex items-center text-sm text-gray-500 space-x-4">
                                        <span><i class="fas fa-calendar mr-1"></i>{{ $article->created_at->format('M d, Y') }}</span>
                                        <span><i class="fas fa-comments mr-1"></i>{{ $article->comments_count ?? 0 }} comments</span>
                                        <span><i class="fas fa-eye mr-1"></i>{{ rand(50, 500) }} views</span>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4">
                                    <a href="{{ route('author.articles.edit', $article) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="confirmDelete({{ $article->id }})" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <div class="relative">
                                        <button onclick="toggleDropdown({{ $article->id }})" class="text-gray-600 hover:text-gray-800">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div id="dropdown-{{ $article->id }}" 
                                             class="absolute right-0 top-8 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10 hidden">
                                            <div class="py-1">
                                                <a href="{{ route('author.articles.show', $article) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-eye mr-2"></i>View</a>
                                                <a href="{{ route('author.articles.edit', $article) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-edit mr-2"></i>Edit</a>
                                                <button onclick="duplicateArticle({{ $article->id }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-copy mr-2"></i>Duplicate</button>
                                                <hr class="my-1">
                                                <button onclick="confirmDelete({{ $article->id }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"><i class="fas fa-trash mr-2"></i>Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $articles->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-newspaper text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No articles found</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request('search'))
                            No articles match your search criteria.
                        @else
                            You haven't written any articles yet. Create your first one!
                        @endif
                    </p>
                    <a href="{{ route('author.articles.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Create Your First Article
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center mb-4">
                <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
                <h3 class="text-lg font-semibold text-gray-900">Delete Article</h3>
            </div>
            <p class="text-gray-600 mb-6">Are you sure you want to delete this article? This action cannot be undone.</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
@push('scripts')
<script>
    const toggleDropdown = (id) => {
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
        const dropdown = document.getElementById(`dropdown-${id}`);
        dropdown.classList.toggle('hidden');
    };

    const confirmDelete = (id) => {
        document.getElementById('deleteForm').action = `/author/articles/${id}`;
        document.getElementById('deleteModal').classList.remove('hidden');
    };

    const closeDeleteModal = () => {
        document.getElementById('deleteModal').classList.add('hidden');
    };

    const duplicateArticle = (id) => {
        // You can implement duplication logic here or redirect to a route
        alert('Duplicate logic not implemented yet for article #' + id);
    };

    document.getElementById('statusFilter').addEventListener('change', function () {
        const status = this.value;
        document.getElementById('statusInput').value = status;
        document.getElementById('filterForm').submit();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('[id^="dropdown-"]') && !e.target.closest('[onclick^="toggleDropdown"]')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
        }
    });
</script>
@endpush

@endsection
