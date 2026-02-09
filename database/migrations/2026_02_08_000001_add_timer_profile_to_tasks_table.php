<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'timer_type')) {
                $table->string('timer_type')->default('custom')->after('status');
            }

            if (! Schema::hasColumn('tasks', 'target_duration_seconds')) {
                $table->unsignedInteger('target_duration_seconds')->nullable()->after('timer_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'target_duration_seconds')) {
                $table->dropColumn('target_duration_seconds');
            }

            if (Schema::hasColumn('tasks', 'timer_type')) {
                $table->dropColumn('timer_type');
            }
        });
    }
};
