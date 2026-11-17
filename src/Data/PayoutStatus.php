<?php

namespace Theaarch\Oceanpay\Data;

use Theaarch\Oceanpay\Enums\PayoutState;

readonly class PayoutStatus
{
    /**
     * @param  ?string  $amountPaid  the amount paid out, when the provider reports it
     * @param  array<mixed>  $raw  the decoded provider response
     */
    public function __construct(
        public PayoutState $state,
        public ?string $providerReference = null,
        public ?string $currencyCode = null,
        public ?string $amountPaid = null,
        public array $raw = [],
    ) {
        //
    }
}
