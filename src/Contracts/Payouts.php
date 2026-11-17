<?php

namespace Theaarch\Oceanpay\Contracts;

use Theaarch\Oceanpay\Data\Payout;
use Theaarch\Oceanpay\Data\PayoutResult;
use Theaarch\Oceanpay\Data\PayoutStatus;
use Theaarch\Oceanpay\Exceptions\RequestFailedException;

/**
 * A driver whose provider sends payouts to a payee's account.
 */
interface Payouts
{
    /**
     * Create a payout at the provider.
     *
     * @throws RequestFailedException when the provider rejects the payout
     */
    public function createPayout(Payout $payout): PayoutResult;

    /**
     * Get the status of the payout with our ID.
     *
     * @throws RequestFailedException when the provider rejects the query
     */
    public function queryPayout(string $id): PayoutStatus;
}
