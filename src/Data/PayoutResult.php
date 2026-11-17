<?php

namespace Theaarch\Oceanpay\Data;

readonly class PayoutResult
{
    /**
     * @param  ?string  $providerReference  the provider's ID for the payout
     * @param  array<mixed>  $raw  the decoded provider response
     */
    public function __construct(
        public ?string $providerReference,
        public array $raw = [],
    ) {
        //
    }
}
