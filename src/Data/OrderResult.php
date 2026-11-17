<?php

namespace Theaarch\Oceanpay\Data;

readonly class OrderResult
{
    /**
     * @param  ?string  $providerReference  the provider's ID for the order
     * @param  ?string  $paymentUrl  the payment page the customer is sent to
     * @param  array<mixed>  $raw  the decoded provider response
     */
    public function __construct(
        public ?string $providerReference,
        public ?string $paymentUrl,
        public array $raw = [],
    ) {
        //
    }
}
