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
        Schema::create('material_submission_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')
                ->constrained('material_submissions')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('type', ['SUBMIT', 'REQUEST_CHANGES', 'ACCEPT', 'REJECT', 'ASSIGN_MODERATOR', 'RECONSIDER', 'MESSAGE']);
            $table->json('details');
            $table->timestamps();

            $table->index(['submission_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_submission_actions');
    }
};
