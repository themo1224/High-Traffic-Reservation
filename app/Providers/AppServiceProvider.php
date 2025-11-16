<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\DbEventRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, DbEventRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
