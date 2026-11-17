<?php

namespace Theaarch\Oceanpay\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use Theaarch\Oceanpay\Contracts\Factory;
use Theaarch\Oceanpay\Contracts\Provider;
use Theaarch\Oceanpay\Contracts\ProviderChannel;
use Theaarch\Oceanpay\OceanpayManager;
use Theaarch\Oceanpay\Providers\AbstractProvider;

/**
 * @method static Provider driver(string|null $driver = null)
 * @method static Provider with(string $driver)
 * @method static Provider channel(ProviderChannel $channel)
 * @method static Provider payments(ProviderChannel $channel)
 * @method static Provider payouts(ProviderChannel $channel)
 * @method static Provider balances(ProviderChannel $channel)
 * @method static AbstractProvider buildProvider(string $provider, array|null $config = [])
 * @method static OceanpayManager extend(string $driver, Closure $callback)
 * @method static OceanpayManager useProviderChannelModel(string $model)
 * @method static OceanpayManager resolveChannelsUsing(Closure $resolver)
 * @method static ProviderChannel|null findChannel(int|string $key)
 * @method static void handlePaymentSucceededUsing(string $handler)
 * @method static void handlePayoutSucceededUsing(string $handler)
 * @method static void handlePayoutFailedUsing(string $handler)
 * @method static OceanpayManager ignoreRoutes()
 *
 * @see OceanpayManager
 */
class Oceanpay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }
}
