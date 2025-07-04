<?php

namespace App\Http\Controllers\Api;

use App\Models\Agendamento;
use App\Models\Servico; // Importe o modelo Servico
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon; // Importe o Carbon

class AgendamentoController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $agendamentos = $request->user()->estabelecimento
            ->agendamentos()
            ->with(['servico', 'cliente'])
            ->orderBy('data_hora_inicio', 'desc') // Ordena pelos mais recentes
            ->get();

        return response()->json($agendamentos);
    }
    public function getAgendaDoDia(Request $request, string $data)
    {
        $agendamentos = $request->user()->estabelecimento
            ->agendamentos()
            // CORREÇÃO: Limpado para ter apenas uma chamada 'with'
            ->with(['servico', 'cliente'])
            ->whereDate('data_hora_inicio', $data)
            ->whereIn('status', ['pendente', 'confirmado', 'em_atendimento'])
            ->orderBy('data_hora_inicio', 'asc')
            ->get();

        return response()->json($agendamentos);
    }
    public function update(Request $request, Agendamento $agendamento)
    {
        $this->authorize('update', $agendamento);

        $validated = $request->validate([
            'status' => ['sometimes', 'in:pendente,confirmado,cancelado,concluido,em_atendimento'],
            'duracao_real_segundos' => ['sometimes', 'integer'],
        ]);

        // Se o status for 'em_atendimento', guarda a hora atual
        if ($request->status === 'em_atendimento') {
            $validated['atendimento_inicio'] = now();
        }

        $agendamento->update($validated);

        return response()->json($agendamento->load(['servico', 'cliente']));
    }
    public function store(Request $request)
    {
        $this->authorize('create', Agendamento::class);

        $validated = $request->validate([
            'servico_id' => ['required', 'exists:servicos,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'data_hora_inicio' => ['required', 'date_format:Y-m-d H:i'],
        ]);

        $servico = Servico::findOrFail($validated['servico_id']);
        $dataHoraInicio = Carbon::parse($validated['data_hora_inicio']);
        $dataHoraFim = $dataHoraInicio->copy()->addMinutes($servico->duracao);

        $agendamento = $request->user()->estabelecimento->agendamentos()->create([
            'servico_id' => $validated['servico_id'],
            'cliente_id' => $validated['cliente_id'],
            'data_hora_inicio' => $validated['data_hora_inicio'],
            'data_hora_fim' => $dataHoraFim,
            'status' => 'confirmado', // Agendamentos manuais já entram como confirmados
        ]);

        return response()->json($agendamento->load(['servico', 'cliente']), 201);
    }
}
