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
        Schema::table('discounts', function (Blueprint $table) {
            $table->string('customer_eligibility', 20)->default('all')->after('type'); // 'all', 'segments'
            $table->string('applies_to', 20)->default('all')->after('customer_eligibility'); // 'all', 'categories', 'products'
        });

        // Pivot table for customer segments
        Schema::create('customer_segment_discount', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->foreignId('customer_segment_id')->constrained('customer_segments')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['discount_id', 'customer_segment_id']);
        });

        // Pivot table for categories
        Schema::create('category_discount', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['discount_id', 'category_id']);
        });

        // Pivot table for products
        Schema::create('discount_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['discount_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_product');
        Schema::dropIfExists('category_discount');
        Schema::dropIfExists('customer_segment_discount');

        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['customer_eligibility', 'applies_to']);
        });
    }
};
