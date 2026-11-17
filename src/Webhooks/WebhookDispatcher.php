<?php

namespace Theaarch\Oceanpay\Webhooks;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Theaarch\Oceanpay\Contracts\HandlePaymentSucceeded;
use Theaarch\Oceanpay\Contracts\HandlePayoutFailed;
use Theaarch\Oceanpay\Contracts\HandlePayoutSucceeded;
use Theaarch\Oceanpay\Contracts\Provider;
use Theaarch\Oceanpay\Contracts\Webhooks;
use Theaarch\Oceanpay\Enums\WebhookEventType;
use Theaarch\Oceanpay\Events\WebhookHandled;
use Theaarch\Oceanpay\Events\WebhookReceived;
use Theaarch\Oceanpay\Events\WebhookRejected;
use Theaarch\Oceanpay\Exceptions\InvalidWebhookException;
use Theaarch\Oceanpay\Exceptions\WebhookConfigurationException;

class WebhookDispatcher
{
    public function __construct(private Container $container, private Dispatcher $events)
    {
        //
    }

    /**
     * Verify a webhook with the driver, pass what happened to the application's handler, and answer the provider.
     *
     * The payment or payout passed to the handler carries the provider channel the driver is
     * configured for, so the handler can check the order or payout was sent through it.
     * `WebhookReceived` is dispatched once the webhook is verified, and `WebhookHandled` after.
     * A webhook the driver cannot verify is answered with 403; see `reject()`.
     *
     * @throws AccessDeniedHttpException when the driver cannot verify the webhook
     * @throws BindingResolutionException
     */
    public function dispatch(Provider&Webhooks $provider, Request $request, string $object, string $driver): Response
    {
        try {
            $event = $provider->parseWebhook($request, $object)->withChannel($provider->getChannel());
        } catch (InvalidWebhookException $exception) {
            $this->reject($request, $driver, $provider, $exception);
        }

        $this->events->dispatch(new WebhookReceived($request, $driver, $provider->getChannel()));

        match ($event->type) {
            WebhookEventType::PaymentSucceeded => $this->container->make(HandlePaymentSucceeded::class)->handle($request, $event->data),
            WebhookEventType::PayoutSucceeded => $this->container->make(HandlePayoutSucceeded::class)->handle($request, $event->data),
            WebhookEventType::PayoutFailed => $this->container->make(HandlePayoutFailed::class)->handle($request, $event->data),
            WebhookEventType::Ignored => null,
        };

        $this->events->dispatch(new WebhookHandled($request, $event));

        return $provider->webhookResponse($event);
    }

    /**
     * Answer a webhook the driver cannot verify with 403, without handling it.
     *
     * Laravel never reports HTTP exceptions, so the rejection is logged as a warning and
     * announced with `WebhookRejected`; otherwise a provider channel with a wrong secret or
     * out-of-date allowed IPs would reject every webhook without a trace. A configuration
     * mistake, such as a missing client secret, is also reported as an error.
     *
     * @throws AccessDeniedHttpException
     */
    protected function reject(Request $request, string $driver, Provider $provider, InvalidWebhookException $exception): never
    {
        $channel = $provider->getChannel();

        Log::warning('Oceanpay webhook rejected: '.$exception->getMessage(), [
            'driver' => $driver,
            'provider_channel' => $channel?->getProviderChannelKey(),
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);

        if ($exception instanceof WebhookConfigurationException) {
            report($exception);
        }

        $this->events->dispatch(new WebhookRejected($request, $driver, $channel, $exception));

        throw new AccessDeniedHttpException($exception->getMessage(), $exception);
    }
}
