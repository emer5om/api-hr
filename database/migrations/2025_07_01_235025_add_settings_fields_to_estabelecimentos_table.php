<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // ..._add_settings_fields_to_estabelecimentos_table.php
public function up(): void
{
    Schema::table('estabelecimentos', function (Blueprint $table) {
        // Aba: Empresa
        $table->string('telefone')->nullable()->after('nome');
        $table->string('ramo')->nullable()->after('telefone');
        // Aba: Página de Agendamento
        $table->string('slug')->unique()->nullable()->after('ramo');
        $table->string('slogan')->nullable()->after('slug');
        $table->string('cor_tema')->nullable()->after('slogan');
        // Aba: Geral
        $table->integer('intervalo_entre_horarios')->default(15)->after('cor_tema');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estabelecimentos', function (Blueprint $table) {
            //
        });
    }
};
