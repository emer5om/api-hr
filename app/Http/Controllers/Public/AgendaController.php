<?php

namespace App\Http\Controllers\Public;

use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Servico;
use App\Models\Agendamento;
use Illuminate\Http\Request;
use App\Models\Estabelecimento;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class AgendaController extends Controller
{
    // Em app/Http/Controllers/Public/AgendaController.php
    // Em app/Http/Controllers/Public/AgendaController.php

    public function getHorariosDisponiveis($estabelecimento_id, $servico_id, $data)
    {
        try {
            $estabelecimento = Estabelecimento::findOrFail($estabelecimento_id);
            $servico = Servico::findOrFail($servico_id);

            // --- INÍCIO DA CORREÇÃO DE FUSO HORÁRIO ---
            $tz = 'America/Sao_Paulo'; // Define o fuso horário de referência

            $dataCarbon = Carbon::parse($data, $tz)->startOfDay();
            $agora = Carbon::now($tz); // Pega a data e hora atuais no fuso horário correto
            // --- FIM DA CORREÇÃO ---

            $diaDaSemanaIndex = $dataCarbon->dayOfWeek;
            $mapaDias = [0 => 'domingo', 1 => 'segunda', 2 => 'terca', 3 => 'quarta', 4 => 'quinta', 5 => 'sexta', 6 => 'sabado'];
            $diaDaSemanaKey = $mapaDias[$diaDaSemanaIndex];

            $horariosDoDia = $estabelecimento->horarios_funcionamento[$diaDaSemanaKey] ?? null;
            if (!$horariosDoDia || !$horariosDoDia['ativo']) {
                return response()->json([]);
            }

            $agendamentosDoDia = $estabelecimento->agendamentos()
                ->whereDate('data_hora_inicio', $dataCarbon)
                ->get()->groupBy(fn($ag) => Carbon::parse($ag->data_hora_inicio)->format('H:i'))->map->count();

            $slots = [];
            $duracaoServico = $servico->duracao;
            foreach ($horariosDoDia['horarios'] as $intervalo) {
                // Cria os horários de início e fim também no fuso horário correto
                $inicio = Carbon::parse($data . ' ' . $intervalo['inicio'], $tz);
                $fim = Carbon::parse($data . ' ' . $intervalo['fim'], $tz);

                while ($inicio->copy()->addMinutes($duracaoServico) <= $fim) {
                    $slotAtual = $inicio->format('H:i');
                    $disponivel = true;

                    // A comparação agora é feita com ambos os tempos no mesmo fuso horário
                    if ($dataCarbon->isToday($tz) && $inicio < $agora) {
                        $disponivel = false;
                    }

                    if ($disponivel && ($agendamentosDoDia->get($slotAtual, 0) >= $estabelecimento->limite_por_horario)) {
                        $disponivel = false;
                    }

                    $slots[] = ['horario' => $slotAtual, 'disponivel' => $disponivel];
                    $inicio->addMinutes($duracaoServico);
                }
            }

            return response()->json($slots);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar horários: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
    public function getEstabelecimentoPorSlug(string $slug)
    {
        // Busca o estabelecimento pelo 'slug' e já carrega os seus serviços junto
        $estabelecimento = Estabelecimento::where('slug', $slug)->with('servicos')->firstOrFail();
        return response()->json($estabelecimento);
    }
    public function storeAgendamento(Request $request)
    {
        $validated = $request->validate([
            'estabelecimento_id' => ['required', 'exists:estabelecimentos,id'],
            'servico_id' => ['required', 'exists:servicos,id'],
            'data_hora_inicio' => ['required', 'date_format:Y-m-d H:i'],
            'nome' => ['required', 'string', 'max:255'],
            'sobrenome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefone' => ['required', 'string', 'max:255'],
        ]);

        // 1. ENCONTRA O CLIENTE PELO TELEFONE OU CRIA UM NOVO
        // O método updateOrCreate é perfeito para isso:
        // - Ele procura um cliente com este telefone NESTE estabelecimento.
        // - Se encontrar, atualiza os dados dele (nome, email, etc.).
        // - Se não encontrar, cria um novo com todos os dados.
        $cliente = Cliente::updateOrCreate(
            [
                'estabelecimento_id' => $validated['estabelecimento_id'],
                'telefone' => $validated['telefone'],
            ],
            [
                'nome' => $validated['nome'],
                'sobrenome' => $validated['sobrenome'],
                'email' => $validated['email'],
            ]
        );

        // 2. CALCULA A DATA DE FIM
        $servico = Servico::findOrFail($validated['servico_id']);
        $dataHoraInicio = Carbon::parse($validated['data_hora_inicio']);
        $dataHoraFim = $dataHoraInicio->copy()->addMinutes($servico->duracao);

        // 3. CRIA O AGENDAMENTO USANDO O ID DO CLIENTE
        $agendamento = Agendamento::create([
            'estabelecimento_id' => $validated['estabelecimento_id'],
            'servico_id' => $validated['servico_id'],
            'cliente_id' => $cliente->id, // Usa o ID do cliente encontrado ou criado
            'data_hora_inicio' => $validated['data_hora_inicio'],
            'data_hora_fim' => $dataHoraFim,
        ]);

        return response()->json(['id' => $agendamento->id], 201);
    }
    public function showAgendamento($id)
    {
        // Busca o agendamento pelo ID e já carrega as informações
        // do serviço e do estabelecimento relacionadas a ele.
        // O findOrFail irá automaticamente retornar um erro 404 se não encontrar.
        $agendamento = Agendamento::with(['servico', 'estabelecimento'])->findOrFail($id);

        return response()->json($agendamento);
    }
    // NOVO MÉTODO PARA GERAR O ARQUIVO .ICS
    public function gerarIcs(Agendamento $agendamento)
    {
        // Carrega as relações para ter acesso aos nomes
        $agendamento->load(['servico', 'estabelecimento']);

        // Formata as datas para o formato UTC exigido pelo padrão iCalendar
        $inicioUTC = Carbon::parse($agendamento->data_hora_inicio)->utc()->format('Ymd\THis\Z');
        $fimUTC = Carbon::parse($agendamento->data_hora_fim)->utc()->format('Ymd\THis\Z');

        // Monta o conteúdo do arquivo .ics
        $icsContent = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Horaly//Agendamento//PT',
            'BEGIN:VEVENT',
            'UID:' . md5($agendamento->id),
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART:' . $inicioUTC,
            'DTEND:' . $fimUTC,
            'SUMMARY:' . $agendamento->servico->nome . ' em ' . $agendamento->estabelecimento->nome,
            'DESCRIPTION:' . 'Seu agendamento para ' . $agendamento->servico->nome . ' está confirmado.',
            'END:VEVENT',
            'END:VCALENDAR'
        ];

        $icsContent = implode("\r\n", $icsContent);

        // Retorna o conteúdo como um arquivo para download
        return response($icsContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="agendamento.ics"');
    }
}
