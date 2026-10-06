<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * A user with the admin role is an admin.
     */
    public function test_admin_role_is_an_admin(): void
    {
        $user = new User;
        $user->role = Role::Admin;

        $this->assertTrue($user->isAdmin());
    }

    /**
     * A user with the user role is not an admin.
     */
    public function test_user_role_is_not_an_admin(): void
    {
        $user = new User;
        $user->role = Role::User;

        $this->assertFalse($user->isAdmin());
    }
}
