<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        // Only the author's name is loaded
        $posts = Post::with('user:id,name')
            ->latest()
            ->paginate(12);

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    #[Authorize('create', Post::class)]
    public function create(): Response
    {
        return Inertia::render('Posts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('create', Post::class)]
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create($request->validated());

        return redirect()->route('posts.show', $post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): Response
    {
        // Load the author and the comments with their authors.
        // Only id and name of each user, so emails are never sent to the page.
        // Each comment's post (id and owner) is loaded too, the CommentPolicy needs it,
        // in one query instead of one per comment.
        $post->load(['user:id,name', 'comments.user:id,name', 'comments.post:id,user_id']);

        // The logged in user, or null for guests
        $user = $request->user();

        return Inertia::render('Posts/Show', [
            // Only the fields the page shows, the comments are sent separately below
            'post' => $post->only(['id', 'title', 'content', 'created_at', 'user']),

            // Each comment as a plain array, with whether the current user may delete it
            'comments' => $post->comments->map(fn (Comment $comment) => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at,
                // "Guest" for guest comments, see Comment::user()
                'author' => $comment->user->name,
                'can_delete' => $user?->can('delete', $comment) ?? false,
            ]),

            // Which buttons to show, the real check is still the policy
            'can' => [
                'update' => $user?->can('update', $post) ?? false,
                'delete' => $user?->can('delete', $post) ?? false,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    #[Authorize('update', 'post')]
    public function edit(Post $post): Response
    {
        return Inertia::render('Posts/Edit', [
            'post' => $post,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'post')]
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->validated());

        return redirect()->route('posts.show', $post);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'post')]
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('posts.index');
    }
}
