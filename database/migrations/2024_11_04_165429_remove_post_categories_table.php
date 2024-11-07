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
        Schema::table('post_versions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            $table->enum(
                'category',
                [
                    'ARTICLES', 'SKINS', 'BEDROCK_RESOURCE_PACKS', 'BEDROCK_ADDONS', 'BEDROCK_MAPS',
                    'JAVA_RESOURCE_PACKS', 'JAVA_DATA_PACKS', 'JAVA_MODS', 'JAVA_MAPS'
                ]
            )
                ->default('ARTICLES')
                ->after('post_id');;
        });

        Schema::dropIfExists('post_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->enum('edition', ['BEDROCK', 'JAVA'])->nullable()->default(null);
            $table->boolean('is_article')->default(false);
            $table->timestamps();
        });

        Schema::table('post_versions', function (Blueprint $table) {
            $table->dropColumn('category');

            $table->foreignId('category_id')
                ->after('post_id')
                ->constrained('post_categories')
                ->cascadeOnDelete();
        });
    }
};
