<?php

namespace Theaarch\Oceanpay\Events;

use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Theaarch\Oceanpay\Contracts\Provider;

class RequestFailed
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Provider $provider,
        public Request $request,
        public Response $response
    ) {}
}
