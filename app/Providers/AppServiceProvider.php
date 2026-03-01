<?php

namespace App\Providers;

use App\Domain\Tickets\Contracts\TicketRepository;
use App\Infrastructure\Persistence\Repositories\EloquentTicketRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TicketRepository::class, EloquentTicketRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
