<?php

namespace App\Models;

use App\Models\Estabelecimento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// app/Models/Servico.php
class Servico extends Model
{
    use HasFactory;

    protected $fillable = ['estabelecimento_id', 'nome', 'duracao', 'preco'];

    public function estabelecimento()
    {
        return $this->belongsTo(Estabelecimento::class);
    }
}