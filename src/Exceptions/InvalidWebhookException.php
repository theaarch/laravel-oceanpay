<?php

namespace Theaarch\Oceanpay\Exceptions;

use RuntimeException;

/**
 * A webhook that cannot be verified, e.g. with a wrong signature or from an address that is not allowed.
 */
class InvalidWebhookException extends RuntimeException
{
    //
}
