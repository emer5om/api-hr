<?php



use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;



// Importações dos Controllers

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClienteController;

use App\Http\Controllers\Api\ServicoController;

use App\Http\Controllers\Api\DashboardController;

use App\Http\Controllers\Public\AgendaController;

use App\Http\Controllers\Api\AgendamentoController;

use App\Http\Controllers\Api\MeuAgendamentoController;
use App\Http\Controllers\Api\EstabelecimentoController;
use App\Http\Controllers\Public\ClienteLoginController;
use App\Http\Controllers\Public\ClienteController as PublicClienteController; // Ponto e vírgula corrigido



/*

|--------------------------------------------------------------------------

| Rotas Protegidas (Exigem Autenticação)

|--------------------------------------------------------------------------

*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', fn(Request $request) => $request->user());



    // Rotas de Estabelecimento

    Route::get('/estabelecimento', [EstabelecimentoController::class, 'show']);

    Route::put('/estabelecimento', [EstabelecimentoController::class, 'update']);



    // Rotas de CRUD para Serviços e Clientes (usando apiResource para simplificar)

    Route::apiResource('servicos', ServicoController::class);


    Route::get('/clientes/stats', [ClienteController::class, 'getStats']);

    Route::apiResource('clientes', ClienteController::class);





    // Rotas de Agendamentos do Painel

    Route::get('/agendamentos', [AgendamentoController::class, 'index']);

    Route::get('/agenda/{data}', [AgendamentoController::class, 'getAgendaDoDia']);

    Route::patch('/agendamentos/{agendamento}', [AgendamentoController::class, 'update']);
    Route::post('/agendamentos', [AgendamentoController::class, 'store']); // <-- ADICIONE ESTA LINHA



    // Rotas do Dashboard

    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);

    Route::get('/dashboard/revenue-chart', [DashboardController::class, 'getRevenueChartData']);

    Route::get('/dashboard/services-chart', [DashboardController::class, 'getServicesChartData']);
    Route::patch('/cliente-final/meus-agendamentos/{agendamento}/cancelar', [MeuAgendamentoController::class, 'cancelar']);

});

Route::get('/admin/me', function (Request $request) {
    // Tenta buscar o utilizador usando o guarda 'admin'
    $admin = Auth::guard('admin')->user();
    return $admin ? response()->json($admin) : response()->json(null, 204);
});




/*

|--------------------------------------------------------------------------

| Rotas Públicas (NÃO Exigem Autenticação)

|--------------------------------------------------------------------------

*/

Route::get('/public/estabelecimentos/{slug}', [AgendaController::class, 'getEstabelecimentoPorSlug']);

Route::get('/public/horarios/{estabelecimento_id}/{servico_id}/{data}', [AgendaController::class, 'getHorariosDisponiveis']);

Route::post('/public/agendamentos', [AgendaController::class, 'storeAgendamento']);

Route::get('/public/agendamentos/{id}', [AgendaController::class, 'showAgendamento']);

Route::get('/public/clientes/search', [PublicClienteController::class, 'search']);
Route::get('/public/agendamentos/{agendamento}/ics', [AgendaController::class, 'gerarIcs']);

Route::post('/public/cliente/login', [ClienteLoginController::class, 'login']);
Route::post('/public/cliente/logout', [ClienteLoginController::class, 'logout']);
Route::get('/cliente-final/me', [MeuAgendamentoController::class, 'me']);
Route::get('/cliente-final/meus-agendamentos', [MeuAgendamentoController::class, 'index']);