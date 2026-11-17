<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Tests\Fixtures\ProviderChannelModel;
use Theaarch\Oceanpay\Facades\Oceanpay;
use Theaarch\Oceanpay\OceanpayServiceProvider;

beforeEach(function () {
    $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
});

test('the migration is published with the payment gateway migrations', function () {
    $paths = ServiceProvider::pathsToPublish(OceanpayServiceProvider::class, 'oceanpay-migrations');

    expect(array_map('realpath', array_keys($paths)))->toBe([realpath(__DIR__.'/../../database/migrations')]);
});

test('a provider channel model on the migrated table is found by its key with its decrypted settings', function () {
    Oceanpay::useProviderChannelModel(ProviderChannelModel::class);
    $channel = ProviderChannelModel::create(['name' => 'Hookpay VND', 'driver' => 'hook', 'code' => '8001', 'settings' => ['client_secret' => 'secret']]);

    $found = Oceanpay::findChannel((string) $channel->id);

    expect($found->getProviderChannelDriver())->toBe('hook')
        ->and($found->getProviderChannelCode())->toBe('8001')
        ->and($found->getProviderChannelSettings())->toBe(['client_secret' => 'secret'])
        ->and(DB::table('provider_channels')->value('settings'))->not->toContain('secret');
});

test('a provider channel without settings or code has empty settings and no code', function () {
    $channel = ProviderChannelModel::create(['name' => 'Pay1', 'driver' => 'pay1']);

    expect($channel->fresh()->getProviderChannelSettings())->toBe([])
        ->and($channel->fresh()->getProviderChannelCode())->toBeNull();
});
