<?php

use Illuminate\Database\Grammar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Grammar::macro('typeCitext', function (Fluent $column) {
            return 'citext';
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->addColumn('citext', 'username')->unique();
            $table->string('email')->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('role', ['USER', 'MODERATOR', 'ADMIN'])->default('USER')->index();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->index(['role', 'created_at']);
            $table->index(['email', 'email_verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
