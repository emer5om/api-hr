<?php

namespace App\Providers;

use App\Models\Cliente;
use App\Models\Servico;
use App\Models\Agendamento;
use App\Policies\ClientePolicy;
use App\Policies\ServicoPolicy;
use App\Policies\AgendamentoPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
        Gate::policy(Servico::class, ServicoPolicy::class);
        Gate::policy(Agendamento::class, AgendamentoPolicy::class); // Adicione esta linha
        Gate::policy(Cliente::class, ClientePolicy::class);


    }
}
