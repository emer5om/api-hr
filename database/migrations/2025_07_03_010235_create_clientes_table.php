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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estabelecimento_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            $table->string('sobrenome');
            $table->string('email')->nullable();
            $table->string('telefone');
            $table->timestamps();

            // Garante que um mesmo número de telefone só pode ser cadastrado uma vez por estabelecimento
            $table->unique(['estabelecimento_id', 'telefone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
