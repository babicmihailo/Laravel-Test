<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // GUEST TESTS (not logged in)
    // ------------------------------------------------------------------

    /**
     * The home page redirects to the posts list.
     */
    public function test_the_home_page_redirects_to_the_posts_list(): void
    {
        $this->get('/')->assertRedirect('/posts');
    }

    /**
     * Guests can see the list of posts.
     */
    public function test_guest_can_see_the_post_list(): void
    {
        Post::factory()->create(['title' => 'Hello list']);

        $response = $this->get('/posts');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Posts/Index')
            ->where('posts.data.0.title', 'Hello list')
        );
    }

    /**
     * Guests can open a single post and see its comments.
     */
    public function test_guest_can_see_a_single_post_with_its_comments(): void
    {
        $post = Post::factory()->create();
        Comment::factory(2)->for($post)->create();

        $response = $this->get('/posts/'.$post->id);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Posts/Show')
            ->where('post.id', $post->id)
            ->has('comments', 2)
        );
    }

    /**
     * Author and commenter emails are never sent to the page.
     */
    public function test_pages_do_not_expose_user_emails(): void
    {
        $author = User::factory()->create(['email' => 'author@example.com']);
        $commenter = User::factory()->create(['email' => 'commenter@example.com']);
        $post = Post::factory()->for($author)->create();
        Comment::factory()->for($post)->for($commenter)->create();

        $this->get('/posts')->assertDontSee('author@example.com');
        $this->get('/posts/'.$post->id)
            ->assertDontSee('author@example.com')
            ->assertDontSee('commenter@example.com');
    }

    /**
     * Guests are sent to login when they open the create form.
     */
    public function test_guest_is_sent_to_login_when_creating_a_post(): void
    {
        $response = $this->get('/posts/create');

        $response->assertRedirect('/login');
    }

    /**
     * Guests can not save a post, nothing is stored.
     */
    public function test_guest_cannot_store_a_post(): void
    {
        $response = $this->post('/posts', ['title' => 'x', 'content' => 'y']);

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * Guests can not delete a post, it still exists.
     */
    public function test_guest_cannot_delete_a_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->delete('/posts/'.$post->id);

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    // ------------------------------------------------------------------
    // USER TESTS (logged in, the owner of the post or another user)
    // ------------------------------------------------------------------

    /**
     * Logged in users can create a post and become its owner.
     */
    public function test_user_can_create_a_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'My title',
            'content' => 'My content',
        ]);

        $post = Post::where('title', 'My title')->first();
        $this->assertSame($user->id, $post->user_id);
        $response->assertRedirect('/posts/'.$post->id);
    }

    /**
     * A post needs a title and content, nothing is stored without them.
     */
    public function test_post_needs_title_and_content(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', ['title' => '', 'content' => '']);

        $response->assertSessionHasErrors(['title', 'content']);
        $this->assertDatabaseCount('posts', 0);
    }

    /**
     * The owner can open the edit form.
     */
    public function test_owner_can_open_the_edit_form(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->get('/posts/'.$post->id.'/edit');

        $response->assertOk();
    }

    /**
     * The owner can update their post.
     */
    public function test_owner_can_update_their_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->put('/posts/'.$post->id, [
            'title' => 'New title',
            'content' => 'New content',
        ]);

        $response->assertRedirect('/posts/'.$post->id);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'New title']);
    }

    /**
     * An update with an empty title is rejected and the post stays the same.
     */
    public function test_update_needs_a_title(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['title' => 'Old title']);

        $response = $this->actingAs($user)->put('/posts/'.$post->id, [
            'title' => '',
            'content' => 'New content',
        ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Old title']);
    }

    /**
     * The owner can delete their post.
     */
    public function test_owner_can_delete_their_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete('/posts/'.$post->id);

        $response->assertRedirect('/posts');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    /**
     * Another user can not open the edit form.
     */
    public function test_other_user_cannot_open_the_edit_form(): void
    {
        $post = Post::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->get('/posts/'.$post->id.'/edit');

        $response->assertForbidden();
    }

    /**
     * Another user can not update the post, it stays the same.
     */
    public function test_other_user_cannot_update_a_post(): void
    {
        $post = Post::factory()->create(['title' => 'Old title']);
        $other = User::factory()->create();

        $response = $this->actingAs($other)->put('/posts/'.$post->id, [
            'title' => 'Hacked',
            'content' => 'Hacked',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Old title']);
    }

    /**
     * Another user can not delete the post, it still exists.
     */
    public function test_other_user_cannot_delete_a_post(): void
    {
        $post = Post::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->delete('/posts/'.$post->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    // ------------------------------------------------------------------
    // ADMIN TESTS
    // ------------------------------------------------------------------

    /**
     * Admins can delete any post.
     */
    public function test_admin_can_delete_any_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($admin)->delete('/posts/'.$post->id);

        $response->assertRedirect('/posts');
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
