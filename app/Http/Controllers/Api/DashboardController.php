<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Retorna um conjunto completo de estatísticas para o painel principal.
     */
    public function getStats(Request $request)
    {
        $estabelecimento = $request->user()->estabelecimento;
        $tz = 'America/Sao_Paulo';
        $startDate = Carbon::parse($request->query('start_date', now()->startOfMonth()), $tz)->startOfDay();
        $endDate = Carbon::parse($request->query('end_date', now()->endOfMonth()), $tz)->endOfDay();

        // --- 1. CÁLCULO DE TAXA DE OCUPAÇÃO (LÓGICA NOVA E PRECISA) ---
        $totalMinutosDeTrabalho = 0;
        $horariosFuncionamento = $estabelecimento->horarios_funcionamento;
        $mapaDias = [0 => 'domingo', 1 => 'segunda', 2 => 'terca', 3 => 'quarta', 4 => 'quinta', 5 => 'sexta', 6 => 'sabado'];

        // Itera sobre cada dia no período selecionado
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $diaDaSemanaKey = $mapaDias[$date->dayOfWeek];
            if (isset($horariosFuncionamento[$diaDaSemanaKey]) && $horariosFuncionamento[$diaDaSemanaKey]['ativo']) {
                foreach ($horariosFuncionamento[$diaDaSemanaKey]['horarios'] as $intervalo) {
                    $inicio = Carbon::parse($intervalo['inicio']);
                    $fim = Carbon::parse($intervalo['fim']);
                    // Soma os minutos de trabalho de cada intervalo de funcionamento
                    $totalMinutosDeTrabalho += $inicio->diffInMinutes($fim);
                }
            }
        }

        // O total de minutos "vendáveis" é o total de minutos de trabalho x o limite de vagas por horário.
        $totalMinutosVendaveis = $totalMinutosDeTrabalho * $estabelecimento->limite_por_horario;

        // --- CÁLCULO DE HORAS JÁ AGENDADAS ---
        $agendamentosNoPeriodo = $estabelecimento->agendamentos()
            ->whereBetween('data_hora_inicio', [$startDate, $endDate]);

        $minutosAgendados = $agendamentosNoPeriodo->clone()
            ->whereIn('status', ['confirmado', 'pendente', 'em_atendimento', 'concluido'])
            ->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->sum('servicos.duracao');

        // A taxa de ocupação é a porcentagem de minutos agendados em relação aos minutos "vendáveis".
        $taxaOcupacao = $totalMinutosVendaveis > 0 ? ($minutosAgendados / $totalMinutosVendaveis) * 100 : 0;

        // --- 2. CÁLCULOS GERAIS DE AGENDAMENTOS ---
        $totalAgendamentos = $agendamentosNoPeriodo->clone()->count();
        $agendamentosConcluidosQuery = $agendamentosNoPeriodo->clone()->where('status', 'concluido');
        $totalConcluidos = $agendamentosConcluidosQuery->count();

        $diffInDays = $endDate->diffInDays($startDate);
        $prevEndDate = $startDate->copy()->subDay();
        $prevStartDate = $prevEndDate->copy()->subDays($diffInDays);
        $agendamentosPeriodoAnterior = $estabelecimento->agendamentos()->whereBetween('data_hora_inicio', [$prevStartDate, $prevEndDate])->count();
        $totalPendentes = $agendamentosNoPeriodo->clone()->where('status', 'pendente')->count();

        // --- 3. CÁLCULOS DE RECEITA ---
        $receitaPeriodo = $agendamentosConcluidosQuery->clone()->with('servico')->get()->sum(fn($ag) => $ag->servico->preco);
        $ticketMedio = $totalConcluidos > 0 ? $receitaPeriodo / $totalConcluidos : 0;

        $faturamentoPorStatus = $agendamentosNoPeriodo->clone()->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')->select('status', DB::raw('SUM(servicos.preco) as total'))->whereIn('status', ['concluido', 'pendente', 'confirmado', 'cancelado'])->groupBy('status')->get()->mapWithKeys(fn($item) => [$item->status => $item->total]);

        // --- 4. CÁLCULOS DE CLIENTES ---
        $totalClientesGeral = $estabelecimento->clientes()->count();
        $aniversariosMes = $estabelecimento->clientes()->whereMonth('data_nascimento', date('m'))->count();

        // --- 5. RETORNO COMPLETO ---
        return response()->json([
            'taxa_ocupacao' => min(100, $taxaOcupacao),
            'total_agendamentos' => $totalAgendamentos,
            'agendamentos_periodo_anterior' => $agendamentosPeriodoAnterior,
            'total_concluidos' => $totalConcluidos,
            'total_pendentes' => $totalPendentes, // <-- NOVO DADO
            'total_cancelados' => $agendamentosNoPeriodo->clone()->where('status', 'cancelado')->count(),
            'receita_periodo' => $receitaPeriodo,
            'ticket_medio' => $ticketMedio,
            'total_clientes_geral' => $totalClientesGeral,
            'aniversarios_mes' => $aniversariosMes,
            'faturamento_por_status' => $faturamentoPorStatus,
        ]);
    }

    /**
     * Retorna dados para o gráfico de receita diária.
     */
    public function getRevenueChartData(Request $request)
    {
        $estabelecimento = $request->user()->estabelecimento;
        $startDate = Carbon::parse($request->query('start_date', now()->subDays(30)))->startOfDay();
        $endDate = Carbon::parse($request->query('end_date', now()))->endOfDay();

        $revenueData = $estabelecimento->agendamentos()
            ->where('status', 'concluido')
            ->whereBetween('data_hora_inicio', [$startDate, $endDate])
            ->select(DB::raw('DATE(data_hora_inicio) as date'), DB::raw('SUM(servicos.preco) as total'))
            ->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($revenueData);
    }

    /**
     * Retorna dados para o gráfico de serviços populares.
     */
    public function getServicesChartData(Request $request)
    {
        $estabelecimento = $request->user()->estabelecimento;
        $startDate = Carbon::parse($request->query('start_date', now()->subDays(30)))->startOfDay();
        $endDate = Carbon::parse($request->query('end_date', now()))->endOfDay();

        $serviceData = $estabelecimento->agendamentos()
            ->whereBetween('data_hora_inicio', [$startDate, $endDate])
            ->join('servicos', 'agendamentos.servico_id', '=', 'servicos.id')
            ->select('servicos.nome as name', DB::raw('count(agendamentos.id) as total'))
            ->groupBy('servicos.nome')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return response()->json($serviceData);
    }
}
