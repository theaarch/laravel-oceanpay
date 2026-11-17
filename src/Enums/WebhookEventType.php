<?php

namespace Theaarch\Oceanpay\Enums;

enum WebhookEventType: string
{
    case PaymentSucceeded = 'payment.succeeded';
    case PayoutSucceeded = 'payout.succeeded';
    case PayoutFailed = 'payout.failed';

    /** A webhook the application does not act on, e.g. a pending status. */
    case Ignored = 'ignored';
}
