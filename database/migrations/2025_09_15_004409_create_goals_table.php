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

            // measurement
            $table->enum('metric_type', ['count', 'hours', 'boolean'])->default('count');
            $table->decimal('target_value', 12, 2)->nullable();

            // lifecycle
            $table->timestamp('deadline')->nullable(); // high-level goal deadline
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['active', 'dormant', 'dropped', 'complete'])->default('active');

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
