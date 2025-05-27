@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            Edit User: {{ $user->name }}
        </div>
        <div class="card-body">
            <form id="edit-user-form" action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <label for="name" class="col-sm-3 col-form-label">Full Name</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ old('name', $user->name) }}" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="email" class="col-sm-3 col-form-label">Email Address</label>
                    <div class="col-sm-9">
                        <input type="email" class="form-control" id="email" name="email" 
                               value="{{ old('email', $user->email) }}" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="password" class="col-sm-3 col-form-label">New Password</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="password_confirmation" class="col-sm-3 col-form-label">Confirm Password</label>
                    <div class="col-sm-9">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <label for="role" class="col-sm-3 col-form-label">User Role</label>
                    <div class="col-sm-9">
                        <select class="form-select" id="role" name="role" required>
                            <option value="admin" {{ $user->hasRole('admin') ? 'selected' : '' }}>Administrator</option>
                            <option value="editor" {{ $user->hasRole('editor') ? 'selected' : '' }}>Editor</option>
                            <option value="author" {{ $user->hasRole('author') ? 'selected' : '' }}>Author</option>
                            <option value="user" {{ $user->hasRole('user') ? 'selected' : '' }}>Regular User</option>
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            Update User
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#edit-user-form').submit(function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');
        
        $.ajax({
            url: $(this).attr('action'),
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let messages = '';
                
                for (const field in errors) {
                    messages += errors[field][0] + '\n';
                }
                
                toastr.error(messages);
            },
            complete: function() {
                $btn.prop('disabled', false);
                $btn.find('.spinner-border').addClass('d-none');
            }
        });
    });
});
</script>
@endpush
@endsection