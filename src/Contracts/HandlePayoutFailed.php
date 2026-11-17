<?php

namespace Theaarch\Oceanpay\Contracts;

use Illuminate\Http\Request;
use Theaarch\Oceanpay\Data\PayoutNotification;

interface HandlePayoutFailed
{
    public function handle(Request $request, PayoutNotification $notification);
}
