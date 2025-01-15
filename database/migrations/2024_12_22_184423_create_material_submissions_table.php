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
        Schema::create('material_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')
                ->constrained('materials')
                ->cascadeOnDelete();
            $table->foreignId('material_state_id')
                ->nullable()
                ->constrained('material_states')
                ->cascadeOnDelete();
            $table->foreignId('submitter_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('assigned_moderator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('type', ['CREATE', 'UPDATE', 'DELETE']);
            $table->enum('status', ['DRAFT', 'PENDING', 'ACCEPTED', 'REJECTED']);
            $table->timestamps();

            $table->index(['status', 'updated_at']);
            $table->index(['material_id', 'status']);
            $table->index(['submitter_id', 'status', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_submissions');
    }
};
