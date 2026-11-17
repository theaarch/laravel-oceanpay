<?php

namespace Theaarch\Oceanpay\Contracts;

use Theaarch\Oceanpay\Data\Order;
use Theaarch\Oceanpay\Data\OrderResult;
use Theaarch\Oceanpay\Data\OrderStatus;
use Theaarch\Oceanpay\Exceptions\RequestFailedException;

/**
 * A driver whose provider takes payments: it creates orders the customer pays on a payment page.
 */
interface Payments
{
    /**
     * Create an order at the provider.
     *
     * @throws RequestFailedException when the provider rejects the order
     */
    public function createOrder(Order $order): OrderResult;

    /**
     * Get the status of the order with our ID.
     *
     * @throws RequestFailedException when the provider rejects the query
     */
    public function queryOrder(string $id): OrderStatus;
}
