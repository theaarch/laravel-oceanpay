<?php

namespace Theaarch\Oceanpay\Enums;

enum PayoutState: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Unknown = 'unknown';
}
