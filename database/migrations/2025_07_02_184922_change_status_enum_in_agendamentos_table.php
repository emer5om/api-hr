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
            // Modifica a coluna para adicionar o novo status
            $table->enum('status', [
                'pendente',
                'confirmado',
  'em_atendimento', // Novo status adicionado
                'cancelado',
                'concluido'
            ])->default('pendente')->change();
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
