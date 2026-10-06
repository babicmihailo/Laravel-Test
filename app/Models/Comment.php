<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// post_id is not fillable, comments are created through $post->comments()
#[Fillable(['user_id', 'comment'])]
class Comment extends Model
{
    use HasFactory;

    /**
     * Get the post that owns this comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who wrote this comment, guests get a default user named Guest.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'Guest']);
    }
}
