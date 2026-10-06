<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use PHPUnit\Framework\TestCase;

class PostPolicyTest extends TestCase
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
     * Make a post in memory that belongs to the given user id.
     */
    private function makePost(int $ownerId): Post
    {
        $post = new Post;
        $post->user_id = $ownerId;

        return $post;
    }

    /**
     * The owner can update the post.
     */
    public function test_owner_can_update(): void
    {
        $this->assertTrue((new PostPolicy)->update($this->makeUser(1), $this->makePost(1)));
    }

    /**
     * Another user can not update the post.
     */
    public function test_other_user_cannot_update(): void
    {
        $this->assertFalse((new PostPolicy)->update($this->makeUser(2), $this->makePost(1)));
    }

    /**
     * An admin can not update the post of someone else.
     */
    public function test_admin_cannot_update_someone_elses_post(): void
    {
        $admin = $this->makeUser(2, Role::Admin);

        $this->assertFalse((new PostPolicy)->update($admin, $this->makePost(1)));
    }

    /**
     * The owner can delete the post.
     */
    public function test_owner_can_delete(): void
    {
        $this->assertTrue((new PostPolicy)->delete($this->makeUser(1), $this->makePost(1)));
    }

    /**
     * An admin can delete the post of someone else.
     */
    public function test_admin_can_delete_any_post(): void
    {
        $admin = $this->makeUser(2, Role::Admin);

        $this->assertTrue((new PostPolicy)->delete($admin, $this->makePost(1)));
    }

    /**
     * Another user can not delete the post.
     */
    public function test_other_user_cannot_delete(): void
    {
        $this->assertFalse((new PostPolicy)->delete($this->makeUser(2), $this->makePost(1)));
    }
}
