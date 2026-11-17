<?php

namespace App\Actions\Oceanpay;

use Illuminate\Http\Request;
use Theaarch\Oceanpay\Contracts\HandlePaymentSucceeded as HandlePaymentSucceededContract;
use Theaarch\Oceanpay\Data\PaymentNotification;

class HandlePaymentSucceeded implements HandlePaymentSucceededContract
{
    public function handle(Request $request, PaymentNotification $notification)
    {
        // TODO: Implement handle() method.
    }
}
