<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; // ADD THIS
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        // Check if the user's role matches the provided role
        if ($role === $this->role) {
            return true;
        }

        // Role hierarchy: admin > editor > author > user
        if ($this->role === 'admin') {
            return true; // Admin has all roles
        }

        if ($this->role === 'editor' && in_array($role, ['editor', 'author', 'user'])) {
            return true;
        }

        if ($this->role === 'author' && in_array($role, ['author', 'user'])) {
            return true;
        }

        return false;
    }

    /**
     * Get all roles that belong to the user based on hierarchy.
     * This is a helper method to provide compatibility with the blade template.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRolesAttribute()
    {
        // Create a collection with a single object that has a name property
        return collect([
            (object) ['name' => $this->role]
        ]);
    }

    /**
     * Get all posts for the user
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get all comments for the user
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}