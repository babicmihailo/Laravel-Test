<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request, Post $post): RedirectResponse
    {
        // Guests can comment too, their comment gets no user (null)
        $post->comments()->create([
            'comment' => $request->validated()['comment'],
            'user_id' => $request->user()?->id,
        ]);

        return redirect()->route('posts.show', $post);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'comment')]
    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()->route('posts.show', $comment->post_id);
    }
}
