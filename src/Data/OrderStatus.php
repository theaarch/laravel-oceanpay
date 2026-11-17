<?php

namespace Theaarch\Oceanpay\Data;

readonly class OrderStatus
{
    /**
     * @param  ?string  $amountPaid  the amount paid, when the order is paid
     * @param  array<mixed>  $raw  the decoded provider response
     */
    public function __construct(
        public bool $isPaid,
        public ?string $providerReference = null,
        public ?string $currencyCode = null,
        public ?string $amountPaid = null,
        public array $raw = [],
    ) {
        //
    }
}
