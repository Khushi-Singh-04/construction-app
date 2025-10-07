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
        Schema::table('users', function (Blueprint $table) {
           // Basic profile fields (mandatory after registration)
            $table->string('profession')->nullable();
            $table->string('image')->nullable();

            // Extended profile fields
            $table->string('full_name')->nullable();
            $table->string('phone_no')->nullable();
            $table->date('dob')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
           $table->dropColumn([
                'profession', 'image',
                'full_name', 'phone_no', 'dob',
                'city', 'state', 'postal_code'
            ]);
        });
    }
};
