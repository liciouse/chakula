<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>User Management</span>
            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-light">Add New User</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->getRoleColor() }}">
                                    {{ ucfirst($user->getRoleNames()->first() ?? 'user') }}
                                </span>
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                       class="btn btn-outline-primary">Edit</a>
                                    <button class="btn btn-outline-danger delete-user" 
                                            data-id="{{ $user->id }}">Delete</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No users found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle user deletion
    $('.delete-user').click(function() {
        const userId = $(this).data('id');
        const url = "{{ route('admin.users.destroy', ':id') }}".replace(':id', userId);
        
        if(confirm('Are you sure you want to delete this user?')) {
            $.ajax({
                url: url,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if(response.success) {
                        toastr.success(response.message);
                        $.ajax({
                            url: "{{ route('admin.users.index') }}",
                            type: "GET",
                            success: function(content) {
                                $('#app-content').html(content);
                            }
                        });
                    } else {
                        toastr.error(response.message);
                    }
                }
            });
        }
    });
});
</script>
@endpush