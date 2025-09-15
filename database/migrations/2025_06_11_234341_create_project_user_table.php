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
        Schema::create('project_user', function (Blueprint $table) {
            $table->foreignUuid('project_id')->constrained()->onDelete('cascade');
            $table->decimal('budget_amount', 12, 2)->nullable(); // e.g. $50,000
            $table->integer('budget_hours')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->primary(['project_id', 'user_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};
