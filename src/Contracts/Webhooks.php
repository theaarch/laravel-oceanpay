<?php

namespace Theaarch\Oceanpay\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Theaarch\Oceanpay\Data\WebhookEvent;
use Theaarch\Oceanpay\Exceptions\InvalidWebhookException;

/**
 * A driver whose provider reports order and payout status changes with webhooks.
 *
 * The package receives them at the `oceanpay.webhook` route, which names the provider
 * channel; the driver only verifies them with its settings and says what happened.
 */
interface Webhooks
{
    /**
     * Verify the webhook with the driver's settings and turn it into an event.
     *
     * @param  string  $object  the object in the webhook URL, e.g. `payment` or `payout`
     *
     * @throws InvalidWebhookException when the webhook cannot be verified
     */
    public function parseWebhook(Request $request, string $object): WebhookEvent;

    /**
     * Get the response the provider expects once the event is handled.
     */
    public function webhookResponse(WebhookEvent $event): Response;
}
