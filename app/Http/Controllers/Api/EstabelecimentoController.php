<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class EstabelecimentoController extends Controller
{
    // Retorna os dados do estabelecimento do utilizador logado
    public function show(Request $request)
    {
        return response()->json($request->user()->estabelecimento);
    }

    // Atualiza os dados do estabelecimento
    public function update(Request $request)
    {
        $estabelecimento = $request->user()->estabelecimento;

        $validated = $request->validate([
            'nome' => ['sometimes', 'string', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'ramo' => ['nullable', 'string', 'max:255'],
            'horarios_funcionamento' => ['sometimes', 'json'],
            // Adicione a validação para os novos campos da aba 'Geral'
            'limite_por_horario' => ['sometimes', 'integer', 'min:1'],
            'intervalo_entre_horarios' => ['sometimes', 'integer', 'min:0'],
            'tempo_minimo_cancelamento' => ['sometimes', 'integer', 'min:0'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:estabelecimentos,slug,' . $estabelecimento->id],
            'slogan' => ['nullable', 'string', 'max:255'],
            'cor_tema' => ['nullable', 'string', 'max:7'],
            // Validação para os uploads de imagem
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'imagem_capa' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'meta_pixel_id' => ['nullable', 'string', 'max:255'],
            'google_tag_manager_id' => ['nullable', 'string', 'max:255'],
        ]);

        // --- A CORREÇÃO ESTÁ AQUI ---
        // Se o campo de horários foi enviado, nós o descodificamos de string para array
        if (isset($validated['horarios_funcionamento'])) {
            $validated['horarios_funcionamento'] = json_decode($validated['horarios_funcionamento'], true);
        }

        // Lógica para upload do Logo
        if ($request->hasFile('logo')) {
            // Apaga o logo antigo, se existir
            if ($estabelecimento->logo_url) {
                Storage::disk('public')->delete($estabelecimento->logo_url);
            }
            // Salva o novo logo e guarda o caminho
            $validated['logo_url'] = $request->file('logo')->store('logos', 'public');
        }

        // Lógica para upload da Imagem de Capa
        if ($request->hasFile('imagem_capa')) {
            if ($estabelecimento->imagem_capa_url) {
                Storage::disk('public')->delete($estabelecimento->imagem_capa_url);
            }
            $validated['imagem_capa_url'] = $request->file('imagem_capa')->store('capas', 'public');
        }

        $estabelecimento->update($validated);
        return response()->json($estabelecimento);
    }
}
