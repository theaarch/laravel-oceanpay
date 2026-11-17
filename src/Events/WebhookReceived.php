<?php

namespace Theaarch\Oceanpay\Events;

use Illuminate\Http\Request;
use Theaarch\Oceanpay\Contracts\ProviderChannel;

/**
 * A webhook has been verified by its driver and is about to be handled.
 */
class WebhookReceived
{
    public function __construct(
        public Request $request,
        public string $driver,
        public ?ProviderChannel $providerChannel,
    ) {}
}
