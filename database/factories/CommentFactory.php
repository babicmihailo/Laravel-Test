<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'comment' => fake()->paragraph(),

            // Within the last month, so always after the post
            'created_at' => $createdAt = fake()->dateTimeBetween('-1 month'),
            'updated_at' => $createdAt,
        ];
    }
}
