<?php

namespace Theaarch\Oceanpay\Data;

use Theaarch\Oceanpay\Contracts\ProviderChannel;
use Theaarch\Oceanpay\Enums\WebhookEventType;

readonly class WebhookEvent
{
    public function __construct(
        public WebhookEventType $type,
        public PaymentNotification|PayoutNotification|null $data = null,
    ) {
        //
    }

    public static function paymentSucceeded(PaymentNotification $payment): static
    {
        return new static(WebhookEventType::PaymentSucceeded, $payment);
    }

    public static function payoutSucceeded(PayoutNotification $payout): static
    {
        return new static(WebhookEventType::PayoutSucceeded, $payout);
    }

    public static function payoutFailed(PayoutNotification $payout): static
    {
        return new static(WebhookEventType::PayoutFailed, $payout);
    }

    public static function ignored(): static
    {
        return new static(WebhookEventType::Ignored);
    }

    /**
     * Get the event with its notification marked as sent for the provider channel.
     */
    public function withChannel(?ProviderChannel $channel): static
    {
        return new static($this->type, $this->data?->withChannel($channel));
    }
}
