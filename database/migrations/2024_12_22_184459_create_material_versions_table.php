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
        Schema::create('material_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')
                ->constrained('materials')
                ->cascadeOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['material_id', 'published_at']);
            $table->index(['material_id', 'deleted_at', 'published_at']);
            $table->index(['deleted_at', 'published_at', 'material_id']);
            $table->index(['id', 'material_id', 'deleted_at']);
            $table->index(['id', 'deleted_at', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_versions');
    }
};
