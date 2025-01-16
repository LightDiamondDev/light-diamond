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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->enum(
                'category',
                [
                    'ARTICLES', 'SKINS', 'BEDROCK_RESOURCE_PACKS', 'BEDROCK_ADDONS', 'BEDROCK_MAPS',
                    'JAVA_RESOURCE_PACKS', 'JAVA_DATA_PACKS', 'JAVA_MODS', 'JAVA_MAPS'
                ]
            );
            $table->enum('edition', ['BEDROCK', 'JAVA'])->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('downloads_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['deleted_at', 'published_at']);
            $table->index(['category', 'deleted_at', 'published_at']);
            $table->index(['edition', 'deleted_at', 'published_at']);
            $table->index(['category', 'edition', 'deleted_at', 'published_at']);
            $table->index(['slug', 'category', 'edition', 'deleted_at', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
