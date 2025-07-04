<?php

namespace App\Models;

use App\Models\User;
use App\Models\Servico;
use App\Models\Agendamento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estabelecimento extends Model
{
    use HasFactory;

    // app/Models/Estabelecimento.php
    protected $fillable = [
        'user_id',
        'nome',
        'telefone',
        'ramo',
        'slug',
        'slogan',
        'cor_tema',
        'intervalo_entre_horarios',
        'horarios_funcionamento',
        'limite_por_horario',
        'tempo_minimo_cancelamento',
        'logo_url',
        'imagem_capa_url',
        'meta_pixel_id',
        'google_tag_manager_id'

    ];
    protected $casts = [
        'horarios_funcionamento' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Um estabelecimento tem muitos serviços.
     */
    public function servicos()
    {
        return $this->hasMany(Servico::class);
    }
    public function agendamentos()
    {
        return $this->hasMany(Agendamento::class);
    }
    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    } // Adiciona esta relação
}
