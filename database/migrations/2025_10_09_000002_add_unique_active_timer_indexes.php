<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->unique(['user_id', 'stopped_at'], 'timers_user_active_unique');
            $table->unique(['task_id', 'stopped_at'], 'timers_task_active_unique');
        });
    }

    public function down(): void
    {
        Schema::table('timers', function (Blueprint $table) {
            $table->dropUnique('timers_user_active_unique');
            $table->dropUnique('timers_task_active_unique');
        });
    }
};
