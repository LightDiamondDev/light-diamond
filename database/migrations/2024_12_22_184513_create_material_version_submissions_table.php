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
        Schema::create('material_version_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')
                ->constrained('material_versions')
                ->cascadeOnDelete();
            $table->foreignId('material_submission_id')
                ->constrained('material_submissions')
                ->cascadeOnDelete();
            $table->foreignId('version_state_id')
                ->nullable()
                ->constrained('material_version_states')
                ->cascadeOnDelete();
            $table->enum('type', ['CREATE', 'UPDATE', 'DELETE']);

            $table->index(['material_submission_id', 'id']);
            $table->index(['version_state_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_version_submissions');
    }
};
