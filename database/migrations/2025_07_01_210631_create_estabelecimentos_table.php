<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // .../database/migrations/..._create_estabelecimentos_table.php
    public function up(): void
    {
        Schema::create('estabelecimentos', function (Blueprint $table) {
            $table->id();
            // Chave estrangeira que liga ao utilizador dono
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            // Adicionaremos mais campos (slug, etc.) no futuro
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estabelecimentos');
    }
};
