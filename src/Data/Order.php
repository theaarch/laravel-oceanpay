<?php

namespace Theaarch\Oceanpay\Data;

/**
 * An order to create at a provider.
 */
readonly class Order
{
    /**
     * @param  string  $id  our order ID, sent to the provider as its reference and returned in webhooks
     * @param  string  $amount  the amount as a decimal string, e.g. `"50000.00"`
     * @param  array<string, mixed>  $extra  provider-specific options, e.g. `['bank_code' => 'VCB']`
     */
    public function __construct(
        public string $id,
        public string $amount,
        public ?string $currencyCode = null,
        public ?string $description = null,
        public array $extra = [],
        public ?array $raw = [],
    ) {
        //
    }
}
