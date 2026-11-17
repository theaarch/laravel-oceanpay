<?php

namespace Theaarch\Oceanpay\Console;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use Theaarch\Oceanpay\OceanpayServiceProvider;

class InstallCommand extends Command
{
    protected $signature = 'oceanpay:install';

    protected $description = 'Install Oceanpay';

    public function handle(): void
    {
        $this->callSilent('vendor:publish', [
            '--provider' => OceanpayServiceProvider::class,
        ]);

        $this->registerApplicationServiceProvider();

        $this->components->info('Oceanpay scaffolding installed successfully.');
    }

    /**
     * Register the published `App\Providers\OceanpayServiceProvider` in `bootstrap/providers.php`.
     */
    protected function registerApplicationServiceProvider(): void
    {
        if (! method_exists(ServiceProvider::class, 'addProviderToBootstrapFile')) {
            return;
        }

        ServiceProvider::addProviderToBootstrapFile('App\\Providers\\OceanpayServiceProvider');
    }
}
