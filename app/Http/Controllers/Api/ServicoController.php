<?php

namespace App\Http\Controllers\Api;

use App\Models\Servico;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class ServicoController extends Controller
{
    use AuthorizesRequests;
    // Este método devolve todos os serviços do estabelecimento do utilizador logado
    public function index(Request $request)
    {
        $servicos = $request->user()->estabelecimento->servicos;

        return response()->json($servicos);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'duracao' => ['required', 'integer', 'min:5'],
            'preco' => ['required', 'numeric', 'min:0'],
        ]);

        $estabelecimento = $request->user()->estabelecimento;

        // 1. Cria o serviço e guarda-o numa variável
        $servico = $estabelecimento->servicos()->create($validated);

        // 2. Atualiza o status do estabelecimento, apenas se ainda não estiver completo
        if (!$estabelecimento->setup_complete) {
            $estabelecimento->setup_complete = true;
            $estabelecimento->save();
        }

        // 3. Retorna o serviço recém-criado como JSON para o frontend
        return response()->json($servico, 201);
    }
    public function destroy(Servico $servico)
    {
        // 1. Verifica se o utilizador logado tem permissão para apagar este serviço
        $this->authorize('delete', $servico);

        // 2. Se a autorização passar, apaga o serviço
        $servico->delete();

        // 3. Retorna uma resposta de sucesso sem conteúdo
        return response()->noContent();
    }
    public function update(Request $request, Servico $servico)
    {
        // 1. Verifica a permissão usando a nossa Policy
        $this->authorize('update', $servico);

        // 2. Valida os novos dados
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'duracao' => ['required', 'integer', 'min:5'],
            'preco' => ['required', 'numeric', 'min:0'],
        ]);

        // 3. Atualiza o serviço com os dados validados
        $servico->update($validated);

        // 4. Retorna o serviço atualizado
        return response()->json($servico);
    }
}
