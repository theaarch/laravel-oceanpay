<?php

namespace Theaarch\Oceanpay\Providers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Theaarch\Oceanpay\Contracts\Provider as ProviderContract;
use Theaarch\Oceanpay\Contracts\ProviderChannel;
use Theaarch\Oceanpay\Data\Account;
use Theaarch\Oceanpay\Data\Order;
use Theaarch\Oceanpay\Data\OrderResult;
use Theaarch\Oceanpay\Data\OrderStatus;
use Theaarch\Oceanpay\Data\Payout;
use Theaarch\Oceanpay\Data\PayoutResult;
use Theaarch\Oceanpay\Data\PayoutStatus;
use Theaarch\Oceanpay\Exceptions\InvalidWebhookException;
use Theaarch\Oceanpay\Exceptions\UnsupportedOperationException;

abstract class AbstractProvider implements ProviderContract
{
    /**
     * The configuration options for the provider.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $config;

    /**
     * The provider channel the provider is configured for.
     */
    protected ?ProviderChannel $channel = null;

    /**
     * Create a new provider instance.
     *
     * @param  array<string, mixed>|null  $config
     */
    public function __construct(?array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Configure the provider with the settings, e.g. read from the database. The provider is
     * then no longer configured for a provider channel.
     *
     * @param  array<string, mixed>|null  $config
     * @return $this
     */
    public function setConfig(?array $config): static
    {
        $this->config = $config;
        $this->channel = null;

        return $this;
    }

    /**
     * Configure the provider for the provider channel: its settings become the configuration,
     * and its code and key are used for requests and the webhook URL.
     *
     * @return $this
     */
    public function setChannel(ProviderChannel $channel): static
    {
        $this->config = $channel->getProviderChannelSettings();
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get the provider channel the provider is configured for, if any.
     */
    public function getChannel(): ?ProviderChannel
    {
        return $this->channel;
    }

    /**
     * Create an order at the provider. Override it, and implement `Payments`, when the provider takes payments.
     *
     * @throws UnsupportedOperationException
     */
    public function createOrder(Order $order): OrderResult
    {
        throw UnsupportedOperationException::method(static::class, __FUNCTION__);
    }

    /**
     * Get the status of an order. Override it, and implement `Payments`, when the provider takes payments.
     *
     * @throws UnsupportedOperationException
     */
    public function queryOrder(string $id): OrderStatus
    {
        throw UnsupportedOperationException::method(static::class, __FUNCTION__);
    }

    /**
     * Create a payout at the provider. Override it, and implement `Payouts`, when the provider sends payouts.
     *
     * @throws UnsupportedOperationException
     */
    public function createPayout(Payout $payout): PayoutResult
    {
        throw UnsupportedOperationException::method(static::class, __FUNCTION__);
    }

    /**
     * Get the status of a payout. Override it, and implement `Payouts`, when the provider sends payouts.
     *
     * @throws UnsupportedOperationException
     */
    public function queryPayout(string $id): PayoutStatus
    {
        throw UnsupportedOperationException::method(static::class, __FUNCTION__);
    }

    /**
     * Get the account balance. Override it, and implement `Balances`, when the provider reports balances.
     *
     * @throws UnsupportedOperationException
     */
    public function queryAccount(): Account
    {
        throw UnsupportedOperationException::method(static::class, __FUNCTION__);
    }

    /**
     * Get the URL the provider sends the channel's webhooks to, or null when the
     * provider is not configured for a channel.
     *
     * @param  string  $object  e.g. `payment` or `payout`
     */
    protected function webhookUrl(string $object): ?string
    {
        if (! $this->channel) {
            return null;
        }

        return route('oceanpay.webhook', [
            'providerChannel' => $this->channel->getProviderChannelKey(),
            'object' => $object,
        ]);
    }

    /**
     * Reject a webhook from an address that is not allowed. An empty list allows any address.
     *
     * @param  mixed  $allowedIps  IP addresses and CIDR ranges, as an array or a comma-separated string
     *
     * @throws InvalidWebhookException when the address is not allowed
     */
    protected function ensureWebhookIpIsAllowed(Request $request, mixed $allowedIps): void
    {
        $allowedIps = static::normalizeIps($allowedIps);
        $ip = $request->ip();

        if ($allowedIps !== [] && ($ip === null || ! IpUtils::checkIp($ip, $allowedIps))) {
            throw new InvalidWebhookException('IP address is not allowed');
        }
    }

    /**
     * Normalize IP addresses given as an array or a comma-separated string, dropping blank entries.
     *
     * @return list<string>
     */
    public static function normalizeIps(mixed $ips): array
    {
        if (is_string($ips)) {
            $ips = explode(',', $ips);
        }

        return collect($ips)
            ->map(fn ($ip) => trim((string) $ip))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Get the configuration options of the provider.
     */
    protected function getConfig(mixed $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->config;
        }

        return data_get($this->config, $key, $default);
    }
}
