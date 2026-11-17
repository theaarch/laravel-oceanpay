<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Theaarch\Oceanpay\Concerns\ActsAsProviderChannel;
use Theaarch\Oceanpay\Contracts\ProviderChannel;

class ProviderChannelModel extends Model implements ProviderChannel
{
    use ActsAsProviderChannel;

    protected $table = 'provider_channels';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['settings' => 'encrypted:array'];
    }
}
