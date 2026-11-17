# Laravel Oceanpay

A driver manager for payment providers, you get a driver by name with `Oceanpay::driver()`, drivers are registered with `extend()` and `buildProvider()`, and each driver extends one `AbstractProvider`. What it adds is that a driver's settings can come from your database, one row per provider account or channel.

Drivers such as [`theaarch/laravel-hook`](https://github.com/oceanpay/laravel-hook) talk to one provider each. This package gives them one API for:

- payments: create and query orders the customer pays on a payment page,
- payouts: create and query payouts to a payee's account,
- the merchant account balance,
- webhooks, received at one route and passed to your application's handlers,
- provider channels: provider settings stored in your database, one row per account or channel.

## Provider channels

A provider channel is a row in your database: which driver it uses, the provider's own code for the channel, and the driver's settings (credentials, endpoints and the like). Publish and run the migration for the `provider_channels` table (`oceanpay:install` publishes it too):

```bash
php artisan vendor:publish --tag=oceanpay-migrations
php artisan migrate
```

| Column | Description |
| --- | --- |
| `name` | A name for the channel, e.g. `Hookpay VND`. |
| `driver` | The driver that talks to the provider, e.g. `hook`. |
| `code` | The provider's own code for the channel, sent with orders and payouts. |
| `settings` | The driver's settings, stored encrypted (`encrypted:array`). |

Add columns your application needs, such as a type or status, to the published migration. If you already have a table, skip the migration and implement the contract on your own model. Implement `Theaarch\Oceanpay\Contracts\ProviderChannel` on your model. The `ActsAsProviderChannel` trait implements it for a model with `driver`, `code` and `settings` attributes:

```php
use Illuminate\Database\Eloquent\Model;
use Theaarch\Oceanpay\Concerns\ActsAsProviderChannel;
use Theaarch\Oceanpay\Contracts\ProviderChannel as ProviderChannelContract;

class ProviderChannel extends Model implements ProviderChannelContract
{
    use ActsAsProviderChannel;

    protected function casts(): array
    {
        return ['settings' => 'encrypted:array'];
    }
}
```

Register the model and your webhook handlers in a service provider, so webhooks can find the channel their URL names:

```php
use Theaarch\Oceanpay\Facades\Oceanpay;

Oceanpay::useProviderChannelModel(ProviderChannel::class);

Oceanpay::handlePaymentSucceededUsing(HandlePaymentSucceeded::class);
Oceanpay::handlePayoutSucceededUsing(HandlePayoutSucceeded::class);
Oceanpay::handlePayoutFailedUsing(HandlePayoutFailed::class);
```

## Usage

Get a provider channel's driver for the operation you need. Each call returns a new driver instance configured with that channel's settings, so channels never share settings:

```php
use Theaarch\Oceanpay\Data\Order;
use Theaarch\Oceanpay\Data\Payout;
use Theaarch\Oceanpay\Facades\Oceanpay;

$order = Oceanpay::payments($providerChannel)->createOrder(new Order(id: $orderId, amount: '50000.00'));
$order->paymentUrl;

$payout = Oceanpay::payouts($providerChannel)->createPayout(new Payout(
    id: $payoutId,
    amount: '50000.00',
    payeeAccount: '0123456789',
    payeeName: 'NGUYEN VAN A',
));

$account = Oceanpay::balances($providerChannel)->queryAccount();
```

`payments()`, `payouts()` and `balances()` throw `UnsupportedOperationException` when the channel's driver does not support the operation. `Oceanpay::channel($providerChannel)` returns the driver without that check.

### Configuring a driver yourself

`Oceanpay::driver('hook')` returns a driver configured from `config('oceanpay.hook')`. Configure it with settings from anywhere, such as your database:

```php
use Theaarch\Oceanpay\Data\Order;
use Theaarch\Oceanpay\Data\Payout;
use Theaarch\Oceanpay\Facades\Oceanpay;

$gateway = Oceanpay::driver('hook');
$gateway->setConfig($settings); // e.g. ['client_id' => '...', 'client_secret' => '...']

$order = $gateway->createOrder(new Order(id: $orderId, amount: '50000.00'));
$status = $gateway->queryOrder($orderId);

$payout = $gateway->createPayout(new Payout(id: $payoutId, amount: '50000.00', payeeAccount: '0123456789', payeeName: 'NGUYEN VAN A'));
$status = $gateway->queryPayout($payoutId);

$account = $gateway->queryAccount();
```

Unlike Socialite, every `Oceanpay::driver()` call returns a new instance, so `setConfig()` only changes your own `$gateway`: one account's settings never carry over into another call or queued job.

A driver configured with `setConfig()` has no provider channel, so its webhook URL cannot name one: webhooks for its orders and payouts go to the driver's own config-verified route, if it has one. For anything that receives webhooks, configure the driver with a provider channel instead, which sets its settings and webhook URL together:

```php
$gateway = Oceanpay::driver('hook')->setChannel($providerChannel); // the same as Oceanpay::channel($providerChannel)
```

## Webhooks

The package registers one route for every driver:

```text
POST /oceanpay/{providerChannel}/webhook/{object}    oceanpay.webhook
```

Drivers set it as the notify URL of orders and payouts sent with a provider channel. A webhook is answered with `404` when no provider channel has the key or its driver does not take webhooks, and with `403` when the driver cannot verify it with the channel's settings. Verified events go to the handlers you bind:

| Event | Contract | Receives |
| --- | --- | --- |
| payment succeeded | `Contracts\HandlePaymentSucceeded` | `Data\PaymentNotification` |
| payout succeeded | `Contracts\HandlePayoutSucceeded` | `Data\PayoutNotification` |
| payout failed | `Contracts\HandlePayoutFailed` | `Data\PayoutNotification` |

The notification carries the `providerChannel` the webhook URL named. Check that the order or payout was sent through it, so one channel's credentials cannot settle another channel's orders.

As in Laravel Cashier, `Events\WebhookReceived` is dispatched once a webhook is verified, and `Events\WebhookHandled` after it is handled or ignored. A webhook that cannot be verified is answered with `403`, logged as a warning and announced with `Events\WebhookRejected`, which carries the reason; listen for it to be alerted when a channel's secret or allowed IPs are wrong. A rejection caused by incomplete settings, such as a missing client secret, is also reported as an error. To register the route yourself, for example with other middleware, call `Oceanpay::ignoreRoutes()` in a service provider's `register` method.

## Writing a driver

As with a Socialite provider, extend `Theaarch\Oceanpay\Providers\AbstractProvider`, implement the contracts the provider supports, and register the driver in your package's service provider. Every driver has `createOrder()`, `queryOrder()`, `createPayout()`, `queryPayout()` and `queryAccount()`, so `Oceanpay::driver()` is fully typed; `AbstractProvider` answers the ones you don't override with an `UnsupportedOperationException`:

```php
use Theaarch\Oceanpay\Facades\Oceanpay;

public function boot(): void
{
    Oceanpay::extend('pay1', function ($app) {
        return Oceanpay::buildProvider(Pay1Provider::class, $app['config']['oceanpay.pay1'] ?? []);
    });
}
```

A minimal driver:

```php
use Illuminate\Support\Facades\Http;
use Theaarch\Oceanpay\Contracts\Payments;
use Theaarch\Oceanpay\Data\Order;
use Theaarch\Oceanpay\Data\OrderResult;
use Theaarch\Oceanpay\Data\OrderStatus;
use Theaarch\Oceanpay\Exceptions\RequestFailedException;
use Theaarch\Oceanpay\Providers\AbstractProvider;

class Pay1Provider extends AbstractProvider implements Payments
{
    public function createOrder(Order $order): OrderResult
    {
        $response = Http::withToken($this->getConfig('api_key'))->post($this->getConfig('endpoints.order'), [
            'channel' => $this->getChannel()?->getProviderChannelCode(),
            'reference' => $order->id,
            'amount' => $order->amount,
            'callback_url' => $this->webhookUrl('payment'),
        ]);

        if ($response->failed()) {
            throw new RequestFailedException($response->body());
        }

        return new OrderResult($response->json('id'), $response->json('checkout_url'), $response->json());
    }

    public function queryOrder(string $id): OrderStatus
    {
        // ...
    }
}
```

| Contract | Methods |
| --- | --- |
| `Contracts\Payments` | `createOrder(Order): OrderResult`, `queryOrder(string $id): OrderStatus` |
| `Contracts\Payouts` | `createPayout(Payout): PayoutResult`, `queryPayout(string $id): PayoutStatus` |
| `Contracts\Balances` | `queryAccount(): Account` |
| `Contracts\Webhooks` | `parseWebhook(Request, string $object): WebhookEvent`, `webhookResponse(WebhookEvent): Response` |
| `Contracts\HasSettingsRules` | `settingsRules(): array`, validation rules for a provider channel's settings |

Inside the driver:

- `$this->getConfig('client_id')` reads the channel's settings, or the config the driver was registered with.
- `$this->getChannel()?->getProviderChannelCode()` is the provider's code for the channel.
- `$this->webhookUrl('payment')` is the channel's webhook URL, to send as the notify URL.
- `parseWebhook()` verifies the webhook and returns `WebhookEvent::paymentSucceeded(...)`, `payoutSucceeded(...)`, `payoutFailed(...)` or `ignored()`. Throw `InvalidWebhookException` when it cannot be verified, or `WebhookConfigurationException` when the settings needed to verify it are missing. `$this->ensureWebhookIpIsAllowed($request, $allowedIps)` checks an IP allowlist.
- Throw `RequestFailedException` when the provider rejects a request.

## Testing

```bash
composer install
vendor/bin/pest
```
