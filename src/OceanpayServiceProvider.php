<?php

namespace Theaarch\Oceanpay;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Theaarch\Oceanpay\Contracts\Factory;
use Theaarch\Oceanpay\Http\Controllers\WebhookController;

class OceanpayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->app->singleton(Factory::class, function (Application $app) {
            return new OceanpayManager($app);
        });

        $this->app->alias(Factory::class, OceanpayManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->configurePublishing();
        $this->registerCommands();
        $this->registerRoutes();
    }

    /**
     * Register the webhook route of provider channels, e.g. `/oceanpay/1/webhook/payment`.
     */
    protected function registerRoutes(): void
    {
        if (! $this->app->make(OceanpayManager::class)->registersRoutes()) {
            return;
        }

        Route::post('oceanpay/{providerChannel}/webhook/{object}', WebhookController::class)
            ->name('oceanpay.webhook');
    }

    protected function configurePublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../stubs/OceanpayServiceProvider.php' => app_path('Providers/OceanpayServiceProvider.php'),

                __DIR__.'/../stubs/HandlePaymentSucceeded.php' => app_path('Actions/Oceanpay/HandlePaymentSucceeded.php'),
                __DIR__.'/../stubs/HandlePayoutSucceeded.php' => app_path('Actions/Oceanpay/HandlePayoutSucceeded.php'),
                __DIR__.'/../stubs/HandlePayoutFailed.php' => app_path('Actions/Oceanpay/HandlePayoutFailed.php'),
            ], 'oceanpay-support');

            $this->publishesMigrations([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'oceanpay-migrations');
        }
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\InstallCommand::class,
            ]);
        }
    }
}
