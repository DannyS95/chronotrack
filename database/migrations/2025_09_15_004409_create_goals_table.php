<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['active', 'dormant', 'dropped', 'complete'])
                ->default('active');
            $table->uuid('project_id');
            $table->foreign('project_id')->references('id')->on('projects')
                ->cascadeOnDelete();
            $table->enum('completion_rule', ['task_based', 'deadline_based', 'hybrid'])
                ->default('hybrid');

            $table->timestamps();
            $table->softDeletes();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
