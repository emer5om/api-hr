<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // .../database/migrations/..._create_servicos_table.php
public function up(): void
{
    Schema::create('servicos', function (Blueprint $table) {
        $table->id();
        // Liga o serviço a um estabelecimento
        $table->foreignId('estabelecimento_id')->constrained()->onDelete('cascade');
        $table->string('nome');
        $table->integer('duracao'); // Duração em minutos
        $table->decimal('preco', 8, 2); // Preço com 8 dígitos e 2 casas decimais
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicos');
    }
};
