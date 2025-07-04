<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Estabelecimento;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp', // <-- ADICIONE ESTA LINHA
    ];

    protected $appends = ['setup_complete']; // 1. ADICIONE ESTA PROPRIEDADE


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function estabelecimento()
    {
        return $this->hasOne(Estabelecimento::class);
    }

    public function getSetupCompleteAttribute() // 2. ADICIONE ESTE MÉTODO
    {
        // O '?' previne erros se o estabelecimento ainda não existir
        return $this->estabelecimento?->setup_complete ?? false;
    }
}
