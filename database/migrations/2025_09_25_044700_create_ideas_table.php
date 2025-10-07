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
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idea_book_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // "my_idea" or "suggestion"
            $table->string('image_path'); // path to uploaded/selected image
            $table->timestamps();

            $table->foreign('idea_book_id')->references('id')->on('idea_books')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ideas');
    }
};
