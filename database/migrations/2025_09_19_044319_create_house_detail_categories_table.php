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
        Schema::create('house_detail_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_detail_id')->constrained('house_details')->onDelete('cascade');
            $table->string('category_name'); // e.g., Bedroom, Kitchen
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('house_detail_categories');
    }
};
