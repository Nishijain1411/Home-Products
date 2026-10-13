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
            $table->string('customer_email')->nullable()->after('customer_id');
            $table->string('customer_phone', 30)->nullable()->after('customer_email');
            $table->string('customer_first_name', 100)->nullable()->after('customer_phone');
            $table->string('customer_last_name', 100)->nullable()->after('customer_first_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_email',
                'customer_phone',
                'customer_first_name',
                'customer_last_name',
            ]);
        });
    }
};
