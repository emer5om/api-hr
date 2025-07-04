<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 1. IMPORTE O TRAIT

class ClienteController extends Controller
{
    // O MÉTODO __construct FOI REMOVIDO DAQUI
    use AuthorizesRequests;

    public function index(Request $request)
    {
        return $request->user()->estabelecimento->clientes()
                    ->with(['ultimoAtendimento', 'proximoAgendamento', 'historicoStatus'])
                    ->orderBy('nome')
                    ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'sobrenome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['required', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
        ]);
        
        $cliente = $request->user()->estabelecimento->clientes()->create($validated);

        return response()->json($cliente, 201);
    }

    public function update(Request $request, Cliente $cliente)
    {
        // Adicionamos a autorização manualmente aqui
        $this->authorize('update', $cliente);

        $validated = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'sobrenome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['required', 'string', 'max:255'],
            'data_nascimento' => ['nullable', 'date'],
        ]);
        
        $cliente->update($validated);
        return response()->json($cliente);
    }
    public function getStats(Request $request)
    {
        $estabelecimento = $request->user()->estabelecimento;
        $clientesQuery = $estabelecimento->clientes();

        // 1. Nº Total de Clientes
        $totalClientes = $clientesQuery->clone()->count();

        // 2. Aniversariantes do Mês Atual
        $aniversariosMes = $clientesQuery->clone()
            ->whereMonth('data_nascimento', '=', date('m'))
            ->count();

        // 3. Cliente do Mês (simplificado como o cliente com mais agendamentos concluídos)
        $clienteDoMes = Cliente::withCount(['agendamentos' => function($query) {
            $query->where('status', 'concluido');
        }])
        ->where('estabelecimento_id', $estabelecimento->id)
        ->orderBy('agendamentos_count', 'desc')
        ->first();

        return response()->json([
            'total_clientes' => $totalClientes,
            'aniversarios_mes' => $aniversariosMes,
            'cliente_do_mes' => $clienteDoMes ? [
                'nome' => $clienteDoMes->nome . ' ' . $clienteDoMes->sobrenome,
                'agendamentos' => $clienteDoMes->agendamentos_count
            ] : null,
        ]);
    }
    public function show(Cliente $cliente)
    {
        // A autorização é tratada pela policy 'view' que já criámos.
        $this->authorize('view', $cliente);
        
        return response()->json($cliente);
    }
}