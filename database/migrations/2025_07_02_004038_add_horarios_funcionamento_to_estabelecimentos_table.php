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
    Schema::table('estabelecimentos', function (Blueprint $table) {
        // Coluna JSON para guardar os horários de cada dia da semana
        $table->json('horarios_funcionamento')->nullable()->after('intervalo_entre_horarios');
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
