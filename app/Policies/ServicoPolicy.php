<?php

namespace App\Policies;

use App\Models\Servico;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Servico $servico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Servico $servico)
    {
        return $user->estabelecimento->id === $servico->estabelecimento_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Servico $servico): bool
    {
        // Retorna 'true' apenas se o ID do utilizador logado for igual
        // ao user_id do estabelecimento dono deste serviço.
        return $user->id === $servico->estabelecimento->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Servico $servico): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Servico $servico): bool
    {
        return false;
    }
}
