<?php

namespace Tests\Fixtures;

use Theaarch\Oceanpay\Contracts\ProviderChannel;

class FakeChannel implements ProviderChannel
{
    /**
     * @param  array<string, mixed>  $settings
     */
    public function __construct(
        private int|string $key,
        private array $settings = [],
        private string $driver = 'fake',
    ) {}

    public function getProviderChannelKey(): int|string
    {
        return $this->key;
    }

    public function getProviderChannelDriver(): string
    {
        return $this->driver;
    }

    public function getProviderChannelCode(): ?string
    {
        return "CODE-{$this->key}";
    }

    public function getProviderChannelSettings(): array
    {
        return $this->settings;
    }
}
