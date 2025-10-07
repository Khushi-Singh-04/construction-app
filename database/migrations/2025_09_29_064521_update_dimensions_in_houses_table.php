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
        Schema::table('houses', function (Blueprint $table) {
            //remove old dimension column
            $table->dropColumn('dimensions');
            
            // Add new dimension columns
            $table->decimal('length', 10, 2)->nullable()->after('stage');
            $table->decimal('width', 10, 2)->nullable()->after('length');
            $table->decimal('area', 10, 2)->nullable()->after('width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            // Reverse the changes
            $table->dropColumn(['length', 'width', 'area']);
            $table->string('dimensions')->nullable()->after('stage');
        });
    }
};
