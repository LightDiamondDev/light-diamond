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
        Schema::create('material_file_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')
                ->constrained('material_files')
                ->cascadeOnDelete();
            $table->string('ip');
            $table->timestamps();

            $table->index(['file_id', 'ip']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_file_downloads');
    }
};
