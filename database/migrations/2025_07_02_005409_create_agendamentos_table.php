<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ..._create_agendamentos_table.php
public function up(): void
{
    Schema::create('agendamentos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('estabelecimento_id')->constrained()->onDelete('cascade');
        $table->foreignId('servico_id')->constrained()->onDelete('cascade');
        // Futuramente, poderemos ligar a um utilizador cliente
        // $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

        // Dados do cliente que agendou
        $table->string('nome_cliente');
        $table->string('contato_cliente'); // Pode ser email ou telefone

        // Data e hora do agendamento
        $table->dateTime('data_hora_inicio');
        $table->dateTime('data_hora_fim');

        $table->enum('status', ['pendente', 'confirmado', 'cancelado', 'concluido'])->default('pendente');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
