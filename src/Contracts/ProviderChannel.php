<?php

namespace Theaarch\Oceanpay\Contracts;

/**
 * A payment provider's channel, stored by the application: which driver talks to the
 * provider, the provider's own code for the channel, and the provider settings
 * (credentials, endpoints and the like) the driver is configured with.
 */
interface ProviderChannel
{
    /**
     * Get the key that identifies the channel, e.g. in webhook URLs.
     */
    public function getProviderChannelKey(): int|string;

    /**
     * Get the name of the driver that talks to the provider, e.g. `hook`.
     */
    public function getProviderChannelDriver(): string;

    /**
     * Get the provider's own code for the channel, sent with orders and payouts.
     */
    public function getProviderChannelCode(): ?string;

    /**
     * Get the settings the driver is configured with.
     *
     * @return array<string, mixed>
     */
    public function getProviderChannelSettings(): array;
}
