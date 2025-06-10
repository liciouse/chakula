@extends('layouts.author')

@section('title', 'Create New Article')
@section('page-title', 'Create Article')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create New Article</h1>
                <p class="text-gray-600">Write and publish your article</p>
            </div>
            <a href="{{ route('author.articles.index') }}" 
               class="inline-flex items-center px-4 py-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Articles
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('author.articles.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="space-y-6">
            <!-- Article Content -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Article Content</h2>
                
                <div class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
                               placeholder="Enter article title..."
                               required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slug -->
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('slug') border-red-500 @enderror"
                               placeholder="article-slug (auto-generated if empty)">
                        <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate from title</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">Excerpt</label>
                        <textarea id="excerpt" 
                                  name="excerpt" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('excerpt') border-red-500 @enderror"
                                  placeholder="Brief description of the article...">{{ old('excerpt') }}</textarea>
                        @error('excerpt')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content -->
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                        <div class="border border-gray-300 rounded-lg overflow-hidden">
                            <!-- Toolbar -->
                            <div class="bg-gray-50 border-b border-gray-300 px-3 py-2">
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="formatText('bold')" class="p-1 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-bold"></i>
                                    </button>
                                    <button type="button" onclick="formatText('italic')" class="p-1 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-italic"></i>
                                    </button>
                                    <button type="button" onclick="formatText('underline')" class="p-1 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-underline"></i>
                                    </button>
                                    <div class="w-px h-4 bg-gray-300"></div>
                                    <button type="button" onclick="formatText('insertUnorderedList')" class="p-1 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-list-ul"></i>
                                    </button>
                                    <button type="button" onclick="formatText('insertOrderedList')" class="p-1 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-list-ol"></i>
                                    </button>
                                    <div class="w-px h-4 bg-gray-300"></div>
                                    <button type="button" onclick="insertLink()" class="p-1 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Editor -->
                            <div id="editor" 
                                 contenteditable="true"
                                 class="min-h-96 p-4 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                 style="max-height: 500px; overflow-y: auto;">
                                {!! old('content') !!}
                            </div>
                        </div>
                        <textarea id="content" name="content" class="hidden" required>{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Article Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Article Settings</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select id="category_id" 
                                name="category_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category_id') border-red-500 @enderror">
                            <option value="">Select a category</option>
                            <option value="1" {{ old('category_id') == '1' ? 'selected' : '' }}>Technology</option>
                            <option value="2" {{ old('category_id') == '2' ? 'selected' : '' }}>Business</option>
                            <option value="3" {{ old('category_id') == '3' ? 'selected' : '' }}>Health</option>
                            <option value="4" {{ old('category_id') == '4' ? 'selected' : '' }}>Lifestyle</option>
                            <option value="5" {{ old('category_id') == '5' ? 'selected' : '' }}>Education</option>
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tags -->
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                        <input type="text" 
                               id="tags" 
                               name="tags" 
                               value="{{ old('tags') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="technology, programming, web development">
                        <p class="mt-1 text-xs text-gray-500">Separate tags with commas</p>
                    </div>

                    <!-- Featured Image -->
                    <div>
                        <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-2">Featured Image</label>
                        <input type="file" 
                               id="featured_image" 
                               name="featured_image"
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('featured_image') border-red-500 @enderror">
                        @error('featured_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" 
                                name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Submit for Review</option>
                        </select>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-md font-medium text-gray-900 mb-4">SEO Settings</h3>
                    
                    <div class="space-y-4">
                        <!-- Meta Description -->
                        <div>
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                            <textarea id="meta_description" 
                                      name="meta_description" 
                                      rows="2"
                                      maxlength="160"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Brief description for search engines (max 160 characters)">{{ old('meta_description') }}</textarea>
                            <div class="flex justify-between mt-1">
                                <p class="text-xs text-gray-500">Recommended: 120-160 characters</p>
                                <p class="text-xs text-gray-500"><span id="meta-count">0</span>/160</p>
                            </div>
                        </div>

                        <!-- Meta Keywords -->
                        <div>
                            <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                            <input type="text" 
                                   id="meta_keywords" 
                                   name="meta_keywords" 
                                   value="{{ old('meta_keywords') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="keyword1, keyword2, keyword3">
                            <p class="mt-1 text-xs text-gray-500">Separate keywords with commas</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button type="submit" 
                                name="action" 
                                value="save"
                                class="inline-flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Save Article
                        </button>
                        
                        <button type="submit" 
                                name="action" 
                                value="preview"
                                class="inline-flex items-center px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-eye mr-2"></i>
                            Save & Preview
                        </button>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <button type="button" 
                                onclick="saveDraft()"
                                class="text-gray-600 hover:text-gray-800">
                            <i class="fas fa-file-alt mr-1"></i>
                            Save as Draft
                        </button>
                        
                        <a href="{{ route('author.articles.index') }}" 
                           class="text-gray-600 hover:text-gray-800">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function() {
        const title = this.value;
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        document.getElementById('slug').value = slug;
    });

    // Meta description character counter
    document.getElementById('meta_description').addEventListener('input', function() {
        document.getElementById('meta-count').textContent = this.value.length;
    });

    // Rich text editor functionality
    function formatText(command) {
        document.execCommand(command, false, null);
        updateContent();
    }

    function insertLink() {
        const url = prompt('Enter URL:');
        if (url) {
            document.execCommand('createLink', false, url);
            updateContent();
        }
    }

    function updateContent() {
        document.getElementById('content').value = document.getElementById('editor').innerHTML;
    }

    // Update hidden textarea when editor content changes
    document.getElementById('editor').addEventListener('input', updateContent);

    // Save draft functionality
    function saveDraft() {
        document.getElementById('status').value = 'draft';
        document.querySelector('form').submit();
    }

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('editor').innerHTML.trim();
        
        if (!title) {
            alert('Please enter a title for your article.');
            e.preventDefault();
            return;
        }
        
        if (!content || content === '<br>' || content === '<div><br></div>') {
            alert('Please enter content for your article.');
            e.preventDefault();
            return;
        }
        
        updateContent();
    });
</script>
@endsection