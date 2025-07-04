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
        Schema::table('agendamentos', function (Blueprint $table) {
        // Adiciona a nova coluna que liga ao cliente
        $table->foreignId('cliente_id')->after('servico_id')->constrained()->onDelete('cascade');

        // Apaga as colunas antigas que não são mais necessárias
        $table->dropColumn(['nome_cliente', 'contato_cliente', 'email_cliente']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            //
        });
    }
};
