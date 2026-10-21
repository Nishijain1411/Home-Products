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
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 30)->default('blue');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customer_segment_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_segment_id')->constrained('customer_segments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['customer_segment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segment_user');
        Schema::dropIfExists('customer_segments');
    }
};
