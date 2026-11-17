<?php

namespace Theaarch\Oceanpay\Data;

/**
 * A payout to create at a provider.
 */
readonly class Payout
{
    /**
     * @param  string  $id  our payout ID, sent to the provider as its reference and returned in webhooks
     * @param  string  $amount  the amount as a decimal string, e.g. `"50000.00"`
     * @param  ?string  $bankCode  the payee's bank, for providers that need it
     * @param  array<string, mixed>  $extra  provider-specific options
     */
    public function __construct(
        public string $id,
        public string $amount,
        public string $payeeAccount,
        public string $payeeName,
        public ?string $bankCode = null,
        public ?string $currencyCode = null,
        public array $extra = [],
        public ?array $raw = [],
    ) {
        //
    }
}
