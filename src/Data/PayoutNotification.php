<?php

namespace Theaarch\Oceanpay\Data;

use Theaarch\Oceanpay\Contracts\ProviderChannel;

/**
 * A provider's webhook saying a payout succeeded or failed.
 */
readonly class PayoutNotification
{
    /**
     * @param  string  $id  our payout ID
     * @param  ?ProviderChannel  $providerChannel  the provider channel the webhook was sent for
     */
    public function __construct(
        public string $id,
        public ?string $currencyCode = null,
        public ?string $amountPaid = null,
        public ?ProviderChannel $providerChannel = null,
    ) {
        //
    }

    /**
     * Get the notification as sent for the provider channel.
     */
    public function withChannel(?ProviderChannel $channel): static
    {
        return new static($this->id, $this->currencyCode, $this->amountPaid, $channel);
    }
}
