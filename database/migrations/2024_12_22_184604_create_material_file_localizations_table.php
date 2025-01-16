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
        Schema::create('material_file_localizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_state_id')
                ->constrained('material_file_states')
                ->cascadeOnDelete();
            $table->string('language');
            $table->string('name');

            $table->index(['file_state_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_file_localizations');
    }
};
