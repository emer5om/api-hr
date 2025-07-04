<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'estabelecimento_id',
        'servico_id',
        'cliente_id',
        'data_hora_inicio',
        'data_hora_fim',
        'status',
        'atendimento_inicio',
        'duracao_real_segundos'
    ];
    protected $casts = [
        'atendimento_inicio' => 'datetime',
    ];
    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }
    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    } // Adiciona esta relação
}
