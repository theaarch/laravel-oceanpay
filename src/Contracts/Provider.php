<?php

namespace Theaarch\Oceanpay\Contracts;

use Theaarch\Oceanpay\Data\Account;
use Theaarch\Oceanpay\Data\Order;
use Theaarch\Oceanpay\Data\OrderResult;
use Theaarch\Oceanpay\Data\OrderStatus;
use Theaarch\Oceanpay\Data\Payout;
use Theaarch\Oceanpay\Data\PayoutResult;
use Theaarch\Oceanpay\Data\PayoutStatus;
use Theaarch\Oceanpay\Exceptions\RequestFailedException;
use Theaarch\Oceanpay\Exceptions\UnsupportedOperationException;

/**
 * A payment provider driver.
 *
 * Every driver offers the operations below. A driver declares the ones its provider supports
 * by implementing `Payments`, `Payouts` or `Balances`; the others throw an
 * `UnsupportedOperationException`. `Oceanpay::driver()` returns a new driver on every call,
 * so its setters only ever affect the caller's own instance.
 */
interface Provider
{
    /**
     * Configure the driver with the settings, e.g. read from the database.
     *
     * @param  array<string, mixed>|null  $config
     * @return $this
     */
    public function setConfig(?array $config): static;

    /**
     * Configure the driver for the provider channel: its settings become the configuration,
     * and its code and key are used for requests and the webhook URL.
     *
     * @return $this
     */
    public function setChannel(ProviderChannel $channel): static;

    /**
     * Get the provider channel the driver is configured for, if any.
     */
    public function getChannel(): ?ProviderChannel;

    /**
     * Create an order at the provider.
     *
     * @throws RequestFailedException when the provider rejects the order
     * @throws UnsupportedOperationException when the driver does not take payments
     */
    public function createOrder(Order $order): OrderResult;

    /**
     * Get the status of the order with our ID.
     *
     * @throws RequestFailedException when the provider rejects the query
     * @throws UnsupportedOperationException when the driver does not take payments
     */
    public function queryOrder(string $id): OrderStatus;

    /**
     * Create a payout at the provider.
     *
     * @throws RequestFailedException when the provider rejects the payout
     * @throws UnsupportedOperationException when the driver does not send payouts
     */
    public function createPayout(Payout $payout): PayoutResult;

    /**
     * Get the status of the payout with our ID.
     *
     * @throws RequestFailedException when the provider rejects the query
     * @throws UnsupportedOperationException when the driver does not send payouts
     */
    public function queryPayout(string $id): PayoutStatus;

    /**
     * Get the merchant account balance at the provider.
     *
     * @throws RequestFailedException when the provider rejects the query
     * @throws UnsupportedOperationException when the driver does not report balances
     */
    public function queryAccount(): Account;
}
