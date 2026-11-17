<?php

namespace Theaarch\Oceanpay\Contracts;

use Illuminate\Http\Request;
use Theaarch\Oceanpay\Data\PaymentNotification;

interface HandlePaymentSucceeded
{
    public function handle(Request $request, PaymentNotification $notification);
}
