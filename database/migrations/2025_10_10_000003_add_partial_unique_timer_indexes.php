<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('timers', function (Blueprint $table) {
            $table->dropUnique('timers_user_active_unique');
            $table->dropUnique('timers_task_active_unique');

            $table->unsignedBigInteger('user_active_key')
                ->nullable()
                ->virtualAs('case when stopped_at IS NULL then user_id else NULL end');
            $table->unique('user_active_key', 'timers_user_active_unique');

            $table->string('task_active_key', 36)
                ->nullable()
                ->virtualAs('case when stopped_at IS NULL then task_id else NULL end');
            $table->unique('task_active_key', 'timers_task_active_unique');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('timers', function (Blueprint $table) {
            $table->dropUnique('timers_user_active_unique');
            $table->dropColumn('user_active_key');

            $table->dropUnique('timers_task_active_unique');
            $table->dropColumn('task_active_key');

            $table->unique(['user_id', 'stopped_at'], 'timers_user_active_unique');
            $table->unique(['task_id', 'stopped_at'], 'timers_task_active_unique');
        });
    }
};
