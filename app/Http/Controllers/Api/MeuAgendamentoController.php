<?php
namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Agendamento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MeuAgendamentoController extends Controller
{
    // Retorna o número de telefone do cliente logado na sessão
    public function me(Request $request)
    {
        if (!$request->session()->has('verified_phone_number')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        return response()->json([
            'telefone' => $request->session()->get('verified_phone_number')
        ]);
    }

    // Retorna a lista de agendamentos de todos os estabelecimentos para o telefone logado
    public function index(Request $request)
    {
        $telefone = $request->session()->get('verified_phone_number');

        $agendamentos = Agendamento::whereHas('cliente', function ($query) use ($telefone) {
            $query->where('telefone', $telefone);
        })
        ->with(['servico', 'estabelecimento']) // Carrega os detalhes do serviço e do estabelecimento
        ->orderBy('data_hora_inicio', 'desc')
        ->get();

        return response()->json($agendamentos);
    }
    public function cancelar(Request $request, Agendamento $agendamento)
    {
        $telefone = $request->session()->get('verified_phone_number');

        // 1. VERIFICAÇÃO DE PERMISSÃO: O telefone na sessão é o mesmo do dono do agendamento?
        if ($agendamento->cliente->telefone !== $telefone) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        // 2. VERIFICAÇÃO DA REGRA DE NEGÓCIO: Ainda pode cancelar?
        $horasMinimas = $agendamento->estabelecimento->tempo_minimo_cancelamento;
        $agora = Carbon::now('America/Sao_Paulo');
        $dataAgendamento = Carbon::parse($agendamento->data_hora_inicio, 'America/Sao_Paulo');

        if ($agora->diffInHours($dataAgendamento, false) < $horasMinimas) {
            return response()->json(['message' => "Não é possível cancelar com menos de {$horasMinimas} horas de antecedência."], 422);
        }

        // 3. SE TUDO ESTIVER OK, ATUALIZA O STATUS
        $agendamento->status = 'cancelado';
        $agendamento->save();

        return response()->json($agendamento->load(['servico', 'estabelecimento']));
    }
}