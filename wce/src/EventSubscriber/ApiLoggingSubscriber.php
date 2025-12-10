<?php

namespace App\EventSubscriber;

use App\EventSubscriber\RequestIdSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiLoggingSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $id = $request->attributes->get(RequestIdSubscriber::ATTRIBUTE);

        // Store start time for performance logging
        $request->attributes->set('_start_time', microtime(true));

        $this->logger->info("[{$id}] Request started", [
            'path'   => $request->getPathInfo(),
            'method' => $request->getMethod(),
        ]);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $id = $request->attributes->get(RequestIdSubscriber::ATTRIBUTE);

        $start = $request->attributes->get('_start_time');
        $duration = round((microtime(true) - $start) * 1000, 2); // ms

        $this->logger->info("[{$id}] Request finished", [
            'status'   => $event->getResponse()->getStatusCode(),
            'duration' => $duration.'ms',
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 20],
            KernelEvents::RESPONSE => ['onKernelResponse', -20],
        ];
    }
}
