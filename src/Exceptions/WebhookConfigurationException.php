<?php

namespace Theaarch\Oceanpay\Exceptions;

/**
 * A webhook that cannot be verified because the driver's settings are incomplete, e.g. no
 * client secret is configured. Unlike other rejected webhooks, it is reported, since it is a
 * setup mistake that rejects every webhook until it is fixed.
 */
class WebhookConfigurationException extends InvalidWebhookException
{
    //
}
