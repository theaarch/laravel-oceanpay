<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Tests\Fixtures\FakeChannel;
use Tests\Fixtures\FakeProvider;
use Theaarch\Oceanpay\Contracts\HandlePaymentSucceeded;
use Theaarch\Oceanpay\Data\PaymentNotification;
use Theaarch\Oceanpay\Enums\WebhookEventType;
use Theaarch\Oceanpay\Events\WebhookHandled;
use Theaarch\Oceanpay\Events\WebhookReceived;
use Theaarch\Oceanpay\Events\WebhookRejected;
use Theaarch\Oceanpay\Exceptions\InvalidWebhookException;
use Theaarch\Oceanpay\Exceptions\WebhookConfigurationException;
use Theaarch\Oceanpay\Facades\Oceanpay;
use Theaarch\Oceanpay\Providers\AbstractProvider;

/**
 * Store provider channels the webhook route can find, and record the payments the application handles.
 *
 * @return object{payments: list<PaymentNotification>}
 */
function webhookChannels(FakeChannel ...$channels): object
{
    Oceanpay::extend('fake', fn () => new FakeProvider);
    Oceanpay::extend('plain', fn () => new class extends AbstractProvider {});
    Oceanpay::resolveChannelsUsing(fn (int|string $key) => collect($channels)->first(fn (FakeChannel $channel) => (string) $channel->getProviderChannelKey() === (string) $key));

    $handler = new class implements HandlePaymentSucceeded
    {
        /** @var list<PaymentNotification> */
        public array $payments = [];

        public function handle(Request $request, PaymentNotification $notification)
        {
            $this->payments[] = $notification;
        }
    };
    app()->instance(HandlePaymentSucceeded::class, $handler);

    return $handler;
}

test('a webhook is verified with the settings of the channel its URL names and handled with that channel', function () {
    $channel = new FakeChannel(1, ['token' => 'first-token']);
    $handler = webhookChannels($channel, new FakeChannel(2, ['token' => 'second-token']));

    $this->postJson(route('oceanpay.webhook', ['providerChannel' => 1, 'object' => 'payment']), ['id' => 'ORDER-1', 'status' => 'paid', 'token' => 'first-token'])
        ->assertOk()
        ->assertSeeText('fake payment.succeeded');

    expect($handler->payments)->toHaveCount(1)
        ->and($handler->payments[0]->orderId)->toBe('ORDER-1')
        ->and($handler->payments[0]->providerChannel)->toBe($channel);
});

test('a webhook the channel driver cannot verify is rejected without being handled', function (array $settings, array $server) {
    $handler = webhookChannels(new FakeChannel(1, $settings));

    $this->withServerVariables($server)
        ->postJson(route('oceanpay.webhook', ['providerChannel' => 1, 'object' => 'payment']), ['id' => 'ORDER-1', 'status' => 'paid', 'token' => 'first-token'])
        ->assertForbidden();

    expect($handler->payments)->toBe([]);
})->with([
    'wrong token' => [['token' => 'other-token'], []],
    'address not allowed' => [['token' => 'first-token', 'allowed_ips' => '203.0.113.0/24, 198.51.100.7'], ['REMOTE_ADDR' => '192.0.2.1']],
]);

test('a webhook the driver ignores is answered without being handled', function () {
    $handler = webhookChannels(new FakeChannel(1, ['token' => 'first-token']));

    $this->postJson(route('oceanpay.webhook', ['providerChannel' => 1, 'object' => 'payment']), ['id' => 'ORDER-1', 'status' => 'pending', 'token' => 'first-token'])
        ->assertOk()
        ->assertSeeText('fake ignored');

    expect($handler->payments)->toBe([]);
});

test('a webhook for a channel that cannot take webhooks is not found', function (FakeChannel $channel) {
    webhookChannels($channel);

    $this->postJson(route('oceanpay.webhook', ['providerChannel' => 1, 'object' => 'payment']), ['token' => 'first-token'])->assertNotFound();
})->with([
    'unknown channel' => [new FakeChannel(2)],
    'unregistered driver' => [new FakeChannel(1, [], 'missing')],
    'no driver' => [new FakeChannel(1, [], '')],
    'driver without webhooks' => [new FakeChannel(1, [], 'plain')],
]);

test('verified webhooks are announced before and after they are handled', function () {
    $channel = new FakeChannel(1, ['token' => 'first-token']);
    webhookChannels($channel);
    Event::fake([WebhookReceived::class, WebhookHandled::class]);

    $this->postJson(route('oceanpay.webhook', ['providerChannel' => 1, 'object' => 'payment']), ['id' => 'ORDER-1', 'status' => 'paid', 'token' => 'first-token'])
        ->assertOk();

    Event::assertDispatched(WebhookReceived::class, fn (WebhookReceived $event) => $event->driver === 'fake' && $event->providerChannel === $channel);
    Event::assertDispatched(WebhookHandled::class, fn (WebhookHandled $event) => $event->event->type === WebhookEventType::PaymentSucceeded);
});

test('a webhook that cannot be verified is announced as rejected and logged, not as received', function () {
    $channel = new FakeChannel(1, ['token' => 'first-token']);
    webhookChannels($channel);
    Event::fake([WebhookReceived::class, WebhookHandled::class, WebhookRejected::class]);
    Exceptions::fake();
    Log::spy();

    $this->postJson(route('oceanpay.webhook', ['providerChannel' => 1, 'object' => 'payment']), ['token' => 'wrong-token'])
        ->assertForbidden();

    Event::assertDispatched(WebhookRejected::class, fn (WebhookRejected $event) => $event->driver === 'fake'
        && $event->providerChannel === $channel
        && $event->exception->getMessage() === 'Invalid token');
    Event::assertNotDispatched(WebhookReceived::class);
    Event::assertNotDispatched(WebhookHandled::class);
    Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $message, array $context) => $message === 'Oceanpay webhook rejected: Invalid token'
        && $context['driver'] === 'fake'
        && $context['provider_channel'] === 1);
    Exceptions::assertNotReported(InvalidWebhookException::class);
});

test('a webhook rejected because the channel settings are incomplete is reported', function () {
    webhookChannels(new FakeChannel(1, []));
    Event::fake([WebhookRejected::class]);
    Exceptions::fake();

    $this->postJson(route('oceanpay.webhook', ['providerChannel' => 1, 'object' => 'payment']), ['token' => 'any-token'])
        ->assertForbidden();

    Exceptions::assertReported(fn (WebhookConfigurationException $exception) => $exception->getMessage() === 'No token is configured');
    Event::assertDispatched(WebhookRejected::class);
});
