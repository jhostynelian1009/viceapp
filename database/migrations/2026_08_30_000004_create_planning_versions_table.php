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
        Schema::create('planning_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_id')->constrained('plannings')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->string('file_path');
            $table->string('file_disk')->default('private_plannings');
            $table->string('original_name');
            $table->string('mime');
            $table->unsignedBigInteger('size');
            $table->string('checksum', 64);
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['planning_id', 'version']);
            $table->index(['planning_id', 'version']);
            $table->index('checksum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planning_versions');
    }
};
