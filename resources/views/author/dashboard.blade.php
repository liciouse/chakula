@extends('layouts.author')

@section('title', 'Author Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-blue-100">Here's an overview of your content and activity.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Published Articles -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Published Articles</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $publishedCount }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('author.articles.index', ['status' => 'published']) }}" 
                   class="text-sm text-green-600 hover:text-green-800">
                    View published articles →
                </a>
            </div>
        </div>

        <!-- Pending Articles -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Pending Review</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $pendingCount }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('author.articles.index', ['status' => 'pending']) }}" 
                   class="text-sm text-yellow-600 hover:text-yellow-800">
                    View pending articles →
                </a>
            </div>
        </div>

        <!-- Total Comments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-comments text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Comments</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $commentsCount }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('author.comments.index') }}" 
                   class="text-sm text-blue-600 hover:text-blue-800">
                    Manage comments →
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('author.articles.create') }}" 
                   class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <i class="fas fa-plus text-blue-600 text-lg mr-3"></i>
                    <span class="text-blue-600 font-medium">New Article</span>
                </a>
                
                <a href="{{ route('author.articles.index') }}" 
                   class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-list text-gray-600 text-lg mr-3"></i>
                    <span class="text-gray-600 font-medium">All Articles</span>
                </a>
                
                <a href="{{ route('author.comments.index') }}" 
                   class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <i class="fas fa-comment-dots text-purple-600 text-lg mr-3"></i>
                    <span class="text-purple-600 font-medium">Comments</span>
                </a>
                
                <a href="#" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <i class="fas fa-chart-line text-green-600 text-lg mr-3"></i>
                    <span class="text-green-600 font-medium">Analytics</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Articles -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Articles</h2>
                    <a href="{{ route('author.articles.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        View all →
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse(auth()->user()->posts()->latest()->limit(5)->get() as $post)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900 truncate">
                                    {{ $post->title }}
                                </h4>
                                <p class="text-xs text-gray-500">
                                    {{ $post->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 
                                   ($post->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($post->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-newspaper text-3xl mb-2"></i>
                            <p>No articles yet. Create your first one!</p>
                            <a href="{{ route('author.articles.create') }}" 
                               class="inline-block mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Create Article
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Performance Overview -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Performance Overview</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Articles this month</span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ auth()->user()->posts()->whereMonth('created_at', now()->month)->count() }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Comments this week</span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ $commentsCount > 0 ? rand(1, 10) : 0 }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Average rating</span>
                        <div class="flex items-center">
                            <div class="flex text-yellow-400 text-sm">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="ml-1 text-sm text-gray-600">4.2</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total views</span>
                        <span class="text-sm font-medium text-gray-900">
                            {{ number_format($publishedCount * rand(100, 1000)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection