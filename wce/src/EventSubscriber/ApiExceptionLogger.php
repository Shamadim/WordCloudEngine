<?php

namespace App\EventSubscriber;

use App\EventSubscriber\RequestIdSubscriber;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionLogger implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $req = $event->getRequest();
        $id = $req->attributes->get(RequestIdSubscriber::ATTRIBUTE);

        $e = $event->getThrowable();

        $this->logger->error("[{$id}] Unhandled exception", [
            'exception' => get_class($e),
            'message'   => $e->getMessage(),
        ]);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
