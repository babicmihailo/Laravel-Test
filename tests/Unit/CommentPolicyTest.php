<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Policies\CommentPolicy;
use PHPUnit\Framework\TestCase;

class CommentPolicyTest extends TestCase
{
    /**
     * Make a user in memory, no database needed.
     */
    private function makeUser(int $id, Role $role = Role::User): User
    {
        $user = new User;
        $user->id = $id;
        $user->role = $role;

        return $user;
    }

    /**
     * Make a comment in memory, written by $authorId (null is a guest) on a post owned by $postOwnerId.
     */
    private function makeComment(?int $authorId, int $postOwnerId): Comment
    {
        $post = new Post;
        $post->user_id = $postOwnerId;

        $comment = new Comment;
        $comment->user_id = $authorId;
        $comment->setRelation('post', $post);

        return $comment;
    }

    /**
     * The author can delete their comment.
     */
    public function test_author_can_delete(): void
    {
        $comment = $this->makeComment(authorId: 2, postOwnerId: 1);

        $this->assertTrue((new CommentPolicy)->delete($this->makeUser(2), $comment));
    }

    /**
     * The post owner can delete a comment on their post.
     */
    public function test_post_owner_can_delete(): void
    {
        $comment = $this->makeComment(authorId: 2, postOwnerId: 1);

        $this->assertTrue((new CommentPolicy)->delete($this->makeUser(1), $comment));
    }

    /**
     * The post owner can delete a guest comment, a guest has no user id.
     */
    public function test_post_owner_can_delete_a_guest_comment(): void
    {
        $comment = $this->makeComment(authorId: null, postOwnerId: 1);

        $this->assertTrue((new CommentPolicy)->delete($this->makeUser(1), $comment));
    }

    /**
     * An admin can delete any comment.
     */
    public function test_admin_can_delete(): void
    {
        $comment = $this->makeComment(authorId: 2, postOwnerId: 1);
        $admin = $this->makeUser(3, Role::Admin);

        $this->assertTrue((new CommentPolicy)->delete($admin, $comment));
    }

    /**
     * Someone who is not the author, the post owner or an admin can not delete it.
     */
    public function test_stranger_cannot_delete(): void
    {
        $comment = $this->makeComment(authorId: 2, postOwnerId: 1);

        $this->assertFalse((new CommentPolicy)->delete($this->makeUser(3), $comment));
    }

    /**
     * A stranger can not delete a guest comment either.
     */
    public function test_stranger_cannot_delete_a_guest_comment(): void
    {
        $comment = $this->makeComment(authorId: null, postOwnerId: 1);

        $this->assertFalse((new CommentPolicy)->delete($this->makeUser(3), $comment));
    }
}
