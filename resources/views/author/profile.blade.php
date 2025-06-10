@extends('layouts.author')

@section('title', 'Profile Settings')
@section('page-title', 'Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center space-x-6">
            <div class="relative">
                <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center">
                    @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" 
                             alt="Profile Picture" 
                             class="w-24 h-24 rounded-full object-cover">
                    @else
                        <i class="fas fa-user text-gray-600 text-3xl"></i>
                    @endif
                </div>
                <button onclick="document.getElementById('avatar-input').click()" 
                        class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-2 hover:bg-blue-700">
                    <i class="fas fa-camera text-sm"></i>
                </button>
                <input type="file" id="avatar-input" class="hidden" accept="image/*" onchange="uploadAvatar()">
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ auth()->user()->name }}</h1>
                <p class="text-gray-600">{{ auth()->user()->email }}</p>
                <p class="text-sm text-gray-500 mt-1">Member since {{ auth()->user()->created_at->format('F Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Profile Settings Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6">
                <button onclick="showTab('general')" 
                        class="tab-button py-4 px-1 border-b-2 border-blue-500 text-blue-600 font-medium"
                        id="general-tab">
                    General Information
                </button>
                <button onclick="showTab('security')" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700"
                        id="security-tab">
                    Security
                </button>
                <button onclick="showTab('preferences')" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700"
                        id="preferences-tab">
                    Preferences
                </button>
                <button onclick="showTab('social')" 
                        class="tab-button py-4 px-1 border-b-2 border-transparent text-gray-500 hover:text-gray-700"
                        id="social-tab">
                    Social Links
                </button>
            </nav>
        </div>

        <!-- General Information Tab -->
        <div id="general-content" class="tab-content p-6">
            <form action="{{ route('author.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <input type="text" 
                                   name="name" 
                                   value="{{ old('name', auth()->user()->name) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" 
                                   name="email" 
                                   value="{{ old('email', auth()->user()->email) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                        <textarea name="bio" 
                                  rows="4"
                                  placeholder="Tell your readers about yourself..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('bio', auth()->user()->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                            <input type="url" 
                                   name="website" 
                                   value="{{ old('website', auth()->user()->website) }}"
                                   placeholder="https://yourwebsite.com"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('website')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" 
                                   name="location" 
                                   value="{{ old('location', auth()->user()->location) }}"
                                   placeholder="City, Country"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Update Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Security Tab -->
        <div id="security-content" class="tab-content p-6 hidden">
            <div class="space-y-8">
                <!-- Change Password -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                    <form action="{{ route('author.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                <input type="password" 
                                       name="current_password"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                <input type="password" 
                                       name="password"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                <input type="password" 
                                       name="password_confirmation"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" 
                                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Two-Factor Authentication -->
                <div class="border-t pt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Two-Factor Authentication</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">Two-Factor Authentication</p>
                                <p class="text-sm text-gray-600">Add an extra layer of security to your account</p>
                            </div>
                            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                Enable 2FA
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Active Sessions -->
                <div class="border-t pt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Active Sessions</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <i class="fas fa-desktop text-green-600"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Current Session</p>
                                    <p class="text-sm text-gray-600">Chrome on Windows • {{ request()->ip() }}</p>
                                </div>
                            </div>
                            <span class="text-sm text-green-600 font-medium">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferences Tab -->
        <div id="preferences-content" class="tab-content p-6 hidden">
            <form action="{{ route('author.preferences.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Email Notifications</h3>
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[new_comments]"
                                       value="1"
                                       {{ auth()->user()->getNotificationSetting('new_comments') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-gray-700">New comments on my articles</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[comment_replies]"
                                       value="1"
                                       {{ auth()->user()->getNotificationSetting('comment_replies') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-gray-700">Replies to my comments</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[article_published]"
                                       value="1"
                                       {{ auth()->user()->getNotificationSetting('article_published') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-gray-700">Article publication confirmations</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="notifications[weekly_digest]"
                                       value="1"
                                       {{ auth()->user()->getNotificationSetting('weekly_digest') ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-gray-700">Weekly performance digest</span>
                            </label>
                        </div>
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Content Preferences</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Default Article Visibility</label>
                                <select name="default_visibility" 
                                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="published" {{ auth()->user()->default_visibility === 'published' ? 'selected' : '' }}>
                                        Published
                                    </option>
                                    <option value="draft" {{ auth()->user()->default_visibility === 'draft' ? 'selected' : '' }}>
                                        Draft
                                    </option>
                                </select>
                            </div>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="auto_save_drafts"
                                       value="1"
                                       {{ auth()->user()->auto_save_drafts ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-gray-700">Auto-save drafts while writing</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="enable_comments"
                                       value="1"
                                       {{ auth()->user()->enable_comments ?? true ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-3 text-gray-700">Enable comments on new articles by default</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Save Preferences
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Social Links Tab -->
        <div id="social-content" class="tab-content p-6 hidden">
            <form action="{{ route('author.social.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Social Media Links</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="fab fa-twitter text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Twitter</label>
                                    <input type="url" 
                                           name="social[twitter]" 
                                           value="{{ old('social.twitter', auth()->user()->getSocialLink('twitter')) }}"
                                           placeholder="https://twitter.com/yourusername"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i class="fab fa-linkedin text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn</label>
                                    <input type="url" 
                                           name="social[linkedin]" 
                                           value="{{ old('social.linkedin', auth()->user()->getSocialLink('linkedin')) }}"
                                           placeholder="https://linkedin.com/in/yourusername"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-gray-100 rounded-lg">
                                    <i class="fab fa-github text-gray-600"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">GitHub</label>
                                    <input type="url" 
                                           name="social[github]" 
                                           value="{{ old('social.github', auth()->user()->getSocialLink('github')) }}"
                                           placeholder="https://github.com/yourusername"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-pink-100 rounded-lg">
                                    <i class="fab fa-instagram text-pink-600"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                                    <input type="url" 
                                           name="social[instagram]" 
                                           value="{{ old('social.instagram', auth()->user()->getSocialLink('instagram')) }}"
                                           placeholder="https://instagram.com/yourusername"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-red-100 rounded-lg">
                                    <i class="fab fa-youtube text-red-600"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">YouTube</label>
                                    <input type="url" 
                                           name="social[youtube]" 
                                           value="{{ old('social.youtube', auth()->user()->getSocialLink('youtube')) }}"
                                           placeholder="https://youtube.com/c/yourchannel"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Update Social Links
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-blue-500', 'text-blue-600');
}

function uploadAvatar() {
    const input = document.getElementById('avatar-input');
    const file = input.files[0];
    
    if (file) {
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        fetch('{{ route("author.avatar.upload") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to upload avatar');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to upload avatar');
        });
    }
}
</script>
@endsection