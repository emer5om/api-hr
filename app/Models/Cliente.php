<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use HasFactory;
    protected $fillable = ['estabelecimento_id', 'nome', 'sobrenome', 'email', 'telefone', 'data_nascimento'];

    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }
    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }
    public function ultimoAtendimento()
    {
        return $this->hasOne(Agendamento::class)
            ->where('status', 'concluido')
            ->latest('data_hora_inicio');
    }

    /**
     * Pega o próximo agendamento PENDENTE ou CONFIRMADO.
     */
    public function proximoAgendamento()
    {
        return $this->hasOne(Agendamento::class)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->oldest('data_hora_inicio');
    }

    /**
     * Agrupa e conta os status dos agendamentos para o histórico.
     */
    public function historicoStatus()
    {
        return $this->hasMany(Agendamento::class)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status');
    }
}
