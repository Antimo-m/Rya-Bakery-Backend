<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('notes');
            $table->timestamp('cancelled_at')->nullable()->after('accepted_at');
            $table->timestamp('delivered_at')->nullable()->after('cancelled_at');
        });

        DB::table('orders')->where('status', 'accepted')->update(['status' => 'pending']);
        DB::table('orders')->where('status', 'pending')->whereNull('accepted_at')->update(['status' => 'received']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['accepted_at', 'cancelled_at', 'delivered_at']);
        });
    }
};
