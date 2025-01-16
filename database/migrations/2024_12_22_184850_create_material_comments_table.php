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
        Schema::create('material_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')
                ->constrained('material_versions')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('parent_comment_id')
                ->nullable()
                ->constrained('material_comments')
                ->cascadeOnDelete();
            $table->text('content');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['version_id', 'deleted_at']);
            $table->index(['version_id', 'deleted_at', 'created_at']);
            $table->index(['id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_comments');
    }
};
