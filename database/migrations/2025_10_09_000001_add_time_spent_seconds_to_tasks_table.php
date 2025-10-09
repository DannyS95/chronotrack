<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'time_spent_seconds')) {
                $table->unsignedBigInteger('time_spent_seconds')->nullable()->after('last_activity_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'time_spent_seconds')) {
                $table->dropColumn('time_spent_seconds');
            }
        });
    }
};
