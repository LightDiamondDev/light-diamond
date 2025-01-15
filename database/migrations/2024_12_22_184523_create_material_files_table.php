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
        Schema::create('material_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')
                ->constrained('material_versions')
                ->cascadeOnDelete();
            $table->string('path')->nullable();
            $table->string('url')->nullable();
            $table->integer('size')->nullable();
            $table->string('extension')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['id', 'deleted_at']);
            $table->index(['version_id', 'deleted_at', 'published_at']);
            $table->index(['version_id', 'path', 'deleted_at', 'published_at']);
            $table->index(['id', 'deleted_at', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_files');
    }
};
