<?php

namespace Theaarch\Oceanpay;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Manager;
use InvalidArgumentException;
use Theaarch\Oceanpay\Contracts\Balances;
use Theaarch\Oceanpay\Contracts\HandlePaymentSucceeded;
use Theaarch\Oceanpay\Contracts\HandlePayoutFailed;
use Theaarch\Oceanpay\Contracts\HandlePayoutSucceeded;
use Theaarch\Oceanpay\Contracts\Payments;
use Theaarch\Oceanpay\Contracts\Payouts;
use Theaarch\Oceanpay\Contracts\Provider;
use Theaarch\Oceanpay\Contracts\ProviderChannel;
use Theaarch\Oceanpay\Exceptions\UnsupportedOperationException;
use Theaarch\Oceanpay\Providers\AbstractProvider;

class OceanpayManager extends Manager implements Contracts\Factory
{
    /**
     * The callback that finds a provider channel by its key.
     *
     * @var (Closure(int|string): (ProviderChannel|null))|null
     */
    protected ?Closure $channelResolver = null;

    /**
     * Whether the package registers its webhook route.
     */
    protected bool $registersRoutes = true;

    /**
     * Get a new driver instance, configured from `config('oceanpay.{driver}')`.
     *
     * Unlike Laravel's other managers, drivers are not shared: every call returns a new
     * instance, so `setConfig()` and `setChannel()` never affect another caller.
     *
     * @param  string|null  $driver
     *
     * @throws InvalidArgumentException when no driver is given, or the driver is not registered
     */
    public function driver($driver = null): Provider
    {
        // A blank name would make Laravel's Manager call its own createDriver() method.
        $driver = blank($driver) ? $this->getDefaultDriver() : $driver;
        $provider = $this->createDriver($driver);

        if (! $provider instanceof Provider) {
            throw new InvalidArgumentException("Driver [{$driver}] must implement [".Provider::class.'].');
        }

        return $provider;
    }

    /**
     * Get a new driver instance.
     *
     * @throws InvalidArgumentException when the driver is not registered
     */
    public function with(string $driver): Provider
    {
        return $this->driver($driver);
    }

    /**
     * Get the provider channel's driver, configured for that channel.
     *
     * @throws InvalidArgumentException when the channel has no driver, or its driver is not registered
     */
    public function channel(ProviderChannel $channel): Provider
    {
        $driver = $channel->getProviderChannelDriver();

        if (blank($driver)) {
            throw new InvalidArgumentException("Provider channel [{$channel->getProviderChannelKey()}] has no driver.");
        }

        return $this->driver($driver)->setChannel($channel);
    }

    /**
     * Get the provider channel's driver for taking payments.
     *
     * @throws UnsupportedOperationException when the driver does not take payments
     */
    public function payments(ProviderChannel $channel): Provider&Payments
    {
        return $this->supporting($channel, Payments::class);
    }

    /**
     * Get the provider channel's driver for sending payouts.
     *
     * @throws UnsupportedOperationException when the driver does not send payouts
     */
    public function payouts(ProviderChannel $channel): Provider&Payouts
    {
        return $this->supporting($channel, Payouts::class);
    }

    /**
     * Get the provider channel's driver for querying the account balance.
     *
     * @throws UnsupportedOperationException when the driver does not report balances
     */
    public function balances(ProviderChannel $channel): Provider&Balances
    {
        return $this->supporting($channel, Balances::class);
    }

    /**
     * Get the provider channel's driver, when it implements the contract.
     *
     * @template TContract of object
     *
     * @param  class-string<TContract>  $contract
     * @return Provider&TContract
     *
     * @throws UnsupportedOperationException when the driver does not implement the contract
     */
    protected function supporting(ProviderChannel $channel, string $contract): Provider
    {
        $provider = $this->channel($channel);

        if (! $provider instanceof $contract) {
            throw UnsupportedOperationException::for($channel->getProviderChannelDriver(), $contract);
        }

        return $provider;
    }

    /**
     * Build a provider instance with the settings, e.g. when registering a driver:
     *
     *     Oceanpay::extend('pay1', fn ($app) => Oceanpay::buildProvider(
     *         Pay1Provider::class, $app['config']['oceanpay.pay1'] ?? []
     *     ));
     *
     * @template TProvider of AbstractProvider
     *
     * @param  class-string<TProvider>  $provider
     * @param  array<string, mixed>|null  $config
     * @return TProvider
     */
    public function buildProvider(string $provider, ?array $config = []): AbstractProvider
    {
        return new $provider($config);
    }

    /**
     * Use the Eloquent model to find provider channels, e.g. for the channel key in a webhook URL.
     *
     * @param  class-string<Model&ProviderChannel>  $model
     * @return $this
     */
    public function useProviderChannelModel(string $model): static
    {
        return $this->resolveChannelsUsing(fn (int|string $key): ?ProviderChannel => $model::query()->find($key));
    }

    /**
     * Set the callback that finds a provider channel by its key.
     *
     * @param  Closure(int|string): (ProviderChannel|null)  $resolver
     * @return $this
     */
    public function resolveChannelsUsing(Closure $resolver): static
    {
        $this->channelResolver = $resolver;

        return $this;
    }

    /**
     * Find a provider channel by its key, e.g. the one in a webhook URL.
     */
    public function findChannel(int|string $key): ?ProviderChannel
    {
        return $this->channelResolver ? ($this->channelResolver)($key) : null;
    }

    /**
     * Handle "payment succeeded" webhooks with the class.
     *
     * @param  class-string<HandlePaymentSucceeded>  $handler
     */
    public function handlePaymentSucceededUsing(string $handler): void
    {
        $this->container->singleton(HandlePaymentSucceeded::class, $handler);
    }

    /**
     * Handle "payout succeeded" webhooks with the class.
     *
     * @param  class-string<HandlePayoutSucceeded>  $handler
     */
    public function handlePayoutSucceededUsing(string $handler): void
    {
        $this->container->singleton(HandlePayoutSucceeded::class, $handler);
    }

    /**
     * Handle "payout failed" webhooks with the class.
     *
     * @param  class-string<HandlePayoutFailed>  $handler
     */
    public function handlePayoutFailedUsing(string $handler): void
    {
        $this->container->singleton(HandlePayoutFailed::class, $handler);
    }

    /**
     * Do not register the package's webhook route, e.g. to register it yourself with other
     * middleware. Call it in a service provider's `register` method.
     *
     * @return $this
     */
    public function ignoreRoutes(): static
    {
        $this->registersRoutes = false;

        return $this;
    }

    /**
     * Determine whether the package registers its webhook route.
     */
    public function registersRoutes(): bool
    {
        return $this->registersRoutes;
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        throw new InvalidArgumentException('No Oceanpay driver was specified.');
    }
}
