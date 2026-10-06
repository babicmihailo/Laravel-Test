<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // GUEST TESTS (not logged in)
    // ------------------------------------------------------------------

    /**
     * Guests can comment, the comment has no user.
     */
    public function test_guest_can_comment(): void
    {
        $post = Post::factory()->create();

        $response = $this->post('/posts/'.$post->id.'/comments', ['comment' => 'Hi from a guest']);

        $response->assertRedirect('/posts/'.$post->id);
        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'comment' => 'Hi from a guest',
            'user_id' => null,
        ]);
    }

    /**
     * An empty comment is rejected and nothing is stored.
     */
    public function test_comment_cannot_be_empty(): void
    {
        $post = Post::factory()->create();

        $response = $this->post('/posts/'.$post->id.'/comments', ['comment' => '']);

        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * A comment longer than 1000 characters is rejected.
     */
    public function test_comment_cannot_be_too_long(): void
    {
        $post = Post::factory()->create();

        $response = $this->post('/posts/'.$post->id.'/comments', ['comment' => str_repeat('a', 1001)]);

        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * Guests can not delete a comment, they are sent to login.
     */
    public function test_guest_cannot_delete_a_comment(): void
    {
        $comment = Comment::factory()->create();

        $response = $this->delete('/comments/'.$comment->id);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    // ------------------------------------------------------------------
    // USER TESTS (logged in, the author, the post owner or another user)
    // ------------------------------------------------------------------

    /**
     * Logged in users can comment, the comment belongs to them.
     */
    public function test_user_can_comment(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts/'.$post->id.'/comments', ['comment' => 'Hi from a user']);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'comment' => 'Hi from a user',
            'user_id' => $user->id,
        ]);
    }

    /**
     * The author can delete their own comment.
     */
    public function test_author_can_delete_their_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete('/comments/'.$comment->id);

        $response->assertRedirect('/posts/'.$comment->post_id);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /**
     * The owner of the post can delete a comment on it, also a guest comment.
     */
    public function test_post_owner_can_delete_a_comment_on_their_post(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();
        $comment = Comment::factory()->for($post)->create(['user_id' => null]);

        $response = $this->actingAs($owner)->delete('/comments/'.$comment->id);

        $response->assertRedirect('/posts/'.$post->id);
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /**
     * Other users can not delete the comment, it still exists.
     */
    public function test_other_user_cannot_delete_a_comment(): void
    {
        $comment = Comment::factory()->create();
        $stranger = User::factory()->create();

        $response = $this->actingAs($stranger)->delete('/comments/'.$comment->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    // ------------------------------------------------------------------
    // ADMIN TESTS
    // ------------------------------------------------------------------

    /**
     * Admins can delete any comment.
     */
    public function test_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = Comment::factory()->create();

        $this->actingAs($admin)->delete('/comments/'.$comment->id);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    // ------------------------------------------------------------------
    // DATABASE RULES (what happens to comments when a post or user is deleted)
    // ------------------------------------------------------------------

    /**
     * Deleting a post also deletes its comments.
     */
    public function test_deleting_a_post_deletes_its_comments(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();
        Comment::factory()->for($post)->create();

        $this->actingAs($owner)->delete('/posts/'.$post->id);

        $this->assertDatabaseCount('comments', 0);
    }

    /**
     * Deleting a user keeps their comments, they become guest comments.
     */
    public function test_deleting_a_user_keeps_their_comments_as_guest_comments(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'user_id' => null]);
    }
}
