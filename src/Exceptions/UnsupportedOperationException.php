<?php

namespace Theaarch\Oceanpay\Exceptions;

use LogicException;

/**
 * A provider channel whose driver does not support the operation, e.g. payouts.
 */
class UnsupportedOperationException extends LogicException
{
    public static function for(string $driver, string $contract): self
    {
        return new self("The [{$driver}] driver does not implement [{$contract}].");
    }

    /**
     * @param  class-string  $provider
     */
    public static function method(string $provider, string $method): self
    {
        return new self("The [{$provider}] driver does not support [{$method}()].");
    }
}
