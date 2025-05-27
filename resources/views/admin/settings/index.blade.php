@extends('layouts.admin')

@section('content')
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>System Settings</h5>
            </div>
            <div class="card-body">
                <form id="settings-form" method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               value="{{ $settings['site_name'] }}">
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode"
                               {{ $settings['maintenance_mode'] ? 'checked' : '' }}>
                        <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#settings-form').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    alert('Settings updated successfully');
                }
            },
            error: function() {
                alert('Failed to update settings');
            }
        });
    });
});
</script>
@endpush