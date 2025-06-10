@extends('layouts.author')

@section('title', 'Edit Article')
@section('page-title', 'Edit Article')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('author.articles.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Articles
                </a>
                <div class="text-sm text-gray-500">
                    Last saved: <span id="last-saved">{{ $post->updated_at->format('g:i A') }}</span>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button type="button" id="auto-save-btn" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm">
                    Auto-save: <span id="auto-save-status">On</span>
                </button>
                <a href="{{ route('posts.show', $post->slug) }}" target="_blank" 
                   class="inline-flex items-center px-4 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('author.articles.update', $post) }}" method="POST" enctype="multipart/form-data" id="article-form">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Article Title -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Article Title</label>
                        <input type="text" 
                               name="title" 
                               id="title"
                               value="{{ old('title', $post->title) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg font-medium"
                               placeholder="Enter your article title..."
                               required>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug Preview -->
                    <div class="text-sm text-gray-500">
                        URL: <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ url('/') }}/posts/<span id="slug-preview">{{ $post->slug }}</span></span>
                    </div>
                </div>

                <!-- Article Content -->
                <div class="bg-white rounded-lg shadow p-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-4">Article Content</label>
                    <div id="editor-container" class="min-h-96 border border-gray-300 rounded-lg">
                        <textarea name="content" id="content" class="hidden">{{ old('content', $post->content) }}</textarea>
                    </div>
                    @error('content')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Article Excerpt -->
                <div class="bg-white rounded-lg shadow p-6">
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Excerpt (Optional)</label>
                    <textarea name="excerpt" 
                              id="excerpt" 
                              rows="3"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                              placeholder="Brief description of your article...">{{ old('excerpt', $post->excerpt) }}</textarea>
                    <div class="mt-2 text-sm text-gray-500">
                        <span id="excerpt-count">{{ strlen($post->excerpt ?? '') }}</span>/500 characters
                    </div>
                    @error('excerpt')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SEO Settings -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">SEO Settings</h3>
                        <button type="button" onclick="toggleSeoSettings()" class="text-blue-600 hover:text-blue-700 text-sm">
                            <span id="seo-toggle-text">Show</span> SEO Options
                        </button>
                    </div>
                    
                    <div id="seo-settings" class="hidden space-y-4">
                        <div>
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">SEO Title</label>
                            <input type="text" 
                                   name="meta_title" 
                                   id="meta_title"
                                   value="{{ old('meta_title', $post->meta_title) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="SEO optimized title...">
                            <div class="mt-1 text-sm text-gray-500">
                                <span id="meta-title-count">{{ strlen($post->meta_title ?? '') }}</span>/60 characters recommended
                            </div>
                        </div>

                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                            <textarea name="meta_description" 
                                      id="meta_description" 
                                      rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Brief description for search engines...">{{ old('meta_description', $post->meta_description) }}</textarea>
                            <div class="mt-1 text-sm text-gray-500">
                                <span id="meta-desc-count">{{ strlen($post->meta_description ?? '') }}</span>/160 characters recommended
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish Settings -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Publish Settings</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="draft" {{ old('status', $post->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $post->status) === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>

                        <div>
                            <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">Publish Date</label>
                            <input type="datetime-local" 
                                   name="published_at" 
                                   id="published_at"
                                   value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-sm text-gray-500">Leave empty to publish immediately</p>
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select name="category_id" id="category_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <option value="">Select a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $post->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                            <input type="text" 
                                   name="tags" 
                                   id="tags"
                                   value="{{ old('tags', $post->tags->pluck('name')->implode(', ')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="technology, programming, web">
                            <p class="mt-1 text-sm text-gray-500">Separate tags with commas</p>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Featured Image</h3>
                    
                    <div class="space-y-4">
                        @if($post->featured_image)
                            <div class="relative">
                                <img src="{{ Storage::url($post->featured_image) }}" 
                                     alt="Current featured image" 
                                     class="w-full h-40 object-cover rounded-lg">
                                <button type="button" 
                                        onclick="removeFeaturedImage()"
                                        class="absolute top-2 right-2 bg-red-600 text-white p-1 rounded-full hover:bg-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                        
                        <div>
                            <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $post->featured_image ? 'Change Image' : 'Upload Image' }}
                            </label>
                            <input type="file" 
                                   name="featured_image" 
                                   id="featured_image"
                                   accept="image/*"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-sm text-gray-500">Max size: 2MB. Formats: JPG, PNG, GIF</p>
                            @error('featured_image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Article Stats -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Article Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Views:</span>
                            <span class="font-medium">{{ number_format($post->views ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Comments:</span>
                            <span class="font-medium">{{ $post->comments_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Likes:</span>
                            <span class="font-medium">{{ $post->likes_count ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Created:</span>
                            <span class="font-medium">{{ $post->created_at->format('M j, Y') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Last Updated:</span>
                            <span class="font-medium">{{ $post->updated_at->format('M j, Y g:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="space-y-3">
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            Update Article
                        </button>
                        
                        <a href="{{ route('author.articles.index') }}" 
                           class="w-full inline-flex justify-center items-center px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                            Cancel
                        </a>
                        
                        <button type="button" 
                                onclick="confirmDelete({{ $post->id }})"
                                class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                            Delete Article
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript for enhanced functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-save functionality
    let autoSaveEnabled = true;
    let saveTimeout;
    
    function autoSave() {
        if (!autoSaveEnabled) return;
        
        const formData = new FormData(document.getElementById('article-form'));
        
        fetch('{{ route("author.articles.autosave", $post) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('last-saved').textContent = new Date().toLocaleTimeString();
            }
        });
    }
    
    // Auto-save on form changes
    document.getElementById('article-form').addEventListener('input', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 2000);
    });
    
    // Toggle auto-save
    document.getElementById('auto-save-btn').addEventListener('click', function() {
        autoSaveEnabled = !autoSaveEnabled;
        document.getElementById('auto-save-status').textContent = autoSaveEnabled ? 'On' : 'Off';
        this.className = autoSaveEnabled ? 
            'px-4 py-2 bg-blue-100 text-blue-700 rounded-lg text-sm' : 
            'px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm';
    });
    
    // Character counters
    const excerptTextarea = document.getElementById('excerpt');
    const excerptCounter = document.getElementById('excerpt-count');
    
    excerptTextarea.addEventListener('input', function() {
        excerptCounter.textContent = this.value.length;
        if (this.value.length > 500) {
            excerptCounter.className = 'text-red-600 font-medium';
        } else {
            excerptCounter.className = '';
        }
    });
    
    // SEO character counters
    const metaTitle = document.getElementById('meta_title');
    const metaTitleCounter = document.getElementById('meta-title-count');
    
    metaTitle.addEventListener('input', function() {
        metaTitleCounter.textContent = this.value.length;
        if (this.value.length > 60) {
            metaTitleCounter.className = 'text-red-600 font-medium';
        } else {
            metaTitleCounter.className = '';
        }
    });
    
    const metaDescription = document.getElementById('meta_description');
    const metaDescCounter = document.getElementById('meta-desc-count');
    
    metaDescription.addEventListener('input', function() {
        metaDescCounter.textContent = this.value.length;
        if (this.value.length > 160) {
            metaDescCounter.className = 'text-red-600 font-medium';
        } else {
            metaDescCounter.className = '';
        }
    });
    
    // Slug preview
    document.getElementById('title').addEventListener('input', function() {
        const slug = this.value.toLowerCase()
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        document.getElementById('slug-preview').textContent = slug || 'article-title';
    });
});

// SEO settings toggle
function toggleSeoSettings() {
    const seoSettings = document.getElementById('seo-settings');
    const toggleText = document.getElementById('seo-toggle-text');
    
    if (seoSettings.classList.contains('hidden')) {
        seoSettings.classList.remove('hidden');
        toggleText.textContent = 'Hide';
    } else {
        seoSettings.classList.add('hidden');
        toggleText.textContent = 'Show';
    }
}

// Featured image removal
function removeFeaturedImage() {
    if (confirm('Are you sure you want to remove the featured image?')) {
        fetch('{{ route("author.articles.remove-image", $post) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Delete confirmation
function confirmDelete(postId) {
    if (confirm('Are you sure you want to delete this article? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("author.articles.destroy", $post) }}';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

@endsection