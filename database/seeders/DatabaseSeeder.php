<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Main user (admin)
        $mainUser = User::factory()->admin()->create([
            'name' => 'Mihailo Babic',
            'email' => 'mihailobabic@example.com',
        ]);

        // 2. Test user with a known login, to test the owner only permissions
        $regularUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
        ]);

        // 3. Create 4 dummy users and merge everyone into one collection (6 total)
        $allUsers = User::factory(4)->create()->push($mainUser, $regularUser);

        // 4. For every post, randomly assign comments to one of the 6 existing users (or guest null)
        $commentFactory = Comment::factory(10)->state(fn () => [
            'user_id' => fake()->boolean(70) ? $allUsers->random()->id : null,
        ]);

        // 5. Attach 3 posts (with 10 comments each) to each of the 6 users
        $allUsers->each(function ($user) use ($commentFactory) {
            Post::factory(3)
                ->has($commentFactory)
                ->create(['user_id' => $user->id]);
        });
    }
}
