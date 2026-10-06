<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // The commenter, the post owner and admins can delete it
        return $user->id === $comment->user_id
            || $user->id === $comment->post->user_id
            || $user->isAdmin();
    }
}
