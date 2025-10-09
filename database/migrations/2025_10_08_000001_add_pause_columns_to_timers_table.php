<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            if (! Schema::hasColumn('timers', 'paused_at')) {
                $table->timestamp('paused_at')->nullable()->after('started_at');
            }

            if (! Schema::hasColumn('timers', 'paused_total')) {
                $table->integer('paused_total')->default(0)->after('paused_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            if (Schema::hasColumn('timers', 'paused_total')) {
                $table->dropColumn('paused_total');
            }

            if (Schema::hasColumn('timers', 'paused_at')) {
                $table->dropColumn('paused_at');
            }
        });
    }
};
