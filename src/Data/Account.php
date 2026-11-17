<?php

namespace Theaarch\Oceanpay\Data;

readonly class Account
{
    public function __construct(
        public string $currencyCode,
        public string $balance,
        /** @var array<mixed> the decoded provider response */
        public array $raw = [],
    ) {
        //
    }
}
