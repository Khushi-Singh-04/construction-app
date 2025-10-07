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
        Schema::create('house_detail_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('house_detail_categories')->onDelete('cascade');
            $table->string('sub_category_name'); // e.g., Walls, Flooring
            $table->text('description')->nullable();
            $table->json('images'); // store multiple image paths as JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('house_detail_sub_categories');
    }
};
