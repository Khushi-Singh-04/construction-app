<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('daily_progress_images');
    }

    public function down(): void
    {
        // You can re-create it if you ever roll back
        Schema::create('daily_progress_images', function ($table) {
            $table->id();
            $table->unsignedBigInteger('work_id');
            $table->string('image_path');
            $table->timestamps();
        });
    }
};
