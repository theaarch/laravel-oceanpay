<?php

namespace Theaarch\Oceanpay\Data;

use Theaarch\Oceanpay\Contracts\ProviderChannel;

/**
 * A provider's webhook saying an order was paid.
 */
readonly class PaymentNotification
{
    /**
     * @param  string  $orderId  our order ID
     * @param  ?ProviderChannel  $providerChannel  the provider channel the webhook was sent for
     */
    public function __construct(
        public string $orderId,
        public string $txnId,
        public ?string $currencyCode = null,
        public ?string $amount = null,
        public ?ProviderChannel $providerChannel = null,
    ) {
        //
    }

    /**
     * Get the notification as sent for the provider channel.
     */
    public function withChannel(?ProviderChannel $channel): static
    {
        return new static($this->orderId, $this->txnId, $this->currencyCode, $this->amount, $channel);
    }
}
