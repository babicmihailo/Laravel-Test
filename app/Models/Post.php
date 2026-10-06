<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// user_id is not fillable, posts are created through $user->posts()
#[Fillable(['title', 'content'])]
class Post extends Model
{
    use HasFactory;

    /**
     * Get the user (author) that created this post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all comments left on this post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
