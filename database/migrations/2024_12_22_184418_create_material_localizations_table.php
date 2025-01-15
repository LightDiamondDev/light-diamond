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
        Schema::create('material_localizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_state_id')
                ->constrained('material_states')
                ->cascadeOnDelete();
            $table->string('language');
            $table->string('cover');
            $table->string('title');
            $table->string('description');
            $table->text('content');

            $table->index(['material_state_id', 'title', 'description']);
            $table->index(['material_state_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_localizations');
    }
};
