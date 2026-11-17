<?php

namespace App\Actions\Oceanpay;

use Illuminate\Http\Request;
use Theaarch\Oceanpay\Contracts\HandlePayoutFailed as HandlePayoutFailedContract;
use Theaarch\Oceanpay\Data\PayoutNotification;

class HandlePayoutFailed implements HandlePayoutFailedContract
{
    public function handle(Request $request, PayoutNotification $notification)
    {
        // TODO: Implement handle() method.
    }
}
