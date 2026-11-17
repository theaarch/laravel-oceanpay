<?php

namespace Theaarch\Oceanpay\Events;

use Illuminate\Http\Request;
use Theaarch\Oceanpay\Contracts\ProviderChannel;
use Theaarch\Oceanpay\Exceptions\InvalidWebhookException;

/**
 * A webhook could not be verified by its driver and was answered with 403, e.g. because of a
 * wrong signature or an address that is not allowed. `$exception->getMessage()` says why.
 */
class WebhookRejected
{
    public function __construct(
        public Request $request,
        public string $driver,
        public ?ProviderChannel $providerChannel,
        public InvalidWebhookException $exception,
    ) {}
}
