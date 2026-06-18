<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index(['status', 'delivered_at']);
            $table->index(['customer_name', 'table_number']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->index(['order_id', 'product_id']);
        });

        Schema::table('order_histories', function (Blueprint $table) {
            $table->index(['restored_at', 'archived_at']);
            $table->index(['reason', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['status', 'delivered_at']);
            $table->dropIndex(['customer_name', 'table_number']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id', 'product_id']);
        });

        Schema::table('order_histories', function (Blueprint $table) {
            $table->dropIndex(['restored_at', 'archived_at']);
            $table->dropIndex(['reason', 'archived_at']);
        });
    }
};
