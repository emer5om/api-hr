<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'telefone' => ['required'],
            'estabelecimento_id' => ['required', 'exists:estabelecimentos,id'],
        ]);

        // Busca um cliente com o telefone E o ID do estabelecimento
        $cliente = Cliente::where('telefone', $validated['telefone'])
                          ->where('estabelecimento_id', $validated['estabelecimento_id'])
                          ->first();

        // Se não encontrar, retorna uma resposta 'Not Found'
        if (!$cliente) {
            return response()->json(['message' => 'Cliente não encontrado'], 404);
        }

        // Se encontrar, retorna os dados do cliente
        return response()->json($cliente);
    }
}