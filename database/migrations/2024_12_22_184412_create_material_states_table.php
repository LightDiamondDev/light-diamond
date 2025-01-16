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
        Schema::create('material_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')
                ->constrained('materials')
                ->cascadeOnDelete();
            $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['material_id', 'published_at', 'id']);
            $table->index(['material_id', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_states');
    }
};
