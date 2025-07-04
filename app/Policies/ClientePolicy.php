<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Cliente $cliente): bool
    {
        return $user->id === $cliente->estabelecimento->user_id;
    }

    /**
     * ADICIONE ESTE MÉTODO
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Permite que qualquer utilizador logado crie clientes.
        // A lógica que garante que ele só crie para o seu próprio estabelecimento
        // já está no controller.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Cliente $cliente): bool
    {
        return $user->id === $cliente->estabelecimento->user_id;
    }
}