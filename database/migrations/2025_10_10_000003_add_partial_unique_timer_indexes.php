<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->dropUnique('timers_user_active_unique');
            $table->dropUnique('timers_task_active_unique');
        });

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX timers_user_active_unique
            ON timers (user_id)
            WHERE stopped_at IS NULL AND user_id IS NOT NULL
        SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX timers_task_active_unique
            ON timers (task_id)
            WHERE stopped_at IS NULL
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS timers_user_active_unique');
        DB::statement('DROP INDEX IF EXISTS timers_task_active_unique');

        Schema::table('timers', function (Blueprint $table) {
            $table->unique(['user_id', 'stopped_at'], 'timers_user_active_unique');
            $table->unique(['task_id', 'stopped_at'], 'timers_task_active_unique');
        });
    }
};
