<?php

namespace Theaarch\Oceanpay\Concerns;

use Theaarch\Oceanpay\Contracts\ProviderChannel;

/**
 * Implements the provider channel contract for an Eloquent model with `driver`,
 * `code` and `settings` attributes. Cast `settings` to `encrypted:array`.
 *
 * @see ProviderChannel
 */
trait ActsAsProviderChannel
{
    /**
     * Get the key that identifies the channel, e.g. in webhook URLs.
     */
    public function getProviderChannelKey(): int|string
    {
        return $this->getKey();
    }

    /**
     * Get the name of the driver that talks to the provider, e.g. `hook`.
     */
    public function getProviderChannelDriver(): string
    {
        return (string) $this->getAttribute('driver');
    }

    /**
     * Get the provider's own code for the channel, sent with orders and payouts.
     */
    public function getProviderChannelCode(): ?string
    {
        $code = $this->getAttribute('code');

        return $code === null ? null : (string) $code;
    }

    /**
     * Get the settings the driver is configured with.
     *
     * @return array<string, mixed>
     */
    public function getProviderChannelSettings(): array
    {
        return $this->getAttribute('settings') ?? [];
    }
}
