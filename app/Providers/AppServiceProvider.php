<?php

namespace App\Providers;

use App\Events\LowStockDetected;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Listeners\SendLowStockNotification;
// use Illuminate\Database\Console\Seeds\SeedCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCommands();
        $this->configureModels();
        $this->configureUrl();

        Event::listen(
            LowStockDetected::class,
            SendLowStockNotification::class,
        );
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(
            $this->app->environment('production'),
        );

        // SeedCommand::prohibit();
    }

    /**
     * Configure the application's models.
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict();

        // Model::automaticallyEagerLoadRelationships();
    }

    /**
     * Configure the application's URL.
     */
    private function configureUrl(): void
    {
        if (config('app.env') != 'local') {
            $this->app['request']->server->set('HTTPS', true);
        }
    }
}
