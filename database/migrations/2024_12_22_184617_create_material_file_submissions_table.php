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
        Schema::create('material_file_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')
                ->constrained('material_files')
                ->cascadeOnDelete();
            $table->foreignId('version_submission_id')
                ->constrained('material_version_submissions')
                ->cascadeOnDelete();
            $table->foreignId('file_state_id')
                ->nullable()
                ->constrained('material_file_states')
                ->cascadeOnDelete();
            $table->enum('type', ['CREATE', 'UPDATE', 'DELETE']);

            $table->index(['version_submission_id', 'id']);
            $table->index(['file_state_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_file_submissions');
    }
};
