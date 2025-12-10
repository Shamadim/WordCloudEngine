<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestIdSubscriber implements EventSubscriberInterface
{
    public const ATTRIBUTE = '_request_id';

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Create unique ID per request
        $request->attributes->set(self::ATTRIBUTE, bin2hex(random_bytes(6)));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
