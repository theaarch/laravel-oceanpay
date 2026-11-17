<?php

namespace App\Actions\Oceanpay;

use Illuminate\Http\Request;
use Theaarch\Oceanpay\Contracts\HandlePayoutSucceeded as HandlePayoutSucceededContract;
use Theaarch\Oceanpay\Data\PayoutNotification;

class HandlePayoutSucceeded implements HandlePayoutSucceededContract
{
    public function handle(Request $request, PayoutNotification $notification)
    {
        // TODO: Implement handle() method.
    }
}
