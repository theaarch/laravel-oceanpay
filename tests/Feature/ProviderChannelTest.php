<?php

use Tests\Fixtures\FakeChannel;
use Tests\Fixtures\FakeProvider;
use Theaarch\Oceanpay\Data\Order;
use Theaarch\Oceanpay\Exceptions\UnsupportedOperationException;
use Theaarch\Oceanpay\Facades\Oceanpay;

beforeEach(function () {
    Oceanpay::extend('fake', fn () => Oceanpay::buildProvider(FakeProvider::class, ['client_id' => 'config-client']));
});

test('a channel gets its own driver instance configured with its settings', function () {
    $first = Oceanpay::channel(new FakeChannel(1, ['client_id' => 'first-client']));
    $second = Oceanpay::channel(new FakeChannel(2, ['client_id' => 'second-client']));

    expect($first->clientId())->toBe('first-client')
        ->and($first->getChannel()->getProviderChannelKey())->toBe(1)
        ->and($second->clientId())->toBe('second-client')
        ->and(Oceanpay::driver('fake')->clientId())->toBe('config-client')
        ->and(Oceanpay::driver('fake')->getChannel())->toBeNull();
});

test('every driver call returns a new instance, so configuring one never affects another', function () {
    $configured = Oceanpay::driver('fake');
    $configured->setConfig(['client_id' => 'ad-hoc-client']);
    $forChannel = Oceanpay::driver('fake')->setChannel(new FakeChannel(4, ['client_id' => 'channel-client']));

    expect($configured->clientId())->toBe('ad-hoc-client')
        ->and($configured->getChannel())->toBeNull()
        ->and($forChannel->clientId())->toBe('channel-client')
        ->and($forChannel->getChannel()->getProviderChannelKey())->toBe(4)
        ->and(Oceanpay::driver('fake'))->not->toBe(Oceanpay::driver('fake'))
        ->and(Oceanpay::driver('fake')->clientId())->toBe('config-client');
});

test('configuring a driver with settings drops its provider channel', function () {
    $provider = Oceanpay::channel(new FakeChannel(4))->setConfig(['client_id' => 'other']);

    expect($provider->getChannel())->toBeNull()
        ->and($provider->clientId())->toBe('other');
});

test('a driver is built from its class and settings', function () {
    $provider = Oceanpay::buildProvider(FakeProvider::class, ['client_id' => 'built-client']);

    expect($provider)->toBeInstanceOf(FakeProvider::class)
        ->and($provider->clientId())->toBe('built-client');
});

test('a channel of a driver that is not registered cannot be used', function () {
    Oceanpay::channel(new FakeChannel(1, [], 'missing'));
})->throws(InvalidArgumentException::class);

test('a channel without a driver cannot be used', function () {
    Oceanpay::channel(new FakeChannel(7, [], ''));
})->throws(InvalidArgumentException::class, 'Provider channel [7] has no driver.');

test('a driver must be named', function (?string $driver) {
    Oceanpay::driver($driver);
})->with(['no name' => [null], 'empty name' => [''], 'blank name' => [' ']])
    ->throws(InvalidArgumentException::class, 'No Oceanpay driver was specified.');

test('a channel driver is used for the operations it implements', function () {
    $result = Oceanpay::payments(new FakeChannel(3))->createOrder(new Order('ORDER-1', '100.00'));

    expect($result->providerReference)->toBe('FAKE-ORDER-1')
        ->and($result->paymentUrl)->toBe(route('oceanpay.webhook', ['providerChannel' => 3, 'object' => 'payment']));
});

test('a channel driver cannot be used for an operation it does not implement', function (string $operation) {
    Oceanpay::{$operation}(new FakeChannel(1));
})->with(['payouts', 'balances'])->throws(UnsupportedOperationException::class);

test('a driver answers operations its provider does not support with a clear error', function () {
    Oceanpay::driver('fake')->queryAccount();
})->throws(UnsupportedOperationException::class, 'does not support [queryAccount()]');

test('channels are found with the resolver the application sets', function () {
    $channel = new FakeChannel(7);
    Oceanpay::resolveChannelsUsing(fn (int|string $key) => (string) $key === '7' ? $channel : null);

    expect(Oceanpay::findChannel('7'))->toBe($channel)
        ->and(Oceanpay::findChannel('8'))->toBeNull();
});

test('no channel is found before the application sets a resolver', function () {
    expect(Oceanpay::findChannel(1))->toBeNull();
});
