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
        Schema::create('material_version_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')
                ->constrained('material_versions')
                ->cascadeOnDelete();
            $table->string('number');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['version_id', 'published_at']);
            $table->index(['version_id', 'published_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_version_states');
    }
};
