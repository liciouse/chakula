<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;

// Editor Controllers
use App\Http\Controllers\Editor\DashboardController as EditorDashboardController;
use App\Http\Controllers\Editor\ContentController as EditorContentController;
use App\Http\Controllers\Editor\CategoryController as EditorCategoryController;
use App\Http\Controllers\Editor\CommentController as EditorCommentController;

// Author Controllers
use App\Http\Controllers\Author\DashboardController as AuthorDashboardController;
use App\Http\Controllers\Author\ArticleController as AuthorArticleController;
use App\Http\Controllers\Author\CommentController as AuthorCommentController;
use App\Http\Controllers\Author\ProfileController as AuthorProfileController;

// User Controllers
use App\Http\Controllers\User\DashboardController as UserDashboardController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');

// Blog Routes
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index'])->name('posts.index');
    Route::get('/{post:slug}', [PostController::class, 'show'])->name('posts.show');
    Route::get('/category/{category}', [PostController::class, 'category'])->name('posts.category');
    Route::get('/tag/{tag}', [PostController::class, 'tag'])->name('posts.tag');
    Route::post('/{post}/comment', [PostController::class, 'storeComment'])->name('posts.comment');
    Route::post('/{post}/like', [PostController::class, 'toggleLike'])->name('posts.like');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Password Reset Routes
Route::get('/password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('/password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');


/*
|--------------------------------------------------------------------------
| General Profile Routes (Add this section after Authentication Routes)
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [AuthorProfileController::class, 'show'])->name('show');
    Route::get('/edit', [AuthorProfileController::class, 'edit'])->name('edit');
    Route::get('/settings', [AuthorProfileController::class, 'settings'])->name('settings'); // Add this line
    Route::put('/', [AuthorProfileController::class, 'update'])->name('update');
    Route::put('/password', [AuthorProfileController::class, 'updatePassword'])->name('password.update');
    Route::post('/avatar', [AuthorProfileController::class, 'updateAvatar'])->name('avatar.update');
    Route::delete('/avatar', [AuthorProfileController::class, 'deleteAvatar'])->name('avatar.delete');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function() {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // User Management
    Route::resource('users', AdminUserController::class);
    
    // Content Management (Posts/Articles)
    Route::get('/content', [ContentController::class, 'index'])->name('content.index');
    Route::get('/content/{post}/edit', [ContentController::class, 'edit'])->name('content.edit');
    Route::put('/content/{post}', [ContentController::class, 'update'])->name('content.update');
    Route::delete('/content/{post}', [ContentController::class, 'destroy'])->name('content.destroy');
    Route::post('/content/{post}/toggle-status', [ContentController::class, 'toggleStatus'])->name('content.toggle-status');
    Route::post('/content/bulk-action', [ContentController::class, 'bulkAction'])->name('content.bulk-action');
    
    // Category Management
    Route::resource('categories', AdminCategoryController::class);
    Route::post('/categories/{category}/toggle-status', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::post('/categories/bulk-action', [AdminCategoryController::class, 'bulkAction'])->name('categories.bulk-action');
    
    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});
/*
|--------------------------------------------------------------------------
| Editor Routes
|--------------------------------------------------------------------------
*/

Route::prefix('editor')->middleware(['auth', 'role:editor'])->name('editor.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [EditorDashboardController::class, 'index'])->name('dashboard');
    
    // User Management - ADD THIS SECTION
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [EditorUserController::class, 'index'])->name('index');
        Route::get('/{user}', [EditorUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [EditorUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [EditorUserController::class, 'update'])->name('update');
        Route::post('/{user}/toggle-status', [EditorUserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-action', [EditorUserController::class, 'bulkAction'])->name('bulk-action');
    });
    
    // Author Management
    Route::prefix('authors')->name('authors.')->group(function () {
        Route::get('/', [EditorAuthorController::class, 'index'])->name('index');
        Route::get('/{user}', [EditorAuthorController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [EditorAuthorController::class, 'edit'])->name('edit');
        Route::put('/{user}', [EditorAuthorController::class, 'update'])->name('update');
        Route::post('/{user}/toggle-status', [EditorAuthorController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-action', [EditorAuthorController::class, 'bulkAction'])->name('bulk-action');
    });
    
    // Content Management
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [EditorContentController::class, 'index'])->name('index');
        Route::get('/create', [EditorContentController::class, 'create'])->name('create'); // Add this line
        Route::post('/', [EditorContentController::class, 'store'])->name('store'); // Add this line
        Route::get('/{post}', [EditorContentController::class, 'show'])->name('show');
        Route::get('/{post}/edit', [EditorContentController::class, 'edit'])->name('edit');
        Route::put('/{post}', [EditorContentController::class, 'update'])->name('update');
        Route::delete('/{post}', [EditorContentController::class, 'destroy'])->name('destroy');
        Route::post('/{post}/approve', [EditorContentController::class, 'approve'])->name('approve');
        Route::post('/{post}/reject', [EditorContentController::class, 'reject'])->name('reject');
        Route::post('/bulk-action', [EditorContentController::class, 'bulkAction'])->name('bulk-action');
    });
    
    // Category Management
    Route::resource('categories', EditorCategoryController::class);
    Route::post('/categories/{category}/toggle-status', [EditorCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    
    // Comment Management
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/', [EditorCommentController::class, 'index'])->name('index');
        Route::post('/{comment}/approve', [EditorCommentController::class, 'approve'])->name('approve');
        Route::post('/{comment}/reject', [EditorCommentController::class, 'reject'])->name('reject');
        Route::post('/{comment}/spam', [EditorCommentController::class, 'spam'])->name('spam');
        Route::delete('/{comment}', [EditorCommentController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [EditorCommentController::class, 'bulkAction'])->name('bulk-action');
    });
});
/*
|--------------------------------------------------------------------------
| Author Routes
|--------------------------------------------------------------------------
*/

Route::prefix('author')->middleware(['auth', 'role:author'])->name('author.')->group(function() {
    // Dashboard
    Route::get('/dashboard', [AuthorDashboardController::class, 'index'])->name('dashboard');
    
    // Article Management
    Route::prefix('articles')->name('articles.')->group(function() {
        Route::get('/', [AuthorArticleController::class, 'index'])->name('index');
        Route::get('/pending', [AuthorArticleController::class, 'pending'])->name('pending');
        Route::get('/published', [AuthorArticleController::class, 'published'])->name('published');
        Route::get('/drafts', [AuthorArticleController::class, 'drafts'])->name('drafts');
        Route::get('/create', [AuthorArticleController::class, 'create'])->name('create');
        Route::post('/', [AuthorArticleController::class, 'store'])->name('store');
        Route::get('/{article}', [AuthorArticleController::class, 'show'])->name('show');
        Route::get('/{article}/edit', [AuthorArticleController::class, 'edit'])->name('edit');
        Route::put('/{article}', [AuthorArticleController::class, 'update'])->name('update');
        Route::delete('/{article}', [AuthorArticleController::class, 'destroy'])->name('destroy');
        Route::post('/{article}/publish', [AuthorArticleController::class, 'publish'])->name('publish');
        Route::post('/{article}/unpublish', [AuthorArticleController::class, 'unpublish'])->name('unpublish');
        Route::post('/{article}/duplicate', [AuthorArticleController::class, 'duplicate'])->name('duplicate');
        Route::get('/{article}/preview', [AuthorArticleController::class, 'preview'])->name('preview');
        
        // AJAX/API routes for articles
        Route::post('/{article}/toggle-status', [AuthorArticleController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-action', [AuthorArticleController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/auto-save', [AuthorArticleController::class, 'autoSave'])->name('auto-save');
    });
    
    // Comment Management
    Route::prefix('comments')->name('comments.')->group(function() {
        Route::get('/', [AuthorCommentController::class, 'index'])->name('index');
        Route::get('/pending', [AuthorCommentController::class, 'pending'])->name('pending');
        Route::get('/approved', [AuthorCommentController::class, 'approved'])->name('approved');
        Route::get('/spam', [AuthorCommentController::class, 'spam'])->name('spam');
        Route::post('/{comment}/approve', [AuthorCommentController::class, 'approve'])->name('approve');
        Route::post('/{comment}/reject', [AuthorCommentController::class, 'reject'])->name('reject');
        Route::post('/{comment}/spam', [AuthorCommentController::class, 'markAsSpam'])->name('spam.mark');
        Route::delete('/{comment}', [AuthorCommentController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [AuthorCommentController::class, 'bulkAction'])->name('bulk');
    });
    
    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function() {
        Route::get('/', [AuthorProfileController::class, 'index'])->name('index');
        Route::put('/', [AuthorProfileController::class, 'update'])->name('update');
        Route::put('/password', [AuthorProfileController::class, 'updatePassword'])->name('password');
        Route::post('/avatar', [AuthorProfileController::class, 'updateAvatar'])->name('avatar');
        Route::put('/social', [AuthorProfileController::class, 'updateSocial'])->name('social');
    });
});

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
*/

Route::prefix('user')->middleware(['auth', 'role:user'])->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    
    // User-specific routes
    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| API Routes for AJAX
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('api')->name('api.')->group(function () {
    // Author API routes
    Route::middleware('role:author')->group(function() {
        Route::get('/author/stats', [AuthorDashboardController::class, 'getStats'])->name('author.stats');
        Route::post('/author/articles/{article}/toggle-status', [AuthorArticleController::class, 'toggleStatus'])->name('author.articles.toggle');
        Route::post('/author/comments/{comment}/quick-approve', [AuthorCommentController::class, 'quickApprove'])->name('author.comments.quick-approve');
    });
    
    // Editor API routes
    Route::middleware('role:editor')->group(function() {
        Route::get('/editor/stats', [EditorDashboardController::class, 'getStats'])->name('editor.stats');
        Route::post('/editor/posts/{post}/approve', [EditorContentController::class, 'approve'])->name('editor.posts.approve');
        Route::post('/editor/posts/{post}/reject', [EditorContentController::class, 'reject'])->name('editor.posts.reject');
        Route::post('/editor/comments/{comment}/approve', [EditorCommentController::class, 'approve'])->name('editor.comments.approve');
        Route::post('/editor/comments/{comment}/reject', [EditorCommentController::class, 'reject'])->name('editor.comments.reject');
    });
    
    // Admin API routes
    Route::middleware('role:admin')->group(function() {
        Route::get('/admin/stats', [AdminDashboardController::class, 'getStats'])->name('admin.stats');
        Route::get('/admin/categories/list', [AdminCategoryController::class, 'getCategories'])->name('admin.categories.list');
    });
});

/*
|--------------------------------------------------------------------------
| Public Author Profiles
|--------------------------------------------------------------------------
*/

Route::get('/authors/{user:username}', [PostController::class, 'authorProfile'])->name('author.public');
Route::get('/authors/{user:username}/posts', [PostController::class, 'authorPosts'])->name('author.posts.public');

/*
|--------------------------------------------------------------------------
| Additional Routes
|--------------------------------------------------------------------------
*/

// Search
Route::get('/search', [PostController::class, 'search'])->name('search');

// Archives
Route::get('/archive', [PostController::class, 'archive'])->name('archive');
Route::get('/archive/{year}', [PostController::class, 'yearArchive'])->name('archive.year');

// RSS Feed
Route::get('/feed', [PostController::class, 'feed'])->name('feed');

// Sitemap
Route::get('/sitemap.xml', [PostController::class, 'sitemap'])->name('sitemap');

/*
|--------------------------------------------------------------------------
| Development Routes (Local Environment Only)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    Route::get('/dev/clear-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        return 'Cache cleared successfully!';
    });
}

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});