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
        Schema::create('post_version_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_version_id')
                ->constrained('post_versions')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('path')->nullable();
            $table->string('url')->nullable();
            $table->integer('size')->nullable();
            $table->string('extension')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_version_files');
    }
};
