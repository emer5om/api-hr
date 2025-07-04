<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClienteLoginController extends Controller
{
    /**
     * Inicia uma sessão para um cliente com base no número de telefone.
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'telefone' => ['required', 'string'],
        ]);

        // Verifica se existe PELO MENOS um cliente com este telefone na base de dados
        $clienteExiste = Cliente::where('telefone', $validated['telefone'])->exists();

        if (!$clienteExiste) {
            // Se não existe, retorna um erro de validação
            throw ValidationException::withMessages([
                'telefone' => 'Este número de telefone não foi encontrado na nossa base de dados.',
            ]);
        }

        // Inicia uma sessão segura, guardando o telefone verificado
        $request->session()->regenerate();
        $request->session()->put('verified_phone_number', $validated['telefone']);

        // Retorna uma resposta de sucesso
        return response()->noContent();
    }

    /**
     * Destrói a sessão do cliente.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('verified_phone_number');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}