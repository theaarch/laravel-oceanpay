<?php

namespace Tests\Fixtures;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Theaarch\Oceanpay\Contracts\Payments;
use Theaarch\Oceanpay\Contracts\Webhooks;
use Theaarch\Oceanpay\Data\Order;
use Theaarch\Oceanpay\Data\OrderResult;
use Theaarch\Oceanpay\Data\OrderStatus;
use Theaarch\Oceanpay\Data\PaymentNotification;
use Theaarch\Oceanpay\Data\WebhookEvent;
use Theaarch\Oceanpay\Exceptions\InvalidWebhookException;
use Theaarch\Oceanpay\Exceptions\WebhookConfigurationException;
use Theaarch\Oceanpay\Providers\AbstractProvider;

/**
 * A driver that takes payments and webhooks. A webhook is valid when its `token` is the `token` setting;
 * without a `token` setting no webhook can be verified.
 */
class FakeProvider extends AbstractProvider implements Payments, Webhooks
{
    public function clientId(): mixed
    {
        return $this->getConfig('client_id');
    }

    public function createOrder(Order $order): OrderResult
    {
        return new OrderResult('FAKE-'.$order->id, $this->webhookUrl('payment'));
    }

    public function queryOrder(string $id): OrderStatus
    {
        return new OrderStatus(isPaid: false);
    }

    public function parseWebhook(Request $request, string $object): WebhookEvent
    {
        $this->ensureWebhookIpIsAllowed($request, $this->getConfig('allowed_ips', []));

        if (blank($this->getConfig('token'))) {
            throw new WebhookConfigurationException('No token is configured');
        }

        if ($request->input('token') !== $this->getConfig('token')) {
            throw new InvalidWebhookException('Invalid token');
        }

        return $object === 'payment' && $request->input('status') === 'paid'
            ? WebhookEvent::paymentSucceeded(new PaymentNotification((string) $request->input('id'), 'TX-1', 'VND', '100'))
            : WebhookEvent::ignored();
    }

    public function webhookResponse(WebhookEvent $event): Response
    {
        return new Response("fake {$event->type->value}");
    }
}
