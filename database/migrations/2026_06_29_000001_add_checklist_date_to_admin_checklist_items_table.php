<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_checklist_items', function (Blueprint $table): void {
            $table->date('checklist_date')->nullable()->after('title')->index();
        });

        DB::table('admin_checklist_items')
            ->select(['id', 'created_at'])
            ->orderBy('id')
            ->each(function (object $item): void {
                DB::table('admin_checklist_items')
                    ->where('id', $item->id)
                    ->update([
                        'checklist_date' => $item->created_at
                            ? Carbon::parse($item->created_at)->toDateString()
                            : now()->toDateString(),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('admin_checklist_items', function (Blueprint $table): void {
            $table->dropIndex(['checklist_date']);
            $table->dropColumn('checklist_date');
        });
    }
};
