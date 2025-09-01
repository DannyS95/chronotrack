<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('metric_type', ['count', 'hours', 'boolean'])->default('count');
            $table->float('target_value')->nullable();
            $table->datetime('deadline')->nullable();
            $table->text('why_statement')->nullable();
            $table->timestamp('target_date')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->integer('reminder_every_n_days')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['active', 'dormant', 'dropped', 'complete'])->default('active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }

};
