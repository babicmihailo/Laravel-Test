<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // Creates post_id column with a foreign key to posts.id; deleting a post deletes its comments
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            // Creates a nullable user_id column with a foreign key to users.id, null means a guest comment,
            // and deleting the user keeps the comment but sets user_id back to null
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('comment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
