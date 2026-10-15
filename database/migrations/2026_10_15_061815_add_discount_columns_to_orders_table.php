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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('discount_id')->nullable()->after('customer_id')->constrained('discounts')->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('discount_id');
            $table->decimal('discount_amount', 10, 2)->default(0.00)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropColumn(['discount_id', 'coupon_code', 'discount_amount']);
        });
    }
};
