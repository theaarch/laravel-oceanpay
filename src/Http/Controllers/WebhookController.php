<?php

namespace Theaarch\Oceanpay\Http\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Theaarch\Oceanpay\Contracts\Webhooks;
use Theaarch\Oceanpay\OceanpayManager;
use Theaarch\Oceanpay\Webhooks\WebhookDispatcher;

class WebhookController
{
    /**
     * Handle a webhook for the provider channel the URL names, with that channel's driver and settings.
     *
     * @throws NotFoundHttpException when no channel has the key, or its driver does not take webhooks
     * @throws BindingResolutionException
     */
    public function __invoke(Request $request, OceanpayManager $manager, WebhookDispatcher $dispatcher, string $providerChannel, string $object): Response
    {
        $channel = $manager->findChannel($providerChannel) ?? throw new NotFoundHttpException('Unknown provider channel.');

        try {
            $provider = $manager->channel($channel);
        } catch (InvalidArgumentException $exception) {
            throw new NotFoundHttpException('Unknown provider channel driver.', $exception);
        }

        if (! $provider instanceof Webhooks) {
            throw new NotFoundHttpException('The provider channel driver does not take webhooks.');
        }

        return $dispatcher->dispatch($provider, $request, $object, $channel->getProviderChannelDriver());
    }
}
