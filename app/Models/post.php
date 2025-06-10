<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Post extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'slug',                // ADD THIS - needed for SEO-friendly URLs
        'content',
        'excerpt',             // ADD THIS - for article summaries
        'featured_image',      // ADD THIS - for article images
        'status',
        'category_id',         // ADD THIS - to link articles to categories
        'user_id',
        'views',
        'meta_title',          // OPTIONAL - for SEO
        'meta_description',    // OPTIONAL - for SEO
        'published_at',  
        'tags',
        'view_count',
        'reading_time',
        'rejection_reason',
       'is_featured',      // OPTIONAL - for scheduling posts
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_featured' => 'boolean',
        'tags' => 'array',
    ];

    protected $dates = [
        'published_at',
        'deleted_at',
    ];

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'published_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all likes for the post.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get all comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get approved comments only.
     */
    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('status', 'approved');
    }

    /**
     * Get the count of likes for the post.
     */
    public function likesCount()
    {
        return $this->likes()->count();
    }

    public function views()
    {
        return $this->hasMany(PostView::class);
    }


    /**
     * Get the count of comments for the post.
     */
    public function commentsCount()
    {
        return $this->comments()->count();
    }

    /**
     * Check if the post is liked by a specific user.
     */
    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Check if the post is commented by a specific user.
     */
    public function isCommentedBy($userId)
    {
        return $this->comments()->where('user_id', $userId)->exists();
    }

    /**
     * Toggle like for a user.
     */
    public function toggleLike($userId)
    {
        $like = $this->likes()->where('user_id', $userId)->first();
        
        if ($like) {
            $like->delete();
            return false; // unliked
        } else {
            $this->likes()->create(['user_id' => $userId]);
            return true; // liked
        }
    }

    /**
     * Increment the view count.
     */
    public function incrementViews()
    {
        $this->increment('views');
    }

    /**
     * Get total views for user's posts.
     */
    public static function totalViewsForUser($userId)
    {
        return static::where('user_id', $userId)->sum('views');
    }

    /**
     * Scope for published posts only.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope for draft posts only.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for pending posts only.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get the route key for the model.
     */
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByAuthor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('view_count', 'desc')->take($limit);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'secondary',
            'pending' => 'warning',
            'published' => 'success',
            'rejected' => 'danger',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $readingTime = ceil($wordCount / 200); // Average reading speed: 200 words per minute
        return max(1, $readingTime);
    }

    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Auto-generate excerpt from content
        $content = strip_tags($this->content);
        return strlen($content) > 150 ? substr($content, 0, 150) . '...' : $content;
    }

    public function getTagsListAttribute()
    {
        return is_array($this->tags) ? implode(', ', $this->tags) : $this->tags;
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function canBeEditedBy(User $user)
    {
        // Author can edit their own posts
        if ($user->role === 'author' && $this->user_id === $user->id) {
            return true;
        }

        // Editors and admins can edit any post
        return in_array($user->role, ['editor', 'admin']);
    }

    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
        ]);
    }

    public function approve()
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    public function duplicate()
    {
        $newPost = $this->replicate();
        $newPost->title = $this->title . ' (Copy)';
        $newPost->slug = $this->slug . '-copy-' . time();
        $newPost->status = 'draft';
        $newPost->published_at = null;
        $newPost->view_count = 0;
        $newPost->save();

        return $newPost;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = \Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = \Str::slug($post->title);
            }
        });
    }
}