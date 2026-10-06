<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Creates a user automatically if user_id is not provided
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),

            // Posts are 1-3 months old, comments are newer so they always come after the post
            'created_at' => $createdAt = fake()->dateTimeBetween('-3 months', '-1 month'),
            'updated_at' => $createdAt,
        ];
    }
}
