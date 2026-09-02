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
        Schema::create('planning_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('plannings')->restrictOnDelete();
            $table->foreignId('version_id')->constrained('planning_versions')->restrictOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('from_status');
            $table->string('to_status');
            $table->string('decision');
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['planning_id', 'version_id']);
            $table->index('reviewer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_reviews');
    }
};
