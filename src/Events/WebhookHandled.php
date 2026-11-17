<?php

namespace Theaarch\Oceanpay\Events;

use Illuminate\Http\Request;
use Theaarch\Oceanpay\Data\WebhookEvent;

/**
 * A webhook has been passed to the application's handler, or ignored.
 */
class WebhookHandled
{
    public function __construct(
        public Request $request,
        public WebhookEvent $event,
    ) {}
}
