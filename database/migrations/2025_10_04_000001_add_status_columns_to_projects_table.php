<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'status')) {
                $table->string('status')->default('active');
                $table->timestamp('completed_at')->nullable();
                $table->string('completion_source')->nullable();
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'status')) {
                $table->dropIndex('projects_status_index');
                $table->dropColumn(['status', 'completed_at', 'completion_source']);
            }
        });
    }
};
