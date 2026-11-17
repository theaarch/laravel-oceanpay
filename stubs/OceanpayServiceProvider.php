<?php

namespace App\Providers;

use App\Actions\Oceanpay\HandlePaymentSucceeded;
use App\Actions\Oceanpay\HandlePayoutFailed;
use App\Actions\Oceanpay\HandlePayoutSucceeded;
use Illuminate\Support\ServiceProvider;
use Theaarch\Oceanpay\Facades\Oceanpay;

class OceanpayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Oceanpay::handlePaymentSucceededUsing(HandlePaymentSucceeded::class);
        Oceanpay::handlePayoutSucceededUsing(HandlePayoutSucceeded::class);
        Oceanpay::handlePayoutFailedUsing(HandlePayoutFailed::class);

        // Oceanpay::useProviderChannelModel(\App\Models\ProviderChannel::class);
    }
}
