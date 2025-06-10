@extends('layouts.editor')

@section('title', isset($post) ? 'Edit Post' : 'Create Post')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <form action="{{ isset($post) ? route('editor.content.update', $post) : route('editor.content.store') }}" method="POST" enctype="multipart/form-data" id="postForm">
                @csrf
                @if(isset($post))
                    @method('PUT')
                @endif
                
                <!-- Main Content Card -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ isset($post) ? 'Edit Post' : 'Create New Post' }}</h5>
                        <div class="btn-group">
                            <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                                <i class="fas fa-save"></i> Save Draft
                            </button>
                            <button type="submit" name="action" value="pending" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Submit for Review
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $post->title ?? '') }}" 
                                   placeholder="Enter post title..." required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Slug: <span id="slug-preview">{{ $post->slug ?? 'will-be-generated-from-title' }}</span>
                            </div>
                        </div>
                        
                        <!-- Excerpt -->
                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" name="excerpt" rows="3" 
                                      placeholder="Brief description of the post...">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Character count: <span id="excerpt-count">0</span>/200
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="15" required>{{ old('content', $post->content ?? '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Post Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Post Status</h6>
                </div>
                <div class="card-body">
                    @if(isset($post))
                        <div class="mb-3">
                            <strong>Current Status:</strong>
                            <span class="badge bg-{{ $post->status_badge }} ms-2">{{ ucfirst($post->status) }}</span>
                        </div>
                        @if($post->status === 'rejected' && $post->rejection_reason)
                            <div class="alert alert-danger">
                                <strong>Rejection Reason:</strong><br>
                                {{ $post->rejection_reason }}
                            </div>
                        @endif
                    @endif
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <i class="fas fa-eye text-muted"></i>
                                <div class="mt-1">
                                    <strong>{{ isset($post) ? number_format($post->view_count) : '0' }}</strong>
                                    <div class="small text-muted">Views</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <i class="fas fa-clock text-muted"></i>
                                <div class="mt-1">
                                    <strong id="reading-time">{{ $post->reading_time ?? 0 }}</strong>
                                    <div class="small text-muted">Min Read</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Category & Tags -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Category & Tags</h6>
                </div>
                <div class="card-body">
                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select select2 @error('category_id') is-invalid @enderror" 
                                id="category_id" name="category_id" form="postForm" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                        {{ old('category_id', $post->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Tags -->
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags</label>
                        <select class="form-select select2" id="tags" name="tags[]" form="postForm" multiple>
                            @if(isset($post) && $post->tags)
                                @foreach(is_array($post->tags) ? $post->tags : json_decode($post->tags, true) as $tag)
                                    <option value="{{ $tag }}" selected>{{ $tag }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="form-text">Press Enter to add new tags</div>
                    </div>
                </div>
            </div>
            
            <!-- Featured Image -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Featured Image</h6>
                </div>
                <div class="card-body">
                    @if(isset($post) && $post->featured_image)
                        <div class="mb-3">
                            <img src="{{ asset($post->featured_image) }}" alt="Current featured image" 
                                 class="img-fluid rounded" id="current-image">
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImage()">
                                    Remove Image
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <input type="file" class="form-control @error('featured_image') is-invalid @enderror" 
                               id="featured_image" name="featured_image" form="postForm" accept="image/*">
                        @error('featured_image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Max size: 2MB. Formats: JPG, PNG, GIF</div>
                    </div>
                    
                    <!-- Image Preview -->
                    <div id="image-preview" class="d-none">
                        <img src="" alt="Preview" class="img-fluid rounded">
                    </div>
                </div>
            </div>
            
            <!-- SEO Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">SEO Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" class="form-control" id="meta_title" name="meta_title" 
                               form="postForm" value="{{ old('meta_title', $post->meta_title ?? '') }}" 
                               maxlength="60">
                        <div class="form-text">
                            <span id="meta-title-count">0</span>/60 characters
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" id="meta_description" name="meta_description" 
                                  form="postForm" rows="3" maxlength="160">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                        <div class="form-text">
                            <span id="meta-desc-count">0</span>/160 characters
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Options -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Additional Options</h6>
                </div>
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_featured" 
                               name="is_featured" form="postForm" value="1"
                               {{ old('is_featured', $post->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            Featured Post
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize TinyMCE
    tinymce.init({
        selector: '#content',
        height: 400,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        menubar: false,
        branding: false,
        setup: function(editor) {
            editor.on('keyup', function() {
                updateReadingTime();
            });
        }
    });
    
    // Initialize Select2 for tags with tagging
    $('#tags').select2({
        tags: true,
        tokenSeparators: [',', ' '],
        theme: 'bootstrap-5',
        placeholder: 'Add tags...'
    });
    
    // Title to slug conversion
    $('#title').on('input', function() {
        let title = $(this).val();
        let slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');
        $('#slug-preview').text(slug || 'will-be-generated-from-title');
    });
    
    // Character counters
    function updateCounter(input, counter, max) {
        $(input).on('input', function() {
            let count = $(this).val().length;
            $(counter).text(count);
            if (count > max * 0.8) {
                $(counter).addClass('text-warning');
            }
            if (count > max) {
                $(counter).addClass('text-danger').removeClass('text-warning');
            } else {
                $(counter).removeClass('text-danger text-warning');
            }
        }).trigger('input');
    }
    
    updateCounter('#excerpt', '#excerpt-count', 200);
    updateCounter('#meta_title', '#meta-title-count', 60);
    updateCounter('#meta_description', '#meta-desc-count', 160);
    
    // Image preview
    $('#featured_image').on('change', function() {
        let file = this.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview img').attr('src', e.target.result);
                $('#image-preview').removeClass('d-none');
                $('#current-image').addClass('d-none');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Reading time calculator
    function updateReadingTime() {
        tinymce.get('content').getContent({format: 'text'}).then(function(content) {
            let wordCount = content.split(' ').length;
            let readingTime = Math.ceil(wordCount / 200); // 200 words per minute
            $('#reading-time').text(readingTime);
        });
    }
    
    // Auto-save draft every 2 minutes
    setInterval(function() {
        if ($('#title').val() || tinymce.get('content').getContent()) {
            saveDraft();
        }
    }, 120000);
    
    function saveDraft() {
        let formData = new FormData($('#postForm')[0]);
        formData.append('action', 'auto_draft');
        
        $.ajax({
            url: $('#postForm').attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showToast('success', 'Draft auto-saved');
            }
        });
    }
});

function removeImage() {
    if (confirm('Are you sure you want to remove the featured image?')) {
        $.post('{{ route("editor.content.remove-image", $post ?? 0) }}', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            $('#current-image').fadeOut();
            showToast('success', 'Image removed successfully');
        });
    }
}
</script>
@endpush