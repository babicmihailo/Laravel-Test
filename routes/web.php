<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// The posts list is the home page
Route::redirect('/', '/posts');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/**
 * Posts
 */
// Logged in only: create, store, edit, update, destroy
Route::middleware('auth')->group(function () {
    Route::resource('posts', PostController::class)->except(['index', 'show']);
});
// Public: index, show
Route::resource('posts', PostController::class)->only(['index', 'show']);

/**
 * Comments
 */
// Logged in only
Route::middleware('auth')->group(function () {
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});
// Public: guests can comment too
Route::post('posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');

require __DIR__.'/auth.php';
