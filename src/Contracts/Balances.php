<?php

namespace Theaarch\Oceanpay\Contracts;

use Theaarch\Oceanpay\Data\Account;
use Theaarch\Oceanpay\Exceptions\RequestFailedException;

/**
 * A driver whose provider reports the merchant account balance.
 */
interface Balances
{
    /**
     * Get the merchant account balance at the provider.
     *
     * @throws RequestFailedException when the provider rejects the query
     */
    public function queryAccount(): Account;
}
